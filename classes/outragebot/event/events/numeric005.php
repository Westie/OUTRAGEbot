<?php
/**
 *	Handler for the 005 numeric for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;


class Numeric005 extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$items = array_slice($this->packet->parts, 3);
		
		foreach($items as $item)
		{
			if($item[0] == ":")
			{
				break;
			}
			
			$matches = [];
			
			if(preg_match("/^(.*?)=(.*)$/", $item, $matches))
			{
				$this->socket->serverconf[$matches[1]] = $matches[2];
			}
			else
			{
				$this->socket->serverconf[$item] = $item;
				
				switch($item)
				{
					case "UHNAMES":
					case "NAMESX":
					{
						$this->socket->write("PROTOCTL ".$item);
						break;
					}
				}
			}
		}
		
		return parent::invoke();
	}
}