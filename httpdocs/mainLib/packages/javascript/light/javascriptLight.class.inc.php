<?php
/**
* this class makes using javascript functions easy
* <code>
* $js=new cJavscriptFunctions();
* $js->printJsFunctions(array('confimationMessage'));
*	
* <a href="#" onclick="return <?=$js->jsfConfimationMessage('Are you sure?')?>">delete</a>
* </code>
* 
* @version $Id: javascriptLight.class.inc.php 431 2009-10-04 11:26:19Z salek $
*
*/

class cmfcJavascriptLightCode {
	var $code='';
	function cmfcJavascriptLightCode($code) {
		$this->code=$code;
	}
}

class cmfcJavascriptLight extends cmfcBaseClass{
	var $_prefix='cmf';
	var $_printedJsFunctions;
   	var $_messagesValue=array(
		CMF_UserSystem_Error=>'Unknown error',
	);
	
	var $_jsFunctionsInfo=array(
		'StrReplace'=>array(
			'compatibleBrowsers'=>array('Firefox','IE','Opera')
		)
	);
	
	/**
	* @desc import the patameter to javascript as a javascript code
	*/
	function asJsCode($str) {
		return new cmfcJavascriptLightCode($str);
	}
    
	function isJsCode($value) {
		if (strtolower(get_class($value))==strtolower('cmfcJavascriptLightCode')) {
			return true;
		} else {
			return false;
		}
	}
	
	function __construct($configs) {
		$this->setConfigs($configs);
	}
	
	function setConfigs() {
		parent::setConfigs($configs);
		if (isset($configs['prefix'])) $this->setPrefix($configs['prefix']);
		
		$this->_configs=$config;
	}
	
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
	
	function jsfConfimationMessage($message) {
		list($message)=$this->phpParamsToJs(func_get_args());
		return $this->_prefix."ConfimationMessage($message);";
	}
	
	function jsfToggleDisplayStyle($elementId,$mode) {
		list($elementId,$mode)=$this->phpParamsToJs(func_get_args());
		//if (!$this->isJsFunctionPrinted('ToggleDisplayStyle')) return false;
		return $this->_prefix."ToggleDisplayStyle($elementId,$mode);";
	}
		
	function jsfToggleTabsDisplayStyle($activeTabId,$tabsId,$onSelectTab=null) {
		$_onSelectTab=$onSelectTab;
		list($activeTabId,$tabsId)=$this->phpParamsToJs(func_get_args());
		$onSelectTab=$_onSelectTab;

		//if (!$this->isJsFunctionPrinted('ToggleTabsDisplayStyle')) return false;
		return $this->_prefix."ToggleTabsDisplayStyle($activeTabId,$tabsId,$onSelectTab);";
	}
	
	function jsfAddNewBox($boxContainerId,$tempBoxId,$baseName) {
		list($boxContainerId,$tempBoxId,$baseName)=$this->phpParamsToJs(func_get_args());

		//if (!$this->isJsFunctionPrinted('ToggleTabsDisplayStyle')) return false;
		return $this->_prefix."AddNewBox($boxContainerId,$tempBoxId,$baseName);";
	}
	
	function jsfIsInArray($needle,$haystackVarName) {
		return $this->_prefix."IsInArray($needle,$haystackVarName);";
	}
	
	function jsfAddCommas($string) {
		return $this->_prefix."AddCommas($string);";
	}
	
	function jsfFormFieldVerification($fieldsInfo) {
		return $this->_prefix."FormFieldVerification($string);";
	}
	
	function jsfPrintf() {
		//list($text,$tabsId)=$this->phpParamsToJs(func_get_args());
		//return $this->_prefix."ConfimationMessage($message);";
	}
	
	function jsfHtmlEntityDecode($string) {
		list($string)=$this->phpParamsToJs(func_get_args());
		//if (!$this->isJsFunctionPrinted('HtmlEntityDecode')) return false;
		return $this->_prefix."HtmlEntityDecode($string);";
	}
	
	
	function jsfCreateHtmlNode($tag,$html) {
		list($tag,$html)=$this->phpParamsToJs(func_get_args());
		//if (!$this->isJsFunctionPrinted('CreateHtmlNode')) return false;
		return $this->_prefix."CreateHtmlNode($tag,$html);";
	}
	
	
	function jsfClearSelectObject($obj) {
		list($obj)=$this->phpParamsToJs(func_get_args());
		//if (!$this->isJsFunctionPrinted('ClearSelectObject')) return false;
		return $this->_prefix."ClearSelectObject($obj);";
	}
	
	
	function jsfClone($obj) {
		list($obj)=$this->phpParamsToJs(func_get_args());
		//if (!$this->isJsFunctionPrinted('Clone')) return false;
		return $this->_prefix."Clone($obj);";
	}
	
	
	function isJsFunctionPrinted($jsFunctionName) {
		if (in_array($jsFunctionName,$this->_printedJsFunctions)) return true;
		return false;
	}
	
	
	function phpParamToJs($var) {
		if ($this->isJsCode($var)) {
			return $var->code;
		} else {
			return cmfcHtml::phpToJavascript($var,0,true);
		}
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
		foreach ($list as $k=>$v) {
			$list[$k]=ucfirst($v);
		}
		
		$this->_printedJsFunctions=$list;
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		<?php
		if (in_array('Popitup',$list)) { ?>

			function <?php echo $this->_prefix?>Popitup(url,width,height,showType)
			{
				var left = (screen.width-width)/2;
				var top = (screen.height-height)/2;
				if (left < 0) left = 0;
				if (top < 0) top = 0;
				var windowAttr='height='+height+',width='+width+',left='+left+',top='+top+',status=1,scrollbars=1,menubar=1';
				if (showType=='full') windowAttr='height='+height+',width='+width+',left='+left+',top='+top+','+'status=1,scrollbars=1,menubar=1,resizable=1';
				if (showType=='fixSimple') windowAttr='height='+height+',width='+width+',left='+left+',top='+top+','+'status=0,scrollbars=0,menubar=0,resizable=0';
				if (showType=='scrollResizable') windowAttr='height='+height+',width='+width+',left='+left+',top='+top+','+'status=0,scrollbars=1,menubar=0,resizable=1';
				if (showType=='fixWithScrollbars') windowAttr='height='+height+',width='+width+',left='+left+',top='+top+','+'status=0,scrollbars=1,menubar=0,resizable=0';

				newWindow=window.open(url,'name',windowAttr);
				if (window.focus) {newWindow.focus()}
				return false;
			}
			
		<?php }
		
		if (in_array('StrReplace',$list)) { ?>
		
			function <?php echo $this->_prefix?>StrReplace(s, t, u) {
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
					r += <?php echo $this->_prefix?>(s.substring(i + t.length, s.length), t, u);
				return r;
			}
			
		<?php }
		
		if (in_array('GetElements',$list)) { ?>
		
			function <?php echo $this->_prefix?>GetElements(docObj) 
			{
				if (docObj==null) {docObj=document;}
				var all = docObj.all ? docObj.all :
						docObj.getElementsByTagName('*');
				var elements = new Array();
				for (var e = 0; e < all.length; e++)
						elements[elements.length] = all[e];
				return elements;
			}
			
		<?php }
		

		if (in_array('HtmlEntityDecode',$list)) { ?>
		
			/* This script and many more are available free online at
			The JavaScript Source!! http://javascript.internet.com
			Created by: Ultimater | http://webdeveloper.com/forum/member.php?u=30185 */
			function <?php echo $this->_prefix?>HtmlEntityDecode(str) {
			  var ta=document.createElement("textarea");
			  ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
			  return ta.value;
			}
			
		<?php }
		
		
		
		if (in_array('CreateHtmlNode',$list)) { ?>
		
			function <?php echo $this->_prefix?>CreateHtmlNode(tag, text) {
				var n = document.createElement(tag);
				if (text) n.innerHTML = text;
				return n;
			}
			
		<?php }
		
		
		if (in_array('Clone',$list)) { ?>
		
			/*
			Script by RoBorg
			RoBorg@geniusbug.com
			http://javascript.geniusbug.com | http://www.roborg.co.uk
			Please do not remove or edit this message
			Please link to this website if you use this script!
			*/
			function <?php echo $this->_prefix?>Clone(myObj)
			{
				if(typeof(myObj) != 'object') return myObj;
				if(myObj == null) return myObj;
			
				var myNewObj = new Object();
			
				for(var i in myObj)
					myNewObj[i] = clone(myObj[i]);
			
				return myNewObj;
			}
			
		<?php }
		
		
		if (in_array('ClearSelectObject',$list)) { ?>
		
			function <?php echo $this->_prefix?>ClearSelectObject(obj) {
				/*
				obj.innerHTML='';
				return true;
				optgroups = obj.getElementsByTagName('optgroup');
				for (var j = 0; j < optgroups.length; j++) {
					obj.removeChild(optgroups[j]);
				}
				options = obj.getElementsByTagName('options');
				for (var j = 0; j < options.length; j++) {
					obj.removeChild(options[j]);
				}*/
				//obj.options.length=0;
				//obj.selectedIndex=null;
				
				var childs = obj.childNodes;
				for(i = childs.length - 1 ; i >= 0 ; i--) {
					obj.removeChild(childs[i],true);
				}
			}
			
		<?php }
				
		
		if (in_array('GetElementsByName',$list)) { ?>
		
			function <?php echo $this->_prefix?>GetElementsByName(docObj) 
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
			
		<?php }
		
		if (in_array('ConfimationMessage',$list)) { ?>
		
			function <?php echo $this->_prefix?>ConfimationMessage(message)
			{
				if(confirm(message)) { return true; }
				else {return false;}
			}
			
		<?php }
		
		if (in_array('ToggleDisplayStyle',$list)) { ?>
			/*
				mode : onlyHide,OnlyShow
				use this parameter to force function to only hide or show the element,
				"mode" is useful if you have separate buttons to show and hide the object.
			*/
			function <?php echo $this->_prefix?>ToggleDisplayStyle(id,mode) {
		
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
			
		<?php }
		
		if (in_array('IsInArray',$list)) { ?>

			function <?php echo $this->_prefix?>IsInArray(needle,haystack)
			{
				var key
				for (key in haystack) {
					if (needle==key) {return true} 
				}
				return false;
			}

		<?php }
		
		if (in_array('WindowSizeAndPosition',$list)) { ?>

			// Browser Window Size and Position
			// copyright Stephen Chapman, 3rd Jan 2005, 8th Dec 2005
			// you may copy these functions but please keep the copyright notice as well
			// part : width,height,left,top,right,bottom
			function <?php echo $this->_prefix?>WindowSizeAndPosition(part) {
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

		<?php }
		
		if (in_array('AddCommas',$list)) { ?>

			function <?php echo $this->_prefix?>AddCommas(nStr) {
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
		<?php }

		if (in_array('ToggleTabsDisplayStyle',$list)) { ?>
			/*
				mode : onlyHide,OnlyShow
				use this parameter to force function to only hide or show the element,
				"mode" is useful if you have separate buttons to show and hide the object.
			*/
			function <?php echo $this->_prefix?>ToggleTabsDisplayStyle(activeTabId,tabsId,onSelectTab) {
				var tabId;
				var elm;
				for (key in tabsId) {
					tabId=tabsId[key];
					elm=document.getElementById(tabId);
					if (elm) {
						if (tabId==activeTabId) {
							elm.style.display='';
							if (onSelectTab) {
								onSelectTab(elm.id)
							}
						} else
							elm.style.display='none';
					}
				}
			}
			

		<?php }
		
		
		if (in_array('AddNewBox',$list)) { ?>
			/**
			* for addable html contents
			*/
			function <?php echo $this->_prefix?>AddNewBox(boxContainerId,tempBoxId,baseName) {
				var itemNumber=0;
				var elementName;
				var element;
				var templateItemBox;
				
				do {
					//alert(itemNumber);
					itemNumber++;
					elementName=baseName+'['+itemNumber+'][columns][id]';
					element=document.myForm.elements[elementName];
					
				} while (element);
			
				//if (document.all) itemNumber=''+itemNumber+'';
				
				var itemsBorad=document.getElementById(boxContainerId);
				var templateItemBox=document.getElementById(tempBoxId).innerHTML;

				templateItemBox=<?php echo $this->jsfHtmlEntityDecode($this->asJsCode('templateItemBox'))?>;

				//alert(templateItemBox);
				var myregexp = new RegExp("%{itemNumber}%", "gmi");
				templateItemBox=templateItemBox.replace(myregexp,itemNumber);
				myregexp = new RegExp("%[^ %{}]*%", "gmi");
				if (document.all) {
					templateItemBox=templateItemBox.replace(myregexp,'');
				} else {
					templateItemBox=templateItemBox.replace(myregexp,'');
				}

				//itemsBorad.innerHTML=itemsBorad.innerHTML+templateItemBox;
				var htmlNode=<?php echo $this->jsfCreateHtmlNode('span',$this->asJsCode('templateItemBox'))?>;
				itemsBorad.appendChild( htmlNode );
			}
		<?php }
		
		

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
			function <?php echo $this->_prefix?>Printf(fstring)
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
		<?php }?>
		/* ]]> */
		</script>
	<?php }
	

}