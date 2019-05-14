<?php
namespace plushka\admin\core;

class Chat {

	//Удаляет сообщение со временем $time (timestamp с миллисекундами)
	public static function delete($chatId,$time) {
		$time=(string)floatVal($time);
		$src=file(plushka::path().'data/chat/'.$chatId.'.txt');
		$dst='';
		foreach($src as $item) {
			$t=strpos($item,"\t");
			if(!$t) continue;
			$t=substr($item,0,$t);
			if($t==$time) continue;
			$dst.=$item;
		}
		$f=fopen(plushka::path().'data/chat/'.$chatId.'.txt','w');
		fwrite($f,$dst);
		fclose($f);
		return true;
	}

}