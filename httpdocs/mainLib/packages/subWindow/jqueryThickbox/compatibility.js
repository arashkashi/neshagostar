/**
* Provides compatibility functions for moving from Greybox to Thickbox
*/

if (!window.GB_showCenter) {
	function GB_showCenter(title, url, width, height) {
		tb_show(title, url+'&TB_iframe=true&height='+height+'&width='+width+'');
		return false;
	}
	
	function GB_hide() {
		tb_remove();
	}
}