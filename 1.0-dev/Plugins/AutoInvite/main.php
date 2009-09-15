<?php


class AutoInvite extends Plugins
{
	private
		$iBindID = -1;
		
	function onConstruct()
	{
		/* Called when the plugin is constructed. */
		$this->iBindID = $this->bindCreate('INVITE', array($this, 'onInvite'), array(2, 3));
	}
	
	function onDestruct()
	{
		/* Called when the plugin is destructed. */
		$this->bindDelete($this->iBindID);
	}
	
	function onInvite($sNickname, $sChannel)
	{
		if($sNickname == $this->oBot->oCurrentBot->aConfig['nickname'])
		{
			$this->sendRaw("JOIN {$sChannel}", SEND_DIST);
		}
	}
}
		
?>
