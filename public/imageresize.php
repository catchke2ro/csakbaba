<?php

$folder = __DIR__.'/upload/product';

$files = scandir($folder);

/*$file = '1492667722_58f84d4ab2861.jpg';
$file = '1396941881_5343a439de517.jpg';

$image = new Imagick($folder.'/'.$file);

$image->resizeImage(1500, 1500, Imagick::FILTER_LANCZOS, 1, true);

$image->writeImage($folder.'/zzz.jpg');
die();*/

$count = 0;
foreach($files as $file) {
	if($file == '.' || $file == '..') {
		continue;
	}
	
	$image = new Imagick($folder.'/'.$file);
	if($image->getImageHeight() > 1900 || $image->getImageWidth() > 1900) {
		$image->resizeImage(1800, 1800, Imagick::FILTER_LANCZOS, 1, true);
		
		$image->writeImage($folder.'/'.$file);
		echo '|';
		$count ++;
	} else {
		echo '.';
	}
	
	
}
echo $count.' files resized';