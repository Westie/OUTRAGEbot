<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     34505731494ce4358c897884a185e6869f52bc08
 *	Committed at:   Tue Jul 26 23:19:16 BST 2011
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
