<?php 
class Autoloader
{
    public static function Register() {
        return spl_autoload_register(array('Autoloader', 'Load'));
    }

    public static function iterate($dir,$strObjectName){
    	
	    $iterator = new DirectoryIterator($dir);
	    
	    foreach ($iterator as $fileinfo) {
	        //var_dump($fileinfo);
	        
	        if ($fileinfo->isDir() && !$fileinfo->isDot() ) {
	            //echo $fileinfo->getPathname()."<br>";
	            Autoloader::iterate($fileinfo->getPathname(),$strObjectName);
	            
	        }elseif ($fileinfo->isFile()) {
	        	//echo "!!!!".$strObjectName.'.php == '.$fileinfo->getFilename()."<br>";
	        	if($strObjectName.'.php' == $fileinfo->getFilename() ){
	        		require($fileinfo->getPathname());
	        		//echo "***".$fileinfo->getPathname()."<br>";
	        		return true;
	        	}

	        	//echo "---".$fileinfo->getPathname()."<br>";

	    
	        }
	    }

	    return false;      

	}

    public static function Load($strObjectName) {
        
        if(class_exists($strObjectName)) {
            return false;
        }


        $strObjectFilePath = 'src/' . str_replace("\\", "/", $strObjectName) . '.php';
        
        //echo $strObjectFilePath;

        if((file_exists($strObjectFilePath)) && (is_readable($strObjectFilePath))) {
            require($strObjectFilePath);
            return true;

        }else{
			$directory = "src";
			return Autoloader::iterate($directory,$strObjectName);
        }
        
    }
}
?>