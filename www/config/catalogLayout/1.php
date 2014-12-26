<?php return array(
'onPage'=>30,
'data'=>array(
'translate'=>array(
'Перевод',
'list',
'оригинал, не требуется
нет
одноголосый любительский
многоголосый любительский
одноголосый профессиональный
многоголосый профессиональный
полный дубляж
'
),
'country'=>array(
'Страны',
'string'
),
'description1'=>array(
'Краткое описание',
'text'
),
'description2'=>array(
'Полное описание',
'text'
),
'genry'=>array(
'Жанр',
'string'
),
'director'=>array(
'Режиссёр',
'string'
),
'actor'=>array(
'Актёры',
'string'
),
'mainPicture'=>array(
'Главный кадр',
'image',
'width'=>186,
'height'=>'<250'
),
'picture'=>array(
'Кадры из фильма',
'gallery',
'width'=>'<900',
'height'=>'<650'
),
'year'=>array(
'Год выхода',
'integer'
)
),
'view1'=>array(
array(
'mainPicture',
false
),
array(
'genry',
true
),
array(
'year',
true
),
array(
'country',
true
),
array(
'description1',
false
)
),
'view2'=>array(
array(
'mainPicture',
false
),
array(
'genry',
true
),
array(
'actor',
true
),
array(
'year',
true
),
array(
'director',
true
),
array(
'country',
true
),
array(
'translate',
true
),
array(
'description2',
false
),
array(
'picture',
true
)
),
'sort'=>''
); ?>