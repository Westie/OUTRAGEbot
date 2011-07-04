<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     0ff759c76277e70602a53631b1c787f238d39a59
 *	Committed at:   Mon Jul  4 21:07:34 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleAutoUpdater
{
	/**
	 *	Called when the module is loaded.
	 */
	static function initModule()
	{
		$sBleedingEdge = "https://github.com/Westie/OUTRAGEbot/zipball/master";

		self::processZipFile($sBleedingEdge);
	}


	/**
	 *	Download the ZIP file to the Resources directory.
	 */
	static function processZipFile($sURL)
	{
		# Prepare the variables
		$sZipResource = ROOT."/Resources/AutoUpdater.zip";
		$sZipFolder = ROOT."/Resources/AutoUpdaterFolder";
		$sSystemFolder = ROOT."/System/";

		# Download the file
		$sZip = file_get_contents($sURL);

		file_put_contents($sZipResource, $sZip);

		$pZip = new ZipArchive();

		$pZip->open($sZipResource);
		$pZip->extractTo($sZipFolder);

		unlink($sZipResource);

		# What is the folder name?
		$aZipFolder = glob(ROOT."/Resources/AutoUpdaterFolder/*");

		# Move all the system files
		self::rmdir($sSystemFolder);
		rename("{$aZipFolder[0]}/System/", $sSystemFolder);

		# Move the Start.php
		unlink(ROOT.'/Start.php');
		rename("{$aZipFolder[0]}/Start.php", ROOT.'/Start.php');

		# And now, clean up our mess.
		self::rmdir($sZipResource);
		unlink($aZipFolder[0]);

		println(" > Successfully updated the System files.");
	}


	/**
	 *	Recursively removes a folder.
	 */
	static function rmdir($sDirectory)
	{
		if(is_dir($sDirectory))
		{
			$pObjects = scandir($sDirectory);
			foreach($pObjects as $pObject)
			{
				if($pObject != "." && $pObject != "..")
				{
					if(filetype($sDirectory."/".$pObject) == "dir")
					{
						self::rmdir($sDirectory."/".$pObject);
					}
					else
					{
						unlink($sDirectory."/".$pObject);
					}
				}
			}

			reset($pObjects);
			rmdir($sDirectory);
		}
	}
}
