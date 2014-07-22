<?php
/**
 * @version $Id: common.inc.php 528 2010-02-07 13:20:19Z salek $
 * @package cmf
 * @subpackage beta
 * @author Sina Salek <sina.salek.ws>
 */
 
/**
* 
*/
class cmfcString {
	/**
	* cut the text in appropiriate positon according to its length.
	* it will not cut the words by default
	* limit text length - "test this file" to "test this..."
	* @previousNames ellipse_text,cmfEllipseText
	*/
	function briefText($string,$length,$dot='...',$wordSafe=true) {
		if ($wordSafe) {
			if (preg_match_all('/([^\\s]*[\\s ]*)/', $string, $parts)) {
				$parts=$parts[0];
				$total=count($parts);
				$result='';
				$rLength=0;
				foreach ($parts as $word) {
					$wordLength=cmfcUtf8::strlen($word);
					if ($rLength+$wordLength<=$length) {
						$rLength+=$wordLength;
						$result.=$word;
					} else break;
				}
				if ($rLength<cmfcUtf8::strlen($string))
					$result.=$dot;
				
				return $result;
			} else {
				return false;
			}
		} else {
	        $matches=array();
	        $string=cmfcHtml::utf8ToHtmlEntity($string);
	        preg_match_all("(&#[0-9]+;)",$string,$matches);
	        $string='';
	        for ($i=0;$i<$length;$i++) { $string.=$matches[0][$i]; }
	        if (count($matches[0])>$length) {$string.=$dot;}
	        return $string;
		}
	}
	
	
	/**
	* 
	*/	
	function removeBom($str=""){
		if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
			$str=substr($str, 3);
		}
		return $str;
	}
	

	function convertToUnixFormat($str="") {
		$str=str_replace(array("\n\r","\r\n"),array("\r","\r"),$str);
		return $str;
	}
	
	
	/**
	* Will convert KEH(with Hamzeh) & YEH(with under dot) to normal KEH & YEH
	* @previousNames convert_to_standard_persian_characters
	*/
	function convertToStandardPersianCharacters($text) {
		$arYeh=chr(hexdec('D9')).chr(hexdec('8A'));//ي
		$arKeh=chr(hexdec('D9')).chr(hexdec('83'));//ك
		$faYeh=chr(hexdec('DB')).chr(hexdec('8C'));//ی
		$faKeh=chr(hexdec('DA')).chr(hexdec('A9'));//ک
		return str_replace(array($arYeh,$arKeh),array($faYeh,$faKeh),$text);
	}

	
	
	/**
	 * converts string from unserline(underscore) naming to camelCase naming
	 *         - there is a option which make it possible to have camelCase with first letter upper case : ThisIsMe
	 * @example 'this_is_me' => 'thisIsMe'
	 * @version Beta
	 * @author Sina Salek : sina@salek.ws
	 * @todo problem in converting! when there is a duplicate occurance of _f (underline first letter) it's
	 *                possible to function convert wrongfuly (all of the similar occurances). so it's nessccery to findout
	 *                 a way to convert each occurance only once time. like "is_this_me" => "IsThIsMe"
	 * @param string $text
	 * @param boolean $firstLetterUpperCase
	 * @return string
	 */
	function convertNamingFromUnderlineToCamelCase($text,$firstLetterLowerCase=true) {
			preg_match_all('/(^[a-zA-Z])|(_[a-zA-Z])/', $text, $matches, PREG_PATTERN_ORDER);
	
			for ($i = 0; $i < count($matches[0]); $i++) {
					$original=$matches[0][$i];
					$originals[]=$original;
					if ($i==0 and $firstLetterLowerCase)
							$replacement=str_replace('_','',$matches[0][$i]);
					else
							$replacement=strtoupper(str_replace('_','',$matches[0][$i]));
					$replacements[]=$replacement;
			}
	
			return str_replace($originals,$replacements,$text);
	}
	
	/**
	* Str Pos Nth (Position of nth occurance of a string)
	* A handy function to get the position of nth occurance of a substring in a string, 
	* with an optional param to make it case insenstive. I am calling it glStrNthOccurPos, suggestions welcome.
	* Third optional parameter gets the value of n, e.g puting in 2 will return position of second occurance
	* of needle in haystack: Valid inputs (1 = default) 2,3,4.....
	* Fourth optional parameter can be used to specify the function 
	* as case insenstive: Valid inputs (0 = case senstive = default) 1 = case insenstive.
	* @previousNames strposnth
	*/
	
	function strNthOccurPos($haystack, $needle, $nth=1, $insenstive=0)
	{
	   //if its case insenstive, convert strings into lower case
	   if ($insenstive) {
		   $haystack=strtolower($haystack);
		   $needle=strtolower($needle);
	   }
	   //count number of occurances
	   $count=substr_count($haystack,$needle);
	
	   //first check if the needle exists in the haystack, return false if it does not
	   //also check if asked nth is within the count, return false if it doesnt
	   if ($count<1 || $nth > $count) return false;
	
	
	   //run a loop to nth number of accurance
	   //start $pos from -1, cause we are adding 1 into it while searchig
	   //so the very first iteration will be 0
	   for($i=0,$pos=0,$len=0;$i<$nth;$i++)
	   {
		   //get the position of needle in haystack
		   //provide starting point 0 for first time ($pos=0, $len=0)
		   //provide starting point as position + length of needle for next time
		   $pos=strpos($haystack,$needle,$pos+$len);
	
		   //check the length of needle to specify in strpos
		   //do this only first time
		   if ($i==0) $len=strlen($needle);
		 }
	
	   //return the number
	   return $pos;
	}
	
	function convertNumbersToEnglish($srting,$mode='plainText') {
		if($mode=='plainText') {
			$num0=chr(hexdec('DB')).chr(hexdec('B0'));
			$num1=chr(hexdec('DB')).chr(hexdec('B1'));
			$num2=chr(hexdec('DB')).chr(hexdec('B2'));
			$num3=chr(hexdec('DB')).chr(hexdec('B3'));
			$num4=chr(hexdec('DB')).chr(hexdec('B4'));
			$num5=chr(hexdec('DB')).chr(hexdec('B5'));
			$num6=chr(hexdec('DB')).chr(hexdec('B6'));
			$num7=chr(hexdec('DB')).chr(hexdec('B7'));
			$num8=chr(hexdec('DB')).chr(hexdec('B8'));
			$num9=chr(hexdec('DB')).chr(hexdec('B9'));
		}
		
		$stringtemp=cmfcString::replaceVariables(array(
			$num0=>0,
			$num1=>1,
			$num2=>2,
			$num3=>3,
			$num4=>4,
			$num5=>5,
			$num6=>6,
			$num7=>7,
			$num8=>8,
			$num9=>9
		),$srting);
		return $stringtemp;
	}
	
	/**
	* here convert to  number in persian
	* @previousNames Convertnumber2farsi
	*/
	function convertNumbersToFarsi($srting,$mode='html')
	{
		if ($mode=='html') {
			$num0="&#1776;";
			$num1="&#1777;";
			$num2="&#1778;";
			$num3="&#1779;";
			$num4="&#1780;";
			$num5="&#1781;";
			$num6="&#1782;";
			$num7="&#1783;";
			$num8="&#1784;";
			$num9="&#1785;";
		} elseif($mode=='plainText') {
			$num0=chr(hexdec('DB')).chr(hexdec('B0'));
			$num1=chr(hexdec('DB')).chr(hexdec('B1'));
			$num2=chr(hexdec('DB')).chr(hexdec('B2'));
			$num3=chr(hexdec('DB')).chr(hexdec('B3'));
			$num4=chr(hexdec('DB')).chr(hexdec('B4'));
			$num5=chr(hexdec('DB')).chr(hexdec('B5'));
			$num6=chr(hexdec('DB')).chr(hexdec('B6'));
			$num7=chr(hexdec('DB')).chr(hexdec('B7'));
			$num8=chr(hexdec('DB')).chr(hexdec('B8'));
			$num9=chr(hexdec('DB')).chr(hexdec('B9'));
		}

		$stringtemp="";
		$len=strlen($srting);
		for($sub=0;$sub<$len;$sub++) {
			if(substr($srting,$sub,1)=="0")$stringtemp.=$num0;
			elseif(substr($srting,$sub,1)=="1")$stringtemp.=$num1;
			elseif(substr($srting,$sub,1)=="2")$stringtemp.=$num2;
			elseif(substr($srting,$sub,1)=="3")$stringtemp.=$num3;
			elseif(substr($srting,$sub,1)=="4")$stringtemp.=$num4;
			elseif(substr($srting,$sub,1)=="5")$stringtemp.=$num5;
			elseif(substr($srting,$sub,1)=="6")$stringtemp.=$num6;
			elseif(substr($srting,$sub,1)=="7")$stringtemp.=$num7;
			elseif(substr($srting,$sub,1)=="8")$stringtemp.=$num8;
			elseif(substr($srting,$sub,1)=="9")$stringtemp.=$num9;
			else $stringtemp.=substr($srting,$sub,1);
		}
		return $stringtemp;
	}///end conver to number in persian
	
	/**
	* apply various actions on sepecific chars of a string
	* toUpper,toLower,...
	* @param $from integer //start from 1
	* @param $to integer //end to text length + 1
	* @param $string string
	* @action $string string //toLower,toUpper
	* @return string
	*/
	function manipulateChars($string,$action,$from,$to) {
		$stringtemp="";
		$from=$from-1;
		$to=$to-1;
		$len=strlen($string);
		for($sub=0;$sub<$len;$sub++) {
			if ($sub>=$from and $sub<=$to) {
				if ($action=='toUpper') $stringtemp.=strtoupper(substr($string,$sub,1));
				if ($action=='toLower') $stringtemp.=strtolower(substr($string,$sub,1));
			} else $stringtemp.=substr($string,$sub,1);
		}
		
		return $stringtemp;
	}
	
	
	/**
	* here convert from number in persian to number in english
	* @previousNames Convertnumber2farsi
	*/
	function convertFarsiNumbersToEnglish($srting)
	{
		$replacements=array(
			array('fa'=>"&#1776;",'en'=>0),
			array('fa'=>"&#1777;",'en'=>1),
			array('fa'=>"&#1778;",'en'=>2),
			array('fa'=>"&#1779;",'en'=>3),
			array('fa'=>"&#1780;",'en'=>4),
			array('fa'=>"&#1781;",'en'=>5),
			array('fa'=>"&#1782;",'en'=>6),
			array('fa'=>"&#1783;",'en'=>7),
			array('fa'=>"&#1784;",'en'=>8),
			array('fa'=>"&#1785;",'en'=>9),
			
		);
		
		foreach ($replacements as $numInfo) {
			$srting=str_replace(html_entity_decode($numInfo['fa'],ENT_COMPAT,'UTF-8'),$numInfo['en'],$srting);
		}
		
		return $srting;
	}
	
	
	/*
	$replacements['00username00']='jafar gholi';
	*/
	//last name : replace_variables
	function replaceVariables($replacements,$text) {
		foreach ($replacements as $needle=>$replacement) {
			$text=str_replace($needle,$replacement,$text);
		}
		return $text;
	}
	
	
	
	
    /**
    * 
    * @previousNames : fa_decode
    * @example
    * <code>
    * 	$test2[0]="ظ¾ظٹظ…ط§ظ†" ;
	* 	$test2[1]="ali";
	* 	$test2[2]="xxxi";
	* 	$test2[3]="ط¢ط±ط²ظˆ";
	* 	$test2[4]="ع†ط±ع†ظٹظ„";
	* 	$test2[5]="عکط§ظ„ظ‡";
	* 	$test2[6]="ع¯ط±ظ…ط§ظٹط´";
	* 	$test2[7]="ظ…ط§ظ†ط¯ظ†ظٹ";
	* 	$test2[8]="ط±ط¶ط§";
	* 	$test2[9]="ظ¾عکظˆظ‡ط´";
	* 	$test2[10]="ظٹط§";
	*  	
	* 	for($i=0;$i<=10;$i++) $test2[$i] =cmfFaEncode($test2[$i] );
	*	sort($test2);
	* 	for($i=0;$i<=10;$i++) { 
	* 		echo cmfFaDecode($test2[$i]) .\"<br>\" ;
	*	}
	* </code>
    */
	function decodeFarsi($str,$second_char=32)
	{
	    $chnum=$second_char;
	    $_to_farsi=array(
	        chr($chnum).chr(48) => chr(216).chr(162),
	        chr($chnum).chr(49) => chr(216).chr(167),
	        chr($chnum).chr(50) => chr(216).chr(168),
	        chr($chnum).chr(51) => chr(217).chr(190),
	        chr($chnum).chr(52) => chr(216).chr(170),
	        chr($chnum).chr(53) => chr(216).chr(171),
	        chr($chnum).chr(54) => chr(216).chr(172),
	        chr($chnum).chr(55) => chr(218).chr(134),
	        chr($chnum).chr(56) => chr(216).chr(173),
	        chr($chnum).chr(57) => chr(216).chr(174),
	        chr($chnum).chr(65) => chr(216).chr(175),
	        chr($chnum).chr(66) => chr(216).chr(176),
	        chr($chnum).chr(67) => chr(216).chr(177),
	        chr($chnum).chr(68) => chr(216).chr(178),
	        chr($chnum).chr(69) => chr(218).chr(152),
	        chr($chnum).chr(70) => chr(216).chr(179),
	        chr($chnum).chr(71) => chr(216).chr(180),
	        chr($chnum).chr(72) => chr(216).chr(181),
	        chr($chnum).chr(73) => chr(216).chr(182),
	        chr($chnum).chr(74) => chr(216).chr(183),
	        chr($chnum).chr(75) => chr(216).chr(184),
	        chr($chnum).chr(76) => chr(216).chr(185),
	        chr($chnum).chr(77) => chr(216).chr(186),
	        chr($chnum).chr(78) => chr(217).chr(129),
	        chr($chnum).chr(79) => chr(217).chr(130),
	        chr($chnum).chr(80) => chr(218).chr(169),
	        chr($chnum).chr(81) => chr(218).chr(175),
	        chr($chnum).chr(82) => chr(217).chr(132),
	        chr($chnum).chr(83) => chr(217).chr(133),
	        chr($chnum).chr(84) => chr(217).chr(134),
	        chr($chnum).chr(85) => chr(217).chr(136),
	        chr($chnum).chr(86) => chr(217).chr(135),
	        chr($chnum).chr(87) => chr(219).chr(140),
	        chr($chnum).chr(88) => chr(217).chr(138)
	    );
	    return strtr($str,$_to_farsi);
	}


	/**
	* solving farsi sorting problem (Created By Arash Mikaili with few changes by sina salek)
	* the separator character in original function is 131, i had to change 
	* it to a standard and visible character becuase my queries didn't work
	* with no reason!!
	* 
	* @previousNames fa_encode 
	*/
	function encodeFarsi($str,$second_char=32)
	{
	    $chnum=$second_char;
	    $_to_safe=array(
	        chr(216).chr(162) => chr($chnum).chr(48),
	        chr(216).chr(167) => chr($chnum).chr(49),
	        chr(216).chr(168) => chr($chnum).chr(50),
	        chr(217).chr(190) => chr($chnum).chr(51),
	        chr(216).chr(170) => chr($chnum).chr(52),

	        chr(216).chr(171) => chr($chnum).chr(53),
	        chr(216).chr(172) => chr($chnum).chr(54),
	        chr(218).chr(134) => chr($chnum).chr(55),
	        chr(216).chr(173) => chr($chnum).chr(56),
	        chr(216).chr(174) => chr($chnum).chr(57),
	        chr(216).chr(175) => chr($chnum).chr(65),
	        chr(216).chr(176) => chr($chnum).chr(66),
	        chr(216).chr(177) => chr($chnum).chr(67),
	        chr(216).chr(178) => chr($chnum).chr(68),
	        chr(218).chr(152) => chr($chnum).chr(69),
	        chr(216).chr(179) => chr($chnum).chr(70),
	        chr(216).chr(180) => chr($chnum).chr(71),
	        chr(216).chr(181) => chr($chnum).chr(72),
	        chr(216).chr(182) => chr($chnum).chr(73),
	        chr(216).chr(183) => chr($chnum).chr(74),
	        chr(216).chr(184) => chr($chnum).chr(75),
	        chr(216).chr(185) => chr($chnum).chr(76),
	        chr(216).chr(186) => chr($chnum).chr(77),
	        chr(217).chr(129) => chr($chnum).chr(78),
	        chr(217).chr(130) => chr($chnum).chr(79),
	        chr(218).chr(169) => chr($chnum).chr(80),
	        chr(218).chr(175) => chr($chnum).chr(81),
	        chr(217).chr(132) => chr($chnum).chr(82),
	        chr(217).chr(133) => chr($chnum).chr(83),
	        chr(217).chr(134) => chr($chnum).chr(84),
	        chr(217).chr(136) => chr($chnum).chr(85),
	        chr(217).chr(135) => chr($chnum).chr(86),
	        chr(219).chr(140) => chr($chnum).chr(87),
	        chr(217).chr(138) => chr($chnum).chr(88)
	    );
	    return strtr($str,$_to_safe);
	}
	
	
	/**
	* @desc 
	*/
	function isFarsi($text) {
		if (preg_match('/^[ \ط§-\غŒظ…ظ„ظ†]*$/sim', $text)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Description: string str_makerand(int $minlength, int $maxlength, bool $useupper, bool $usespecial, bool $usenumbers)
	* returns a randomly generated string of length between $minlength and $maxlength inclusively.
    *
	* Notes:
	* - If $useupper is true uppercase characters will be used; if false they will be excluded.
	* - If $usespecial is true special characters will be used; if false they will be excluded.
	* - If $usenumbers is true numerical characters will be used; if false they will be excluded.
	* - If $minlength is equal to $maxlength a string of length $maxlength will be returned.
	* - Not all special characters are included since they could cause parse errors with queries.
	*
	* Modify at will.
	* @author Peter Mugane Kionga-Kamau
	* @website http://www.pmkmedia.com
	* 
	* @previousNames cmfMakeRandomString,make_random_string
	*/
	function makeRandomString($minlength, $maxlength, $useupper, $usespecial, $usenumbers) {
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper)   $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|][";   // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else                         $length = mt_rand ($minlength, $maxlength);
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}
    
	/**
	* 
	* @previousNames is_email_valid
	*/
	function isEmailValid($mailAddress) {
		if (preg_match("/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i", $mailAddress)) {
			return true;
		} else {
			return false;
		}	
	}
	
	
	
	/**
	* @desc 
	* @previousNames str_word_count_multibyte
	*/
	function countWordMultibyte($string,$format=0,$charlist=null) {
		if (preg_match_all('/([^ ]*)/', $string, $result, PREG_PATTERN_ORDER)) {
			$result=$result[1];
			if (is_array($result))
				foreach ($result as $word) {
					if (!empty($word))
						$words[]=$word;
				}
			if (is_array($words)) {
				if ($format==0) return count($words);
				if ($format==1) return $words;
				if ($format==2) return trigger_error(__FUNCTION__.": only format=2 is not available yet",E_ERROR);
			}
		}
		return 0;
	}
	
	
	
	/**
	* @previousNames encrypt,cmfEncrypt
	*/
	function simpleEncrypt($string, $key)
	{
	        $result = '';
	        for($i=1; $i<=strlen($string); $i++)
	        {
	                $char = substr($string, $i-1, 1);
	                $keychar = substr($key, ($i % strlen($key))-1, 1);
	                $char = chr(ord($char)+ord($keychar));
	                $result.=$char;
	        }
	        return $result;
	}



	/**
	* @previousNames decrypt,cmfDecrypt
	*/
	function simpleDecrypt($string, $key)
	{
	        $result = '';
	        for($i=1; $i<=strlen($string); $i++)
	        {
	                $char = substr($string, $i-1, 1);
	                $keychar = substr($key, ($i % strlen($key))-1, 1);
	                $char = chr(ord($char)-ord($keychar));
	                $result.=$char;
	        }
	        return $result;
	}

}


class cmfcArray {
	 /**
	 * solving farsi sorting problem
	 * 
	 * <code>
	 * 	$test2[0]="ï؟½ï؟½ï؟½ï؟½ï؟½" ;
	 * 	$test2[1]="ali";
	 * 	$test2[2]="xxxi";
	 * 	$test2[3]="ï؟½ï؟½ï؟½ï؟½";
	 * 	$test2[4]="ï؟½??ï؟½ï؟½";
	 * 	for($i=0;$i<=11;$i++) $test2[$i] =fa_encode($test2[$i] );
	 * 	sort($test2);
	 * 	for($i=0;$i<=11;$i++)
	 * 	{
	 *		echo fa_decode($test2[$i]) ."<br>" ;
	 * 	}
	 * </code>
	 * 
	 * @author Arash Mikaili
	 * @author Sina Salek
	 * @param unknown $arr
	 * @return void
	 * @previousNames farsi_sort
	*/
	function farsiSort(&$arr) {
	    foreach ($arr as $key=>$item) {
	    	$arr[$key]=cmfcString::encodeFarsiText($item);
	    }
	    asort($arr);
	    foreach ($arr as $key=>$item) {
			$arr[$key]=fa_decode($item);
	    }
	}	
	
	/**
	 * Find a string in array of wildcards
	 * @param $needle
	 * 	string
	 * @param $haystack
	 * 	array of wild cards
	 * @return boolean
	 */
	function inArrayWildcard($needle, $haystack) {
	    return true;
	    #this function allows wildcards in the array to be searched
	    foreach ($haystack as $value) {
	        if (true === fnmatch($value, $needle)) {
	            return true;
	        }
	    }
	    return false;
	}
    
    
    /**
    * only works with 2 dimentional array;
    * @param array
    * @param array $value = array ('key'=>'id','value'=>5)
    * @previousNames cmfcGetArrayKeyByValue
    */
    function getKeyByValue($arr,$value) {
	    $n=-1;
	    foreach ($arr as $aKey=>$aItem) {
		    $n++;
		    //if (!isset($akey)) $aKey=$n;
		    if (!is_array($aItem)) {
			    if ($Item==$value) return $aKey;
		    } else {
			    if ($aItem[$value['key']]==$value['value']) {
				    return $aKey;
			    }
		    }
	    }
    }
    
    
    /**
    * only works with 2 dimentional array;
    * @param array
    * @param array $value = array ('key'=>'id','value'=>5)
    * @previousNames cmfcGetArrayKeyByValue
    */
    function getKeyByValueWildcard($arr,$value,$multiValue=false) {
	    $n=-1;
	    foreach ($arr as $aKey=>$aItem) {
		    $n++;
		    //if (!isset($akey)) $aKey=$n;
		    if (!is_array($aItem)) {
			    if ($Item==$value) return $aKey;
		    } else {
			    if (fnmatch($value['value'],$aItem[$value['key']])===true) {
					if($multiValue)
						$aKeyArray[]=$aKey;
					else
				    	return $aKey;
			    }
		    }
	    }
		return $aKeyArray;
    }
    
    
    
	/**
	* 
	* @previousNames array_key_insensitive
	*/
	function keyInsensitive($needle, $haystick) {
	   foreach($haystick as $key => $val) {
	       if (strtolower($needle) == strtolower($key)) {
	           return($key);
	       }
	   }
	   return(false);
	}
	
	
	/**
	* 
	*
	* Similar to array_merge_recursive but keyed-valued are always overwritten.
	* Priority goes to the 2nd array.
	*
	* @static yes
	* @public yes
	* @param $paArray1 array
	* @param $paArray2 array
	* @return array
	*/
	function mergeRecursive($paArray1, $paArray2)
	{
	   if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
	   foreach ($paArray2 AS $sKey2 => $sValue2)
	   {
	       $paArray1[$sKey2] = cmfcArray::mergeRecursive(@$paArray1[$sKey2], $sValue2);
	   }
	   return $paArray1;
	}
	
	
	/**
	* <code>
	* $arr=array(
	* 	'level1'=>array(
	* 		'level2'=>5
	* 	)
	* )
	* cmfcArray::getArrayValueByPath($arr,array('level1','level2'))
	* </code>
	* result is "5"
	* @param $arr array
	* @param $path array
	* return variant //false on invalid path
	*/
	function getValueByPath($arr,$path) {
		$r=$arr;
		if (!is_array($path)) return false;
		foreach ($path as $key) {
			if (isset($r[$key]))
				$r=$r[$key];
			else
				return false;
		}
		return $r;
	}
	
	
	/**
	* <code>
	* $arr=array(
	* 	'level1'=>array(
	* 		'level2'=>5
	* 	)
	* )
	* cmfcArray::setValueByPath($arr,array('level1','level2'),12)
	* //value of level2 array is now "12"
	* </code>
	* @author Sina Salek <sina.salek.ws>
	* @param array
	* @param array
	* @param variant
	* @param boolean
	* @return variant //false on invalid path
	* @previousNames arrayPath,cmfArrayPath
	*/
	function setValueByPath($arr,$path,$value,$createPath=false) {
		if ($createPath)
			$r=&$arr;
		else	
			$r=$arr;
		if (!is_array($path)) return false;
		foreach ($path as $key) {
			if ($createPath and !isset($r[$key])) {
				$r[$key]='';
			}
			if (isset($r[$key])) {
				$r=&$r[$key];
			} else {
				return false;
			}
		}

		$r=$value;
		
		return $r;
	}
	
	/**
	* @author Arash Dalir
	*/
	function sortItems($array, $by, $type){
		if (is_array($array)){
			$count = count ($array);
			for ($i = 0; $i < $count-1; $i++){
				for ($j = $i+1; $j < $count; $j++){
					$temp = '';
					
					if ( strtolower($type) == 'asc' ){
						$condition = ($array[$i][$by] > $array[$j][$by]);
					} else {
						$condition = ($array[$i][$by] < $array[$j][$by]);
					}
					
					if ($condition){
						$temp = $array[$i];
						$array[$i] = $array[$j];
						$array[$j] = $temp;
					}
				}
			}
		}
		return $array;
	}
	
	/**
	 * Sorting two dimensional array by sub-key's value
	 * Example : 
	 * <code>
	 * $users=array(
	 * 		array(
	 * 			'username'=>'Ashkan1',
	 * 			'age'=>20, 
	 * 		),
	 * 		array(
	 * 			'username'=>'Ashkan2',
	 * 			'age'=>35, 
	 * 		),
	 * 		array(
	 * 			'username'=>'Ashkan3',
	 * 			'age'=>18, 
	 * 		),  
	 * );
	 * $users=cmfcArray::sort($users,'age','asc')
	 * </code> 
	 * Result :  
	 * <code>
	 * array (
	 * 		array(
	 * 			'username'=>'Ashkan3',
	 * 			'age'=>18, 
	 * 		),
	 * 		array(
	 * 			'username'=>'Ashkan1',
	 * 			'age'=>20, 
	 * 		),
	 * 		array(
	 * 			'username'=>'Ashkan2',
	 * 			'age'=>35, 
	 * 		), 
	 * )
	 * </code>  
	 * 
	 * @param $array array
	 * @param $by string //Sub-key name
	 * @param $type string //asc,desc
	 * @return array
	 */
	function sort($array,$by,$type='asc') {
		$sortField=&$by;
		$multArray=&$array;
		
		$tmpKey='';
		$ResArray=array();
		$maIndex=array_keys($multArray);
		$maSize=count($multArray)-1;
		for($i=0; $i < $maSize ; $i++) {
			$minElement=$i;
			$tempMin=$multArray[$maIndex[$i]][$sortField];
			$tmpKey=$maIndex[$i];
			for ($j=$i+1; $j <= $maSize; $j++) {
				if ($multArray[$maIndex[$j]][$sortField] < $tempMin ) {
					$minElement=$j;
					$tmpKey=$maIndex[$j];
					$tempMin=$multArray[$maIndex[$j]][$sortField];
		     	}
			}
			$maIndex[$minElement]=$maIndex[$i];
			$maIndex[$i]=$tmpKey;
		}
		if ($type=='asc') {
			for ($j=0;$j<=$maSize;$j++) {
				$ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
			}
		} else {
			for ($j=$maSize;$j>=0;$j--) {
				$ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
			}
		}
		
		return $ResArray;
	}
	
	
	
	/**
	 * set the pointer of an array to the given key
	 * 
	 * @author Sina Salek <sina.salek.ws>
	 * @param array:pointer $array
	 * @param variant $key
	 * @return unknown
	 * @previousNames array_set_current,cmfArraySetCurrent
	 */
	function setCurrent(&$array, $key)
	{
	   reset($array);
	   while (current($array)!==FALSE){
	       if (key($array) == $key) {
	           break;
	       }
	       next($array);
	   }
	   return current($array);
	}
	
	
	/**
	* 
	*converts this :
	* <code>
	* Array (
	*   [image] => Array (
	*       [name] => Array (
	*           [1] => Array (
	*               [columns] => Array (
	*                   [file_name] => Water lilies.jpg
	*                )
    *            )
    *       )
    *    )
    * )
    * </code>
    *	to :
    * <code>
    * array(5) { [0]=> string(5) "image" [1]=> string(4) "name" [2]=> int(1) [3]=> string(7) "columns" [4]=> string(9) "file_name" }
    * </code>
    *
	* @previousNames convertNestedArrayToFlatArray,cmfConvertNestedArrayToFlatArray
	*/
	function convertToSimilarFlatArray($myArray) {
	    $currArr=$myArray;
	    while (is_array($currArr)) {
	    	$path[]=key($currArr);
	    	$currArr=reset($currArr);
	    };
	    return $path;
	}
	
	
	/**
	* flatten multidimensional array to one dimension
	* preserves keys by generating a key for the flattened array which consists of the
	* key-path of the multidimensional array separated by $Separator
	* usage: array convertToFlatArray( array Array [, string Separator] )
	* 
	* convert this
	* <code>
	* $arr=array(
	* 	'a'=>1,
	* 	'b'=>array(
	* 		'ba'=>2,
	* 		'bb'=>3
	* 	)
	* )
	* </code>
	* to :
	* <code>
	* array(
	* 	'a'=>1,
	* 	'b.ba'=>2,
	* 	'b.bb'=>3
	* )
	* </code>
	* 
	* @see cmfcArray::flatToMultiDimensional
	*/
	function convertToFlatArray($Array,$Separator='.',$FlattenedKey='') {		
		$FlattenedArray=Array();
		foreach($Array as $Key => $Value) {
			if(is_Array($Value)) {
				$_FlattenedKey=(strlen($FlattenedKey)>0?$FlattenedKey.$Separator:"").$Key;
      			$FlattenedArray=array_merge(
	  				$FlattenedArray,
        			cmfcArray::convertToFlatArray($Value,$Separator,$_FlattenedKey)
                );				
			} else {
				$_FlattenedKey=(strlen($FlattenedKey)>0?$FlattenedKey.$Separator:"").$Key;
				$FlattenedArray[$_FlattenedKey]=$Value;
			}
		}
		return $FlattenedArray;
	}
	
	/*
	http://ir2.php.net/manual/en/ref.array.php#80839
	
	$parts = explode('.',$k);
	if(count($parts)>1)
	{
		unset($temp);
		$temp[$formInfo['name']]['fieldsInfo'][$row['id']][$parts[0]] = $this->wsfMakeSubArray($parts , 0,$val );
		$result = cmfcArray::mergeRecursive($result , $temp );
		
		unset($result[$formInfo['name']]['fieldsInfo'][$row['id']][$k]);
	}
	
	function &flatToMultiDimensional($inputArray , $key , &$finalVal)
	{
		if(isset($inputArray[++$key])) {
			return array ($inputArray[$key]=>cmfcArray::flatToMultiDimensional($inputArray, $key , &$finalVal));	
		} else {
			return $finalVal;
		}	
	}
	*/
	
	

	/**
	 * if key does not exist in array, compiler get an Notice or Warning message, this function is for preventing this kind of messages
	 * $key is an variant, you can use it in three way
	 * Description :
	 * $key=$index
	 * //$key='index1,index2,98';
	 * $key=array($index1,$index2,$index3);
	 *
	 * @param array $array
	 * @param variant $key
	 * @return variant
	 * @previousNames array_value,cmfArrayValue
	 */
	function getValue($array,$key)
	{
		$result=null;
		if (isset($array))
			if (!empty($array) and is_array($array))
			{
				if (!is_array($key))
				{
					if (array_key_exists($key,$array)) { $result=$array[$key]; }
				} else
				{
					$result=$array;
					foreach ($key as $k)
						if (array_key_exists($k,$result)) { $result=$result[$k];}
					else { $result=null; break;}
				}
			}
		return $result;
	}
	
	
	/**
	* @previousNames arrayPath,cmfArrayPath
	*/
	function &path(&$array,$path,$createIfNotExists=false) {
	        if(!is_array($array)) {
	                trigger_error('array_path(): First argument should be an array', E_USER_WARNING);
	        }
	        settype($path, 'array');

	        $offset =& $array;
	        foreach ($path as $index) {
	                if (!isset($offset[$index])) {
	                        if ($createIfNotExists)
	                                $offset[$index]='';
	                        else {
	                                trigger_error("Undefined offset: $index");
	                                return false;
	                        }
	                }
	                $offset =& $offset[$index];
	        }
	        return $offset;
	}
	
	
	
	
	function walkRecursive(&$input,$funcname,$path=array(),$depth=0) {
		//$funcname = array(&$this, $this->funcname);
		if (!is_callable($funcname)) {
			return false;
  		}
		if (!is_array($input)) {
			return false;
		}

		$depth++;
		foreach (array_keys($input) AS $keyIdx => $key) {
			$saved_value = $input[$key];
       		$saved_key = $key;
       		$path[]=$key;
       		
			call_user_func_array($funcname, array(&$input[$saved_key], &$key, $path, $depth));

       		if ($input[$saved_key] !== $saved_value || $saved_key !== $key) {
				$saved_value = $input[$saved_key];
				unset($input[$saved_key]);
           		$input[$key] = $saved_value;
       		}
       		
      		if (is_array($input[$key])) {
				if (!cmfcArray::walkRecursive($input[$key],$funcname, $path, $depth)) return false;
				$depth--;
      		}
      		array_pop($path);
  		} 
  		return true;
	}
	
	/**
	 * Gives the list of items which are new or changed comparing to the first array
	 * numberChange : Gives the difference between numbers
	 * 
	 * @todo
	 * - support deleted items
	 * - making it multi-dimensional
	 * - support strict mode for chagnes in type
	 * @param $array1 array
	 * @param $array2 array
	 * @param $options array
	 * @return array
	 */	
	function diff($array1,$array2,$options=array('strict'=>false,'numberChange'=>false)) {
		$changes=array();

		foreach ($array2 as $columnName=>$columnValue) {
			$oldColumnValue=$array1[$columnName];
			if ($columnValue!=$oldColumnValue) {
				if ($options['numberChange']===true and (is_numeric($columnValue) or empty($columnValue)) and 
					(is_numeric($oldColumnValue) or empty($oldColumnValue))) {
					$changes[$columnName]=intval($columnValue)-intval($oldColumnValue);
				} else {
					$changes[$columnName]=$columnValue;
				}
				// $columnName.'='.$changes[$columnName].'|'.$oldColumnValue.' -> '.$columnValue.'<br/>';
			}
		}

		return $changes;
	}
}



class cmfcNumber {
	/**
	 * %(s) --> symbol
	 * %(v) --> value
	 * %(n) --> name
	 * %(sn) -> short name
	 *
	 * @param string $value
	 * @param string $name
	 * @param string $short_name
	 * @param string $symbol
	 * @param string $format
	 * @return string
	 * @previousNames format_unit,
	 */
	function formatUnit($value,$name,$short_name,$symbol,$format)
	{
	        $result=$format;
	        $result=str_replace('%(v)',$value,$result);
	        $result=str_replace('%(n)',$name,$result);
	        $result=str_replace('%(sn)',$short_name,$result);
	        $result=str_replace('%(s)',$symbol,$result);
	        if ($result=='') {$result=$value;}
	        return $result;
	}

	/**
	* @previousNames just_numbers
	*/
	function justNumbers($value)
	{
	        return str_replace(array(':','/',chr(92),' ','-'),array('','','','',''),$value);
	}
}


class cmfcFile {
	/**
	* Uploading File into a specific Directory
	* $old_name refres is used in Updating, when you do not have
	* any file, the function will return old file name.
	* NOTICE : to using current location as destination path, you should use getcwd() before $dir parameter.
	* Valid Formats By Default : 'jpg','gif','jpeg','pdf','xls','zip','gz','png','psd','rar','zip','doc','txt','log'
	* <code>
	* $image_file_name=cmfUploadFile($uploaded_file_path,$file_name,$this->path_temp,$image_file_name,array('jpg','png','gif'));
	* </code>
	* @changes
	* 	- $validExtensions added for improving security (not tested yet) will return false if fails
	* 
	* @previousNames upload_file
	*/
	function uploadFile($file,$file_name,$dir,$old_name,$validExtensions=array('jpg','gif','jpeg','pdf','xls','zip','gz','png','psd','rar','zip','doc','txt','log','csv','tar','flv','swf','mp3','ogg','avi','mpg','mpeg','doc','docx','ppt','pptx'))
	{
		if($file !='')
		{
			$dest = $dir."/".$file_name;
			while ( file_exists($dest) )
			{
				$file_name = rand(1000,1000000).$file_name;
				$dest = $dir."/".$file_name;
			}
			$fileExt=strtolower(cmfcFile::getFileExtension($dest));
			if (!in_array($fileExt,$validExtensions)) {
				return false;
			}
			$res = move_uploaded_file($file,$dest);
			$dest=cmfcDirectory::normalizePath($dest);
			//if (file_exists($dest)) {
				chmod($dest,0755);
				return $file_name;
			//} else {
				return false;
			//}
		} else {
			return $old_name;
		}
	}
	
	
	
	function getFileName($path)
	{
		$path_parts = pathinfo($path);
		//	echo $path_parts['dirname'], "\n";
		//	echo $path_parts['basename'], "\n";
		$baseFileName = $path_parts['basename'];
		$baseFileNameParts = explode('.',$baseFileName);
		return $baseFileNameParts[0];
		
	}
	
    /**
    * $timeout is in seconds
    * @previousNames filemtime_remote,cmfGetRemoteFileModifyTime
    */
	function getRemoteFileModifyTime($uri,$timeout=null)
	{
	   $uri = parse_url($uri);
	   $handle = @fsockopen($uri['host'],80);
	   if (!is_null($timeout)) stream_set_timeout($handle,$timeout);
	   if(!$handle)
		   return 0;
	   fputs($handle,"GET $uri[path] HTTP/1.1\r\nHost: $uri[host]\r\n\r\n");
	   $result = 0;
	   while(!feof($handle))
	   {
		   $line = fgets($handle,1024);
		   if(!trim($line))
		       break;
		   $col = strpos($line,':');
		   if($col !== false)
		   {
		       $header = trim(substr($line,0,$col));
		       $value = trim(substr($line,$col+1));
		       if(strtolower($header) == 'last-modified')
		       {
		           $result = strtotime($value);
		           break;
		       }
		   }
	   }
	   fclose($handle);
	   return $result;
	}
	
	/**
	* <code>
	* cmfFile::UploadFileSimple($_FILES['image']['tmp_name'],'gallery/image.jpeg',0777)
	* </code>
	* @previousNames simple_file_upload
	*/
	function uploadFileSimple($uploaded_file_path,$dest_path,$chmod=0777) {
		if (is_uploaded_file($uploaded_file_path)) {
			$res = move_uploaded_file($uploaded_file_path,$dest);
			chmod($dest,$chmod);
			if (file_exists($dest)) { return true; } else {return false;}
		}
		return false;
	}
	
	
	
	/**
	* @previousNames remove_dir
	*/
	function removeDir($dir)                                            
	{
		return cmfcDirectory::remove($dir);
	}
	
	/**
	* delete everything in the directory recursively
	* @previousNames clear_dir
	*/
	function clearDir($dir) {
		return cmfcDirectory::clear($dir);
	}
	

	/**
	* accept file full path and return file extension without dot
	* <code>
	* echo cmfcFile::getFileExtension('/home/test/file.php');
	* </code>
	* result would be "php"
	* 
	* @previousNames : get_file_extension
	*/
	function getFileExtension($path,$keepOriginalCase=false) {
		$path_parts = pathinfo($path);
		//	echo $path_parts['dirname'], "\n";
		// echo $path_parts['basename'], "\n";
		if ($keepOriginalCase) {
			$result=$path_parts['extension'];
		} else {
			$result=strtolower($path_parts['extension']);
		}
		return $result;
	}
	
	
	function directoryAsMultiLevelArray($topdir, &$list, $ignoredDirectories=array()) {
		return cmfcDirectory::getAsMultiLevelArray($topdir, &$list, $ignoredDirectories);
	}
	
	
	function directoryAsSingleLevelArray ($startdir="./", $searchSubdirs=1, $directoriesonly=0, $maxlevel="all", $level=1) {
		return cmfcDirectory::getContentsAsSingleLevelArray($startdir, $searchSubdirs, $directoriesonly, $maxlevel, $level);
	}
	

	/**
	* 
	* @previousNames download_file
	*/
	function download($file,$customName=null)
	{
	    //First, see if the file exists
	    if (!is_file($file)) { die("<b>404 File not found!</b>"); }
	    //Gather relevent info about file
	    $len = filesize($file);
	    $filename = basename($file);
	    $file_extension = strtolower(substr(strrchr($filename,"."),1));
	    
	    if (!is_null($customName)) {
	    	$filename=$customName;
		}
	    
	    //This will set the Content-Type to the appropriate setting for the file
	    switch( $file_extension )
	    {
	        case "pdf": $ctype="application/pdf"; break;
	        case "exe": $ctype="application/octet-stream"; break;
	        case "zip": $ctype="application/zip"; break;
	        case "doc": $ctype="application/msword"; break;
	        case "xls": $ctype="application/vnd.ms-excel"; break;
	        case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
	        case "gif": $ctype="image/gif"; break;
	        case "png": $ctype="image/png"; break;
	        case "jpeg":
	        case "jpg": $ctype="image/jpg"; break;
	        case "mp3": $ctype="audio/mpeg"; break;
	        case "wav": $ctype="audio/x-wav"; break;
	        case "mpeg":
	        case "mpg":
	        case "mpe": $ctype="video/mpeg"; break;
	        case "mov": $ctype="video/quicktime"; break;
	        case "avi": $ctype="video/x-msvideo"; break;
	        //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
	        case "php":
	        case "htm":
	        case "html":
			//case "txt": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;
	        default: $ctype="application/force-download";
	    }

	    //Begin writing headers
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: public");
	    header("Content-Description: File Transfer");

	    //Use the switch-generated Content-Type
	    header("Content-Type: $ctype");
	    //Force the download

	    
	    $header="Content-Disposition: attachment; filename=".$filename.";";
	    header($header );
	    header("Content-Transfer-Encoding: binary");
	    header("Content-Length: ".$len);
	    @readfile($file);
	    exit;
	}
	
	
	/**
	* @previousNames cmfGetUniqueRandomFilename
	*/
	function getUniqueFileName($path,$name) {
   		list($usec, $sec) = explode(" ", microtime());
	    $microtime=((float)$usec + (float)$sec);

		$file='';
		do {
			$file=$path.'/'.date('YmdHis').$microtime.rand(1,900000).$name;
		} while (file_exists($file));
		return $file;
	}

	/**
	* @return boolean $file //number on success boolean false on failure
	*/
	function getNumberOfLines($file) {
		$lines=0;
		//$contents=file_get_contents($file);
	    $handle = @fopen($file, "r");
	    if ($handle) {
	       while (!feof($handle)) {
	       	   $buffer = fgets($handle);
	       	   $lines++;
		   }
		   return $lines;
		}
		return false;
	}
}


class cmfcHtml {
		
	/**
	* IE cannot represent persian floating numbers separated with /.
	* this function fixes this issue
	* @author sina salek
	* @param string/array
	* @return string/array
	*/
	function fixPersianFloatingNumbersInIe($content) {
		if (is_string($content)) {
			return preg_replace('%(^|[^/0-9])([0-9]+ *)/( *[0-9]+)([^/0-9]|$)%s', '$1<span dir="ltr">$2/$3</span>$4', $content);
		} elseif(is_array($content)) {
			foreach ($content as $key=>$value) {
				$content[$key]=preg_replace('%(^|[^/0-9])([0-9]+ *)/( *[0-9]+)([^/0-9]|$)%s', '$1<span dir="ltr">$2/$3</span>$4', $value);
			}
			return $content;
		}
		return false;
	}

	
	function utf8ToHtmlEntities($string) { 
	      /* Only do the slow convert if there are 8-bit characters */ 
	    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */ 
	    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string)) { 
	        return $string; 
		}
	    // decode three byte unicode characters 
	    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",        
	    "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",    
	    $string); 

	    // decode two byte unicode characters 
	    $string = preg_replace("/([\300-\337])([\200-\277])/e", 
	    "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", 
	    $string); 

	    return $string; 
	}
	
	
	function printr($var, $echo = true){
		if (!$var)
			$var = 'Empty/False/Null';

		if ($echo){
			echo '<pre dir="ltr" style="text-align:left; background:#FFCC99; color:#000000; font-family:Verdana, Arial, Helvetica, sans-serif; overflow:auto">'.print_r($var, true).'</pre>';
		} else return print_r($var, true);
	}
	
	/**
	* accept an html and select one of its options according 
	* to select name and option value
	* 
	* @param string $name //name of the select tag
	* @param variant $value //value of the option you want selected
	* @param boolean $selected //should be true
	* @param string $html //html which contains select html
	* @param string $pattern //pattern of option tags
	*/
	function changeSelectOptionSelected($name,$value,$selected,$html,$pattern='<option value="__value__">') {
		if (preg_match('/<(select*)\\b name="'.$name.'"[^>]*>(.*?)<\/\\1>/sim', $html, $regs)) {
			$selectHtml=$regs[0];
			
			$pattern=str_replace('__value__',$value,$pattern);
			if ($selected==true)
				$patternNew=str_replace('>',' selected="selected" >',$pattern);
			
			
			$selectNewHtml=str_replace($pattern,$patternNew,$selectHtml);
			$html=str_replace($selectHtml,$selectNewHtml,$html);
			return $html;
		}
		
		return false;
	}

    /**
    * @todo
    * 	- old style $options parameters parameters like max_columns should be removed
    * 	- should use cmfcHtml::drawMultiRadioBoxes
    */
	function drawMultiCheckBoxes($baseName,$items,$selectedItems,$displayMode='table',$options=null) {
		$result='';
		if (isset($options['value'])) $options['value']=$options['value'];
		if (isset($options['max_columns'])) $options['maxColumns']=$options['max_columns'];
		if (isset($options['max_rows'])) $options['maxRows']=$options['max_rows'];
		if (isset($options['title_key_name'])) $options['titleKeyName']=$options['title_key_name'];
		if (isset($options['value_key_name'])) $options['valueKeyName']=$options['value_key_name'];
		if (isset($options['sort_type'])) $options['sortType']=$options['sort_type'];
		if (isset($options['value_type'])) $options['valueType']=$options['value_type'];
		if (isset($options['unique_key_name'])) $options['uniqueKeyName']=$options['unique_key_name'];
		//if (isset($options['checkBoxesAttributes'])) $=$options['checkBoxesAttributes'];
		   
		if (!isset($options['value'])) $options['value']='1';
		if (!isset($options['maxColumns'])) $options['maxColumns']='5';
		if (!isset($options['maxRows'])) $options['maxRows']='5';
		if (!isset($options['titleKeyName'])) $options['titleKeyName']='1';
		if (!isset($options['valueKeyName'])) $options['valueKeyName']='1';
		if (!isset($options['sortType'])) $options['sortType']='horizontal';
		if (!isset($options['valueType'])) $options['valueType']='uniqueKey';
		if (!isset($options['tagName'])) $options['tagName']='checkbox';
		
		$checkBoxesAttributesStr=cmfcHtml::attributesToHtml($options['checkBoxesAttributes']);
		
		if ($displayMode=='table')
			$result.='<table border="0">'."\n".'</tr>'."\n";
	    
		foreach ($items as $item) {
			if ($displayMode=='table') {
				if (!isset($counter) or $counter==0) {
					$counter=0;
					$result.='</tr>'."\n".'<tr>'."\n";
				}
				
				$counter++;
				
				if ($counter>=$options['maxColumns']) {
					$counter=0;
				}
				if (in_array($item[$options['uniqueKeyName']],$selectedItems)) $checked='checked="checked"'; else $checked='';
				if ($options['valueType']=='custom') $value=$options['value'];
				if ($options['valueType']=='unique_key' or $options['valueType']=='uniqueKey') $value=$item[$options['uniqueKeyName']];
				$result.='<td><input name="'.$baseName.'['.$item[$options['uniqueKeyName']].']" type="'.$options['tagName'].'" value="'.$value.'" '.$checkBoxesAttributesStr.' '.$checked.'>&nbsp;'.$item[$options['titleKeyName']].'</td>'."\n";
			}
		}
		
		if ($displayMode=='table')
			$result.='</tr>'."\n".'</table>'."\n";
		return $result;
	}
	
	
	/**
	* this function accepts three types of inputs: checkbox, radio and a CUSTOM type,
	* in "checkbox" type, $baseName must be an array to support multi selection.
	* in "custom mode" type, an extra array "tag" must be added to $options which has two items: 'selected' & 'notSelected'
	* in all modes, an array 'inputAttributes' can be added to $options to specify the generic attributes of the tag.
	* 
	* the following example shows the usage with custom mode:
	* <code>
	* 	echo cmfcHtml::drawMultiRadioBoxes(
	* 		$internalName,
	* 		$request[$internalName], 
	* 		$items, 
	* 		$optionsTable['columns']['id'],
	* 		$optionsTable['columns']['name'],
	* 		array(
	* 			'columns' => 6,
	* 			'type' => 'custom',
	* 			'tag' => array(
	* 				'notSelected' 	=> '<img src="interface/images/icon-not-selected.jpg" %inputAttributes% />%value%',
	* 				'selected' 		=> '<img src="interface/images/icon-selected.jpg" %inputAttributes% />%value%',
	* 			),
	* 			'inputAttributes' => array(
	* 				'style' => 'width:16px; height:16px;',
	* 			)
	* 		)
	* 	);
	* 	</code>
	* @author Sina Salek <sina.salek.ws>
	* @author Arash Dalir
	* @author Akbar Nasr Abadi
	* 
	* @todo
	* 	- should support arranging options vertically or horizontally
    * 	- in non custom way should use cmfcHtml::drawCheckBox and cmfcHtml::drawRadioBox
	*/		
	function drawMultiRadioBoxes($baseName,$selectedItem, $items, $valueColumnName, $titleColumnName, $options=null) {

		$sortBy = $options['sortBy'];
		if (!$sortBy)
			$sortBy = 'value';
		
		$optionType = $options['type'];
		if ($optionType=='radio' or empty($optionType)) {
			$optionType = 'radio';
		} else {
			$baseName.='[]';
		}
		
		//echo $optionType;
		
		if (!is_array($selectedItem))
			$selectedItems[] = $selectedItem;
		else
			$selectedItems = $selectedItem;
		
		$displayMode = $options['displayMode'];
		if (!$displayMode)
			$displayMode = 'table';
		
		$template['table'] = array(
			'mainWrapper' => '<table %wrapperAttributes%>%contents%</table>',
			'contents' => '<tr>%fields%</tr>',
			'fields' => '<td>%field%</td>',
			'defaultAttributes' => array(
				'border' => 0,
			),
		);
		$template['normal'] = array(
			'mainWrapper' => '<p>%contents%</p>',
			'contents' => '%fields%',
			'fields' => '%field%<br />',
			'defaultAttributes' => array(
				'border' => 0,
			),
		);
		
		if (is_array($options[$displayMode])){
			$attributes = array_merge($template[$displayMode]['defaultAttributes'], $options[$displayMode]);
			$wrapperAttributes = cmfcHtml::attributesToHtml($attributes);
		}
		elseif (is_string($options[$displayMode]))
			$wrapperAttributes = $options[$displayMode];
		
		if (is_array($options['inputAttributes']))
			$inputAttributes = cmfcHtml::attributesToHtml($options['inputAttributes']);
		elseif (is_string($options['inputAttributes']))
			$inputAttributes = $options['inputAttributes'];
		
		$numColumns = $options['columns'];
		if (!$numColumns)
			$numColumns = 5;
		
		if (is_string($items)){
			$items = cmfcMySql::getRowsCustom($items);
		}
		
		$counter = 0;
		$contents = '';
		if (is_array($items)){
			//cmfcHtml::printr($items);
			switch ($sortBy){
			case 'title':
				$items = cmfcArray::sortItems($items, $titleColumnName, 'asc');
				break;
			
			case 'value':
				$items = cmfcArray::sortItems($items, $valueColumnName, 'asc');
				break;
			
			default:
				$items = cmfcArray::sortItems($items, $valueColumnName, 'asc');
			}
			//cmfcHtml::printr($items);
			//cmfcHtml::printr( $options);
			foreach ($items as $item) {
				//cmfcHtml::printr($item);
				//echo $numColumns;
				$counter ++;
				
				if (in_array($item[$valueColumnName], $selectedItems) )
					$checked='checked="checked"';
				else 
					$checked='';
				
				if (in_array($optionType, array('checkbox', 'radio')) ){
					$value = $item[$valueColumnName];
					$field = '<input '.$inputAttributes.
						'name="'.$baseName.'" id="'.$baseName.$counter.'" type="'.$optionType.'" value="'.$value.'" '.$checked.'>&nbsp;<label for="'.$baseName.$counter.'">';
					
					if(is_array($options['strongItems']))
					{
						if(in_array($value, $options['strongItems'])) {
							$itemTitleColumnName = "<b>".$item[$titleColumnName]."</b>";
						} else {
							$itemTitleColumnName = $item[$titleColumnName];
						}
					} else {
						$itemTitleColumnName = $item[$titleColumnName];
					}
					$field .= $itemTitleColumnName;
					$field .= '</label>';
				}
				elseif ($optionType == 'custom'){
					if ($checked)
						$tag = $options['tag']['selected'];
					else
						$tag = $options['tag']['notSelected'];
					
					$value = $item[$titleColumnName];
					$tag = str_replace('%value%', $value, $tag);
					$tag = str_replace('%inputAttributes%', $inputAttributes, $tag);
					
					$field = $tag;
				}
				
				$html .= str_replace('%field%', $field, $template[$displayMode]['fields']);
				
				if ($counter % $numColumns == 0) {
					$contents .= str_replace('%fields%', $html, $template[$displayMode]['contents']);
					$html = '';
				}
			}
			if ($html){
				$contents .= str_replace('%fields%', $html, $template[$displayMode]['contents']);
			}
			$result = str_replace('%contents%', $contents, $template[$displayMode]['mainWrapper']);
		}
		else{
			$result = 'no valid items';
		}
		return $result;
	}
    
    
	function drawCheckBox($name , $value="", $defaultValue= "1",$disabled=false, $moreAttributes=array()) {  
        $moreAttributesStr=cmfcHtml::attributesToHtml($moreAttributes);
		if ($value==$defaultValue) $checked='checked="checked"';
		if ($disabled==true) $disabled='disabled="disabled"';
		$html='<input name="'.$name.'" id="'.$name.'" type="checkbox" value="'.$defaultValue.'" '.$disabled.' '.$checked.' '.$moreAttributesStr.' />';
		return $html;
	}
    
	function drawRadioButton($name , $value="", $defaultValue= "1",$disabled=false, $moreAttributes=array()) {  
        $moreAttributesStr=cmfcHtml::attributesToHtml($moreAttributes);
		if ($value==$defaultValue) $checked='checked="checked"';
		if ($disabled==true) $disabled='disabled="disabled"';
		$html='<input name="'.$name.'" id="'.$name.'" type="radio" value="'.$defaultValue.'" '.$disabled.' '.$checked.' '.$moreAttributesStr.' />';
		return $html;
	}
	
	/**
	* I spent hours looking for a function which would take a numeric HTML entity value and 
	* output the appropriate UTF-8 bytes.  I found this at another site and only had to modify 
	* it slightly; so I don't take credit for this.
	* 
	* <code>
	* $str = "Chinese: &#20013;&#25991;";
	* $str = preg_replace("/&#(\d{2,5});/e", "cmfHtmlEntityToUTF8($1);", $str);
	* </code>
	* 
	* @previousNames unichr
	*/
	function htmlEntityToUTF8($dec) {
		if ($dec < 128) {
			$utf = chr($dec);
		} else if ($dec < 2048) {
			$utf = chr(192 + (($dec - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		} else {
			$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
			$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
		}
		return $utf;
	}
	
	
	
	/**
	* takes a string of utf-8 encoded characters and converts it to a string of unicode entities
	* each unicode entitiy has the form &#nnnnn; n={0..9} and can be displayed by utf-8 supporting
	* browsers
	* ronen at greyzone dot com
	 8 01-Mar-2002 08:37
	* The following function will take a utf-8 encoded string and convert it to Unicode entities (the format is &#nnn; or &#nnnnn; with n={0..9} ).  Most browsers will display Unicode entities regardless of the encoding of the page.  Otherwise try charset=utf-8 to make sure the entities display correctly.  This works well with IE and Mozilla (tested with Mozilla 0.9.8 for X-Windos).
	* @param $source string encoded using utf-8 [STRING]
	* @return string of unicode entities [STRING]
	* @access public
	* @previousNames utf8_to_unicode_entities,cmfUtf8ToUnicodeEntities
	*/
	function utf8ToHtmlEntity ($source) {
	   // array used to figure what number to decrement from character order value
	   // according to number of characters used to map unicode to ascii by utf-8
	   $decrement[4] = 240;
	   $decrement[3] = 224;
	   $decrement[2] = 192;
	   $decrement[1] = 0;

	   // the number of bits to shift each charNum by
	   $shift[1][0] = 0;
	   $shift[2][0] = 6;
	   $shift[2][1] = 0;
	   $shift[3][0] = 12;
	   $shift[3][1] = 6;
	   $shift[3][2] = 0;
	   $shift[4][0] = 18;
	   $shift[4][1] = 12;
	   $shift[4][2] = 6;
	   $shift[4][3] = 0;

	   $pos = 0;
	   $len = strlen ($source);
	   $encodedString = '';
	   while ($pos < $len) {
	       $asciiPos = ord (substr ($source, $pos, 1));
	       if (($asciiPos >= 240) && ($asciiPos <= 255)) {
	           // 4 chars representing one unicode character
	           $thisLetter = substr ($source, $pos, 4);
	           $pos += 4;
	       }
	       else if (($asciiPos >= 224) && ($asciiPos <= 239)) {
	           // 3 chars representing one unicode character
	           $thisLetter = substr ($source, $pos, 3);
	           $pos += 3;
	       }
	       else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
	           // 2 chars representing one unicode character
	           $thisLetter = substr ($source, $pos, 2);
	           $pos += 2;
	       }
	       else {
	           // 1 char (lower ascii)
	           $thisLetter = substr ($source, $pos, 1);
	           $pos += 1;
	       }

	       // process the string representing the letter to a unicode entity
	       $thisLen = strlen ($thisLetter);
	       $thisPos = 0;
	       $decimalCode = 0;
	       while ($thisPos < $thisLen) {
	           $thisCharOrd = ord (substr ($thisLetter, $thisPos, 1));
	           if ($thisPos == 0) {
	               $charNum = intval ($thisCharOrd - $decrement[$thisLen]);
	               $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
	           }
	           else {
	               $charNum = intval ($thisCharOrd - 128);
	               $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
	           }

	           $thisPos++;
	       }

	       if ($thisLen == 1)
	           $encodedLetter = "&#". str_pad($decimalCode, 3, "0", STR_PAD_LEFT) . ';';
	       else
	           $encodedLetter = "&#". str_pad($decimalCode, 5, "0", STR_PAD_LEFT) . ';';

	       $encodedString .= $encodedLetter;
	   }

	   return $encodedString;
	}

	
	


	/**
	* @previousNames htmlspecialchars_decode
	*/
	function htmlSpecialCharsDecode($uSTR) {
		return strtr($uSTR, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
	}
	
	/**
	* redirect to a specific location after n seconds via HTML meta tag
	*/
	function redirect($url,$delay) {
		echo '<meta http-equiv="refresh" content="'.$delay.';url='.$url.'">'; 
	}	

    /**
    * Convertes array of attributes to proper html :
    * <code>
    * array('style'=>'color:red')
    * style="color:red"
    * </code>
    */
    function attributesToHtml($attributes) {
        $html='';
        if (is_array($attributes))
        foreach ($attributes as $attrName=>$attrValue) {
            $html.=' '.$attrName.'="'.$attrValue.'"';
        } elseif(is_string($attributes))
        	$html=$attributes;
        return $html;
    }
    

	/**
	* draw html select control via array items or sql query
	* Parameter based
	* <code>	
	* echo  cmfcHtml::drawDropDown  (
	*	'$p1', 
	*	'$p2', 
	*	'$items',  
	*	'$valueCol',  
	*	'$titleCol', 
	*	null, 
	*	null, 
	*	'$defValue', 
	*	'$defTitle', 
	*	array(
	*		'$maName1' => '$maValue1'
	*	)
	* )
	* </code>
	* This code will result in something like this:
	* <code>
	* <select name="$p1" $maName1="$maValue1">
	*    <option value="$defValue"> $defTitle </option>
	*    <option value="$items[0][$valueCol]">$items[0][$titleCol]</option>
	*    <option value="$items[1][$valueCol]">$items[1][$titleCol]</option>
	*    <option value="$items[2][$valueCol]">$items[2][$titleCol]</option>
	* </select>
	* </code>
	* Note : if there is an item in the list which its value matches $p2 , that item will be shown as selected 
	*
	*
	* Suppose we have a table with below information in our /requirements/constants.inc.php
	* <code>
	* $_ws["physicalTables"]=array(
	*   'fiscalYears'=>array(
	*       'tableName'=>$_ws['databaseInfo']['tablesPrefix'].'fiscal_years',
	*       'originalTableName'=>'fiscal_years',
	*       'orderType'=>'DESC',
	*       'orderByColumnName'=>'id',
	*       'columns'=>array(
	*         'id'=>'id',
	*         'name'=>'name',
	*         'startDate'=>'start_date',
	*         'endDate'=>'end_date',
	*         'currentYear'=>'current_year',
	*       )
	*     )
	*  );
	* </code> 
	* if you want to have a form with a dropdown list which its items come from this table
	* so first you should fetch table data
	* in this case we fetched all data in table , you can choose some filter for it
	* <code>
	* $allFiscalYears = cmfcMySql::getRowsCustom("SELECT * FROM ".$_ws['physicalTables']['fiscalYears']['tableName']);
	* // or 
	* // $allFiscalYears = cmfcMySql::getRows($_ws['physicalTables']['fiscalYears']['tableName']);
	* </code>
	* <code>
	* echo cmfcHtml::drawDropDown(
	*         "fiscalYear",             // ($controlName) Name of dropdown list in the Form -> $controlName
	*         $_REQUEST['fiscalYear'],  // ($items) This value will decide which item in dropdown list should be marked as selected
	*         $allFiscalYears,          // ($moreAttributes) data from table as an array or sql query result
	*         $_ws["physicalTables"]['columns']['id'],  // ($valueColumnName) the key of the field in $allFiscalYears array (or the name of the field in $allFiscalYears sql query result which is going to be used as `Value` for each item in the list
	*         $_ws["physicalTables"]['columns']['name'],// ($titleColumnName) same as 'id' but used as the text wich will be shown for each item in dropdown list
	*         NULL,                     // ($groupNameColumnName ) used in grouping items like a tree list
	*         NULL,                     // ($groupIdColumnName) used in grouping items like a tree list
	*         '',                       // ($defaultValue) if $_REQUEST['fiscalYear'] is not set or does not match any 'id' then default item would be an item with this value
	*         '---  ---',               // ($defaultTitle) if $_REQUEST['fiscalYear'] is not set or does not match any 'id' then default item would be an item with this name
	*         array(                    // you can fill this array with any other Attribute 
	*           'class' => 'dropList',
	*           'id'    => 'dropList1',
	*           'style' => '',
	*           // 'onChange', => ''
	*         )
	*  );
	* </code>
	*
	*
	* This code will result in something like this:
	* <code>
	* <select name="fiscalYear" class="dropList" id="dropList1" style="">
	*    <option value="">---  ---</option>
	*    <option value="13851386">1385 - 1386</option>
	*    <option value="13861387">1386 - 1387</option>
	* </select>
	* </code>
	* All The Codes :
	* <code>
	* $allFiscalYears = cmfcMySql::getRowsCustom("SELECT * FROM ".$_ws['physicalTables']['fiscalYears']['tableName']);
	* 
	* echo cmfcHtml::drawDropDown(
	*         "fiscalYear",
	*         $_REQUEST['fiscalYear'],
	*         $allFiscalYears,
	*         $_ws["physicalTables"]['columns']['id'],
	*         $_ws["physicalTables"]['columns']['name'],
	*         NULL,
	*         NULL,
	*         '',
	*         '---  ---',
	*         array(
	*           'class' => 'dropList',
	*           'id'    => 'dropList1',
	*           'style' => '',
	*           // 'onChange', => ''
	*         )
	*       );
	* </code>
	*
	* another example for a virtual table
	* 
	* <code>
	* $_ws['virtualTables']=array(
	* 	'currencies' => array(
	* 		'name'=>'Currency',
	* 		'title'=>'Currency',
	* 		'title_en'=>'Currency',
	* 		'columns' => array (
	* 			'id'=>'id',
	* 			'title'=>'title',
	* 			'titleEn'=>'titleEn',
	* 			'abbreviation'=>'abbreviation',
	* 			'languageId'=>'languageId',
	* 			'relatedItem'=>'relatedItem',
	* 		),
	* 		'rows'=> array(
	* 			array(
	* 				'id'=>1,
	* 				'title'=>'Rials',
	* 				'titleEn'=>'Rials',
	* 				'abbreviation'=>'RLS',
	* 				'languageId'=>'2',
	* 				'languageTitle'=>'english',
	* 				'relatedItem'=>'1111111121'
	* 			),
	* 			array(
	* 				'id'=>2,
	* 				'title'=>'Euro',
	* 				'titleEn'=>'Euro',
	* 				'abbreviation'=>'EUR',
	* 				'languageId'=>'2',
	* 				'languageTitle'=>'english',
	* 				'relatedItem'=>'1111111122'
	* 			),
	* 		)
	* 	),
	* );
	* </code>
	* and the code :
	* <code>
	* if ($_cp['sectionInfo']['tableInfo']['columns']['currency'])
	* {
	*   // Fetching Items
	*   $items=$_ws['virtualTables']['currencies']['rows'];
	*   
	*   // Showing Dropdown list
	*   echo  cmfcHtml::drawDropDown(
	*       "rows[$num][columns][currency]",
	*       $_REQUEST['rows'][$num]['columns']['currency'],
	*       $items,
	*       $_ws['virtualTables']['currencies']['columns']['relatedItem'],
	*       $_ws['virtualTables']['currencies']['columns']['title'],
	*       'languageTitle',//Group by langauge id, set to null to ignore
	*       'languageId',//Group by langauge id, set to null to ignore
	*       '',
	*       ' '
	*     );
	* }
	* </code>
	* NOTE : For group by to works, items should be sorted by group and then sub items
	* @todo
	* 	- should use cmfcHtml::drawDropDownCustom
	* @param $controlName string
	* @param $items array|string //can be a sql query
	* @param $moreAttributes array|string
	*/
	function drawDropDown($controlName, 
							$orgValue,
							$items, 
							$valueColumnName,
							$titleColumnName,
							$groupNameColumnName=null,
							$groupIdColumnName=null,
							$defaultValue=null,
							$defaultTitle=null,
							$moreAttributes=array())
	{
		if (is_string($items))
			$items=cmfcMySql::getRowsCustom($items);

		/*
		echo '<pre style="direction:ltr">';
		print_r($items);
		echo "</pre>";
		*/
		$moreAttributesStr=cmfcHtml::attributesToHtml($moreAttributes);
		
		$lastGroupName='';
		$firstRow=true;
		$html=sprintf('<select name="%s" %s >'."\n", $controlName, $moreAttributesStr);
		if (!is_null($defaultValue)) 
			$html.=sprintf('<option value="%s">%s</option>'."\n",$defaultValue, $defaultTitle);
		
		if (!is_array($orgValue)) {
			if($orgValue === NULL) {
				$orgValues = array();
			} else {
				$orgValues[] = $orgValue;
			}
		} else {
			$orgValues = $orgValue;
		}
		
		if(is_array($items)) // added by babak
		foreach ($items as $key=>$item) {
			
			if (is_array($item)) {
				$title=$item[$titleColumnName];
				$value=$item[$valueColumnName];
			} elseif (is_integer($key)) {
                $title=$item;
                $value=$item;
            } else {
				$title=$item;
                $value=$key;
			}

			if (!empty($groupNameColumnName)) {
				$groupName=$item[$groupNameColumnName];
				$groupId=$item[$groupIdColumnName];
				
				if ($lastGroupName==$groupName) {$groupChanged=false;} else {$groupChanged=true;}
				//echo "[$lastGroupName:$groupName]";
				if ($groupChanged==true) {
					if (!$firstRow) { $html.="</optgroup>\n"; }
					$html.=sprintf('<optgroup label="%s" title="%s">'."\n",$groupName, $groupId);
					$lastGroupName=$groupName;
				}
				$groupChanged=false;
			}
			$selected='';
			if (in_array($value, array_values($orgValues) ) ){
				$selected='selected="selected"';
			}
			else
				$selected = '';
			$html.=sprintf('<option value="%s" %s >%s</option>'."\n",
							$value, $selected, $title);
			$firstRow=false;
		}
		if (isset($groupName)) $html.="</optgroup>\n";
		$html.="</select>\n";
		return $html;
	}
	
	
	
	/**
	* draw html select control via array items or sql query
	* it can also draw items like hierarchical items
	* 
	* 'useKeyAsKeyAndValueAsValue' parameter is for solving an issue when
	* items keys are integer. the default behavior of function is it uses item value
	* for both value and title of the list items, by setting this parameter to true
	* it uses key for value and value for title
	* @example
	* <code>
	*	cmfcHtml::drawDropDownCustom(
	*		'controlName'=>'',
	*		'orgValue'=>'',
	*		'items'=>'',
	*		'valueColumnName'=>'',
	*		'titleColumnName'=>'',
	*		'levelColumnName'=>'',
	*		'groupNameColumnName'=>'',
	*		'groupIdColumnName'=>'',
	*		'defaultValue'=>'',
	*		'defaultTitle'=>'',
	*		'attributes'=>'',
	*		'selectType'=>'multiSelect',
	*		'isParentsSelectable'=>true
	* 		'attributes'=>array('style'=>'test'),
	* 		'useKeyAsKeyAndValueAsValue'=>true
	*		'interface'=>array(
	*			'itemIndent'=>'',
	*			'direction'=>'rightToLeft',
	*			'isIe'=>false,
	*		)
	*	)
	* </code>
	* @param $options array
	*/
	function drawDropDownCustom($options)
	{
		$controlName=$options['controlName'];
		$orgValue=$options['orgValue'];
		$items=$options['items']; 
		$valueColumnName=$options['valueColumnName'];
		$titleColumnName=$options['titleColumnName'];
		$levelColumnName=$options['levelColumnName'];
		$hasChildColumnName=$options['hasChildColumnName'];
		$groupNameColumnName=$options['groupNameColumnName'];
		$groupIdColumnName=$options['groupIdColumnName'];
		$defaultValue=$options['defaultValue'];
		$defaultTitle=$options['defaultTitle'];
		$moreAttributes=$options['attributes'];
		$useKeyAsKeyAndValueAsValue=$options['useKeyAsKeyAndValueAsValue'];
		
		if (is_string($items))
			$items=cmfcMySql::getRowsCustom($items);

			
		if ($options['selectType']=='multiSelect') {
			$moreAttributes['multiple']='multiple';
			$controlName.='[]';
		}
		
		/*
		echo '<pre style="direction:ltr">';
		print_r($items);
		echo "</pre>";
		*/
		$moreAttributesStr=cmfcHtml::attributesToHtml($moreAttributes);
		
		$lastGroupName='';
		$firstRow=true;

		$html=sprintf('<select name="%s" %s >'."\n", $controlName, $moreAttributesStr);
		if (!is_null($defaultValue)) {
			if ($options['disabledDefaultValue']==true) {
				$disabledHtml='disabled="disabled"';
			} else {
				$disabledHtml='';
			}
			$html.=sprintf('<option value="%s" %s>%s</option>'."\n",$defaultValue,$disabledHtml, $defaultTitle);
		}
		//$i = 0; //count the number of selected orgValues
		
		if (!is_array($orgValue))
			$orgValues[] = $orgValue;
		else
			$orgValues = $orgValue;
			
		if(is_array($items)) // added by babak
		foreach ($items as $key=>$item) {
			if (is_array($item)) {
				$title=$item[$titleColumnName];
				$value=$item[$valueColumnName];
			} elseif (is_integer($key) and !$useKeyAsKeyAndValueAsValue) {
				$title=$item;
				$value=$item;
			} else {
				$title=$item;
				$value=$key;
			}

			if (!empty($groupNameColumnName) && ($options['interface']['group'] or !isset($options['interface']['group']))) {
				$groupName=$item[$groupNameColumnName];
				$groupId=$item[$groupIdColumnName];
				
				if ($lastGroupName==$groupName) {$groupChanged=false;} else {$groupChanged=true;}
				//echo "[$lastGroupName:$groupName]";
				if ($groupChanged==true) {
					if (!$firstRow) { $html.="</optgroup>\n"; }
					$html.=sprintf('<optgroup label="%s" title="%s">'."\n",$groupName, $groupId);
					$lastGroupName=$groupName;
				}
				$groupChanged=false;
			}
			$selected='';
			if (in_array($value,$orgValues)) {
				$selected='selected="selected"';
				//$i++;
			} else {
				$selected = '';
			}
			
			$itemAttributes=array();
			#--(Begin)-->indent items to show them as a tree (hierarchical items)
			if (!empty($levelColumnName)) {
				if (empty($firstLevelNumber))
					$firstLevelNumber=$item[$levelColumnName];
					
				$depth=$item[$levelColumnName]-$firstLevelNumber;
				if (!isset($options['interface']['itemIndent'])) $options['interface']['itemIndent']=20;
				$itemIndent=intval($options['interface']['itemIndent']);
				if (!$options['interface']['isIe'])	 {
					$itemIndent=intval($options['interface']['itemIndent']);
					if ($options['interface']['direction']=='rightToLeft')
						$itemAttributes['style'].=';padding-right:'.($itemIndent*$depth).'px;';
					else
						$itemAttributes['style'].=';padding-left:'.($itemIndent*$depth).'px;';
				} else {
					$itemIndent=round($itemIndent/2)*$depth;
					$title=str_repeat('&nbsp;',$itemIndent).$title;
				}
				
				if (isset($options['isParentsSelectable'])) {
					$item['isParentsSelectable'] = $options['isParentsSelectable'];
				}
				
				if ($item[$hasChildColumnName]) {
					$itemAttributes['style'].=';font-weight:bold';
					if ($options['isParentsSelectable']!=true)
						$itemAttributes['disabled']=true;
				}
			}
			#--(End)-->indent items to show them as a tree (hierarchical items)
			$itemAttributes=cmfcHtml::attributesToHtml($itemAttributes);
			
			$html.=sprintf('<option value="%s" %s %s>%s</option>'."\n",
							$value, $selected, $itemAttributes,$title);
			$firstRow=false;
		}
		if (isset($groupName)) $html.="</optgroup>\n";
		$html.="</select>\n";
		return $html;
	}
	
	/**
	* @todo
	* 	- will be deleted in general lib 3
	* @param $datetime timestamp|datetime string
	* @param $dateType string : jalali or gregorian
	*/	
	function drawDateTimeDropDownBeta($fieldNamePrefix,$dateTime,$dateType='gregorian',$fieldsToShow=array('year','month','day'),$options=array()) {
		return cmfcHtml::drawDateTimeDropDown($fieldNamePrefix,$dateTime,$dateType,$fieldsToShow,$options);
	}
	
	
	/**
	* @todo
	* @param $datetime timestamp|datetime string
	* @param $dateType string : jalali or gregorian
	*/
	function drawDateTimeDropDown($fieldNamePrefix,$dateTime,$dateType='gregorian',$fieldsToShow=array('year','month','day'),$options=array())
	{
		
		if (!$options['autoAssignHtmlId']) $options['autoAssignHtmlId']=false;
		
		if (is_array($dateTime)) {
			if ($dateTime['type']==$dateType) {
			} elseif ($dateTime['type']=='jalali' and $dateType=='gregorian') {
				list($dateTime['year'],$dateTime['month'],$dateTime['day'])=cmfcJalaliDateTime::jalaliToGregorian($dateTime['year'],$dateTime['month'],$dateTime['day']);
			} elseif ($dateTime['type']=='gregorian' and $dateType=='jalali') {
				list($dateTime['year'],$dateTime['month'],$dateTime['day'])=cmfcJalaliDateTime::gregorianToJalali($dateTime['year'],$dateTime['month'],$dateTime['day']);
			}			
		}
		
		if (is_integer($dateTime)) {
			$dateTime=date('Y-m-d H:i:s',$dateTime);
		}
		
        if ($dateTime=='0000-00-00' or $dateTime=='0000-00-00 00:00:00')
            $dateTime=null;
        
		if (is_string($dateTime)) {
			$dateTime=strtotime($dateTime);
			if ($dateType=='jalali') $dateArray=explode('-',cmfcJalaliDateTime::get('Y-m-d',$dateTime,0));
				else $dateArray=explode('-',date('Y-m-d',$dateTime));
			$dateArray=array(
				'year'=>$dateArray[0],
				'month'=>$dateArray[1],
				'day'=>$dateArray[2]
			);

			$timeArray=explode(':',date('H:i:s',$dateTime));
			$timeArray=array(
				'hour'=>$timeArray[0],
				'minute'=>$timeArray[1],
				'second'=>$timeArray[2]
			);
			
			$dateTime=cmfcPhp4::array_merge($dateArray,$timeArray);
		}

		#--(Beigin)-->preaparing options
		if (is_string($options['yearRange'])) {
			$myValue=explode('-',$options['yearRange']);
			$options['yearRange']=range($myValue[0],$myValue[1]);
		}
		
		if (empty($options['yearRange']))
			if ($dateType == "gregorian")
				$options['yearRange']=range(1980,2020);
			else if ($dateType = 'jalali')
				$options['yearRange']=range(1370,1400);
			
		$options['yearRange']=array_combine(array_values($options['yearRange']),array_values($options['yearRange']));
		
		if (is_string($options['monthRange'])) {
			$myValue=explode('-',$options['monthRange']);
			$options['monthRange']=range($myValue[0],$myValue[1]);
		}
			
		if (empty($options['monthRange'])) {
			$options['monthRange']=range(1,12);

			foreach ($options['monthRange'] as $number) {
				if ($dateType=='jalali')
					$options['monthRange'][$number]=array('value'=>$number,'name'=>cmfcJalaliDateTime::getMonthName($number));
				else
					$options['monthRange'][$number]=array('value'=>$number,'name'=>cmfcGregorianDateTime::getMonthName($number));
			}
			unset($options['monthRange'][0]);
		}
		
		if (empty($options['dayRange'])){
			$options['dayRange']=range(1,31);
		}
		$options['dayRange']=array_combine(array_values($options['dayRange']),array_values($options['dayRange']));
		//unset($options['dayRange'][0]);
		
		if (empty($options['hourRange']))
			$options['hourRange']=range(0,23);
		
		$options['hourRange']=array_combine(array_values($options['hourRange']),array_values($options['hourRange']));
		//unset($options['hourRange'][0]);
		
		if (empty($options['minuteRange']))
			$options['minuteRange']=range(0,59);
		
		$options['minuteRange']=array_combine(array_values($options['minuteRange']),array_values($options['minuteRange']));
		//unset($options['minuteRange'][0]);
		
		if (empty($options['secondRange']))
			$options['secondRange']=range(0,59);
		$options['secondRange']=array_combine(array_values($options['secondRange']),array_values($options['secondRange']));	
		//unset($options['secondRange'][0]);
		#--(End)-->preaparing options
		$html='';
		foreach ($fieldsToShow as $fieldName) {
			if ($fieldName=='year') {
				$attributes=array('style'=>'direction:ltr','size'=>'1','class'=>'dateTimeDropDownYear');
				if ($options['autoAssignHtmlId']) $attributes['id']=$fieldNamePrefix."[year]";
				$html.=cmfcHtml::drawDropDown(
					$fieldNamePrefix."[year]", //name
					$dateTime['year'],//value
					$options['yearRange'],//items
					null,//$valueColumnName
					null,//$titleColumnName
					null,//$groupNameColumnName=null
					null,//$groupIdColumnName=null
					'',//$defaultValue=null
					'',//$defaultTitle=null
					$attributes 
				);
			}
			if ($fieldName=='month') {
				$attributes=array('style'=>'direction:ltr','size'=>'1','class'=>'dateTimeDropDownMonth');
				if ($options['autoAssignHtmlId']) $attributes['id']=$fieldNamePrefix."[month]";
				if ($dateType=='jalali') $attributes['style']='direction:rtl';
				$html.=cmfcHtml::drawDropDown(
					$fieldNamePrefix."[month]", //name
					$dateTime['month'],//value
					$options['monthRange'],//items
					'value',//$valueColumnName
					'name',//$titleColumnName
					null,//$groupNameColumnName=null
					null,//$groupIdColumnName=null
					'',//$defaultValue=null
					'',//$defaultTitle=null
					$attributes
				);
			}
			if ($fieldName=='day') {
				$attributes=array('style'=>'direction:ltr','size'=>'1','class'=>'dateTimeDropDownDay');
				if ($options['autoAssignHtmlId']) $attributes['id']=$fieldNamePrefix."[day]";
				$html.=cmfcHtml::drawDropDown(
					$fieldNamePrefix."[day]", 
					$dateTime['day'],//value
					$options['dayRange'],//items
					null,//$valueColumnName
					null,//$titleColumnName
					null,//$groupNameColumnName=null
					null,//$groupIdColumnName=null
					'',//$defaultValue=null
					'',//$defaultTitle=null
					$attributes 
				);
			}
			if ($fieldName=='hour') {
				$attributes=array('style'=>'direction:ltr','size'=>'1','class'=>'dateTimeDropDownHour');
				if ($options['autoAssignHtmlId']) $attributes['id']=$fieldNamePrefix."[hour]";
				$html.=cmfcHtml::drawDropDown(
					$fieldNamePrefix."[hour]", 
					$dateTime['hour'],//value
					$options['hourRange'],//items
					null,//$valueColumnName
					null,//$titleColumnName
					null,//$groupNameColumnName=null
					null,//$groupIdColumnName=null
					'',//$defaultValue=null
					'',//$defaultTitle=null
					$attributes
				);
			}
			if ($fieldName=='minute') {
				$attributes=array('style'=>'direction:ltr','size'=>'1','class'=>'dateTimeDropDownMinute');
				if ($options['autoAssignHtmlId']) $attributes['id']=$fieldNamePrefix."[minute]";
				$html.=cmfcHtml::drawDropDown(
					$fieldNamePrefix."[minute]", 
					$dateTime['minute'],//value
					$options['minuteRange'],//items
					null,//$valueColumnName
					null,//$titleColumnName
					null,//$groupNameColumnName=null
					null,//$groupIdColumnName=null
					'',//$defaultValue=null
					'',//$defaultTitle=null
					$attributes
				);
			}
			if ($fieldName=='second') {
				$attributes=array('style'=>'direction:ltr','size'=>'1','class'=>'dateTimeDropDownSecond');
				if ($options['autoAssignHtmlId']) $attributes['id']=$fieldNamePrefix."[second]";
				$html.=cmfcHtml::drawDropDown(
					$fieldNamePrefix."[second]", 
					$dateTime['second'],//value
					$options['secondRange'],//items
					null,//$valueColumnName
					null,//$titleColumnName
					null,//$groupNameColumnName=null
					null,//$groupIdColumnName=null
					'',//$defaultValue=null
					'',//$defaultTitle=null
					$attributes
				);
			}
		}	
		$html.='<input name="'.$fieldNamePrefix.'[type]" value="'.$dateType.'" type="hidden"/>';
		return $html;
	}
	
	
	/**
	* Convert PHP data structure to Javascript
	*/
	function phpToJavascript($var, $tabs = 0,$singleLine=false,$options=array()) {
		if (is_numeric($var)) {
			return $var;
		}
		
		if (is_bool($var)) {
			return ($var===true)?'true':'false';
		}

		if (is_string($var)) {
			return "'" . cmfcHtml::javascriptEncode($var) . "'";
		}

		if (is_array($var)) {
			$useObject = false;
			if ($options['alwaysUseObjectForArray']==true) {
				$useObject=true;
			}

			if ($singleLine==true) $newLineChar=''; else $newLineChar="\n";

			foreach(array_keys($var) as $k) {
				if(!is_numeric($k)) $useObject = true;
			}

			$js = array();
			foreach($var as $k => $v) {
				$i = "";
				if($useObject) {
					if(preg_match('#^[a-zA-Z]+[a-zA-Z0-9]*$#', $k)) {
						if (in_array(strtolower($k),array('function')))
							$k="'$k'";
						$i .= "$k: ";
					} else {
						$i .= "'$k': ";
					}
				}

				$i .= cmfcHtml::phpToJavascript($v, $tabs + 1,$singleLine,$options);
				$js[] = $i;
			}
			
			if ($singleLine) $tabs=0;
		
			if($useObject) {
				$ret = "{"."$newLineChar" . cmfcHtml::javascriptTabify(implode(",$newLineChar", $js), $tabs) . "$newLineChar"."}";
			} else {
				$ret = "[$newLineChar" . cmfcHtml::javascriptTabify(implode(",$newLineChar", $js), $tabs) . "$newLineChar]";
			}
			return $ret;
		}

		return 'null';
	}


	/**
	* Like htmlspecialchars() except for javascript strings.
	*/
	function javascriptEncode($string) {
		static $strings = "\\,\",',%,&,<,>,{,},@,\n,\r";
		
		if(!is_array($strings)) {
			$tr = array();
			foreach(explode(',', $strings) as $chr) {
				$tr[$chr] = sprintf('\x%02X', ord($chr));
			}
			$strings = $tr;
		}
		
		return strtr($string, $strings);
	}

	/**
	* Just space-tab indent some text
	*/
	function javascriptTabify($text, $tabs) {
		if($text) {
			return str_repeat("  ", $tabs) . preg_replace('/\n(.)/', "\n" . str_repeat("  ", $tabs) . "\$1", $text);
		}
	}
	
	
	/**
	* convert php value to javascript
	* @param variant $var
	* @param interger $tab //number of tab chars for indent
	* @param boolean $singleLine //result as single line
	*/
	function phpParamToJs($var,$tabs = 0,$singleLine=true) {
		//if (is_string($var)) return $var;
		return cmfcHtml::phpToJavascript($var,0,true);
	}
	
	/**
	* convert php values to javascript
	* @param variant $var
	*/
	function phpParamsToJs($vars) {
		foreach ($vars as $key=>$var) {
			$vars[$key]=cmfcHtml::phpParamToJs($var);
		}
		return $vars;
	}
	
	/**
	* fixing javascript incompatibility with number as array key
	* @todo : should be recursive
	* @param string $array
	*/
	function getJavascriptCompatibleArray($array) {
		$result=array();
		foreach ($array as $key=>$value) {
			$key="_$key";
			$result[$key]=$value;
		}
		
		return $result;
	}
	/**
	* @shortName getJsCAK
	*/
	function getJavascriptCompatibleArrayKey($key) {
		if (is_numeric($key)) $key="_$key";
		$key=cmfcHtml::phpParamToJs($key);
		return $key;
	}
	
	/**
	* short name version of getJavascriptCompatibleArrayKey()
	* @fullName getJavascriptCompatibleArrayKey
	*/
	function getJsCAK($key) {
		return cmfcHtml::getJavascriptCompatibleArrayKey($key);
	}
	
	
	/**
	* This article provides two functions for converting HTML color (like #AAED43) to three 
	* RGB values ($r = 170, $g = 237, $b = 67) and converting RGB values to HTML color.
	* First function, html2rgb recognizes HTML or CSS colors in format #(hex_red)(hex_green)(hex_blue), 
	* where hex_red, hex_green and hex_blue are 1 or 2-digit hex-representations of red, green or blue color components.
	* # character in the beginning can be omitted. Function returns array of three integers in range 
	* (0..255) or false when it fails to recognize color format. 
	* 
	* @link http://www.anyexample.com/programming/php/php_convert_rgb_from_to_html_hex_color.xml
	* @author Sina Salek (support for rgba)
	*/
	function html2rgb($color)
	{
	    if ($color[0] == '#')
	        $color = substr($color, 1);

	    if (strlen($color) == 6)
	        list($r, $g, $b) = array($color[0].$color[1],
	                                 $color[2].$color[3],
	                                 $color[4].$color[5]);
	                                 
	    elseif (strlen($color) == 8)
	        list($r, $g, $b, $a) = array($color[0].$color[1],
	                                 $color[2].$color[3],
	                                 $color[4].$color[5],
	                                 $color[6].$color[7]);
	    elseif (strlen($color) == 4)
	        list($r, $g, $b, $a) = array($color[0], $color[1], $color[2], $color[3]);
	    elseif (strlen($color) == 3)
	        list($r, $g, $b) = array($color[0], $color[1], $color[2]);
	    else
	        return false;
	        
	    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	    if (!is_null($a)) {
	        $a = hexdec($a);
	        return array($r, $g, $b, $a);
	    } else {
	        return array($r, $g, $b);
	    }
	}

	/**
	* Second function, rgb2html converts its arguments (r, g, b) to hexadecimal html-color 
	* string #RRGGBB Arguments are converted to integers and trimmed to 0..255 range. 
	* It is possible to call it with array argument rgb2html($array_of_three_ints) or
	* specifying each component value separetly rgb2html($r, $g, $b). 
	* 
	* 
	* @link http://www.anyexample.com/programming/php/php_convert_rgb_from_to_html_hex_color.xml
	*/

	function rgb2html($r, $g=-1, $b=-1)
	{
	    if (is_array($r) && sizeof($r) == 3)
	        list($r, $g, $b) = $r;

	    $r = intval($r); $g = intval($g);
	    $b = intval($b);

	    $r = dechex($r<0?0:($r>255?255:$r));
	    $g = dechex($g<0?0:($g>255?255:$g));
	    $b = dechex($b<0?0:($b>255?255:$b));

	    $color = (strlen($r) < 2?'0':'').$r;
	    $color .= (strlen($g) < 2?'0':'').$g;
	    $color .= (strlen($b) < 2?'0':'').$b;
	    return '#'.$color;
	}
	
	
	/**
	* convert 'row[1][columns][name]' to array('row','1','columns','name')
	* @param $name string
	* return array
	*/
	function fieldNameToArrayPath($name) {
		preg_match_all('/[^\\[\\]]+/', $name, $result, PREG_PATTERN_ORDER);
		return $result = $result[0];
	}
	
	/**
	* @example
	* <code>
	* 	fieldNamePrependBaseName('row[1][columns][name]','headName');
	* 	//result is 'headerName[row][1][columns][name]'
	* </code>
	* 
	* @param string $name
	* @param string $baseName
	* return array
	*/
	function fieldNamePrependBaseName($name,$baseName) {
		$name = preg_replace('/^([^\\[\\]]+)(.*)/', $baseName.'[$1]$2', $name);
		return $name;
	}
	
	
	/**
	* get 'row[1][columns][name]' as name
	* @param $name string
	* return array
	*/
	function getFieldNameHeadName($name) {
		if (preg_match('/\\[([^\\[\\]]+)\\]$/', $name, $regs)) {
			$name = $regs[1];
		}	
		return $name;
	}
	
	

	/**
	* gzip (compress) the html page
	* @notice calling this code before using this function is required
	* <code>
	* 	// At the beginning of each page call these two functions
	* 	ob_start();
	* 	ob_implicit_flush(0);
	* </code>
	* 
	* @return string
	* @previousNames print_gzipped_page, cmfPrintGzippedPage
	*/
	function printGzippedPage() {

	    global $HTTP_ACCEPT_ENCODING;
	    if( headers_sent() ){
	        $encoding = false;
	    }elseif( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ){
	        $encoding = 'x-gzip';
	    }elseif( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ){
	        $encoding = 'gzip';
	    }else{
	        $encoding = false;
	    }

	    if( $encoding ){
	        $contents = ob_get_contents();
	        ob_end_clean();
	        header('Content-Encoding: '.$encoding);
	        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
	        $size = strlen($contents);
	        $contents = gzcompress($contents, 9);
	        $contents = substr($contents, 0, $size);
	        print($contents);
	        exit();
	    }else{
	        ob_end_flush();
	        exit();
	    }
	}
	
	
	/**
	* merges $_FILES with $_POST for simplicity
	* WARNING : it has a bug when there is more than file field!!
	* @previousNames mergePhpFilesAndPhpPost,cmfMergePhpFilesAndPhpPost
	*/
	function mergePhpFilesAndPhpPost($phpFiles,$phpPost,$keepPostValue=false) {
	        $result=array();
	        foreach ($phpFiles as $baseName=>$fileInfo) {
	                foreach ($fileInfo as $infoName=>$info) {
	                        $result[$infoName][$baseName]=$info;
	                        $infoPath=cmfcArray::convertToSimilarFlatArray($result[$infoName]);
	                        $fileInfoValue=&cmfArrayPath($result[$infoName],$infoPath);
	                        //--(BEGIN)-->Merging

	                        $infoPath=cmfcArray::convertToSimilarFlatArray($result[$infoName]);
	                        //var_dump($infoPath);echo '<br/>';
	                        $fileInfoValue=&cmfcArray::Path($result[$infoName],$infoPath);

	                        $postInfoValue=&cmfArrayPath($phpPost,$infoPath,true);
	                        if ($keepPostValue) {
	                                $fileInfoValue['value']=$postInfoValue;
	                                $postInfoValue[$infoName]=$fileInfoValue;
	                        } else {
	                                $postInfoValue[$infoName]=$fileInfoValue;
	                        }
	                        //--(END)-->Meging
	                }
	        }
	        echo '<pre>';print_r($result);echo '</pre>';
	        return $phpPost;
	}

	/**
	* @previousNames cmfMakePhpFilesLikePhpPost,makePhpFilesLikePhpPost
	*/
	function makePhpFilesLikePhpPost($phpFiles) {
	        $result=array();
	        foreach ($phpFiles as $baseName=>$fileInfo) {
	                foreach ($fileInfo as $infoName=>$info) {
	                        $result[$infoName][$baseName]=$info;
	                }
	        }
	        return $result;
	}
}


define('CMF_OQS_WITH',true);
define('CMF_OQS_WITHOUT',false);
class cmfcUrl {
	/**
	* @desc exclude a query string variable and its value from REQUEST_URI
	* @todo should be compatible with XHTML (&amp character)
	* @param array('qsvn1','qsvn2')
	* @param string //get,requestUri,customGet,customUrl
	*/
	function excludeQueryStringVars($qsVars,$resourceType='get',$options=array()) {
		//([^\?]*)\?(([^&=]*)=([^&=]*)&?)*
		if ($resourceType=='request_uri' or $resourceType=='requestUri') {

			if ($options['requestUrl'])
				$url=$options['requestUrl'];
			else
				$url=$_SERVER['REQUEST_URI'];

			if (preg_match_all('/(([^&?=]*)=([^&=?]*)?)*/', $url, $regs,PREG_SET_ORDER)) {
				
				foreach ($qsVars as $qsVarKey=>$qsVarValue) {
					$found=false;
					foreach ($regs as $regKey=>$reg) {
						if (is_string($qsVarKey)) {
							if ($reg[2]==$qsVarKey) {
								$found=true;
								$url=str_replace($reg[1],$reg[2].'='.$qsVarValue,$url);
								//echo $reg[1].','.$reg[2].'='.$qsVarValue.'<br />';
								unset($regs[$regKey]);
								break;
							}
						} else {
							if ($reg[2]==$qsVarValue) {
								$url=str_replace('&'.$reg[1],'',$url);
								unset($regs[$regKey]);
								break;
							}
						}
					}
					if (is_string($qsVarKey) and !$found) {
						$separator='&';
						if (strpos($url,'=')===false) $separator='?';
                        if (strpos($url,'?')===false) $separator='?';
						$url=$url.$separator.$qsVarKey.'='.$qsVarValue;
					}
				}
				return $url;
			} else {
				return $url;
			}
		} elseif ($resourceType=='get') {
			$get=$_GET;
			foreach ($qsVars as $qsName=>$qsVar) {
				if (is_int($qsName)) {
					unset($get[$qsVar]);
				} else {
					$get[$qsName]=$qsVar;
				}
			}
			return cmfcUrl::arrayToQueryString($get);
			
		} elseif ($resourceType=='customGet') {
			$get=$options['get'];
			foreach ($qsVars as $qsVar) {
				unset($get[$qsVar]);
			}
			return cmfcUrl::arrayToQueryString($get);
		}
		
		trigger_error('$resourceType parameter only accept ("get","request_uri") but you entered "'.$resourceType.'"',E_USER_ERROR);
	}
	
	/**
	* Removes duplicate and incorrect characters in url
	* Converts this :
	* <code>
	* http://sadfasdf.com///dsfas//?asdfa&&&sdfa=asd&
	* </code>
	* To this : 
	* <code>
	* http://sadfasdf.com/dsfas/?asdfa&sdfa=asd
	* </code>
	*/
	function normalize($url) {
		//$result = preg_replace('%(?<!:/|[^/])/|(?<=&)&|&$|%', '', $url);
		$result=str_replace(array('://','//',':::'),array(':::','/','://'),$url);
		return $result;
	}
	
	
	/**
	* @previousNames : glArrayToQueryString , cmfcArrayToQueryString
	*/
	function arrayToQueryString($varArray,$mainName=null) {
		if (is_array($varArray)) {
			$result='';
			foreach ($varArray as $name=>$value) {
				//if (!empty($parentName)) {$name=$parentName.'['.$name.']';}
				if (!is_array($value)) {
					$value=urlencode($value);
					if (empty($mainName))
						$result.="$name=$value&";
					else
						$result.="$mainName"."[$name]=$value&";
				} else {
					if (!empty($mainName))
						$name=$mainName.'['.$name.']';
					$result.=cmfcUrl::arrayToQueryString($value,$name);
				}
			}
		}
		return $result;
	}
	
	/**
	* @previousNames : get_page_url , cmfGetPageUrl
	*/
	function getPageUrl($page_name) {
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		return "http://$host$uri/$page_name";
	}
	

	/**
	* @previousNames : is_valid_url , cmfIsValidUrl
	*/
	function isValid($url) {
		if (preg_match ('/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $url))
			return true;
		else
			return false;
	}
	
	

	
	/**
	* @desc generate query string depends on $_GET and can filter some query vars with $qs_vars.
	* 		example for OQS_WITHOUT :
	*	 	$qs_vars=array('language','command','name');
	* 		cmfObtainQueryString($qs_vars,OQS_WITHOUT);
	*
	* @previousNames : obtain_query_string , cmfObtainQueryString
	*
	* @param array $qs_vars //$qs_vars['page_number']='1' <-just string
	* @param const:boolean $type
	* @param array:string $query_string
	* @return string
	*/
	function obtainQueryString($qs_vars=null,$type=CMFC_OQS_WITHOUT,$query_string=null,$result_as_array=false)
	{
			if (is_null($query_string)) {$query_string=$_GET;}
			if (is_null($type)) {$type=CMFC_OQS_WITHOUT;}
			$query_string=cmfQueryStringToArray($query_string);
			$query_string=cmfcPhp4::array_merge($query_string,$qs_vars);
			$str_query='';
			foreach ($query_string as $var=>$value)
			{
					if ($value!==CMFC_OQS_WITH and $value!==CMFC_OQS_WITHOUT)
					{
							if ($value!='' and $value!=null)
							{
									if ($result_as_array===false)
									{
											//--(BEGIN)--> this is just a trick to get rid of array items, in fact i should find the way to convert array items to original shape 'column[test]=val'
											if (is_array($value)) {$value='';}
											//--(END)--> this is just a trick to get rid of array items, in fact i should find the way to convert array items to original shape 'column[test]=val'
											$value=urlencode($value);
											$var=urlencode($var);
											if ($str_query!='') { $str_query.='&amp;'; }
											$str_query.="$var=$value";
									} else {
											$str_query[$var]=$value;
									}
							}
					}
			}
			return  $str_query;
	/*
			$str_query='';
			foreach ($query_string as $var=>$value)
			{
					$found=false;
					$qs_type=$type;
					if (!empty($qs_vars))
					{
							if (key_exists($var,$qs_vars))
							{
									$found=true;
									$qs_value=$qs_vars[$var];
									if ($qs_value===OQS_WITH or $qs_value===OQS_WITHOUT) {$qs_type=$qs_value;}
							}
					}
	
					if (($found==false and $qs_type==OQS_WITHOUT) or ($found==true and $qs_type==OQS_WITH))
					{
							if (isset($qs_value))
									if ($qs_value!==OQS_WITH and $qs_value!==OQS_WITHOUT) {$value=$qs_value;}
							if ($str_query!='') { $str_query.='&'; }
							$str_query.="$var=$value";
					}
			}
	
			foreach ($qs_vars as $var=>$value)
			{
					if (!key_exists($var,$query_string) and $value!==OQS_WITH and $value!==OQS_WITHOUT)
					{
							$query_string[$var]=$value;
							if ($str_query!='') { $str_query.='&'; }
							$str_query.="$var=$value";
					}
			}
	
			return  $str_query;
	*/
	}
	
	
	/**
	* @previousNames : encrypt_query_string , cmfEncryptQueryString
	*/
	function encryptQueryString($qs,$key='biaberimmadreseh',$name='eqs')
	{
			//return $name.'='.cmfEncrypt($qs,$key);
			//-->(NOTICE)-->maybe it will more secure        if i add somthing at the begining and at the end of the encoded value and then remove them before deconfing!
			return $name.'='.urlencode(base64_encode($qs));
	}
	
	
	
	/**
	* @previousNames : decrypt_query_string , cmfDecryptQueryString
	*/
	function decryptQueryString($qs,$key='biaberimmadreseh',$name='eqs')
	{
			//return cmfDecrypt($qs,$key);
			return base64_decode(urldecode($qs));
	}
	
	/**
	* @previousNames : query_string_to_array , cmfQueryStringToArray
	*/
	function queryStringToArray($query_string)
	{
			if (is_string($query_string))
			{
					if (preg_match_all('/(([^=&?]+)=([^=&?]+))+/is',$query_string,$matches,PREG_SET_ORDER))
					{
							$result=array();
							foreach ($matches as $item)
							{
									$var=$item[2];
									$value=$item[3];
									if (!empty($var) or $var==0)
									{
											$result[$var]=$value;
									}
							}
							return $result;
					}
			} else {return $query_string;}
	}
	
	

	/**
	* @desc cmfQuesryStringToHiddenFields('key1=1&key2=2&key3=5');
	* 		[or] cmfQuesryStringToHiddenFields(array('key1'=>'1','key2'=>'2','key3'=>'3'));
	* 		result:
	* 		<input type=\"hidden\" name=\"key1\" value=\"1\"/>\n
	* 		<input type=\"hidden\" name=\"key2\" value=\"2\"/>\n
	* 		<input type=\"hidden\" name=\"key3\" value=\"3\"/>\n
	*
	* @previousNames : glQuesryStringToHiddenFields , cmfQuesryStringToHiddenFields , cmfArrayToHiddenFields , array_to_hidden_fields
	*
	* @param string|array $query_string
	* @return string
	*/
	function quesryStringToHiddenFields($query_string)
	{
			$result='';
			if (is_string($query_string))
			{
					if (preg_match_all('/((([^?&]*)=([^?&]*)))/', $query_string,$arr))
					{
							$query_string=array();
							foreach ($arr[3] as $i=>$key)
							{
									$value=$arr[4][$i];
									$query_string[$key]=$value;
							}
					}
			}
	
			if (is_array($query_string))
			{
					foreach ($query_string as $key=>$value)
					{
							//$key=urldecode($key);
							//--(BEGIN)--> this is just a trick to get rid of array items, in fact i should find the way to convert array items to original shape 'column[test]=val'
							if (is_array($value)) {$value='';}
							//--(END)--> this is just a trick to get rid of array items, in fact i should find the way to convert array items to original shape 'column[test]=val'
							//$value=urldecode($value);
							$value=htmlspecialchars($value);
							$result.="<input type=\"hidden\" name=\"$key\" value=\"$value\"/>\n";
					}
			}
			return $result;
	}
	
	/**
	* call a url but don't wait for the result
	* @param string $url //http://test.com/?id=5
	*/
	function callUrl($url) {
		if (preg_match('/http:\/\/([^\/]+)(.*)/i', $url, $regs)) {
			$host = $regs[1];
			$path = $regs[2];
			
			$fp=fsockopen($host,80);
			$header = "GET ".$path." HTTP/1.1\r\n";
			$header .= "Host: ".$host."\r\n";
			$header .= "Connection: Close\r\n\r\n";
			fputs($fp,$header);
			return $fp;
		}
		return false;
	}

}


class cmfcDirectory {
	/**
	* remove the whole directory what everything inside it
	* @previousNames remove_dir, removeDir , cmfcFile::removeDir
	*/
	function remove($dir)
	{
		$handle = opendir($dir);
		while (false!==($item = readdir($handle))) {
			if($item != '.' && $item != '..') {
				if(is_dir($dir.'/'.$item)) {
					cmfcDirectory::remove($dir.'/'.$item);
				} else {
					unlink($dir.'/'.$item);
				}
			}
		}
		closedir($handle);
		if(rmdir($dir)) {
			$success = true;
		}
		return $success;
	}
	
	/**
	* $condition=array('modifiedDateTime','>',mktime)
	* 
	* @dir string
	* @condition array
	* @return boolean
	*/
	function removeSubFoldersByDateTime($dir,$condition=array()) {
	}

	/**
	* @todo
	* 	- should be remove in favor of normalizePath as of generallib 3
	*/
	function normilizePath($path) {
		trigger_error('cmfcDirectory::normilizePath is departed , please replace it with cmfcDirectory::normalizePath ASAP',E_USER_WARNING);
		return cmfcDirectory::normalizePath($path);
	}
	
	/**
	 * Replaces // , \\ , / , etc with correct path
	 * @changelog
	 * 	- Supports windows
	 * @param $path
	 * @return unknown_type
	 */
	function normalizePath($path) {
	    $val=str_replace(array('///','//','\\','\\\\',"\n","\r"),'/',$path);
	    $val=str_replace(array("\n","\r"),'',$val);
	    if (PHP_OS=='WINNT') {
	    	$val=preg_replace('/\/+([a-zA-Z]{1}:)/','$1',$val);
		}
	    return $val;
	    //return $path = ereg_replace('(\\\\*)|(\/*/)', '/', $path);  //only forward-slash
	}


	/**
	* Create a new directory, and the whole path.
	*
	* If  the  parent  directory  does  not exists, we will create it,
	* etc.
	* @todo
	* 	- PHP5 mkdir functoin supports recursive, it should be used
	* @author baldurien at club-internet dot fr 
	* @param string the directory to create
	* @param int the mode to apply on the directory
	* @return bool return true on success, false else
	* @previousNames mkdirs
	*/

	function makeAll($dir, $mode = 0777, $recursive = true) {
		if( is_null($dir) || $dir === "" ){
			return FALSE;
		}
		
		if( is_dir($dir) || $dir === "/" ){
			return TRUE;
		}
		if( cmfcDirectory::makeAll(dirname($dir), $mode, $recursive) ){
			return mkdir($dir, $mode);
		}
		return FALSE;
		
		# without callback algoritm, this algoritm may have some bugs
		//$path = preg_replace('/\\\\*|\/*/', '/', $path);  //only forward-slash
		/*
		$dirs=array();
		$dirs=explode("/",$path);
		$path="";
		foreach ($dirs as $element) {
		    $path.=$element."/";
		    if(!is_dir($path) and strpos(':',$path)===false) { 
		        if(!mkdir($path) and !is_file($path)){ 
		        	//echo something
		        }
		    }   
		}
		return true;
		*/
	}
	
	/**
	* @previousNames scandir_recursive
	*/
	function scanRecursive($path) {
	    if (!is_dir($path)) return 0;
	    $list=array();
	    $directory = @opendir("$path"); // @-no error display
	    while ($file= @readdir($directory))  {
	        if (($file<>".")&&($file<>".."))  {
	            $f=$path."/".$file;
	            $f=preg_replace('/(\/){2,}/','/',$f); //replace double slashes
	            if(is_file($f)) $list[]=$f;
	            if(is_dir($f))
	            $list = cmfcPhp4::array_merge($list ,cmfcDirectory::scanRecursive($f));   //RECURSIVE CALL
	        }    
	    }
	    @closedir($directory);  
	    return $list ;
	}
	

	/**
	* -1.first one is parent of second one
	* 0.they don't have any direct relation
	* 1.second one is parent of first one
	* 2.they are equal
	*/
	function areTwoPathRelated($pathOne, $pathTwo) {
	    if (!empty($pathOne) and !empty($pathTwo)) {
	        if ($pathTwo==$pathOne) return 2;
	        if (strpos($pathTwo,$pathOne)!==false) return -1;
	        if (strpos($pathOne,$pathTwo)!==false) return 1;
	        return 0;
	    }
	}
	
	/**
	 * Copy file or folder from source to destination, it can do
	 * recursive copy as well and is very smart
	 * It recursively creates the dest file or directory path if there weren't exists
	 * Situtaions :
	 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
	 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
	 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest      
	 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
	 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
	 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
	 * @todo
	 * 	- Should have rollback technique so it can undo the copy when it wasn't successful
	 *  - Auto destination technique should be possible to turn off
	 *  - Supporting callback function
	 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask 
	 * @param $source //file or folder
	 * @param $dest ///file or folder
	 * @param $options //folderPermission,filePermission
	 * @return boolean
	 */
	function copy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
	{
		$result=false;
		
		if (is_file($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if (!file_exists($dest)) {
					cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
				}
				$__dest=$dest."/".basename($source);
			} else {
				$__dest=$dest;
			}
			$result=copy($source, $__dest);
			chmod($__dest,$options['filePermission']);
			
		} elseif(is_dir($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if ($source[strlen($source)-1]=='/') {
					//Copy only contents
				} else {
					//Change parent itself and its contents
					$dest=$dest.basename($source);
					@mkdir($dest);
					chmod($dest,$options['filePermission']);
				}
			} else {
				if ($source[strlen($source)-1]=='/') {
					//Copy parent directory with new name and all its content
					@mkdir($dest,$options['folderPermission']);
					chmod($dest,$options['filePermission']);
				} else {
					//Copy parent directory with new name and all its content
					@mkdir($dest,$options['folderPermission']);
					chmod($dest,$options['filePermission']);
				}
			}

			$dirHandle=opendir($source);
			while($file=readdir($dirHandle))
			{
				if($file!="." && $file!="..")
				{
					if(!is_dir($source."/".$file)) {
						$__dest=$dest."/".$file;
					} else {
						$__dest=$dest."/".$file;
					}
					//echo "$source/$file ||| $__dest<br />";
					$result=cmfcDirectory::copy($source."/".$file, $__dest, $options);
				}
			}
			closedir($dirHandle);
			
		} else {
			$result=false;
		}
		return $result;
	}
	
	
	
	function mergeNewPath($newPath,$pathList) {
	    foreach ($pathList as $key=>$path) {
	        $compareResult=cmfcDirectory::areTwoPathRelated($newPath,$path);
	        if ($compareResult==-1) {
	            $pathList[$key]=$newPath;
	        }
	        if ($compareResult==-1 or $compareResult==2 or $compareResult==1) {
	            $merged=true;
	        }
	    }
	    if ($merged!=true) $pathList[]=$newPath; 
	    return $pathList;
	}
	
	
	/**
	* delete everything in the directory recursively
	* @param $mode string //all,onlyFiles
	* @paran $filter array() //file or dir or path regular expression. array('file'=>'','directory'=>'','path'=>'') use ! "!/.+/is" to make it negative
	* @previousNames clear_dir, cmfcFile::cleanDir
	*/
	function clear($dir,$mode='all',$filter=null) {
		$handle = opendir($dir);
		while (false!==($item = readdir($handle))) {
			if($item != '.' && $item != '..') {
				$delete=false;
				if(is_dir($dir.'/'.$item)) {
					cmfcDirectory::clear($dir.'/'.$item,$mode,$filter);
					if ($mode=='all') {
						$delete=true;
					}
				} else {
					$delete=true;
				}
				
				if (!is_null($filter)) {
					if ($delete) {
						if (isset($filter['path']))  {
							$r=preg_match($filter['path'],$dir.'/'.$item);
							if ($filter['path'][0]=='!') {
								unset($filter['path'][0]);
								$r=($r==true)?false:true;
							}
							if (!$r) {
								$delete=false;
							}
						}
					}
					if ($delete and is_dir($dir.'/'.$item)) {
						if (isset($filter['directory']))  {
							$r=preg_match($filter['directory'],$item);
							if ($filter['directory'][0]=='!') {
								unset($filter['directory'][0]);
								$r=($r==true)?false:true;
							}
							if (!$r) {
								$delete=false;
							}
						}
					} else {
						if (isset($filter['file']))  {
							$r=preg_match($filter['file'],$item);
							if ($filter['file'][0]=='!') {
								unset($filter['file'][0]);
								$r=($r==true)?false:true;
							}
							if (!$r) {
								$delete=false;
							}
						}
					}
				}
				
				if ($delete==true) {
					unlink($dir.'/'.$item);
				}
			}
		}
		closedir($handle);
		$success = true;
		return $success;
	}
	
	
	
	/**
	* Here's a function that will recrusively turn a directory into a hash of 
	* directory hashes and file arrays, automatically ignoring "dot" files. 
	* 
	* <code>
	* 	$public_html["StudentFiles"] = array();
	* 	hashify_directory("StudentFiles/", $public_html["StudentFiles"]);
	* </code>
	* on the directory structure:
	* -./StudentFiles/tutorial_01/case1/file1.html
	* -./StudentFiles/tutorial_01/case1/file2.html
	* -./StudentFiles/tutorial_02/case1/file1.html
	* -./StudentFiles/tutorial_02/case2/file2.html
	* -./StudentFiles/tutorial_03/case1/file2.html
	* etc...
	* becomes:
	* <code>
	* 	print_r($public_html);
	* </code>
	* outputs:
	* <code>
	* array(
	*  "StudentFiles" => array (
	*        "tutorial_01" => array (
	*              "case1" => array( "file1.html", "file2.html")
	*         ),
	*         "tutorial_02" => array (
	*               "case1" => array( "file1.html"),
	*               "case2" => array( "file2.html")
	*         ),
	*        "tutorial_03" => array (
	*               "case1" => array( "file2.html")
	*         )
	*   )
	* )
	* </code>
	* @previousNames 
	*/
	
	function getContentsAsMultiLevelArray($topdir, &$list, $ignoredDirectories=array()) {
	    if (is_dir($topdir)) {
	        if ($dh = opendir($topdir)) {
	            while (($file = readdir($dh)) !== false) {
	                if (!(array_search($file,$ignoredDirectories) > -1) && preg_match('/^\./', $file) == 0) {
	                    if (is_dir("$topdir$file")) {
	                        if(!isset($list[$file])) {
	                            $list[$file] = array();
	                        }
	                        ksort($list);
	                        cmfcDirectory::getContentsAsMultiLevelArray("$topdir$file/", $list[$file]);
	                    } else {
	                        array_push($list, $file);
	                    }
	                }
	            }
	            closedir($dh);
	        }
	    }
	}
	
	
	
	/**
	* The below function will list all folders and files within a directory
	* It is a recursive function that uses a global array.  The global array was the easiest
	* way for me to work with an array in a recursive function
	* This function has no limit on the number of levels down you can search.
	* The array structure was one that worked for me.
	* ARGUMENTS:
	* $startdir => specify the directory to start from; format: must end in a "/"
	* $searchSubdirs => True/false; True if you want to search subdirectories
	* $directoriesonly => True/false; True if you want to only return directories
	* $maxlevel => "all" or a number; specifes the number of directories down that you want to search
	* $level => integer; directory level that the function is currently searching
	* 
	* <code>
	* $files = filelist("./",1,1); // call the function
	* foreach ($files as $list) {//print array
	*    echo "Directory: " . $list['dir'] . " => Level: " . $list['level'] . " => Name: " . 
	* 			$list['name'] . " => Path: " . $list['path'] ."<br>";
	* }
	* </code>
	* <code>
	* $options=array(
	* 	'ignoredDirectoryNames'=>array('.svn'),
	* 	'ignoredDirectories'=>array(
	* 		$sourcePath.'/beta',
	* 		$sourcePath.'/develop',
	* 		$sourcePath.'/discontinued',
	* 		$sourcePath.'/info',
	* 		$sourcePath.'/test',
	* 		$sourcePath.'/dependencies'
	* 	),
	* 	'onlyFiles'=>true
	* );
	* </code>
	* @previousNames directoryAsSingleLevelArray
	*/
	function getContentsAsSingleLevelArray ($startdir="./", $searchSubdirs=1, $directoriesonly=0, $maxlevel="all", $level=1,$options=array(),$directorylist=array()) {
		if ($startdir[strlen($startdir)-1]!=DIRECTORY_SEPARATOR) {
			$startdir.=DIRECTORY_SEPARATOR;
		}
		if ((PHP_OS=='WINNT' or PHP_OS=='Windows' or PHP_OS=='WIN32') and $_SERVER['CLIENTNAME']=='Console') {
			if (!isset($options['filesListStr'])) {
				$options['getModifyTimeDirectly']=true;
				//http://codesnippets.joyent.com/posts/show/80
				$cmd='dir /s /a:-d /o:-d /t:c "'.$startdir.'"';
				$options['filesListStr']=cmfcShell::windExecCustom($cmd,array('waitForResult'=>true,'runInBackground'=>true),array('temporaryDir'=>''));
				//file_put_contents('fd.txt',$options['filesListStr']);
			}
		}

		//list the directory/file names that you want to ignore
		$ignoredDirectoryNames=$options['ignoredDirectoryNames'];
		$ignoredDirectoryNames[] = ".";
		$ignoredDirectoryNames[] = "..";
		$ignoredDirectoryNames[] = "_vti_cnf";
		
		$ignoredDirectories=$options['ignoredDirectories'];
		if (empty($ignoredDirectories)) {
			$ignoredDirectories=array();
		}

		if (is_dir($startdir)) {
	        if ($dh = opendir($startdir)) {
				while (($file = readdir($dh)) !== false) {
					if (!(array_search($file,$ignoredDirectoryNames) > -1) and !(array_search($startdir.$file,$ignoredDirectories) > -1)) {
						if (filetype($startdir . $file) == 'dir') {
							//build your directory array however you choose;
							//add other file details that you want.
							if ($options['onlyFiles']!=true) {
								$directorylist[$startdir . $file]['level'] = $level;
								$directorylist[$startdir . $file]['dir'] = 1;
								$directorylist[$startdir . $file]['name'] = $file;
								$directorylist[$startdir . $file]['path'] = $startdir;
								if ($options['fastModeEnabled']!=true) {
									$directorylist[$startdir . $file]['modificationTime'] = filemtime($startdir.$file);
									$directorylist[$startdir . $file]['lastAccessTime'] = fileatime($startdir.$file);
									$directorylist[$startdir . $file]['lastChangeTime'] = filectime($startdir.$file);
									$directorylist[$startdir . $file]['permissions'] = fileperms($startdir.$file);
								}
							}
							if ($searchSubdirs) {
								if ((($maxlevel) == "all") or ($maxlevel > $level)) {  
								    $list2 = cmfcDirectory::getContentsAsSingleLevelArray($startdir . $file . DIRECTORY_SEPARATOR, $searchSubdirs, $directoriesonly, $maxlevel, $level + 1,$options,&$directorylist);
								    if(is_array($list2)) {
								        $directorylist = cmfcPhp4::array_merge($directorylist, $list2);
								    }
								}
							}
						} else {
							if (!$directoriesonly) {
								//if you want to include files; build your file array 
								//however you choose; add other file details that you want.
								$directorylist[$startdir . $file]['level'] = $level;
								$directorylist[$startdir . $file]['dir'] = 0;
								$directorylist[$startdir . $file]['name'] = $file;
								$directorylist[$startdir . $file]['path'] = $startdir;
								if ($options['fastModeEnabled']!=true) {
									$directorylist[$startdir . $file]['modificationTime'] = filemtime($startdir.$file);
									$directorylist[$startdir . $file]['lastAccessTime'] = fileatime($startdir.$file);
									$directorylist[$startdir . $file]['lastChangeTime'] = filectime($startdir.$file);
									$directorylist[$startdir . $file]['permissions'] = fileperms($startdir.$file);
									$directorylist[$startdir . $file]['size'] = filesize ($startdir.$file);

									if ($options['getModifyTimeDirectly']==true) {
										$dirName=preg_quote($startdir);
										$fileName=preg_quote($file);
										if (preg_match('%Directory of '.$dirName.'?[\s]+(([0-9/]+  [0-9/:]+ [AMP]+) +[0-9,]+ +[^\n]+\s+)+%', $options['filesListStr'], $regs)) {
											if (preg_match('%([0-9/]+  [0-9/:]+ [AMP]+) +[0-9,]+ +'.$fileName.'%', $regs[0], $regs2)) {
												$directorylist[$startdir . $file]['creationTime']=strtotime($regs2[1]);
												//echo '<pre>';print_r($regs[1]);echo '</pre>';
											}
										}
									}
								}
							}
						}
					}
				}
				closedir($dh);
			}
		}
		return($directorylist);
	}
	
	/**
	* The below function will list all folders and files within a directory
	* It is a recursive function that uses a global array.  The global array was the easiest
	* way for me to work with an array in a recursive function
	* This function has no limit on the number of levels down you can search. 
	* <code>
	* $files = filelist("./",1,1); // call the function
	* foreach ($files as $list) {//print array
	*    echo "Directory: " . $list['dir'] . " => Level: " . $list['level'] . " => Name: " . 
	* 			$list['name'] . " => Path: " . $list['path'] ."<br>";
	* }
	* </code>
	* <code>
	* $options=array(
	* 	'startDirectory' => '/root';//specify the directory to start from; format: must end in a "/"
	* 	'includeInResult'=> array('file','folder') //the name is clear enough
	* 	'maxLevel' => "all" , //or a number; specifes the number of directories down that you want to search
	* 	'detailedResultsEnabled'=>true //Each result item contains details of the file or folder like date, type, path etc
	* 	'resultRelativeToStartDirectoryEnabled'=>false,
	* 	'startDirectoryOriginal'=>'',//Above option base of relative path, if not set startDirectory will be used
	* 	'level' => 1, //directory level that the function is currently searching
	* 	'fastModeEnabled'=> true //When enabled, package won't return extra information about folders
	* 	'correctModificationTime'=>true, //Iin windows php can not give the correct time
	* 	'correctCreationTime'=>true, //Iin windows php can not give the correct time
	* 	'whitelist'=>array(//if defined only files passed the whitelist will be included in the result
	* 		'*.txt'=>array('file','name','wildcard'),
	* 		'/name[^.]+\.txt/i'=>array('file','name','regex')
	* 	),
	* 	'blackList'=>array(//Blacklist is superior to whitelist
	* 		array('.svn'),
	* 		'd:\\test'=>array('path','folder'),
	*		'd:\\test2\\b.txt'=>array('path','file'),
	* 		'b.txt'=>array('name','file'),
	* 	),
	* 	'whiteListResult'=>array(), //list whitelist but does not prevent traversing and only filters result
	* 	'blackListResult'=>array() //list whitelist but does not prevent traversing and only filters result 
	* );
	* </code>
	* @changelog
	* - regex support
	* - Correct wrong file time in windows
	* - supports blackList and whiteList as replacement for ignore list
	* - configurable default ignore list , and ignore list should be platform specific
	* - should automatically append trailing slash if pathes does not have it
	* - should be platform independent, which means it does not matter if provide pathes use / or \
	* @todo
	* 	- support for filter result, currently if once wants to get only specific subdirectory
	* 		he can't becuase while list does not allow it to pass parent directories  
	* 	- support for filtering via modify,create time or metatag or even permissions	
	* 	- support for smart * in wild card, ** for recursive and * for non recursive 	 	 	
	* 	- support for xml output
	* @previousNames directoryAsSingleLevelArray
	*/
	function getContentsAsSingleLevelArray2 ($options,$directorylist=array()) {		
		#--(Begin)-->Default global black list
		if (!function_exists('______isAcceptable')) {
			function ______isAcceptable($filePath,$blackList,$whiteList,$type) {
				if (is_null($type) and file_exists($filePath)) {
					$pathParts = pathinfo($filePath);
					if (is_dir($filePath)) {
						$type='folder'; 
					} else {
						$type='file';
					}
					$fileName=$pathParts['basename'];
					$fileFolderPath=dirname($filePath);
					//echo $type;var_dump($pathParts);exit;
				}
				
				$isWhiteListed=false;
				$hasWhiteList=false;
				if (is_array($whiteList) and !empty($whiteList)) {
					$hasWhiteList=true;
					$isWhiteListed=false;
					$loopResult=&$isWhiteListed;
					$items=&$whiteList;
					//This loop is the same betwen black and whitelist
					foreach($items as $itemKey=>$itemValue) {
						if (is_string($itemValue)) {
							if ($fileName==$itemValue) {
								$loopResult=true;
								break;
							}
						} elseif(is_array($itemValue)) {
							$__key=$__value=null;
							$__itemIsPath=in_array('path', $itemValue);
							$__itemIsFolder=in_array('folder', $itemValue);
							$__itemIsFile=in_array('file', $itemValue);
							$__itemIsName=in_array('name', $itemValue);
							$__itemIsWildcard=in_array('wildcard', $itemValue);
							$__itemIsRegex=in_array('regex', $itemValue);
							if (!$__itemIsPath) {
								$__itemIsName=true;
							}
							if (!$__itemIsWildcard and !$__itemIsRegex) {
								$__itemIsEqual=true;
							}
							if ($__itemIsPath and ((!$__itemIsFolder and !$__itemIsFile) or ($__itemIsFolder and $type=='folder') or ($__itemIsFile and $type=='file'))) {
								$__key=$itemKey;
								$__value=$filePath;
							}
							if ($__itemIsName and ((!$__itemIsFolder and !$__itemIsFile) or ($__itemIsFolder and $type=='folder') or ($__itemIsFile and $type=='file')) or ($__itemIsFolder and $type=='file')) {
								$__key=$itemKey;
								$__value=$fileName;
							}
							
							if (!empty($__value) and !empty($__key)) {
								if ($__itemIsRegex) {
									if (preg_match($__key,$__value)) {
										$loopResult=true;
										break;										
									}
								} elseif ($__itemIsWildcard) {
									if (fnmatch($__key,$__value)) {
										$loopResult=true;
										break;										
									}
								} elseif ($__itemIsEqual) {								
									if ($__key==$__value) {
										$loopResult=true;
										break;
									}
								}
							}
						}
					}
				}
				
				$isBlackListed=false;
				$hasBlackList=false;

				if (is_array($blackList) and !empty($blackList)) {
					$isBlackListed=false;
					$hasBlackList=true;
					$loopResult=&$isBlackListed;
					$items=&$blackList;
					//This loop is the same betwen black and whitelist
					foreach($items as $itemKey=>$itemValue) {						
						if (is_string($itemValue)) {
							if ($fileName==$itemValue) {
								$loopResult=true;
								break;
							}
						} elseif(is_array($itemValue)) {
							$__key=$__value=null;
							$__itemIsPath=in_array('path', $itemValue);
							$__itemIsFolder=in_array('folder', $itemValue);
							$__itemIsFile=in_array('file', $itemValue);
							$__itemIsName=in_array('name', $itemValue);
							$__itemIsWildcard=in_array('wildcard', $itemValue);
							$__itemIsRegex=in_array('regex', $itemValue);
							if (!$__itemIsPath) {
								$__itemIsName=true;
							}
							if (!$__itemIsWildcard and !$__itemIsRegex) {
								$__itemIsEqual=true;
							}
							if ($__itemIsPath and ((!$__itemIsFolder and !$__itemIsFile) or ($__itemIsFolder and $type=='folder') or ($__itemIsFile and $type=='file'))) {
								$__key=$itemKey;
								$__value=$filePath;
							}
							if ($__itemIsName and ((!$__itemIsFolder and !$__itemIsFile) or ($__itemIsFolder and $type=='folder') or ($__itemIsFile and $type=='file')) or ($__itemIsFolder and $type=='file')) {
								$__key=$itemKey;
								$__value=$fileName;
							}
							
							if (!empty($__value) and !empty($__key)) {
								if ($__itemIsRegex) {
									if (preg_match($__key,$__value)) {
										$loopResult=true;
										break;										
									}
								} elseif ($__itemIsWildcard) {
									if (fnmatch($__key,$__value)) {
										$loopResult=true;
										break;										
									}
								} elseif ($__itemIsEqual) {								
									if ($__key==$__value) {
										$loopResult=true;
										break;
									}
								}
							}
						}
					}
				}
				
				if (($isBlackListed!=true or $hasBlackList!=true) and ($isWhiteListed==true or $hasWhiteList!=true)) {
					return true;
				} else {
					return false;
				}
			}
		}
		#--(End)-->Default global black list
		
		if (!isset($options['blackList']['.'])) {
			$options['blackList']['.']=array('folder');
		}
		if (!isset($options['blackList']['..'])) {
			$options['blackList']['..']=array('folder');
		}
		if (!isset($options['blackList']['_vti_cnf'])) {
			$options['blackList']['_vti_cnf']=array('folder');
		}
		
		if (!isset($options['startDirectory'])) {
			$options['startDirectory']=".".DIRECTORY_SEPARATOR;
		}
		if ($options['startDirectory'][strlen($options['startDirectory'])-1]!=DIRECTORY_SEPARATOR) {
			$options['startDirectory'].=DIRECTORY_SEPARATOR;
		}
		$startdir=$options['startDirectory'];
		
		if (!isset($options['startDirectoryOriginal'])) {
			$options['startDirectoryOriginal']=$options['startDirectory'];
		} elseif ($options['startDirectoryOriginal'][strlen($options['startDirectoryOriginal'])-1]!=DIRECTORY_SEPARATOR) {
			$options['startDirectoryOriginal'].=DIRECTORY_SEPARATOR;
		}

		if (!isset($options['includeInResult'])) {
			#--(Begin)-->For backward compatitiblity
			if (!isset($options['directoriesOnly'])) {
				$options['directoriesOnly']=0;
			}
			if (!isset($options['onlyFiles'])) {
				$options['onlyFiles']=0;
			}
			if ($options['directoriesOnly']) {
				$options['includeInResult']=array('folder');
			} else if ($options['onlyFiles']) {
				$options['includeInResult']=array('file');
			} else {
				$options['includeInResult']=array('folder','file');
			}
			#--(End)-->For backward compatitiblity
		}
		
		if (!isset($options['detailedResultsEnabled'])) {
			$options['detailedResultsEnabled']=true;
		}
		
		
		if (!isset($options['maxLevel'])) {
			$options['maxLevel']='all';
		}
		$maxlevel=$options['maxLevel'];

		if (!isset($options['level'])) {
			$options['level']=1;
		}
		$level=$options['level'];

		if (!isset($options['correctModificationTime'])) {
			$options['correctModificationTime']=false;
		}
		if (!isset($options['correctCreationTime'])) {
			$options['correctCreationTime']=false;
		}
		if ($options['fastModeEnabled']!=true) {
			if ((PHP_OS=='WINNT' or PHP_OS=='Windows' or PHP_OS=='WIN32') and ($_SERVER['CLIENTNAME']=='Console' or $_SERVER['SESSIONNAME']=='Console')) {
				if ($options['correctModificationTime']==true) {
					if (!isset($options['filesListStrModification'])) {
						$options['getModificationTimeDirectly']=true;
						//http://www.microsoft.com/resources/documentation/windows/xp/all/proddocs/en-us/tree.mspx?mfr=true
						$cmd='dir /s /a:-d /o:-d /t:w "'.$startdir.'"';
						$options['filesListStrModification']=cmfcShell::windExecCustom($cmd,array('waitForResult'=>true,'runInBackground'=>true),array('temporaryDir'=>''));
						//file_put_contents('fd.txt',$options['filesListStrModification']);
					}
				}
				if ($options['correctCreationTime']==true) {
					if (!isset($options['filesListStrCreation'])) {
						$options['getCreationTimeDirectly']=true;
						//http://www.microsoft.com/resources/documentation/windows/xp/all/proddocs/en-us/tree.mspx?mfr=true
						$cmd='dir /s /a:-d /o:-d /t:c "'.$startdir.'"';
						$options['filesListStrCreation']=cmfcShell::windExecCustom($cmd,array('waitForResult'=>true,'runInBackground'=>true),array('temporaryDir'=>''));
						//file_put_contents('fd.txt',$options['filesListStrCreation']);
					}
				}
			}
		}
		if (is_dir($startdir)) {
	        if ($dh = opendir($startdir)) {
				while (($file = readdir($dh)) !== false) {
					if (______isAcceptable($startdir.$file, $options['blackList'], $options['whiteList'], null)) {
						$r=is_dir($startdir . $file);
						//echo "$startdir$file|$r<br />";
						if (is_dir($startdir . $file) == 'dir') {
							//build your directory array however you choose;
							//add other file details that you want.
							$key=$startdir . $file;
							if ($options['resultRelativeToStartDirectoryEnabled']) {
								$key=str_replace($options['startDirectoryOriginal'],'',$key);
							}
							if (in_array('folder',$options['includeInResult']) and ______isAcceptable($startdir.$file, $options['blackListResult'], $options['whiteListResult'], null) ) {
								if ($options['detailedResultsEnabled']!=true) {
									$directorylist[$key]=$key;
									
								} else {
									$directorylist[$key]['level'] = $level;
									$directorylist[$key]['dir'] = 1;
									$directorylist[$key]['name'] = $file;
									$directorylist[$key]['path'] = $startdir;
									if ($options['fastModeEnabled']!=true) {
										$directorylist[$key]['modificationTime'] = filemtime($startdir.$file);
										$directorylist[$key]['lastAccessTime'] = fileatime($startdir.$file);
										$directorylist[$key]['lastChangeTime'] = filectime($startdir.$file);
										$directorylist[$key]['permissions'] = fileperms($startdir.$file);
									}
								}
							}
							if ($maxlevel == "all" or $maxlevel == null or $maxlevel > $level) {
								$subOptions=$options;
								$subOptions['level']=$subOptions['level']+1;
								$subOptions['startDirectory']=$subOptions['startDirectory'] . $file . DIRECTORY_SEPARATOR;
							    $list2 = cmfcDirectory::getContentsAsSingleLevelArray2($subOptions,&$directorylist);
							    if(is_array($list2)) {
							        $directorylist = cmfcPhp4::array_merge($directorylist, $list2);
							    }
							}
						} else {
							if (in_array('file',$options['includeInResult']) and ______isAcceptable($startdir.$file, $options['blackListResult'], $options['whiteListResult'], null) ) {
								$key=$startdir . $file;
								if ($options['resultRelativeToStartDirectoryEnabled']) {
									$key=str_replace($options['startDirectoryOriginal'],'',$key);
								}
								
								if ($options['detailedResultsEnabled']!=true) {
									$directorylist[$key]=$key;
									
								} else {
									//if you want to include files; build your file array 
									//however you choose; add other file details that you want.
									$directorylist[$key]['level'] = $level;
									$directorylist[$key]['dir'] = 0;
									$directorylist[$key]['name'] = $file;
									$directorylist[$key]['path'] = $startdir;
									if ($options['fastModeEnabled']!=true) {
										$directorylist[$key]['modificationTime'] = filemtime($startdir.$file);
										$directorylist[$key]['lastAccessTime'] = fileatime($startdir.$file);
										$directorylist[$key]['creationTime'] = filectime($startdir.$file);
										$directorylist[$key]['lastChangeTime'] = $directorylist[$key]['creationTime'];
										
										$directorylist[$key]['permissions'] = fileperms($startdir.$file);
										$directorylist[$key]['size'] = filesize ($startdir.$file);
										
										if ($options['getModificationTimeDirectly']==true and !empty($options['filesListStrModification'])) {
											$dirName=preg_quote($startdir);
											$fileName=preg_quote($file);
											if (preg_match('%Directory of '.$dirName.'?[\s]+(([0-9/]+  [0-9/:]+ [AMP]+) +[0-9,]+ +[^\n]+\s+)+%', $options['filesListStrModification'], $regs)) {
												if (preg_match('%([0-9/]+  [0-9/:]+ [AMP]+) +[0-9,]+ +'.$fileName.'%', $regs[0], $regs2)) {
													$directorylist[$key]['modificationTime']=strtotime($regs2[1]);
													//$directorylist[$key]['creationTimeStr']=date('Y-m-d H:i:s',strtotime($regs2[1]));
													//echo '<pre>';print_r($regs[1]);echo '</pre>';
												}
											}
										}
										if ($options['getCreationTimeDirectly']==true and !empty($options['filesListStrCreation'])) {
											$dirName=preg_quote($startdir);
											$fileName=preg_quote($file);
											if (preg_match('%Directory of '.$dirName.'?[\s]+(([0-9/]+  [0-9/:]+ [AMP]+) +[0-9,]+ +[^\n]+\s+)+%', $options['filesListStrCreation'], $regs)) {
												if (preg_match('%([0-9/]+  [0-9/:]+ [AMP]+) +[0-9,]+ +'.$fileName.'%', $regs[0], $regs2)) {
													$directorylist[$key]['creationTime']=strtotime($regs2[1]);
													//$directorylist[$key]['creationTimeStr']=date('Y-m-d H:i:s',strtotime($regs2[1]));
													//echo '<pre>';print_r($regs[1]);echo '</pre>';
												}
											}
										}
									}
								}
							}
						}
					}
				}
				closedir($dh);
			}
		}
		return($directorylist);
	}
	
	
	/**
	* recursive_directory_size( directory, human readable format )
	* expects path to directory and optional TRUE / FALSE
	* PHP has to have the rights to read the directory you specify
	* and all files and folders inside the directory to count size
	* if you choose to get human readable format,
	* the function returns the filesize in bytes, KB and MB

	* to use this function to get the filesize in bytes, write:
	* recursive_directory_size('path/to/directory/to/count');

	* to use this function to get the size in a nice format, write:
	* recursive_directory_size('path/to/directory/to/count',TRUE);
	* 
	* @author Sina Salek <sina.salek.ws>
	*/

	function size($directory, $format=FALSE)
	{
		$size = 0;

		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}

		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
		{
			// ... we return -1 and exit the function
			return -1;
		}
		// we open the directory
		if($handle = opendir($directory))
		{
			// and scan through the items inside
			while(($file = readdir($handle)) !== false)
			{
				// we build the new path
				$path = $directory.'/'.$file;

				// if the filepointer is not the current directory
				// or the parent directory
				if($file != '.' && $file != '..')
				{
					// if the new path is a file
					if(is_file($path))
					{
						// we add the filesize to the total size
						$size += filesize($path);

					// if the new path is a directory
					}elseif(is_dir($path))
					{
						// we call this function with the new path
						$handlesize = recursive_directory_size($path);

						// if the function returns more than zero
						if($handlesize >= 0)
						{
							// we add the result to the total size
							$size += $handlesize;

						// else we return -1 and exit the function
						}else{
							return -1;
						}
					}
				}
			}
			// close the directory
			closedir($handle);
		}
		// if the format is set to human readable
		if($format == TRUE)
		{
			// if the total size is bigger than 1 MB
			if($size / 1048576 > 1)
			{
				return round($size / 1048576, 1).' MB';

			// if the total size is bigger than 1 KB
			}elseif($size / 1024 > 1)
			{
				return round($size / 1024, 1).' KB';

			// else return the filesize in bytes
			}else{
				return round($size, 1).' bytes';
			}
		}else{
			// return the total filesize in bytes
			return $size;
		}
	}
}


class cmfcClass {
	/** 
	* If You Want to have just variables of end class, not variables of end class and its parents, this function is your solution	
	* @return array
    */

	function getObjectVars(&$obj)
	{
		$parent_object_vars=get_class_vars(get_parent_class($obj));
		$object_vars=get_object_vars($obj);
		foreach ($parent_object_vars as $key=>$value)
			foreach ($object_vars as $_key=>$_value)
				if ($key==$_key) { unset($object_vars[$_key]);  break; }
		return $object_vars;
	}	
}


class cmfcShell {
	function windExec($cmd,$mode='',$tmpDir=''){

	    // runs a command line and returns
	    // the output even for Wind XP SP2
	    // example: $cmd = "fullpath.exe -arg1 -arg2"
	    // $outputString = windExec($cmd, "FG");
	    // OR windExec($cmd);
	    // (no output since it runs in BG by default)
	    // for output requires that EXEC_TMP_DIR be defined
	    // Setup the command to run from "run"
	    $cmdline = "cmd /C $cmd";
	    // set-up the output and mode
	    if ($mode=='FG'){
	        $outputfile = cmfcFile::getUniqueFileName($tmpDir,"cmdTemp.txt");
	        $cmdline .= " > \"$outputfile\"";
	        $m = true;
	    } else $m = false;
	    // Make a new instance of the COM object
	    $WshShell = new COM("WScript.Shell");
	    // Make the command window but dont show it.

	    $oExec = $WshShell->Run($cmdline, 0, $m);
	    if ($outputfile){
	        // Read the tmp file.
	        $retStr = file_get_contents($outputfile);
	        // Delete the temp_file.
	        unlink($outputfile);
	    } else $retStr = "";
	    return $retStr;
	}
	
	
	/**
	* for using -format in windows you should use %% instead of % for variables
    * like %%w or %%h
	* @param string $mode //waitForResult,runInBackground
	*/
	function windExecCustom($cmd,$mode=array('waitForResult'=>true,'runInBackground'=>true),$options=array()) {
		if ($mode['waitForResult']!=true and $mode['runInBackground']==true) {
		    $cmdline = "cmd /C $cmd";
		    // set-up the output and mode
		    $outputfile = cmfcFile::getUniqueFileName($options['temporaryDir'],"cmdTemp.txt");
		    $cmdline .= " > \"$outputfile\"";
		    $m = false;
		    //file_put_contents($outputfile,'');
		    //chmod($outputfile,0777);
		    
		    // Make a new instance of the COM object
		    $WshShell = new COM("WScript.Shell");
		    // Make the command window but dont show it.
		    $oExec = $WshShell->Run($cmdline, 0, $m);
		    return $outputfile;
		    
		} elseif ($mode['waitForResult']==true and $mode['runInBackground']==true) {
			if (empty($options['temporaryDir'])) {
				/**
				* apache should run as service and Start>Run>services.msc->Apache->Proprties->LOG ON->"Allow this service to interact with desktop"
				* should be enable
				* @link http://www.thescripts.com/forum/thread680563.html
				*/
				#--(Begin)-->does not need temp file but open a windows
				$wscript= new COM('WScript.Shell');
				$runCommand='cmd.exe /C '.$cmd;
				$output=$wscript->Exec($runCommand);
				return $output=$output->StdOut->ReadAll();
				#--(End)-->does not need temp file but open a windows
			} else {
				return cmfcShell::windExec($cmd,'FG',$options['temporaryDir']);
			}
		}
	}
	
	
	/**
	* @example
	* 	<code>
	* 		php file.php --foo=bar -abc -AB 'hello world' --baz
	* 	</cdoe>
	* 	produces:
	* 	<code>
	*   cmfcShell::getArgs($argv);    
	*	  Array
	* 	  (
	*   	[foo] => bar
	*   	[a] => true
	*   	[b] => true
	*   	[c] => true
	*   	[A] => true
	*   	[B] => hello world
	*   	[baz] => true
	* 	  )
	* </code>
	*/
	function getArgs($args) {
	 $out = array();
	 $last_arg = null;
	    for($i = 1, $il = sizeof($args); $i < $il; $i++) {
	        if( (bool)preg_match("/^--(.+)/", $args[$i], $match) ) {
	         $parts = explode("=", $match[1]);
	         $key = preg_replace("/[^a-z0-9]+/", "", $parts[0]);
	            if(isset($parts[1])) {
	             $out[$key] = $parts[1];   
	            }
	            else {
	             $out[$key] = true;   
	            }
	         $last_arg = $key;
	        }
	        else if( (bool)preg_match("/^-([a-zA-Z0-9]+)/", $args[$i], $match) ) {
	            for( $j = 0, $jl = strlen($match[1]); $j < $jl; $j++ ) {
	             $key = $match[1]{$j};
	             $out[$key] = true;
	            }
	         $last_arg = $key;
	        }
	        else if($last_arg !== null) {
	         $out[$last_arg] = $args[$i];
	        }
	    }
	 return $out;
	}
}


/**
* for backward compatibility with php4 :
* 	- rename array_merge() to cmfcPhp4::array_merge()
* 	- get_class() to cmfcPhp4::get_class()
* 	- get_parent_class() to cmfcPhp4::get_parent_class()
* 	- get_class_methods() to cmfcPhp4::get_class_methods()
* 	- strrpos() to cmfcPhp4::get_parent_class()
* 	- strripos() to cmfcPhp4::strripos()
* 	- ip2long() to cmfcPhp4::ip2long()
*	- $this=$object does not allowed anymore
*	- now are case-sensitive
*		__CLASS__, __METHOD__, and __FUNCTION__
* 	- An object with no properties is no longer considered "empty"
*/
class cmfcPhp4 {
	/**
	* as of php5 was changed to accept only arrays. 
	* If a non-array variable is passed, a E_WARNING will be thrown for every such parameter. 
	* Be careful because your code may start emitting E_WARNING out of the blue. 
	* 
	* @changelog
	* 	- tested
	*/
	function array_merge() {
		$args = func_get_args();
		foreach ($args as $k=>$value) {
			if (!is_array($value)) $value=array();
			$args[$k]=$value;
		}
		return call_user_func_array("array_merge", $args);
	}
	
	/**
	* as of php5 return the name of the classes/methods as they were declared (
	* case-sensitive) which may lead to problems in older scripts that rely
	* on the previous behaviour (the class/method name was always returned lowercased).
	* A possible solution is to search for those functions in all your scripts and use strtolower(). 
	*/
	function get_class() {
		$args = func_get_args();
		return strtolower(call_user_func_array("get_class", $args));
	}

	/**
	* as of php5 return the name of the classes/methods as they were declared (
	* case-sensitive) which may lead to problems in older scripts that rely
	* on the previous behaviour (the class/method name was always returned lowercased).
	* A possible solution is to search for those functions in all your scripts and use strtolower(). 
	*/	
	function get_parent_class() {
		$args = func_get_args();
		return strtolower(call_user_func_array("get_parent_class", $args));
	}
	
	/**
	* as of php5 return the name of the classes/methods as they were declared (
	* case-sensitive) which may lead to problems in older scripts that rely
	* on the previous behaviour (the class/method name was always returned lowercased).
	* A possible solution is to search for those functions in all your scripts and use strtolower(). 
	*/
	function get_class_methods() {
		$args = func_get_args();
		$result=call_user_func_array("get_class_methods", $args);
		if (is_array($result))
			foreach ($result as $key=>$value) {
				$result[$key]=strtolower($value);
			}
		return $result;
	}
	
	/**
	* as of php5 now use the entire string as a needle.
	*/
	function strrpos ($haystack, $needle, $offset)
	{
	    $size = strlen ($haystack);
	    $pos = strpos (strrev($haystack), strrev($needle), $size - $offset);
	  
	    if ($pos === false)
	        return false;
	  
	    return $size - $pos - strlen($needle);
	}
	
	/**
	* as of php5 now use the entire string as a needle.
	*/
    function strripos($haystack, $needle, $offset=0) {
        if($offset<0){
            $temp_cut = strrev(  substr( $haystack, 0, abs($offset) )  );
        }
        else{
            $temp_cut = strrev(  substr( $haystack, $offset )  );
        }
        $pos = strlen($haystack) - (strpos($temp_cut, strrev($needle)) + $offset + strlen($needle));
        if ($pos == strlen($haystack)) { $pos = 0; }
       
        if(strpos($temp_cut, strrev($needle))===false){
             return false;
        }
        else return $pos;
    }/* endfunction strripos*/
	
	/**
	* as of php5 now returns FALSE when an invalid IP address is passed as 
	* argument to the function, and no longer -1.
	*/
	function ip2long() {
		$args = func_get_args();
		$result=call_user_func_array("ip2long", $args);
		if ($result===false) $result=-1;
		return $result;
	}
	
	/**
	* as of php5 An object with no properties is no longer considered "empty" 
	* @note does not work
	*/
	function _empty($var) {
		if (is_object($var) and empty($var)===false) {
			//if (empty(get_object_vars($var)))
				
		}
	}

}


// Standard URL validation : http://www.foad.org/~abigail/Perl/url3.regex
/*
(?:http://(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.
)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)
){3}))(?::(?:\d+))?)(?:/(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F
\d]{2}))|[;:@&=])*)(?:/(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{
2}))|[;:@&=])*))*)(?:\?(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{
2}))|[;:@&=])*))?)?)|(?:ftp://(?:(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?
:%[a-fA-F\d]{2}))|[;?&=])*)(?::(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-
fA-F\d]{2}))|[;?&=])*))?@)?(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-
)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?
:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))?))(?:/(?:(?:(?:(?:[a-zA-Z\d$\-_.+!
*'(),]|(?:%[a-fA-F\d]{2}))|[?:@&=])*)(?:/(?:(?:(?:[a-zA-Z\d$\-_.+!*'()
,]|(?:%[a-fA-F\d]{2}))|[?:@&=])*))*)(?:;type=[AIDaid])?)?)|(?:news:(?:
(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[;/?:&=])+@(?:(?:(
?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:[
a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)){3})))|(?:[a-zA-Z](
?:[a-zA-Z\d]|[_.+-])*)|\*))|(?:nntp://(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[
a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d
])?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))?)/(?:[a-zA-Z](?:[a-zA-Z
\d]|[_.+-])*)(?:/(?:\d+))?)|(?:telnet://(?:(?:(?:(?:(?:[a-zA-Z\d$\-_.+
!*'(),]|(?:%[a-fA-F\d]{2}))|[;?&=])*)(?::(?:(?:(?:[a-zA-Z\d$\-_.+!*'()
,]|(?:%[a-fA-F\d]{2}))|[;?&=])*))?@)?(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a
-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d]
)?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))?))/?)|(?:gopher://(?:(?:
(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:
(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+
))?)(?:/(?:[a-zA-Z\d$\-_.+!*'(),;/?:@&=]|(?:%[a-fA-F\d]{2}))(?:(?:(?:[
a-zA-Z\d$\-_.+!*'(),;/?:@&=]|(?:%[a-fA-F\d]{2}))*)(?:%09(?:(?:(?:[a-zA
-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[;:@&=])*)(?:%09(?:(?:[a-zA-Z\d$
\-_.+!*'(),;/?:@&=]|(?:%[a-fA-F\d]{2}))*))?)?)?)?)|(?:wais://(?:(?:(?:
(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:
[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))?
)/(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))*)(?:(?:/(?:(?:[a-zA
-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))*)/(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(
?:%[a-fA-F\d]{2}))*))|\?(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]
{2}))|[;:@&=])*))?)|(?:mailto:(?:(?:[a-zA-Z\d$\-_.+!*'(),;/?:@&=]|(?:%
[a-fA-F\d]{2}))+))|(?:file://(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]
|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:
(?:\d+)(?:\.(?:\d+)){3}))|localhost)?/(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'()
,]|(?:%[a-fA-F\d]{2}))|[?:@&=])*)(?:/(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(
?:%[a-fA-F\d]{2}))|[?:@&=])*))*))|(?:prospero://(?:(?:(?:(?:(?:[a-zA-Z
\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)
*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))?)/(?:(?:(?:(?
:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[?:@&=])*)(?:/(?:(?:(?:[a-
zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[?:@&=])*))*)(?:(?:;(?:(?:(?:[
a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[?:@&])*)=(?:(?:(?:[a-zA-Z\d
$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[?:@&])*)))*)|(?:ldap://(?:(?:(?:(?:
(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?:
[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))?
))?/(?:(?:(?:(?:(?:(?:(?:[a-zA-Z\d]|%(?:3\d|[46][a-fA-F\d]|[57][Aa\d])
)|(?:%20))+|(?:OID|oid)\.(?:(?:\d+)(?:\.(?:\d+))*))(?:(?:%0[Aa])?(?:%2
0)*)=(?:(?:%0[Aa])?(?:%20)*))?(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F
\d]{2}))*))(?:(?:(?:%0[Aa])?(?:%20)*)\+(?:(?:%0[Aa])?(?:%20)*)(?:(?:(?
:(?:(?:[a-zA-Z\d]|%(?:3\d|[46][a-fA-F\d]|[57][Aa\d]))|(?:%20))+|(?:OID
|oid)\.(?:(?:\d+)(?:\.(?:\d+))*))(?:(?:%0[Aa])?(?:%20)*)=(?:(?:%0[Aa])
?(?:%20)*))?(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))*)))*)(?:(
?:(?:(?:%0[Aa])?(?:%20)*)(?:[;,])(?:(?:%0[Aa])?(?:%20)*))(?:(?:(?:(?:(
?:(?:[a-zA-Z\d]|%(?:3\d|[46][a-fA-F\d]|[57][Aa\d]))|(?:%20))+|(?:OID|o
id)\.(?:(?:\d+)(?:\.(?:\d+))*))(?:(?:%0[Aa])?(?:%20)*)=(?:(?:%0[Aa])?(
?:%20)*))?(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))*))(?:(?:(?:
%0[Aa])?(?:%20)*)\+(?:(?:%0[Aa])?(?:%20)*)(?:(?:(?:(?:(?:[a-zA-Z\d]|%(
?:3\d|[46][a-fA-F\d]|[57][Aa\d]))|(?:%20))+|(?:OID|oid)\.(?:(?:\d+)(?:
\.(?:\d+))*))(?:(?:%0[Aa])?(?:%20)*)=(?:(?:%0[Aa])?(?:%20)*))?(?:(?:[a
-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))*)))*))*(?:(?:(?:%0[Aa])?(?:%2
0)*)(?:[;,])(?:(?:%0[Aa])?(?:%20)*))?)(?:\?(?:(?:(?:(?:[a-zA-Z\d$\-_.+
!*'(),]|(?:%[a-fA-F\d]{2}))+)(?:,(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-f
A-F\d]{2}))+))*)?)(?:\?(?:base|one|sub)(?:\?(?:((?:[a-zA-Z\d$\-_.+!*'(
),;/?:@&=]|(?:%[a-fA-F\d]{2}))+)))?)?)?)|(?:(?:z39\.50[rs])://(?:(?:(?
:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?)\.)*(?:[a-zA-Z](?:(?
:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:\d+)){3}))(?::(?:\d+))
?)(?:/(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))+)(?:\+(?:(?:
[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))+))*(?:\?(?:(?:[a-zA-Z\d$\-_
.+!*'(),]|(?:%[a-fA-F\d]{2}))+))?)?(?:;esn=(?:(?:[a-zA-Z\d$\-_.+!*'(),
]|(?:%[a-fA-F\d]{2}))+))?(?:;rs=(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA
-F\d]{2}))+)(?:\+(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))+))*)
?))|(?:cid:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[;?:@&=
])*))|(?:mid:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[;?:@
&=])*)(?:/(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[;?:@&=]
)*))?)|(?:vemmi://(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z
\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\
.(?:\d+)){3}))(?::(?:\d+))?)(?:/(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a
-fA-F\d]{2}))|[/?:@&=])*)(?:(?:;(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a
-fA-F\d]{2}))|[/?:@&])*)=(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d
]{2}))|[/?:@&])*))*))?)|(?:imap://(?:(?:(?:(?:(?:(?:(?:[a-zA-Z\d$\-_.+
!*'(),]|(?:%[a-fA-F\d]{2}))|[&=~])+)(?:(?:;[Aa][Uu][Tt][Hh]=(?:\*|(?:(
?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[&=~])+))))?)|(?:(?:;[
Aa][Uu][Tt][Hh]=(?:\*|(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2
}))|[&=~])+)))(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[
&=~])+))?))@)?(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])
?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:\.(?:
\d+)){3}))(?::(?:\d+))?))/(?:(?:(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:
%[a-fA-F\d]{2}))|[&=~:@/])+)?;[Tt][Yy][Pp][Ee]=(?:[Ll](?:[Ii][Ss][Tt]|
[Ss][Uu][Bb])))|(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))
|[&=~:@/])+)(?:\?(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[
&=~:@/])+))?(?:(?:;[Uu][Ii][Dd][Vv][Aa][Ll][Ii][Dd][Ii][Tt][Yy]=(?:[1-
9]\d*)))?)|(?:(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[&=~
:@/])+)(?:(?:;[Uu][Ii][Dd][Vv][Aa][Ll][Ii][Dd][Ii][Tt][Yy]=(?:[1-9]\d*
)))?(?:/;[Uu][Ii][Dd]=(?:[1-9]\d*))(?:(?:/;[Ss][Ee][Cc][Tt][Ii][Oo][Nn
]=(?:(?:(?:[a-zA-Z\d$\-_.+!*'(),]|(?:%[a-fA-F\d]{2}))|[&=~:@/])+)))?))
)?)|(?:nfs:(?:(?://(?:(?:(?:(?:(?:[a-zA-Z\d](?:(?:[a-zA-Z\d]|-)*[a-zA-
Z\d])?)\.)*(?:[a-zA-Z](?:(?:[a-zA-Z\d]|-)*[a-zA-Z\d])?))|(?:(?:\d+)(?:
\.(?:\d+)){3}))(?::(?:\d+))?)(?:(?:/(?:(?:(?:(?:(?:[a-zA-Z\d\$\-_.!~*'
(),])|(?:%[a-fA-F\d]{2})|[:@&=+])*)(?:/(?:(?:(?:[a-zA-Z\d\$\-_.!~*'(),
])|(?:%[a-fA-F\d]{2})|[:@&=+])*))*)?)))?)|(?:/(?:(?:(?:(?:(?:[a-zA-Z\d
\$\-_.!~*'(),])|(?:%[a-fA-F\d]{2})|[:@&=+])*)(?:/(?:(?:(?:[a-zA-Z\d\$\
-_.!~*'(),])|(?:%[a-fA-F\d]{2})|[:@&=+])*))*)?))|(?:(?:(?:(?:(?:[a-zA-
Z\d\$\-_.!~*'(),])|(?:%[a-fA-F\d]{2})|[:@&=+])*)(?:/(?:(?:(?:[a-zA-Z\d
\$\-_.!~*'(),])|(?:%[a-fA-F\d]{2})|[:@&=+])*))*)?)))
*/


class cmfcImage {
	/**
	* <code>
	*    $source_file='gallery_files/IMG_9736.jpg';
	*    $desc_file='gallery_files/test.jpg';
	*    cmfFlexibleImageCropper($source_file,$desc_file,200,200);
	*    $source = imagecreatefromjpeg($desc_file);
	*    imagejpeg($source);
	* </code>
	* @previousImages flexible_image_cropper , cmfFlexibleImageCropper
	*/
	function flexibleCropper($source_location, $desc_location=null,$crop_size_w=null,$crop_size_h=null)
	{
	    list($src_w, $src_h) = getimagesize($source_location);

	    $image_source = imagecreatefromjpeg($source_location);
	    $image_desc = imagecreatetruecolor($crop_size_w,$crop_size_h);

	/*
	    if ($crop_size_w>$crop_size_h) {$my_crop_size_w=null;}
	            elseif ($crop_size_h>$crop_size_w)
	                    {$my_crop_size_h=null;

	    if (is_null($my_crop_size_h) and !is_null($my_crop_size_w))
	            { $my_crop_size_h=$src_h*$my_crop_size_w/$src_w; }
	    elseif (!is_null($my_crop_size_h))
	            { $my_crop_size_w=$src_w*$my_crop_size_h/$src_h; }
	*/

	    if ($src_w<$src_h) {$my_crop_size_h=$src_h*$crop_size_w/$src_w;} else {$my_crop_size_h=$crop_size_h;}
	    if ($src_h<$src_w) {$my_crop_size_w=$src_w*$crop_size_h/$src_h;} else {$my_crop_size_w=$crop_size_w;}
	//                echo "($my_crop_size_w-$my_crop_size_h)";
	    if ($my_crop_size_w>$crop_size_w) {$additional_x=round(($crop_size_w-$my_crop_size_w)/2);} else {$additional_x=0;}
	    if ($my_crop_size_h>$crop_size_h) {$additional_y=round(($crop_size_h-$my_crop_size_h)/2);} else {$additional_y=0;}

	    $off_x=round($src_w/2)-round($my_crop_size_w/2);
	    $off_y=round($src_h/2)-round($my_crop_size_h/2)+$additional_y;
	    $off_w=(round($src_w/2)+round($my_crop_size_w/2))-$off_x;
	    $off_h=(round($src_h/2)+round($my_crop_size_h/2))-$off_y;

	    imagecopyresampled($image_desc, $image_source,$additional_x, $additional_y, $off_x, $off_y, $my_crop_size_w, $my_crop_size_h, $off_w, $off_h);

	    if (!is_null($desc_location))
	            imagejpeg($image_desc, $desc_location,100);
	    else
	            imagejpeg($image_desc);
	}

	/**
	* integrated with cropCanvas class and worked amazingly, so there is no need for this function, except for backward compatibility
	* @previousNames image_resizer , cmfImageResizer
	*/
	function resizer($image_file_name,$desc_location=null,$new_width=null,$new_height=null,$min_size=null,$show_image=true)
	{
	    list($width, $height) = @getimagesize($image_file_name);
	    if (empty($width) or empty($height)) {return false;}

	    if (!is_null($min_size)) {
	            if (is_null($min_size)) {$min_size=$new_width;}
	            if (is_null($min_size)) {$min_size=$new_height;}

	            if ($width>$height) {$new_height=$min_size;$new_width=null;}
	                    else {$new_width=$min_size;$new_height=null;}
	    }

	//        $my_new_height=$new_height;
	    if (is_null($new_height) and !is_null($new_width))
	            { $my_new_height=round($height*$new_width/$width);$my_new_width=$new_width; }
	    elseif (!is_null($new_height))
	            { $my_new_width=round($width*$new_height/$height);$my_new_height=$new_height; }
	//echo "$my_new_width, $my_new_height <br/>";
	    $image_resized = imagecreatetruecolor($my_new_width, $my_new_height);
	    $image = imagecreatefromjpeg($image_file_name);
	    imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $my_new_width, $my_new_height, $width, $height);

	    //--(BEGIN)-->save , show or send image pointer as result
	    if (!is_null($desc_location))
	            imagejpeg($image_resized, $desc_location,100);
	    elseif ($show_image==true)
	            imagejpeg($image_resized);
	    return $image_resized;
	    //--(END)-->save , show or send image pointer as result
	}



	/**
	* @previousNames restrainImage, cmfRestrainImage
	*/
	function restrainImage($path,$maxWidth,$maxHeight,$dst=null){
	    $imageInfo = getimagesize($path);
	    $width = $imageInfo[0];
	    $height = $imageInfo[1];
	    $types = array (1 => 'gif', 'jpeg', 'png');

	    $x_ratio = $maxWidth / $width;
	    $y_ratio = $maxHeight / $height;

	    if( $width <= $maxWidth && $height <= $maxHeight ){
	            $newWidth = $width;
	            $newHeight = $height;
	    }elseif( ($x_ratio * $height) < $maxHeight){
	            $newWidth = $maxWidth;
	            $newHeight = ceil($x_ratio * $height);
	    }else{
	            $newWidth = ceil($y_ratio * $width);
	            $newHeight = $maxHeight;
	    }
	    $image = call_user_func('imagecreatefrom'.$types[$imageInfo[2]],$path);
	    $resized = imagecreatetruecolor($newWidth,$newHeight);
	    imagecopyresized($resized,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
	    call_user_func('image'.$types[$imageInfo[2]],$resized,($dst!=null?$dst:$path));
	}
}



class cmfcDom {
	/**
	* @desc buggy
	* $subNodes=domXpathQuery($xpath,"//li[contains(@class,'executor')]",$node);
	*/
	function xpathQuery($xpath,$query,$relativeNode) {
		 $nodes=$xpath->query($query);
		 if (!empty($nodes)) {
	 		foreach ($nodes as $key=>$node) {
	 			if (!cmfcDom::isChildOf($node,$relativeNode)) {
	 				$nodes->offsetUnset($key);
				}
			} 

			return $nodes;
		 }
		 return $nodes;
	}
	
	/**
    * result sample : /html[1]/body[1]/span[1]/fieldset[1]/div[1]
    * @return string
    */
	function getNodeXPath( $node ) {
		
		$result='';
		while ($parentNode = $node->parentNode) {
			$nodeIndex=-1;
            $nodeTagIndex=0;
			do {
                $nodeIndex++;
				$testNode = $parentNode->childNodes->item( $nodeIndex );
                
                /**
                * This complex condition is because there is two html tags inside dom tree, one is empty!!
                */
                if ($testNode->nodeName==$node->nodeName and $testNode->parentNode->isSameNode($node->parentNode) and ($testNode->nodeName!='html' or $testNode->childNodes->length>0)) {
                    $nodeTagIndex++;
                }
                
			} while (!$node->isSameNode($testNode));

			$result="/{$node->nodeName}[{$nodeTagIndex}]".$result;
			$node=$parentNode;
		};
		return $result;
	}

	function getInnerXml ($node) {
		$innerXml = '';
		$xmlDoc = $node->ownerDocument;
		for ($i = 0; $i < $node->childNodes->length; $i++) {
			$innerXml .= $xmlDoc->saveXML($node->childNodes->item($i));
		}
		return $innerXml;
	}
	
	function getSingleNode($xpath,$query) {
		$nodes = $xpath->query($query);
		foreach ($nodes as $node) {
			if (is_object($node)) {
				return $node;
				break;
			}
		}
		return false;
	}


	function moveChilds($parentNode,$destParentNode) {
		if ($parentNode->hasChildNodes()) {
			$childNodes=$parentNode->childNodes;
			foreach ($childNodes as $childNode) {
				if ($parentNode->ownerDocument!==$destParentNode->ownerDocument) {
					$newChildNode=$destParentNode->ownerDocument->importNode($childNode, true);
				} else {
					$newChildNode=$childNode->parentNode->removeChild($childNode);
					$destParentNode->appendChild($newChildNode);
				}
				$destParentNode->appendChild($newChildNode);
			}
		}
	}

	function getOuterXml ($node) {
		$innerXml = '';
		$xmlDoc = $node->ownerDocument;
		$xmlDoc->formatOutput = true;
		return $xmlDoc->saveXML($node);
	}

	function getXmlAsObject ($xml) {
		$doc = new DOMDocument();
		$doc->loadXML('<span />');
		$f = $doc->createDocumentFragment();
		$f->appendXML($xml);
		$doc->documentElement->appendChild($f);
		return $doc->documentElement;
	}

	function getXmlAsDocument ($xml) {
		$doc = new DOMDocument();
		$doc->loadXML('<span />');
		$f = $doc->createDocumentFragment();
		$f->appendXML($xml);
		$doc->documentElement->appendChild($f);
		return $doc;
	}


	/**
	* Helper function for replacing $node (DOMNode)
	* with an XML code (string)
	*
	* @var DOMNode $node
	* @var string $xml
	*/
	function setInnerXML($node,$xml) {
		$doc=$node->ownerDocument;
		$f = $doc->createDocumentFragment();
		$f->appendXML($xml);
		$node->parentNode->replaceChild($f,$node);
	}



	function isChildOf($child,$parent) {
		$node=$child;
		while ($node=$node->parentNode) {
			if ($node===$parent) {
				return true;
			}
		}
		
		return false;
	}
}




class cmfcMath {
	function movingAverageSimplified($value,$avgWindow,&$valuesStorage) {
		if (empty($valuesStorage)) {
			$valuesStorage['n']=0;
			$valuesStorage['values']=array();
			$valuesStorage['total']=0;
			//$valuesStorage['oldAvg']=null;
		}
		$valuesStorage['n']++;
		$valuesStorage['values'][]=$value;
		$valuesStorage['total']+=$value;
		
		if (count($valuesStorage['values'])>$avgWindow) {
			$valuesStorage['total']+=$value-$valuesStorage['values'][0];
			echo "firstVal:".$valuesStorage['values'][0]."\n";
			array_shift($valuesStorage['values']);
		}
		echo 'count'.count($valuesStorage['values']).'-'.($valuesStorage['n']%$avgWindow)."\n";
		if ($valuesStorage['n']>=$avgWindow) {
			$b=$avgWindow;
		} else {
			$b=$valuesStorage['n'];
		}

		$valuesStorage['curAvg']=($valuesStorage['total'])/$b;
		return $valuesStorage['curAvg'];
	}
}

?>