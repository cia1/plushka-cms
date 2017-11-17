<?php
$config=array(
'smtpHost'=>'ssl://smtp.yandex.ru',
'smtpPort'=>'465',
'smtpUser'=>'ns.management',
'smtpPassword'=>'qazwsx25',
'smtpEmail'=>'ns.management@yandex.ru'
//'adminEmailEmail'=>'seoutils@yandex.ru',
//'adminEmailName'=>'PARISROAYLCLUB.ORG',
//'languageDefault'=>'ru',
//'dbDriver'=>'mysql',
//'mysqlHost'=>'localhost',
//'mysqlUser'=>'root',
//'mysqlPassword'=>'',
//'mysqlDatabase'=>'cms',
//'languageList'=>array('ru','en')
);

if(file_exists($_SERVER['DOCUMENT_ROOT'].'/configuration.php')) {
	include_once($_SERVER['DOCUMENT_ROOT'].'/configuration.php');
	$cfg=new JConfig();
	$config['adminEmailEmail']=$cfg->mailfrom;
	$config['adminEmailName']=$cfg->fromname;
}
return $config;
?>