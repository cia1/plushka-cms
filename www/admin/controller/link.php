<?php
/* Реализует произвольную ссылку в меню */
class sController extends controller {

/* ----------- PUBLIC ---------------------------------------------------------------- */
	public function right($right) {
		if(isset($right['menu.*'])) return true; else return false;
	}
/* ----------------------------------------------------------------------------------- */

/* ---------- MENU ------------------------------------------------------------------- */
/* Произвольная ссылка в меню */
public function actionMenuLink() {
	$f=core::form();
	$f->text('link','Ссылка',$_GET['link']);
	$f->submit('Продолжить');
	return $f;
}

public function actionMenuLinkSubmit($data) {
	$i=strlen('http://'.$_SERVER['HTTP_HOST']);
	if(substr($data['link'],0,$i)=='http://'.$_SERVER['HTTP_HOST']) $data['link']=substr($data['link'],$i+1);
	return $data['link'];
}
/* ----------------------------------------------------------------------------------- */

}
?>