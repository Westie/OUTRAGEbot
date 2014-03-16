<?php
/**
 *	Grabs the output of the various scripts and sticks it into nice
 *	and easy to access arrays.
 */


function get_registered_events()
{
	$contents = file_get_contents("sources/compiled/events.json");
	$contents = json_decode($contents);
	
	return $contents->events;
}


function get_registered_methods()
{
	$set = [];
	
	foreach([ "script", "methods" ] as $item)
	{
		$contents = file_get_contents("sources/compiled/".$item.".json");
		$contents = json_decode($contents);
		
		foreach($contents->methods as $class => $methods)
		{
			if(!isset($set[$class]))
				$set[$class] = [];
			
			foreach($methods as $key => $method)
				$set[$class][$key] = $method;
		}
	}
	
	return $set;
}


function custom_highlight_string($string)
{
	return str_replace([ "&lt;?php&nbsp;", "?>" ], "", highlight_string("<?php ".$string, true));
}