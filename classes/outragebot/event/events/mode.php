<?php
/**
 *	Handler for the mode event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Mode extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel     Channel in which modes were applied
	 *	@supplies Element\User    $user        User who applied those changes
	 *	@supplies string          $operations  List of operations
	 *	@supplies array           $arguments   Arguments for those operations
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->user);
		$operations = $this->packet->parts[3];
		$arguments = explode(" ", !empty($this->packet->parts[4]) ? $this->packet->parts[4] : "");
		
		$this->process($channel, $operations, $arguments);
		
		return $this->dispatch([ $channel, $user, $operations, $arguments ]);
	}
	
	
	/**
	 *	Our helper method to deal with well, modes.
	 */
	public function process($channel, $operations, $arguments)
	{
		$set = 0;
		$length = strlen($operations);
		
		$settings = [ "-" => 0, "+" => 1 ];
		
		for($i = 0; $i < $length; ++$i)
		{
			$character = $operations[$i];
			
			# are we setting or removing a mode
			if(isset($settings[$character]))
			{
				$set = $settings[$character];
				continue;
			}
			
			# check to see if permissions are being set
			if(isset($this->instance->serverconf->prefixes[$character]))
			{
				$target = array_shift($arguments);
				
				if($set == 0)
				{
					if(!isset($channel->users[$target]))
						continue;
					
					$channel->users[$target]["modes"] = str_replace($character, "", $channel->users[$target]["modes"]);
				}
				elseif($set == 1)
				{
					if(!isset($channel->users[$target]))
						continue;
					
					$channel->users[$target]["modes"] .= $character;
				}
				
				continue;
			}
			
			/**
			 *	@todo: fix this, it's not properly compliant
			 */
			
			# arguments required at all times here
			if(in_array($character, $this->instance->serverconf->chanmodes[0]))
			{
				if($set == 1)
					$channel->modes[$character] = array_shift($arguments);
				else
					unset($channel->modes[$character]);
				
				continue;
			}
			
			if(in_array($character, $this->instance->serverconf->chanmodes[1]))
			{
				if($set == 1)
					$channel->modes[$character] = array_shift($arguments);
				else
					unset($channel->modes[$character]);
				
				continue;
			}
			
			# arguments passed only if enabled, or changed
			if(in_array($character, $this->instance->serverconf->chanmodes[2]))
			{
				if($set == 1)
					$channel->modes[$character] = array_shift($arguments);
				else
					unset($channel->modes[$character]);
				
				continue;
			}
			
			# boolean values here
			if(in_array($character, $this->instance->serverconf->chanmodes[3]))
			{
				if($set == 1)
					$channel->modes[$character] = true;
				else
					unset($channel->modes[$character]);
				
				continue;
			}
		}
		
		return true;
	}
}