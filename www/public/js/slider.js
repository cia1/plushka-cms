$.fn.sliderHorizontal=function(data) {
	var data=jQuery.extend({
		prevButton:false,
		nextButton:false,
		setInnerWidth:true,
		animateSpeed:'fast',
		auto:2
	},data);
	return this.each(function() {
		var container=$('.sliderContainer',this);
		var item=$('.item',container);
		var max=0;
		for(i=0;i<item.length;i++) {
		 	var div=$(item[i]);
			if(data.setInnerWidth) {
				var inner=$('.inner',div);
тут надо настроить ширину с учётом отступов (паддинг) или вообще забить на это
				alert(inner.outerWidth(true)-inner.innerWidth());
				inner.width(div.innerWidth());
			}
			max+=div.outerWidth(true);
		}
		max-=$(this).width();
		var slider={
			container:container,
			item:item,
			count:item.length,
			current:0,
			max:max*-1,
			offset:0,
			animateSpeed:data.animateSpeed,
			timer:null,
			prev:function() {
				if(this.current==0 || this.offset==0) return false;
				this.current--;
				this.offset+=$(this.item[this.current]).outerWidth(true);
				if(this.offset>0) this.offset=0;
				this.container.animate({'margin-left':this.offset},this.animateSpeed);
				return false;
			},
			next:function() {
				if(this.current==this.count-1 || this.offset<this.max) return false;
				this.current++;
				this.offset-=$(this.item[this.current]).outerWidth(true);
				if(this.offset<this.max) this.offset=this.max;
				this.container.animate({'margin-left':this.offset},this.animateSpeed);
				return false;
			},
			go:function(index) {
				alert('go');
				return false;
			}
		};
		
		if(data.prevButton) $(data.prevButton,this).click(function() { return slider.prev.call(slider)});
		if(data.nextButton) $(data.nextButton,this).click(function() { return slider.next.call(slider)});
	});

}