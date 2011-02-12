<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     38fb3c4f22acf74f171cab72e46deae0e093ece4
 *	Committed at:   Sat Feb 12 16:28:45 GMT 2011
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

		$sXML = utf8_encode(file_get_contents("http://www.google.com/ig/api?weather=".urlencode($sArguments)));
		$pXML = new SimpleXMLElement($sXML);

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
