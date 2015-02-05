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
 * @file 		/includes/SessionManager.php
 */
 
/*
  Р—Р°С‰РёС‚Р° РѕС‚ РїСЂСЏРјРѕР№ Р·Р°РіСЂСѓР·РєРё
*/
defined('ACCESS') or die();

class SessionManager {
	private $driver;
    private static $instance;
	
	public function __construct(){
		SessionManager::$instance = & $this;
		//Р·Р°РіСЂСѓР¶Р°РµРј РґСЂР°Р№РІРµСЂ
		require_once(INCLUDE_PATH.'drivers'.DS.'SessionManager_Driver'.EXT);
		$driver_name = 'SessionManager_'.Manager::$conf['session.driver'].'_Driver';
		require_once(INCLUDE_PATH.'drivers'.DS.'SessionManager'.DS.$driver_name.EXT);
		$this->driver = new $driver_name;
	}	
	
	public static function & instance(){
		empty(SessionManager::$instance) and new SessionManager;
		return SessionManager::$instance;
	}
	
	public function authorisation(){
		return $this->driver->authorisation();
	}
}
?>