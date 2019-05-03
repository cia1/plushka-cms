<?php
require_once('./core/core.php');
session_start();
if(plushka::userGroup()!=255) exit;
plushka::import('admin/data/phpliteadmin');
?>
