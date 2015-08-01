<?php class widgetMap extends widget {

	public function __invoke() {
		if(isset($this->options['id'])) $this->id=$this->options['id']; else $this->id=time();
		if(!isset($this->options['type'])) $this->options['type']='ROAD';
		$this->id=$this->options['id'];
		return true;
	}

	public function adminLink() {
		return array(
			array('map.*','?controller=map&action=marker&id='.$this->id,'setting','Настройка карты и меток')
		);
	}

	public function render() {
		echo core::script('http://maps.google.com/maps/api/js?sensor=false');
		?>
		<div class="map" id="map<?=$this->id?>" style="width:100%;height:200px;"></div>
		<script>
		google.maps.event.addDomListener(window,'load',function() {
			var center=new google.maps.LatLng(<?=$this->options['centerLongitude']?>,<?=$this->options['centerLatitude']?>);
			map=new google.maps.Map(document.getElementById('map<?=$this->id?>'), {
				zoom:<?=$this->options['zoom']?>,
				center:center,
				mapTypeId:google.maps.MapTypeId.<?=$this->options['type']?>
			});
			<?php foreach($this->options['marker'] as $item) { ?>
				var latLng=new google.maps.LatLng(<?=$item['longitude']?>,<?=$item['latitude']?>);
				var marker=new google.maps.Marker({
					map:map,
					position:latLng,
					title:"<?=$item['title']?>"
				});
			<?php } ?>
		});
		</script>
		<?php
	}

} ?>