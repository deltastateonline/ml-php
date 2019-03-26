<?php
/**
* Performs the prediction on a single file
 * @author Agbagbara Omokhoa
 * @email nimble@deltastateonline.com
 */
require_once("predictions".DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."autoload.php");
require_once("classes".DIRECTORY_SEPARATOR."helper.php");
require_once("classes".DIRECTORY_SEPARATOR."image2features.php");

use Phpml\ModelManager;
define('ADJDEBUG' , FALSE);

$writeHeader = TRUE;
$logoClass = 0; // we shall use 0 = is logo and 1 is photo

$currentDir =  getcwd(); 
$prediction_model_folder = $currentDir.DIRECTORY_SEPARATOR."predictions.model".DIRECTORY_SEPARATOR;

 try {

	if(count($argv) <= 1 ){	
		throw new Exception("Input file required");  
	}
	
	$filename = $argv[1];	
	if(!realpath($filename)){
		throw new Exception("Valid Input file required : {$filename}");  
	}
	
	\log_message("Input file ".realpath($filename));	
	
	 $anImage = new image2features($filename,$logoClass);  
     $anImage->imageFeaturesNoOutput(); 
	 
	 $predictionFeatures = $anImage->toFeatures();
	 
	 if($writeHeader){
		 $finalString[] =  $anImage->featuresHeader();
		 $writeHeader = FALSE;           
	 }        
            
         $finalString[] = (string)$anImage; 
	 
	 $t = implode(PHP_EOL,$finalString);
	 \log_message($t);
	 
	 
	$filepath = $prediction_model_folder."nmodel_0.9821.data";
    $modelManager = new ModelManager();

	$classifier = $modelManager->restoreFromFile($filepath);
	$logo_class = $classifier->predict(array_slice($predictionFeatures,1));
	
	echo sprintf("%-20s:\t %s %s",$predictionFeatures[0],($logo_class == 0)?"LOGO":"PHOTO",PHP_EOL);
	
	
	
} catch (Exception $e) {
    echo $e->getMessage();
}   
     