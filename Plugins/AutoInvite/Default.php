<?php
/**
 *	AutoInvite class for OUTRAGEbot.
 *
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */


class AutoInvite extends Plugins
{
	private
		$iBindID = -1;
	
	
	function onConstruct()
	{
		/* Called when the plugin is constructed. */
		$this->iBindID = $this->addHandler('INVITE', 'onInvite', array(2, 3));
	}
	
	
	function onInvite($sNickname, $sChannel)
	{
		if($sNickname == $this->getChildConfig('nickname'))
		{
			$this->Join($sChannel);
		}
		else
		{
			$aChildren = $this->getChildren();
			
			foreach($aChildren as $sChild)
			{
				$pChild = $this->getChildObject($sChild);
				
				if($pChild->aConfig['nickname'] == $sNickname)
				{
					$pChild->Output('JOIN '.$sChannel);
				}
			}
		}
	}
}
		
?>
