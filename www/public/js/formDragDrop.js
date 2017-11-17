function fileDragDrop(namespace,name,dropBox,fileCount,callBack) {
	if(typeof(window.FileReader)=='undefined') {
		var html='<input type="file" name="'+namespace+'['+name+']';
		if(fileCount>1) html+='[]';
		html+='" />';
		dropBox.innerHTML=html;
		$('.fileDragDrop').removeClass('fileDragDrop').addClass('file');
		return false;
	}
	var dropBox=$(dropBox);
	dropBox[0].ondragover=function() {
		dropBox.addClass('hover');
		return false;
	}
	dropBox[0].ondragleave=function() {
		dropBox.removeClass('hover');
		return false;
	}

	var onUpload=function(fileList) {
		if(this._fileIndex==undefined) this._fileIndex=-1;
		dropBox.removeClass('hover').addClass('drop');
		for(var i=0;i<fileList.length;i++) {
			if(++this._fileIndex>=fileCount) {
				if(fileCount==1) alert(document._lang['LNGFileAlreadyUploaded']);
				else alert(document._lang['LNGFileMaximumAlreadyUploaded'].replace('%s',fileCount));
				break;
			}
			var fd=new FormData;
			fd.append("upload",fileList[i]);
			var xhr=new XMLHttpRequest();
			//xhr.upload.addEventListener('progress',uploadProgress,false);
			xhr.onreadystatechange=function(event) {
				if(event.target.readyState!=4) return;
				if(event.target.status!=200) {
					alert('Unknown error');
					return false;
				}
				try {
					var answer=JSON.parse(event.target.responseText);
				} catch(e) {
					alert(event.target.responseText);
					return false;
				}
				if(!answer.uploaded) {
					alert(answer.error.message);
					return false;
				}
				if(callBack && !window[callBack](answer.fileName,answer.url)) return;
				html='<input type="hidden" name="'+namespace+'['+name+']';
				if(fileCount>1) html+='[]';
				html+='" value="upload:'+answer.fileName+'" />';
				html+='<img src="'+answer.url+'" />';
				$(dropBox).append(html);
			}
			xhr.open('POST','/upload.php');
			xhr.send(fd);
		}
		return true;
	};
	$('input[type=file]',dropBox).get(0).onchange=function(event) {
		return onUpload(this.files);
	}
	dropBox[0].ondrop=function(event) {
		event.preventDefault();
		return onUpload(event.dataTransfer.files);
	}
	return true;
}