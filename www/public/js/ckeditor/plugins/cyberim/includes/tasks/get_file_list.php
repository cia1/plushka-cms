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
 * @file 		/includes/tasks/get_file_list.php
 */
 
	/*
  		Р—Р°С‰РёС‚Р° РѕС‚ РїСЂСЏРјРѕР№ Р·Р°РіСЂСѓР·РєРё
	*/
	defined('ACCESS') or die();
	
	header('Content-type: text/json; charset='.Manager::$conf['general.char_set']);
	$list = Manager::$file_m->get_path_list($_REQUEST['path'], true, false);
	$files = array();
				
	if (sizeof($list) == 0) {
		echo json_encode(array());
		exit();
	}
				
	//СЃРѕР·РґР°РµРј РєР°С‚Р°Р»РѕРі РµСЃР»Рё РµРіРѕ РЅРµС‚
	$thumb_path = FileManager::convertToFileSystem(
		FileManager::clear_path(	
			Manager::$conf['filesystem.files_abs_path'].DS.$_REQUEST['path'].DS.Manager::$conf['thumbnail.folder']
		)
	);
	
	if (!is_dir($thumb_path)){FileManager::create_dir(FileManager::convertToGeniral($thumb_path));}
				
	/*
		Р¤РѕСЂРјРёСЂСѓРµРј РґР°РЅРЅС‹Рµ Рѕ РїРѕ СЃС‚Р°РЅРёС‡РЅРѕР№ РЅР°РІРёРіР°С†РёРё
	*/
				
	$start = ($_REQUEST['page'] - 1) * Manager::$conf['general.elements'];
	$total = count($list);
	$pages = ceil($total/Manager::$conf['general.elements']);
	$list = array_slice($list, $start, Manager::$conf['general.elements']);
				
	/*
		Р¤РѕСЂРјРёСЂСѓРµРј СЃРїРёСЃРѕРє С„Р°Р№Р»РѕРІ РґР»СЏ РѕС‚РІРµС‚Р°
	*/
	foreach ($list as $item){
		//СЃРѕР·РґР°РµРј РїСЂРµРІСЊСЋС€РєРё
		$src = FileManager::clear_path(
			FileManager::convertToFileSystem(
				Manager::$conf['filesystem.files_abs_path'].DS.$_REQUEST['path'].DS.$item['name']
			)
		);
		$dest = $thumb_path.$item['name'];
		$width = Manager::$conf['thumbnail.width'];
		$height = Manager::$conf['thumbnail.hieght'];
				  
		if (!file_exists($dest)){						
			//СЃРѕР·РґР°РµРј РїСЂРµРІСЊСЋ
			if (!ImageManager::instance()->thunbnail($src, $dest, $width, $height)){
				//РµСЃР»Рё РЅРµ СѓРґР°Р»РѕСЃСЊ С‚Рѕ РІС‹РІРѕРґРёРј РїСЂРµРІСЊСЋ С‡С‚Рѕ РїСЂРѕСЃРјРѕС‚СЂ РЅРµ РґРѕСЃС‚СѓРїРµРЅ
				$dest = 'pages/'.Manager::$conf['general.template'].'/img/error_thumbnails.gif';						
			};
		}
			
		$src = str_ireplace(Manager::$conf['filesystem.files_abs_path'], 
							Manager::$conf['filesystem.path'],
							FileManager::convertToGeniral($src));
		
		$dest = FileManager::convertToGeniral(
			str_ireplace(Manager::$conf['filesystem.files_abs_path'], Manager::$conf['filesystem.files_path'], $dest)
		);
			
		$files [] = array (
			'filename'  => $item['name'],
			'filepath'  => FileManager::path_encode(str_replace(array($item['name'], DS), array($item['name'], '/'), $src)),
			'thumbnail' => FileManager::path_encode(str_replace(array($item['name'], DS), array($item['name'], '/'), $dest)) 
		);
	}
				
	count($files) == 0 ? $data = array()
	: $data = array ('paginator' => array ('pagesTotal' => $pages, 'pageCurrent' => $_REQUEST['page']), 'files' => $files);
			
	echo json_encode($data);
?>