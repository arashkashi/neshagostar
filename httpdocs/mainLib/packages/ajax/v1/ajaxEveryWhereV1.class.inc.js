/**
* @author : sina salek
* @website : http://sina.salek.ws
*/
function cmfAjaxEverWhereV1() {
	
		this.qsvnAjaxRequest;
		this.qsvnElementsId;
		this.loadingIndicatorGlobal;
		this.loadingIndicatorGlobalEnabled;
		this.loadingIndicator;
		this.loadingIndicatorEnabled;
		this.debugModeLevel;
		this.debugModeEnabled;
		this.timeout;
		this.instanceName;
		this.packageFolderPathBrowser;
		
		/**
		 * Inherit from parent
		 */
		this.inheritFrom = cmfcClassesCore;
		this.inheritFrom();

		/**
		 * Implement commandable and observable
		 */
		cmfImplementCommandable(cmfAjaxEverWhereV1.prototype);
		cmfImplementObservable(cmfAjaxEverWhereV1.prototype);
		
		/**
		 * Contains id of loading indicators which have been created already
		 */
		this.loadingIndicatorItems;
						
		var _this=this;
		
		if ($.browser.name=='msie' && $.browser.versionNumber=='6' ) {
			this.isIe6=true;
		} else {
			this.isIe6=false;
		}
		
		
		/**
		* Accept Form,A, url, id of From or A
		*/
		this.simpleCall=function(trigger,uriOrIdOrElm,elementsId,options) {
			
			var formId;
			var data;
			var form;
			var method;
			var url;
			var element;
			var a;
			var aId;
			
			this.notifyObservers('onStart',{'event':'onSuccess','trigger':trigger,'elementsId':elementsId,'options':options});
			_this.runCommand('startBefore',{'event':'onStart','trigger':trigger,'elementsId':elementsId,'options':options});
			
			if (_this.hasCommandHandler('start')) {
				_this.runCommand('start',{'event':'onStart','trigger':trigger,'elementsId':elementsId,'options':options});
			} else {

				if (typeof(uriOrIdOrElm)=='object') {
					if (uriOrIdOrElm.tagName=='FORM') {
						form=$(uriOrIdOrElm);
					}
					if (uriOrIdOrElm.tagName=='A') {
						a=$(uriOrIdOrElm);
					}
					
				} else if (typeof(uriOrIdOrElm)=='string') {
					element=document.getElementById(uriOrIdOrElm);
					if (element) {
						if (element.tagName=='FORM') {
							form=$(element);
							formId=form.attr('id');
						} else if (element.tagName=='A') {
							a=$(element);
							aId=form.attr('id');
						}
					}
				}
				
				if (a) {
					uri=a.attr('href');
					if (uri=='') {
						uri=window.location;
					}
					method='GET';
					
				} else if (form) {
					uri=form.attr('action');
					if (uri=='') {
						uri=window.location;
					}
					method=form.attr('method');
					if (method.toLowerCase()=='post' || method=='') {
						method='POST';
					} else {
						method='GET';
					}
					
				} else {
					uri=uriOrIdOrElm;
					method='GET';
				}
				
				//--(Begin)-->Parse Url
				var parsedUri=cmfcUrl.parse(uri);
				var elementsIdStr=elementsId.join(',');
				//var link=cmfAjaxEveryWhereV1Urlencode(uri);
				var pageUrl=parsedUri.path;
				var pageQuery=parsedUri.query+'&'+_this.qsvnAjaxRequest+'=1'+'&'+_this.qsvnElementsId+'='+elementsIdStr;
				//--(End)-->Parse Url
				
				//--(Begin)-->Prepare data acording to method
				if (form) {
					/**
					* Does not serialize submit button cause it does not know which one you
					* clicked!
					*/
					//--(Begin)-->Jquery does not send submit button, here is the fix
					var submitId=null;
					if (typeof(trigger)=='object') {
						if ($(trigger).attr('type')=='submit') {
							submitId=cmfcHtml.uniqueId();
							form.append('<input id="'+submitId+'" type="hidden" name="'+$(trigger).attr('name')+'" value="'+$(trigger).attr('value')+'" />');
						}
					}
					//--(End)-->Jquery does not send submit button, here is the fix
					data=form.serialize();
	
					var enctype=form.attr('enctype');
					url=pageUrl+'\?'+pageQuery;
					
					if (submitId) {					
						$('#'+submitId).remove();
					}
					//--(Begin)-->Notify user that upload via ajax is not supported
					if ($('input[type="file"]',form).length>0) {
						message=_this.raiseError('','_4','ERROR_RETURN',null, {} );
						this.displayMessage(message,'warning');
					}
					//--(End)-->Notify user that upload via ajax is not supported
	
				} else {
					url=pageUrl;
					data=pageQuery;
				}
				if (!enctype) {
					var enctype='multipart/form-data';
					var enctype='application/x-www-form-urlencoded';
				}
				//--(End)-->Prepare data acording to method
				
				_this.loadingIndicatorDisplayToggle('onStart',trigger,elementsId,options);
				
				//--(Begin)-->Loop through elements and do some effects like fading out them
				_this.doEffects(elementsId,'fadeOut');
				//--(End)-->Loop through elements and do some effects like fading out them
	
				if (_this.debugModeEnabled==true) {
					window.open(url+'?'+data,'ajax debug window');
					return false;
				}			
	
				$.ajax({
					type: method,
					url: url,
					data: data,
					dataType : 'text', //script
					contentType : enctype,
					//beforeSend: callback,
					//global:true,
					//ifModified:true.
					//async: false,
					//cache: false,
					//processData: false,
					timeout:_this.timeout,//milliseconds, 0 by default
					error: function(xhr, desc, exceptionobj) {
						_this.notifyObservers('onFail',{'event':'onFail','ajaxObject':this,'trigger':trigger,'elementsId':elementsId,'options':options});
						_this.runCommand('failBefore',{'event':'onFail','ajaxObject':this,'xhr':xhr,'desc':desc,'exceptionobj':exceptionobj,'trigger':trigger,'elementsId':elementsId,'options':options});
						if (_this.hasCommandHandler('fail')) {
							_this.runCommand('fail',{'event':'onFail','ajaxObject':this,'xhr':xhr,'desc':desc,'exceptionobj':exceptionobj,'trigger':trigger,'elementsId':elementsId,'options':options});
						} else {
							//	alert('Error loading document');
							_this.loadingIndicatorDisplayToggle('onFail',trigger,elementsId,options);
							_this.doEffects(elementsId,'fadeIn');
						}
						_this.runCommand('failAfter',{'event':'onFail','ajaxObject':this,'xhr':xhr,'desc':desc,'exceptionobj':exceptionobj,'trigger':trigger,'elementsId':elementsId,'options':options});
						
					},
					success: function(data,textStatus) {
						_this.notifyObservers('onSuccess',{'event':'onSuccess','ajaxObject':this,'data':data,'textStatus':textStatus,'trigger':trigger,'elementsId':elementsId,'options':options});
						_this.runCommand('successBefore',{'event':'onSuccess','ajaxObject':this,'data':data,'textStatus':textStatus,'trigger':trigger,'elementsId':elementsId,'options':options});
						if (_this.hasCommandHandler('success')) {
							_this.runCommand('success',{'event':'onSuccess','ajaxObject':this,'data':data,'textStatus':textStatus,'trigger':trigger,'elementsId':elementsId,'options':options});
							
						} else {
							//--(Begin)-->Validate server respond and execute it
							var myregexp = /\/\/-_-_\(SIZE:([^()]*)\)_-_-;/g;
							var match = myregexp.exec(data);
							if (match!=null) {
								var orgSize=match[1];
								data = data.replace(myregexp,"");
								var size=data.length;
								//alert(orgHash+'  /   '+hash);
								if (orgSize==size) {
									$.globalEval(data);
								} else {
									message=_this.raiseError('','_3','ERROR_RETURN',null, {'recievedSize':data.length,'realSize':orgSize} );
									_this.displayMessage(message,'error');
								}
							} else {
								$.globalEval(data);
							}
							//--(End)-->Validate server respond and execute it
							
							_this.loadingIndicatorDisplayToggle('onSuccess',trigger,elementsId,options);
							_this.doEffects(elementsId,'fadeIn',{},function () {
								//Some browsers create the elements only when they're visisble , this event exists for this reason
								_this.runCommand('successAfterDone',{'event':'onSuccess','ajaxObject':this,'data':data,'textStatus':textStatus,'trigger':trigger,'elementsId':elementsId,'options':options});
							});
							//alert( "Data Saved: "+html);
						}
						_this.runCommand('successAfter',{'event':'onSuccess','ajaxObject':this,'data':data,'textStatus':textStatus,'trigger':trigger,'elementsId':elementsId,'options':options});
					}
				});
				
				_this.runCommand('startAfter',{'event':'onStart','ajaxObject':this,'trigger':trigger,'elementsId':elementsId,'options':options});
			}
			
			return false;
		};
		
		/**
		 * 
		 */
		this.displayMessage=function(message,type) {
			if (_this.hasCommandHandler('displayMessage')) {
				return _this.runCommand('displayMessage',{'message':message,'type':type});
			} else {
				if (type=='error' || type=='warning') {
					alert(message);
				}
			}
		};
		
		
		/**
		 * 
		 */
		this.doEffects=function (elementsId,effectName,options,func) {
			if (!options) {
				options=new Array();
			}
			
			for (x in elementsId) {
				var elementId=elementsId[x];
				if (typeof(elementId)!='function') {
					if (effectName=='fadeOut') {
						//$('#'+elementId).fadeOut(300,0,func);
						if (_this.isIe6 && ($('#'+elementId).css('backgroundColor')=='none' || $('#'+elementId).css('backgroundColor')=='' || $('#'+elementId).css('backgroundColor')=='transparent' )) {
							$('#'+elementId).css('visibility', 'hidden');
							if (typeof(func)=='function') {
								func();
							}
						} else {

							if (!options['targetOpacity'] && options['targetOpacity']!==0) {
								options['targetOpacity']=0.1;
							}
							
							$('#'+elementId).fadeTo(300,options['targetOpacity'],func);
						}
						
						
					} else if (effectName=='fadeIn') {
						//$('#'+elementId).fadeIn(300,100,func);
						if (_this.isIe6 && ($('#'+elementId).css('backgroundColor')=='none' || $('#'+elementId).css('backgroundColor')=='' || $('#'+elementId).css('backgroundColor')=='transparent' )) {
							$('#'+elementId).css('visibility', 'visible');
							if (typeof(func)=='function') {
								func();
							}
						} else {
							$('#'+elementId).fadeTo(300,1,func);
						}
					}
				}
			}
		};

		 
		
		/**
		 * 
		 */
		this.generateId=function (type,elementId) {
			var id;
			if (type=='loadingIndicator_global') {
				id=_this.instanceName+'Li'+'Global';
				return id;
			}
			if (type=='loadingIndicator_trigger') {
				id=_this.instanceName+'Li'+'Trigger_';
				return id;
			}
			if (type=='loadingIndicator_content') {
				id=_this.instanceName+'Li'+'Content_'+elementId;
				return id;
			}
			return false;
		};
		
		/**
		 * 
		 */
		this.loadingIndicatorPrepare=function (info,triggerId,options) {
			var imgSrc='';
			
			if (!$('#'+info['id']).length>0) {
				if (info['imageUrl']) {
					var imgSrc=info['imageUrl'];
				}
				html='<img id="'+info['id']+'" src="'+_this.loadingIndicatorImageUrl(imgSrc)+'" style="display:none;" alt="Loading..." />';
				
				$(html).prependTo('body');
 
				/*
				if (!triggerId || 1==1) {
				} else if (info['name']=='trigger') {
					if (typeof(triggerId)=='object') {
						$(triggerId).prepend(html);
					} else {
						$('#'+triggerId).prepend(html);
					}
					
				} else if (info['name']=='content') {
					$('#'+triggerId).prepend(html);					
				}
				*/
			}			
			var indicatorJqElm=$('#'+info['id']);
			
			if (info['position']) {
				if (info['position']['type']=='fixed') {
					if (_this.isIe6) {
						indicatorJqElm.css('position','absolute');
					} else {
						indicatorJqElm.css('position','fixed');
					}
				}
				
				if (info['position']['type']=='absolute') {
					indicatorJqElm.css('position','absolute');
				}
			}
			
			if (triggerId && info['position']) {
				if (typeof(triggerId)=='object') {
					var ownerJqElm=$(triggerId);
				} else {
					var ownerJqElm=$('#'+triggerId);
				}
				
				if (typeof(ownerJqElm)=='object') {
					var ownerPos=ownerJqElm.offset();
					var newPos={left:ownerPos.left,top:ownerPos.top};
					if (info['id']=='myAjaxLiContent_area2') {
						//alert(ownerPos.left+' '+ownerPos.top);
					}
					
					if (info['position']['horizontal']=='left') {
						if (info['position']['area']=='inside') {
							newPos.left=ownerPos.left;
						} else {
							newPos.left=ownerPos.left-indicatorJqElm.width();
						}
					}
					if (info['position']['vertical']=='center') {
						newPos.top=ownerPos.top+(ownerJqElm.height()/2)-(indicatorJqElm.width()/2);
					}
					if (info['position']['horizontal']=='right') {
						if (info['position']['area']=='inside') {
							newPos.left=ownerPos.left+ownerJqElm.width()-indicatorJqElm.width();
						} else {
							newPos.left=ownerPos.left+ownerJqElm.width();
						}
					}
					if (info['position']['vertical']=='top') {
						if (info['position']['area']=='inside') {
							newPos.top=ownerPos.top;
						} else {
							newPos.top=ownerPos.top-indicatorJqElm.height();
						}
					}
					if (info['position']['horizontal']=='center') {
						newPos.left=ownerPos.left+(ownerJqElm.width()/2)-(indicatorJqElm.height()/2);
					}
					if (info['position']['vertical']=='bottom') {
						if (info['position']['area']=='inside') {
							newPos.top=ownerPos.top+ownerJqElm.height()-indicatorJqElm.height();
						} else {
							newPos.top=ownerPos.top+ownerJqElm.height()+indicatorJqElm.height();
						}
					}
					indicatorJqElm.css('left',newPos.left);
					indicatorJqElm.css('top',newPos.top);
				}
			}

			
			return info;
			
		};
		
		/**
		 * 
		 */
		this.loadingIndicatorImageUrl=function(image) {
			if (image.indexOf('packageUrl:')>-1) {
				image=image.replace('packageUrl:',_this.packageFolderPathBrowser+'/');
			}
			return image;
		};
		
		/**
		 * Show and Hide loading indicator
		 * http://docs.jquery.com/CSS
		 */
		this.loadingIndicatorDisplayToggle=function (event,triggerId,targetIds,options) {			
			if (_this.hasCommandHandler('loading')) {
				return this.runCommand('loading',{'event':event,'triggerId':triggerId,'targetIds':targetIds,'options':options});
			} else {
				var html;
				var style;
				var info;
				var infoOriginal;
				
				//--(Begin)-->Analyze and prepare options
				//if (!options['loadingIndicator'] && _this.loadingIndicatorEnabled==true) {
				if (!options['loadingIndicator']) {
					options['loadingIndicator']=_this.loadingIndicator;
				} else {
					if (options['loadingIndicator']['trigger']) {
						if (options['loadingIndicator']['trigger']['merge']!==false) {
							options['loadingIndicator']['trigger']=cmfcArray.mergeRecursive(_this.loadingIndicator['trigger'],options['loadingIndicator']['trigger']);
						}
					} else {
						options['loadingIndicator']['trigger']=_this.loadingIndicator['trigger'];
					}
					if (options['loadingIndicator']['content']) {
						if (options['loadingIndicator']['content']['merge']!==false) {
							options['loadingIndicator']['content']=cmfcArray.mergeRecursive(_this.loadingIndicator['content'],options['loadingIndicator']['content']);
						}
					} else {
						options['loadingIndicator']['content']=_this.loadingIndicator['content'];
					}
				}
				if (_this.loadingIndicatorGlobal['enabled']) {
					options['loadingIndicator']['global']=_this.loadingIndicatorGlobal;
				} else {
					//options['loadingIndicator']['global']=null;
				}
				//--(End)-->Analyze and prepare options
				
				for (name in options['loadingIndicator']) if (typeof(name)!='function') {
					info=infoOriginal=options['loadingIndicator'][name];
	
					if (info['enabled']==true) {
						info['name']=name;
						
						var elementsId=[document.body];
						if (info['name']=='trigger') {
							elementsId=[triggerId];
						} else if (info['name']=='content') {
							elementsId=targetIds;
						}
		
						for (x in elementsId) if (typeof(elementsId[x])!='function') {
							var elementId=elementsId[x];
		
							if (!info['id'] || info['name']=='content') {
								info['id']=_this.generateId('loadingIndicator_'+name,elementId);
							}

							info=_this.loadingIndicatorPrepare(info,elementId,options);
							
							if (event=='onStart') {
								$('#'+info['id']).fadeIn(300);
								//_this.doEffects([info['id']],'fadeIn');
								
							} else if (event=='onSuccess') {
								$('#'+info['id']).fadeOut(300);
								
							} else if (event=='onFail') {
								_this.doEffects([info['id']],'fadeOut');
								$('#'+info['id']).fadeOut(300);
							}
						}
					}
				}
				
				//document.getElementById(_this.loadingIndicatorDivId).style.position='fixed';
				//--(Beign)-->Create Global ajax indicator if its wasn't defined
	
				//--(End)-->Create Global ajax indicator if its wasn't defined
				/*
				$("#"+_this.jsLoadingIndicatorDivId).ajaxStart(function(){
				   $(this).fadeIn(200);
				});
				
				$("#"+_this.jsLoadingIndicatorDivId).ajaxStop(function(){
				   $(this).fadeOut(200);
				});
				*/
			}
		};
		

}