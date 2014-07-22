/**
* @author : sina salek
* @website : http://sina.salek.ws
*/
function cmfcValidationV1() {
	this.formName;
	this.formObj;
	this.fieldsInfo=new Array();
	this.fieldTypesInfo=new Array();
	this.messagesValue=new Array();
	this.messagesCode=new Array();

	this.message;
	this.displayMethod='alert';
	this.displayMethodOptions;
	this.displayModesInfo;
	this.commandHandlers=new Array();
	
	var _this=this;
	
	this.callUserFunction=function (name,params) {
		if (params) {
			var paramsStr='';
			var comma='';
			for (i in params) {
				paramsStr+=comma+'params["'+i+'"]';
				comma=',';
			}
		}

		if (typeof(name)=='string') {
			var s=name+'('+paramsStr+')';
			var result=eval(s);
		} else if (typeof(name)=='array' || typeof(name)=='object') {
			var s='name[0].'+name[1]+'('+paramsStr+')';
			var result=eval(s);
		}
		
		return result;
	};
	
	this.isEmailValid=function (email) {
		var str=email;
		var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		if (filter.test(str))
			testresults=true;
		else {
			testresults=false;
		}
		return (testresults)
	};
	
	this.addCommandHandler=function (cmd, commandHandler,parameters) {
		if (!_this.commandHandlers[cmd]) _this.commandHandlers[cmd]=new Array();
		_this.commandHandlers[cmd].push(commandHandler);
	};
	
	this.prependCommandHandler=function (cmd, commandHandler,parameters) {
		if (!_this.commandHandlers[cmd]) _this.commandHandlers[cmd]=new Array();
		_this.commandHandlers[cmd].unshift(commandHandler);
	};
	
	this.runCommand=function (cmd,params) {
		if (_this.commandHandlers[cmd])
		for (i in _this.commandHandlers[cmd]) {
			var commandHandler=_this.commandHandlers[cmd][i];
			if (typeof(commandHandler)!='function') {
				return _this.callUserFunction(commandHandler,{'obj':_this,'cmd':cmd,'params':params});
			}
		}
	};
	
	this.isUrlValid=function (value){
		var str=value;
		var filter=/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i;
		if (filter.test(str))
			testresults=true;
		else {
			testresults=false;
		}
		return (testresults);

		/*
		var urlregex = new RegExp("^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}[0-9A-Za-z\.\-]*\.[0-9A-Za-z\.\-]*$");
		if(urlregex.test(value)) {
			return true;
		} return false;
		*/
	};
	
	this.isNumeric=function (value) {
		if ((isNaN(value)) || (value.length == 0))
			return false;
		return true;
	};
	
	this.isString=function (value) {
		if (typeof(value)=='string')
			return true;
		return false;
	};
	
	this.isEmpty=function(value) {
		if (!value) return true;
		return false;
	};
	
	
	this.getAllElements=function(docObj) {
		var all = docObj.all ? docObj.all : docObj.getElementsByTagName('*');
		return all;
	};
	
	
	this.getElementsByName=function (docObj,name) 
	{
		if (!_this.elementsByNameCache) {
			_this.elementsByNameCache=new Array();
			
			if (docObj==null) {docObj=document;}
			var all = docObj.all ? docObj.all : docObj.getElementsByTagName('*');
			var elements = new Array();
			var n=0;
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];

				if (elm.name) {
					if (typeof(_this.elementsByNameCache[elm.name])!='array' && typeof(_this.elementsByNameCache[elm.name])!='object') {
						_this.elementsByNameCache[elm.name]=new Array();
						_this.elementsByNameCache[elm.name][0]=elm;
					} else {
						_this.elementsByNameCache[elm.name][_this.elementsByNameCache[elm.name].length+1]=elm;
					}
					//alert(elm.name+':'+_this.elementsByNameCache[elm.name].length);
				}
			}
		}
       
		var elements=_this.elementsByNameCache[name];
		
		
		if (elements)
			return elements;
		else
			return false;
	};
	
	/**
	* replace a variable 
	* @param needle string
	* @param _replace string
	* @param text string
	*/
	this.strReplace=function (needle, _replace, text) {
		//return text.split(needle).join(replacement);
		if (text.indexOf(needle)!=-1) {
			//alert('needle : '+needle+' , replace : '+_replace+' , text : '+text);
			text = text.replace(needle, _replace);
			//alert('needle : '+needle+' , replace : '+_replace+' , text : '+text);
		}
		
		return text;
	};
	
	/**
	* replace variables with replacements in string
	* @param replacements array
	* @param text string
	*/
	this.replaceVariables=function (replacements,text) {
		for (key in replacements) {
			value=replacements[key];
			text=_this.strReplace(key,value,text);
			
		}
		return text;
	};
	
	/**
	* 
	*/
	this.raiseError=function (message, code , mode , options , userinfo , error_class , skipmsg ) {

		if (_this.messagesValue[code] && !message)
			message=_this.messagesValue[code];

		if (userinfo && typeof(userinfo)!='string' && message ) {
			var replacements=new Array();
			for (key in userinfo) {
				value=userinfo[key];
				replacements['__'+key+'__']=value;
			}
			message=_this.replaceVariables(replacements,message);
			
		}
		if (!message) message=' No error message defined!! ';
		return message;
	};
	
    /**
    * get field object
    */
	this.getFormFieldObject=function (name,parent) {
		if (!parent) parent=document;

		var elms=_this.getElementsByName(parent,name);
		
		if (elms) {
			var x=0;
			var found=false;
			var foundIndex=0;
			for (var x = 0; x < elms.length; x++) {
				if (typeof(elms[x])=='object') {
				
					if (elms[x].type=='radio') {
						if (elms[x].checked)
							if (elms[x].checked==true)
								foundIndex=x;
					}

					if (elms[x].type=='checkbox') {
						if (elms[x].checked)
							if (elms[x].checked==true)
								foundIndex=x;
					}
				}
				
			}						
			return elms[foundIndex];
		}
		return false;
	};
	
	this.pause=function (millis) {
		var date = new Date();
		var curDate = null;

		do { curDate = new Date(); }
		while(curDate-date < millis)
	};
	
	/**
	* disable or enable field validation dynamically
	*/
	this.changeFieldVerificationDisabled=function (name,value) {
		if (_this.fieldsInfo[name])
			_this.fieldsInfo[name]['disabled']=value;
	};
	
	
	/**
	* validate field according to fieldInfo
	* @param fieldInfo array //contains require info for validating field
	*/
	this.validateField=function (fieldInfo) {
		//var fieldInfo=params['fieldInfo'];
		//var _this=obj;
		var message='';
		var fieldTypeInfo;
		var fieldValue;
		
		if (fieldInfo['type']!='')
			if (_this.fieldTypesInfo[fieldInfo['type']])
				fieldTypeInfo=_this.fieldTypesInfo[fieldInfo['type']];
		
		if (fieldInfo['disabled']==true) return true;
		
		var likeType;
		var type=fieldInfo['type'];

		if (fieldInfo['likeType'])
			likeType=fieldInfo['likeType'];
		else
			likeType=fieldInfo['type'];

		if (likeType=='number' || likeType=='string' || likeType=='url' || likeType=='email' || likeType=='checkBox' || 
				type=='number' || type=='string' || type=='url' || type=='email' || type=='checkBox' || type=='password') {
			var fieldObj=_this.getFormFieldObject(fieldInfo['name'],_this.formObj);
			      
			if (fieldObj)
				fieldValue=fieldObj.value;
			else {
				message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Field_Does_No_Exists'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'fieldName':fieldInfo['name']} );
			}
		}
        
		if (!message)
		switch (likeType) {
			case 'number' : 
				var isFieldValueEmpty=false;
				if (_this.isEmpty(fieldValue) && fieldObj && (fieldValue==fieldInfo['param']['emptyValue'] || fieldInfo['param']['emptyValue']==null))
					isFieldValueEmpty=true;
				if (fieldInfo['param']['notEmpty']==true && isFieldValueEmpty) {
				//if (fieldInfo['param']['notEmpty']==true && _this.isEmpty(fieldValue) && fieldObj && fieldValue!=fieldInfo['param']['emptyValue']) {
					message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Is_Empty'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				}
				
				if(!isFieldValueEmpty) {
					if (message=='') {
						 if (!_this.isNumeric(fieldValue) && fieldValue!='')
							message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Number'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue} );
					}
					if (message=='') {
						if (fieldInfo['param']['countMin'] || fieldInfo['param']['countMax']) {
							if (!fieldInfo['param']['countMin']) fieldInfo['param']['countMin']='*';
							if (!fieldInfo['param']['countMax']) fieldInfo['param']['countMax']='*';
						
							if ( !((fieldValue.length>=fieldInfo['param']['countMin'] || fieldInfo['param']['countMin']=='*') && (fieldValue.length<=fieldInfo['param']['countMax'] || fieldInfo['param']['countMax']=='*')) )
								message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Within_Count_Range'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue,'min':fieldInfo['param']['countMin'], 'max':fieldInfo['param']['countMax']} );
						}
					}
					if (message=='') {
						if (fieldInfo['param']['min'] || fieldInfo['param']['max']) {
							if (!fieldInfo['param']['min']) fieldInfo['param']['min']='*';
							if (!fieldInfo['param']['max']) fieldInfo['param']['max']='*';
						
							if ( !((fieldValue>=fieldInfo['param']['min'] || fieldInfo['param']['min']=='*') && (fieldValue<=fieldInfo['param']['max'] || fieldInfo['param']['max']=='*')) )
								message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Within_Range'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue,'min':fieldInfo['param']['min'], 'max':fieldInfo['param']['max']} );
						}
					}
				}
			break;
			case 'email' :
				if (fieldInfo['param']['notEmpty']==true && _this.isEmpty(fieldValue) && fieldObj) {
					message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Is_Empty'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				} 
				if (!message && !_this.isEmpty(fieldValue))
					if (!_this.isEmailValid(fieldValue)) message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Valid_Email'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue} );
			break;
			case 'url' :
				if (fieldInfo['param']['notEmpty']==true && _this.isEmpty(fieldValue) && fieldObj) {
					message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Is_Empty'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				} 
				if (!message && !_this.isEmpty(fieldValue))
					if (!_this.isUrlValid(fieldValue)) message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Valid_Url'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue} );
			break;
			case 'dropDownDate' :
				if (fieldInfo['param']['notEmpty']==true) {
					var dayObj=_this.getFormFieldObject(fieldInfo['name']+'[day]', _this.formObj);
					var monthObj=_this.getFormFieldObject(fieldInfo['name']+'[month]', _this.formObj);
					var yearObj=_this.getFormFieldObject(fieldInfo['name']+'[year]', _this.formObj);

					if (_this.isEmpty(dayObj.value) || _this.isEmpty(monthObj.value) || _this.isEmpty(yearObj.value))
						message=_this.raiseError('', _this.messagesCode['CMF_ValidationV1_Is_Empty'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				}
			break;
			case 'checkBox' :
				fieldValue=fieldObj.checked;
				if (fieldInfo['param']['notEmpty']==true && fieldValue!=true && fieldObj)
					message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Is_Not_Selected'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
			break;
			case 'password' :
				if (fieldInfo['param']['notEmpty']==true && _this.isEmpty(fieldValue) && fieldObj) {
					message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Is_Empty'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				}
				
				if (!message) {
					var confirmationFieldObj=_this.getFormFieldObject(fieldInfo['param']['confirmationFieldName'], _this.formObj);
					var confirmationFieldValue=confirmationFieldObj.value;
					if (fieldValue!=confirmationFieldValue)
						message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Password_And_Its_Confirmation_Are_Not_Same'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				}
			break;
			case 'string' :
				if (fieldInfo['param']['notEmpty']==true && _this.isEmpty(fieldValue) && fieldObj) {
					message=_this.raiseError( '', _this.messagesCode['CMF_ValidationV1_Is_Empty'], 'ERROR_RETURN', null, {'title':fieldInfo['title'],'value':fieldValue} );
				}
				
				if (message=='') {
					 if (!_this.isString(fieldValue))
						message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_String'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue} );
				}
				
				if (message=='') {
					if (fieldInfo['param']['lengthMin'] || fieldInfo['param']['lengthMax']) {
						if (!fieldInfo['param']['lengthMin']) fieldInfo['param']['lengthMin']='*';
						if (!fieldInfo['param']['lengthMax']) fieldInfo['param']['lengthMax']='*';
					
						if ( !((fieldValue.length>=fieldInfo['param']['lengthMin'] || fieldInfo['param']['lengthMin']=='*') && (fieldValue.length<=fieldInfo['param']['lengthMax'] || fieldInfo['param']['lengthMax']=='*')) )
							message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Within_Length_Range'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue,'min':fieldInfo['param']['lengthMin'], 'max':fieldInfo['param']['lengthMax']} );
					}
				}
				
				if (message=='') {
					if (fieldInfo['param']['jsRegexp']) {
						var myregexp = eval(fieldInfo['param']['jsRegexp']);
						
						if (myregexp.exec(fieldValue)==null)
							message=_this.raiseError('',_this.messagesCode['CMF_ValidationV1_Is_Not_Match_With_Pattern'],'ERROR_RETURN',null, {'title':fieldInfo['title'],'value':fieldValue,'desc':fieldInfo['param']['regexpDescription']} );
					}
				}
			break;
		}
		//--(Begin)-->custom type
		if (message=='') {
			if (fieldTypeInfo)
				if (fieldTypeInfo['jsValidationHandler']!='') {
					var s=fieldTypeInfo['jsValidationHandler']['function']+'('+'_this,fieldInfo,fieldValue'+')';
					message=eval(s);
				}
		}
		//--(End)-->custom type							
		
		if (message!=null && message!='' && message) {
			return message;
		}
		return false;
	};
	
	/**
	* display generated messages by validate form in various ways 
	* @param fieldsInfo array
	* @param messsages array //generated messages
	*/
	this.displayError=function (fieldsInfo,messages) {
		var result=false;

		if (_this.displayMethod=='alert' && messages!==false) {
			var messagesStr='';
			for (key in messages) if (typeof(messages[key])!='function') {
				//alert(key+'|'+messages[key]+'|');
				fieldInfo=_this.fieldsInfo[key];
				if (messages[key]!='') {
					messagesStr+=messages[key]+'\n';
				}
			}
			//if (messagesStr!='')
			alert(messagesStr);
			result=true;
		}
		
		if (_this.displayMethod=='div') {
			var messagesStr='';
			messagesStr='<ul>';
			for (key in messages) if (typeof(messages[key])!='function') {
				fieldInfo=_this.fieldsInfo[key];
				if (messages[key]) {
					messagesStr+='<li>'+messages[key]+'</li>';
				}
			}
			messagesStr+='</ul>';
			var container=document.getElementById(_this.displayMethodOptions['id']);
			container.innerHTML=messagesStr;
			result=true;
		}
		
		if (_this.displayMethod=='pageCenterDiv' || _this.displayMethod=='formCenterDiv') {
			if (_this.displayMethod=='formCenterDiv') {
				var divContainer=_this.formObj;
			}
			
			var messagesStr='';
			messagesStr='<ul>';
			for (key in messages) if (typeof(messages[key])!='function') {
				fieldInfo=_this.fieldsInfo[key];
				if (messages[key]) {
					messagesStr+='<li>'+messages[key]+'</li>';
				}
			}
			messagesStr+='</ul>';
			messagesStr+='<div style="text-align:center;padding:4px"><a href="javascript:void(0)" onclick="this.parentNode.parentNode.style.display=\'none\'">[OK]</a></div>';
			
			if (!divContainer)
				var divContainer=document.body;
			
			if (!_this.centerDiv) {

				_this.centerDiv = document.createElement('div');
				//newdiv.setAttribute('id', id);
				_this.centerDiv.className='cmfcValidationV1ErrorMessageBoard';
				_this.centerDiv.style.position='absolute';
				if (_this.defaultStylesEnabled==true) {
					_this.centerDiv.style.background="white";
					_this.centerDiv.style.border="1px solid";
				}
				document.body.appendChild(_this.centerDiv );
			}
			
			_this.centerDiv.innerHTML=messagesStr;
			_this.centerDiv.style.display='';

			if (divContainer==document.body) {
				var scrollTop=typeof(window.pageYOffset)!='undefined' ? window.pageYOffset : document.documentElement.scrollTop;
				var scrollLeft=typeof(window.pageYOffset)!='undefined' ? window.pageXOffset : document.documentElement.scrollLeft;
			} else {
				var scrollTop=0;
				var scrollLeft=0;
			}
			var IpopTop = (divContainer.clientHeight - _this.centerDiv.offsetHeight) / 2 ;
			var IpopLeft = (divContainer.clientWidth - _this.centerDiv.offsetWidth) / 2;

			_this.centerDiv.style.left=IpopLeft + scrollLeft+'px';
			_this.centerDiv.style.top=IpopTop + scrollTop+'px';
			
			//this.pause(1000);
			result=true;
		}
		
		if (_this.displayMethod=='nearFields') {
			for (key in messages) if (typeof(messages[key])!='function') {
				var message=messages[key];
				var messageBoard=null;
				
				var fieldInfo=_this.fieldsInfo[key];
				if (fieldInfo['jsMessageBoardObject']) {
					messageBoard=fieldInfo['jsMessageBoardObject'];
				} else if(fieldInfo['jsMessageBoardId']) {
					messageBoard=document.getElementById(fieldInfo['jsMessageBoardId']);
					fieldInfo['jsMessageBoardObject']=messageBoard;
					
					if (!fieldInfo['jsMessageBoardObject'])
						alert('Message board "'+fieldInfo['jsMessageBoardId']+'" of field "'+fieldInfo['name']+'" id does not exists');
				}
				
				if (!messageBoard) {
					var fieldObj=_this.getFormFieldObject(fieldInfo['name'],_this.formObj);
					var messageBoard = document.createElement('div');
					messageBoard.className='cmfcValidationV1ErrorMessageBoard';

					fieldInfo['jsMessageBoardId']=messageBoard.id=fieldInfo['name']+'MsgBoard';
					if (_this.defaultStylesEnabled==true) {
						messageBoard.style.background="white";
						messageBoard.style.border="1px solid";
					}
					fieldObj.parentNode.appendChild(messageBoard);
					_this.fieldsInfo[key]['jsMessageBoardObject']=messageBoard;
				} else {
					
				}

				if (message) {
					messageBoard.innerHTML=message;
					messageBoard.style.display='';
				} else {
					messageBoard.style.display='none';
				}
			}
			result=true;
		}
		
		
		//--(Begin)-->custom display mode
		if (result==false) {
			if (_this.displayModesInfo) {
				var displayModeInfo=_this.displayModesInfo[_this.displayMethod];
				
				if (displayModeInfo['jsHandler']) {
					var s=displayModeInfo['jsHandler']['function']+'('+'_this,fieldsInfo,messages,_this.displayMethodOptions'+')';
					eval(s);
				}
			}
			result=true;
		}
		//--(End)-->custom display mode
		
		return result;
	};
	
	/**
	* merge 2 dimensional arrays
	*/
	this.arrayMerge=function (array1,array2) {
		var rArray=array1;
		for (i in array2) {
			rArray[i]=array2[i];
		}
		return rArray;
	};
 	
 	/**
 	* trigger after submiting form
 	*/
	this.onFormSubmit=function (e) {
		var allMessages=new Array();
		var isValid=true;
		var messages;
		messages=_this.runCommand('validateBefore',{'fieldsInfo':_this.fieldsInfo});
		if (messages!==true && messages) {
			allMessages=_this.arrayMerge(allMessages,messages);
			isValid=true;
		}
		messages=_this.runCommand('validate',{'fieldsInfo':_this.fieldsInfo});
		if (messages!==true && messages) {
			allMessages=_this.arrayMerge(allMessages,messages);
			isValid=false;
		}
		messages=_this.runCommand('validateAfter',{'fieldsInfo':_this.fieldsInfo});
		if (messages!==true && messages) {
			allMessages=_this.arrayMerge(allMessages,messages);
			isValid=false;
		}

		
		if (!isValid) {
			_this.displayError(_this.fieldsInfo,allMessages);
			return false;
		} else {
			var r=_this.runCommand('afterSuccessfulValidation',{'fieldsInfo':_this.fieldsInfo});
			if (r===false) {
				return false;
			}
			return true;
		}
	};
	
	/**
	* assign built in function to handle form validation
	*/
	this.addCommandHandler('validate',[_this,'validateForm']);
	
	/**
	* built in form validation function
	*/
	this.validateForm=function (obj,cmd,params) {
		var messages=new Array();
		var fieldInfo;
		var fieldObj;
		var key;
		_this.elementsByNameCache='';
		var isCompletelyValid=true;
		
		for (key in _this.fieldsInfo) {
			fieldInfo= _this.fieldsInfo[key];

			try {
				messages[key]=_this.validateField(fieldInfo);
				
				if (messages[key]!==false) {
					isCompletelyValid=false;
				}
			} catch(err) {
				messages[key]='Error Description : '+err.description;
				isCompletelyValid=false;
			}
		}

		if (isCompletelyValid===false) return messages;
		return true;
	};
	
	/**
	* assign object validation methods to handle form validation
	*/
	this.prepare=function () {
		if (_this.formName!='' && _this.formName!=null) {
			
			_this.formObj=document.getElementsByName(_this.formName)[0];
			if (!_this.formObj) {
				alert('cmfcValidation : the specified form does not exists!');
			} else {
				var obj=_this.getAllElements(_this.formObj);
				if (!obj.length>0) alert('cmfcValidation : the specified form does not have any field (The form tag might not be W3C valid)!');
			}
			_this.formObj.onsubmit=function () { return _this.onFormSubmit()};
		}
	};
	
	/**
	* do the preapre on page load
	*/
	this.prepareOnLoad=function () {
		if (window.addEventListener) {
			window.addEventListener("load", this.prepare, false)
		} else if (window.attachEvent) {
			window.attachEvent("onload", this.prepare)
		}
	};
}