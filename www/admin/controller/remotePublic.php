<?php
/* Реализует удалённую публикацию материалов (статей) */
class sController extends controller {

	public function right($right) { return false; }

	/* Настройки удалённой публикации */
	public function actionSetting() {
		$cfg=core::configAdmin('remotePublic');
		$f=core::form();
		$f->text('secret','Секретная фраза',$cfg['secret']); //Для проверки источника
		$db=core::db();
		$db->query('SELECT alias,title FROM articleCategory ORDER BY title');
		$f->html('<h3>Разрешено для следующих категорий:</h3>');
		while($item=$db->fetch()) {
			$f->checkbox('articleCategory]['.$item[0],$item[1],(isset($cfg['articleCategory']) ? true : false));
		}
		$f->submit('Сохранить');
		return $f;
	}

	public function actionSettingSubmit($data) {
		if(!$data['secret']) {
			controller::$error='Секретная фразаа должна быть задана обязательно';
			return false;
		}
		core::import('admin/core/config');
		$cfg=new config();
		$cfg->secret=$data['secret'];
		$articleCategory=array();
		foreach($data['articleCategory'] as $id=>$item) $articleCategory[]=$id;
		$cfg->articleCategory=$articleCategory;
		$cfg->save('../admin/config/remotePublic');
		core::redirect('?controller=remotePublic&action=setting','Настройки сохранены');
	}

	/* Зарпос на добавление нового материала (удалённо) */
	public function actionJson() {
	 	//Проверка наличия обязательный параметров и правильности секретной фразы
		if(!$_POST || !isset($_POST['secret']) || !isset($_POST['category']) || !isset($_POST['alias']) || !$_POST['alias'] || !isset($_POST['title']) || !$_POST['title'] || !isset($_POST['text2']) || !$_POST['text2']) die('WRONG QUERY');
		$cfg=core::configAdmin('remotePublic');
		if($_POST['secret']!=$cfg['secret']) die('NO ACCESS');
		if(!in_array($_POST['category'],$cfg['articleCategory'])) die('NO ACCESS');
		//Доступ есть - можно добавить статью
		$q='INSERT INTO article (categoryId,alias,title,text1,text2,sort,metaTitle,metaKeyword,metaDescription,date) VALUES (';
		$db=core::db();
		//Получить ИД категории по её псевдониму
		$categoryId=$db->query('SELECT id FROM articleCategory WHERE alias='.$db->escape($_POST['category']));
		if(!$categoryId) die('CATEGORY IS WRONG');
		$q.=$categoryId;
		//Проверить уникальность псевдонима статьи
		if($db->fetchValue('SELECT 1 FROM article WHERE categoryId='.$categoryId.' AND alias='.$db->escape($_POST['alias']))) die('ALREADY EXISTS');
		$q.=','.$db->escape($_POST['alias']);
		$q.=','.$db->escape($_POST['title']);
		if(isset($_POST['text1']) && $_POST['text1']) $q.=','.$db->escape($_POST['text1']); else $q.=',null';
		$q.=','.$db->escape($_POST['text2']);
		$q.=',0';
		if(isset($_POST['metaTitle']) && $_POST['metaTitle']) $q.=','.$db->escape($_POST['metaTitle']); else $q.=',null';
		if(isset($_POST['metaKeyword']) && $_POST['metaKeyword']) $q.=','.$db->escape($_POST['metaKeyword']); else $q.=',null';
		if(isset($_POST['metaDescription']) && $_POST['metaDescription']) $q.=','.$db->escape($_POST['metaDescription']); else $q.=',null';
		if(isset($_POST['date']) && strtotime($_POST['date'])) $q.=','.strtotime($_POST['date']); else $q.=',null';
		$q.=')';
		if(!$db->query($q)) die('ERROR'); else die('OK');
	}

}
?>