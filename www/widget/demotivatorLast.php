<?php
/* Последний добавленный демотиватор */
class widgetDemotivatorLast extends widget {

	public function __invoke() { return true; }

	public function render() {
		$db=core::db();
		$data=$db->fetchArrayOnce('SELECT title,image FROM demotivator WHERE status=1 ORDER BY date DESC');
		echo '<a href="'.core::link('demotivator').'"><img src="'.core::url().'public/demotivator/'.$data[1].'" style="max-width:150px;" alt="'.$data[0].'" /></a>';
	}

}
?>