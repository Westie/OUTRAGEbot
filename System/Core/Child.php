<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     feb769fa604708e8e67d7f182cf9bf3b3abf098e
 *	Committed at:   Tue Jul  5 18:41:30 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


abstract class CoreChild
{
	private
		$sInternalMasterObject = null;


	/**
	 *	Used to set (and return) the Master object.
	 */
	protected final function internalMasterObject($pMaster = null)
	{
		if($this->sInternalMasterObject === null)
		{
			$this->sInternalMasterObject = $pMaster->pConfig->sInstance;
		}

		return Core::getSpecificInstance($this->sInternalMasterObject);
	}
}
