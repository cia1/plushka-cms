<?php
define('WIDTH',130);
define('HEIGHT',40);
define('FONT_SIZE',14);
$r=rand(10000,99999);
session_start();
$_SESSION['captcha']=$r;
for($i=0;$i<7;$i++) $arr[$i]=substr($r,$i,1);
$im=imagecreate(WIDTH,HEIGHT);
imagecolorallocate($im,255,255,255);
$aInc=(int)(WIDTH/7);
$a=0;
for($i=0;$i<7;$i++) {
	$color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
	imagettftext($im,FONT_SIZE,rand(-50,50),$a+=$aInc,rand(15,25),$color,dirname(__FILE__).'/data/captcha.ttf',$arr[$i]);
}
header('Content-type: image/png');
imagepng($im);
?>