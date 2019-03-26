<?php
require_once("vendor".DIRECTORY_SEPARATOR."autoload.php");

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;




$currentDir =  getcwd(); // where am i , test, validate or test

$features_folder = $currentDir.DIRECTORY_SEPARATOR."features".DIRECTORY_SEPARATOR;

$prediction_model_folder = $currentDir.DIRECTORY_SEPARATOR."predictions.model".DIRECTORY_SEPARATOR;

try {
    
    $logo_dataset = new Phpml\Dataset\CsvDataset($features_folder."logo.csv",34, true);    
    $photos_dataset = new Phpml\Dataset\CsvDataset($features_folder."photos.csv",34, true);
    
    
    $tmp_logo_samples =  $logo_dataset->getSamples();
    $tmp_logo_labels =  $logo_dataset->getTargets();    
	
	$tmp_photos_samples =  $photos_dataset->getSamples();
    $tmp_photos_labels =  $photos_dataset->getTargets(); 
	
	
	$samples_ = array_merge($tmp_logo_samples,$tmp_photos_samples );
	$labels = array_merge($tmp_logo_labels,$tmp_photos_labels );
	// extract only the 
	for($i=0 ; $i<count($samples_); $i++){		
		$samples[$i] = array($samples_[$i][0],$samples_[$i][33], );
	}
	
	$combinedDataset = new Phpml\Dataset\ArrayDataset($samples,$labels);
	$randomSplit = new Phpml\CrossValidation\RandomSplit($combinedDataset, 0.3);      
     
    $training_samples = $randomSplit->getTrainSamples(); 	// get training data
    $test_sample = $randomSplit->getTestSamples();			// get test data
    
    $training_labels = $randomSplit->getTrainLabels();		// get training labels
    $test_labels = $randomSplit->getTestLabels();			// get test labels
    
    list($training_samples, $training_samples_files) = removeFilenames($training_samples);    
    list($test_sample, $test_samples_files) = removeFilenames($test_sample);
    
    //$classifier = new Phpml\Classification\SVC(Kernel::LINEAR, $cost = 1000);
	$classifier = new Phpml\Classification\SVC(Kernel::LINEAR, $cost = 1000);
    $classifier->train($training_samples, $training_labels);    
    
    echo sprintf("%'-50s\n",'');
    echo "Predictions using 32 Bins of normalized grayscale and Sum of grayscale\n";
    echo sprintf("%'-50s\n",'');
    
	$predictedLabels  = array();
    for($i = 0; $i < count($test_sample) ; $i++){
        $logo_class = 0;
        $predictedLabels[$i] = $classifier->predict($test_sample[$i]);
        echo sprintf("%-30s:%s\t:\t%s\n",$test_samples_files[$i],$test_labels[$i],$predictedLabels[$i]);
    }
  
	
	$report = Phpml\Metric\Accuracy::score($test_labels, $predictedLabels);
	
	echo sprintf("%'-50s\n",'');
	echo sprintf("\nAccuracy : %.2f\n",$report);
	
    $new = time();
    $filepath = $prediction_model_folder."model.pixelsum.data";
	$filepath = sprintf("%smodel.pixelsum_%.2f.data",$prediction_model_folder,$report);
    $modelManager = new Phpml\ModelManager();
    $modelManager->saveToFile($classifier, $filepath);
    
} catch (Exception $e) {
    
    echo $e->getMessage().PHP_EOL;
}



/**
remove the file name from the dataset, so that we can list the prediction and the filename
*/
function removeFilenames($pred_samples_){
    
    foreach($pred_samples_ as $a_sample){
        $filename[] = $a_sample[0];
        array_shift($a_sample);
        $pred_samples[] = $a_sample;
    }    
    return array($pred_samples, $filename);  
    
}