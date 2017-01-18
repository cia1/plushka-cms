<?php
/* Последние сообщения чата
array $options: int id - идентификатор чата; int count - количество сообщений */
class widgetChat extends widget {

	public function __invoke() {
		core::language('chat');
		return true;
	}

	public function render($view) {
		echo '<link rel="stylesheet" type="text/css" href="'.core::url().'public/css/chat.css" />';
		$f=file(core::path().'data/chat.'.$this->options['id'].'.txt'); //тут хранятся сообщения чата
		$cnt=count($f)-1;
		if($this->options['count']<$cnt) $cnt=$this->options['count'];
		echo '<div class="container">';
		for($i=$cnt;$i>=0;$i--) {
			$item=explode('|||',$f[$i]);
			echo '<p><span class="time">'.date('H:i',$item[0]).'</span><span class="name">'.$item[1].'</span>: '.$item[2].'</p>';
		}
		echo '</div>';
		echo '<a href="'.core::link('chat/'.$this->options['id']).'">'.LNGHaveJaw.'</a>';
	}

}
?>