<?php
/**
 *	Evaluation script for OUTRAGEbot.
 */


class evaluationbf1451157951d89c2ec96ee287c7ff4f59801794 extends OUTRAGEbot\Script\Instance
{
	/**
	 *	Store the variables from the execution environment in here.
	 */
	private $environment = [];
	
	
	/**
	 *	Called whenever the script is constructed.
	 */
	public function construct()
	{
		$this->addCommandHandler($this->instance->network->delimiter, "compile");
	}
	
	
	/**
	 *	Called to handle evaluation requests.
	 */
	public function compile($channel, $user, $payload)
	{
		if(!$user->is_admin)
			return true;
		
		try
		{
			if($this->environment)
				extract($this->environment, EXTR_SKIP);
			
			ob_start();
			
			eval($payload);
			
			$this->environment = get_defined_vars();
			
			unset($this->environment["channel"]);
			unset($this->environment["user"]);
			unset($this->environment["payload"]);
			
			$output = ob_get_flush();
		}
		catch(Exception $exception)
		{
			$channel->send("Exception in evaluated code: ".$exception->getMessage());
			return true;
		}
		
		$channel->send($output);
		return true;
	}
}
