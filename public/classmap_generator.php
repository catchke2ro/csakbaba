<?php
$thisPath=dirname(__FILE__);
include $thisPath.'/../library/CB/global_functions.php';
include $thisPath.'/../library/Zend/File/ClassFileLocator.php';
function createMap(Iterator $i, $map) {
	$file      = $i->current();
	$namespace = empty($file->namespace) ? '' : $file->namespace . '\\';
	$filename  = $file->getRealpath();
	$map->{$namespace . $file->classname} = $filename;
	return true;
}

function classMapGenerator($libs=array(), $outputFile=''){
	ini_set('display_errors', 1);
	$thisPath=dirname(__FILE__);

	$libPath=$thisPath.'/../library/';

	$iterator['Library']=new Zend_File_ClassFileLocator($libPath);
	$map=new stdClass;

	foreach($libs as $lib){
		iterator_apply($iterator[$lib], 'createMap', array($iterator[$lib], $map));
	}

	$content = "<?php\n"."return ".var_export((array) $map, true).";";

	file_put_contents($outputFile, '');
	file_put_contents($outputFile, $content);

}

classMapGenerator(array('Library'), dirname(__FILE__).'/../library/'.'classmap.php');
