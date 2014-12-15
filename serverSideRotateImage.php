<?php 

	require_once('class.authentification.php');
	require_once('class.util.php');
	
	$auth = authentification::instance();
	$imagePath = util::getParam($_GET, 'imagePath');
	$angle = util::getParam($_GET, 'angle');
		
	if(!$auth->estIdentifie() || !$auth->isUserAdmin() )
	{
		header('Location: index.php');
		exit(0);
	}
	
	if(!isset($angle) || !isset($imagePath))
	{
		exit(0);
	}
	
	if(file_exists($imagePath))
	{
		$ext =   strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
		
		header("Content-type: image/".$ext."") ;
		
		if($ext == 'gif' )
			rotateGifImage($angle, $imagePath);
		else if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'pjpeg')
			rotateJpegImage($angle, $imagePath);
		else if($ext == 'png' || $ext == 'x-png')
			rotatePngImage($angle, $imagePath);
		else
		{			
			
		}	
		
		imagedestroy($image);
	}
	else 
	{
		
	}
	
	function rotateJpegImage($angle, $imagePath)
	{
		$image = imagecreatefromjpeg($imagePath); 
		$image = rotateImage($image, $angle);
		imagejpeg($image);	
	}
	
	function rotateGifImage($angle, $imagePath)
	{
		$image = imagecreatefromgif($imagePath);
		$image = rotateImage($image, $angle);
		
		// Turn off alpha blending and set alpha flag
		imagealphablending($image, false);
		imagesavealpha($image, true);
		
		imagegif($image);
	}
	
	function rotatePngImage($angle, $imagePath)
	{	
		$image = imagecreatefrompng($imagePath);
		$image = rotateImage($image, $angle);
		
		// Turn off alpha blending and set alpha flag
		imagealphablending($image, false);
		imagesavealpha($image, true);
		
		imagepng($image);
	}
	
	function rotateImage($image, $angle)
	{
		return imagerotate($image, $angle, 0, 0);
	}

?>