<?php
/**
 *	Grabs the output of the various scripts and sticks it into nice
 *	and easy to access arrays.
 */


function get_registered_events()
{
	$contents = file_get_contents("output/events.json");
	$contents = json_decode($contents);
	
	return $contents->events;
}


function get_registered_methods()
{
	$set = [];
	
	foreach([ "script", "methods" ] as $item)
	{
		$contents = file_get_contents("output/".$item.".json");
		$contents = json_decode($contents);
		
		foreach($contents->methods as $method)
			$set[$method->metadata->method] = $method;
	}
	
	return $set;
}