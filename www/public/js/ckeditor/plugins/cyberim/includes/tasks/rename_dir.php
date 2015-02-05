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
 * @file 		/includes/tasks/rename_dir.php
 */
 
	/*
  		Р—Р°С‰РёС‚Р° РѕС‚ РїСЂСЏРјРѕР№ Р·Р°РіСЂСѓР·РєРё
	*/
	defined('ACCESS') or die();
	
	echo json_encode(array('done' => FileManager::rename(
		FileManager::clear_path(Manager::$conf['filesystem.files_path'].$_REQUEST['old_name']), 
		FileManager::clear_path(Manager::$conf['filesystem.files_path'].$_REQUEST['new_name'])
	)));
?>