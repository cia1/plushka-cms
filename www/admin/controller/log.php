<?php class sController extends controller {

	public function right() {
		return array(
			'Index'=>'log.*',
			'Log'=>'log.*'
		);
	}

	//Список журналов регистрации
	public function actionIndex() {
		$table=core::table();
		$path=core::path().'tmp/';
		$d=opendir($path);
		$table->rowTh('Журналы регистраций');
		while($f=readdir($d)) {
			if($f=='.' || $f=='..' || substr($f,-4)!='.log') continue;
			$cfg=core::path().'admin/config/'.$f.'.php';
			if(!file_exists($cfg)) continue;
			$cfg=include($cfg);
			$table->link($cfg['title'],'log/log&id='.$f);
		}
		return $table;
	}

	//Лог журнала регистрации
	public function actionLog() {
		//Анализ конфигурационного файла (/admin/config/example.log.php), подготовка формата
		$cfg=core::configAdmin($_GET['id']);
		if(!$cfg) core::error404();
		$field=$cfg['field'];
		unset($cfg['field']);
		for($i=0,$cnt=count($field);$i<$cnt;$i++) {
			$y=strpos($field[$i][1],'(');
			if($y) {
				$type=substr($field[$i][1],0,$y);
				$param=substr($field[$i][1],$y+1,strlen($field[$i][1])-$y-2);
			} else {
				$type=$field[$i][1];
				$param=null;
			}
			switch($type) {
			case 'callback':
				$y=strrpos($param,'.');
				$f=substr($param,0,$y);
				core::import($f);
				$param=substr($param,$y+1);
			}
			$field[$i]=array($field[$i][0],$type,$param);
		}
		//Чтение файла лога (целиком!)
		$f=fopen(core::path().'tmp/'.$_GET['id'],'r');
		$table=core::table();
		foreach($field as $item) $table->th($item[0]);
		while($line=fgets($f)) {
			$line=rtrim($line);
			if(!$line) continue;
			$line=explode("\t",$line);
			foreach($line as $i=>$fld) {
				$method='_format'.ucfirst($field[$i][1]);
				if(!method_exists($this,$method)) $method='_formatText';
				$table->text(self::$method($fld,$field[$i][2]));
			}
		}
		fclose($f);
		return $table;
	}

	private static function _formatText($value,$param) {
		return $value;
	}
	private static function _formatDate($value,$param) {
		return date($param,$value);
	}

	private static function _formatCallback($value,$param) {
		return $param($value);
	}
	
}