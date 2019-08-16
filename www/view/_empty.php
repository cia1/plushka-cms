<?php if(isset($this->content)===true) {
	if(is_object($this->content)) $this->content->render();
	else echo $this->content;
}
