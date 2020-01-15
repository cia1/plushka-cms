<?php
use plushka\admin\core\plushka;

/** @noinspection PhpIncludeInspection */
return array_merge(include(plushka::path().'config/_core.php'),array(
	'mainPath'=>'index'
));