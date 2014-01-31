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


# go through the modules and include them
$methods = [];
$modules = glob("../../classes/outragebot/script/instance.php");

foreach($modules as $module)
{
	require $module;
	
	$name = basename($module);
	$name = substr($name, 0, -4);
	
	$reflector = new ReflectionClass("\\OUTRAGEbot\\Script\\".$name);
	
	foreach($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $item)
		$methods[$item->name] = $item;
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
$global_methods = parse($root, $methods);

foreach($global_methods as $key => $method)
	$global_methods[$key]["metadata"]["class"] = "Script";


# since we want it in JSON format...
echo json_encode([ "methods" => $global_methods ]);
exit;