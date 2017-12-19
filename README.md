## Machine Learning Using Php, implementing image classification.
Attempt to use supervised machine learning to classify email attachment images as either being logos or photos of damages. 
When emails are being processed, any attachments which are images (png, jpg or gif) can either be logos or be valid images which have to be kept for further processing.
Features about the images have to be obtained and used to train a model which the PHP-ML library can use to make predictions.

* PHP-ML - Machine Learning library for PHP
* pChart - a PHP Charting library 

## Installation
* Create two folders , one to hold logo training sample and one for the photos training sample, 
* Create a subfolder called "images" in each folder.
* Create features from training sets , all features are stored in csv files in the features folder
```bat
php image.creation.php {training sample folder} {target class} > "{features folder}"

For example
echo "Create Features for Logos"
php image.creation.php training.logos 0 > "features\\logo.csv"

echo "Create Features for Photos"
php image.creation.php training.photos 1 > "features\\photos.csv"

echo "Create Features for other data set"
php image.creation.php sample.photos 99 > "features\\samples.csv"
```
* Train model 
```bat
"C:\xampp718\php\php.exe" "predictions\prediction.php"
```

* Validate Model
```bat
"C:\xampp718\php\php.exe" "predictions\prediction.validate.php" "{csv files in the features folder}"

For example
"C:\xampp718\php\php.exe" "predictions\prediction.validate.php"  "logo.csv"
"C:\xampp718\php\php.exe" "predictions\prediction.validate.php"  "photos.csv"
"C:\xampp718\php\php.exe" "predictions\prediction.validate.php"  "samples.csv"
```

*Make a Prediction on images
```bat
"C:\xampp718\php\php.exe" "image.prediction.php" {image file name}
For example
"C:\xampp718\php\php.exe" "image.prediction.php" "C:\Development\logos\misc.images\thumbnail_image3.jpg"
"C:\xampp718\php\php.exe" "image.prediction.php" "C:\Development\logos\misc.images\baffy.jpg"
"C:\xampp718\php\php.exe" "image.prediction.php" "C:\Development\logos\misc.images\pillow.jpg"
```


### PHP-ML - Machine Learning library for PHP
Fresh approach to Machine Learning in PHP. Algorithms, Cross Validation, Neural Network, Preprocessing, Feature Extraction and much more in one library.
PHP-ML requires PHP >= 7.0.

## Official Documentation
Documentation for this library can be found on the [PHP-ML website](http://php-ml.readthedocs.io/en/latest/).


### pChart - a PHP Charting library 
pChart is a PHP library that will help you to create anti-aliased charts or pictures directly from your web server. You can then display the result in the client browser, sent it by mail or insert it into PDFs. pChart provide object oriented coding syntax and is fully in line with the new web standards allowing you to enhance your web2.0 applications. 

Histogram of Logos
![Brisbane Collision Center](https://github.com/deltastateonline/ml-php/blob/master/training.logos/folder.histogram/brisbane.collision.center.png)
![BMW](https://github.com/deltastateonline/ml-php/blob/master/training.logos/folder.histogram/bmw_2.png)

Histogram of Photos
![Photos 1](https://github.com/deltastateonline/ml-php/blob/master/training.photos/folder.histogram/10616_0012.png)
![Photos 2](https://github.com/deltastateonline/ml-php/blob/master/training.photos/folder.histogram/4by4.onwhite.png)