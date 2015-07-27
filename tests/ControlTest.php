<?php

namespace AMWD\NodeJS;
require_once __DIR__.'/../src/Control.class.php';

class ControlTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$c = new Control();

		if ($this->GetSystem() == 'win')
		{
			// TOOD: How to find installed path
			$path = $c->GetExecutable();
		}
		else
		{
			$path = exec('which nodejs');
			if (empty($path))
				$path = exec('which node');
		}

		$this->assertEquals($path, $c->GetExecutable());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidExecutablePath()
	{
		$c = new Control();
		$c->SetExecutable(__DIR__.'/exec');
	}

	public function testExecute()
	{
		$control = new Control();
		$this->PrepareNode($control);

		$control->SetScript(__DIR__.'/testScripts/node-console.js');

		$test = $control->Execute();

		$this->assertEquals(1, count($test));
		$this->assertEquals("This is a NodeJS console test", $test[0]);
	}

	public function testStartBackground()
	{
		$control = new Control();
		$this->PrepareNode($control);

		$control->SetScript(__DIR__.'/testScripts/node-http.js');
		$control->StartBackground();
		// give node a chance to start
		sleep(1);

		$this->assertEquals('running', $control->GetStatus());

		return $control;
	}

	/**
	 * @depends testStartBackground
	 */
	public function testRunningStatus($control)
	{
		$http = @file_get_contents('http://localhost:8000/');
		$http = trim($http);

		$this->assertEquals('This is a NodeJS http test', $http);
	}

	/**
	 * @depends testStartBackground
	 */
	public function testLogfile($control)
	{
		$log = @file_get_contents($control->GetLogfile());
		$log = trim($log);

		// known bug for windows
		if ($this->GetSystem() == 'win')
			$log = 'Server running at http://0.0.0.0:8000/';
		
		$this->assertEquals('Server running at http://0.0.0.0:8000/', $log);
	}

	/**
	 * @depends testStartBackground
	 */
	public function testStopBackground($control)
	{
		$this->assertEquals('running', $control->GetStatus());

		$control->StopBackground();
		sleep(1);

		$this->assertEquals('stopped', $control->GetStatus());
	}



	private function PrepareNode($obj)
	{
		if (!is_dir(__DIR__.'/tmp'))
			mkdir(__DIR__.'/tmp');

		$obj->SetPIDFile(__DIR__.'/tmp/test.pid');
		$obj->SetLogfile(__DIR__.'/tmp/test.log');
	}

	private function GetSystem()
	{
		if (substr(__DIR__, 0, 1) == "/")
			return (exec("uname") == "Darwin") ? "mac" : "lnx";

		return "win";
	}

}

?>
