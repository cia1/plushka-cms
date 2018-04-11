<?php class widgetMap extends widget {

	public function __invoke() {
		if(isset($this->options['id'])) $this->id=$this->options['id']; else $this->id=time();
		if(!isset($this->options['type'])) $this->options['type']='ROAD';
		$this->jsLink='//maps.google.com/maps/api/js';
		if($this->options['apiKey']) $this->jsLink.='?key='.$this->options['apiKey'];
		if(!isset($this->options['centerLatitude']) && isset($this->options['marker'])) {
			$this->options['centerLatitude']=$this->options['marker'][0]['latitude'];
			$this->options['centerLongitude']=$this->options['marker'][0]['longitude'];
		}
		if(!isset($this->options['zoom'])) $this->options['zoom']=11;
		return true;
	}

	public function adminLink() {
		return array(
			array('map.*','?controller=map&action=marker&id='.$this->id,'setting','Настройка карты и меток')
		);
	}

	public function render($view) {
		echo core::js($this->jsLink);
		?>
		<div class="map" id="map<?=$this->id?>" style="width:100%;"></div>
		<script>
		google.maps.event.addDomListener(window,'load',function() {
			var center=new google.maps.LatLng(<?=$this->options['centerLatitude']?>,<?=$this->options['centerLongitude']?>);
			map=new google.maps.Map(document.getElementById('map<?=$this->id?>'), {
				zoom:<?=$this->options['zoom']?>,
				center:center,
				mapTypeId:google.maps.MapTypeId.<?=$this->options['type']?>
			});
			<?php foreach($this->options['marker'] as $item) { ?>
				var latLng=new google.maps.LatLng(<?=$item['latitude']?>,<?=$item['longitude']?>);
				var marker=new google.maps.Marker({
					map:map,
					position:latLng,
					title:"<?=$item['title']?>"
					<?php if(isset($item['icon'])) { ?>,
					icon:'<?=$item['icon']?>'
					<?php } ?>
				});
				<?php if(isset($item['infoWindow'])) { ?>
					marker.infoWindow=new google.maps.InfoWindow({
						content: "<?=str_replace('"','\"',$item['infoWindow'])?>"
					});
					marker.addListener('click',function() {
    				this.infoWindow.open(map,this);
					});
				<?php } ?>
			<?php } ?>
		});
		</script>
		<?php
	}

}