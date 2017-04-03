<?
session_start();

if(!(!empty($_SESSION['Zend_Auth']) && !empty($_SESSION['Zend_Auth']['storage']) && !empty($_SESSION['Zend_Auth']['storage']['ckupload']))) die('ERR');

if(!empty($_FILES)){
    $filename = time().'_'.$_FILES['file']['name'];
	move_uploaded_file($_FILES['file']['tmp_name'], __DIR__.'/../../../../upload/content/'.$filename);

	$file='/upload/content/'.$filename;

}
?>

<html>
	<head>
		<title>File feltöltés</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
	</head>
	<style type="text/css">
		input{
			margin: 20px auto 10px;
			display: block;
			width: 50%;
			border: 1px solid darkslategray;
			background: white;
			line-height: 2rem;
			font-size: 1rem;
		}
		input[type=submit]{
			cursor: pointer;
			font-weight: bold;
		}
		input[type=submit]:hover {
			background-color: #000066;
			color: #ffffff;
		}
		div.current{
			width: 100%;
			height: 200px;
			text-align: center;
		}
		div.current img {
			height: 100%;
			cursor: pointer;
			opacity: 0.8
		}
		div.current img:hover {
			opacity: 1;
		}
	</style>

	<script type="text/javascript" src="/js/jquery/jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$('div.current img').click(function(){
				window.opener.CKEDITOR.tools.callFunction(<?=$_GET['CKEditorFuncNum'];?>, $(this).attr('src'));
				window.close();
			});
		});
	</script>
	<body>
		<form action="" method="post" enctype="multipart/form-data">
			<input name="file" type="file"/>
			<input type="submit" value="FELTÖLTÉS"/>
		</form>
		<div class="current">
			<? if(isset($file)){?>
				<p>Kattints a képre a kiválasztáshoz</p>
				<img src="<?=$file;?>" />
			<? } ?>
 		</div>
	</body>
</html>