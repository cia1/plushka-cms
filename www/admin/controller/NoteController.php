<?php
namespace plushka\admin\controller;
use plushka\admin\core\Controller;
use plushka\admin\core\plushka;

/* Заметки для администрации */
class NoteController extends Controller {

	public function right() {
		return array(
			'index'=>'note.*',
			'item'=>'note.*',
			'delete'=>'note.*',
			'view'=>'note.*'
		);
	}

	/* Список доступных пользователю заметок */
	public function actionIndex() {
		$this->button('note/item','new','Добавить заметку');
		$t=plushka::table();
		$t->rowTh('Тема|');
		$db=plushka::db();
		$myGroup=plushka::userGroup();
		$db->query('SELECT id,groupView,groupEdit,title FROM admin_note WHERE groupView<='.$myGroup);
		while($item=$db->fetch()) {
			$t->link('note/view?id='.$item[0],$item[3]);
			if($item[2]!=$myGroup) $t->text(''); //Только пользователи, относящиеся к той же группе, могут редактировать заметку
			else $t->itemDelete('id='.$item[0]);
		}
		$this->cite='Здесь могут находиться любые заметки, доступные только администраторам. ВНИМАНИЕ! Не храните здесь особо важные сведения - абсолютная конфиденциальность не может быть гарантирована.';
		return $t;
	}

	/* Редактирование заметки */
	public function actionItem() {
		$myGroup=plushka::userGroup();
		$db=plushka::db();
		if(isset($_GET['id'])) {
			$data=$db->fetchArrayOnceAssoc('SELECT id,groupView,title,html FROM admin_note WHERE id='.$_GET['id'].' AND groupEdit<='.$myGroup);
			if(!$data) plushka::redirect('note');
		} else $data=array('id'=>null,'groupView'=>$myGroup,'title'=>'','html'=>'');
		$userGroup=$db->fetchArray('SELECT id,name FROM user_group WHERE id>=200 AND id<='.$myGroup.' ORDER BY id');
		for($i=0,$cnt=count($userGroup);$i<$cnt;$i++) $userGroup[$i][1].=' ('.$userGroup[$i][0].')';
		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->label('Ваша группа пользователей',$myGroup);
		$f->select('groupView','Доступно для просмотра',$userGroup,$data['groupView']);
		$f->text('title','Тема',$data['title']);
		$f->textarea('html','Текст',$data['html']);
		$f->submit('Сохранить');
		$this->cite='В поле <b>Доступно для просмотра</b> укажите минимальную группу пользователей, которой будет доступен просмотр заметки, например, если выбрать группу с номером 240, то просмотр информации будет доступен всем пользвателям с номером группы не меньше 240.<br />Изменять данную информацию могут пользователи вашей группы ('.$myGroup.')';
		if($myGroup!=255) $this->cite.=', а также пользователи с номерами '.$myGroup.'-255.'; else $this->cite.='.';
		return $f;
	}

	public function actionItemSubmit($data) {
		$data['groupEdit']=$myGroup=plushka::userGroup();
		$m=plushka::model('admin_note');
		$m->set($data);
		if(!$m->save(array(
			'id'=>array('primary'),
			'groupView'=>array('integer','группа просмотра',true,'min'=>200,'max'=>$myGroup),
			'groupEdit'=>array('integer'),
			'title'=>array('string','тема',true),
			'html'=>array('html')
		))) return false;
		plushka::redirect('note');
	}

	/* Удалить заметку */
	public function actionDelete() {
		$db=plushka::db();
		$db->query('DELETE FROM admin_note WHERE id='.(int)$_GET['id'].' AND groupEdit<='.plushka::userGroup());
		plushka::redirect('note');
	}

	/* Просмотр текста заметки */
	public function actionView() {
		$db=plushka::db();
		$this->data=$db->fetchArrayOnceAssoc('SELECT title,html FROM admin_note WHERE id='.(int)$_GET['id'].' AND groupView<='.plushka::userGroup());
		$this->data['html']=nl2br($this->data['html']);
		return 'View';
	}

}
?>