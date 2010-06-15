<?php
/**
 *	ConfigParser class for OUTRAGEbot
 *
 *	This class deals with the parsing of the bots.
 *
 *	This class loads the bots details from the INI file, then executes it. This enables the bot to have a very
 *	modular and easily modifiable structure. Due to the fact that this has moved from the rather redundant
 *	PHP based configuration, here is a sample (containing all the current values) of the new config file.
 *
 -	Yay, epic HTML formatting.
 *	<pre>
 *	<font color="#008000">; </font>
 *	<font color="#008000">; This header has a '~' suffix, which denotes that it is NOT</font>
 *	<font color="#008000">; an IRC bot, but rather a needed config file. (It must be named ~Network.)</font>
 *	<font color="#008000">; </font>
 *	
 *	<font color="4E009B"><b>[~Network]</b></font>
 *	<font color="#008000">; These keys are needed for the bot to operate.</font>
 *	name = FFSNetwork
 *	host = irc.ffsnetwork.com
 *	port = 6667
 *	owners = westie-cat.co.uk
 *	<font color="#008000">; These keys are optional, but are useful to have.</font>
 *	bind = 193.238.85.98
 *	channels = #westie, #channel2
 *	plugins = Evaluation, AutoInvite
 *	rotation = SEND_DIST
 *	quitmsg = "A quit messag- wait, why are you reading this?"
 *	delimiter = "!"
 *
 *	<font color="4E009B"><b>[OUTRAGEbot]</b></font>
 *	<font color="#008000">; </font>
 *	<font color="#008000">; This header doesn't have a '~' suffix, which denotes that it</font>
 *	<font color="#008000">; is an IRC bot. All three are needed.</font>
 *	<font color="#008000">; </font>
 *	altnick = OUTRAGEbot`
 *	username = testing
 *	realname = David Weston
 *
 *
 *	<font color="#008000">; </font>
 *	<font color="#008000">; This is an example of configuration for plugins. What you can do here</font>
 *	<font color="#008000">; is use the name of the plugin, with '~' prefixed to it, like '~Network'.</font>
 *	<font color="#008000">; You can then call this from PHP with $this->getConfig(); - which will</font>
 *	<font color="#008000">; the configuration (for that plugin only) in an array.</font>
 *	<font color="#008000">; </font>
 *	<font color="4E009B"><b>[~Evaluation]</b></font>
 *	<font color="#008000">; As you can tell, this is for the plugin 'Evaluation'.</font>
 *	testing = "It seems to work!"
 *	</pre>
 -	End of epic HTML formatting.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC1 (Git commit: 7636ab6a1e87abc7b52e265f41b908c96ecca575)
 */
 

class ConfigParser
{
	/**
	 *	Parses all of the /Configuration/ directory.
	 *
	 *	@ignore
	 */
	public function parseDirectory()
	{
		foreach(glob(BASE_DIRECTORY."/Configuration/*.ini") as $sGlob)
		{
			$this->parseConfigFile($sGlob);
		}		
	}
	
	
	/**
	 *	Parses a configuration file in /Configuration/. Note that $sConfig must not have an extension.
	 *
	 *	<code>Control::$oConfig->parseConfig("OUTRAGEbot"); // This loads /Configuration/OUTRAGEbot.ini</code>
	 *
	 *	@param string $sConfig Configuration filename
	 */
	public function parseConfig($sConfig)
	{
		if(file_exists(BASE_DIRECTORY."/Configuration/{$sConfig}.ini"))
		{
			$this->parseConfigFile(BASE_DIRECTORY."/Configuration/{$sConfig}.ini");
		}
	}
	
	
	/**
	 *	Parses a configuration file in any directory in the server (that it is possible to reach)
	 *
	 *	@param string $sConfig Exact configuration file location
	 */
	public function parseConfigFile($sConfig)
	{
		$sName = substr(basename($sConfig), 0, -4);
		
		if($sName[0] == "~")
		{
			return;
		}
		
		$aConfig = @parse_ini_file($sConfig, true);
		
		if(@count($aConfig) <= 1)
		{
			echo "Error: Corrupt configuration format - {$sConfig}".PHP_EOL;
			exit;
		}
		
		$oConfig = new stdClass();
		
		foreach($aConfig as $sKey => $aBot)
		{
			if($sKey[0] == '~')
			{
				$sKey = substr($sKey, 1);
				$oConfig->$sKey = $aBot;
				
				continue;
			}
			
			$oConfig->Bots[$sKey] = $aBot;
			$oConfig->Bots[$sKey]['nickname'] = $sKey;
		}
		
		Control::$aBots[$sName] = new Master($sName, $oConfig);
	}
}
