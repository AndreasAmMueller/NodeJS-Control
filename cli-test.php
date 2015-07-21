<?php

require_once __DIR__.'/NodeJSControl.class.php';
use AMWD\NodeJSControl as NodeJSControl;


if (!is_dir(__DIR__.'/tmp/'))
{
	if (!mkdir(__DIR__.'/tmp/'))
	{
		die("Could not create tmp");
	}
}

$node = new NodeJSControl();
$node->SetPIDFile(__DIR__.'/tmp/node-'.md5(__DIR__).'.pid');
$node->SetLogfile(__DIR__.'/tmp/node-'.md5(__DIR__).'.log');
$node->SetScript(__DIR__.'/node_scripts/node-console.js');

//echo $node->GetInfo();
//echo PHP_EOL;
//$testConsole = $node->Run();
//echo "Console Test: ";
//echo ($testConsole[0] == "This is a NodeJS console test" ? "success" : "failed");
//echo PHP_EOL;


###################   OK   ###########################
$node->SetScript(__DIR__.'/node_scripts/node-http.js');
echo $node->GetInfo();

echo PHP_EOL;
print_r($node->RunBackground());
echo PHP_EOL;
echo "Status: ".$node->GetStatus();
echo PHP_EOL;
echo "PID: ".$node->GetPID();

echo PHP_EOL;
echo PHP_EOL;

sleep(1);

/*
echo $node->GetLogfile();
echo PHP_EOL;
*/

echo "File exists: ".file_exists($node->GetLogfile());
echo PHP_EOL;

/*$log = file_get_contents($node->GetLogfile());
$log = trim($log);
echo "Logfile Test: ";
echo ($log == "Server running at http://0.0.0.0:8000/") ? "success" : "failed";
echo PHP_EOL;*/

$http = file_get_contents('http://127.0.0.1:8000/');
$http = trim($http);
echo "HTTP Test: ";
echo ($http == "This is a NodeJS http test") ? "success" : "failed";
echo PHP_EOL;

echo PHP_EOL;
$node->Stop();
echo $node->GetInfo();


?>