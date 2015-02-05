function featureGroupNew() {
	var title=prompt('Название новой группы характеристик:');
	if(!title) return false;
	$.post(document.mainUrl+'index2.php?controller=shop&action=featureGroupItem',{'shop[title]':title},function(data) {
		var data2=data.split("\n");
		if(data2[0]!='OK') {
			alert(data);
			return;
		}
		document.location=document.featureLink+'&gid='+data2[1];
	});
}
function featureGroupDelete() {
	var id=$('#featureGroup').val();
	if(!id) return false;
	if(!confirm('Подтвердите удаление группы характеристик.')) return false;
	$.post(document.mainUrl+'index2.php?controller=shop&action=featureGroupDelete',{'shop[id]':id},function(data) {
		if(data!='OK') {
			alert(data);
			return;
		}
		document.location=document.featureLink;
	});
}

function featureMove(lst1,lst2) {
	var lst1=$('#feature'+lst1);
	var lst2=$('#feature'+lst2);
	var id=lst1.val();
	var group1=featureGroup(lst1,id);
	var group2=featureGroup(lst2,group1.val());
	if(id[0]=='#') {
		id=id.substr(1);
		if(!group2) {
			lst2.append(group1);
			group2=group1;
		} else group1.remove();
		group2.after($('.g'+id,lst1));
	} else {
		if(!group2) {
			group2=group1.clone();
			lst2.append(group2);
		}
		group2.after($(':selected',lst1));
		if(!$('.g'+group1.val().substring(1),lst1).length) group1.remove();
	}
}
function featureGroup(lst,id) {
	if(id[0]!='#') {
		var data=$('[value="'+id+'"]',lst);
		if(!data.length) return false
		id='#'+data[0].className.substring(1);
	}
	var data=$('[value="'+id+'"]',lst);
	if(!data.length) return false; else return data;
}

function featureCategorySubmit() {
	var s='';
	$('#feature2 option').each(function() {
		if(this.value[0]=='#') return;
		if(s) s+=',';
		s+=this.value;
	});
	document.getElementById('featureField').value=s;
	return false;
}

//function appendFeature() {
//	var html='<div id="asdf"><iframe src="http://ya.ru"></iframe></div>';
//	alert(html);
//}