<?php

namespace AMWD;

require_once __DIR__.'/../src/NodeJSControl.class.php';

class NodeJSControlTest extends \PHPUnit_Framework_TestCase
{
	public function testDefaultConstructor()
	{
		$c = new NodeJSControl();

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
		$c = new NodeJSControl();
		$c->SetExecutable(__DIR__.'/exec');
	}

	public function testRunConsole()
	{
		$control = new NodeJSControl();
		$this->PrepareNode($control);

		$control->SetScript(__DIR__.'/testScripts/node-console.js');

		$test = $control->Run();

		$this->assertEquals(1, count($test));
		$this->assertEquals("This is a NodeJS console test", $test[0]);
	}





	private function PrepareNode($obj)
	{
		if (!is_dir(__DIR__.'/tmp'))
			mkdir(__DIR__.'/tmp');

		$obj->SetPIDFile(__DIR__.'/tmp/node.pid');
		$obj->SetLogfile(__DIR__.'/tmp/node.log');
	}

	private function GetSystem()
	{
		if (substr(__DIR__, 0, 1) == "/")
			return (exec("uname") == "Darwin") ? "mac" : "lnx";

		return "win";
	}
}



?>
