<?php
namespace plushka\widget;
use plushka;

class DemotivatorLastWidget extends \plushka\core\Widget {

	public function __invoke() { return true; }

	public function render($view) {
		$db=plushka::db();
		$data=$db->fetchArrayOnce('SELECT title,image FROM demotivator WHERE status=1 ORDER BY date DESC');
		echo '<a href="'.plushka::link('demotivator').'"><img src="'.plushka::url().'public/demotivator/'.$data[1].'" style="max-width:150px;" alt="'.$data[0].'" /></a>';
	}

}