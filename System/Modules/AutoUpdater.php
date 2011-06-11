<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     a53ca6c5bfdf712e6df4b62e5003c18fa157b2d7
 *	Committed at:   Sat Jun 11 22:17:11 BST 2011
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
		$sSystemFolder = ROOT."/System";

		# Download the file
		$sZip = file_get_contents($sURL);

		file_put_contents($sZipResource, $sZip);

		$pZip = new ZipArchive();

		$pZip->open($sZipResource);
		$pZip->extractTo($sZipFolder);

		unlink($sZipResource);

		# What is the folder name?
		$aZipFolder = glob(ROOT."/Resources/AutoUpdaterFolder/*");

		# Move all the system files (that's all we will replace!)
		self::rmdir($sSystemFolder);
		rename("{$sZipFolder}/{$aZipFolder[0]}/System", $sSystemFolder);

		# And now, clean up our mess.
		self::rmdir($sZipResource);

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
