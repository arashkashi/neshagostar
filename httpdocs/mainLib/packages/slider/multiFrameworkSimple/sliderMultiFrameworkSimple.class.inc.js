/**
*
*/

function cmfcSliderMultiFrameworkSimple() {
	
}
function simpleSliderClass() {
	var _this=this;
	this.instanceName;
	this.slidesInfo;
	this.mode='auto';
	this.selfClick=true; /* if click on active slide button, it will become hide*/
	
	this.getById=function(id) {
		return document.getElementById(id);
	};
	
	this.onClickSlideButton=function(id,slidesInfo) {
		return true;
	};
	
	this.prepareOnLoad=function () {
		if (window.addEventListener) {
			window.addEventListener("load", this.prepare, false);
		} else if (window.attachEvent) {
			window.attachEvent("onload", this.prepare);
		}
	};
	
	this.getActiveFrameworkName=function () {
		if (typeof(jQuery)!='undefined') {
			return 'jquery';
		} else if (typeof(Fx)=='object') {
			return 'mootools';
		}
	};
	
	this.addOnClick=function(elmId,func) {
				
		if (_this.getActiveFrameworkName()=='jquery') {
			//$('#'+elmId).click(func);
			$(this.getById(elmId)).click(func);
			
			
		} else {
			button=_this.getById(elmId);
			try {
				button.addEvent('click', func);
			} catch(e) {
				button.onclick=func;
			}
		}
	};
	
	this.prepare=function () {			
		var key;
		var slideInfo;
		var button;
		var myFunc;
		var slideElm;

		for (key in _this.slidesInfo) {
			slideInfo=_this.slidesInfo[key]; 
			
			/*
			slideElm=_this.getById(key);
			if (slideElm) {
				slideElm.style.height='0px';
				slideElm.style.overflow='hidden';
			}
			*/
			if (typeof(slideInfo.buttonId)!='object' && typeof(slideInfo.buttonId)!='array') {
				slideInfo.buttonId=[slideInfo.buttonId];
			}
			
			for (skey in slideInfo.buttonId) {
				var buttonId=slideInfo.buttonId[skey];
								
				if (typeof(buttonId)=='string') {					
			
					button=_this.getById(buttonId);
					
					if (buttonId=='myTree1Row[23][openCloseButton]') {
						//alert(buttonId);
						//button=document.getElementById(buttonId);
						//alert(button.tagName);
					}
					
					if (button) {
						myFunc=new Function(_this.instanceName+".toggleMulti('"+key+"');return false;");
						_this.addOnClick(buttonId,myFunc);
					}
				}
			}
		}
	};
	
	
	
	this.toggle=function (id) {

		var iconCloseSrc=_this.slidesInfo[id].iconCloseSrc;
		var iconOpenSrc=_this.slidesInfo[id].iconOpenSrc;
		var icon=_this.getById(_this.slidesInfo[id].iconId);

		if (_this.getActiveFrameworkName()=='jquery' && _this.mode=='auto') {
			var element=_this.getById(id);

			if (element.style.height=='0px' || element.style.height=='undefiend') {
				element.style.padding='0px';
				element.style.display='none';
				element.style.height='auto';
			} else {
			}

			$(this.getById(id)).slideToggle('',function() {
				if (element.style.display!='none') {
					if (icon) {
						icon.src=iconOpenSrc;
					}
				} else {
					if (icon) {
						icon.src=iconCloseSrc;			
					}
				}
			});
			return 0;
		     
		} if (_this.getActiveFrameworkName()=='mootools' && _this.mode=='auto') {

			var toggle = new Fx.Height(id, {
				duration: 500,
				onComplete: function(element){
						if (element.style.height!='0px') {
							element.style.height='auto'; 
						}
					},
				onStart: function(element){
					if (element.style.height=='0px') {
						if (icon) {
							icon.src=iconOpenSrc;
						}
					} else {
						if (icon) {
							icon.src=iconCloseSrc;
						}
					}
				}
			});

			toggle.toggle();
		} else {

			var element=_this.getById(id);
			if (element.style.height=='0px' || element.style.display=='none') {
				if (element.style.height=='0px') {
					element.style.height='auto'; 
				}
				element.style.display='';
				if (icon) {
					icon.src=iconOpenSrc;
				}
			} else {
				//element.style.height='0px'; 
				element.style.display='none';
				if (icon) {
					icon.src=iconCloseSrc;
				}
			}
		}

		
		//_this.getById(id).setStyle('display','block');
	};

	this.toggleMulti=function (selectedSlideId) {

		var key;
		var slideInfo;
		var slideElm;
		var selectedSlideElm=_this.getById(selectedSlideId);
		
		_this.onClickSlideButton(selectedSlideId,this.slidesInfo);

        if (_this.selfClick || selectedSlideElm.style.height=='0px' || selectedSlideElm.style.display=='none') {
			for (key in _this.slidesInfo) {
				slideInfo=_this.slidesInfo[key];
				slideElm=_this.getById(key);
				if (slideElm) {
					//_this.toggle(key);
					if ((selectedSlideElm!=slideElm && slideElm.style.height!='0px' && slideElm.style.display!='none') || selectedSlideElm==slideElm) {
						_this.toggle(key);
					}
				}
			}
		}
	};
}