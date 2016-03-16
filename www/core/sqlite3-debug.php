<?php class sqlite extends _sqlite {

	public function query($query,$limit=null,$page=null) {
		log::add('sqlite',$query);
		return parent::query($query,$limit,$page);
	}

}