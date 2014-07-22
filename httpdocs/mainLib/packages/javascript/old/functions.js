function showElement(element_id) {
	var control=document.getElementById(element_id);
	control.style.display='';
}

function hideElement(element_id) {
	var control=document.getElementById(element_id);
	control.style.display='none';
}

function showHideElement(element_id) {
	var control=document.getElementById(element_id);

	if (control.style.display!='none') {
		control.style.display='none';
	} else {
		control.style.display='block';
	}
	return false;
}

function slideUpAndDown(element_id) {
	var control=document.getElementById(element_id);

	if (control)
	if (control.style.display!='none') {
		Effect.SlideUp(element_id);
		//control.style.display='none';
	} else {
//		alert(element_id);
		Effect.SlideDown(element_id);
	}

	return false;
	
}

//--(BEGIN)--> Loading Bar Section
// Browser Window Size and Position
// copyright Stephen Chapman, 3rd Jan 2005, 8th Dec 2005
// you may copy these functions but please keep the copyright notice as well
function pageWidth() {return window.innerWidth != null? window.innerWidth: document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth:document.body != null? document.body.clientWidth:null;}
function pageHeight() {return window.innerHeight != null? window.innerHeight: document.documentElement && document.documentElement.clientHeight ? document.documentElement.clientHeight:document.body != null? document.body.clientHeight:null;}
function posLeft() {return typeof window.pageXOffset != 'undefined' ? window.pageXOffset:document.documentElement && document.documentElement.scrollLeft? document.documentElement.scrollLeft:document.body.scrollLeft? document.body.scrollLeft:0;}
function posTop() {return typeof window.pageYOffset != 'undefined' ? window.pageYOffset:document.documentElement && document.documentElement.scrollTop? document.documentElement.scrollTop: document.body.scrollTop?document.body.scrollTop:0;}
function posRight() {return posLeft()+pageWidth();}
function posBottom() {return posTop()+pageHeight();}

//-->


function addCommas(nStr)
{
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


function isInArray(needle,haystack)
{
	var key
	for (key in haystack)
	{
		if (needle==key) {return true} 
	}
	return false;
}

function loadingBarMove(id_name) {
	var x = (posLeft()+10) + 'px';
	var y = (posTop()+10) + 'px';
	
	var control=document.getElementById(id_name);
	control.style.left=x;
	control.style.top=y;
}

function hideLoadingBar(id_name) {
	var control=document.getElementById(id_name);
	control.style.display='none';
}

function showLoadingBar(id_name) {
	loading_bar_move();
	var control=document.getElementById(id_name);
	control.style.display='block';
}

//window.onscroll = loadingBarMove('loadingBar');
//--(END)--> Loading Bar Section

function popitup(url,width,height,show_type)
{
	var left = (screen.width-width)/2;
	var top = (screen.height-height)/2;
	if (left < 0) left = 0;
	if (top < 0) top = 0;
	var window_attr='height='+height+',width='+width+',left='+left+',top='+top+',status=1,scrollbars=1,menubar=1';
	if (show_type=='full') window_attr='height='+height+',width='+width+',left='+left+',top='+top+','+'status=1,scrollbars=1,menubar=1,resizable=1';
	newwindow=window.open(url,'name',window_attr);
	if (window.focus) {newwindow.focus()}
	return false;
}

function replace(s, t, u) {
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
	r += replace(s.substring(i + t.length, s.length), t, u);
  return r;
}


function confimationMessage(message)
{
	if(confirm(message)) { return true; }
	else {return false;}
}


function checkContentHeight() {
	ch = document.getElementById('content');
	document.getElementById('footer').style.top = ch.scrollHeight + "px";
	if (ch.scrollHeight > 1200) {
		
	}
	return;
}


/*
document.all isn't a cross-browser property, this function is solution

var all = getElements(document);
for (var e = 0; e < all.length; e++) {
	var element=all[e];
	if (element.type=='checkbox') {
		document.getElementById(element.id).checked=true;
	}
}
*/
function getElements(doc_obj) 
{
	if (doc_obj==null) {doc_obj=document;}
	var all = doc_obj.all ? doc_obj.all :
			doc_obj.getElementsByTagName('*');
	var elements = new Array();
	for (var e = 0; e < all.length; e++)
			elements[elements.length] = all[e];
	return elements;
}


function getElementsByName(doc_obj) 
{
	if (doc_obj==null) {doc_obj=document;}
	var all = doc_obj.all ? doc_obj.all :
			doc_obj.getElementsByTagName('*');
	var elements = new Array();
	for (var e = 0; e < all.length; e++)
	{
		elements[all[e].name] = all[e];
	}
	return elements;
}




//---------------- SAMPLE FUNCTION ------------------------


/*
	function OpenComments (c) {
		var widthVal=580;
		var heightVal=400;
		var leftVal = (screen.width-widthVal) / 2;
		var topVal = (screen.height-heightVal) / 2;
//		window.open(c,'comments','width=480,height=300,scrollbars=yes,status=yes');
		window.open(c,'comments','left='+leftVal+',top='+topVal+',width='+widthVal+',height='+heightVal+',scrollbars=yes,status=yes,resizable=1');
	}
	
	function show_radio_buttons(uid)
	{
		var clients_value=document.getElementById('clients_value').innerHTML;
		var clients=document.getElementById('clients').value;
		var clients=clients.split(",");
		var clients_value=clients_value.split(" , ");
//		alert(clients_value);
		var fields='';
		var checked='';
		var field_area = document.getElementById('clients_value');
		field_area.innerHTML='';
		for (var i=0; i<clients.length; i++)
		{
			if (clients[i]==uid) {checked='checked'} else {checked=''}
			if (clients[i]!='')
			{
				if(document.createElement) { //W3C Dom method.
					var span = document.createElement("span");
					var input = document.createElement("input");
					//input.id = field+count;
					input.value = clients[i];
					input.name = 'owner_id';
					input.id='owner_id'+clients[i];
					input.checked = true;
					input.type = "radio"; //Type of field - can be any valid input type like text,file,checkbox etc.
					span.appendChild(input);
					span.innerHTML+=clients_value[i-1]+'<br/>';
					field_area.appendChild(span);
					document.getElementById('owner_id'+clients[i]).checked=checked;
				} else { //Older Method
					field_area.innerHTML +='<input type="radio" name="owner_id" value="'+clients[i]+'" '+checked+'/>&nbsp;'+clients_value[i-1]+'<br/>';
				}

			}
				
		}
//		document.getElementById('clients_value').innerHTML=fields;
	}
	
	*/
	
	/*
<script>
	function getElements(doc_obj) 
	{
		if (doc_obj==null) {doc_obj=document;}
		var all = doc_obj.all ? doc_obj.all :
				doc_obj.getElementsByTagName('*');
		var elements = new Array();
		for (var e = 0; e < all.length; e++)
				elements[elements.length] = all[e];
		return elements;
	}


function replace(s, t, u) {
  //
  //  Replace a token in a string
  //    s  string to be processed
  //    t  token to be found and removed
  //   u  token to be inserted
  /  returns new String
  //
  i = s.indexOf(t);
  r = "";
  if (i == -1) return s;
  r += s.substring(0,i) + u;
  if ( i + t.length < s.length)
    r += replace(s.substring(i + t.length, s.length), t, u);
  return r;
}

	var backup_elements=new Array(2);

	function onSelectParentDropDown(parent_id,child_id)
	{
		var parent=document.getElementById(parent_id);
		var child=document.getElementById(child_id);
		var display='none';
		var child_value=document.getElementById(child_id).value;

		if (document.all) {
			if (!backup_elements[child_id]) {
				backup_elements[child_id]=document.getElementById(child_id).outerHTML;
			} else {
				//document.getElementById(child_id).outerHTML=backup_elements[child_id];
			}
	
			var regex = new RegExp("<OPTGROUP title="+parent.value+" label=[^<>]*>( *<OPTION [^<>]*>[^<>]*</OPTION> *)*</OPTGROUP>");
			var match = regex.exec(backup_elements[child_id]);
//			alert(match);
			match='<SELECT class="field" id="'+child_id+'" name="'+child_id+'"><option value=" ">&nbsp;</option>'+match+'</SELECT>';
			
			regex = new RegExp(".*</OPTGROUP>");
			var match = regex.exec(match)+'</SELECT>';
			//alert(match);
			match=replace(match,'VALUE="'+child_value+'"','VALUE="'+child_value+'" SELECTED');
			document.getElementById(child_id).outerHTML=match;
		} else {
			child.selectedIndex=null;
			items = child.getElementsByTagName('optgroup');
	
			for (var j = 0; j < items.length; j++)
			{
				if (parent.value==items[j].title) {
					display='block';
				} else {
					display='none';
				}
				items[j].style.display = display;
			}
			child.value=child_value;
		}	
	}

	*/
