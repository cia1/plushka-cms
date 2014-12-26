<?php
/* Библиотека функций личного кабинета пользователей */
class modelUser {

	/* Отправляет личное сообщение пользователю
	int $user2Id и string $user2Login - ИД и логин получателя; string $message - текст сообщения; bool $email - надо или нет продублировать сообщение по электронной почте */
	public static function message($user2Id=null,$user2Login=null,$message,$email=null) {
		$db=core::db();
		//Даже если были бы заданы и ИД и логин, то всёравно нужно удостовериться что такой пользователь существует
		if($user2Id) $user2=$db->fetchArrayOnceAssoc('SELECT id,login,email FROM user WHERE id='.$user2Id);
		elseif($user2Login) $user2=$db->fetchArrayOnceAssoc('SELECT id,login,email FROM user WHERE login='.$db->escape($user2Login));
		if(!$user2) {
			controller::$error='Ошибка отправки сообщеня: некорренктные данные получателя';
			return false;
		}
		$user1=core::userCore(); //$user1 - отправитель
		if(!$user1->id) core::redirect('user/login');
		$db->query('INSERT INTO userMessage SET user1Id='.$user1->id.',user1Login='.$db->escape($user1->login).',user2Id='.$user2['id'].',user2Login='.$db->escape($user2['login']).',message='.$db->escape($message).',date='.time());
		if($email) {
			core::import('core/email');
			$cfg=core::config();
			$e=new email();
			$e->from($cfg['adminEmailEmail'],$cfg['adminEmailName']);
			$e->subject('Личное сообщение');
			$e->replyTo($user1->email);
			$e->message('<p>На сайте '.$_SERVER['HTTP_HOST'].core::url().' вам было отправлено личное сообщение от администрации сайта. Ниже приведён текст сообщения.</p><hr />'.$message);
			$e->send($user2['email']);
		}
		return true;
	}
}
?>