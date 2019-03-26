<?php
require_once("vendor".DIRECTORY_SEPARATOR."autoload.php");
use Phpml\ModelManager;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;


if(count($argv) > 1 ){
	$inputFile = $argv[1];	
}else{
	$inputFile = "validate.csv";
}

try {	
	
	$currentDir =  getcwd(); // where am i , test, validate or test

	$features_folder = $currentDir.DIRECTORY_SEPARATOR."features".DIRECTORY_SEPARATOR;

	$prediction_model_folder = $currentDir.DIRECTORY_SEPARATOR."predictions.model".DIRECTORY_SEPARATOR;
	
    
    $validate_dataset = new Phpml\Dataset\CsvDataset($features_folder.$inputFile,34, true);
    
    $validate_samples =  $validate_dataset->getSamples();
    $validate_labels =  $validate_dataset->getTargets();    
    
    list($validate_samples, $validate_files) = removeFilenames($validate_samples); 
    
	$filepath = $prediction_model_folder."nmodel_0.9821.data";
	
    $modelManager = new ModelManager();

	$classifier = $modelManager->restoreFromFile($filepath);    
    
    echo sprintf("%'-50s\n",'');
    echo "Predictions using 32 Bins of normalized grayscale and Sum of grayscale\n";
	echo $filepath,"\n";
    echo sprintf("%'-50s\n",'');
    
    for($i = 0; $i < count($validate_samples) ; $i++){
        $logo_class = 0;
        $logo_class = $classifier->predict($validate_samples[$i]);
        echo sprintf("%-30s:%s\t:\t%s\n",$validate_files[$i],$validate_labels[$i],$logo_class);
    }
	
} catch (Exception $e) {
    
    echo $e->getMessage().PHP_EOL;
}

function removeFilenames($pred_samples_){
    
    foreach($pred_samples_ as $a_sample){
        $filename[] = $a_sample[0];
        array_shift($a_sample);
        $pred_samples[] = $a_sample;
    }    
    return array($pred_samples, $filename);  
    
}


