<?php
define('_LANG','en');
include(dirname(__FILE__).'/core/core.php');
$cfg=core::config('form-casting');
if($_POST['plushka']) {
	@session_start();
	if(class_exists('JFactory')) {
		$s=JFactory::getSession();
		$_SESSION['captcha']=$s->get('captcha');
	}
	$m=core::model();
	$m->set($_POST['plushka']);
	$validate=array();
	foreach($cfg['field'] as $id=>$item) {
		if(!isset($item['validate'])) continue;
		$validate[$id]=$item['validate'];
	}
	$showForm=!$m->validate($validate);
	if(!$showForm) {
		core::import('core/email');
		$email=new email();
		$email->from($m->email,$m->name);
		$email->subject('Casting contact form');
		$email->replyTo($m->email);
		$data=$m->get();
		$message='<p><b>Casting contact form '.$_SERVER['HTTP_HOST'].'</b></p><p>';
		foreach($cfg['field'] as $id=>$item) {
			if($item[0]=='file') continue;
			$message.='<b>'.$item[1].'</b>: ';
			if($item[0]=='textarea') $message.='<br />';
			$message.=$data[$id].'<br />';
		}
		$message.='</p><p><b>Attached images:</b></p>';
		foreach($data['photo'] as $item) {
			$message.=$email->getImg($email->attachImage($item['tmpName'],$item['type']));
		}
		$email->message($message);
		$cfgCore=core::config();
		if(!$email->send($cfgCore['adminEmailEmail'])) {
			if(!core::error()) core::error('Unknown error');
		} else core::success('Message was sent. Thank you.');
	}
} else $showForm=true;
?>
<link rel="stylesheet" type="text/css" href="<?=core::url()?>public/plushka.css" />
<?php if(core::error()) echo '<div class="messageError">',core::error(false),'</div>'; ?>
<?php if(core::success()) echo '<div class="messageSuccess">',core::success(false),'</div>'; ?>
<?php if($showForm) {
	$form=core::form();
	foreach($cfg['field'] as $id=>$item) {
		unset($item['validate']);
		$form->field($item[0],$id,$item[1],(isset($item[2]) ? $item[2] : null),(isset($item[3]) ? $item[3] : null),(isset($item[4]) ? $item[4] : null));
	}
	$form->submit();
	$form->action='';
	$form->render();
	echo '<div style="clear:both;"></div>';
}
?>