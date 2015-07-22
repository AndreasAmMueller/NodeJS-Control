<?php
/**
 * NodeJSControl.class.php
 *
 * @author Andreas Mueller <webmaster@am-wd.de>
 * @license MIT - http://am-wd.de/index.php?p=about#license
 */

/*
 * Asscociate Namespace
 */
namespace AMWD;
@error_reporting(E_ALL);

/**
 * Collection of functions to control your NodeJS scripts
 *
 * This class wrappes all functions to control your scripts running in background of your page.
 *
 * @version GIT: $Id$ In development
 */
class NodeJSControl {
	/**
	 * Version of this Class
	 */
	private $version = "1.0";

	/**
	 * Absolute path to executable NodeJS binary
	 */
	private $pathBinary;

	/**
	 * Absolute path to your script you want to control
	 */
	private $pathScript;

	/**
	 * Absolute path to the file, where the ProcessID will be saved in
	 */
	private $pathPidFile;

	/**
	 * Absolute path to the logfile
	 */
	private $pathLogfile;

	/**
	 * Buffer the PID for less file operations
	 */
	private $pid;

	/**
	 * Proposed default path for NodeJS under Windows
	 */
	const PathBinaryWin = '"C:\Program Files\nodejs\node.exe"';

	/**
	 * path to script to manage start and stop in background on Unix machines
	 */
	const PathUnxBackground = 'bin/RunBackground.sh';

	/**
	 * path to script to manage start and stop in background on Windows machines
	 */
	const PathWinBackground = 'bin/RunBackground.bat';

	/**
	 * Initialize new instance of NodeJSControl.
	 *
	 * Create a new object and try to locate executable of NodeJS.
	 * If it failes, Exception is thrown and no object will be available
	 *
	 * @param string $bin absolute path to NodeJS executable
	 *
	 * @return NodeJSControl
	 *
	 * @throws InvalidArgumentException if no valid path can be found
	 * @throws RuntimeException if no valid system can be found
	 */
	function __construct($bin = '') {
		switch ($this->GetSystem()) {
			case 'mac':
			case 'lnx':
				if (empty($bin))
					$bin = exec('which nodejs');
				if (empty($bin))
					$bin = exec('which node');
				if (empty($bin))
					throw new \InvalidArgumentException("Specify path to binary");

				$this->SetExecutable($bin);
				break;
			case 'win':
				if (empty($bin))
					$bin = file_exists('"C:\Program Files\nodejs\node.exe"') ? '"C:\Program Files\nodejs\node.exe"' : '';
				if (empty($bin))
					$bin = file_exists('"C:\Program Files (x86)\nodejs\node.exe"') ? '"C:\Program Files (x86)\nodejs\node.exe"' : '';
				if (empty($bin))
					throw new \InvalidArgumentException("Specify path to binary");

				$this->SetExecutable($bin);
				break;
			default:
				throw new \RuntimeException("Unknown system");
		}

		$this->pid = 0;
	}


	/**
	 * Try to return current installed node version
	 *
	 * @return string
	 */
	public function GetVersion() {
		$out = array();
		exec($this->pathBinary.' --version', $out);
		return $out[0];
	}

	/**
	 * Return version of this control
	 *
	 * @return string
	 */
	public function GetControlVersion() {
		return $this->version;
	}

	/**
	 * list all relevant infos about the control
	 *
	 * @return string
	 */
	public function GetInfo() {
		$str = "NodeJS Control ".$this->GetControlVersion().PHP_EOL;
		$str.= PHP_EOL;
		$str.= "Version:     ".$this->GetVersion().PHP_EOL;
		$str.= "Path to exe: ".$this->GetExecutable().PHP_EOL;
		$str.= "PID File:    ".$this->GetPIDFile().PHP_EOL;
		$str.= "Logfile:     ".$this->GetLogfile().PHP_EOL;
		$str.= "Script:      ".$this->GetScript().PHP_EOL;
		$str.= "Status:      ".($this->GetStatus() == "running" ? "running with PID ".$this->GetPID() : "stopped").PHP_EOL;

		return $str;
	}

	/**
	 * Get Path of executable (node)
	 *
	 * @return string
	 */
	public function GetExecutable() {
		return $this->pathBinary;
	}

	/**
	 * Set path of executable (node)
	 *
	 * Sets the path of NodeJS executable. Checks the given path for existence and execution
	 *
	 * @param string $path Absolute path to executable of NodeJS
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException if file not exists or not executable
	 */
	public function SetExecutable($path) {
		if (!file_exists($path))
			throw new \InvalidArgumentException("File ".$path." does not exist");

		if (!is_executable($path))
			throw new \InvalidArgumentException("File ".$path." is not executable");

		$this->pathBinary = $path;
	}

	/**
	 * Return path to controlled script
	 *
	 * @return string
	 */
	public function GetScript() {
		return $this->pathScript;
	}

	/**
	 * Set path to controlled script
	 *
	 * Sets the path of script run by NodeJS. Checks the given path for existence.
	 *
	 * @param string $path Absolute path to controlled script
	 *
	 * @return void
	 */
	public function SetScript($path) {
		if (!file_exists($path))
			throw new \Exception("File " . $path . " does not exist");

		$this->pathScript = $path;
	}

	/**
	 * return current path to ProcessID file
	 * @return string
	 */
	public function GetPIDFile() {
		return $this->pathPidFile;
	}

	/**
	 * set path for ProcessId file
	 * @param string $path Absolute path to PID file
	 * @return void
	 * @throws InvalidArgumentException if directory not existing or not writable
	 */
	public function SetPIDFile($path) {
		$dir = dirname($path);

		if (!is_dir($dir))
			throw new \InvalidArgumentException("Directory ".$dir." not existing");

		if (!is_writable($dir))
			throw new \InvalidArgumentException("Directory ".$dir." not writable for PID file");

		$this->pathPidFile = $path;
	}

	/**
	 * return path to Logfile for background process
	 * @return string
	 */
	public function GetLogfile() {
		return $this->pathLogfile;
	}

	/**
	 * set path to logfile for background process
	 *
	 * Set the path for your logfile where all outputs from NodeJS will be written.
	 *
	 * @param string $path Absolute path to logfile
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function SetLogfile($path) {
		$dir = dirname($path);

		if (!is_dir($dir))
			throw new \InvalidArgumentException("Directory ".$dir." not existing");
		if (!is_writable($dir))
			throw new \InvalidArgumentException("Directory ".$dir." not writable for logfile");

		$this->pathLogfile = $path;
	}

	public function GetStatus() {
		$pid = $this->GetPID();

		if ($pid != 0 && $this->PIDinProcesslist())
			return "running";

		return "stopped";
	}

	public function GetPID() {
		if ($this->pid == 0) {
			$pid = @file_get_contents($this->pathPidFile);
			$pid = trim($pid);
			if (!empty($pid))
				$this->pid = $pid;
		}

		return $this->pid;
	}



	public function Run() {
		$out = array();
		$exit = 0;
		exec($this->pathBinary.' '.$this->pathScript, $out, $exit);

		if ($exit > 0)
			throw new Exception(implode(PHP_EOL, $out));

		return $out;
	}

	public function RunBackground() {
		switch ($this->GetSystem()) {
			case 'lnx':
			case 'mac':
				return $this->RunBackgroundUnx();
				break;
			case 'win':
				//return $this->RunBackgroundWin();
				break;
			default:
				throw new \Exception("unknown system");
				break;
		}
	}

	public function Stop() {
		switch ($this->GetSystem()) {
			case 'lnx':
			case 'mac':
				return $this->StopUnx();
				break;
			case 'win':
				//return $this->StopWin();
				break;
			default:
				throw new \Exception("Unknown system");
				break;
		}

		$this->pid = 0;
	}




	private function GetSystem() {
		if (substr(__DIR__, 0, 1) == "/") {
			return (exec("uname") == "Darwin") ? "mac" : "lnx";
		} else {
			return "win";
		}
	}

	private function RunBackgroundUnx() {
		if (empty($this->pathBinary) || !is_executable($this->pathBinary))
			throw new \Exception("Error on executable path");

		if (empty($this->pathScript) || !file_exists($this->pathScript))
			throw new \Exception("Error on script path");

		if (empty($this->pathPidFile))
			throw new \Exception("Error on pid-file path");

		if (empty($this->pathLogfile))
			throw new \Exception("Error on log-file path");

		if (!file_exists($this->pathUnxBin) || !is_executable($this->pathUnxBin))
			throw new \Exception("Error background worker");

		$exec = $this->pathUnxBin.' start '.$this->pathBinary.' '.$this->pathScript;
		$exec.= ' '.$this->pathPidFile.' '.$this->pathLogfile;

		$out = array();
		//$out[] = $exec;
		exec($exec, $out);

		return $out;
	}
	private function StopUnx() {
		if (empty($this->pathBinary) || !is_executable($this->pathBinary))
			throw new \Exception("Error on executable path");

		if (empty($this->pathScript) || !file_exists($this->pathScript))
			throw new \Exception("Error on script path");

		if (empty($this->pathPidFile))
			throw new \Exception("Error on pid-file path");

		if (empty($this->pathLogfile))
			throw new \Exception("Error on log-file path");

		if (!file_exists($this->pathUnxBin) || !is_executable($this->pathUnxBin))
			throw new \Exception("Error background worker");

		$exec = $this->pathUnxBin.' stop '.$this->pathBinary.' '.$this->pathScript;
		$exec.= ' '.$this->pathPidFile.' '.$this->pathLogfile;

		exec($exec);
	}

	private function PIDinProcesslist() {
		$pid = $this->GetPID();
		$line = '';

		foreach ($this->GetProcesslist() as $row) {
			if (strpos($row, $pid) !== false) {
				$line = $row;
			}
		}

		return !empty($line);
	}

	private function GetProcesslist() {
		$out = array();
		switch ($this->GetSystem()) {
			case 'mac':
			case 'lnx':
				exec('ps aux 2>&1', $out);
				break;
			case 'win':
				exec(__DIR__.'/bin/pslist.exe', $out);
				break;
			default:
				throw new \Exception("Unknown system");
		}

		return $out;
	}

}

?>
