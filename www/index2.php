<?php
$s='';
if(isset($_GET['controller'])===true) {
	$s=$_GET['controller'];
	if(isset($_GET['action'])===true) $s.='/'.$_GET['action'];
}
if($s) $_GET['corePath']=$s;
unset($s);
require_once(__DIR__.'/core/plushka.php');

$name=plushka::path().'controller/'.ucfirst($_GET['corePath'][0]).'Controller.php';
if(!file_exists($name)) {
	plushka::template(false);
	plushka::$controller=new \plushka\controller\ErrorController();
	plushka::error404();
}
\plushka\core\runApplication(false);