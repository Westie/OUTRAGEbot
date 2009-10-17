<?php
/**
 *	Master class for OUTRAGEbot
 *
 *	This class deals with all the interaction with plugins, how the bot acts, etc.
 *	This class also contains all of the commands, etc.
 *
 *	Note: In this documentation, there are some psuedo types that are used to describe
 *	certain arguments.
 *
 *	- callback<code>
 *	$cCallback = array($this, "Function"); // Different class instance
 *	$cCallback = "callLocalFunction";      // Not available right now.
 *	</code>It is used for binds and timers.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0-RC2
 *	@todo Fix that memory leak. It does my head in. :(
 */
 

/* The real code. Woohoo */
class Master
{
	/**
	 *	@ignore
	 */
	public $oPlugins;
	
	
	/**
	 *	@ignore
	 */
	public $aBinds = array();
	
	
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
	public $oModes;
	
	
	/**
	 *	@ignore
	 */
	public $iBotIndex = 0;
	
	
	/**
	 *	@ignore
	 */
	public $oConfig;
	
	
	/**
	 *	Contains the instance of the current bot.
	 *	@var Socket
	 */
	public $oCurrentBot;
	
	
	/**
	 *	Contains the string/message that is sent to the bot
	 *	@var string
	 */
	public $sCurrentChunk;
	
	
	/**
	 *	Constructor for class 'Master'
	 *
	 *	@ignore
	 */
	public function __construct($sKey, &$oConfig)
	{
		$this->oConfig = $oConfig;
		$this->oPlugins = new stdClass();
		$this->oModes = new stdClass();

		echo PHP_EOL." Creating '{$this->oConfig->Network['name']}' at {$this->oConfig->Network['host']}:{$this->oConfig->Network['port']}".PHP_EOL;
		
		foreach($this->oConfig->Bots as $aOption)
		{	
			$this->_childCreate($aOption['nickname'], $aOption);
		}
		
		foreach(explode(',', $this->oConfig->Network['plugins']) as $sPlugin)
		{
			$sPlugin = trim($sPlugin);
			$this->pluginLoad($sPlugin);
		}
		
		/* The uncool stuff. This does mean that yeah, you can this in the configs. */
		if(!isset($this->oConfig->Network['delimiter']))
		{
			$this->oConfig->Network['delimiter'] = "!";
		}
		
		if(!isset($this->oConfig->Network['rotation']))
		{
			$this->oConfig->Network['rotation'] = SEND_DEF;
		}
		
		if(!isset($this->oConfig->Network['quitmsg']))
		{
			$this->oConfig->Network['quitmsg'] = "OUTRAGEbot is going to bed :(";
		}
		
		if(!isset($this->oConfig->Network['version']))
		{
			$this->oConfig->Network['version'] = "OUTRAGEbot v1.0-RC2 (rel. BETA); David Weston; http://outrage.typefish.co.uk";
		}
		
		foreach(explode(',', $this->oConfig->Network['owners']) as $sAddr)
		{
			$sAddr = trim($sAddr);
			$this->oConfig->Network['_owners'][] = $sAddr;
		}
	}
	
	
	/**
	 *	Destructor for class 'Master'
	 *
	 *	@ignore
	 */
	public function _onDestruct()
	{
		foreach($this->oPlugins as $sReference => $oPlugin)
		{
			call_user_func(array($this->oPlugins->$sReference, "onDestruct"));
			unset($this->oPlugins->$sReference);
		}
		
		foreach($this->aBotObjects as $iReference => $oBotObject)
		{
			$oBotObject->destructBot();
			unset($this->aBotObjects[$iReference]);
		}
		
		unset($this->oModes);
		unset($this->oConfig);
	}
	
	
	/**
	 *	Destructor for class 'Master'
	 *
	 *	@ignore
	 */
	public function __destruct()
	{
		$this->_onDestruct();
	}
	
	
	/**
	 *	Loops the bot and its slaves.
	 *
	 *	@ignore
	 */
	public function Loop()
	{
		foreach($this->aBotObjects as $oClones)
		{	
			$oClones->Input();
			
			if($oClones->isClone() == false)
			{
				$this->invokeEvent("onTick");
			}
		}
	}
	
	
	/**
	 *	The backend of creating a child. Why oh why did I make it so complicated?
	 *
	 *	@ignore
	 *	@param string $sChild Child reference.
	 *	@param array $aDetails Details of the child.
	 *	@return bool true on success.
	 */
	private function _childCreate($sChild, $aDetails)
	{
		if($this->childExists($sChild))
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
		return true;
	}
	
	
	/**
	 *	This function creates a child. A child is an instance of the Socket,
	 *	basically an IRC client.
	 *
	 *	<code>$this->childCreate("bot4", "OUTRAGEbot|4", "outrage", "OUTRAGEbot");</code> 
	 *
	 *	@param string $sChild Child reference.
	 *	@param string $sNickname The child's nickname.
	 *	@param string $sUsername The child's username.
	 *	@param string $sRealname The child's real name.
	 *	@return bool true on success.
	 */
	public function childCreate($sChild, $sNickname, $sUsername, $sRealname)
	{
		$aDetails = array
		(
			'nickname' => $sNickname,
			'username' => $sUsername,
			'realname' => $sRealname,
			'altnick' => $sNickname.rand(0, 10),
		);
		
		return $this->_childCreate($sChild, $aDetails);
	}
	
	
	/**
	 *	Lists all the children this group has.
	 *
	 *	@return array Array of children.
	 */
	public function childGet()
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
	 *	<code>$oChild = $this->childGetObject("OUTRAGEbot");
	 *	$oChild->Output("PRIVMSG #OUTRAGEbot :Hello from the raw!");</code>
	 *
	 *	@param string $sChild Child reference.
	 *	@return Socket Class of the socket child.
	 */
	public function childGetObject($sChild)
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
	public function childRename($sChild, $sNewNick)
	{
		if(($oChild = $this->childGetObject($sChild)) === null)
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
	 *	@return bool true on success.
	 */
	public function childRemove($sChild)
	{
		foreach($this->aBotObjects as $iReference => $oChild)
		{
			if($oChild->sChild == $sChild)
			{
				if($iReference == 0)
				{
					return false;
				}
				
				$oChild->destructBot();
				unset($this->aBotObjects[$iReference]);				
				$this->aBotObjects = array_values($this->aBotObjects);
				
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
	public function childExists($sChild)
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
	public function childReconnect($sChild, $sMessage = "Rehash!")
	{
		if(($oChild = $this->childGetObject($sChild)) === null)
		{
			return false;
		}

		$oChild->destructBot($sMessage);
		$oChild->constructBot();
		
		return true;
	}
	
	
	/**
	 *	Returns an object of a child from its reference. Alias of Master::childGetObject().
	 *
	 *	@param string $sChild Child reference.
	 *	@return Socket Class of the socket child.
	 *	@uses childGetObject
	 */
	public function getChildAsObject($sChild)
	{
		return $this->childGetObject($sChild);
	}
	
	
	/**
	 *	Sends RAW IRC Messages
	 *
	 *	@param string $sMessage Raw IRC message you want to send.
	 *	@param integer $iSend How to send the message.
	 */
	public function sendRaw($sMessage, $iSend = SEND_CURR)
	{
		if($iSend == SEND_DEF)
		{
			$iSend = $this->oConfig->Network['rotation'];
		}
		
		switch($iSend)
		{
			case SEND_MAST:
			{
				$this->aBotObjects[0]->Output($sMessage);
				break;
			}
			case SEND_CURR:
			{
				$this->oCurrentBot->Output($sMessage);
				break;
			}
			case SEND_ALL:
			{
				foreach($this->aBotObjects as $oBot)
				{
					$oBot->Output($sMessage);
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
	
		
	/**
	 *	Request the modes in a channel - used for user modes.
	 *	Do we need to call this any more?
	 *
	 *	@ignore
	 *	@param string $sChannel Channel name.
	 */
	function getNames($sChannel)
	{
		if(!$sChannel) return false;
		if($this->oCurrentBot->isClone()) return false;
		
		$this->sendRaw("NAMES {$sChannel}");
		return true;
	}
	
	
	/**
	 *	Get the users nickname from a hostname string.
	 *
	 *	@param string $sHost The hostname string
	 *	@return string Nickname
	 */
	public function getNickname($sHost)
	{
		$aDetails = explode('!', $sHost);
		return str_replace(':', '', $aDetails[0]);
	}
	
	
	/**
	 *	Get the users hostname from a hostname string.
	 *
	 *	@param string $sHost The hostname string
	 *	@return string Hostmask
	 */
	public function getHostname($sHost)
	{
		$aDetails = explode('@', $sHost);
		return (isset($aDetails[1]) ? $aDetails[1] : "");
	}

	
	/**
	 *	This function gets the next child along in the queue.
	 *
	 *	@return Socket Child object.
	 */
	public function getNextChild()
	{
		return next($this->aBotObjects) == false ? reset($this->aBotObjects) : current($this->aBotObjects);
	}
	
	
	/**
	 *	Recieve input from the children.
	 *
	 *	@ignore
	 */
	public function getSend(Socket &$oBot, $sMessage)
	{
		if(strlen($sMessage) < 3) return true;
		
		/* Deal with the useless crap. */
		$this->oCurrentBot = &$oBot;
		$this->sCurrentChunk = $sMessage;
		
		$aChunks = explode(' ', $sMessage, 4);
		$this->bindScan($aChunks);
		
		/* Deal with realtime scans */
		if($oBot->iUseQueue == true)
		{
			if(array_search($aChunks[1], $oBot->aSearch) === false)
			{
				$oBot->aMsgQueue[] = $sMessage;
			}
			else
			{
				$oBot->aMatchQueue[] = $sMessage;
			}
			return true;
		}

		/* More taxing crap. Should I improve this one day? I have no idea! */
		$aChunks[0] = trim(isset($aChunks[0][0]) ? ($aChunks[0][0] == ":" ? substr($aChunks[0], 1) : $aChunks[0]) : "");
		$aChunks[1] = trim(isset($aChunks[1][0]) ? $aChunks[1] : "");
		$aChunks[2] = trim(isset($aChunks[2][0]) ? ($aChunks[2][0] == ":" ? substr($aChunks[2], 1) : $aChunks[2]) : "");
		$aChunks[3] = trim(isset($aChunks[3][0]) ? ($aChunks[3][0] == ":" ? substr($aChunks[3], 1) : $aChunks[3]) : "");

		/* Deal with pings */
		if($aChunks[0] == 'PING')
		{
			$oBot->Output('PONG '.$aChunks[1]);
			return true;
		}
		
		/* The infamous switchboard. */
		switch(strtoupper($aChunks[1]))
		{
			case "PONG":
			{
				$oBot->iNoReply = 0;
				$oBot->iHasBeenReply = true;
				break;
			}
			case "001":
			{
				$this->_onConnect($oBot);
				break;
			}
			case "JOIN":
			{
				$this->_onJoin($aChunks);
				break;
			}
			case "KICK":
			{
				$this->_onKick($aChunks);
				break;
			}
			case "PART":
			{
				$this->_onPart($aChunks);
				break;
			}
			case "QUIT":
			{
				$this->_onQuit($aChunks);
				break;
			}
			case "MODE":
			{
				$this->_onMode($aChunks);
				break;
			}
			case "NICK":
			{
				$this->_onNick($aChunks);
				break;
			}
			case "NOTICE":
			{
				$this->_onNotice($aChunks);
				break;
			}
			case "PRIVMSG":
			{
				if($aChunks[3][0] == Format::CTCP)
				{
					$this->_onCTCP($aChunks);
					break;
				}
				switch($aChunks[2][0])
				{
					case '&':
					case '#':
					{
						if($aChunks[3][0] == $this->oConfig->Network['delimiter'])
						{
							$this->_onCommand($aChunks);
							break;
						}
						
						$this->_onMessage($aChunks);
						break;
					}
					default:
					{
						$this->_onPrivMessage($aChunks);
						break;
					}
				}
				break;
			}
			case "TOPIC":
			{
				$this->_onTopic($aChunks);
				break;
			}
			case "ERROR":
			{
				$this->_onError($aChunks);
				break;
			}
			default:
			{
				$this->_onRaw($aChunks);
				break;
			}
		}
		return true;
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onConnect($oBot)
	{
		$this->invokeEvent("onConnect");
		
		foreach((array) explode(',', $this->oConfig->Network['channels']) as $sChannel)
		{
			$this->sendRaw("JOIN {$sChannel}");
		}
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onJoin($aChunks)
	{
		$this->invokeEvent("onJoin", $this->getNickname($aChunks[0]), $aChunks[2]);
		$this->addUserToChannel($aChunks[2], $this->getNickname($aChunks[0]));
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onKick($aChunks)
	{
		$aChunks[3] = explode(' ', $aChunks[3], 2);
		$aChunks[3][1] = trim(isset($aChunks[3][1]) ? substr($aChunks[3][1], 1) : "");
		$this->invokeEvent("onKick", $this->getNickname($aChunks[0]), $aChunks[3][0], $aChunks[2], $aChunks[3][1]);
		$this->removeUserFromChannel($aChunks[2], $aChunks[3][0]);
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onPart($aChunks)
	{
		$this->invokeEvent("onPart", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3]);
		$this->removeUserFromChannel($aChunks[2], $this->getNickname($aChunks[0]));
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onQuit($aChunks)
	{
		$this->invokeEvent("onQuit", $this->getNickname($aChunks[0]), $aChunks[3]);
		$this->removeUserFromChannel('*', $this->getNickname($aChunks[0]));
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onMode($aChunks)
	{
		$this->invokeEvent("onMode", $aChunks[2], $aChunks[3]);
			
		foreach($this->parseModes($aChunks[3]) as $aMode)
		{
			switch($aMode['MODE'])
			{
				case 'v':
				{
					if($aMode['ACTION'] == '+') $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] |= 1;
					else $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] ^= 1;
					break;
				}
				case 'h':
				{
					if($aMode['ACTION'] == '+') $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] |= 3;
					else $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] ^= 3;
					break;
				}
				case 'o':
				{
					if($aMode['ACTION'] == '+') $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] |= 7;
					else $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] ^= 7;
					break;
				}
				case 'a':
				{
					if($aMode['ACTION'] == '+') $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] |= 15;
					else $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] ^= 15;
					break;
				}
				case 'q':
				{
					if($aMode['ACTION'] == '+') $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] |= 31;
					else $this->oModes->aChannels[strtolower($aChunks[2])][$aMode['PARAM']]['iMode'] ^= 31;
					break;
				}
			}
		}
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onNick($aChunks)
	{
		$sNickname = $this->getNickname($aChunks[0]);
		
		if(!strcmp($sNickname, $this->oCurrentBot->aConfig['nickname']))
		{
			/* God, this IRCnet sucks. :( */
			$this->oCurrentBot->aConfig['nickname'] = $sNickname;
		}
		
		$this->invokeEvent("onNick", $sNickname, $aChunks[2]);
		$this->renameUserFromChannel($sNickname, $aChunks[2]);
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onNotice($aChunks)
	{
		$this->invokeEvent("onNotice", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3]);
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onCTCP($aChunks)
	{
		$aChunks[3] = explode(' ', str_replace("\001", "", $aChunks[3]), 2);
		$this->invokeEvent("onCTCP", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3][0], (isset($aChunks[3][1]) ? $aChunks[3][1] : ""));
		
		switch(strtoupper($aChunks[3][0]))
		{
			case "VERSION":
			{
				$this->ctcpReply($this->getNickname($aChunks[0]), "VERSION {$this->oConfig->Network['version']}");
				break;
			}
			case "TIME":
			{
				$this->ctcpReply($this->getNickname($aChunks[0]), "TIME ".date("d/m/Y H:i:s", time()));
				break;
			}
			case "PING":
			{
				$this->ctcpReply($this->getNickname($aChunks[0]), "PING {$aChunks[3][1]}");
				break;
			}
			case 'UPTIME':
			{
				$this->ctcpReply($this->getNickname($aChunks[0]), "UPTIME ".date("d/m/Y H:i:s", $this->oCurrentBot->aStatistics['StartTime']));
				break;
			}
		}
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onCommand($aChunks)
	{
		$aCommand = explode(' ', trim($aChunks[3]), 2);
		$this->invokeEvent("onCommand", $this->getNickname($aChunks[0]), $aChunks[2], substr($aCommand[0], 1), (isset($aCommand[1]) ? $aCommand[1] : ""));
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onMessage($aChunks)
	{
		$this->invokeEvent("onMessage", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3]);
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onPrivMessage($aChunks)
	{
		$this->invokeEvent("onPrivMessage", $this->getNickname($aChunks[0]), $aChunks[3]);
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onTopic($aChunks)
	{
		$this->invokeEvent("onTopic", $this->getNickname($aChunks[0]), $aChunks[2], $aChunks[3]);
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onError($aChunks)
	{
	}
	
	
	/**
	 *	@ignore
	 */
	private function _onRaw($aChunks)
	{
		switch($aChunks[1])
		{
			/* NAMES reply. */
			case 353:
			{
				/* Dirty arrays are dirty. I hate them. */
				$aData = explode(" ", $aChunks[3], 3);
				$aData[2] = substr($aData[2], 1);
				$aUsers = explode(" ", $aData[2]);
				$sChan = strtolower($aData[1]);
				
				/* Great, we now parse the users... */
				foreach($aUsers as $sUser)
				{
					$iTemp = 0;
					$sUser = trim($sUser);
					
					switch($sUser[0])
					{
						case '+': $iTemp = 1; break;
						case '%': $iTemp = 3; break;
						case '@': $iTemp = 7; break;
						case '&': $iTemp = 15; break;
						case '~': $iTemp = 31; break;
						default:  break;
					}
					
					$sUser = preg_replace("/[+%@&~]/", "", $sUser);
					$this->oModes->aChannels[$sChan][$sUser]['iMode'] = $iTemp;
					$this->oModes->aUsers[$sUser][$sChan] = true;
				}
				
				break;
			}
			
			/* Nick already in use. */
			case 433:
			{
				if(isset($this->oCurrentBot->aConfig['altnick']))
				{
					$sNewNick = $this->oCurrentBot->aConfig['altnick'];
				}
				else
				{
					$sNewNick = $this->oCurrentBot->aConfig['nickname'].rand(10, 99);
				}
				
				$this->oCurrentBot->setNickname($sNewNick);
				break;
			}
		}
	}
	
	
	/**
	 *	Adds a user to the channel's database. Used internally.
	 *
	 *	@ignore
	 *	@param string $sChan Channel where user is.
	 *	@param string $sUser Nickname to remove from list.
	 */
	public function addUserToChannel($sChan, $sUser)
	{
		$this->oModes->aUsers[$sUser][strtolower($sChan)] = true;
	}
	
	
	/**
	 *	Removes a user from the channel's database. Used internally. '*' signifies all.
	 *
	 *	@ignore
	 *	@param string $sChan Channel where user is.
	 *	@param string $sUser Nickname to remove from list.
	 */
	public function removeUserFromChannel($sChan, $sUser)
	{
		if($sChan != '*')
		{
			unset($this->oModes->aChannels[strtolower($sChan)][$sUser]);
			return;
		}
		
		foreach($this->oModes->aUsers[$sUser] as $sChannel => $mUnused)
		{
			unset($this->oModes->aChannels[$sChannel][$sUser]);
			unset($this->oModes->aUsers[$sUser][$sChannel]);
		}
		return;
	}
	
	
	/**
	 *	Renames a user from the channel's database. Used internally.
	 *
	 *	@ignore
	 *	@param string $sOldNick The old nickname.
	 *	@param string $sNewNick The new nickname.
	 */
	public function renameUserFromChannel($sOldNick, $sNewNick)
	{
		foreach($this->oModes->aUsers[$sOldNick] as $sChannel => $mUnused)
		{
			$this->oModes->aChannels[$sChannel][$sNewNick] = $this->oModes->aChannels[$sChannel][$sOldNick];
			unset($this->oModes->aChannels[$sChannel][$sOldNick]);
		}
		
		$this->oModes->aUsers[$sNewNick] = $this->oModes->aUsers[$sOldNick];	
		unset($this->oModes->aUsers[$sOldNick]);
		
		return;
	}
	
	
	/**
	 *	Returns user information from a channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return array Returns an array on success, FALSE on failure.
	 */
	public function getUserInfoFromChannel($sChan, $sUser)
	{
		$sChan = strtolower($sChan);
		
		if(!isset($this->oModes->aChannels[$sChan][$sUser]))
		{
			return false;
		}
		
		return $this->oModes->aChannels[$sChan][$sUser];
	}
	
	
	/**
	 *	Checks if that user is actually in that channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserInChannel($sChan, $sUser)
	{
		return isset($this->oModes->aUsers[$sUser][strtolower($sChan)]) != false;
	}
	
	
	/**
	 *	Checks if that user has voice in that channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserVoice($sChan, $sUser)
	{
		$aUser = $this->getUserInfoFromChannel($sChan, $sUser);
		
		if($aUser === false)
		{
			return false;
		}
		
		return ($aUser['iMode'] & MODE_USER_VOICE) != 0; 
	}
	
	
	/**
	 *	Checks if that user has half-op in that channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserHalfOp($sChan, $sUser)
	{
		$aUser = $this->getUserInfoFromChannel($sChan, $sUser);
		
		if($aUser === false)
		{
			return false;
		}
		
		return ($aUser['iMode'] & MODE_USER_HOPER) != 0; 
	}
	
	
	/**
	 *	Checks if that user has operator in that channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserOper($sChan, $sUser)
	{
		$aUser = $this->getUserInfoFromChannel($sChan, $sUser);
		
		if($aUser === false)
		{
			return false;
		}
		
		return ($aUser['iMode'] & MODE_USER_OPER) != 0; 
	}
	
	
	/**
	 *	Checks if that user has admin in that channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserAdmin($sChan, $sUser)
	{
		$aUser = $this->getUserInfoFromChannel($sChan, $sUser);
		
		if($aUser === false)
		{
			return false;
		}
		
		return ($aUser['iMode'] & MODE_USER_ADMIN) != 0; 
	}
	
	
	/**
	 *	Checks if that user has owner in that channel.
	 *
	 *	@param string $sChan Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserOwner($sChan, $sUser)
	{	
		$aUser = $this->getUserInfoFromChannel($sChan, $sUser);
		
		if($aUser === false)
		{
			return false;
		}
		
		return ($aUser['iMode'] & MODE_USER_OWNER) != 0; 
	}
		
	
	/**
	 *	Checks if that user is registered as an administrator of the bot.
	 *
	 *	@return bool 'true' on success.
	 */
	public function isAdmin()
	{
		$aChunks = explode(' ', $this->sCurrentChunk, 4);
		$sHostname = $this->getHostname($aChunks[0]);
		
		return (in_array($sHostname, $this->oConfig->Network['_owners']) !== false);
	}
	
	
	/**
	 *	Checks if either the current instance, or a specific instance is actually a child.
	 *
	 *	@param string $sChild Child reference.
	 *	@return bool true on success, null if child is non-existant.
	 */
	public function isChild($sChild = "")
	{
		$oChild = ($sChild == "" ? $this->oCurrentBot : $this->childGetObject($sChild));
		
		if($oChild == null)
		{
			return null;
		}
		
		return $oChild->isClone();
	}


	/**
	 *	Invokes an event/callback from plugins.
	 *
	 *	<code>$this->invokeEvent("onTick");
	 *	$this->invokeEvent("onCommand", $sNickname, $sChannel, $sCommand, $sArguments);</code>
	 *
	 *	@param string $sEvent Event to invoke
	 *	@param mixed $... Arguments to pass to event.
	 *	@return void
	 */
	public function invokeEvent($sEvent)
	{
		if(func_num_args() == 0)
		{
			return false;
		}
		
		$aArguments = func_get_args();
		array_shift($aArguments);
		
		foreach($this->oPlugins as &$oPlugin)
		{
			if(call_user_func_array(array($oPlugin, $sEvent), $aArguments) != false)
			{
				break;
			}
		}
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
	 *	@param string $sChannel Channel name
	 *	@param string $sMessage Message to send
	 *	@param integer $iSend How to send message
	 *	@see Master::sendRaw()
	 */
	public function sendMessage($sChannel, $sMessage, $iSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :{$sMessage}", $iSend);
	}
	
	
	/**
	 *	Sends an action to the specified channel.
	 *
	 *	@param string $sChannel Channel name
	 *	@param string $sMessage Message to send
	 *	@param integer $iSend How to send message
	 *	@see Master::sendRaw()
	 */
	public function sendAction($sChannel, $sMessage, $iSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :".chr(1)."ACTION {$sMessage}".chr(1), $iSend);
	}
	
	
	/**
	 *	Sends a notice to the specified channel.
	 *
	 *	@param string $sNickname Nickname
	 *	@param string $sMessage Message to send
	 *	@param integer $iSend How to send message
	 *	@see Master::sendRaw()
	 */
	public function sendNotice($sNickname, $sMessage, $iSend = SEND_DEF)
	{
		return $this->sendRaw("NOTICE {$sNickname} :{$sMessage}", $iSend);
	}
	
	
	/**
	 *	Sends a message to the specified channel.
	 *
	 *	@see Master::sendMessage()
	 */
	public function Message($sChannel, $sMessage, $iSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :{$sMessage}", $iSend);
	}
	
	
	/**
	 *	Sends an action to the specified channel.
	 *
	 *	@see Master::sendAction()
	 */
	public function Action($sChannel, $sMessage, $iSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sChannel} :".chr(1)."ACTION {$sMessage}".chr(1), $iSend);
	}
	
	
	/**
	 *	Sends a notice to the specified channel.
	 *
	 *	@see Master::sendNotice()
	 */
	public function Notice($sNickname, $sMessage, $iSend = SEND_DEF)
	{
		return $this->sendRaw("NOTICE {$sNickname} :{$sMessage}", $iSend);
	}
	
	
	/**
	 *	Sends a CTCP reply.
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
	 *	@param string $sNickname Nickname
	 *	@param string $sRequest CTCP request
	 *	@param integer $iSend How to send message
	 *	@see Master::sendRaw()
	 */
	public function ctcpRequest($sNickname, $sRequest, $iSend = SEND_DEF)
	{
		return $this->sendRaw("PRIVMSG {$sNickname} :".chr(1).trim($sRequest).chr(1), $iSend);
	}


	/**
	 *	Creates a timer, note that arguments to be passed to $cCallback to after $iRepeat.
	 *
	 *	@param callback $cCallback Timer callback 
	 *	@param integer $iInterval <b>Seconds</b> between timer calls.
	 *	@param integer $iRepeat How many times the timer should call before it is destroyed. -1 implies infinite.
	 *	@param mixed $... Arguments to pass to timer.
	 *	@return string Timer reference ID.
	 */
	public function timerCreate($cCallback, $iInterval, $iRepeat)
	{
		$aArguments = func_get_args();
		array_shift($aArguments);
		array_shift($aArguments);
		array_shift($aArguments);
		
		return Timers::Create($cCallback, $iInterval, $iRepeat, (array) $aArguments); 
	}

		
	/**
	 *	Gets the information of a timer from its reference ID.
	 *
	 *	These are the contents of the array that is returned when this function is invoked.
	 *	
	 *	<pre>
	 *	<b>CALLBACK</b>  -> Callback which the timer calls when invoked.
	 *	<b>INTERVAL</b>  -> How many seconds pass between each call.
	 *	<b>REPEAT</b>    -> How many times left the plugin is called before it is unlinked.
	 *	<b>CALLTIME</b>  -> When the plugin will next call itself (Unix time).
	 *	<b>ARGUMENTS</b> -> Array of arguments that will be passed to the timer.
	 *	</pre>
	 *
	 *	@param string $sKey Timer reference ID.
	 *	@return array Array of timer information.
	 */
	public function timerGet($sKey)
	{
		return Timers::Get($sKey);
	}
	
	
	/**
	 *	Creates a timer, note that arguments to be passed to $cCallback to after $iRepeat.
	 *
	 *	@param string $sKey Timer reference ID.
	 *	@return bool 'true' on success.
	 */
	public function timerKill($sKey)
	{
		return Timers::Delete($sKey);
	}
	
	
	/**
	 *	Loads a plugin from the plugin directory.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function pluginLoad($sPlugin)
	{
		if(array_key_exists($sPlugin, $this->oPlugins))
		{
			return false;
		}

		$sDirname = BASE_DIRECTORY."/Plugins/{$sPlugin}/Default.php";

		if(!file_exists($sDirname))
		{
			return false;
		}

		$sIdentifier = substr($sPlugin, 0, 8).'_'.substr(sha1(time()."-".uniqid()), 2, 10);
		$sClass = file_get_contents($sDirname); // Ouch, this has gotta hurt.

		if(!preg_match("/class[\s]+?".$sPlugin."[\s]+?extends[\s]+?Plugins[\s]+?{/", $sClass))
		{
			return false;
		}
			
		$sClass = preg_replace("/(class[\s]+?)".$sPlugin."([\s]+?extends[\s]+?Plugins[\s]+?{)/", "\\1".$sIdentifier."\\2", $sClass);
		$sFile = tempnam(dirname($sDirname), "mod"); // Stops the __FILE__ bugs.
		file_put_contents($sFile, $sClass);				
		unset($sClass); // Weight off the shoulders anyone?
			
		include($sFile);
		unlink($sFile);
				
		$this->oPlugins->$sPlugin = new $sIdentifier($this, array($sPlugin, $sIdentifier));
		echo "* Plugin ".$sPlugin." has been loaded.".PHP_EOL;
		
		return true;
	}
	

	/**
	 *	Gets the instance of the plugin if it exists.
	 *
	 *	@param string $sPlugin Plugin name
	 *	@return Plugin Object of the plugin.
	 */
	public function pluginGet($sPlugin)
	{
		if(isset($this->oPlugins->$sPlugin))
		{
			return $this->oPlugins->$sPlugin;
		}

		return null;
	}


	/**
	 *	Unloads an active plugin from memory.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function pluginUnload($sPlugin)
	{
		if(isset($this->oPlugins->$sPlugin))
		{
			unset($this->oPlugins->$sPlugin);
			echo "* Plugin ".$sPlugin." has been unloaded.".PHP_EOL;
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
	public function pluginReload($sPlugin)
	{
		$this->pluginUnload($sPlugin);
		return $this->pluginLoad($sPlugin);
	}
	
	
	/**
	 *	Check if a plugin is loaded into memory.
	 *
	 *	@param string $sPlugin
	 *	@return bool 'true' on success.
	 */
	public function pluginExists($sPlugin)
	{
		return isset($this->oPlugins->$sPlugin);
	}
		
	
	/**
	 *	Create a bind handler for IRC numerics/commands.
	 *
	 *	If you are passing arguments to the bind handler, then <b>must</b> $aFormat must be populated.
	 *	If you do not want to pass arguments, you can either assign $aFormat to false or a blank array.
	 *	If you want the full string, assign $aFormat to be true.
	 *	Otherwise, when using $aFormat, numeric characters are replaced with their corresponding chunk when called.
	 *
	 *	<code>$this->iBindID = $this->bindCreate("INVITE", array($this, "onInvite"), array(2, 3));</code>
	 *
	 *	@example ../_Examples/bindCreate.php A demo plugin that demonstrates how to use it.
	 *	@param string $sInput IRC numeric name
	 *	@param callback $cCallback Callback to bind handler.
	 *	@param array $aFormat Array of arguments to pass to the bind handler.
	 *	@return string Bind resource ID.
	 */
	public function bindCreate($sInput, $cCallback, $aFormat)
	{
		$sHandle = substr(sha1(time()."-".uniqid()), 2, 10);
		
		$this->aBinds[$sHandle] = array
		(
			"INPUT" => $sInput,
			"CALLBACK" => $cCallback,
			"FORMAT" => $aFormat,
		);
		
		return $sHandle;
	}
	   
	/**
	 *	Gets the information of a bind from its reference ID.
	 *
	 *	These are the contents of the array that is returned when this function is invoked.
	 *	<pre>
	 *	<b>CALLBACK</b> -> Callback which the bind calls when invoked.
	 *	<b>FORMAT</b>   -> Arguments to pass to the event. See bindCreate.
	 *	<b>INPUT</b>    -> The IRC command matches the bind.
	 *	</pre>
	 *
	 *	@param string $sKey Bind reference ID.
	 *	@return array Array of bind information.
	 */
	public function bindGet($sKey)
	{
		if(isset($this->aBinds[$sKey]))
        {
			return $this->aBinds[$sKey];
		}

		return null;
	}

	
	/**
	 *	Delete a reference to a bind handler.
	 *
	 *	@param string $sKey
	 *	@return bool 'true' on success.
	 */
	public function bindDelete($sKey)
	{
		if(isset($this->aBinds[$sKey]))
		{
			unset($this->aBinds[$sKey]);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Scans through the bind handlers.
	 *
	 *	@param string $sKey
	 *	@ignore
	 */
	public function bindScan($aChunks)
	{
		foreach($this->aBinds as &$aSection)
		{
			if(!isset($aChunks[1])) return;
			
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
			
			call_user_func_array($aSection['CALLBACK'], $aArguments);
		}
	}
	
	
	/**
	 *	Request information realtime.
	 *
	 *	The data that are you requesting (for instance, what is in $mSearch) will not be parsed by the bot.
	 *	This essentially means it is the job of the code using that request to deal with parsing it properly.
	 *
	 *	<code>$aMatches = $this->getRealtimeRequest("NAMES #westie", array(353, 366), 4);
	 *
	 *	// Array
	 *	// (
	 *	//	 [0] => :ircd 353 OUTRAGEbot = #westie :OUTRAGEbot ~Westie
	 *	//	 [1] => :ircd 366 OUTRAGEbot #westie :End of /NAMES list.
	 *	// )</code>
	 *
	 *	@param string $sRequest Message to send to the server.
	 *	@param mixed $mSearch IRC numerics to cache.
	 *	@param integer $iSleep <i>Microseconds</i> to sleep before getting input.
	 *	@return array The response matched to the data in $aSearch.
	 */
	public function getRealtimeRequest($sRequest, $mSearch, $iSleep = 10000)
	{
		$this->oCurrentBot->iUseQueue = true;
		$this->oCurrentBot->aSearch = (array) $mSearch;
		$this->oCurrentBot->aMatchQueue = array();
		$this->oCurrentBot->Output($sRequest);
		usleep($iSleep);
		$this->oCurrentBot->Input();
		$this->oCurrentBot->iUseQueue = false;
		
		return $this->oCurrentBot->aMatchQueue;
	}
	
	
	/**
	 *	Invites a user to a channel.
	 *
	 *	@param string $sChannel Channel name.
	 *	@param string $sNickname Nickname of the person to kick.
	 *	@param string $sReason Reason of the kick.
	 */
	public function channelInvite($sChannel, $sNickname)
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
	public function channelKick($sChannel, $sNickname, $sReason = "Kick")
	{
		return $this->sendRaw("KICK {$sChannel} :{$sReason}");
	}
	
	
	/**
	 *	Allows the bot to join a channel.
	 *
	 *	@param string $sChannel Channel name (IRC format applies!).
	 */
	public function channelJoin($sChannel)
	{
		return $this->sendRaw("JOIN $sChannel");
	}
	
	
	/**
	 *	Allows the bot to part a channel.
	 *
	 *	@param string $sChannel Channel name.
	 *	@param string $sReason Reason for leaving
	 */
	public function channelPart($sChannel, $sReason = false)
	{
		return $this->sendRaw("PART $sChannel".($sReason == false ? "" : " :{$sReason}"));
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
	 -
	 -	Please forgive me, for I have sinned with a lot of HTML.
	 -	You should be able to figure out what this is all about anyway.
	 -
	 *	A list of rows that this function returns when called.
	 *	<pre>
	 *	<b>INFO:</b>
	 *		|- <b>USERNAME</b> -> The username of the user.
	 *		|- <b>HOSTNAME</b> -> The user's hostname.
	 *		'- <b>REALNAME</b> -> The user's realname.
	 *	 
	 *	<b>SERVER:</b>
	 *		|- <b>SERVER</b>   -> The server's address.
	 *		'- <b>REALNAME</b> -> The server/network name.
	 *
	 *	<b>CHANNELS</b> -> An array of all the channels (with modes) that the user is in.
	 *	</pre>
	 *
	 *	@param $sNickname Nickname of the user.
	 *	@param $iDelay Microseconds to wait before fetching input.
	 *	@return array Array of modes.
	 */
	public function getWhois($sNickname, $iDelay = 500000)
	{
		$aMatches = $this->getRealtimeRequest("WHOIS {$sNickname}", array('311', '312', '318', '319'), $iDelay);
		$aReturn = array();
		$aReturn['CHANNELS'] = array();
	
		foreach($aMatches as $sMatch)
		{
			$aTemp = explode(' ', $sMatch, 5);
			$aTemp[4] = trim($aTemp[4]);
			
			switch($aTemp[1])
			{
				case '311':
				{
					$aChunks = explode(' ', $aTemp[4], 4);
					$aReturn['INFO'] = array
					(
						"USERNAME" => $aChunks[0],
						"HOSTNAME" => $aChunks[1],
						"REALNAME" => substr($aChunks[3], 1),
					);
					break;
				}
				case '312':
				{
					$aChunks = explode(' ', $aTemp[4], 2);
					$aReturn['SERVER'] = array
					(
						"SERVER" => $aChunks[0],
						"INFO" => substr($aChunks[1], 1),
					);
					break;
				}
				case '318':
				{
					break;
				}
				case '319':
				{
					$aReturn['CHANNELS'] = array_merge($aReturn['CHANNELS'], explode(' ', substr($aTemp[4], 1)));
					break;
				}
			}
		}

		return $aReturn;
	}
}

?>
