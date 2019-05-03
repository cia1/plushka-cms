<?php
/* Событие: удаление пункта меню
Модуль: catalog (универсальный каталог)
Параметры: string $data[0] - удаляемая ссылка, int $data[1] - идентификатор удаляемого пункта меню */
$link=$data[0];

if(substr($link,0,8)!='catalog/') return true;
$id=substr($link,strrpos($link,'/')+1);
$db=plushka::db();
$db->query('DROP TABLE catalog_'.$id);
unlink(plushka::path().'config/catalogLayout/'.$id.'.php');
return true;
?>