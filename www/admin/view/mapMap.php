<style>* {margin:0;padding:0;}</style>
<div id="map" style="width:100%;height:250px;margin:0 auto;"></div>
<script>
google.maps.event.addDomListener(window,'load',function() {
	var center=new google.maps.LatLng(<?=$_GET['centerLatitude']?>,<?=$_GET['centerLongitude']?>);
	var map=new google.maps.Map(document.getElementById('map'), {
		zoom:<?=$_GET['zoom']?>,
		center:center,
		mapTypeId:google.maps.MapTypeId.<?=$_GET['type']?>,
		mapTypeControlOptions: { mapTypeIds: 'HYBRID' }
	});
	parent._map=map;
	parent._google=google;
});
</script>