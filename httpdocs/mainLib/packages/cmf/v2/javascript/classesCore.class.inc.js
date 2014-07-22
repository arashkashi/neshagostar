function cmfcClassesCore() {
	this.messagesValue=new Array();
	this.message;

	/**
	* 
	*/
	this.raiseError=function (message, code , mode , options , userinfo , error_class , skipmsg ) {

		if (this.messagesValue[code] && !message) {
			message=this.messagesValue[code];
		}
		if (userinfo && typeof(userinfo)!='string' && message ) {
			var replacements=new Array();
			for (key in userinfo) {
				value=userinfo[key];
				replacements['%'+key+'%']=value;
			}
			message=cmfcString.replaceVariables(replacements,message);
		}
		if (!message) { 
			message=' No error message defined!! ';
		}
		return message;
	}

	/**
	* assign object validation methods to handle form validation
	*/
	this.prepare=function () {
	}
	
	/**
	* do the preapre on page load
	*/
	this.prepareOnLoad=function () {
	}
}