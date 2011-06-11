<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     a53ca6c5bfdf712e6df4b62e5003c18fa157b2d7
 *	Committed at:   Sat Jun 11 22:17:10 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


abstract class CoreChild
{
	private
		$pInternalMasterObject = null;


	/**
	 *	Used to set (and return) the Master object.
	 */
	protected final function internalMasterObject($pMaster = null)
	{
		if($this->pInternalMasterObject === null)
		{
			return $this->pInternalMasterObject = $pMaster;
		}

		return $this->pInternalMasterObject;
	}
}
