<?php class sqlite extends _sqlite {

	public function query($query,$limit=null,$page=null) {
		log::add('sqlite',$query);
		parent::query($query,$limit,$page);
	}

}