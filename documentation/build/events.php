<?php
/**
 *	So, for the interests of documentation, let's generate a JSON object
 *	that can be manipulated somehow into some sort of public method
 *	access documentation.
 */


# bootstrap the autoloader
$root = realpath("../../");

define("OUTRAGEbot_DEBUG", true);

require "_parser.php";

require $root."/classes/outragebot/core/autoloader.php";
require $root."/classes/externals/PEAR/Services/JSON.php";

\OUTRAGEbot\Core\Autoloader::register($root);


# go through some basic modules...
$methods = [];
$events = glob("../../classes/outragebot/event/events/*.php");

foreach($events as $item)
{
	require $item;
	
	$name = basename($item);
	$name = substr($name, 0, -4);
	
	$numeric = strtoupper($name);
	$numeric = str_replace("NUMERIC", "", $numeric);
	
	$methods[$numeric] = (new ReflectionClass("\\OUTRAGEbot\\Event\\Events\\".$name))->getMethod("invoke");
}

$global_methods = parse($root, $methods);


# now for the funny part - we have to now go through each of the child
# modules, and modify the result to represent what they really mean.
# heh!
$subs = glob("../../classes/outragebot/event/events/*");

foreach($subs as $sub)
{	
	if(!is_dir($sub))
		continue;
	
	$methods = [];
	$namespace = strtoupper(basename($sub));
	
	$events = glob($sub."/*.php");
	
	foreach($events as $item)
	{
		require $item;
		
		$name = basename($item);
		$name = substr($name, 0, -4);
		
		$numeric = strtoupper($name);
		$numeric = str_replace("NUMERIC", "", $numeric);
		
		$methods[$numeric] = (new ReflectionClass("\\OUTRAGEbot\\Event\\Events\\".$namespace."\\".$name))->getMethod("invoke");
	}
	
	$local_methods = parse($root, $methods);
	
	foreach($local_methods as $key => $item)
		$local_methods[$key]["metadata"]["method"] = $namespace;
	
	$global_methods = array_merge($global_methods, $local_methods);
}



# since we want it in JSON format...
echo json_encode([ "events" => $global_methods ]);
exit;