<?php

class core extends _core {

	public static function section($name) {
		coreLog::add('section',$name,false);
		parent::section($name);
	}

	public static function widget($name,$options=null,$cacheTime=null,$title=null,$link=null) {
		$s=$name.'[';
		if(is_string($options) && isset($options[1]) && $options[1]==':') {
			$options=unserialize($options);
		}
		if(is_array($options)) {
			$s.=self::_implode($options);
		} else $s.=$options;
		$s.=']';
		if($link) $s.='; link: '.$link;
		coreLog::add('widget',$s,false);
		parent::widget($name,$options,$cacheTime,$title,$link);
	}

	private static function _implode($array) {
		$s='';
		$cnt=count($array);
		$index=0;
		foreach($array as $key=>$value) {
			$index++;
			$s.=$key.': ';
			if(is_array($value)) $s.='['.self::_implode($value).'] ';
			else $s.=$value;
			if($index<$cnt)	$s.='; ';
		}
		return $s;
	}
}

class coreLog {

	private static $_log;
	private static $_redirect=false;

	public static function add($type,$data,$point=1) {
		if($type=='REDIRECT') {
			self::$_redirect=true;
		}
		if($point===false) $point=null; else {
			$debug=debug_backtrace(0);
			$point=substr($debug[$point]['file'],strlen(core::path())).' #'.$debug[$point]['line'];
		}
		self::$_log[]=array($type,$data,$point);
	}

	public static function render() {
		$cfg=core::config('../admin/config/debug');
		?>
		<link href="<?=core::url()?>public/css/debug.css" rel="stylesheet" type="text/css" />
		<div id="_debugButton" onclick="document.getElementById('_debugLog').style.display='';">LOG<?php if(self::$_redirect) echo ' redirect!'; ?></div>
		<div id="_debug">
			<ul id="_debugLog" style="display:none;" onclick="document.getElementById('_debugLog').style.display='none';">
			<?php foreach(self::$_log as $item) { ?>
				<li>
					<span><?=$item[0]?></span>: <?=$item[1]?>
					<?php if($item[2]) echo '<p>',$item[2],'</p>'; ?>
				</li>
			<?php } ?>
			<li style="list-style-type:none;text-align:right;"><label><input type="checkbox"<?php if(!$cfg['redirect']) echo ' checked="checked"'; ?> onclick="$.get('<?=core::url()?>admin/index2.php?controller=debug&action=setRedirect&redirect='+this.checked);" /> Не выполнять редирект</label></li>
			</ul>
		</div>
	<?php }
}