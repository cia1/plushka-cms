<?php
require_once(__DIR__.'/core/plushka.php');

$name=plushka::path().'admin/controller/'.ucfirst($_GET['controller']).'Controller.php';
if(file_exists($name)===false) {
	plushka::$controller=new \plushka\admin\controller\ErrorController();
	plushka::error404();
}
\plushka\admin\core\runApplication();