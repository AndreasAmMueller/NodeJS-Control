<?php

/**
 * NodeJSControl.class.php
 *
 * @author Andreas Mueller <webmaster@am-wd.de>
 * @version 1.0-20150717
 *
 * @description
 * Wrappes all needed functions to control a NodeJS segment within an CMS
 **/
namespace AMWD;

@error_reporting(E_ALL);

class NodeJSControl {
	/* Version of this Class
	======================== */
	private $version = "1.0";
	
	/* private Member
	================= */
	private $pathBinary;
	private $pathScript;
	private $pathPidFile;
	private $pathLogfile;
	
	private $pathUnxBin;
	private $pathWinBin;
	
	private $pid;
	
	/* Constants (default values)
	============================= */
	const PathBinaryWin = '"C:\Program Files\nodejs\node.exe"';
	const PathBinaryMac = '/usr/local/bin/node';
	const PathBinaryLnx = '/usr/local/bin/node';
	
	function __construct() {
		$this->pathUnxBin = __DIR__.'/bin/RunBackground.sh';
		$this->pathWinBin = __DIR__.'/bin/RunBackground.bat';
		
		switch ($this->GetSystem()) {
			case "mac":
				$this->SetExecutable(self::PathBinaryMac);
				break;
			case "lnx":
				$this->SetExecutable(self::PathBinaryLnx);
				break;
			default:
				$this->SetExecutable(self::PathBinaryWin);
				break;
		}
		
		$this->pid = 0;
	}
	
	
	
	public function GetVersion() {
		$out = array();
		exec($this->pathBinary.' --version', $out);
		return $out[0];
	}
	
	public function GetControlVersion() {
		return $this->version;
	}
	
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
	
	public function GetExecutable() {
		return $this->pathBinary;
	}
	public function SetExecutable($path) {
		if (!file_exists($path))
			throw new \Exception("File ".$path." does not exist");
		
		if (!is_executable($path))
			throw new \Exception("File ".$path." is not executable");
		
		$this->pathBinary = $path;
	}
	
	public function GetScript() {
		return $this->pathScript;
	}
	public function SetScript($path) {
		if (!file_exists($path))
			throw new \Exception("File " . $path . " does not exist");
		
		$this->pathScript = $path;
	}
	
	public function GetPIDFile() {
		return $this->pathPidFile;
	}
	public function SetPIDFile($path) {
		$dir = dirname($path);
		
		if (!is_writable($dir))
			throw new \Exception("Directory ".$dir." not writable for PID file");
		
		$this->pathPidFile = $path;
	}
	
	public function GetLogfile() {
		return $this->pathLogfile;
	}
	public function SetLogfile($path) {
		$dir = dirname($path);
		if (!is_writable($dir))
			throw new \Exception("Directory ".$dir." not writable for logfile");
		
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