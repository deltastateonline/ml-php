<?php
require_once("vendor/autoload.php");

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

$currentDir =  getcwd(); // where am i , test, validate or test
$features_folder = $currentDir.DIRECTORY_SEPARATOR."features".DIRECTORY_SEPARATOR;
$prediction_model_folder = $currentDir.DIRECTORY_SEPARATOR."predictions.model".DIRECTORY_SEPARATOR;

try {
    
    $logo_dataset = new Phpml\Dataset\CsvDataset($features_folder."logo.csv",34, true);  // load logo features
    $photos_dataset = new Phpml\Dataset\CsvDataset($features_folder."photos.csv",34, true); //// load photos features
    
    $tmp_logo_samples =  $logo_dataset->getSamples(); // samples from col 1 to col 33
    $tmp_logo_labels =  $logo_dataset->getTargets();    // label is at col 34
	
	$tmp_photos_samples =  $photos_dataset->getSamples();// samples from col 1 to col 33
    $tmp_photos_labels =  $photos_dataset->getTargets(); // label is at col 34
	echo "\n";
	for($j = 0 ; $j < 20 ; $j++){
	
		$samples = array_merge($tmp_logo_samples,$tmp_photos_samples ); // merge logo and photos samples
		$labels = array_merge($tmp_logo_labels,$tmp_photos_labels ); // merge photos and logo labels
		
		$combinedDataset = new Phpml\Dataset\ArrayDataset($samples,$labels); // create a new dataset
		$randomSplit = new Phpml\CrossValidation\RandomSplit($combinedDataset, 0.3);  // create validation sets    
		 
		$training_samples = $randomSplit->getTrainSamples(); 	// get training data
		$test_sample = $randomSplit->getTestSamples();			// get test data
		
		$training_labels = $randomSplit->getTrainLabels();		// get training labels
		$test_labels = $randomSplit->getTestLabels();			// get test labels
		
		list($training_samples, $training_samples_files) = removeFilenames($training_samples); // remove the filename from the dataset   
		list($test_sample, $test_samples_files) = removeFilenames($test_sample); // remove the file name from the dataset
		
		//$classifier = new Phpml\Classification\SVC(Kernel::LINEAR, $cost = 1000);	
		$classifier = new SVC(Kernel::RBF, $cost = 1000, $degree = 3, $gamma = 6);
		$classifier->train($training_samples, $training_labels);  	
		
		$predictedLabels  = array();
		for($i = 0; $i < count($test_sample) ; $i++){
			$logo_class = 0;
			$predictedLabels[$i] = $classifier->predict($test_sample[$i]);
		}		
		$report = Phpml\Metric\Accuracy::score($test_labels, $predictedLabels); // calculate the accuracy		
		echo sprintf("Accuracy : %.4f\n",$report); // 
		
		$filepath = sprintf("%smodel_%.4f.data",$prediction_model_folder,$report);
		$modelManager = new Phpml\ModelManager();
		$modelManager->saveToFile($classifier, $filepath); // store classifier based on the accuracy.
	}
    
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