<?php
/**
 * Jalali Date function by Milad Rastian (miladmovie AT yahoo DOT com)
 * The main function which convert Gregorian to Jalali calendars is:
 * Copyright (C) 2000  Roozbeh Pournader and Mohammad Toossi 
 * you can see complete note of those function in down of the page
 * 
 * 
 * @package cmf
 * @subpackage beta
 * @author Sina Salek
 * @author Milad Rastian
 * @version $Id: datetime.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
 */
 

if (!is_callable('div')) {
	function div($a,$b) {
	    return (int) ($a / $b);
	}
}

/**
* 
*/
class cmfcJalaliDateTime {
	function get($type,$maket="now",$farsi=1)
	{
		//set 1 if you want translate number to farsi or if you don't like set 0
		$transnumber=$farsi;
		///chosse your timezone
		$TZhours=0;
		$TZminute=0;
		$need="";
		$result1="";
		$result="";
		if ($maket=="now") {
			$year=date("Y");
			$month=date("m");
			$day=date("d");
			list( $jyear, $jmonth, $jday ) = cmfcJalaliDateTime::gregorianToJalali($year, $month, $day);
			$maket=mktime(date("H")+$TZhours,date("i")+$TZminute,date("s"),date("m"),date("d"),date("Y"));
		} else {
			//$maket=0;
			$maket+=$TZhours*3600+$TZminute*60;
			$date=date("Y-m-d",$maket);
			list( $year, $month, $day ) = preg_split ( '/-/', $date );

			list( $jyear, $jmonth, $jday ) = cmfcJalaliDateTime::gregorianToJalali($year, $month, $day);
		}

		$need= $maket;
		$year=date("Y",$need);
		$month=date("m",$need);
		$day=date("d",$need);
		$i=0;
		$subtype="";
		$subtypetemp="";
		list( $jyear, $jmonth, $jday ) = cmfcJalaliDateTime::gregorianToJalali($year, $month, $day);
		while($i<strlen($type))
		{
			$subtype=substr($type,$i,1);
			if($subtypetemp=="\\")
			{
				$result.=$subtype;
				$i++;
				continue;
			}
			
			switch ($subtype)
			{

				case "A":
					$result1=date("a",$need);
					if($result1=="pm") $result.= "&#1576;&#1593;&#1583;&#1575;&#1586;&#1592;&#1607;&#1585;";
					    else $result.="&#1602;&#1576;&#1604;&#8207;&#1575;&#1586;&#1592;&#1607;&#1585;";
					break;

				case "a":
					$result1=date("a",$need);
					if($result1=="pm") $result.= "&#1576;&#46;&#1592;";
					    else $result.="&#1602;&#46;&#1592;";
					break;
				case "d":
					if($jday<10) $result1="0".$jday;
					    else $result1=$jday;
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					    else $result.=$result1;
					break;
				case "D":
					$result1=date("D",$need);
					if($result1=="Thu") $result1="&#1662;";
					else if($result1=="Sat") $result1="&#1588;";
					else if($result1=="Sun") $result1="&#1609;";
					else if($result1=="Mon") $result1="&#1583;";
					else if($result1=="Tue") $result1="&#1587;";
					else if($result1=="Wed") $result1="&#1670;";
					else if($result1=="Thu") $result1="&#1662;";
					else if($result1=="Fri") $result1="&#1580;";
					$result.=$result1;
					break;
				case"F":
					$result.=cmfcJalaliDateTime::getMonthName($jmonth);
					break;
				case "g":
					$result1=date("g",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					    else $result.=$result1;
					break;
				case "G":
					$result1=date("G",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
					case "h":
					$result1=date("h",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "H":
					$result1=date("H",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "i":
					$result1=date("i",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "j":
					$result1=$jday;
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "l":
					$result1=date("l",$need);
					if($result1=="Saturday") $result1="&#1588;&#1606;&#1576;&#1607;";
					else if($result1=="Sunday") $result1="&#1610;&#1603;&#1588;&#1606;&#1576;&#1607;";
					else if($result1=="Monday") $result1="&#1583;&#1608;&#1588;&#1606;&#1576;&#1607;";
					else if($result1=="Tuesday") $result1="&#1587;&#1607;&#32;&#1588;&#1606;&#1576;&#1607;";
					else if($result1=="Wednesday") $result1="&#1670;&#1607;&#1575;&#1585;&#1588;&#1606;&#1576;&#1607;";
					else if($result1=="Thursday") $result1="&#1662;&#1606;&#1580;&#1588;&#1606;&#1576;&#1607;";
					else if($result1=="Friday") $result1="&#1580;&#1605;&#1593;&#1607;";
					$result.=$result1;
					break;
				case "m":
					if($jmonth<10) $result1="0".$jmonth;
					else	$result1=$jmonth;
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "M":
					$result.=cmfcJalaliDateTime::getMonthShortName($jmonth);
					break;
				case "n":
					$result1=$jmonth;
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "s":
					$result1=date("s",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "S":
					$result.="&#1575;&#1605;";
					break;
				case "t":
					$result.=cmfcJalaliDateTime::monthTotalDays ($month,$day,$year);
					break;
				case "w":
					$result1=date("w",$need);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "y":
					$result1=substr($jyear,2,4);
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;
				case "Y":
					$result1=$jyear;
					if($transnumber==1) $result.=cmfcString::convertNumbersToFarsi($result1);
					else $result.=$result1;
					break;		
				case "U" :
					$result.=cmfcJalaliDateTime::time();
					break;
				case "Z" :
					$result.=cmfcJalaliDateTime::yearTotalDays($jmonth,$jday,$jyear);
					break;
				case "L" :
					list( $tmp_year, $tmp_month, $tmp_day ) = cmfcJalaliDateTime::jalaliToGregorian(1384, 12, 1);
					echo $tmp_day;
					/*if(lastday($tmp_month,$tmp_day,$tmp_year)=="31")
						$result.="1";
					else 
						$result.="0";
						*/
					break;
				default:
					$result.=$subtype;
			}
			$subtypetemp=substr($type,$i,1);
		$i++;
		}
		return $result;
	}
	
	
	/**
	* @desc accept array,timestamp and string as input datetime in jalali or gregorian format
	*/
	function smartGet($type,$value="now",$farsi=1) {
		
        if ($value!='now') {
        	$value=cmfcJalaliDateTime::toTimeStamp($value);
		}
		
		if (empty($value)) return;
		
		return cmfcJalaliDateTime::get($type,$value,$farsi);
	}
	
	
	/**
	* @desc 
	* @todo this function needs to be completed
	*/
	function strtotime($str) {
		return cmfcJalaliDateTime::toTimeStamp($str);
	}
	
	
	/**
	* accept array,timestamp and string as input datetime in jalali 
	* or gregorian format and convert it to timestamp
	*/
	function toTimeStamp($value) {
		if (is_string($value)) {
			if (preg_match('/^([0-9]{2,4})[-\/\\\]([0-9]{1,2})[-\/\\\]([0-9]{1,2})( +([0-9]{1,2})[:]([0-9]{1,2})[:]([0-9]{1,2}))?/', $value, $regs)) {
				$y=$regs['1'];
				$m=$regs['2'];
				$d=$regs['3'];
				$h=$regs['5'];
				$i=$regs['6'];
				$s=$regs['7'];
			}    
		}
		
		if (is_array($value)) {
			if (isset($value[0])) {
				$y=$value['0'];
				$m=$value['1'];
				$d=$value['2'];
				$h=$value['3'];
				$i=$value['4'];
				$s=$value['5'];
			} elseif (isset($value['year'])) {
				$y=$value['year'];
				$m=$value['month'];
				$d=$value['day'];
				$h=$value['hour'];
				$i=$value['minute'];
				$s=$value['second'];
			} elseif (isset($value['y'])) {
				$y=$value['y'];
				$m=$value['m'];
				$d=$value['d'];
				$h=$value['h'];
				$i=$value['i'];
				$s=$value['s'];
			}
		}
		
		if (!empty($y)) {

			$y=intval(strval($y));
			$m=intval(strval($m));
			$d=intval(strval($d));
			$h=intval(strval($h));
			$i=intval(strval($i));
			$s=intval(strval($s));
			
			if ($y<1900) {
				list($y,$m,$d)=cmfcJalaliDateTime::jalaliToGregorian($y,$m,$d);
			}
			if (!empty($h) or $h!=0)           
				$value=strtotime("$y-$m-$d $h:$i:$s");
			else
				$value=strtotime("$y-$m-$d");
		}
		
		return $value;
	}
	
	

	function gregorianToJalali ($g_y, $g_m, $g_d) 
	{
		if ($g_y<1300 or $g_m<1 or $g_d<1) return array('','','');
	    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
	    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);     
	    
	   $gy = $g_y-1600; 
	   $gm = $g_m-1; 
	   $gd = $g_d-1; 

	   $g_day_no = 365*$gy+div($gy+3,4)-div($gy+99,100)+div($gy+399,400); 

	   for ($i=0; $i < $gm; ++$i) 
	      $g_day_no += $g_days_in_month[$i]; 
	   if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0))) 
	      /* leap and after Feb */ 
	      $g_day_no++; 
	   $g_day_no += $gd; 

	   $j_day_no = $g_day_no-79; 

	   $j_np = div($j_day_no, 12053); /* 12053 = 365*33 + 32/4 */ 
	   $j_day_no = $j_day_no % 12053; 

	   $jy = 979+33*$j_np+4*div($j_day_no,1461); /* 1461 = 365*4 + 4/4 */ 

	   $j_day_no %= 1461; 

	   if ($j_day_no >= 366) { 
	      $jy += div($j_day_no-1, 365); 
	      $j_day_no = ($j_day_no-1)%365; 
	   } 

	   for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) 
	      $j_day_no -= $j_days_in_month[$i]; 
	   $jm = $i+1; 
	   $jd = $j_day_no+1; 

	   return array($jy, $jm, $jd); 
	} 

	function jalaliToGregorian($j_y, $j_m, $j_d) 
	{
		$j_d = (int) $j_d;
		$j_m = (int) $j_m;
		$j_y = (int) $j_y;
		
		
		$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
		$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
		
		
		if ($j_d < 1)
			$j_d = 1;
		elseif ($j_d > $j_days_in_month[ $j_m - 1 ])
			$j_d = $j_days_in_month[ $j_m - 1 ];
		
		
		$jy = $j_y-979; 
		$jm = $j_m-1;
		$jd = 0;
		$jd = $j_d - 1; 
		
		$j_day_no = 365*$jy + div($jy, 33)*8 + div($jy%33+3, 4); 
		for ($i=0; $i < $jm; ++$i) 
		  $j_day_no += $j_days_in_month[$i]; 
		
		$j_day_no += $jd; 
		
		$g_day_no = $j_day_no+79; 
		
		$gy = 1600 + 400*div($g_day_no, 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */ 
		$g_day_no = $g_day_no % 146097; 
		
		$leap = true; 
		if ($g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */ 
		{ 
		  $g_day_no--; 
		  $gy += 100*div($g_day_no,  36524); /* 36524 = 365*100 + 100/4 - 100/100 */ 
		  $g_day_no = $g_day_no % 36524; 
		
		  if ($g_day_no >= 365) 
			 $g_day_no++; 
		  else 
			 $leap = false; 
		} 
		
		$gy += 4*div($g_day_no, 1461); /* 1461 = 365*4 + 4/4 */ 
		$g_day_no %= 1461; 
		
		if ($g_day_no >= 366) { 
		  $leap = false; 
		
		  $g_day_no--; 
		  $gy += div($g_day_no, 365); 
		  $g_day_no = $g_day_no % 365; 
		} 
		
		for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++) 
		  $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap); 
		$gm = $i+1; 
		$gd = $g_day_no+1; 
		return array($gy, $gm, $gd); 
	}
	
	///Find num of Day Begining Of Month ( 0 for Sat & 6 for Sun)
	function monthStartDay($month,$day,$year)
	{
		list( $jyear, $jmonth, $jday ) = cmfcJalaliDateTime::gregorianToJalali($year, $month, $day);
		list( $year, $month, $day ) = cmfcJalaliDateTime::jalaliToGregorian($jyear, $jmonth, "1");
		$timestamp=mktime(0,0,0,$month,$day,$year);
		return date("w",$timestamp);
	}
	
	//Find days in this year untile now 
	function yearTotalDays($jmonth,$jday,$jyear)
	{
		$year="";
		$month="";
		$year="";
		$result="";
		if($jmonth=="01")
			return $jday;
		for ($i=1;$i<$jmonth || $i==12;$i++)
		{
			list( $year, $month, $day ) = cmfcJalaliDateTime::jalaliToGregorian($jyear, $i, "1");
			$result+=lastday($month,$day,$year);
		}
		return $result+$jday;
	}
	
	
	//translate number of month to name of month
	function getMonthName($month)
	{

	    if($month=="01") return "&#1601;&#1585;&#1608;&#1585;&#1583;&#1610;&#1606;";

	    if($month=="02") return "&#1575;&#1585;&#1583;&#1610;&#1576;&#1607;&#1588;&#1578;";

	    if($month=="03") return "&#1582;&#1585;&#1583;&#1575;&#1583;";

	    if($month=="04") return  "&#1578;&#1610;&#1585;";

	    if($month=="05") return "&#1605;&#1585;&#1583;&#1575;&#1583;";

	    if($month=="06") return "&#1588;&#1607;&#1585;&#1610;&#1608;&#1585;";

	    if($month=="07") return "&#1605;&#1607;&#1585;";

	    if($month=="08") return "&#1570;&#1576;&#1575;&#1606;";

	    if($month=="09") return "&#1570;&#1584;&#1585;";

	    if($month=="10") return "&#1583;&#1609;";

	    if($month=="11") return "&#1576;&#1607;&#1605;&#1606;";

	    if($month=="12") return "&#1575;&#1587;&#1601;&#1606;&#1583;";
	}

	function getMonthShortName($month)
	{

	    if($month=="01") return "&#1601;&#1585;&#1608;&#1585;&#1583;&#1610;&#1606;";

	    if($month=="02") return "&#1575;&#1585;&#1583;&#1610;&#1576;&#1607;&#1588;&#1578;";

	    if($month=="03") return "&#1582;&#1585;&#1583;&#1575;&#1583;";

	    if($month=="04") return  "&#1578;&#1610;&#1585;";

	    if($month=="05") return "&#1605;&#1585;&#1583;&#1575;&#1583;";

	    if($month=="06") return "&#1588;&#1607;&#1585;&#1610;&#1608;&#1585;";

	    if($month=="07") return "&#1605;&#1607;&#1585;";

	    if($month=="08") return "&#1570;&#1576;&#1575;&#1606;";

	    if($month=="09") return "&#1570;&#1584;&#1585;";

	    if($month=="10") return "&#1583;&#1609;";

	    if($month=="11") return "&#1576;&#1607;&#1605;&#1606;";

	    if($month=="12") return "&#1575;&#1587;&#1601;&#1606;&#1583;";
	    if($month=="12") return "&#1575;&#1587;&#1601; ";
	}
	
	
	function isKabise($year)
	{
		if($year%4==0 && $year%100!=0)
			return true;
		return false;
	}
	
	function time()
	{
		return mktime()	;
	}
	
	function makeTime($hour="",$minute="",$second="",$jmonth="",$jday="",$jyear="")
	{
		if(!$hour && !$minute && !$second && !$jmonth && !$jmonth && !$jday && !$jyear)
			return mktime();
		list( $year, $month, $day ) = cmfcJalaliDateTime::jalaliToGregorian($jyear, $jmonth, $jday);
		$i=mktime($hour,$minute,$second,$month,$day,$year);	
		return $i;
	}
	
	
	function isDateValid($month,$day,$year) {
		$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
		if($month<=12 && $month>0)
		{
			if($j_days_in_month[$month-1]>=$day && 	$day>0)
				return 1;
			if(is_kabise($year))
				echo "Asdsd";
			if(is_kabise($year) && $j_days_in_month[$month-1]==31)
				return 1;
		}
		
		return 0;
			
	}
	
	
	function dateDiff($first,$second) {
		$first_date = explode("-",$first);
		$first_date = mktime(0, 0, 0, $first_date[1],$first_date[2], $first_date[0]);
		//echo $first_date[1];
		$second_date = explode("-",$second);
		$second_date = mktime(0, 0, 0,$second_date[1],$second_date[2], $second_date[0]);
		$totalsec=$second_date- $first_date;
		return $totalday = round(($totalsec/86400));
	}
	
	
	function dateTimeDiff($first_timestamp,$second_timestamp)
	{
		// Author: Tone.
		// Date    : 15-12-2003.
		
		// Ref: Dates go in "2003-12-31".
		// Ref: Times go in "12:59:13".
		// Ref: mktime(HOUR,MIN,SEC,MONTH,DAY,YEAR).
		
		// Splits the dates into parts, to be reformatted for mktime.
	//	$first_datetime = getdate($first_datetime);
	//	$second_datetime = getdate($second_datetime);
		
		// makes the dates and times into unix timestamps.
		// $first_unix  = mktime($first_datetime['hours'], $first_time_ex[1], $first_time_ex[2], $first_date_ex[1], $first_date_ex[2], $first_date_ex[0]);
		// $second_unix  = mktime($second_time_ex[0], $second_time_ex[1], $second_time_ex[2], $second_date_ex[1], $second_date_ex[2], $second_date_ex[0]);
		// Gets the difference between the two unix timestamps.
		if (empty($first_timestamp) or $first_timestamp<0 or empty($second_timestamp) or $second_timestamp<0)
			return false;
		$timediff = $first_timestamp-$second_timestamp;
				   
		// Works out the days, hours, mins and secs.
		$days=intval($timediff/86400);
		$remain=$timediff%86400;
		$hours=intval($remain/3600);
		$remain=$remain%3600;
		$mins=intval($remain/60);
		$secs=$remain%60;
		// Returns a pre-formatted string. Can be chagned to an array.
		$result['days']=$days;
		$result['hours']=$hours;
		$result['minutes']=$mins;
		return $result;
	}
	
	
	
	/**
	* convert seconds to days,hours,minuts,seconds as array
	* @param integer $seconds
	* @return array
	*/
	function secondsToDays($seconds) {
		$days=intval($seconds/86400);
		$remain=$seconds%86400;
		$hours=intval($remain/3600);
		$remain=$remain%3600;
		$mins=intval($remain/60);
		$secs=$remain%60;
		$r=array(
			'days'=>$days,
			'hours'=>$hours,
			'minutes'=>$mins,
			'seconds'=>$secs
		);
		return $r;
	}
	
	
	
	/**
	* @author 
	* Find Number Of Days In This Month
	*/
	function monthTotalDays($month,$day,$year)
	{
		$jday2="";
		$jdate2 ="";
		$lastdayen=date("d",mktime(0,0,0,$month+1,0,$year));
		list( $jyear, $jmonth, $jday ) = cmfcDateTime::gregorianToJalali($year, $month, $day);
		$lastdatep=$jday;
		$jday=$jday2;
		while($jday2!="1")
		{
			if($day<$lastdayen)
			{
				$day++;
				list( $jyear, $jmonth, $jday2 ) = cmfcDateTime::gregorianToJalali($year, $month, $day);
				if($jdate2=="1") break;
				if($jdate2!="1") $lastdatep++;
			}
			else
			{ 
				$day=0;
				$month++;
				if($month==13) 
				{
						$month="1";
						$year++;
				}
			}

		}
		return $lastdatep-1;
	}
	
		
	/**
	* @author  Arash Dalir
	* Find Number Of Days In This Month
	*/
	function daysInMonth($monthId, $ctype = 'gregorian'){
		$daysInMonth = array(
			'jalali' => array(
				'31', 
				'31',
				'31',
				'31',
				'31',
				'31',
				'30',
				'30',
				'30',
				'30',
				'30',
				'29'
			),
			'gregorian' => array(
				'31', 
				'28',
				'31',
				'30',
				'31',
				'30',
				'31',
				'31',
				'30',
				'31',
				'30',
				'31'
			)
		);
		return $daysInMonth[$ctype][$monthId];
	}

}


class cmfcGregorianDateTime {

	/**
	* @previousNames strDateTimeToArray,cmfStrDateTimeToArray
	*/
	function strDateTimeToArray($datetime_string,$asTimeStamp=false) {
	        $datetime_string=trim($datetime_string);
	        $timestamp=strtotime($datetime_string);
	        if ($timestamp===-1) {
	                if (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(\\+[0-9]{2}:[0-9]{2})/im', $datetime_string, $matches)) {
	                        //Sample : 2006-08-11T12:55:30+03:30
	                        $datetime_string="$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6] ".str_replace(':','',$matches[7]);
	                } elseif(preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z/im', $datetime_string, $matches)) {
	                        //Sample : 2006-08-14T00:14:20Z
	                        $datetime_string="$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6]";
	                }
	                $timestamp=strtotime(trim($datetime_string));
	        }

	        if ($timestamp!==-1) {
	                if ($asTimeStamp)
	                        return $timestamp;
	                else
	                        return getdate($timestamp);
	        } else
	                return false;
	}

	/**
	* @previousNames strDateTimeToTimeStamp,cmfStrDateTimeToTimeStamp
	*/
	function strDateTimeToTimeStamp($datetime_string) {
	   return cmfStrDateTimeToArray($datetime_string,true);
	}


	/**
	* @previousNames safestrtotime,cmfSafeStrToTime
	*/
	function safeStrToTime($strInput)
	{
	        $iVal = -1;
	        for ($i=1900; $i<=1969; $i++)
	        {
	                // Check for this year string in date
	                $strYear = (string)$i;
	                if (!(strpos($strInput, $strYear)===false))
	                {
	                        $replYear = $strYear;
	                        $yearSkew = 1970 - $i;
	                        $strInput = str_replace($strYear, '1970', $strInput);
	                }
	        }
	        $iVal = strtotime($strInput);
	        if ($yearSkew> 0)
	        {
	                $numSecs = (60 * 60 * 24 * 365 * $yearSkew);
	                $iVal = $iVal - $numSecs;
	                $numLeapYears = 0;  // determine number of leap years in period
	                for ($j=$replYear; $j<=1969; $j++)
	                {
	                        $thisYear = $j;
	                        $isLeapYear = false;
	                        // Is div by 4?
	                        if (($thisYear % 4) == 0)
	                        {
	                                $isLeapYear = true;
	                        }
	                        // Is div by 100?
	                        if (($thisYear % 100) == 0)
	                        {
	                                $isLeapYear = false;
	                        }
	                        // Is div by 1000?
	                        if (($thisYear % 1000) == 0)
	                        {
	                                $isLeapYear = true;
	                        }
	                        if ($isLeapYear == true)
	                        {
	                                $numLeapYears++;
	                        }
	                }
	                $iVal = $iVal - (60 * 60 * 24 * $numLeapYears);
	        }
	        return $iVal;
	}

	function dateDiff($first,$second) {
		$first_date = explode("-",$first);
		$first_date = mktime(0, 0, 0, $first_date[1],$first_date[2], $first_date[0]);
		//echo $first_date[1];
		$second_date = explode("-",$second);
		$second_date = mktime(0, 0, 0,$second_date[1],$second_date[2], $second_date[0]);
		$totalsec=$second_date- $first_date;
		return $totalday = round(($totalsec/86400));
	}
	
	
	function dateTimeDiff($first_timestamp,$second_timestamp)
	{
		// Author: Tone.
		// Date    : 15-12-2003.
		
		// Ref: Dates go in "2003-12-31".
		// Ref: Times go in "12:59:13".
		// Ref: mktime(HOUR,MIN,SEC,MONTH,DAY,YEAR).
		
		// Splits the dates into parts, to be reformatted for mktime.
	//	$first_datetime = getdate($first_datetime);
	//	$second_datetime = getdate($second_datetime);
		
		// makes the dates and times into unix timestamps.
		// $first_unix  = mktime($first_datetime['hours'], $first_time_ex[1], $first_time_ex[2], $first_date_ex[1], $first_date_ex[2], $first_date_ex[0]);
		// $second_unix  = mktime($second_time_ex[0], $second_time_ex[1], $second_time_ex[2], $second_date_ex[1], $second_date_ex[2], $second_date_ex[0]);
		// Gets the difference between the two unix timestamps.
		if (empty($first_timestamp) or $first_timestamp<0 or empty($second_timestamp) or $second_timestamp<0)
			return false;
		$timediff = $first_timestamp-$second_timestamp;
				   
		// Works out the days, hours, mins and secs.
		$days=intval($timediff/86400);
		$remain=$timediff%86400;
		$hours=intval($remain/3600);
		$remain=$remain%3600;
		$mins=intval($remain/60);
		$secs=$remain%60;
		// Returns a pre-formatted string. Can be chagned to an array.
		$result['days']=$days;
		$result['hours']=$hours;
		$result['minutes']=$mins;
		return $result;
	}
	
	

	function jGetDate($timestamp="",$transNumber=1)
	{
		if($timestamp=="")
			$timestamp=mktime();

		return array(
			0=>$timestamp,	
			"seconds"=>jdate("s",$timestamp,$transNumber),
			"minutes"=>jdate("i",$timestamp,$transNumber),
			"hours"=>jdate("G",$timestamp,$transNumber),
			"mday"=>jdate("j",$timestamp,$transNumber),
			"wday"=>jdate("w",$timestamp,$transNumber),
			"mon"=>jdate("n",$timestamp,$transNumber),
			"year"=>jdate("Y",$timestamp,$transNumber),
			"yday"=>cmfcDateTime::yearTotalDays(jdate("m",$timestamp,$transNumber),jdate("d",$timestamp,$transNumber),jdate("Y",$timestamp,$transNumber)),
			"weekday"=>jdate("l",$timestamp,$transNumber),		
			"month"=>jdate("F",$timestamp,$transNumber),
		);
	}

	function getMonthName($month)
	{

	    if($month=="01") return "January";

	    if($month=="02") return "February";

	    if($month=="03") return "March";

	    if($month=="04") return  "April";

	    if($month=="05") return "May";

	    if($month=="06") return "June";

	    if($month=="07") return "July";

	    if($month=="08") return "August";

	    if($month=="09") return "September";

	    if($month=="10") return "October";

	    if($month=="11") return "November";

	    if($month=="12") return "December";
	}

	function change_to_miladi($date)
	{
	  $date = explode("-",$date);
	  $date = cmfcDateTime::jalaliToGregorian($date[0],$date[1],$date[2]);
	  //$date[1] = $date[1] -1;
	  $date = $date[0]."-".$date[1]."-15";
	  return $date;
	}


	function date_fa($date) {
	   list($year, $month, $day) = preg_split ( '/-/', $date);
	   list($jyear, $jmonth, $jday) = cmfcDateTime::gregorianToJalali($year, $month, $day);
	   $date = jmaketime(0,0,0,$jmonth,$jday,$jyear) ;
	   $date = jdate("d M Y",$date) ;
	   return $date;
	}

	function date_en($date) {
	  $date = explode("-",$date);
	  $date = date("F j, Y",mktime(0, 0, 0, $date[1], $date[2],$date[0]));
	  return $date;
	}


	function dateByLanguage($format,$time_stamp,$lang) {
		if ($lang=='fa') {
			return cmfcJalaliDateTime::smartGet($format,$time_stamp,1);
		} else {
			return date($format,cmfcJalaliDateTime::toTimeStamp($time_stamp));
		}
	}



}