function mapMarkerInit(config) {
	google.maps.event.addDomListener(window,'load',function() {
		var center=new google.maps.LatLng(config.latitude,config.longitude);
		var map=new google.maps.Map(document.getElementById(config.containerId), {
			zoom:config.zoom,
			center:center,
			mapTypeId:config.mapTypeId,
			zoomControl: true,
			fullscreenControl: true
		});
		var marker=new google.maps.Marker({
			map:map,
			position:center,
			draggable:true
		});
		google.maps.event.addListener(marker,'dragend',function() {
			var position=marker.getPosition();
			document.getElementById(config.formFieldLatitude).value=position.lat();
			document.getElementById(config.formFieldLongitude).value=position.lng();
		});
		map.addListener('click',function(event) {
			marker.setPosition(event.latLng);
			document.getElementById(config.formFieldLatitude).value=event.latLng.lat();
			document.getElementById(config.formFieldLongitude).value=event.latLng.lng();
		});
		document.getElementById(config.formFieldLatitude).value=config.latitude;
		document.getElementById(config.formFieldLongitude).value=config.longitude;
	});
}