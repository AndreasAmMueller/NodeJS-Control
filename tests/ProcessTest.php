<?php

namespace AMWD\NodeJS;
require_once __DIR__.'/../src/Process.class.php';

class ProcessTest extends \PHPUnit_Framework_TestCase
{
	public function testUnixConstructor() {
		try {
			$p = new ProcessUnx('php '.__DIR__.'/../tools/phpunit.phar --version');
			// Unix system -> cary on with asserts
			$this->assertEquals('php '.__DIR__.'/../tools/phpunit.phar --version', $p->GetCommand());
			$this->assertEmpty($p->GetLogfile());
			$this->assertEquals(0, $p->getPID());
			
		} catch (\RuntimeException $ex) {
			// Windows System -> check Exception text
			$this->assertEquals('Unix processes not running on windows machines', $ex->getMessage());
		}
	}
	
	public function testWindowsConstructor() {
			try {
			$p = new ProcessWin('php '.__DIR__.'/../tools/phpunit.phar --version');
			// Windows system -> cary on with asserts
			$this->assertEquals('php '.__DIR__.'/../tools/phpunit.phar --version', $p->GetCommand());
			$this->assertEmpty($p->GetLogfile());
			$this->assertEquals(0, $p->getPID());
			
		} catch (\RuntimeException $ex) {
			// Unix System -> check Exception text
			$this->assertEquals('Windows processes not running on unix machines', $ex->getMessage());
		}
	}

}

?>