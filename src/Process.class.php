<?php
/**
 * Process.class.php
 * 
 * (c) Andreas Mueller <webmaster@am-wd.de>
 */
namespace AMWD\NodeJS;

/**
 * abstract object as basic representation of an system process
 *
 * Kind of a fork command in C.
 * 
 * @package    NodeJS
 * @author     Andreas Mueller <webmaster@am-wd.de>
 * @copyright  (c) 2015 Andreas Mueller
 * @license    MIT - http://am-wd.de/index.php?p=about#license
 * @link       https://bitbucket.org/BlackyPanther/nodejs-control
 * @version    v1.0-20150727 | semi stable
 */
abstract class Process {
	
	/**
	 * override if logfile exists
	 * @var string
	 */
	const LOGFILE_OVERRIDE = 'override';
	
	/**
	 * append if logfile exists
	 * @var string
	 */
	const LOGFILE_APPEND = 'append';
	
	/**
	 * command to execute
	 * @var string
	 */
	protected $command;
	
	/**
	 * PID of process running the command
	 * @var int
	 */
	protected $pid;
	
	/**
	 * path to logfile
	 * @var string
	 */
	protected $logfile;

	/**
	 * Initialize new Instance of Process
	 * 
	 * @param mixed $cmd command to execute or false if no command should be set
	 * @return Process
	 */
	function __construct($cmd = false) {
		if ($cmd != false)
			$this->command = $cmd;
		
		$this->pid = 0;
	}

	/**
	 * returns command to execute
	 * @return string
	 */
	public function GetCommand() {
		return $this->command;
	}

	/**
	 * set command to execute
	 * @param string $cmd command to execute
	 * @return void
	 */
	public function SetCommand($cmd) {
		$this->command = $cmd;
	}

	/**
	 * returns pid of process
	 * @return int
	 */
	public function GetPID() {
		return $this->pid;
	}

	/**
	 * set PID, if just monitoring / terminating a process
	 * @param int $pid process id in system
	 * @return void
	 * @throws \InvalidArgumentException if pid is less or equal zero
	 */
	public function SetPID($pid) {
		if ($pid <= 0)
			throw new \InvalidArgumentException("PID not valid");
		
		$this->pid = $pid;
	}

	/**
	 * returns path of logfile
	 * @return string
	 */
	public function GetLogfile() {
		return $this->logfile;
	}

	/**
	 * set path to logfile
	 * 
	 * if no path to logfile is set, all output is written to /dev/null
	 * 
	 * @param string $file absolute path to logfile
	 * @return void
	 * @throws \InvalidArgumentException if directory for logfile not exists or not writable
	 */
	public function SetLogfile($file) {
		$dir = dirname($file);

		if (!is_dir($dir))
			throw new \InvalidArgumentException("Logfile directory not existing");
		if (!is_writable($dir))
			throw new \InvalidArgumentException("Logfile directory not writable");

		$this->logfile = $file;
	}
	
	/**
	 * start process with given command
	 * 
	 * returns true if command is started and pid is greater than zero
	 *         false in any other case
	 * 
	 * @param string $handle how to handle with logfile if set; override or append
	 * @return bool
	 */
	abstract public function Start($handle = self::LOGFILE_OVERRIDE);
	
	/**
	 * stop process by given pid
	 * 
	 * returns true if process has been stopped
	 *         false else
	 * 
	 * @return bool
	 */
	abstract public function Stop();
	
	/**
	 * check process status
	 * 
	 * returns stopped or running
	 * 
	 * @return string
	 */
	abstract public function Status();
	
	/**
	 * Try to recognize system (win|lnx|mac)
	 * @static
	 * @return string
	 */
	protected static function GetSystem() {
		if (substr(__DIR__, 0, 1) == "/") {
			return (exec("uname") == "Darwin") ? "mac" : "lnx";
		} else {
			return "win";
		}
	}
}

/**
 * Object representing an system process on unix machines.
 *
 * Kind of a fork command. Running unix system commands / scripts for PHP
 * 
 * @package    NodeJS
 * @author     Andreas Mueller <webmaster@am-wd.de>
 * @copyright  (c) 2015 Andreas Mueller
 * @license    MIT - http://am-wd.de/index.php?p=about#license
 * @link       https://bitbucket.org/BlackyPanther/nodejs-control
 * @version    v1.0-20150724 | In Development
 */
class ProcessUnx extends Process {
	
	/**
	 * Initialize new Instance of ProcessUnx
	 * 
	 * @param mixed $cmd command to execute or false if no command should be set
	 * @return ProcessUnx
	 */
	public function __construct($cmd = false) {
		if (parent::GetSystem() == "win")
			throw new \RuntimeException("Unix processes not running on windows machines");
		
		parent::__construct($cmd);
	}
	
	/**
	 * start process with given command
	 * 
	 * returns true if command is started and pid is greater than zero
	 *         false in any other case
	 * 
	 * @param string $handle how to handle the logfile (if set)
	 * @return bool
	 */
	public function Start($handle = parent::LOGFILE_OVERRIDE) {
		$cmd = trim($this->command);
		if (!empty($cmd) && $this->pid == 0) {
			$this->RunCommand($handle);
			return $this->pid > 0;
		}
		
		return false;
	}
	
	/**
	 * stop process by given pid
	 * 
	 * returns true if process has been stopped
	 *         false else
	 * 
	 * @return bool
	 */
	public function Stop() {
		if ($this->pid == 0)
			return true;
		
		$cmd = 'kill '.$this->pid;
		exec($cmd);
		if ($this->Status() == 'stopped') {
			//$this->pid = 0;
			return true;
		}

		return false;
	}
	
	/**
	 * check process status
	 * 
	 * returns stopped or running
	 * 
	 * @return string
	 */
	public function Status() {
		if ($this->pid == 0)
			throw new \RuntimeException("Invalid PID");

		$cmd = 'ps -p '.$this->pid;
		exec($cmd, $stdout);
		if (!isset($stdout[1]))
			return 'stopped';

		return 'running';
	}
	
	/**
	 * execute given command; sets the pid property
	 * @param string $handle tell if logfile is appended or overridden
	 * @return void
	 */
	private function RunCommand($handle) {
		$cmd = 'nohup '.$this->command;
		
		if (!empty($this->logfile)) {
			if ($handle == parent::LOGFILE_APPEND) {
				$cmd .= ' >> '.$this->logfile;
			} else {
				$cmd .= ' > '.$this->logfile;
			}
		} else {
			$cmd .= ' > /dev/null';
		}
		
		$cmd.= ' 2>&1 & echo $!';
		
		exec($cmd, $stdout);
		$this->pid = (int)$stdout[0];
	}
}

/**
 * Object representing an system process on windows machines.
 *
 * Kind of a fork command. Running windows system commands / scripts for PHP
 * 
 * @package    NodeJS
 * @author     Andreas Mueller <webmaster@am-wd.de>
 * @copyright  (c) 2015 Andreas Mueller
 * @license    MIT - http://am-wd.de/index.php?p=about#license
 * @link       https://bitbucket.org/BlackyPanther/nodejs-control
 * @version    v1.0-20150724 | In Development
 */
class ProcessWin extends Process {

	/**
	 * Initialize new Instance of ProcessWin
	 * 
	 * @param mixed $cmd command to execute or false if no command should be set
	 * @return ProcessWin
	 */
	public function __construct($cmd = false) {
		if (parent::GetSystem() != "win")
			throw new \RuntimeException("Windows processes not running on unix machines");
		
		parent::__construct($cmd);
	}

	/**
	 * start process with given command
	 * 
	 * returns true if command is started and pid is greater than zero
	 *         false in any other case
	 * 
	 * @param string $handle how to handle the logfile (if set)
	 * @return bool
	 */
	public function Start($handle = parent::LOGFILE_OVERRIDE) {
		$cmd = trim($this->command);
		if (!empty($cmd) && $this->pid == 0) {
			$this->RunCommand($handle);
			return $this->pid > 0;
		}
		
		return false;
	}
	
	/**
	 * stop process by given pid
	 * 
	 * returns true if process has been stopped
	 *         false else
	 * 
	 * @return bool
	 */
	public function Stop() {
		if ($this->pid == 0)
			return true;
		
		$cmd = __DIR__.'\bin\pskill.exe -accepteula '.$this->pid.' 2>&1';
		exec($cmd);
		if ($this->Status() == 'stopped') {
			//$this->pid = 0;
			return true;
		}

		return false;
	}
	
	/**
	 * check process status
	 * 
	 * returns stopped or running
	 * 
	 * @return string
	 */
	public function Status() {
		if ($this->pid == 0)
			return 'stopped';

		$cmd = __DIR__.'\bin\pslist.exe -accepteula '.$this->pid.' 2>&1';
		exec($cmd, $stdout);
		if (!empty($stdout[count($stdout)-1])) {
			return 'running';
		}
		
		return 'stopped';
	}
	
	/**
	 * execute given command; sets the pid property
	 * @param string $handle tell if logfile is appended or overridden
	 * @return void
	 */
	private function RunCommand($handle) {
		$cmd = __DIR__.'\bin\psexec.exe -d '.$this->command.' 2>&1';
		exec($cmd, $stdout);
		
		$words = explode(' ', $stdout[count($stdout)-1]);
		$pid = str_replace('.', '', $words[count($words)-1]);
		
		$this->pid = (int)$pid;
	}
}

?>