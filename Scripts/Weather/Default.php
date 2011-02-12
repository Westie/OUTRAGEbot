<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     1de27b0aebb46c7123e76c6c916633a0606be8a6
 *	Committed at:   Sat Feb 12 15:26:10 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class Weather extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->addCommandHandler("weather", "getWeatherForLocation");
	}


	/**
	 *	So, the user wants weather, correct?
	 */
	public function getWeatherForLocation($sChannel, $sNickname, $sArguments)
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "USAGE: weather [Town or City name]");
			return END_EVENT_EXEC;
		}

		$pXML = simplexml_load_file("http://www.google.com/ig/api?weather=".urlencode($sArguments));

		$pCurrentConditions = $pXML->xPath("//xml_api_reply/weather/current_conditions");
		$pLocationDetails = $pXML->xPath("//xml_api_reply/weather/forecast_information");

		if(!$pLocationDetails[0]->city['data'])
		{
			$this->Notice($sNickname, "Nope, give me a real town name, please!");
			return END_EVENT_EXEC;
		}

		$this->Message($sChannel, "{b}Weather for {c:darkblue}{$pLocationDetails[0]->city['data']}", FORMAT);
		$this->Message($sChannel, "{c:darkgreen}Condition:{r} {$pCurrentConditions[0]->condition['data']}", FORMAT);
		$this->Message($sChannel, "{c:darkgreen}Temperature:{r} {$pCurrentConditions[0]->temp_c['data']}°C, {$pCurrentConditions[0]->temp_f['data']}°F", FORMAT);
		$this->Message($sChannel, "{c:darkgreen}Humidity:{r} ".substr($pCurrentConditions[0]->humidity['data'], 10), FORMAT);
		$this->Message($sChannel, "{c:darkgreen}Wind Speed:{r} ".substr($pCurrentConditions[0]->wind_condition['data'], 6), FORMAT);

		return END_EVENT_EXEC;
	}
}
