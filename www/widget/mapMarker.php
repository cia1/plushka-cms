<?php class widgetMapMarker extends widget {

	public function __invoke() {
		static $_index=0;
		$_index++;
		$this->mapId='map'.$_index;
		if(!isset($this->options['key'])) return false;
		if(!isset($this->options['formFieldLatitude'])) $this->options['formFieldLatitude']=controller::$self->url[0].'[latitude]';
		if(!isset($this->options['formFieldLongitude'])) $this->options['formFieldLongitude']=controller::$self->url[0].'[longitude]';
		if(!isset($this->options['type'])) $this->options['type']='ROADMAP';
		if(!isset($this->options['zoom'])) $this->options['zoom']=5;
		return true;
	}

	public function render($view) { ?>
		<?=core::js('//maps.google.com/maps/api/js?key='.$this->options['key'])?>
		<?=core::js('mapMarker')?>
		<div class="map" id="<?=$this->mapId?>" style="height:200px;"></div>
		<p>* Переместите метку на карте в нужное место</p>
		<input type="hidden" name="<?=$this->options['formFieldLatitude']?>" id="<?=$this->mapId?>Latitude">
		<input type="hidden" name="<?=$this->options['formFieldLongitude']?>" id="<?=$this->mapId?>Longitude">
		<script>mapMarkerInit({
			'containerId':'<?=$this->mapId?>',
			'latitude':<?=$this->options['latitude']?>,
			'longitude':<?=$this->options['longitude']?>,
			'zoom':<?=$this->options['zoom']?>,
			'mapTypeId':google.maps.MapTypeId.<?=$this->options['type']?>,
			'formFieldLatitude':'<?=$this->mapId?>Latitude',
			'formFieldLongitude':'<?=$this->mapId?>Longitude'
		});</script>
	<?php }

}