<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     95304f4359b55dae9234c2c1156593d3c5fdb40d
 *	Committed at:   Thu Dec  1 23:01:51 GMT 2011
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
	protected final function internalMasterObject(CoreMaster $pMaster = null)
	{
		if($this->sInternalMasterObject === null)
		{
			$this->sInternalMasterObject = $pMaster->pConfig->sInstance;
		}

		return Core::getSpecificInstance($this->sInternalMasterObject);
	}
}
