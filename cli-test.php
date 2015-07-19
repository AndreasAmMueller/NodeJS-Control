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
$node->SetPIDFile(__DIR__.'/tmp/node_'.md5(__DIR__).'.pid');
$node->SetLogfile(__DIR__.'/tmp/node_'.md5(__DIR__).'.log');
$node->SetScript(__DIR__.'/node_scripts/node-console.js');

echo $node->GetInfo();
echo PHP_EOL;
print_r($node->Run());

?>