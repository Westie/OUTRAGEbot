<?php
/**
 *	So, for the interests of documentation, let's generate a JSON object
 *	that can be manipulated somehow into some sort of public method
 *	access documentation.
 */


# bootstrap the autoloader
$root = realpath("../../");
$globals = [];

define("OUTRAGEbot_DEBUG", true);

require "_parser.php";

require $root."/classes/outragebot/core/autoloader.php";
require $root."/classes/externals/PEAR/Services/JSON.php";

\OUTRAGEbot\Core\Autoloader::register($root);


# go through the modules and include them
# and run the modules!
#
# need to make sure that somewhere in the documentation that people
# are really aware that this happens, perhaps make them aware of a
# definition of something
$methods = [];
$modules = glob("../../classes/outragebot/module/modules/*.php");

foreach($modules as $module)
{
	require $module;
	
	$name = basename($module);
	$name = substr($name, 0, -4);
	
	$object = (new ReflectionClass("\\OUTRAGEbot\\Module\\Modules\\".$name))->newInstance();
	
	if(!empty($object->__methods))
	{
		foreach($object->__methods as $method => $reflector)
			$methods[$method] = $reflector;
	}
}


# now we have all the public methods within modules, now we need
# to single the ones we actually want out.
# these are methods with partial javadoc - something along these
# lines:
#
#	/**
#	 *	bla bla bla bla bla? bla bla bla blah!
#	 *	
#	 *	@param  string $string  blah Blah blah
#	 *	@return string          blah Blah blah
#	 */
#
# return values are optional... dunno if I should make it
# mandatory?
#
# also, they need to be introduced into the stack with introduceModule,
# this script will force this - and then read from some debug variable
# somewhere.
$globals["OUTRAGEbot\\Script"] = parse_methods($root, $methods);


# thankfully we don't have to do as awkward things for the other things I want
# to document, such as the user & channel classes.
$objects = array
(
	'OUTRAGEbot\Element\User',
	'OUTRAGEbot\Element\Channel',
);

foreach($objects as $object)
{
	$class = new ReflectionClass($object);
	$manifest = $class->getMethods(ReflectionMethod::IS_PUBLIC);
	
	$methods = [];
	$properties = [];
	
	foreach($manifest as $item)
	{
		if($class->getName() != $item->getDeclaringClass()->getName())
			continue;
		
		$name = $item->getShortName();
		
		if(preg_match("/^__/", $name))
			continue;
		
		if(preg_match("/^getter_/", $name))
			$properties[substr($name, 7)] = $item;
		else
			$methods[$name] = $item;
	}
	
	$methods_parsed = parse_methods($root, $methods);
	$properties_parsed = parse_properties($root, $properties);
	
	$globals[$object] = [];
	
	foreach($properties_parsed as $key => $item)
		$globals[$object][$key] = $item;
	
	foreach($methods_parsed as $key => $item)
		$globals[$object][$key] = $item;
	
	if(empty($globals[$object]))
		unset($globals[$object]);
}


# we might want to add some redundancy here - option class reference in the
# metadata property
foreach($globals as $object => $items)
{
	foreach($items as $name => $method)
		$globals[$object][$name]["metadata"]["class"] = $object;
}


# since we want it in JSON format...
echo json_encode([ "methods" => $globals ], JSON_PRETTY_PRINT);
exit;