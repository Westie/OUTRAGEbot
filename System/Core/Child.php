<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     715e888c1cc36aad4bc58e520cffbe92c8304e76
 *	Committed at:   Sat Jun 11 18:17:36 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreChild
{
	/**
	 *	Used to set (and return) the Master object.
	 */
	protected final function internalMasterObject($pMaster = null)
	{
		static
			$pMasterObject;

		if($pMaster === null)
		{
			return $pMasterObject;
		}

		$pMasterObject = $pMaster;
		return null;
	}
}
