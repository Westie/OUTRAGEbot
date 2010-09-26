<?php
/**
 *	Master class for OUTRAGEbot
 *
 *	This class deals with all the interaction with plugins, how the bot acts, etc.
 *	This class also contains all of the commands, etc.
 *
 *	In this version, about half of the functions have been renamed, in order
 *	to be easier to remember, for example. That means, any
 *
 *	Note: In this documentation, there are some psuedo types that are used to describe
 *	certain arguments.
 *
 *	- callback<code>
 *	$cCallback = array($this, "Function"); // Different class instance
 *	$cCallback = "callLocalFunction";      // Only available in plugins
 *	</code>It is used for binds and timers.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-BETA7 (Git commit: fdeb506d199e5c806317b594b541f78287131a8b)
 */
 

/* The real code. Woohoo */
class Master
{
	/**
	 *	@ignore
	 */
	public $sBotGroup;
	
	
	/**
	 *	@ignore
	 */
	public $pPlugins;
	
	
	/**
	 *	@ignore
	 */
	public $aHandlers = array();
	
	
	/**
	 *	@ignore
	 */
	public $aEvents = array();
	
	
	/**
	 *	@ignore
	 */
	public $MasterPresent = false;
		
	
	/**
	 *	@ignore
	 */
	public $aBotObjects = array();
	
	
	/**
	 *	@ignore
	 */
	public $aFunctions = array();
	
	
	/**
	 *	@ignore
	 */
	public $aChannelObjects = array();
	
	
	/**
	 *	@ignore
	 */
	public $pBotItter = false;
	
	
	/**
	 *	@ignore
	 */
	public $pConfig;
	
	
	/**
	 *	Contains the instance of the current bot.
	 *	@var Socket
	 */
	public $pCurrentBot;
	
	
	/**
	 *	Contains the string/message that is sent to the bot
	 *	@var string
	 */
	public $sCurrentChunk;
	
	
	/**
	 *	Contains the current (or last) accessed plugin.
	 *	@ignore
	 */
	public $sLastAccessedPlugin;
	
	
	/**
	 *	Internal: Constructor for class 'Master'
	 *
	 *	@ignore
	 */
	public function __construct($sKey, $pConfig)
	{
		$this->pConfig = $pConfig;
		$this->sBotGroup = $sKey;
		
		$this->pPlugins = new stdClass();
		$this->pModes = new stdClass();
		
		$this->pBotItter = new stdClass();
		$this->pBotItter->iIndex = 0;
		$this->pBotItter->iCount = 0;
		
		Control::$aStack[$this->sBotGroup] = array();

		echo PHP_EOL." Creating '{$this->pConfig->Network['name']}' at {$this->pConfig->Network['host']}:{$this->pConfig->Network['port']}".PHP_EOL;
		
		foreach($this->pConfig->Bots as $aOption)
		{	
			$this->_addChild($aOption['nickname'], $aOption);
		}
		
		foreach(explode(',', $this->pConfig->Network['plugins']) as $sPlugin)
		{
			$sPlugin = trim($sPlugin);
			$this->activatePlugin($sPlugin);
		}
		
		/* The uncool stuff. This does mean that yeah, you can this in the configs. */
		if(!isset($this->pConfig->Network['delimiter']))
		{
			$this->pConfig->Network['delimiter'] = "!";
		}
		
		if(!isset($this->pConfig->Network['rotation']))
		{
			$this->pConfig->Network['rotation'] = SEND_DEF;
		}
		
		if(!isset($this->pConfig->Network['quitmsg']))
		{
			$this->pConfig->Network['quitmsg'] = "OUTRAGEbot is going to bed :(";
		}
		
		if(!isset($this->pConfig->Network['version']))
		{
			$this->pConfig->Network['version'] = "OUTRAGEbot ".BOT_VERSION." (rel. ".BOT_RELDATE."); David Weston; http://outrage.typefish.co.uk";
		}
		
		foreach(explode(',', $this->pConfig->Network['owners']) as $sAddr)
		{
			$sAddr = trim($sAddr);
			$this->pConfig->Network['_owners'][] = $sAddr;
		}
	}
	
	
	/**
	 *	Internal: Destructor for class 'Master'
	 *
	 *	@ignore
	 */
	public function _onDestruct()
	{
		foreach($this->pPlugins as $sReference => $pPlugin)
		{
			call_user_func(array($this->pPlugins->$sReference, "onDestruct"));
			unset($this->pPlugins->$sReference);
		}
		
		foreach($this->aBotObjects as $iReference => $pBotObject)
		{
			$pBotObject->destructBot();
			unset($this->aBotObjects[$iReference]);
		}
		
		unset($this->pModes);
		unset($this->pConfig);
	}
	
	
	/**
	 *	Internal: Destructor for class 'Master'
	 *
	 *	@ignore
	 */
	public function __destruct()
	{
		$this->_onDestruct();
	}
	
	
	/**
	 *	Internal: Call superclass.
	 *
	 *	@ignore
	 */
	public function __call($sFunction, $aArguments)
	{
		if(isset($this->aFunctions[$sFunction]))
		{
			$cCallback = array($this->getPlugin($this->aFunctions[$sFunction][0]), $this->aFunctions[$sFunction][1]);
			return call_user_func_array($cCallback, $aArguments);
		}
		
		return null;
	}

	
	/**
	 *	Internal: Loops the bot and its slaves.
	 *
	 *	@ignore
	 */
	public function Loop()
	{		
		foreach($this->aBotObjects as $pClones)
		{
			$pClones->Input();
			
			if($pClones->isClone() == false)
			{
				$this->triggerEvent("onTick");
			}
		}
	}
	
	
	/**
	 *	Internal: The backend of creating a child. Why oh why did I make it so complicated?
	 *
	 *	@ignore
	 *	@param string $sChild Child reference.
	 *	@param array $aDetails Details of the child.
	 *	@return bool true on success.
	 */
	private function _addChild($sChild, $aDetails)
	{
		if($this->doesChildExist($sChild))
		{
			return false;
		}
		
		$aDetails['slave'] = $this->MasterPresent;
		
		if($this->MasterPresent == false)
		{
			$this->MasterPresent = true;
			$this->MasterReference = $sChild;
		}
		
		$aDetails['timewait'] = 1;
		$aDetails['loadtime'] = (time() + $aDetails['timewait']);
		
		$this->aBotObjects[] = new Socket($this, $sChild, $aDetails);
		$this->pBotItter->iCount = count($this->aBotObjects);
		return true;
	}
	
	
	/**
	 *	This function creates a child. A child is an instance of the Socket,
	 *	basically an IRC client.
	 *
	 *	@param string $sChild Child reference.
	 *	@param string $sNickname The child's nickname.
	 *	@param string $sUsername The child's username.
	 *	@param string $sRealname The child's real name.
	 *	@return bool true on success.
	 */
	public function addChild($sChild, $sNickname, $sUsername = null, $sRealname = null)
	{
		$aDetails = array
		(
			'nickname' => $sNickname,
			'username' => ($sUsername == null ? $sChild : $sUsername),
			'realname' => ($sRealname == null ? $sNickname : $sRealname),
			'altnick' => $sNickname.rand(0, 10),
		);
		
		return $this->_addChild($sChild, $aDetails);
	}
	
	
	/**
	 *	Returns a list of all the children that the bot has.
	 *
	 *	@return array Array of children's names.
	 */
	public function getChildren()
	{
		$aReturn = array();
		
		foreach($this->aBotObjects as $iReference => $oChild)
		{
			$aReturn[$iReference] = $oChild->sChild;
		}
		
		return $aReturn;
	}
	
	
	/**
	 *	Returns an object of a child from its reference.
	 *
	 *	@param string $sChild Child reference.
	 *	@return Socket Class of the socket child.
	 */
	public function getChildObject($sChild)
	{
		$aReturn = array();
		
		foreach($this->aBotObjects as $iReference => $oChild)
		{
			if($sChild == $oChild->sChild)
			{
				return $oChild;
			}
		}
		
		return null;
	}
	
	
	/**
	 *	This function renames a child by its reference. The reference is (in most cases)
	 *	the bot's original name. Look in the configuration for more details.
	 *
	 *	@param string $sChild Child reference.
	 *	@param string $sNewNick New nickname of the child.
	 */
	public function setNickname($sChild, $sNewNick)
	{
		if(($oChild = $this->getChildObject($sChild)) === null)
		{
			return false;
		}
		
		$oChild->setNickname($sNewNick);
	}
	
	
	/**
	 *	Removes a child from this group.
	 *	Please note that you cannot remove the master. That would just be pointless.
	 *
	 *	@param string $sChild Child reference.
	 *	@param string $sReason Reason for quitting channel.
	 *	@return bool true on success.
	 */
	public function removeChild($sChild, $sReason = null)
	{
		foreach($this->aBotObjects as $iReference => $oChild)
		{
			if($oChild->sChild == $sChild)
			{
				if($iReference == 0)
				{
					return false;
				}
				
				$oChild->destructBot($sReason);

				unset($this->aBotObjects[$iReference]);			
				$this->aBotObjects = array_values($this->aBotObjects);
				$this->pBotItter->iCount = count($this->aBotObjects);
				
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 *	Checks if a child exists. Note that the child name is not necessarily the 
	 *	IRC nick of the bot, but in most cases it is.
	 *
	 *	@param string $sChild Child name.
	 *	@return bool true on success.
	 */
	public function doesChildExist($sChild)
	{
		foreach($this->aBotObjects as $oChild)
		{
			if($oChild->sChild == $sChild)
			{
				return true;
			}
		}
		
		return false;
	}


	/**
	 *	This function reconnects a IRC child. This is useful in cases where IRC bots
	 *	have to physically disconnect from the network in order to work.
	 *
	 *	@param string $sChild Child reference.
	 *	@param string $sMessage Quit message.
	 */
	public function resetChild($sChild, $sMessage = "Rehash!")
	{
		if(($oChild = $this->getChildObject($sChild)) === null)
		{
			return false;
		}

		$oChild->destructBot($sMessage);
		$oChild->constructBot();
		
		return true;
	}
	
	
	/**
	 *	Returns a value from the current bot's configuration.
	 *
	 *	@param string $sKey Configuration key to lookup.
	 *	@return mixed Value that is returned.
	 */
	public function getChildConfig($sKey)
	{
		if(isset($this->pCurrentBot->aConfig[$sKey]))
		{
			return $this->pCurrentBot->aConfig[$sKey];
		}
		
		return null;
	}
	
	
	/**
	 *	Returns a value from the network configuration. This
	 *	is anything that is within [~Network].
	 *
	 *	@param string $sKey Configuration key to lookup.
	 *	@return mixed Value that is returned.
	 */
	public function getNetworkConfig($sKey)
	{
		if(isset($this->pConfig->Network[$sKey]))
		{
			return $this->pConfig->Network[$sKey];
		}
		
		return null;
	}
	
	
	/**
	 *	Sends RAW IRC Messages to the server.
	 *
	 *	There are many different ways of sending a message with this
	 *	function - this covers all outbound functions. There are three
	 *	different ways, using the definitions, a string of a child name,
	 *	or an array of children's names.
	 *
	 *	<b>as Definitions:</b>
	 *	 - SEND_MAST: Sends a message from the master child.
	 *	 - SEND_CURR: Sends a message from the current child.
	 *	 - SEND_DIST: Sends a message from each child in succession.
	 *	 - SEND_ALL: Send a message from all children at the same time.
	 *
	 *	<b>as a String:</b>
	 *	You can send a message from a child's name. For children that
	 *	are defined in the configuration, it will be their original
	 *	nickname, whilst for bots created later, it will be name you
	 *	give them.
	 *
	 *	<b>as an Array:</b>
	 *	You can send messages only from selected children. The same note
	 *	above applies.
	 *
	 *	<code>
	 *	$this->sendRaw('PRIVMSG #Westie :hai there');		                   // Use default settings.
	 *	$this->sendRaw('PRIVMSG #Westie :Everyone says hai!', SEND_ALL);           // All children.
	 *	$this->sendRaw('PRIVMSG #Westie :OUTRAGEbot says hai!', 'OUTRAGEbot');     // From the OUTRAGEbot child.
	 *	</code>
	 *
	 *	@param string $sMessage Raw IRC message you want to send.
	 *	@param mixed $mSend How to send the message (Look above).
	 */
	public function sendRaw($sMessage, $mSend = SEND_DEF)
	{
		if(is_int($mSend))
		{
			if($mSend == SEND_DEF)
			{
				$mSend = $this->pConfig->Network['rotation'];
			}

			switch($mSend)
			{
				case SEND_MAST:
				{
					$this->aBotObjects[0]->Output($sMessage);
					break;
				}
				case SEND_CURR:
				{
					$this->pCurrentBot->Output($sMessage);
					break;
				}
				case SEND_ALL:
				{
					foreach($this->aBotObjects as $pBot)
					{
						$pBot->Output($sMessage);
					}
					break;
				}
				case SEND_DIST:
				default:
				{
					$this->getNextChild()->Output($sMessage);
					break;
				}
			}
			
			return true;
		}
		elseif(is_string($mSend))
		{
			$oChild = $this->getChildObject($mSend);
			
			if($oChild != null)
			{
				$oChild->Output($sMessage);
				return true;
			}
			
			return false;
		}
		elseif(is_array($mSend))
		{
			foreach($mSend as $sSend)
			{
				$oChild = $this->getChildObject($sSend);
			
				if($oChild != null)
				{
					$oChild->Output($sMessage);
				}
			}
			
			return true;
		}
	}
	
	
	/**
	 *	Get the users username from a hostname string.
	 *
	 *	@param string $sHost The hostmask string
	 *	@return string Nickname
	 */
	public function getUsername($sHost)
	{
		$aHostmask = $this->parseHostmask($sHost);
		return $aHostmask['Username'];
	}
	
	
	/**
	 *	Get the users nickname from a hostname string.
	 *
	 *	@param string $sHost The hostmask string
	 *	@return string Nickname
	 */
	public function getNickname($sHost)
	{
		$aHostmask = $this->parseHostmask($sHost);
		return $aHostmask['Nickname'];
	}
	
	
	/**
	 *	Get the users hostname from a hostname string.
	 *
	 *	@param string $sHost The hostmask string
	 *	@return string Hostname
	 */
	public function getHostname($sHost)
	{
		$aHostmask = $this->parseHostmask($sHost);
		return $aHostmask['Hostname'];
	}
	
	
	/**
	 *	Get the hostmask info as an array.
	 *
	 *	@param string $sHost The hostmask string
	 *	@return array Array of hostmask info
	 */
	public function parseHostmask($sHost)
	{
		$bMatch = preg_match('/(.*)!(.*)@(.*)/', $sHost, $aDetails);
		
		if($bMatch)
		{
			return array
			(
				"Nickname" => $aDetails[1],
				"Username" => $aDetails[2],
				"Hostname" => $aDetails[3],
			);
		}
		else
		{
			return array
			(
				"Nickname" => "",
				"Username" => "",
				"Hostname" => "",
			);
		}
	}

	
	/**
	 *	This function gets the next child along in the queue.
	 *
	 *	@return Socket Child object.
	 */
	public function getNextChild()
	{		
		if($this->pBotItter->iIndex >= $this->pBotItter->iCount)
		{
			$this->pBotItter->iIndex = 0;
		}
		
		$pBot = $this->aBotObjects[$this->pBotItter->iIndex];
		++$this->pBotItter->iIndex;
		
		return $pBot;
	}
	
	
	/**
	 *	Internal: Recieve input from the children.
	 *
	 *	@ignore
	 */
	public function getInput(Socket $pBot, $sMessage)
	{		
		/* Deal with the useless crap. */
		$this->pCurrentBot = $pBot;
		$this->sCurrentChunk = $sMessage;
		$aRaw = explode(' ', $sMessage, 4);
		
		/* A ping check */
		if($aRaw[0] == 'PING')
		{
			$pBot->Output('PONG '.$aRaw[1]);
			return;
		}
		elseif($aRaw[1] == 'PONG')
		{
			$pBot->iNoReply = 0;
			$pBot->iHasBeenReply = true;
			
			return;
		}
		
		/* Deal with realtime scans */
		if($pBot->iUseQueue == true)
		{
			StaticLibrary::sortQueue($pBot, $aRaw, $sMessage);
			return;
		}
		
		$aChunks = StaticLibrary::sortChunks($aRaw);

		/* Real code now! */	
		if($pBot->isClone())
		{
			$this->_onRaw($aChunks);
			return;
		}
		
		/* The infamous switchboard is removed! */
		$this->scanHandlers($aChunks, $aRaw);
		$sCallback = '_on'.$aChunks[1];
		
		if(method_exists($this, $sCallback))
		{
			$this->$sCallback($aChunks);
		}
		else
		{
			$this->_onRaw($aChunks);
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onConnect()
	{
		$this->triggerEvent("onConnect");
		
		foreach((array) $this->getNetworkConfig('perform') as $sRaw)
		{
			$this->sendRaw($sRaw);
		}
		
		foreach((array) $this->getChildConfig('perform') as $sRaw)
		{
			$this->sendRaw($sRaw);
		}
		
		foreach((array) explode(',', $this->pConfig->Network['channels']) as $sChannel)
		{
			$this->sendRaw("JOIN {$sChannel}");
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onJoin($aChunks)
	{
		$sNickname = $this->getNickname($aChunks[0]);
		$pChannel = $this->getChannel($aChunks[2]);
			
		$this->triggerEvent("onJoin", $sNickname, $pChannel);
		$pChannel->addUserToChannel($sNickname);
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onKick($aChunks)
	{
		$aChunks[3] = explode(' ', $aChunks[3], 2);
		$aChunks[3][1] = trim(isset($aChunks[3][1]) ? substr($aChunks[3][1], 1) : "");
		
		$pChannel = $this->getChannel($aChunks[2]);
		
		$this->triggerEvent("onKick", $this->getNickname($aChunks[0]), $aChunks[3][0], $pChannel, $aChunks[3][1]);
		$pChannel->removeUserFromChannel($aChunks[3][0]);
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onPart($aChunks)
	{
		$sNickname = $this->getNickname($aChunks[0]);
		$pChannel = $this->getChannel($aChunks[2]);
		
		$this->triggerEvent("onPart", $sNickname, $pChannel, $aChunks[3]);
		$pChannel->removeUserFromChannel($sNickname);
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onQuit($aChunks)
	{
		$sNickname = $this->getNickname($aChunks[0]);
		
		$this->triggerEvent("onQuit", $sNickname, $aChunks[3]);
		
		foreach($this->aChannelObjects as $pChannel)
		{
			$pChannel->removeUserFromChannel($sNickname);
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onMode($aChunks)
	{
		$this->triggerEvent("onMode", $aChunks[2], $aChunks[3]);
		$pChannel = $this->getChannel($aChunks[2]);
			
		foreach($this->parseModes($aChunks[3]) as $aMode)
		{
			if(!preg_match('/[qaohv]/', $aMode['MODE']))
			{
				continue;
			}
			
			$sMode = $aMode['MODE'];
			$sNickname = $aMode['PARAM'];
			
			$pChannel->modifyUserInChannel($sNickname, $aMode['ACTION'], $sMode);
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onNick($aChunks)
	{
		$sNickname = $this->getNickname($aChunks[0]);
		
		if(!strcmp($sNickname, $this->pCurrentBot->aConfig['nickname']))
		{
			/* God, this IRCnet sucks. :( */
			$this->pCurrentBot->aConfig['nickname'] = $sNickname;
		}
		
		$this->triggerEvent("onNick", $sNickname, $aChunks[2]);
		
		foreach($this->aChannelObjects as $pChannel)
		{
			$pChannel->renameUserInChannel($aChunks[2], $sNickname);
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onNotice($aChunks)
	{
		$this->triggerEvent("onNotice", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3]);
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onCTCP($aChunks)
	{
		$aChunks[3] = explode(' ', str_replace("\001", "", $aChunks[3]), 2);
		$this->triggerEvent("onCTCP", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3][0], (isset($aChunks[3][1]) ? $aChunks[3][1] : ""));
		
		$sNickname = $this->getNickname($aChunks[0]);

		switch(strtoupper($aChunks[3][0]))
		{
			case "VERSION":
			{
				$this->ctcpReply($sNickname, "VERSION {$this->pConfig->Network['version']}");
				break;
			}
			case "TIME":
			{
				$this->ctcpReply($sNickname, "TIME ".date("d/m/Y H:i:s", time()));
				break;
			}
			case "PING":
			{
				$this->ctcpReply($sNickname, "PING {$aChunks[3][1]}");
				break;
			}
			case 'UPTIME':
			{
				$aSince = StaticLibrary::dateSince($this->pCurrentBot->aStatistics['StartTime']);
				
				$sString = "{$aSince['WEEKS']} weeks, {$aSince['DAYS']} days, {$aSince['HOURS']} hours, ".
				"{$aSince['MINUTES']} minutes, {$aSince['SECONDS']} seconds.";
				
				$this->ctcpReply($sNickname, "UPTIME ".$sString);
				break;
			}
			case 'START':
			{
				$this->ctcpReply($sNickname, "START ".date("d/m/Y H:i:s", $this->pCurrentBot->aStatistics['StartTime']));
				break;
			}
			case 'SOURCE':
			{
				$this->ctcpReply($sNickname, "SOURCE http://outrage.typefish.co.uk");
				break;
			}
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onPrivmsg($aChunks)
	{
		if($aChunks[3][0] == Format::CTCP)
		{
			$this->_onCTCP($aChunks);
			return;
		}
		
		switch($aChunks[2][0])
		{
			case '&':
			case '#':
			{
				if($aChunks[3][0] == $this->pConfig->Network['delimiter'])
				{
					$aCommand = explode(' ', trim($aChunks[3]), 2);
					$this->triggerEvent("onCommand", $this->getNickname($aChunks[0]), $this->getChannel($aChunks[2]),
						substr($aCommand[0], 1), (isset($aCommand[1]) ? $aCommand[1] : ""));
						
					return;
				}
				
				$this->triggerEvent("onMessage", $this->getNickname($aChunks[0]), $this->getChannel($aChunks[2]), $aChunks[3]);
				return;
			}
			default:
			{
				$this->triggerEvent("onPrivMessage", $this->getNickname($aChunks[0]), $aChunks[3]);
				return;
			}
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onTopic($aChunks)
	{
		$sNickname = $this->getNickname($aChunks[0]);
		$pChannel = $this->getChannel($aChunks[2]);
		
		$pChannel->aTopicInformation['String'] = $aChunks[3];
		$pChannel->aTopicInformation['Time'] = time();
		$pChannel->aTopicInformation['SetBy'] = $sNickname;
	
		$this->triggerEvent("onTopic", $sNickname, $pChannel, $aChunks[3]);
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onError($aChunks)
	{
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _on353($aChunks)
	{
		$aData = explode(" ", $aChunks[3], 3);
		$aData[2] = substr($aData[2], 1);
		
		$aUsers = explode(" ", $aData[2]);
		$sChannel = strtolower($aData[1]);
		
		$pChannel = $this->getChannel($sChannel);
		
		/* Great, we now parse the users... */
		foreach($aUsers as $sUser)
		{
			$sUser = trim($sUser);
			$aModes = array();
			
			preg_match("/[+%@&~]/", $sUser, $aModes);
			
			$sModeLetter = implode("", $aModes);
			$sModeLetter = StaticLibrary::modeCharToLetter($sModeLetter);
			
			$sUser = preg_replace("/[+%@&~]/", "", $sUser);
			$pChannel->addUserToChannel($sUser, $sModeLetter);
		}
	}
	
	
	/**
	 *	Internal: (no explanation)
	 *	@ignore
	 */
	private function _onRaw($aChunks)
	{
		switch($aChunks[1])
		{
			/* The NAMEX protocol */
			case "005":
			{
				$this->pCurrentBot->Output("PROTOCTL NAMESX");
				return;
			}
			
			/* When the bot connects */
			case "001":
			{
				$this->_onConnect();
				return;
			}
			
			/* Nick already in use. */
			case "433":
			{
				if($this->getChildConfig('altnick') != null)
				{
					$sNewNick = $this->getChildConfig('altnick');
				}
				else
				{
					$sNewNick = $this->getChildConfig('nickname').rand(10, 99);
				}
				
				$this->pCurrentBot->setNickname($sNewNick);
				return;
			}
			
			/* Topic information */
			case "332":
			{
				$aData = explode(' :', $aChunks[3], 2);
			
				$this->getChannel($aData[0])->aTopicInformation['String'] = $aData[1];
				
				return;
			}
			
			case "333":
			{
				$aData = explode(' ', $aChunks[3], 3);		
				
				$this->getChannel($aData[0])->aTopicInformation['Time'] = $aData[2];
				$this->getChannel($aData[0])->aTopicInformation['SetBy'] = $aData[1];
				
				return;
			}
		}
		
		/* Other stuff */
		if(isset($aChunks[3][0]))
		{
			if($aChunks[3][0] == Format::CTCP)
			{
				$this->_onCTCP($aChunks);
				return;
			}
		}
	}
	
	
	/**
	 *	Returns information about the user in an OOP format. This only
	 *	currently retrieves channel information. For this, the user must
	 *	be in a channel with the bot.
	 *
	 *	@param string $sNickname Nickname you want to get data for.
	 *	@return User Class with information.
	 */
	public function getUser($sNickname)
	{
		return new User($this, $sNickname);
	}
	
	
	/**
	 *	Returns a stdClass instance of the information about a channel.
	 *	Will only work if the bot is in the channel, otherwise a blank
	 *	object will be returned.
	 *
	 *	@param string $sChannel Channel name
	 *	@return Channel Channel information
	 */
	public function getChannel($sChannel)
	{
		$sChannel = strtolower($sChannel);
		
		if(!isset($this->aChannelObjects[$sChannel]))
		{
			$this->aChannelObjects[$sChannel] = new Channel($this, $sChannel);
		}
		
		return $this->aChannelObjects[$sChannel];
	}
	
	
	/**
	 *	Returns the channels that the bot is in.
	 *
	 *	@return array Array of channels
	 */
	public function getChannelList()
	{
		return array_keys($this->aChannelObjects);
	}
	
	
	/**
	 *	Returns the amount of users in the channel.
	 *
	 *	@param string $sChannel Channel name
	 *	@return integer Amount of users in channel
	 */
	public function getChannelUserCount($sChannel)
	{
		$pChannel = $this->getChannel($sChannel);
		return count($pChannel->aUsers);
	}
	
	
	/**
	 *	Syncs the internal channel lists. Useful if there happens
	 *	to be a mistake.
	 *
	 *	@param string $sChannel Channel name
	 */
	public function syncChannelLists($sChannel)
	{
		$this->Raw("NAMES {$sChannel}");
	}
	
	
	/**
	 *	Returns the active bans in the channel requested.
	 *
	 *	@param string $sChannel Channel name
	 *	@return array List of bans
	 */
	public function getChannelBanList($sChannel)
	{
		$aBans = $this->getRequest("MODE {$sChannel} +b", '367', '368');
		$aBanList = array();
		
		foreach($aBans as $sBan)
		{
			$aBan = explode(' ', $sBan, 7);
			
			$aBanList[] = array
			(
				"Hostmask" => trim($aBan[4]),
				"AddedBy" => trim($aBan[5]),
				"Time" => trim($aBan[6]),
			);
		}
		
		return $aBanList;
	}
	
	
	/**
	 *	Returns the active invite list in the channel requested.
	 *
	 *	@param string $sChannel Channel name
	 *	@return array List of exceptions
	 */
	public function getChannelInviteList($sChannel)
	{
		$aInvites = $this->getRequest("MODE {$sChannel} +I", '346', '347');
		$aInviteList = array();
		
		foreach($aInvites as $sInvite)
		{
			$aInvite = explode(' ', $sInvite, 7);
			
			$aInviteList[] = array
			(
				"Hostmask" => trim($aInvite[4]),
				"AddedBy" => trim($aInvite[5]),
				"Time" => trim($aInvite[6]),
			);
		}
		
		return $aInviteList;
	}
	
	
	/**
	 *	Returns the active exceptions in the channel requested.
	 *
	 *	@param string $sChannel Channel name
	 *	@return array List of exceptions
	 */
	public function getChannelExceptList($sChannel)
	{
		$aExcepts = $this->getRequest("MODE {$sChannel} +e", '348', '349');
		$aExceptList = array();
		
		foreach($aExcepts as $sExcept)
		{
			$aExcept = explode(' ', $sExcept, 7);
			
			$aExceptList[] = array
			(
				"Hostmask" => trim($aExcept[4]),
				"AddedBy" => trim($aExcept[5]),
				"Time" => trim($aExcept[6]),
			);
		}
		
		return $aExceptList;
	}
	
	
	/**
	 *	Returns the channel topic in the channel requested.
	 *
	 *	@param string $sChannel Channel name
	 *	@return array Topic information
	 */
	public function getChannelTopic($sChannel)
	{
		return $this->getChannel($sChannel)->aTopicInformation;
	}
	
	
	/**
	 *	Checks if that user is actually in that channel.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserInChannel($sChannel, $sUser)
	{
		return $this->getChannel($sChannel)->isUserInChannel($sUser);
	}
	
	
	/**
	 *	Checks if the current child is in the channel.
	 *
	 *	@param string $sChannel Channel to check.
	 *	@return bool 'true' on success.
	 */
	public function isChildInChannel($sChannel)
	{
		return $this->getChannel($sChannel)->isUserInChannel($this->getChildConfig('nickname'));
	}
	
	
	/**
	 *	Checks if that user has voice in that channel. Voicers have the
	 *	mode ' + '.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserVoice($sChannel, $sUser)
	{
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->aUsers[$sUser]))
		{
			return false;
		}
		
		return preg_match('/[qaohv]/', $pChannel->aUsers[$sUser]);
	}
	
	
	/**
	 *	Checks if that user has half-op in that channel. Half operators
	 *	have the mode ' % ', and may not be available on all networks.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserHalfOp($sChannel, $sUser)
	{
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->aUsers[$sUser]))
		{
			return false;
		}
		
		return preg_match('/[qaoh]/', $pChannel->aUsers[$sUser]);
	}
	
	
	/**
	 *	Checks if that user has operator in that channel. Operators have
	 *	the mode ' @ '.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserOper($sChannel, $sUser)
	{
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->aUsers[$sUser]))
		{
			return false;
		}
		
		return preg_match('/[qao]/', $pChannel->aUsers[$sUser]);
	}
	
	
	/**
	 *	Checks if that user has admin in that channel. Admins have the
	 *	mode ' & ', and may not be available on all networks.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserAdmin($sChannel, $sUser)
	{
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->aUsers[$sUser]))
		{
			return false;
		}
		
		return preg_match('/[qa]/', $pChannel->aUsers[$sUser]);
	}
	
	
	/**
	 *	Checks if that user has owner in that channel. Owners have the
	 *	mode ' ~ ', and may not be available on all networks.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserOwner($sChannel, $sUser)
	{	
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->aUsers[$sUser]))
		{
			return false;
		}
		
		return preg_match('/[q]/', $pChannel->aUsers[$sUser]);
	}
		
	
	/**
	 *	Check if the current, active IRC user is a bot admin.
	 *
	 *	@return bool 'true' on success.
	 */
	public function isAdmin()
	{
		$aChunks = explode(' ', $this->sCurrentChunk, 4);
		$sHostname = $this->getHostname($aChunks[0]);
		
		return (in_array($sHostname, $this->pConfig->Network['_owners']) !== false);
	}
	
	
	/**
	 *	Checks if either the current instance, or a specific instance is
	 *	actually a child. Children differ from master bots in one variable.
	 *
	 *	@param string $sChild Child reference.
	 *	@return bool true on success, null if child is non-existant.
	 */
	public function isChild($sChild = "")
	{
		$oChild = ($sChild == "" ? $this->pCurrentBot : $this->getChildObject($sChild));
		
		if($oChild == null)
		{
			return null;
		}
		
		return $oChild->isClone();
	}


	/**
	 *	Invokes an event/callback from plugins.
	 *
	 *	@param string $sEvent Event to invoke
	 *	@param mixed $... Arguments to pass to event.
	 *	@return void
	 */
	public function triggerEvent($sEvent)
	{
		if(func_num_args() == 0)
		{
			return false;
		}
		
		$aArguments = func_get_args();
		array_shift($aArguments);
		
		foreach($this->pPlugins as &$oPlugin)
		{
			$this->sLastAccessedPlugin = $this->getPluginName($oPlugin);
			$rResult = call_user_func_array(array($oPlugin, $sEvent), $aArguments);
		}
		
		if(!isset($this->aEvents[$sEvent]) || !count($this->aEvents[$sEvent]))
		{
			return;
		}
		
		array_unshift($aArguments, $this);
		
		foreach($this->aEvents[$sEvent] as $cCallback)
		{
			call_user_func_array($cCallback, $aArguments);
		}
	}
	
	
	/**
	 *	Invokes an event from one plugin only.
	 *
	 *	@param string $sPlugin Plugin name
	 *	@param string $sEvent Event to invoke
	 *	@param mixed $... Arguments to pass to event.
	 */
	public function triggerSingleEvent($sPlugin, $sEvent)
	{
		$aArguments = func_get_args();
		
		array_shift($aArguments);
		array_shift($aArguments);
		
		return $this->triggerSingleEventArray($sPlugin, $sEvent, $aArguments);
	}
	
	
	/**
	 *	Invokes an event from one plugin only, but arguments are an array
	 *
	 *	@param string $sPlugin Plugin name
	 *	@param string $sEvent Event to invoke
	 *	@param array $aArguments Arguments to pass to event.
	 */
	public function triggerSingleEventArray($sPlugin, $sEvent, $aArguments, $iTimerCalled = false)
	{
		if(func_num_args() == 0)
		{
			return false;
		}
		
		$cCallback = array($this->getPlugin($sPlugin), $sEvent);
		
		if(!is_callable($cCallback))
		{
			if($iTimerCalled === true)
			{
				$this->removeTimer($this->getActiveTimer());
			}
			
			return;
		}

		$this->sLastAccessedPlugin = $sPlugin;
		return call_user_func_array($cCallback, $aArguments);
	}
	
	
	/**
	 *	Adds formatting to the text.
	 *
	 *	@param string $sText Text to format
	 *	@return string Formatted text
	 */
	public function Format($sInputText)
	{
		return Format($sInputText);
	}
	
	
	/**
	 *	Strips the text of formatting.
	 *
	 *	@param string $sText Text to strip
	 *	@return string Stripped text
	 */
	public function stripFormat($sText) 
	{
		return preg_replace("/[\002\017\001\026\001\037]/", "", $sText);
	}
	
	
	/**
	 *	Strips the text of colours.
	 *
	 *	@param string $sText Text to strip
	 *	@return string Stripped text
	 */
	public function stripColour($sText)
	{
		return preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $sText);
	}
	
	
	/**
	 *	Strips the text of formatting and colours.
	 *
	 *	@param string $sText Text to strip
	 *	@return string Stripped text
	 */
	public function stripAll($sText)
	{
		return preg_replace("/[\002\017\001\026\001\037]/", "", 
		preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $sText));
	}
	
	
	/**
	 *	Sends a message to the specified channel.
	 *
	 *	@param string $sChannel Channel name or nickname
	 *	@param string $sMessage Message to send
	 *	@param integer $mSend Method to send messages (see sendRaw() for details)
	 *	@see Master::sendRaw()
	 */
	public function Message($sChannel, $sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :{$sMessage}", $mSend);
	}
	
	
	/**
	 *	Sends an action to the specified channel.
	 *
	 *	@param string $sChannel Channel name
	 *	@param string $sMessage Message to send
	 *	@param integer $mSend Method to send messages (see sendRaw() for details)
	 *	@see Master::sendRaw()
	 */
	public function Action($sChannel, $sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :".chr(1)."ACTION {$sMessage}".chr(1), $mSend);
	}
	
	
	/**
	 *	Alternative: Sends a raw IRC message.
	 *
	 *	@ignore
	 */
	public function Raw($sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw($sMessage, $mSend);
	}
	
	
	/**
	 *	Sends a notice to the specified channel.
	 *
	 *	@param string $sNickname Nickname
	 *	@param string $sMessage Message to send
	 *	@param integer $mSend Method to send messages (see sendRaw() for details)
	 *	@see Master::sendRaw()
	 */
	public function Notice($sNickname, $sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw("NOTICE {$sNickname} :{$sMessage}", $mSend);
	}
	
	
	/**
	 *	Alternative: Sends a message to the specified channel.
	 *
	 *	@ignore
	 */
	public function sendMessage($sChannel, $sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :{$sMessage}", $mSend);
	}
	
	
	/**
	 *	Alternative: Sends an action to the specified channel.
	 *
	 *	@ignore
	 */
	public function sendAction($sChannel, $sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :".chr(1)."ACTION {$sMessage}".chr(1), $mSend);
	}
	
	
	/**
	 *	Alternative: Sends a notice to the specified channel.
	 *
	 *	@ignore
	 */
	public function sendNotice($sNickname, $sMessage, $mSend = SEND_DEF)
	{
		return $this->sendRaw("NOTICE {$sNickname} :{$sMessage}", $mSend);
	}
	
	
	/**
	 *	Sends a CTCP reply.
	 *
	 *	<code>$this->ctcpReply('Westie', 'COMMAND something here');</code>
	 *
	 *	@param string $sNickname Nickname
	 *	@param string $sMessage CTCP reply
	 *	@see Master::sendRaw()
	 */
	public function ctcpReply($sNickname, $sMessage)
	{
		return $this->sendRaw("NOTICE {$sNickname} :".chr(1).trim($sMessage).chr(1), SEND_CURR);
	}
	
	
	/**
	 *	Sends a CTCP request.
	 *
	 *	<code>$this->ctcpRequest('deLUX', 'VERSION');</code>
	 *
	 *	@param string $sNickname Nickname
	 *	@param string $sRequest CTCP request
	 *	@see Master::sendRaw()
	 */
	public function ctcpRequest($sNickname, $sRequest)
	{
		// return $this->sendRaw("PRIVMSG {$sNickname} :".chr(1).trim($sRequest).chr(1), $mSend);
		// $this->getRequest("PRIVMSG {$sNickname} :".chr(1).trim($sRequest).chr(1), "NOTICE", "", 4, 2);
	}


	/**
	 *	Creates a timer, note that arguments to be passed to $cCallback to after $iRepeat.
	 *
	 *	<code>
	 *	$sKey = $this->addTimer(array($this, 'Message'), '0.5000', '10', '#OUTRAGEbot', 'Test Message');
	 *	$sKey = $this->addTimer('sampleTimer', '10', '-1');
	 *	</code>
	 *
	 *	@param callback $cCallback Timer callback 
	 *	@param float $fInterval <b>Seconds</b> (decimals can be used) between timer calls.
	 *	@param integer $iRepeat How many times the timer should call before it is destroyed. -1 implies infinite.
	 *	@param mixed $... Arguments to pass to timer.
	 *	@return string Timer reference ID.
	 */
	public function addTimer($cCallback, $fInterval, $iRepeat)
	{
		$aArguments = func_get_args();
		array_shift($aArguments);
		array_shift($aArguments);
		array_shift($aArguments);
		
		if(is_array($cCallback))
		{
			if(!($cCallback[0] instanceof Master))
			{
				$aArguments = array($this->getPluginName($cCallback[0]), $cCallback[1], $aArguments, true);
				$cCallback = array($this, "triggerSingleEventArray");
			
				return Timers::Create($cCallback, $fInterval, $iRepeat, $aArguments); 
			}
		}
		elseif(is_string($cCallback))
		{	
			$aArguments = array($this->sLastAccessedPlugin, $cCallback, $aArguments, true);
			$cCallback = array($this, "triggerSingleEventArray");
			
			return Timers::Create($cCallback, $fInterval, $iRepeat, $aArguments); 
		}
		
		return Timers::Create($cCallback, $fInterval, $iRepeat, $aArguments); 
	}

		
	/**
	 *	Gets the information of a timer from its reference ID.
	 *
	 *	@param string $sKey Timer reference ID.
	 *	@return array Array of timer information.
	 */
	public function getTimer($sKey)
	{
		return Timers::Get($sKey);
	}
	
	
	/**
	 *	Gets the active timer ID.
	 *
	 *	@return string Timer ID
	 */
	public function getActiveTimer()
	{
		return Timers::$sCurrentTimer;
	}
	
	/**
	 *	Removes a timer from memory.
	 *
	 *	@param string $sKey Timer reference ID.
	 *	@return bool 'true' on success.
	 */
	public function removeTimer($sKey)
	{
		return Timers::Delete($sKey);
	}
	
	
	/**
	 *	Loads a plugin from the plugin directory.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function activatePlugin($sPlugin)
	{
		if(array_key_exists($sPlugin, $this->pPlugins))
		{
			return false;
		}

		$sIdentifier = StaticLibrary::getPluginIdentifier($sPlugin);
		
		if($sIdentifier == false)
		{
			return false;
		}
		
		$this->pPlugins->$sPlugin = new $sIdentifier($this, array($sPlugin, $sIdentifier));
		echo "* Plugin ".$sPlugin." has been activated.".PHP_EOL;
		
		return true;
	}
	

	/**
	 *	Gets the instance of the plugin if it exists.
	 *
	 *	@param string $sPlugin Plugin name
	 *	@return Plugin Object of the plugin.
	 */
	public function getPlugin($sPlugin)
	{
		if(isset($this->pPlugins->$sPlugin))
		{
			return $this->pPlugins->$sPlugin;
		}

		return null;
	}
	
	
	/**
	 *	Get the name of the plugin from the instance
	 *
	 *	@param class $pInstance Instance of plugin
	 *	@return string Plugin name, or if not Plugin class, the object.
	 */
	public function getPluginName($pInstance)
	{
		if(method_exists($pInstance, "__getName"))
		{
			return $pInstance->__getName();
		}
		
		return $pInstance;
	}


	/**
	 *	Unloads an active plugin from memory.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function deactivatePlugin($sPlugin)
	{
		if(isset($this->pPlugins->$sPlugin))
		{
			foreach($this->pPlugins->$sPlugin as $sKey => $mVar)
			{
				unset($this->pPlugins->$sPlugin->$sKey);
			}
			
			Timers::CheckCall();
			$this->checkHandlers($sPlugin);
			$this->checkFunctions($sPlugin);
			
			unset($this->pPlugins->$sPlugin);
			
			echo "* Plugin ".$sPlugin." has been deactivated.".PHP_EOL;
			return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Unloads and reloads a plugin.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function reactivatePlugin($sPlugin)
	{
		$this->deactivatePlugin($sPlugin);
		return $this->activatePlugin($sPlugin);
	}
	
	
	/**
	 *	Check if a plugin is loaded into memory.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function isPluginActivated($sPlugin)
	{
		return isset($this->pPlugins->$sPlugin);
	}
	
	
	/**
	 *	Introduces a function handler.
	 *
	 *	@param string $sFunction Function name
	 *	@param callback $cCallback Callback
	 *	@return boolean True on success
	 */
	public function introduceFunction($sFunction, $cCallback)
	{
		if(isset($this->aFunctions[$sFunction]))
		{
			return false;
		}
		
		if(is_array($cCallback))
		{
			$this->aFunctions[$sFunction] = array($this->getPluginName($cCallback[0]), $cCallback[1]);
		}
		else
		{
			$this->aFunctions[$sFunction] = array($this->sLastAccessedPlugin, $cCallback);
		}
		
		return true;
	}
	
	
	/**
	 *	Checks if a function handler exists.
	 *
	 *	@param string $sFunction Function name
	 *	@return boolean True on success
	 */
	public function isFunction($sFunction)
	{
		return isset($this->aFunctions[$sFunction]);
	}
	
	
	/**
	 *	Remove a function handler.
	 *
	 *	@param string $sFunction Function name
	 *	@return void
	 */
	public function removeFunction($sFunction)
	{
		unset($this->aFunctions[$sFunction]);
	}
	
	
	/**
	 *	Checks for inactive function handlers.
	 *	@ignore
	 */
	public function checkFunctions($sPlugin)
	{
		foreach($this->aFunctions as $sKey => $cCallback)
		{
			if($sPlugin == $cCallback[0])
			{
				unset($this->aFunctions[$sKey]);
			}
		}
	}
	
	
	/**
	 *	Creates an event handler, designed for lambda functions.
	 *
	 *	@param string $sEvent Event name
	 *	@param callback $cCallback Callback to event handler.
	 *	@return string Event resource ID.
	 */
	public function addEventHandler($sEvent, $cCallback)
	{
		$sHandle = substr(sha1(time()."-".uniqid()), 2, 10);
		
		$this->aEvents[$sEvent][$sHandle] = $cCallback;
		return $sHandle;
	}
	
	
	/**
	 *	Removes the event hander.
	 *
	 *	@param string $sHandlerID Event handler ID
	 */
	public function removeEventHandler($sHandlerID)
	{
		foreach($this->aEvents as $sEvent => $aEvents)
		{
			foreach(array_keys($aEvents) as $sHandler)
			{
				if($sHandlerID == $sHandler)
				{
					unset($this->aEvents[$sEvent][$sHandler]);
				}
			}
		}
	}
	
	
	
	/**
	 *	Create a command or event handler for IRC numerics/commands.
	 *
	 *	@param string $sInput either: IRC command/numeric name, or: 'COMMAND' for a text-based channel command.
	 *	@param callback $cCallback Callback to bind handler.
	 *	@param array $aFormat Array of arguments to pass to the bind handler.
	 *	@return string Bind resource ID.
	 */
	public function addHandler($sInput, $cCallback, $aFormat = array())
	{
		if(is_array($cCallback))
		{
			$cCallback[0] = $this->getPluginName($cCallback[0]);
		}
		else
		{
			$cCallback = array($this->sLastAccessedPlugin, $cCallback);
		}
		
		$sHandle = substr(sha1(time()."-".uniqid()), 2, 10);
		
		$this->aHandlers[$sHandle] = array
		(
			"INPUT" => strtoupper($sInput),
			"CALLBACK" => $cCallback,
			"FORMAT" => $aFormat,
		);
		
		return $sHandle;
	}
	
	   
	/**
	 *	Gets the information of a bind from its reference ID.
	 *
	 *	@param string $sKey Bind reference ID.
	 *	@return array Array of bind information.
	 */
	public function getHandler($sKey)
	{
		if(isset($this->aHandlers[$sKey]))
		{
			return $this->aHandlers[$sKey];
		}

		return null;
	}

	
	/**
	 *	Delete a reference to a bind handler.
	 *
	 *	@param string $sKey
	 *	@return bool 'true' on success.
	 */
	public function removeHandler($sKey)
	{
		if(isset($this->aHandlers[$sKey]))
		{
			unset($this->aHandlers[$sKey]);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Internal: Scans through the bind handlers.
	 *
	 *	@ignore
	 */
	public function scanHandlers(&$aChunks, &$aRaw)
	{
		$aChunks[1] = strtoupper($aChunks[1]);
	
		if(!isset($aChunks[1])) return;
		
		foreach($this->aHandlers as $aSection)
		{			
			if($aChunks[1] == 'PRIVMSG')
			{
				if($aSection['INPUT'] == 'COMMAND')
				{
					$aCommand = explode(' ', $aChunks[3], 2);

					if($aCommand[0] == $this->pConfig->Network['delimiter'].$aSection['FORMAT'])
					{
						$this->triggerSingleEvent($aSection['CALLBACK'][0], $aSection['CALLBACK'][1], $this->getNickname($aChunks[0]), $aChunks[2], (isset($aCommand[1]) ? $aCommand[1] : ""));
					}
					
					continue;
				}
			}
			
			if($aSection['INPUT'] != $aChunks[1])
			{
				continue;
			}
			
			$aArguments = array();
			
			if($aSection['FORMAT'] === true)
			{
				$aArguments = implode(' ', $aChunks);
			}
			else
			{
				foreach($aSection['FORMAT'] as $mFormat)
				{
					$aArguments[] = (is_integer($mFormat) ? $aChunks[$mFormat] : $mFormat);
				}
			}
			
			$this->triggerSingleEventArray($aSection['CALLBACK'][0], $aSection['CALLBACK'][1], $aArguments);
		}
	}
	
	
	/**
	 *	Internal:  This function loops through all current handlers,
	 *	and if they are not callable (plugin instance is removed, eg.),
	 *	then the handler is removed.
	 *
	 *	@ignore
	 */
	public function checkHandlers($sPlugin)
	{
		foreach($this->aHandlers as $sKey => $aHandle)
		{
			if($aHandle['CALLBACK'][0] == $sPlugin)
			{
				unset($this->aHandlers[$sKey]);
			}
		}
	}
	
	
	/**
	 *	Request information realtime.
	 *
	 *	The data that are you requesting (for instance, what is in $mSearch) will not be parsed by the bot.
	 *	This essentially means it is the job of the code using that request to deal with parsing it properly.
	 *
	 *	@param string $sRequest Message to send to the server.
	 *	@param mixed $mSearch IRC numerics to cache.
	 *	@param mixed $mEndNumeric IRC numerics that signify end of stream
	 *	@param mixed $iTimeout Seconds to timeout
	 *	@param integer $iSleep <i>Seconds</i> to sleep before getting input.
	 *	@return array The response matched to the data in $mSearch.
	 */
	public function getRequest($sRequest, $mSearch, $mEndNumeric, $iTimeout = 4, $iSleep = 0.3)
	{
		$this->pCurrentBot->iUseQueue = true;
		$this->pCurrentBot->aRequestOutput = array();
		
		$this->pCurrentBot->aRequestConfig = array
		(
			"SEARCH" => (array) $mSearch,
			"ENDNUM" => (array) $mEndNumeric,
			"TIMEOUT" => (time() + $iSleep + $iTimeout),
		);
		
		$this->pCurrentBot->Output($sRequest);
		usleep($iSleep * 1000000);
		
		$this->pCurrentBot->Input();
		$this->pCurrentBot->iUseQueue = false;
		$aReturn = $this->pCurrentBot->aRequestOutput;
		$this->pCurrentBot->aRequestOutput = array();
		
		return $aReturn;
	}	
	
	/**
	 *	Invites a user to a channel.
	 *
	 *	@param string $sChannel Channel name.
	 *	@param string $sNickname Nickname of the person to invite
	 */
	public function Invite($sChannel, $sNickname)
	{
		return $this->sendRaw("INVITE {$sNickname} {$sChannel}");
	}
	
	
	/**
	 *	Kicks a user from a channel.
	 *
	 *	@param string $sChannel Channel name.
	 *	@param string $sNickname Nickname of the person to kick.
	 *	@param string $sReason Reason of the kick.
	 */
	public function Kick($sChannel, $sNickname, $sReason = "Kick")
	{
		return $this->sendRaw("KICK {$sChannel} :{$sReason}");
	}
	
	
	/**
	 *	Allows the bot to join a channel.
	 *
	 *	@param string $sChannel Channel name (IRC format applies!).
	 *	@param mixed $mSend Output flags
	 */
	public function Join($sChannel, $mSend = SEND_DEF)
	{
		return $this->sendRaw("JOIN {$sChannel}", $mSend);
	}
	
	
	/**
	 *	Allows the bot to part a channel.
	 *
	 *	@param string $sChannel Channel name.
	 *	@param string $sReason Reason for leaving
	 *	@param mixed $mSend Method for sending messages
	 */
	public function Part($sChannel, $sReason = null, $mSend = SEND_DEF)
	{
		return $this->sendRaw("PART {$sChannel}".($sReason == null ? "" : " :{$sReason}"));
	}
	
	
	/**
	 *	Set the modes on a channel.
	 *
	 *	@param string $sChannel Channel name
	 *	@param string $sMode Mode to be set
	 */
	public function Mode($sChannel, $sMode)
	{
		return $this->sendRaw('MODE '.$sChannel.' '.$sMode);
	}
	
	
	/**
	 *	Parses a mode string into a usable array.
	 *
	 *	@param $sModes Mode that has just been set.
	 *	@return array Array of modes.
	 */
	public function parseModes($sMode)
	{
		$iAction = 0;
		$iIndex = 0;
		$aModes = explode(' ', $sMode);
		$iModes = strlen($aModes[0]);
		$aReturn = array();

		for($iCount = 0; $iCount < $iModes; ++$iCount)
		{
			if($aModes[0][$iCount] == '+' || $aModes[0][$iCount] == '-')
			{
				$iAction = $aModes[0][$iCount];
				continue;
			}
			
			if(isset($aModes[++$iIndex]))
			{
				$aReturn[] = array
				(
					"ACTION" => $iAction,
					"MODE" => $aModes[0][$iCount],
					"PARAM" => $aModes[$iIndex],
				);
			}
		}
		
		return $aReturn;
	}
	
	
	/**
	 *	Returns current WHOIS data about a user into an array.
	 *
	 *	@param string $sNickname Nickname of the user.
	 *	@param boolean $bKeepModes Keep the user perms next to the channel name
	 *	@param integer $iDelay Microseconds to wait before fetching input.
	 *	@return array Array of modes.
	 */
	public function getWhois($sNickname, $bKeepModes = false)
	{
		$aMatches = $this->getRequest("WHOIS {$sNickname}", array(301, 310, 311, 312, 313, 319), '318', 4, 1);
		
		$aReturn = array
		(
			'Channels' => array(),
			'Details' => array(),
			'Connection' => array(),
			
			'Away' => '',
			
			'IRCOp' => false,
		);
	
		foreach($aMatches as $sMatch)
		{
			$aTemp = explode(' ', $sMatch, 5);
			$aTemp[4] = trim($aTemp[4]);
			
			switch($aTemp[1])
			{
				case '301':
				{
					$aReturn['Away'] = $aTemp[4];
					break;
				}
				
				case '311':
				{
					$aChunks = explode(' ', $aTemp[4], 4);
					$aReturn['Details'] = array
					(
						"Username" => $aChunks[0],
						"Hostname" => $aChunks[1],
						"Realname" => substr($aChunks[3], 1),
					);
					break;
				}
				
				case '312':
				{
					$aChunks = explode(' ', $aTemp[4], 2);
					$aReturn['Connection'] = array
					(
						"Address" => $aChunks[0],
						"Network" => substr($aChunks[1], 1),
					);
					break;
				}
				
				case 313:
				{
					$aReturn['IRCOp'] = true;
					break;
				}
				
				case '318':
				{
					break;
				}
				
				case '319':
				{
					$aReturn['Channels'] = array_merge($aReturn['Channels'], explode(' ', substr($aTemp[4], 1)));
					break;
				}
			}
		}
		
		if(!$bKeepModes)
		{
			foreach($aReturn['Channels'] as &$sChannel)
			{
				$sChannel = strstr($sChannel, '#');
			}
		}

		return $aReturn;
	}
	
	/**
	 *	Send an inter-bot-communication message to a bot-group. It will
	 *	remain in the queue until it is retrieved from the stack.
	 *
	 *	@param string $sBotGroup Bot group to send the message to.
	 *	@param mixed $mContents Thing to put into the stack.
	 *	@param string $sChannel Channel name
	 */
	public function sendIBCMessage($sBotGroup, $mContents, $sChannel = "Default")
	{
		Control::$aStack[$sBotGroup][$sChannel][] = $mContents;
		return true;
	}
	
	
	/**
	 *	Recieve all inter-bot-communication messages that are in the
	 *	stack for this particular bot.
	 *
	 *	@param string $sChannel Channel name
	 *	@return array Array of all messages.
	 */
	public function getIBCMessages($sChannel = "Default")
	{
		$aResult = Control::$aStack[$this->sBotGroup][$sChannel];
		Control::$aStack[$this->sBotGroup][$sChannel] = array();
		
		return $aResult;
	}
	
	
	/**
	 *	Counts the amount of messages in the stack for the current
	 *	bot group.
	 *
	 *	@param string $sChannel Channel name
	 *	@return integer Amount of messages in the stack.
	 */
	public function getIBCCount($sChannel = "Default")
	{
		if(isset(Control::$aStack[$this->sBotGroup][$sChannel]))
		{
			return count(Control::$aStack[$this->sBotGroup][$sChannel]);
		}
		
		return 0;
	}
}

