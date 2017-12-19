<?php
function createFolders($currentDir, $outputKey){
    
     
    $outputDir = $currentDir.DIRECTORY_SEPARATOR.$outputKey;
    
    if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) {
        echo "Unable to Create New Directory:".$outputDir."\n";
        
        // throw error
        return ;
    }
}

/**
 * print out a time stamped message
 * @param string $string
 */

function log_message($string){
    
    if(ADJDEBUG){        
        echo sprintf("%s : %s%s",date("Y-m-d H:i:s"),$string,PHP_EOL);
    }
    
}