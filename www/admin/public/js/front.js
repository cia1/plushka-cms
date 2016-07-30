function _showError(message) {
	jQuery('.messageError').remove();
	if(message) jQuery('#content').prepend('<div class="messageError">'+message+'</div>');
}

/* ---------------------------------------------- */

jQuery.fn.tab=function(defIndex) {
	if(!defIndex) defIndex=0;
	this.css({'position':'relative','overflow':'hidden'});
	return this.each(function() {
		var container=$(this);
		var leftMargin=0;
		var legend=$('legend',this).each(function() {
			var o=$(this).css('left',leftMargin);
			this.fieldset=o.parent();
			leftMargin+=o.outerWidth(true);
		}).css({'position':'absolute','top':0,'z-index':2});
		container.prepend(legend.remove());
		legend.click(function() {
			var l=$(this);
			var c=l.parent();
			$('fieldset',c).hide().removeClass('active');
			$('legend',c).removeClass('active');
			this.fieldset.show().addClass('active');
			l.addClass('active');
		});
		var o=$(legend[defIndex]);
		var height=0;
		$('fieldset',this).css({'position':'absolute','top':o.outerHeight(true)-1,'left':0}).hide().each(function() {
			h=$(this).outerHeight(true);
			if(h>height) height=h;
		});
		container.height(height+o.outerHeight(true)-1);
		o.click();
	});
};

jQuery(document).ready(function() {
	if(!top.$.adminDialog) return;
	top.$.adminDialog.afterLoad($('#content').outerHeight());
	resizer.onresize=function() {
//		if(this.timeOut) return;
		if(this.timeOut) clearTimeout(this.timeOut);
		this.timeOut=setTimeout(function() {
			top.$.adminDialog.afterLoad($('#content').outerHeight());
		},250);
	};
});