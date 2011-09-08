<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     ebfddab76bb5fe996e439e9c2697eaa89e465874
 *	Committed at:   Thu Sep  8 15:53:04 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleCTCP
{
	private static
		$iEndTime = null,
		$sCommand = null,
		$sMessage = "";


	/**
	 *	Called when the module is loaded.
	 */
 	public static function initModule()
 	{
 		Core::introduceFunction("replyCTCP", array("ModuleCTCP", "Reply"));
 		Core::introduceFunction("replyCTCPMessage", array("ModuleCTCP", "Reply"));

		Core::introduceFunction("requestCTCPMessage", array("ModuleCTCP", "Request"));
		Core::introduceFunction("requestCTCP", array("ModuleCTCP", "Request"));
 	}


	/**
	 *	Sends a CTCP reply.
	 */
	public static function Reply($sNickname, $sMessage)
	{
		$sString = "NOTICE {$sNickname} :".chr(1).trim($sMessage).chr(1);

		return Core::getCurrentInstance()->Raw($sString, SEND_CURR);
	}


	/**
	 *	Sends a CTCP request.
	 */
	public static function Request($sNickname, $sRequest, $iTimeout = 3)
	{
		list(self::$sCommand) = explode(' ', $sRequest, 2);

		self::$iEndTime = time() + $iTimeout;
		self::$sCommand = strtoupper(self::$sCommand);

		$sString = "PRIVMSG {$sNickname} :".chr(1).trim($sRequest).chr(1);

		$pInstance = Core::getCurrentInstance();

		$pInstance->Raw($sString, SEND_CURR);

		$pSocket = $pInstance->getCurrentSocket();
		$pSocket->executeCapture(array("ModuleCTCP", "Response"));

		$sMessage = self::$sMessage;

		self::cleanVariables();
		return $sMessage;
	}


	/**
	 *	Deals with all the incoming connections.
	 */
	public static function Response($sString)
	{
		# Have we timed out?
		if(self::$iEndTime < time())
		{
			return true;
		}

		# Check if this string is a CTCP response.
		$pInstance = Core::getCurrentInstance();
		$pSocket = $pInstance->getCurrentSocket();

		$pMessage = $pInstance->internalPortkey($pSocket, $sString);

		if($pMessage === null)
		{
			return false;
		}

		if($pSocket->isSocketSlave())
		{
			CoreHandler::Unhandled($pInstance, $pMessage);
			return false;
		}

		# Is this the command we want?
		$aResponse = explode(' ', substr($pMessage->Payload, 1, -1), 2);

		if(($pMessage->Payload[0] != chr(1) || $pMessage->Numeric != "NOTICE") || ($aResponse[0] != self::$sCommand))
		{
			Core::Handler($pInstance, $pMessage);
			return false;
		}

		# Yes! It's the command we want - finally!
		# PS: SPAAAAAAAAAAAAAAAAAAACE!
		if(isset($aResponse[1]))
		{
			self::$sMessage = $aResponse[1];
		}
		else
		{
			self::$sMessage = "";
		}

		return true;
	}


	/**
	 *	Cleans all associated variables.
	 */
	public static function cleanVariables()
	{
		self::$iEndTime = null;
		self::$sCommand = null;
		self::$sMessage = "";
	}
 }
