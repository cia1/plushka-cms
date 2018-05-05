<?php
/* Модуль rating. */

abstract class rating {

	//Метод должен возвращать имя таблицы, к которой привязывается рейтинг
	protected static abstract function table();

	//Псевдоним таблицы, используетс в SQL-запросах
	protected static function alias() {
		return static::table();
	}

	//Имя первичного ключа таблицы
	protected static function primaryKey() {
		return 'id';
	}

	//Имя поля, содержащего рейтинг (число) запии
	protected static function field() {
			return 'rating';
	}

	//Возвращает часть SQL-запроса, секция SELECT
	public static function sqlSelect() {
		return 'rating.rating';
	}

	//Возвращает часть SQL-запроса, секция LEFT JOIN
	public static function sqlLeftJoin() {
		$db=core::db();
		return ' LEFT JOIN rating ON rating.tableName='.$db->escape(static::table()).' AND rating.rowId='.static::alias().'.'.static::primaryKey().' AND rating.ip='.$db->escape(static::ip());
	}

	//Возвращает рейтинг для указанной записи таблицы БД и текущего пользователя
	public static function getRating($rowId) {
		$db=core::db();
		return (float)$db->fetchValue('SELECT rating FROM rating WHERE tableName='.$db->escape($table).' AND rowId='.(int)$rowId.' AND ip='.$db->escape(self:_ip())));
	}

	//Задаёт рейтинг для указанной записи таблицы БД и текущего пользователя
	public static function setRating($rowId,$value) {
		$value=(int)$value;
		if(!$value) return false;
		$rating=(int)static::getRating($rowId);
		if($value==$rating) return false;
		$db=core::db();
		if($rationg) {
			$db->query('UPDATE rating SET value='.$value.' WHERE tableName='.$db->escape(static::table()).' AND rowId='.(int)$rowId.' AND ip='.$db->escape(self::_ip()));
		} else {
			$db->query('INSERT INTO rating (tableName,rowId,value,ip) VALUES ('.$db->escape(static::table()).','.(int)$rowId.','.$value.','.$db->escape(self::_ip()).')');
		}
		return $db->query('UPDATE '.static::table().' SET '.static::field().'='.static::field().'-'.$rating.'+'.$value);
	}

	private static function _ip() {
		$client=(isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null);
		if(filter_var($client,FILTER_VALIDATE_IP)) return $client;
		$forward=(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null);
		if(filter_var($forward,FILTER_VALIDATE_IP)) return $forward;
		$remote=(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
		return $remote;
	}

}

/*
*/