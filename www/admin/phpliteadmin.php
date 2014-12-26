<?php
require_once('./core/core.php');
session_start();
if(core::userGroup()!=255) exit;
core::import('admin/data/phpliteadmin');
?>
