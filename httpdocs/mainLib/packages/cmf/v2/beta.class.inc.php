<?php
/**
 * 
 * @package cmf
 * @subpackage beta
 * @author Sina Salek
 * @version $Id: beta.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
 */
 
/**
* 
*/
class cmfcCoreBeta {
	
	var $info=array(
		'packages'=>array(
			'base'=>array(
				'file'=>'base.class.inc.php',
				'className'=>''
			),
			'commonClasses'=>array(
				'file'=>'common.class.inc.php',
				'className'=>''
			),
			'tableClassesBase'=>array(
				'file'=>'tableClassesBase.class.inc.php',
				'className'=>''
			),
			'common'=>array(
				'file'=>'common.inc.php',
				'className'=>''
			),
			'compatibility'=>array(
				'file'=>'compatibility.inc.php',
				'className'=>''
			),
			'constants'=>array(
				'file'=>'consts.inc.php',
				'className'=>''
			),
			'datetime'=>array(
				'file'=>'datetime.class.inc.php',
				'className'=>''
			),
			'mysql'=>array(
				'file'=>'mysql.class.inc.php',
				'className'=>''
			),
			'utf8'=>array(
				'file'=>'utf8.php',
				'className'=>''
			)
	)
	
	/**
	* @desc
	* @example : $coreVersion='beta' ;$packages=array('mysql','string','utf8') , 
	*/
	function include($packages) {
		foreach ($packages as $packageName) {
			if (in_array($packageName,$this->info['packages'])) {
				//if (class_exists($this->info['packages'][$packageName]['className']]))
				require(dirname(__FILE__).'/'.$this->info['packages'][$packageName]['file']);
			} else {
				trigger_error("package '$packageName' does not exist in CMFC Core Beta",E_USER_ERROR);
			}
		}
	}
}