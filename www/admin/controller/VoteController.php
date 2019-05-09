<?php
namespace plushka\admin\controller;

/* Социальный опрос */
class VoteController extends \plushka\admin\core\Controller {

	public function right() {
		return array(
			'index'=>'vote.*',
			'reset'=>'vote.*',
			'result'=>'vote.*',
			'widgetVote'=>'vote.*'
		);
	}

/* ---------- PUBLIC ----------------------------------------------------------------- */
	/* Форма редактирования вопросов и ответов */
	public function actionIndex() {
		if(isset($_GET['id'])) $id=$_GET['id']; else $id=null;
		return $this->_form($id); //Также используется виджетом
	}

	public function actionIndexSubmit($data) {
		if(!$this->_submit($data)) return false;
		plushka::redirect('vote/index?id='.$data['id'],'Изменения сохранены');
	}

	/* Сброс результатов опроса */
	public function actionReset() {
		$f=plushka::form();
		$f->hidden('id',$_GET['id']);
		$f->html('Результаты голосования, а также список проголосовавших будет очищен. Подтвердите операцию.');
		$f->submit('Продолжить');
		return $f;
	}

	public function actionResetSubmit($data) {
		$db=plushka::db();
		$db->query('UPDATE vote SET ip='.$db->escape('').',result='.$db->escape('').' WHERE id='.$data['id']);
		plushka::redirect('vote/result?id='.$data['id'],'Результаты очищены.');
	}

	/* Таблица с результатами опроса */
	public function actionResult() {
		$this->button('vote/reset?id='.$_GET['id'],'refresh','Сбросить результаты');
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT question,answer,result FROM vote WHERE id='.$_GET['id']);
		$this->question=$data[0];
		//$this->answer - массив, содержащий количество голосов для каждого из вопросов
		$this->answer=$data[1];
		$this->answer=explode('|',$this->answer);
		$total=explode('|',$data[2]);
		$this->total=0;
		for($i=0,$cnt=count($this->answer);$i<$cnt;$i++) {
			$this->answer[$i]=array($this->answer[$i],$total[$i]);
			$this->total+=$total[$i];
		}
		return 'Result';
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- WIDGET ----------------------------------------------------------------- */
	/* Виджет опроса
	Параметр: (int) - ИД опроса */
	public function actionWidgetVote($data=null) {
		if($data) $data=(int)$data; else $data=null;
		return $this->_form($data);
	}

	public function actionWidgetVoteSubmit($data) {
		$id=$this->_submit($data);
		if(!$id) return false;
		return $id;
	}
/* ----------------------------------------------------------------------------------- */


/* ---------- PRIVATE ---------------------------------------------------------------- */
	/* Возвращает форму (class form) редактирования опроса */
	private function _form($id) {
		if($id) { //Уже существующий опрос
			$this->button('vote/reset?id='.$id,'refresh','Сбросить результаты');
			$this->button('vote/result?id='.$id,'graph','Результаты опроса');
			$db=plushka::db();
			$data=$db->fetchArrayOnceAssoc('SELECT id,question,answer FROM vote WHERE id='.$id);
			if(!$data['answer']) $data['answer']=''; else $data['answer']=str_replace('|',"\n",$data['answer']);
		} else $data=array('id'=>null,'question'=>'','answer'=>''); //Новый опрос
		$f=plushka::form();
		$f->hidden('id',$data['id']);
		$f->hidden('cacheTime',10);
		$f->text('question','Вопрос',$data['question']);
		$f->textarea('answer','Варианты ответов',$data['answer']);
		$f->submit('Продолжить');
		$this->cite='<b>Варианты ответов</b>: каждый ответ в новой строке.';
		if($id) $this->cite.='<br />Если варианты ответов были изменены, то результаты опроса будут автоматически сброшены.';
		return $f;
	}

	/* Сохраняет данные формы в базу данных */
	private static function _submit($data) {
		$data['answer']=str_replace("\n",'|',$data['answer']);
		$validate=array(
			'id'=>array('primary'),
			'question'=>array('string','вопрос',true),
			'answer'=>array('string','варианты ответов',true),
			'result'=>array('string')
		);
		if($data['id']) {
			$db=plushka::db();
			$result=$db->fetchArrayOnce('SELECT answer,result FROM vote WHERE id='.$data['id']);
			if($result[0]==$data['answer']) $result=$result[1]; else {
				$result=array_fill(0,substr_count($data['answer'],'|')+1,'0');
				$data['ip']='';
				$validate['ip']=array('string');
			}
		} else {
			$cnt=substr_count($data['answer'],'|')+1;
			if($data['answer']) $result=array_fill(0,$cnt,'0'); else $result='';
			$data['ip']='';
			$validate['ip']=array('string');
		}
		if(is_array($result)) $result=implode('|',$result);
		$data['result']=$result;
		$m=plushka::model('vote');
		$m->set($data);
		if(!$m->save($validate)) return false;
		return $m->id;
	}

}
?>