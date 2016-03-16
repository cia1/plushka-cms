<?php class mysql extends _mysql {

	public function query($query,$limit=null,$page=null) {
		log::add('mysql',$query);
		return parent::query($query,$limit,$page);
	}

}