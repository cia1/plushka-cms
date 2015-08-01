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
 * @file 		/includes/tasks/upload.php
 */
  
	/*
  		Р—Р°С‰РёС‚Р° РѕС‚ РїСЂСЏРјРѕР№ Р·Р°РіСЂСѓР·РєРё
	*/
	defined('ACCESS') or die();
		
	header('Content-type: text/html; charset='.Manager::$conf['general.char_set']);	
		
	if (!empty($_FILES)) {
		$code = 0; //Р—Р°РіСЂСѓР·РєР° РїСЂРѕС€Р»Р° СѓСЃРїРµС€РЅРѕ
		switch ($_FILES['file']['error']){
			case UPLOAD_ERR_FORM_SIZE : 
			case UPLOAD_ERR_INI_SIZE :
				$code = 1;	//Р¤Р°Р№Р» РїСЂРµРІС‹С€Р°РµС‚ РјР°РєСЃРёРјР°Р»СЊРЅС‹Р№ СЂР°Р·РјРµСЂ
				break;
				
			case UPLOAD_ERR_PARTIAL  :
				$code = 2; //Р¤Р°Р№Р» Р±С‹Р» РїРµСЂРµРґР°РЅ РЅРµ РїРѕР»РЅРѕСЃС‚СЊСЋ
				break;
				
			case UPLOAD_ERR_NO_FILE :
				$code = 3; //С„Р°Р№Р» РЅРµ Р±С‹Р» Р·Р°РіСЂСѓР¶РµРЅ
				break;
				
			case UPLOAD_ERR_OK :
				if (!preg_match(',[[:cntrl:]]|[/\\:\*\?\"\<\>\|],', $_FILES['file']['name'])){
					$tempFile = $_FILES['file']['tmp_name'];
					$targetPath =  FileManager::clear_path(Manager::$conf['filesystem.files_abs_path'].$_REQUEST['folder']);
					$targetFile =  $targetPath.$_FILES['file']['name'];
					$fileTypes = explode('|', strtolower(Manager::$conf['filesystem.allowed_extensions']));
					$ext = FileManager::get_ext($_FILES['file']['name']);							
					if (in_array(strtolower($ext), $fileTypes)) {
						move_uploaded_file(FileManager::convertToFileSystem($tempFile), FileManager::convertToFileSystem($targetFile));
						chmod(FileManager::convertToFileSystem($targetFile), Manager::$conf['filesystem.file_chmod']);
					} else {
						$code = 4; //Р—Р°РїСЂРµС€РµРЅРЅРѕРµ СЂР°СЃС€РёСЂРµРЅРёРµ С„Р°Р№Р»Р°
					}
				} else {
					$code = 5;
				}	
				break;
		}
	}	else {
		$code = 1;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head></head>
	<body>
		<script type="text/javascript">
			window.parent.CyberCore.continue_upload(<?php echo $code; ?>);
		</script>
	</body>
</html>