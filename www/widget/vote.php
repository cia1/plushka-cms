<?php
/* Опрос
int $options - идентификатор опроса */
class widgetVote extends widget {

	public function __invoke() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT question,answer FROM vote WHERE id='.$this->options);
		$this->question=$data[0];
		$answer=explode('|',$data[1]);
		for($i=0,$cnt=count($answer);$i<$cnt;$i++) {
			$answer[$i]=array($answer[$i],$answer[$i]);
		}
		$this->form=core::form('vote');
		$this->form->radio('answer','',$answer);
		$this->form->submit();
		return true;
	}

	public function render() { ?>
		<h3><?=$this->question?></h3>
		<?php $this->form->render('vote/'.$this->options); ?>
		<div style="clear:both;"></div>
		<p style="text-align:center;"><a href="<?=core::link('vote/'.$this->options)?>"><?=LNGresults?></a></p>
	<?php }

	public function adminLink() {
		return array(
			array('vote.*','?controller=vote&action=index&id='.$this->options,'setting','Настройки опроса'),
			array('vote.*','?controller=vote&action=result&id='.$this->options,'grapth','Результаты опроса')
		);
	}

}
?>