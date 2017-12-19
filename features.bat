ECHO OFF

echo "Create Features for Logos"
php image.creation.php training.logos 0 > "features\\logo.csv"

echo "Create Features for Photos"
php image.creation.php training.photos 1 > "features\\photos.csv"

echo "Create Features for Validation"
php image.creation.php validate 999 > "features\\validate.csv"

echo "Create Features for Perth Data"
php image.creation.php perth 998 > "features\\perth.csv"

php image.creation.php emailphotos 1 > "features\\emailphotos.csv"

php image.creation.php sample.reactive 0 > "features\\reactive.csv"
