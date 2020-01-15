* languageCreate
  ```
  string $data[0] Псевдоним языка
* menuItemDelete
  ```
  string $data[0] Относительная ссылка (без языка)
  int    $data[1] ИД меню
* modify
  ```
  string $data[0] Относительный URL страницы (без языка)
  bool   $data[1] Затрагивают ли изменения все языки или только текущий
* pageDelete
  ```
  string $data[0] Относительный URL страницы (без языка)
  bool   $data[1] Затрагивают ли изменения все языки или только текущий
* userCreate
  ```
  int    $data[0] ID
  string $data[1] Логин
  string $data[2] Адрес электронной почты
* widgetDelete
  ```
  string $data[0] Имя виджета
  int    $data[1] ID
  mixed  $data[2] Данные виджета
* widgetPageDelete
  ```  
  string   $data[0] Имя виджета
  int      $data[1] ID виджета
  string[] $data[2] Список страниц, с которых виджет удалён