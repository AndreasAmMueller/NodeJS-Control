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
	
	private $pid;
	
	/* Constants (default values)
	============================= */
	const PathBinaryWin = '"C:\Program Files\nodejs\node.exe"';
	const PathBinaryMac = '/usr/local/bin/node';
	const PathBinaryLnx = '/usr/local/bin/node';
	
	function __construct() {
		switch ($this->GetSystem()) {
			case "mac":
				$this->pathBinary = self::PathBinaryMac;
				break;
			case "lnx":
				$this->pathBinary = self::PathBinaryLnx;
				break;
			default:
				$this->pathBinary = self::PathBinaryWin;
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
			throw new Exception("File ".$path." does not exist");
		
		if (!is_executable($path))
			throw new Exception("File ".$path." is not executable");
		
		$this->pathBinary = $path;
	}
	
	public function GetScript() {
		return $this->pathScript;
	}
	public function SetScript($path) {
		if (!file_exists($path))
			throw new Exception("File " . $path . " does not exist");
		
		$this->pathScript = $path;
	}
	
	public function GetPIDFile() {
		return $this->pathPidFile;
	}
	public function SetPIDFile($path) {
		$dir = dirname($path);
		
		if (!is_writable($dir))
			throw new Exception("Directory ".$dir." not writable for PID file");
		
		$this->pathPidFile = $path;
	}
	
	public function GetLogfile() {
		return $this->pathLogfile;
	}
	public function SetLogfile($path) {
		$dir = dirname($path);
		if (!is_writable($dir))
			throw new Exception("Directory ".$dir." not writable for logfile");
		
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
		// TODO: run script in background
		//       write outputs to log
	}
	
	public function Stop() {
		// TODO: stop script running in background
	}
	
	
	
	
	
	
	
	private function GetSystem() {
		if (substr(__DIR__, 0, 1) == "/") {
			return (exec("uname") == "Darwin") ? "mac" : "lnx";
		} else {
			return "win";
		}
	}
	
}

?>