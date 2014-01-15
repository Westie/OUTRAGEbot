<?php
/**
 *	Delegations trait for OUTRAGEbot - we'll stick any global delegations
 *	in this here class.
 */


namespace OUTRAGEbot\Core\Attributes;


trait Delegations
{
	/**
	 *	Retrieves the reflector for this class.
	 */
	public function getter_reflector()
	{
		return $this->reflector = $this->reflector();
	}
}