<?php
/* Событие: удаление виджета
Модуль: чат
Параметры: string [0] - имя виджета, int [1] - идентификатор виджета, mixed [2] - параметры виджета
int [2]['id'] - идентификатор чата */
if($data[0]!='chat') return true;
unlink(core::path().'data/chat.'.$data[2]['id'].'.txt');
return true;
?>