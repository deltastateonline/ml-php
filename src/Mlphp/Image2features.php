<?php 
namespace Mlphp;
/**
 * 
 */
class Image2features {
    
    
    var $type = NULL;  
    var $allowedTypes = array(
        1,  // [] gif
        2,  // [] jpg
        3,  // [] png
        6   // [] bmp
    );
    
    var $im = NULL;
    
    var $resizedIm = NULL;
    
    var $imageUrl = NULL;
    
    /**
     * array of string
     *  dirname : dir where the file is located
     *  basename : filename with extension
     *  extension :
     *  filename : filename with no extension
     */
    var $pathInfo = NULL;  
    
    
    var $bins = array();  // use 256 bins for the the entire gray scale  
    var $compressedBins = array();
    
    var $pixel_sum = 0;
   
    var $debug = false;
    
    var $logoClass = NULL;
    
    
    function __construct($imageUrl = NULL,$logoClass = NULL ){
       
        $this->imageUrl = $imageUrl;        
        $this->pathInfo = pathinfo($this->imageUrl);       
        $this->im = $this->imageCreateFromAny($this->imageUrl);
        
        $this->logoClass = $logoClass;
    }
    
    function __destruct(){
        
        if(is_resource($this->im)){
            imagedestroy($this->im);
        }
        
        if(is_resource($this->resizedIm)){
            imagedestroy($this->resizedIm);
        }        
        
    }
    
    function getFileName(){
        
        return $this->pathInfo["basename"];
    }
    
    /**
     *  Resize the image (default 400 by 400)
	 *	create  and save the grayscale from the resized image
	 *	create 256 grayscale bin which represents the features.
	 *	for each pixel in image, push the grayscale value into the appropriate bin.
	 *	reduce the number of features from 256 to 32, by grouping every 8 bins
	 *	then normalize the bin to have values between 0 and 1
	 *	 
     * @param unknown $outputFolder
     * @throws Exception
     */
    function imageFeatures($outputFolder){
        
        
        $this->resizedIm = $this->resizeImage(100,100); // resize the file        
        $this->resizedIm = $this->createGrayScale($this->resizedIm);
        
        $filePath = $outputFolder.DIRECTORY_SEPARATOR.$this->pathInfo["basename"];        
        $filePath = strtolower($filePath);     
        
        $created =	$this->imageSaveAny($filePath,$this->resizedIm);
        
        if($created){            
            Helper::log_message("File created ".realpath($filePath));
        }else{            
            throw new \Exception("File not created {$filePath}.\n");            
        }
        
        $bins = $this->initBin(); // create 256 element array which is intialised with 0        
  
        $resize_w = imagesx($this->resizedIm); // image width
        $resize_h = imagesy($this->resizedIm); // image height    
        
        $this->bins = $this->grayScaleHistogram($this->resizedIm,$bins,$resize_w,$resize_h);        
        
        $validBin = $this->validBin($this->bins);        
        
        $compressed = $this->_compressedBins($this->bins, 8); //   reduced the number of features   from 256 to 32    
        $this->compressedBins = $this->normalize($compressed); // scale all the points to between 0 and 1        
        
        $this->pixel_sum = array_sum($this->compressedBins);
        
        Helper::log_message(sprintf("%-12s:\t%s","Histogram",$validBin));         
        Helper::log_message(sprintf("Normalized pixel sum %s",$this->pixel_sum));        
        Helper::log_message(sprintf("%'-80s",''));       
       
    } 
	
	 /**
     * the method as above but don't create csv file
     * @param unknown $outputFolder
     * @throws Exception
     */
    function imageFeaturesNoOutput(){
        
        
        $this->resizedIm = $this->resizeImage(); // resize the file        
        $this->resizedIm = $this->createGrayScale($this->resizedIm);    
        
        $bins = $this->initBin(); // create 256 element array which is intialised with 0        
  
        $resize_w = imagesx($this->resizedIm); // image width
        $resize_h = imagesy($this->resizedIm); // image height    
        
        $this->bins = $this->grayScaleHistogram($this->resizedIm,$bins,$resize_w,$resize_h);        
        
        $validBin = $this->validBin($this->bins);        
        
        $compressed = $this->_compressedBins($this->bins, 8); //   reduced the number of features   from 256 to 32    
        $this->compressedBins = $this->normalize($compressed); // scale all the points to between 0 and 1        
        
        $this->pixel_sum = array_sum($this->compressedBins);
        
        Helper::log_message(sprintf("%-12s:\t%s","Histogram",$validBin));         
        Helper::log_message(sprintf("Normalized pixel sum %s",$this->pixel_sum));        
        Helper::log_message(sprintf("%'-80s",''));       
       
    } 
	
    /**
     * return a string for the feature
     * @param string $prefix
     * @return string
     */
    public function featuresHeader($prefix = "pixel_"){        
       
       $tmp_ = array_map(function($a) use($prefix) { return $prefix.$a;}, array_keys($this->compressedBins));      
       
       $hd[] = "filename";
       $hd = array_merge($hd, $tmp_);
       $hd[] = "pixel_sum";
       $hd[] = "feature_class";
       
       return implode(",",$hd );
       
    }
    
    
    /**
     * 
     * @param resource $im
     * @throws Exception
     * @return resource
     */

    function createGrayScale($im){       
      
        if($im && imagefilter($im, IMG_FILTER_GRAYSCALE)){           
            return $im;            
        }else{                
            throw new \Exception("Conversion to grayscale failed: {$this->imageUrl}.\n");
        }        
    }    
    
    /**
     * Create the image resource from the given file path,
     * The image resource has to be created based on he different file type
     * 
     * @param string $filepath
     * @throws Exception
     * @return boolean
     */
    function imageCreateFromAny($filepath) {
        $this->type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
       
        if (!in_array($this->type, $this->allowedTypes)) {            
            throw new \Exception("Invalid file type : {$this->type} from {$filepath}.\n");            
        }
        
        switch ($this->type) {
            case 1 :
                $im = imageCreateFromGif($filepath);
                break;
            case 2 :
                $im = imageCreateFromJpeg($filepath);
                break;
            case 3 :
                $im = imageCreateFromPng($filepath);
                break;
            case 6 :
                $im = imagecreatefromwbmp ($filepath);
                break;
        }
        
        $this->w = imagesx($im); // image width
        $this->h = imagesy($im); // image height      
        
        return $im;
    }
    
    
    /**
     * Given an image resource, save the resource by type to a file
     * @param string $filepath
     * @param resource $im
     * @return boolean
     */
    function imageSaveAny($filepath,$im ) {
        
        $created = FALSE ;
        
        switch ($this->type) {
            case 1 :
                $created = imagegif ($im,$filepath);
                break;
            case 2 :
                $created = imagejpeg ($im,$filepath);
                break;
            case 3 :
                $created =imagepng($im,$filepath);
                break;
            case 6 :
                $created =image2wbmp($im,$filepath);
                break;
        }
        
        return $created;
    } 
    
    
    /**
     * Resize an image and keep the proportions
     * @author Allison Beckwith <allison@planetargon.com>
     * @param string $filename
     * @param integer $max_width
     * @param integer $max_height
     * @return boolean.
     */
    function resizeImage($max_width = 400, $max_height = 400)
    {      
        
        $width = $this->w;
        $height = $this->h;
        
        # taller
        if ($height > $max_height) {
            $width = ($max_height / $height) * $width;
            $height = $max_height;
        }
        
        # wider
        if ($width > $max_width) {
            $height = ($max_width / $width) * $height;
            $width = $max_width;
        }
        
        if($height < 1 ){ $height = $this->h; }        
        if($width < 1 ){ $width = $this->w; }
        
       
        $im = imagecreatetruecolor($width, $height);   
        
        if(!is_resource($im)){
            throw new \Exception("Unable to resize file : {$this->imageUrl}.\n");
        }        
          
        imagecopyresampled($im, $this->im, 0, 0, 0, 0,
            $width, $height, $this->w, $this->h);
        
        return $im;
        
    }   
   
    
 /**
  * 
  * @param number $binSize
  * @return array
  */
    private function initBin($binSize = 256){        
        return array_fill ( 0 , $binSize , 0 );      
    }
    
    
    private function grayScaleHistogram($im , $bins , $w, $h){    
        
        $binItem = 1.0 / ($w * $h);
        for($i = 0; $i<$w; $i++){            
            for($j = 0; $j<$h;$j++){
                $cIndex = imagecolorat($im, $i,$j);                
                if($cIndex > 255){
                    $cIndex = $cIndex % 255; // fix a weird bug
                }                
                $bins[$cIndex] = $bins[$cIndex] + $binItem;
            }
        }        
        return $bins;      
    }

 
    private function validBin($bins){        
        return  array_sum($bins);
    }
    
    
    /**
	 * there are other ways of doing this, you can use array chunk 
	 * 
	 * @param array $bins
	 * @param number $step
	 * @return number[]
	 */
    private function _compressedBins($bins, $step = 1){
        
        $count = count($bins) ;        
        $compressedBins = array();        
        $i = 0;
        while($i <  $count){					// start at 0
            $x = 0;            
            $end = $i + $step;					// we shall stop at 8
            //for($j = 0; $j < $step; $j++){
            for($j = $i; $j < $end; $j++){		// loop from 0 to 7                   
                $x = $x + $bins[$j];			// sum each
            }            
            $compressedBins[] = $x;				// add the sum to the compressed bin
            $i = $i + $step;					// move pointer to index 8
        }        
        return $compressedBins;        
    }
    
    /**
	*	find max and min,
	*	then scale all the values to 0 to 1
	*/
    private function normalize($bins){       
        
        $max = max($bins);
        $min = min($bins);      
        
        $fraction =  1.0 / ($max - $min);
        
        return array_map(function($a) use ($fraction , $min) { return ($a - $min) * $fraction ; }, $bins);
        
    }
    
    /**
     *  craete a string, which can be written to a file
     * @return string
     */
    
    public function __toString(){
        return strtolower($this->pathInfo["basename"]).",".implode(",",$this->compressedBins).",".$this->pixel_sum.",".$this->logoClass;
    }
	
	/**
	*
	*/
	public function toFeatures(){
		
		$a[] = strtolower($this->pathInfo["basename"]);		
		$a = array_merge($a,$this->compressedBins );
		$a[] = $this->pixel_sum;
		$a[] = $this->logoClass;		
		return $a;
	}
    
    /**
     * 
     * @param string $outputFolder
     */
    public function writeFeatures2Csv($outputFolder){
        
        $filePath = $outputFolder.DIRECTORY_SEPARATOR.$this->pathInfo["filename"].".csv";
        $filePath = strtolower($filePath);     
        
        $fpath = "";
        $fp = fopen($filePath, 'w');
        
        fputcsv($fp, array("Grey","Hits"));
        
        for($i=0; $i<count($this->compressedBins); $i++){            
            fputcsv($fp, array($i,$this->compressedBins[$i]));            
        }
        fclose($fp);
        
        return TRUE;
    
    }
}

?>