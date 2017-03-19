<?php class mysql extends _mysql {

	public function query($query,$limit=null,$page=null) {
		coreLog::add('mysql',$query);
		return parent::query($query,$limit,$page);
	}

}