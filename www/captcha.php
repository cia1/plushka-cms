<?php
define('WIDTH',130);
define('HEIGHT',40);
define('LETTER_COUNT',4);
define('OFFSET_PERCENT',5);
define('MIN_CONTRAST_COLOR',70); //0-125

$matrix=array(
	'1'=>array(
		array(30,45,85,5),
		array(85,5,85,85)
	),
	'2'=>array(
		array(15,15,85,15),
		array(85,15,15,50),
		array(15,50,15,85),
		array(15,85,85,85)
	),
	'3'=>array(
		array(15,15,85,15),
		array(85,15,20,50),
		array(20,50,85,85),
		array(85,85,15,85)
	),
	'4'=>array(
		array(15,15,15,50),
		array(15,50,85,50),
		array(85,15,85,85)
	),
	'5'=>array(
		array(85,15,15,15),
		array(15,15,15,35),
		array(15,35,85,35),
		array(85,35,85,85),
		array(85,85,15,85)
	),
	'6'=>array(
		array(85,15,15,15),
		array(15,15,15,85),
		array(15,85,85,85),
		array(85,85,85,50),
		array(85,50,15,50)
	),
	'7'=>array(
		array(15,15,85,15),
		array(85,15,85,85)
	),
	'8'=>array(
		array(15,15,85,15),
		array(85,15,85,85),
		array(85,85,15,85),
		array(15,85,15,15),
		array(15,50,85,50)
	),
	'9'=>array(
		array(15,15,85,15),
		array(85,15,85,85),
		array(85,85,15,85),
		array(15,15,15,50),
		array(15,50,85,50)
	),
	'0'=>array(
		array(15,15,85,15),
		array(85,15,85,85),
		array(85,85,15,85),
		array(15,85,15,15)
	)
);

$r=rand('1'.str_repeat('0',LETTER_COUNT-1),str_repeat('9',LETTER_COUNT));
session_start();
$_SESSION['captcha']=$r;

for($i=0;$i<LETTER_COUNT;$i++) $string[$i]=substr($r,$i,1);
$letterWidth=(int)(WIDTH/LETTER_COUNT);
$_letterWidth=$letterWidth/100;
$_letterHeight=HEIGHT/100;
$bgColor=array(rand(0,255),rand(0,255),rand(0,255));
$_offset=OFFSET_PERCENT*1000;

$im=imagecreate(WIDTH,HEIGHT);
imagecolorallocate($im,$bgColor[0],$bgColor[1],$bgColor[2]);

for($i=0;$i<LETTER_COUNT;$i++) {
	//Подбор цвета
	$color=array();
	for($y=0;$y<3;$y++) {
		do {
			$c=rand(0,255);
		} while(abs($bgColor[$y]-$c)<MIN_CONTRAST_COLOR);
		$color[$y]=$c;
	}
	$color=imagecolorallocate($im,$color[0],$color[1],$color[2]);
	$_x=$letterWidth*$i;
	foreach($matrix[$string[$i]] as $item) {
		$x1=$_letterWidth*$item[0]+$_x+$_letterWidth*rand($_offset*-1,$_offset)/1000;
		$y1=$_letterHeight*$item[1]+$_letterHeight*rand($_offset*-1,$_offset)/1000;
		$x2=$_letterWidth*$item[2]+$_x+$_letterWidth*rand($_offset*-1,$_offset)/1000;
		$y2=$_letterHeight*$item[3]+$_letterHeight*rand($_offset*-1,$_offset)/1000;
		imageline($im,$x1,$y1,$x2,$y2,$color);
	}
}
header('Content-type: image/png');
imagepng($im);