<?php
use plushka\admin\core\plushka;

require __DIR__.'/core/plushka.php';
session_start();
if(plushka::userGroup()!==255) exit;
plushka::import('admin/data/phpliteadmin');