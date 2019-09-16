<?php
use plushka\core\plushka;

$this->formPassword->render();
if($this->notification===true) plushka::widget('notificationSettingForm');