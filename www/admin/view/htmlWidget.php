<?php
$f=core::form();
$f->hidden('filename',$this->data['filename']);
if(isset($this->data['section'])) $f->hidden('section',$this->data['section']);
if($this->data['filename']) $f->label('Имя файла','/data/widgetHtml/'.$this->data['filename'].'.html');
$f->editor('html','Текст блока',$this->data['text']);
$f->submit('Продолжить');
$f->render();