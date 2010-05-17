<?php


if(!strcmp($sCommand, "hai"))
{
	$this->Message($sChannel, "BAI");
}


if($this->isAdmin())
{
	if(!strcmp($sCommand, "reload"))
	{
		$this->getCode();
		$this->Message($sChannel, "Reloaded!");
	}
}
