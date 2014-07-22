<?php
/*
	this class makes using javascript functions easy
	
	$js=new cJavscriptFunctions();
	$js->printJsFunctions(array('ConfimationMessage'));
	
	<a href="#" onclick="return <?=$js->jsfConfimationMessage('Are you sure?')?>">delete</a>
*/

class cmfcHierarchicalSystemJavscriptFunctions{
	var $_prefix='cmf';
	var $_printedJsFunctions=array();
	
	var $_jsFunctionsInfo=array(
		'StrReplace'=>array(
			'compatibleBrowsers'=>array('Firefox','IE','Opera')
		)
	);
	
	function setPrefix($prefix) {
		$this->_prefix=$prefix;
	}
	
	function jsfPopitup($url,$width,$height,$showType) {
		list($url,$width,$height,$showType)=$this->phpParamsToJs(func_get_args());
		return $this->_prefix."Popitup($url,$width,$height,$showType);";
	}
	
	function jsfStrReplace($search,$replace,$subject) {
		return $this->_prefix."StrReplace($subject, $search, $replace);";
	}
	
	function jsfGetElements($docObjVarName) {
		return $this->_prefix."GetElements($docObjVarName);";
	}
	
	function jsfGetElementsByName($docObjVarName) {
		return $this->_prefix."GetElementsByName($docObjVarName);";
	}
	
	function jsfGetElementsByIdPattern($pattern,$docObjVarName) {
		return $this->_prefix."GetElementsByIdPattern($pattern,$docObjVarName);";
	}
	
	function jsfConfimationMessage($message) {
		list($message)=$this->phpParamsToJs(func_get_args());
		return $this->_prefix."ConfimationMessage($message);";
	}
	
	function jsfToggleDisplayStyle($elementId,$mode) {
		list($elementId,$mode)=$this->phpParamsToJs(func_get_args());
		if (!$this->isJsFunctionPrinted('ToggleDisplayStyle')) return false;
		return $this->_prefix."ToggleDisplayStyle($elementId,$mode);";
	}
	
	function jsfToggleTabsDisplayStyle($activeTabId,$tabsId,$mode) {
		list($activeTabId,$tabsId,$mode)=$this->phpParamsToJs(func_get_args());

		if (!$this->isJsFunctionPrinted('ToggleTabsDisplayStyle')) return false;
		return $this->_prefix."ToggleTabsDisplayStyle($activeTabId,$tabsId,$mode);";
	}
	
	function jsfIsInArray($needle,$haystackVarName) {
		return $this->_prefix."IsInArray($needle,$haystackVarName);";
	}
	
	function jsfAddCommas($string) {
		return $this->_prefix."AddCommas($string);";
	}

	function jsfNotifyObservers($event,$params,$observers) {
		list($event,$params,$subordinate)=$this->phpParamsToJs(func_get_args());
		return $this->_prefix."NotifyObservers($event,$params,$observers);";
	}
	
	function jsfFormFieldVerification($fieldsInfo) {
		return $this->_prefix."FormFieldVerification($string);";
	}
	
	function jsfPrintf() {
//		list($text,$tabsId)=$this->phpParamsToJs(func_get_args());
//		return $this->_prefix."ConfimationMessage($message);";
	}
	
	function jsfToggleBullet($elm) {
		return $this->_prefix."ToggleBullet($elm);";
	}

	function jsfCollapseAll($id) {
		return $this->_prefix."CollapseAll($id);";
	}
	
	
	function isJsFunctionPrinted($jsFunctionName) {
		if (in_array($jsFunctionName,$this->_printedJsFunctions)) return true;
		return false;
	}
	
	
	function phpParamToJs($var) {
		//if (is_string($var)) return $var;
		return phpToJavascript($var,0,true);
	}
	
	function phpParamsToJs($vars) {
		foreach ($vars as $key=>$var) {
			$vars[$key]=$this->phpParamToJs($var);
		}
		return $vars;
	}

	/*
		$list=array('Popitup','StrReplace','GetElements')
	*/
	function printJsFunctions($list) {
		//$this->_printedJsFunctions=array_merge($this->_printedJsFunctions,$list);
		if (empty($list)) $list=array();
		?>
		<script type="text/javascript">
		<?
		if (in_array('Popitup',$list)) { ?>

			function <?=$this->_prefix?>Popitup(url,width,height,showType)
			{
				var left = (screen.width-width)/2;
				var top = (screen.height-height)/2;
				if (left < 0) left = 0;
				if (top < 0) top = 0;
				var windowAttr='height='+height+',width='+width+',left='+left+',top='+top+',status=1,scrollbars=1,menubar=1';
				if (showType=='full') windowAttr='height='+height+',width='+width+',left='+left+',top='+top+','+'status=1,scrollbars=1,menubar=1,resizable=1';
				newWindow=window.open(url,'name',windowAttr);
				if (window.focus) {newWindow.focus()}
				return false;
			}
			
		<? }
		
		if (in_array('StrReplace',$list)) { ?>
		
			function <?=$this->_prefix?>StrReplace(s, t, u) {
				//
				//  Replace a token in a string
				//    s  string to be processed
				//    t  token to be found and removed
				//   u  token to be inserted
				//  returns new String
				//
				i = s.indexOf(t);
				r = "";
				if (i == -1) return s;
				r += s.substring(0,i) + u;
				if ( i + t.length < s.length)
					r += <?=$this->_prefix?>(s.substring(i + t.length, s.length), t, u);
				return r;
			}
			
		<? }
		
		if (in_array('GetElements',$list)) { ?>
		
			function <?=$this->_prefix?>GetElements(docObj) 
			{
				if (docObj==null) {docObj=document;}
				var all = docObj.all ? docObj.all :
						docObj.getElementsByTagName('*');
				var elements = new Array();
				for (var e = 0; e < all.length; e++)
						elements[elements.length] = all[e];
				return elements;
			}
			
		<? }
		
		if (in_array('GetElementsByName',$list)) { ?>
		
			function <?=$this->_prefix?>GetElementsByName(docObj) 
			{
				if (docObj==null) {docObj=document;}
				var all = docObj.all ? docObj.all :
						docObj.getElementsByTagName('*');
				var elements = new Array();
				for (var e = 0; e < all.length; e++)
				{
					elements[all[e].name] = all[e];
				}
				return elements;
			}
			
		<? }
		
		if (in_array('GetElementsByIdPattern',$list)) { ?>
		
			function <?=$this->_prefix?>GetElementsByIdPattern(regexp,docObj) 
			{
				if (docObj==null) {docObj=document;}
				var all = docObj.all ? docObj.all :
						docObj.getElementsByTagName('*');
				var elements = new Array();
				var str;
				var elm;
				for (var e = 0; e < all.length; e++) {
					elm=all[e];
					if (elm.id) {
						var match=regexp.exec(elm.id);
						if (match!=null) {
							elements[elements.length] = elm;
						}
					}
				}
				
				return elements;
			}
			
		<? }
		
		
		if (in_array('ConfimationMessage',$list)) { ?>
		
			function <?=$this->_prefix?>ConfimationMessage(message)
			{
				if(confirm(message)) { return true; }
				else {return false;}
			}
			
		<? }
		
		if (in_array('ToggleDisplayStyle',$list)) { ?>
			/*
				mode : onlyHide,OnlyShow
				use this parameter to force function to only hide or show the element,
				"mode" is useful if you have separate buttons to show and hide the object.
			*/
			function <?=$this->_prefix?>ToggleDisplayStyle(id,mode) {
		
				var element=document.getElementById(id);
				if (element) {
					if (element.style.display=='none' && mode!='onlyHide') {
						element.style.display='';
						return true;
					}
					else if (mode!='onlyShow') {
						element.style.display='none';
						return true;
					}
				}
				return false;
			}
			
		<? }
		
		if (in_array('IsInArray',$list)) { ?>

			function <?=$this->_prefix?>IsInArray(needle,haystack)
			{
				var key
				for (key in haystack) {
					if (needle==key) {return true} 
				}
				return false;
			}

		<? }
		
		if (in_array('WindowSizeAndPosition',$list)) { ?>

			// Browser Window Size and Position
			// copyright Stephen Chapman, 3rd Jan 2005, 8th Dec 2005
			// you may copy these functions but please keep the copyright notice as well
			// part : width,height,left,top,right,bottom
			function <?=$this->_prefix?>WindowSizeAndPosition(part) {
				var pageWidth=window.innerWidth != null? window.innerWidth: document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth:document.body != null? document.body.clientWidth:null;
				var pageHeight=window.innerHeight != null? window.innerHeight: document.documentElement && document.documentElement.clientHeight ? document.documentElement.clientHeight:document.body != null? document.body.clientHeight:null;
				var posLeft=typeof window.pageXOffset != 'undefined' ? window.pageXOffset:document.documentElement && document.documentElement.scrollLeft? document.documentElement.scrollLeft:document.body.scrollLeft? document.body.scrollLeft:0;
				var posTop=typeof window.pageYOffset != 'undefined' ? window.pageYOffset:document.documentElement && document.documentElement.scrollTop? document.documentElement.scrollTop: document.body.scrollTop?document.body.scrollTop:0;
				var posRight=posLeft+pageWidth;
				var posBottom=posTop+pageHeight;
				
				switch (part) {
					case 'width' : return pageWidth; break;
					case 'height' : return pageHeight; break;
					case 'left' : return posLeft; break;
					case 'top' : return posTop; break;
					case 'right' : return posRight; break;
					case 'bottom' : return posBottom; break;
				} 
			}
			//-->

		<? }
		
		if (in_array('AddCommas',$list)) { ?>

			function <?=$this->_prefix?>AddCommas(nStr) {
				nStr += '';
				x = nStr.split('.');
				x1 = x[0];
				x2 = x.length > 1 ? '.' + x[1] : '';
				var rgx = /(\d+)(\d{3})/;
				while (rgx.test(x1)) {
					x1 = x1.replace(rgx, '$1' + ',' + '$2');
				}
				return x1 + x2;
			}
		<? }
		
		
		if (in_array('ToggleBullet',$list)) { ?>
	
			function <?=$this->_prefix?>ToggleBullet(elm, onToggle) {
				if (onToggle) {
					onToggle(elm);
				}
				
				var newDisplay = "none";
				var e = elm.nextSibling;
				while (e != null) {
					if (e.tagName == "UL" || e.tagName == "ul") {
						if (e.style.display == "none") newDisplay = "block";
						break;
					}
					e = e.nextSibling;
				}
				while (e != null) {
					if (e.tagName == "UL" || e.tagName == "ul") e.style.display = newDisplay;
					e = e.nextSibling;
				}
			}
		<? }
		
		
		
		if (in_array('CollapseAll',$list)) { ?>
	
			function <?=$this->_prefix?>CollapseAll(id) {
				if (id=='') id='root';
				var e = document.getElementById(id);
				
				var lists = e.getElementsByTagName('UL');
				for (var j = 0; j < lists.length; j++) 
					lists[j].style.display = "none";
				lists = e.getElementsByTagName('ul');
				for (var j = 0; j < lists.length; j++) 
					lists[j].style.display = "none";
				
				e.style.display = "block";
			}
			
		<? }


		if (in_array('ToggleTabsDisplayStyle',$list)) { ?>
			/**
			* @desc 
			*	displayNextOne Mode: hide visible element and show next element in tabsId array
			*
			* @param activeTabId string
			* @param tabsId array
			* @param mode string //onlyHide, onlyShow, displayNextOne 
			*/
			function <?=$this->_prefix?>ToggleTabsDisplayStyle(activeTabId,tabsId,mode) {
				var tabId;
				var elm;
				var key;
				var visibleElmFound=null;
				
				for (key in tabsId) {
					tabId=tabsId[key];
					elm=document.getElementById(tabId);
					if (elm) {
						if (mode=='displayNextOne') {
							if (visibleElmFound) {
								visibleElmFound=false;
								elm.style.display='';
							} else {
								if (elm.style.display!='none' && visibleElmFound==null)
									visibleElmFound=true;
								elm.style.display='none';
							}
						} else {
							if (tabId==activeTabId)
								elm.style.display='';
							else
								elm.style.display='none';
						}
					}
				}
			}
			

		<? }
		
		if (in_array('notifyObservers',$list)) { ?>
			
			/**
			* @desc
			*/
			function <?=$this->_prefix?>NotifyObservers(myEvent,params,observers)
			{
				var observer;
				var myFunc;
				var key;
				var myKey;
				var argumans='';
				var comma;
				
				observers=observers[myEvent];
				for (key in observers) {
					observer=observers[key];
					for (myKey in params) {
						argumans+=comma+'params['.myKey.']';
						comma=',';
					}
					eval("observer("+argumans+")");
				}
				return false;
			}

		<? }
		
		if (in_array('Printf',$list)) { ?>
		
			/* Function printf(format_string,arguments...)
			 * Javascript emulation of the C printf function (modifiers and argument types 
			 *    "p" and "n" are not supported due to language restrictions)
			 *
			 * Copyright 2003 K&L Productions. All rights reserved
			 * http://www.klproductions.com 
			 *
			 * Terms of use: This function can be used free of charge IF this header is not
			 *               modified and remains with the function code.
			 * 
			 * Legal: Use this code at your own risk. K&L Productions assumes NO resposibility
			 *        for anything.
			 ********************************************************************************/
			function <?=$this->_prefix?>Printf(fstring)
			  { var pad = function(str,ch,len)
				  { var ps='';
					for(var i=0; i<Math.abs(len); i++) ps+=ch;
					return len>0?str+ps:ps+str;
				  }
				var processFlags = function(flags,width,rs,arg)
				  { var pn = function(flags,arg,rs)
					  { if(arg>=0)
						  { if(flags.indexOf(' ')>=0) rs = ' ' + rs;
							else if(flags.indexOf('+')>=0) rs = '+' + rs;
						  }
						else
							rs = '-' + rs;
						return rs;
					  }
					var iWidth = parseInt(width,10);
					if(width.charAt(0) == '0')
					  { var ec=0;
						if(flags.indexOf(' ')>=0 || flags.indexOf('+')>=0) ec++;
						if(rs.length<(iWidth-ec)) rs = pad(rs,'0',rs.length-(iWidth-ec));
						return pn(flags,arg,rs);
					  }
					rs = pn(flags,arg,rs);
					if(rs.length<iWidth)
					  { if(flags.indexOf('-')<0) rs = pad(rs,' ',rs.length-iWidth);
						else rs = pad(rs,' ',iWidth - rs.length);
					  }    
					return rs;
				  }
				var converters = new Array();
				converters['c'] = function(flags,width,precision,arg)
				  { if(typeof(arg) == 'number') return String.fromCharCode(arg);
					if(typeof(arg) == 'string') return arg.charAt(0);
					return '';
				  }
				converters['d'] = function(flags,width,precision,arg)
				  { return converters['i'](flags,width,precision,arg); 
				  }
				converters['u'] = function(flags,width,precision,arg)
				  { return converters['i'](flags,width,precision,Math.abs(arg)); 
				  }
				converters['i'] =  function(flags,width,precision,arg)
				  { var iPrecision=parseInt(precision);
					var rs = ((Math.abs(arg)).toString().split('.'))[0];
					if(rs.length<iPrecision) rs=pad(rs,' ',iPrecision - rs.length);
					return processFlags(flags,width,rs,arg); 
				  }
				converters['E'] = function(flags,width,precision,arg) 
				  { return (converters['e'](flags,width,precision,arg)).toUpperCase();
				  }
				converters['e'] =  function(flags,width,precision,arg)
				  { iPrecision = parseInt(precision);
					if(isNaN(iPrecision)) iPrecision = 6;
					rs = (Math.abs(arg)).toExponential(iPrecision);
					if(rs.indexOf('.')<0 && flags.indexOf('#')>=0) rs = rs.replace(/^(.*)(e.*)$/,'$1.$2');
					return processFlags(flags,width,rs,arg);        
				  }
				converters['f'] = function(flags,width,precision,arg)
				  { iPrecision = parseInt(precision);
					if(isNaN(iPrecision)) iPrecision = 6;
					rs = (Math.abs(arg)).toFixed(iPrecision);
					if(rs.indexOf('.')<0 && flags.indexOf('#')>=0) rs = rs + '.';
					return processFlags(flags,width,rs,arg);
				  }
				converters['G'] = function(flags,width,precision,arg)
				  { return (converters['g'](flags,width,precision,arg)).toUpperCase();
				  }
				converters['g'] = function(flags,width,precision,arg)
				  { iPrecision = parseInt(precision);
					absArg = Math.abs(arg);
					rse = absArg.toExponential();
					rsf = absArg.toFixed(6);
					if(!isNaN(iPrecision))
					  { rsep = absArg.toExponential(iPrecision);
						rse = rsep.length < rse.length ? rsep : rse;
						rsfp = absArg.toFixed(iPrecision);
						rsf = rsfp.length < rsf.length ? rsfp : rsf;
					  }
					if(rse.indexOf('.')<0 && flags.indexOf('#')>=0) rse = rse.replace(/^(.*)(e.*)$/,'$1.$2');
					if(rsf.indexOf('.')<0 && flags.indexOf('#')>=0) rsf = rsf + '.';
					rs = rse.length<rsf.length ? rse : rsf;
					return processFlags(flags,width,rs,arg);        
				  }  
				converters['o'] = function(flags,width,precision,arg)
				  { var iPrecision=parseInt(precision);
					var rs = Math.round(Math.abs(arg)).toString(8);
					if(rs.length<iPrecision) rs=pad(rs,' ',iPrecision - rs.length);
					if(flags.indexOf('#')>=0) rs='0'+rs;
					return processFlags(flags,width,rs,arg); 
				  }
				converters['X'] = function(flags,width,precision,arg)
				  { return (converters['x'](flags,width,precision,arg)).toUpperCase();
				  }
				converters['x'] = function(flags,width,precision,arg)
				  { var iPrecision=parseInt(precision);
					arg = Math.abs(arg);
					var rs = Math.round(arg).toString(16);
					if(rs.length<iPrecision) rs=pad(rs,' ',iPrecision - rs.length);
					if(flags.indexOf('#')>=0) rs='0x'+rs;
					return processFlags(flags,width,rs,arg); 
				  }
				converters['s'] = function(flags,width,precision,arg)
				  { var iPrecision=parseInt(precision);
					var rs = arg;
					if(rs.length > iPrecision) rs = rs.substring(0,iPrecision);
					return processFlags(flags,width,rs,0);
				  }
				farr = fstring.split('%');
				retstr = farr[0];
				fpRE = /^([-+ #]*)(\d*)\.?(\d*)([cdieEfFgGosuxX])(.*)$/;
				for(var i=1; i<farr.length; i++)
				  { fps=fpRE.exec(farr[i]);
					if(!fps) continue;
					if(arguments[i]!=null) retstr+=converters[fps[4]](fps[1],fps[2],fps[3],arguments[i]);
					retstr += fps[5];
				  }
				return retstr;
			  }
			/* Function printf() END */
		<? }?>
		</script>
	<? }
}
?>