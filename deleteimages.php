<?php

$validimages = include "validimages.php";

$images = scandir(__DIR__.'/public/upload/product');

$allImagesCount = count($images);
$validImagesCount = count($validimages);
echo "Count of all images: $allImagesCount\n";
echo "Count of valid images: $validImagesCount\n";

$invalidImages = array();
foreach($images as $image){
	if($image == '.' || $image == '..') continue;
	
	if(in_array($image, $validimages)) continue;
	$invalidImages[] = $image;
}

$invalidImagesCount = count($invalidImages);
echo "Count of invalid images: $invalidImagesCount\n";

foreach($invalidImages as $invalidImage){
	
	if(file_exists(__DIR__.'/public/upload/product/'.$invalidImage)){
		unlink(__DIR__.'/public/upload/product/'.$invalidImage);
		echo "\tFile deleted: $invalidImage\n";
	}
	
}

