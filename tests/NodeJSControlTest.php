<?php

namespace AMWD;

require_once __DIR__.'/../src/NodeJSControl.class.php';

class NodeJSControlTest extends \PHPUnit_Framework_TestCase
{
	public function testDefaultConstructor()
	{
		if (substr(__DIR__, 0, 1) == "/")
		{
			$sys = (exec("uname") == "Darwin") ? "mac" : "lnx";
		}
		else
		{
			$sys = "win";
		}

		$control = new NodeJSControl();

		if ($sys == "win")
		{
			$this->assertEquals(NodeJSControl::PathBinaryWin, $control->GetExecutable());
		}
		else
		{
			$this->assertEquals(NodeJSControl::PathBinaryUnx, $control->GetExecutable());
		}
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
		mkdir(__DIR__.'/tmp/');
		$obj->SetPIDFile(__DIR__.'/tmp/node.pid');
		$obj->SetLogfile(__DIR__.'/tmp/node.log');
	}
}



?>
