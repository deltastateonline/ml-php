<?php
/**
 * @author Agbagbara Omokhoa
 * @email nimble@deltastateonline.com
 */

require('vendor/autoload.php');


define('ADJDEBUG' , FALSE);


$prefix = "logos";
$logoClass = 0; // we shall use 0 = is logo and 1 is photo

if(count($argv) > 1 ){
	$prefix = $argv[1];
	
	if(!empty($argv[2])){
	    $logoClass = (int)$argv[2];
	}
}

if(!@chdir($prefix)){
	echo "Unable to change directory to {$prefix}\n"; // change to the required dir
	return;
}

$currentDir =  getcwd(); // where am i , logos, validate or test


$folder = "images".DIRECTORY_SEPARATOR; // i need to get all iamges.
$gray_folder = "folder.gray";  // put all the grayscaled images here
$cvs_folder = "folder.csv"; // write the csv files here
$histogram="folder.histogram"; // write all the histograms here


Mlphp\Helper::createFolders($currentDir,$gray_folder);
Mlphp\Helper::createFolders($currentDir,$cvs_folder);
Mlphp\Helper::createFolders($currentDir,$histogram);


$allFiles = glob($folder."*.*", GLOB_NOSORT); // find all images
$hashValue = "*";

$output_ = array();

$writeHeader = TRUE;
$i = 0;

foreach( $allFiles as $image){    
    
    $anImage = NULL;
    
     try {
         $anImage = new Mlphp\Image2features($image,$logoClass);  
         $anImage->imageFeatures($gray_folder); 
         
         if($writeHeader){ // write the header for the csv file the very first time you loop thru
             $finalString[] =  $anImage->featuresHeader();
             $writeHeader = FALSE;           
         }        
            
         $finalString[] = (string)$anImage;       
         
         $anImage->writeFeatures2Csv($cvs_folder); // write the output to a file so that you can render a histogram 
         echo "Process - ",$i++,"\r";
         unset($anImage);
         
     } catch (Exception $e) {
         echo $e->getMessage();
     }   
     
     unset($anImage);  
}

// output the content you can redirect the output to a file

$fp = fopen($prefix.".csv","w");
fwrite($fp , implode(PHP_EOL,$finalString));
fclose($fp);

echo "Done \n";

