<?php
defined('ACCESS') or die();
session_start();
if(!isset($_SESSION['_uploadFolder'])) exit;

/*
  General options
*/
//шаблон файлового менеджера
$conf['general.template'] = 'default';
//язык менеджера изображений
$conf['general.language'] = 'ru';
//кодировка файлов и страниц
$conf['general.char_set'] = 'utf-8';
//количество изображений показываемых на одной странице
$conf['general.elements'] =20;
//драйвер обработки ошибок редактора
$conf['general.error'] = 'Default';

/*
  Session options
*/
//драйвер обработчика сесси
$conf['session.driver'] = 'Sample';
//регулярное выражение для проверки имени пользователя
$conf['session.valid_users'] = '/^.+$/';
//регулярное выражение для проверки группы пользователей
$conf['session.valid_users_groups'] = '/^.+$/';

/*
  File System options
*/
//путь для заключительного url
//require_once(substr(__FILE__,0,-45).'core/core.php');
$conf['filesystem.path']=$conf['filesystem.path']='../../../'.$_SESSION['_uploadFolder'];
//относительный путь к файлам пользователя
$conf['filesystem.files_path']='../../../../../'.$_SESSION['_uploadFolder'];
//регулярное выражения описания пропускаемых каталогов
$conf['filesystem.exclude_directory_pattern'] = '/^_thumb$|^_system$/i';
//права устанавливаемые на создаваемые директории
$conf['filesystem.directory_chmod'] = 0777;
//права утанавливаемые на создаваемые и загружаемые файлы
$conf['filesystem.file_chmod'] = 0777;
//допустимые расшырения файлов в случае если использовать драйвер обработки изображений ImageMagick этот список можно значительно расширить
$conf['filesystem.allowed_extensions'] = 'gif|png|jpeg|jpg|jpe|xls|doc|docx|zip|7zip|rar|gzip|pdf';
//сортировка файлов если установленно в true то файлы сортируются в порядке возростание, false - порядке убывания
$conf['filesystem.sort'] = true;
//максимальный размер загружаемого файла в байтах
$conf['filesystem.max_file_size'] = 2097152;
//размер очереди файлов
$conf['filesystem.queue_size_limit'] = 5;
//кодировка файловой системы
$conf['filesystem.char_set'] = 'CP1251';

/*
  Thumbnail options
*/
//драйвер обработки изображений может принимать значения GD2 или ImageMagick
$conf['thumbnail.driver'] = 'GD2';
//имя каталого с файлами предварительного просмотра изображений
$conf['thumbnail.folder'] = '_thumb/';
//ширина изображения предварительного просмотра 
$conf['thumbnail.width'] = 100;
//высота изображения предварительного просмотра 
$conf['thumbnail.hieght'] = 90;
//коэффициент качества jpeg файла изображения предварительного просмотра 
$conf['thumbnail.jpeg_quality'] = 80;
//если установленно в true, то файл вписывается в рамку, false - изменяет ширину и высоту на указаные параметры
$conf['thumbnail.resize_to_frame'] = true;

/*
  Stream options
*/
//активировать потоковое gzip сжатие данных передаваемых от сервера к пользователю
$conf['stream.use_gzip'] = true;
//уровень компресси данных от 1 до 9, 9 - максимальное сжатие
$conf['stream.compression_level'] = 9;
//типы файлов (необходимо для скачивания файла)
$conf['stream.mimes'] = array(	'psd'	=>	'application/x-photoshop',
								'pdf'	=>	array('application/pdf', 'application/x-download'),
								'eps'	=>	'application/postscript',
								'ps'	=>	'application/postscript',
								'bmp'	=>	'image/bmp',
								'gif'	=>	'image/gif',
								'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
								'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
								'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
								'png'	=>	array('image/png',  'image/x-png'),
								'tiff'	=>	'image/tiff',
								'tif'	=>	'image/tiff' );