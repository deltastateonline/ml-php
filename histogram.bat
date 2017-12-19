ECHO OFF

echo "Create Features for Logos"
php create.histograms.php logos 

echo "Create Features for Photos"
php create.histograms.php photos 

echo "Create Features for Validation"
php create.histograms.php validate 

echo "Create Features for Perth Data"
php create.histograms.php sample.perth 

echo "Create Features for emailphotos Data"
php create.histograms.php emailphotos 


php create.histograms.php sample.reactive
