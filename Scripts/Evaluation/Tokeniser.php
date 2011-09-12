<?php
/**
 *	This is the Tokeniser class, part of the PHP-Scripting
 *	collection.
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     5f0b25489c21ae65471f2289c56a4475a94296dc
 *	Committed at:   Mon Sep 12 18:38:47 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class EvaluationTokeniser
{
	/**
	 *	Store our output
	 */
	private
		$fTimeDelta = 0,
		$aBindingInstances = null,
		$aPatches = array(),
		$aTokeniser = array();


	/**
	 *	Called when the tokeniser is loaded.
	 */
	public final function __construct()
	{
		$this->patch("Method", function($current, $previous)
		{
			if($current[0] == T_STRING && $previous[0] != T_OBJECT_OPERATOR && $previous[0] != T_DOUBLE_COLON)
			{
				if(!defined($current[1]) && !function_exists($current[1]) && $current[1] != "function")
				{
					return true;
				}
			}

			return false;
		});

		$this->patch("Cast", function($current, $previous)
		{
			if($current[1] != '(')
			{
				return false;
			}

			switch($previous[0])
			{
				case T_WHITESPACE:
				case '(':
				case ';':
				case ',':
				{
					return true;
				}

				default:
				{
					return false;
				}
			}

			return true;
		});
	}


	/**
	 *	Tokenise and patch the code.
	 */
	public final function __invoke($sCode)
	{
		return $this->runTokeniser($sCode);
	}


	/**
	 *	Binds the tokeniser to an object.
	 *	This enables advanced object patches.
	 */
	public final function bind($sPointer, $pObject)
	{
		$this->aBindingInstances[] = (object) array
		(
			"variable" => $sPointer,
			"pointer" => $pObject,
		);
	}


	/**
	 *	Adds a patch to the Tokeniser.
	 */
	public final function patch($cPatchCallback, $cPatchVerifier)
	{
		$this->aPatches[] = (object) array
		(
			"callback" => $cPatchCallback,
			"verifier" => $cPatchVerifier,
		);
	}


	/**
	 *	Analyses the code, tokenises it, and then
	 *	applies the patches where needed.
	 */
	public final function run($sCode)
	{
		$fTimeDelta = microtime(true);
		$this->aTokeniser = token_get_all('<?php '.$sCode.' ?>');

		foreach($this->aTokeniser as $iIndex => $mValue)
		{
			if(!is_array($this->aTokeniser[$iIndex]))
			{
				$this->aTokeniser[$iIndex] = array
				(
					(string) $mValue,
					(string) $mValue,
					0,
				);
			}
		}

		foreach($this->aPatches as $cPatch)
		{
			$iIndex = 0;
			$iLength = count($this->aTokeniser);

			while($iIndex < $iLength)
			{
				$mCurrent = $this->aTokeniser[$iIndex];
				$mPrevious = ($iIndex != 0 ? $this->aTokeniser[$iIndex - 1] : array(0, "", 0));

				$cVerifier = $cPatch->verifier;

				if($cVerifier($mCurrent, $mPrevious))
				{
					if(is_array($cPatch->callback))
					{
						call_user_func($cPatch->callback, $iIndex);
					}
					else
					{
						if(method_exists($this, $cPatch->callback))
						{
							$this->{$cPatch->callback}($iIndex);
						}
						else
						{
							$sFunction = $cPatch->callback;
							$sFunction($iIndex);
						}
					}

					$iLength = count($this->aTokeniser);
				}

				++$iIndex;
			}
		}

		$this->fTimeDelta = microtime(true) - $fTimeDelta;
		return $this->getOutput();
	}


	/**
	 *	Return the output as a string.
	 */
	public final function getOutput()
	{
		$sReturn = "";

		foreach($this->aTokeniser as $mToken)
		{
			if(is_string($mToken))
			{
				$sReturn .= $mToken;
			}
			else
			{
				$sReturn .= $mToken[1];
			}
		}

		return substr($sReturn, 6, -3);
	}


	/**
	 *	Returns the amount of time in seconds it took to compile
	 *	the patches.
	 */
	public final function getTimeDelta()
	{
		return $this->fTimeDelta;
	}


	/**
	 *	Gets the token string.
	 */
	protected function getToken($iIndex, &$iType = null, &$iLine = 0)
	{
		if(!isset($this->aTokeniser[$iIndex]))
		{
			$iLength = count($this->aTokeniser);
			throw new OutOfRangeException("Invalid index accessed [{$iIndex}, {$iLength}]");
		}

		if(is_array($this->aTokeniser[$iIndex]))
		{
			$iType = $this->aTokeniser[$iIndex][0];
			$iLine = $this->aTokeniser[$iIndex][2];

			return $this->aTokeniser[$iIndex][1];
		}

		return $this->aTokeniser[$iIndex];
	}


	/**
	 *	Called to cast something to a custom type/object.
	 */
	private function Cast(&$iIndex)
	{
		# Retrieve our cast.
		$iStart = $iIndex;
		++$iIndex;

		$sCast = $this->getToken($iIndex, $iType);

		if($iType != T_STRING)
		{
			return;
		}

		++$iIndex;

		if($this->getToken($iIndex) != ')' || function_exists($sCast) || defined($sCast))
		{
			return;
		}

		# Get rid of whitespace to locate our variable.
		++$iIndex;

		$aContext = array();

		list($iType, $sContext, $iLine) = ($aContext[0] = $this->aTokeniser[++$iIndex]);

		# Make sure what we're doing here...
		if($sContext == '{')
		{
			# So, it's a complex thing. We need to make everything inside
			# brackets the context.
			$aContext = array();

			while($this->aTokeniser[++$iIndex][1] != '}')
			{
				$aContext[] = $this->aTokeniser[$iIndex];
			}

			# A retarded hack, I know! Please don't kill me.
			$this->aTokeniser[$iIndex] = array
			(
				T_WHITESPACE,
				'',
				0,
			);
		}
		else
		{
			if($iType == T_STRING && !is_callable($sContext) && !defined($sContext))
			{
				$aContext[0] = array
				(
					T_CONSTANT_ENCAPSED_STRING,
					'"'.$sContext.'"',
					$iLine,
				);
			}

			++$iIndex;
		}

		# Find the end of our expression.

		# And finally, fix the array.
		$iSpliceLength = $iIndex - $iStart;

		$aTokens = array();

		$sMethod = 'to'.ucwords($sCast);
		$bMethod = is_callable($sMethod);

		foreach($this->aBindingInstances as $pBind)
		{
			$cMethod = array
			(
				$pBind->pointer,
				$sMethod,
			);

			if(!is_callable($cMethod))
			{
				continue;
			}

			$aTokens[] = array
			(
				T_VARIABLE,
				$pBind->variable,
				$iLine,
			);

			$aTokens[] = array
			(
				T_OBJECT_OPERATOR,
				'->',
				$iLine,
			);

			$bMethod = true;

			break;
		}

		if(!$bMethod)
		{
			throw new BadFunctionCallException("Function call doesn't exist { {$sMethod}() } in tokenised code.");
		}

		$aTokens[] = array
		(
			T_STRING,
			$sMethod,
			$iLine,
		);

		$aTokens[] = array
		(
			'(',
			'(',
			$iLine,
		);

		foreach($aContext as $aToken)
		{
			$aTokens[] = $aToken;
		}

		$aTokens[] = array
		(
			')',
			')',
			$iLine,
		);

		array_splice($this->aTokeniser, $iStart, $iSpliceLength, $aTokens);
	}


	private function Method(&$iIndex)
	{
		# Retrieve our cast.
		$iStart = $iIndex;

		list(, $sMethod, $iLine) = $this->aTokeniser[$iIndex];

		# Get rid of whitespace to locate our function.
		$sToken = $this->getToken(++$iIndex);

		if($sToken != '(')
		{
			return;
		}

		foreach($this->aBindingInstances as $pBind)
		{
			if(!is_callable(array($pBind->pointer, $sMethod)) && !is_callable(array($pBind->pointer, "__invoke")))
			{
				continue;
			}

			array_splice($this->aTokeniser, $iStart, 1, array
			(
				array
				(
					T_VARIABLE,
					$pBind->variable,
					$iLine,
				),

				array
				(
					T_OBJECT_OPERATOR,
					'->',
					$iLine,
				),

				array
				(
					T_STRING,
					$sMethod,
					$iLine,
				),
			));

			return;
		}

		throw new BadFunctionCallException("Function call doesn't exist { {$sMethod}() } in tokenised code.");
	}
}
