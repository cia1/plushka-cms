<?php
namespace plushka\widget;
use plushka;
use plushka\core\Widget;

class DemotivatorLastWidget extends Widget {

	public function __invoke() { return true; }

	public function render($view): void {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT title,image FROM demotivator WHERE status=1 ORDER BY date DESC');
		echo '<a href="'.plushka::link('demotivator').'"><img src="'.plushka::url().'public/demotivator/'.$data[1].'" style="max-width:150px;" alt="'.$data[0].'" /></a>';
	}

}
