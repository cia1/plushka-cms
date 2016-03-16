<?php
/* Внимание! Этот файл является частью фреймворка. Вносить изменения не рекомендуется.
Кеширует шаблоны */
class cache {


	/* Создаёт кеш шаблона с именем $name */
	public static function template($name) {
		if(substr($_SERVER['REQUEST_URI'],0,7)=='/admin/') $adminPath='admin/'; else $adminPath='';
		$template=file_get_contents(core::path().$adminPath.'template/'.$name.'.html');
		$template=str_replace('{{metaTitle}}','<?=$this->metaTitle?>',$template);
		$template=str_replace('{{metaKeyword}}','<?php if($this->meataKeyword) echo \'<meta name="keyword" content="\'.$this->metaKeyword.\'" />\'; ?>',$template);
		$template=str_replace('{{metaDescription}}','<?php if($this->meataDescription) echo \'<meta name="description" content="\'.$this->metaDescription.\'" />\'; ?>',$template);
		$template=str_replace('{{head}}','<?=$this->_head?>',$template);
		$template=str_replace('{{pageTitle}}','<?php if($this->pageTitle) echo \'<h1 class="pageTitle">\'.$this->pageTitle.\'</h1>\'; ?>',$template);
		$template=str_replace('{{breadcrumb}}','<?php $this->breadcrumb(); ?>',$template);
		$section=$widget=array(); //Список секций и виджетов в шаблоне - эта информация может кому-то понадобиться в последствии
		$template=preg_replace_callback('/{{section\[([^\]]+)\]}}/',function($data) use(&$section) {
			if(!isset($data[1])) return;
			$section[]=$data[1];
			return '<?=core::section(\''.$data[1].'\')?>';
		},$template);
		$template=preg_replace_callback('/{{widget\[([^\]]+)\](?:\[([^\]]*?)\]|)(?:\[([^\]]*?)\]|)(?:\[([^\]]+)\]|)}}/',function($data) use(&$widget) {
			if(!isset($data[1])) return;
			$s='<?=core::widget(\''.$data[1].'\'';
			if(isset($data[2])) {
				$sub=$data[2];
				$quote=false;
				$count=0;
				for($i=0;$i<strlen($sub);$i++) {
					if($sub[$i]=='"') {
						$quote=!$quote;
						continue;
					}
					if($sub[$i]==':' && !$quote) {
						$sub=substr($sub,0,$i).'=>'.substr($sub,++$i);
						$count++;
					}
				}

				if($count) $s.=',array('.str_replace('"','\'',$sub).')';
				elseif($data[2]) $s.=",'".$data[2]."'";
				else $s.=',null';
			} else $sub=null;
			$widget[]=array($data[1],$sub);
			if(isset($data[3]) && (int)$data[3]) $s.=','.(int)$data[3];
			if(isset($data[4]) && $data[4]) {
				if(!isset($data[3]) || !$data[3]) $s.=',null';
				$s.=",'".$data[4]."'";
			}
			$s.=')?>';
			return $s;
		},$template);

		$i=strpos($template,'{{content}}');
		$f=fopen(core::path().$adminPath.'cache/template/'.$name.'Head.php','w');
		if($i) fwrite($f,substr($template,0,$i)); else fwrite($f,$template);
		fclose($f);
		$f=fopen(core::path().$adminPath.'cache/template/'.$name.'Footer.php','w');
		if($i) fwrite($f,substr($template,$i+11));
		fclose($f);
		$s='<?php return array(\'widget\'=>array(';
		$cnt=count($widget);
		for($i=0;$i<$cnt;$i++) {
			if($i) $s.=',';
			$s.='array(\''.$widget[$i][0].'\','.($widget[$i][1]===null ? 'NULL' : $widget[$i][1]).')';
		}
		$s.='),\'section\'=>array(\''.implode('\',\'',$section).'\')); ?>';
		$f=fopen(core::path().$adminPath.'cache/template/'.$name.'.ini','w');
		fwrite($f,$s);
	}

}
?>