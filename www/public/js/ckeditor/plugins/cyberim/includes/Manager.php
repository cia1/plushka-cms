<?php
/**
 * Cyber Image Manager
 *
 *
 * @package		Cyber Image Manager
 * @author		Radik
 * @copyright	Copyright (c) 2010, Cyber Applications.
 * @link		http://www.cyberapp.ru/
 * @since		Version 1.1
 * @file 		/includes/Manager.php
 */
 
/*
  Р—Р°С‰РёС‚Р° РѕС‚ РїСЂСЏРјРѕР№ Р·Р°РіСЂСѓР·РєРё
*/
defined('ACCESS') or die();

class Manager {
	public static $file_m;
	public static $image_m;
	public static $sess_m;
	public static $error_m;
	public static $conf;
	
	/*
	  РњРµС‚РѕРґ РІС‹РїРѕР»РЅСЏРµС‚ Р·Р°РґР°С‡Сѓ
	*/
	public function peform($task = ''){
		if (file_exists(TASKS_PATH.$task.EXT)){
			require_once(TASKS_PATH.$task.EXT);
		}
	}	
}
?>