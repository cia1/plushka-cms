$('form#comment').ajaxForm({success:function(data) {
	if(data!='OK') {
		alert(data);
		return;
	}
	$('form#comment').remove();
	alert(document._lang['commentMessage']);
	$("#commentList").load('<?=core::url()?>index2.php?controller=comment&action=list&link=<?=$this->link?>');
} });