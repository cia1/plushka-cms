<?php
namespace plushka\widget;
use plushka\core\plushka;
use plushka\core\Widget;

/* Опрос
int $options - идентификатор опроса */
class VoteWidget extends Widget {

	public function __invoke() {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT question,answer FROM vote WHERE id='.$this->options);
		$this->question=$data[0];
		$answer=explode('|',$data[1]);
		for($i=0,$cnt=count($answer);$i<$cnt;$i++) {
			$answer[$i]=array($answer[$i],$answer[$i]);
		}
		$this->form=plushka::form('vote');
		$this->form->radio('answer','',$answer);
		$this->form->submit();
		return true;
	}

	public function render($view): void { ?>
		<h3><?=$this->question?></h3>
		<?php $this->form->render('vote/'.$this->options); ?>
		<div style="clear:both;"></div>
		<p style="text-align:center;"><a href="<?=plushka::link('vote/'.$this->options)?>"><?=LNGresults?></a></p>
	<?php }

	public function adminLink(): array {
		return array(
			array('vote.*','?controller=vote&action=index&id='.$this->options,'setting','Настройки опроса'),
			array('vote.*','?controller=vote&action=result&id='.$this->options,'grapth','Результаты опроса')
		);
	}

}
