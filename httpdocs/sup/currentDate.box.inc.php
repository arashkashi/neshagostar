<div id="currentDateTime" align="<?php echo $translation->languageInfo['align']?>" style="padding:4px; display:none">
<img src="interface/images/icon_date.gif"  align="middle" alt="" />
	<span id="currentDateTime_day" class="timer"></span> 
	<span id="currentDateTime_month" class="timer"></span> 
	<span id="currentDateTime_year" class="timer"></span> - 
	<span dir="ltr">
		<span id="currentDateTime_hour" class="timer"></span>:
		<span id="currentDateTime_minute" class="timer" ></span>:
		<span id="currentDateTime_second" class="timer" ></span>
	</span>
<input id="currentDateTime_firstTime" type="hidden" value="1" />

<script language="javascript" type="text/javascript">
	//<![CDATA[

	<!--
	// please keep these lines on when you copy the source
	// made by: Nicolas - http://www.javascript-page.com
	//۱۲۳۴۵۶۷۸۹۰
	var clockID = 0;
	numbers = new Array();
	<?php 	if ($translation->languageInfo['calendarType'] == 'jalali'){
		?>
		numbers[1] = '۱';
		numbers[2] = '۲';
		numbers[3] = '۳';
		numbers[4] = '۴';
		numbers[5] = '۵';
		numbers[6] = '۶';
		numbers[7] = '۷';
		numbers[8] = '۸';
		numbers[9] = '۹';
		numbers[0] = '۰';
		<?php 	}
	else{
		?>
		numbers[1] = '1';
		numbers[2] = '2';
		numbers[3] = '3';
		numbers[4] = '4';
		numbers[5] = '5';
		numbers[6] = '6';
		numbers[7] = '7';
		numbers[8] = '8';
		numbers[9] = '9';
		numbers[0] = '0';
		<?php 	}
	?>
	
	monthNames = new Array();
	
	<?php 	if ($translation->languageInfo['calendarType'] == 'jalali'){
		?>
		monthNames[1] = 'فروردین';
		monthNames[2] = 'اردیبهشت';
		monthNames[3] = 'خرداد';
		monthNames[4] = 'تیر';
		monthNames[5] = 'مرداد';
		monthNames[6] = 'شهریور';
		monthNames[7] = 'مهر';
		monthNames[8] = 'آبان';
		monthNames[9] = 'آذر';
		monthNames[10] = 'دی';
		monthNames[11] = 'بهمن';
		monthNames[12] = 'اسفند';
		<?php 	}
	else{
		?>
		monthNames[1] = 'January';
		monthNames[2] = 'February';
		monthNames[3] = 'March';
		monthNames[4] = 'April';
		monthNames[5] = 'May';
		monthNames[6] = 'June';
		monthNames[7] = 'July';
		monthNames[8] = 'August';
		monthNames[9] = 'September';
		monthNames[10] = 'October';
		monthNames[11] = 'November';
		monthNames[12] = 'December';
		<?php 	}
	?>
	
	var years = 0;
	var months = 0;
	var days = 0;
	var hours = 0;
	var minutes = 0;
	var seconds = 0;
	
	daysInMonths = new Array();
	<?php 	if ($translation->languageInfo['calendarType'] == 'jalali'){
		?>
		daysInMonths[1] = 31;
		daysInMonths[2] = 31;
		daysInMonths[3] = 31;
		daysInMonths[4] = 31;
		daysInMonths[5] = 31;
		daysInMonths[6] = 31;
		daysInMonths[7] = 30;
		daysInMonths[8] = 30;
		daysInMonths[9] = 30;
		daysInMonths[10] = 30;
		daysInMonths[11] = 30;
		daysInMonths[12] = 29;
		<?php 	}
	else{
		?>
		daysInMonths[1] = 31;
		daysInMonths[2] = 28;
		daysInMonths[3] = 31;
		daysInMonths[4] = 30;
		daysInMonths[5] = 31;
		daysInMonths[6] = 30;
		daysInMonths[7] = 31;
		daysInMonths[8] = 31;
		daysInMonths[9] = 30;
		daysInMonths[10] = 31;
		daysInMonths[11] = 30;
		daysInMonths[12] = 31;
		<?php 	}
	?>
	
	function UpdateClock() {
		containerElm = document.getElementById('currentDateTime');
		hourElm = document.getElementById('currentDateTime_hour');
		minuteElm = document.getElementById('currentDateTime_minute');
		secondElm = document.getElementById('currentDateTime_second');
		yearElm = document.getElementById('currentDateTime_year');
		monthElm = document.getElementById('currentDateTime_month');
		dayElm = document.getElementById('currentDateTime_day');
		firstTimeElm = document.getElementById('currentDateTime_firstTime');
		
		if(clockID) {
		  clearTimeout(clockID);
		  clockID  = 0;
		}
		
		timeDiv = document.getElementById('time');
		tDate = new Date();
		
		if (firstTimeElm){
			seconds = tDate.getSeconds();
			minutes = tDate.getMinutes();
			hours = tDate.getHours() ;
			months = '<?php echo wsfGetDateTime('m', 'now', $translation->languageInfo['shortName'], 0)?>';
			days = '<?php echo wsfGetDateTime('d', 'now', $translation->languageInfo['shortName'], 0)?>';
			years = '<?php echo wsfGetDateTime('Y', 'now', $translation->languageInfo['shortName'], 0)?>';
			firsTimeElm = 0;
		}
		AdjustTime();
		
		yearElm.innerHTML = ReplaceNumbers(years);
		monthElm.innerHTML = ReplaceMonths(months);
		dayElm.innerHTML = ReplaceNumbers(days);
		hourElm.innerHTML = ReplaceNumbers(hours);
		minuteElm.innerHTML = ReplaceNumbers(minutes);
		secondElm.innerHTML = ReplaceNumbers(seconds);
		containerElm.style.display = '';
		
		clockID = setTimeout("UpdateClock()", 1000);
	}
	
	function AdjustTime(){
		seconds = parseInt(seconds, 10);
		mitnues = parseInt(minutes, 10);
		hours = parseInt(hours, 10);
		days = parseInt(days, 10);
		months = parseInt(months, 10);
		years = parseInt(years, 10);
		
		if (seconds > 59){
			seconds = 1;
			minutes++;
		}
		if (seconds < 10)
			seconds = '0' + seconds;
		
		if (minutes > 59){
			minutes = 1;
			hours ++;
		}
		if (minutes < 10)
			minutes = '0' + minutes;
		
		if (hours > 23){
			hours = 0;
			days++;
		}
		if (hours < 10)
			hours = '0' + hours;
		
		if (days > daysInMonths[ months ]){
			days = 1;
			months++;
		}
	
		if (days < 10)
			days = '0' + days;
		
		if (months > 12){
			months = 1;
			years++;
		}
	}
	
	function ReplaceMonths(value){
		array = monthNames;
		//alert(array);
		value = parseInt(value);
		value = array[ value ];
		return value;
	}
	
	function ReplaceNumbers(value){
		array = numbers;
		//alert(array);
		var newValue='';
		value = value.toString();
		//alert(value.len
		for ( var i = 0; i< value.length; i++){
			newValue += array[ parseInt(value.charAt(i)) ];
		}
		return newValue;
	}
	UpdateClock();
	//-->
	//]]>
	</script>
</div>
