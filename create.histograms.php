<?php
/**
 * Render the csv file as an histogram
 * @var unknown
 */

define('FPATH' , __DIR__.DIRECTORY_SEPARATOR."libs".DIRECTORY_SEPARATOR."pChart.2");

require_once("classes".DIRECTORY_SEPARATOR."helper.php");
include(FPATH.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."pDraw.class.php"); 
include(FPATH.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."pImage.class.php"); 
include(FPATH.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."pData.class.php"); 


define('ADJDEBUG' , TRUE);


$prefix = "logos";
if(count($argv) > 1 ){
	$prefix = $argv[1];
}

chdir($prefix); // change to the required dir
$currentDir =  getcwd(); // where am i , test, validate or test

$folder = "folder.csv".DIRECTORY_SEPARATOR;

$output_folder  = "folder.histogram".DIRECTORY_SEPARATOR;

//$outputDir = $currentDir.DIRECTORY_SEPARATOR.$outputKey;



$allFiles = glob($folder."*.*", GLOB_NOSORT);

foreach( $allFiles as $aFile){	
    
    $pInfo = pathinfo($aFile);
    $fPath = realpath($output_folder);	// get thhe path to file
		
    $newPath = $fPath.DIRECTORY_SEPARATOR.strtolower($pInfo['filename']).".png"; // rename to .csv first
	
	$myData = new pData(); 
	$Options["GotHeader"] = TRUE;
	$Options["SkipColumns"] = array(0);
	$Options["DefaultSerieName"] = strtolower($pInfo['filename']);
	$myData->importFromCSV($aFile,$Options);	
	
	$myData->loadPalette(FPATH.DIRECTORY_SEPARATOR."palettes/blind.color",TRUE);
		
	/* Create a pChart object and associate your dataset */ 
	 $myPicture = new pImage(700,230,$myData);
	 
	 
	 $myPicture->setFontProperties(array("FontName"=>FPATH."/fonts/verdana.ttf","FontSize"=>11));
	 $myPicture->drawText(280,30,strtolower($pInfo['filename']),array("R"=>32,"G"=>32,"B"=>32));	
	 
	 /* Choose a nice font */
	 $myPicture->setFontProperties(array("FontName"=>FPATH."/fonts/Forgotte.ttf","FontSize"=>11));
	

	 /* Define the boundaries of the graph area */
	 $myPicture->setGraphArea(60,40,670,190);	 

	 /* Draw the scale, keep everything automatic */ 
	 $scaleSettings = array("DrawSubTicks"=>FALSE,'RemoveXAxis'=>TRUE);
	 $myPicture->drawScale($scaleSettings);	

	 /* Draw the scale, keep everything automatic */ 	 
	 $myPicture->drawBarChart(array("DisplayValues"=>FALSE,"DisplayColor"=>DISPLAY_AUTO,"Rounded"=>FALSE,"Surrounding"=>NULL,"OverrideSurrounding"=>FALSE,"Interleave"=>0));

 
	$myPicture->autoOutput($newPath); 
	 
	unset($myData);
	unset($myPicture); 
	
}