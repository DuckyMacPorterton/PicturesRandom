<?php


function PicturesRandom ($strTargetDir, $strSourceDir, $nSpaceReallyFullK)
{
	//
	//  1) Figure out how much space we have to fill with pictures.
	//  2) Fill it.

	$nTriesToFindFileThatFits 	= 10;	// At the end, when space gets tight, try this hard to find another file
	$nSpaceAvailable 			= disk_free_space ($strTargetDir);
	$nSpaceReallyFullK			*= 1024;	//  Keep 100k free at the end

	//
	//  First let's build an enormous list of every file under our structure

	$arrFilesAvailable = array ();
	if (!ReadAllFilenames ($arrFilesAvailable, $strSourceDir))
		return false;

	echo "Free space to fill  : " . number_format ($nSpaceAvailable / (1024*1024)) . " MB\n";
	echo "Files to choose from: " . number_format (sizeof ($arrFilesAvailable)) . "\n\n";

	if ($strTargetDir[strlen ($strTargetDir) - 1] != '\\')
		$strTargetDir .= "\\";

	//
	//  Now choose those files

	$nCopied = 0;
	while ($nSpaceAvailable > $nSpaceReallyFullK && $nTriesToFindFileThatFits > 0)
	{
		//
		//  Pick a random file

		$nToCopy = rand () % sizeof ($arrFilesAvailable);
		if ($arrFilesAvailable[$nToCopy] == false)
			continue;

		//
		//  See if that file will fit on our target

		$nSizeOfFileToCopy = filesize ($arrFilesAvailable[$nToCopy]);
		if ($nSizeOfFileToCopy > ($nSpaceAvailable - $nSpaceReallyFullK))
		{
			//
			//  Won't fit.  Don't copy this one.

			$arrFilesAvailable[$nToCopy] = false;
			$nTriesToFindFileThatFits --;
			continue;
		}

		//
		//  Copy it and make it so we won't try to copy it again later

		if (! copy ($arrFilesAvailable[$nToCopy], $strTargetDir . basename ($arrFilesAvailable[$nToCopy])))
		{
			echo "Failed to copy: " . $arrFilesAvailable[$nToCopy] . " to " . $strTargetDir . basename ($arrFilesAvailable[$nToCopy]) . "\n";
			$nTriesToFindFileThatFits --;
			$arrFilesAvailable[$nToCopy] = false;
			continue;
		}
		else
			$nTriesToFindFileThatFits = 10;

		$arrFilesAvailable[$nToCopy] = false;
		$nSpaceAvailable -= $nSizeOfFileToCopy;

		//
		//  Make sure the user doesn't get bored

		$nCopied ++;
		if ($nCopied % 50 == 0) {
			echo "Copied $nCopied files - Space Free: " . number_format ($nSpaceAvailable / (1024*1024)) . " MB\n";
		}
	} // end picture chooser loop

	echo "\nAll done! Copied $nCopied files\n";
	return true;
} // end pictures random



//////////////////////////////////////////////////////////
///
///   Recursively grab all the files
///
///
function ReadAllFilenames (&$rarrFilesAvailable, $strSourceDir)
{
	$dir = new RecursiveDirectoryIterator ($strSourceDir);
	$iter = new RecursiveIteratorIterator ($dir, RecursiveIteratorIterator::SELF_FIRST);

	foreach ($iter as $path)
	{
		// skip unwanted directories
		if(!$iter->isDot()) {
			if($iter->isDir()) {
				echo "Found " . sizeof ($rarrFilesAvailable) . " files\n";
				// output linked directory along with the number of files contained within
				// for example: some_folder (13)
			}
			else
			{
				$rarrFilesAvailable[] = $path->__toString ();
			}
		}
	}

	return true;
} // end read all files



//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////

if ($argc < 3) die ("Usage: {$argv[0]} TargetDir SourceDir [KeepFreeBufferInKBytes]");

if (isset ($argv[3]))
	$nSpaceReallyFull = argv[3];
else
	$nSpaceReallyFull = 100;

PicturesRandom ($argv[1], $argv[2], $nSpaceReallyFull);


