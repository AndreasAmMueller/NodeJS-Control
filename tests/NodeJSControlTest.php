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
}



?>
