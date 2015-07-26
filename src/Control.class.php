<?php
/*
 * Control.class.php
 *
 * (c) Andreas Mueller <webmaster@am-wd.de>
 */
namespace AMWD\NodeJS;
require_once __DIR__.'/Process.class.php';
require_once __DIR__.'/PathException.php';

 /**
 *Collection of functions to control your NodeJS scripts
 *
 * This class wrappes all functions to control your scripts running in background of your page.
 * 
 * @package    NodeJS
 * @author     Andreas Mueller <webmaster@am-wd.de>
 * @copyright  (c) 2015 Andreas Mueller
 * @license    MIT - http://am-wd.de/index.php?p=about#license
 * @link       https://bitbucket.org/BlackyPanther/nodejs-control
 * @version    v1.0-20150724 | In Development
 */
class Control {
	/**
	 * Version of this Class
	 * @var string
	 */
	private $controlVersion = "1.0";

	/**
	 * Absolute path to executable NodeJS binary
	 * @var string
	 */
	protected $pathBinary;

	/**
	 * Absolute path to your script you want to control
	 * @var string
	 */
	protected $pathScript;

	/**
	 * Absolute path to the file, where the ProcessID will be saved in
	 * @var string
	 */
	protected $pathPidFile;

	/**
	 * Absolute path to the logfile
	 * @var string
	 */
	protected $pathLogfile;
	
	/**
	 * process to run in background
	 * @var Process
	 */
	protected $process;

	/**
	 * Initialize new instance of NodeJSControl.
	 *
	 * Create a new object and try to locate executable of NodeJS.
	 * If it failes, Exception is thrown and no object will be available
	 *
	 * @param string $bin absolute path to NodeJS executable
	 *
	 * @return Control
	 *
	 * @throws PathException if no valid path can be found
	 * @throws \RuntimeException if no valid system can be found
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
					throw new PathException("Specify path to binary");

				$this->SetExecutable($bin);
				$this->process = new ProcessUnx();
				break;
			case 'win':
				if (empty($bin))
					$bin = file_exists('"C:\Program Files\nodejs\node.exe"') ? '"C:\Program Files\nodejs\node.exe"' : '';
				if (empty($bin))
					$bin = file_exists('"C:\Program Files (x86)\nodejs\node.exe"') ? '"C:\Program Files (x86)\nodejs\node.exe"' : '';
				if (empty($bin))
					throw new PathException("Specify path to binary");

				$this->SetExecutable($bin);
				$this->process = new ProcessWin();
				break;
			default:
				throw new \RuntimeException("Unknown system");
		}
		
		$this->UpdateProcess();
	}

	/**
	 * Try to return current installed node version
	 *
	 * @return string
	 */
	public function NodeVersion() {
		$out = array();
		exec($this->pathBinary.' --version', $out);
		return $out[0];
	}

	/**
	 * Return version of this control
	 *
	 * @return string
	 */
	public function Version() {
		return $this->controlVersion;
	}

	/**
	 * list all relevant infos about the control
	 *
	 * @return string
	 */
	public function GetInfo() {
		$str = "NodeJS Control ".$this->Version().PHP_EOL;
		$str.= PHP_EOL;
		$str.= "Version:       ".$this->NodeVersion().PHP_EOL;
		$str.= "Path to exe:   ".$this->GetExecutable().PHP_EOL;
		$str.= "Script:        ".$this->GetScript().PHP_EOL;
		$str.= "PID File:      ".$this->GetPIDFile().PHP_EOL;
		$str.= "Logfile:       ".$this->GetLogfile().PHP_EOL;
		$str.= "Status:        ".($this->GetStatus() == "running" ? "running with PID ".$this->GetPID() : $this->GetStatus()).PHP_EOL;

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
	 * @throws \InvalidArgumentException if file not exists or not executable
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
	 * @return void
	 * @throws \InvalidArgumentException if file not exists
	 */
	public function SetScript($path) {
		if (!file_exists($path))
			throw new \InvalidArgumentException("File " . $path . " does not exist");

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
	 * @throws \InvalidArgumentException if directory not existing or not writable
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
	 * @throws \InvalidArgumentException if direcotry for logfile not exists or not writable
	 */
	public function SetLogfile($path) {
		$dir = dirname($path);

		if (!is_dir($dir))
			throw new \InvalidArgumentException("Directory ".$dir." not existing");
		if (!is_writable($dir))
			throw new \InvalidArgumentException("Directory ".$dir." not writable for logfile");

		$this->pathLogfile = $path;
	}

	/**
	 * return status of nodejs
	 * @return string
	 */
	public function GetStatus() {
		$this->UpdateProcess();
		
		return ($this->GetPID() == 0) ? 'stopped' : $this->process->Status();
	}

	/**
	 * return pid of background process; zero if not running
	 * @return int
	 */
	public function GetPID() {
		$this->UpdateProcess();
		return $this->process->GetPID();
	}



	/**
	 * Execute script in foreground and wait for termination
	 * @return string[]
	 * @throws PathExcetion if path to script or binary missing
	 */
	public function Execute() {
		if (empty($this->pathScript))
			throw new PathException("no path to script");

		if (empty($this->pathBinary))
			throw new PathException("no path to binary");

		$stdout = array();
		exec($this->pathBinary.' '.$this->pathScript, $stdout);

		return $stdout;
	}

	/**
	 * Execute script in background
	 * 
	 * Starting NodeJS with script in background. No wait for termination.
	 * To start the script a script (shell/batch) will be used
	 * 
	 * @param string $handle how to handle with logfile if set; override or append
	 * @return bool
	 * @throws PathException if directory for pid file not exists or not writable
	 */
	public function StartBackground($handle = Process::LOGFILE_OVERRIDE) {
		$dir = dirname($this->pathPidFile);

		if (!is_dir($dir))
			throw new PathException("Directory for PID file not exists");

		if (!is_writable($dir))
			throw new PathException("Directory for PID file not writable");

		$this->UpdateProcess();
		if ($this->process->Start($handle)) {
			file_put_contents($this->pathPidFile, $this->process->GetPID());
			return true;
		}
		
		return false;
	}

	/**
	 * stop the background running nodejs
	 * @return bool
	 * @throws PathException if pid file not exists
	 * @throw \RuntimeException if pid file is not writable
	 */
	public function StopBackground() {
		if (!file_exists($this->pathPidFile))
			throw new PathException("PID file not existing");
		
		if (!is_writable($this->pathPidFile))
			throw new \RuntimeException("PID file not writable");
		
		$this->UpdateProcess();
		if ($this->process->Stop()) {
			file_put_contents($this->pathPidFile, 0);
			return true;
		}
		
		return false;
	}

	/**
	 * Try to recognize system (win|lnx|mac)
	 * @return string
	 */
	private function GetSystem() {
		if (substr(__DIR__, 0, 1) == "/") {
			return (exec("uname") == "Darwin") ? "mac" : "lnx";
		} else {
			return "win";
		}
	}

	/**
	 * Updates data of process object
	 * @return void
	 */
	private function UpdateProcess() {
		$cmd = $this->pathBinary.' '.$this->pathScript;
		$this->process->SetCommand($cmd);
		
		if (!empty($this->pathLogfile))
				$this->process->SetLogfile($this->pathLogfile);
		
		if ($this->process->GetPID() == 0) {
			$pid = @file_get_contents($this->pathPidFile);
			$pid = (int)trim($pid);
			if ($pid > 0)
				$this->process->SetPID($pid);
		}
	}
}

?>