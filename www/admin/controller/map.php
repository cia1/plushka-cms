<?php
//Управление виджетами интерактивных карт Google
class sController extends controller {

	public function right() {
		return array(
			'Marker'=>'map.*',
			'Map'=>'map.*',
			'WidgetMap'=>'map.*'
		);
	}

	//Список меток на карте; добавление, редактирование и удаление меток.
	public function actionMarker() {
		//Найти требуемый виджет. К сожалению его ID неизвестен.
		$db=core::db();
		$db->query('SELECT id,data FROM widget WHERE name='.$db->escape('map').' ORDER BY id DESC');
		while($widget=$db->fetch()) {
			$this->id=$widget[0];
			$widget=unserialize($widget[1]);
			if($widget['id']==$_GET['id']) break;
		}
		$this->data=$widget;
		$this->button('','size','Сохранить центр карты и масштаб','Центр','onclick="return mapMarker.setCenter(document.forms.map);"');
		$this->button('','new','Добавить метку на карту','Добавить','onclick="return mapMarker.add(true);"');
		$this->button('html','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		$this->button('','delete','Удалить выбранную метку','Удалить','id="markerDelete" style="display:none;" onclick="return mapMarker.delete();"');
		//Форма карты
		$form=core::form();
		$form->hidden('id',$this->id);
		$form->hidden('marker','');
		$form->text('apiKey','API-ключ',$widget['apiKey']);
		$form->text('centerLatitude','Центр (широта)',$widget['centerLatitude'],'readonly="readonly" id="centerLatitude"');
		$form->text('centerLongitude','Центр (долгота)',$widget['centerLongitude'],'readonly="readonly" id="centerLongitude"');
		$form->text('zoom','Масштаб',$widget['zoom'],'readonly="readonly" id="zoom"');
		$form->select('type','Тип карты',array(
			array('ROADMAP','дорожная'),
			array('SATELLITE','спутниковая'),
			array('HYBRID','гибридная'),
			array('TERRAIN','ландшафт')
		),$widget['type'],null,'onchange="window._map.setMapTypeId(_google.maps.MapTypeId[this.value]);"');
		$form->submit('Сохранить');
		$this->formMap=$form;
		//Форма изменения/добавления точки
		$form=core::form('marker');
		$form->text('title','Заголовок');
		$form->text('latitude','Широта');
		$form->text('longitude','Долгота');
		$this->formMarker=$form;
		$this->scriptAdmin('map');
		return 'MarkerList';
	}

	public function actionMarkerSubmit($data) {
		$db=core::db();
		$id=(int)$data['id'];
		$widget=unserialize($db->fetchValue('SELECT data FROM widget WHERE id='.$id)); //id - это уже первичный ключ
		if(!$widget) core::error404();
		$widget['marker']=json_decode($data['marker'],true);
		$widget['centerLatitude']=(float)$data['centerLatitude'];
		$widget['centerLongitude']=(float)$data['centerLongitude'];
		$widget['zoom']=(int)$data['zoom'];
		$widget['type']=$data['type'];
		$widget['apiKey']=($data['apiKey'] ? trim($data['apiKey']) : null);
		$db->query('UPDATE widget SET data='.$db->escape(serialize($widget)).' WHERE id='.$id);
		core::success('Изменения сохранены');
		core::redirect('?controller=map&action=marker&id='.$data['id']);
	}

	//Рисует карту, это действие загружается во фрейме
	public function actionMap() {
		$link='http://maps.google.com/maps/api/js';
		if(isset($_GET['key']) && $_GET['key']) $link.='?key='.$_GET['key'];
		$this->js($link);
		return 'Map';
	}

	//Виджет карты
	public function actionWidgetMap($data=null) {
		if(!$data) {
			$db=core::db();
			$data=array(
				'id'=>($db->fetchValue('SELECT MAX(id) FROM widget')+1), //не обязан совпадать с ИД виджета
				'centerLatitude'=>41.88179139990236,
				'centerLongitude'=>55.728950739854184,
				'zoom'=>6,
				'type'=>'HIBRID',
				'marker'=>array(),
				'apiKey'=>null
			);
		}
		$form=core::form();
		$form->hidden('id',$data['id']); //нужен для выбора нужного виджета, т.к. нет способа получить ИД.
		$form->hidden('centerLatitude',$data['centerLatitude']);
		$form->hidden('centerLongitude',$data['centerLongitude']);
		$form->hidden('zoom',$data['zoom']);
		$form->hidden('type',$data['type']);
		$form->hidden('marker',urlencode(json_encode($data['marker'])));
		$form->text('apiKey','API ключ',$data['apiKey']);
		$form->submit();
    $this->cite='<b>apiKey</b> - ключ API, получить можно тут: https://developers.google.com/maps/documentation/geocoding/get-api-key?hl=ru<br />Настроить карту и добавить метки можно будет после создания виджета.';
		return $form;
	}

	public function actionWidgetMapSubmit($data) {
		$data['id']=(int)$data['id'];
		$data['centerLatitude']=(float)$data['centerLatitude'];
		$data['centerLongitude']=(float)$data['centerLongitude'];
		$data['zoom']=(int)$data['zoom'];
		$data['marker']=json_decode(urldecode($data['marker']),true);
		$data['apiKey']=($data['apiKey'] ? trim($data['apiKey']) : null);
		return $data;
	}

}