<?php if(isset($this->content)) {
	if(is_object($this->content)) $this->content->render();
	else echo $this->content;
}