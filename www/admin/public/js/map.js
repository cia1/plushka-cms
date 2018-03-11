var mapMarker=new function(_map) {
	this._list=new Array;
	this._formMarker=null;

	this.setCenter=function(form) {
		var tmp=window._map.getCenter();
		var center=window._map.getCenter();
		form['map[centerLatitude]'].value=tmp.lat();
		form['map[centerLongitude]'].value=tmp.lng();
		form['map[zoom]'].value=window._map.getZoom();
		return false;
	}

	this.add=function(showForm,_marker) {
		if(!_marker) {
			var tmp=window._map.getCenter();
			var latLng=new _google.maps.LatLng(tmp.lat(),tmp.lng());
		} else var latLng=new _google.maps.LatLng(_marker.latitude,_marker.longitude);
		var marker=new _google.maps.Marker({
			map:window._map,
			position: latLng,
			draggable:true,
		});
		if(!_marker) marker.title=''; else marker.title=_marker.title;
		_google.maps.event.addListener(marker,'dragend',function() {
			if(mapMarker._formMarker!=this) return;
			var position=marker.getPosition();
			var form=document.forms.marker;
			form['marker[latitude]'].value=position.lat();
			form['marker[longitude]'].value=position.lng();
		});
		_google.maps.event.addListener(marker,'click',function() {
			mapMarker.form(this);
		});
		this._list.push(marker);
		if(showForm) this.form(marker);
		return false;
	}

	this.form=function(marker) {
		if(marker==this._formMarker) return;
		var form=document.forms.marker;
		if(this._formMarker) {
			this._formMarker.setAnimation(null);
			this._formMarker.title=form['marker[title]'].value;
		}
		this._formMarker=marker;
		form['marker[title]'].value=marker.title;
		var position=marker.getPosition();
		form['marker[latitude]'].value=position.lat();
		form['marker[longitude]'].value=position.lng();
		document.getElementById('markerForm').style.display='';
		document.getElementById('markerDelete').style.display='';
		marker.setAnimation(_google.maps.Animation.BOUNCE);
		return true;
	}

	this.delete=function(marker) {
		if(!marker) marker=this._formMarker;
		if(!marker) {
			alert('Ничего не выбрано');
			return false;
		}
		if(!confirm('Подтвердите удаление метки на кате')) return false;
		for(var i=0;i<this._list.length;i++) {
			if(this._list[i]==marker) {
				this._list.splice(i,1);
			}
		}
		this._formMarker=null;
		marker.setMap(null);
		document.getElementById('markerForm').style.display='none';
		document.getElementById('markerDelete').style.display='none';
		return false;
	}

	this.getJson=function() {
		if(this._formMarker) {
			this._formMarker.title=document.forms.marker['marker[title]'].value;
		}
		var data=[];
		for(var i=0;i<this._list.length;i++) {
			var position=this._list[i].getPosition();
			var marker={title:this._list[i].title,latitude:position.lat(),longitude:position.lng()};
			data.push(marker);
		}
		return JSON.stringify(data);
	}

}