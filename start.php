<?php
/**
 *	OUTRAG3bot - standard OUTRAGEbot but with a three added
 *	for awesomeness.
 *	
 *	This will be the last re-write for the bot, I swear! It's
 *	just that PHP has grown so much over the past year, that
 *	the code for version 2 seems so antiquated.
 */


try
{
	/**
	 *	First, we'll just configure some PHP settings that are needed
	 *	to blurt out billions of errors, and mask them at the same
	 *	time.
	 */
	gc_enable();
	gc_collect_cycles();
	
	ini_set("error_reporting", "on");
	
	error_reporting(E_ALL | E_STRICT);
	date_default_timezone_set("Europe/London");
	
	
	/**
	 *	Next, we'll include the autoloader.
	 */
	include "classes/outragebot/core/autoloader.php";
	include "classes/externals/PEAR/Services/JSON.php";
	
	\OUTRAGEbot\Core\Autoloader::register(__DIR__);
	
	
	/**
	 *	Now we've got the ability to autoload classes, we'll take this
	 *	opportunity to scan and load modules.
	 */
	\OUTRAGEbot\Module\Stack::getInstance()->scan();
	
	
	/**
	 *	Then, we'll need to load configuration files.
	 */
	$hive = \OUTRAGEbot\Core\Hive::getInstance();
	
	$files = glob("configuration/*.json");
	
	foreach($files as $file)
	{
		$configuration = new \OUTRAGEbot\Core\Configuration();
		$configuration->load($file);
		
		$instance = new \OUTRAGEbot\Connection\Instance();
		$instance->configure($configuration);
		
		$hive->push($instance);
	}
	
	
	/**
	 *	Next, we'll just start to connect everything.
	 */
	foreach($hive as $instance)
	{
		$instance->connect();
	}
	
	
	/**
	 *	And then, I guess what we need to do next is keep it looping.
	 */
	while($hive->tick());
}
catch(Exception $exception)
{
	echo $exception->getMessage();
	exit(1);
}