<?php
namespace plushka\controller;
use plushka;

/* Опрос (голосование) */
class VoteController extends \plushka\core\Controller {

	public function __construct() {
		parent::__construct();
		$this->id=(int)$this->url[1]; //идентификатор опроса
		$this->url[1]='Index';
		plushka::language('vote');
	}

	/* Выводит результаты опроса */
	public function actionIndex() {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT question,answer,result FROM vote WHERE id='.$this->id);
		if(!$data) plushka::error404();
		$this->answer=array();
		$this->total=0;
		$answer=explode('|',$data[1]);
		$result=explode('|',$data[2]);
		for($i=0,$cnt=count($answer);$i<$cnt;$i++) {
			$this->total+=$result[$i];
			$this->answer[]=array($answer[$i],(int)$result[$i]);
		}
		if($this->total) {
			for($i=0,$cnt=count($answer);$i<$cnt;$i++) {
				$this->answer[$i][1]=round($this->answer[$i][1]/$this->total*100).'% ('.$this->answer[$i][1].')';
			}
		}
		$this->pageTitle=$this->metaTitle=LNGVote.': '.$data[0];
		return 'Index';
	}

	public function actionIndexSubmit($data) {
		$data['answer']=(int)$data['answer'];
		$db=plushka::db();
		$vote=$db->fetchArrayOnce('SELECT result,ip FROM vote WHERE id='.$this->id);
		if(!$vote) plushka::error404();
		if($vote[1]) $ip=explode('|',$vote[1]); else $ip=array();
		if(in_array($this->_ip(),$ip)) plushka::redirect('vote/'.$this->id,LNGYouAlreadyVoted);
		$ip[]=$this->_ip();
		$ip=implode('|',$ip);
		$result=explode('|',$vote[0]);
		$result[$data['answer']]++;
		$result=implode('|',$result);
		$db->query('UPDATE vote SET result='.$db->escape($result).',ip='.$db->escape($ip).' WHERE id='.$this->id);
		plushka::redirect('vote/'.$this->id,LNGYourVoteGot);
	}

	public function adminIndexLink() {
		return array(
			array('vote.*','?controller=vote&action=index&id='.$this->id,'setting','Настройки опроса'),
			array('vote.*','?controller=vote&action=result&id='.$this->id,'grapth','Результаты опроса')
		);
	}

	private static function _ip() {
 		if(!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
 		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}

}
?>