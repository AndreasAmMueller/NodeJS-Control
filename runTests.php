<?php

$cmd = array(); $stdout = array(); $retval = array();

// stdtest
$cmd[] = 'php tools/phpunit.phar tests/NodeJSControlTest';

/* Starting tests
================= */

echo "Starting Tests".PHP_EOL;
echo "--------------".PHP_EOL;
echo PHP_EOL;

for ($i = 0; $i < count($cmd); $i++)
{
	$stdout = array();
	$retval = 0;

	echo "Executing: ".$cmd[$i].PHP_EOL;
	echo "---".PHP_EOL;

	exec($cmd[$i], $stdout, $retval);

	foreach ($stdout as $line)
	{
		echo $line.PHP_EOL;
	}

	echo "Test returned with: ".$retval.PHP_EOL;
	echo PHP_EOL;
}

echo PHP_EOL;
echo "-------------".PHP_EOL;
echo "END Tests".PHP_EOL;

?>
