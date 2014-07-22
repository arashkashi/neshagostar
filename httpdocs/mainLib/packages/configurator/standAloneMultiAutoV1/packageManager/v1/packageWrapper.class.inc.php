<?php
class cmfcPackageManagerV1Wrapper {
	var $_packageInfo=array();
	
	/**
	 * 
	 * @param $file //Relative path
	 * @return unknown_type
	 */
	function packageGetFilePath($file) {
		return $this->_packageInfo['folderPath'].'/'.$file;
	}
	
	/**
	 * 
	 * @param $file //Relative path
	 * @return unknown_type
	 */
	function packageGetFilePathInBrowser($file) {
		return $this->_packageInfo['folderPathBrowser'].'/'.$file;
	}
	
	/**
	 * 
	 * @param $file //Relative path
	 * @return unknown_type
	 */
	function packageGetFolderPath() {
		return $this->_packageInfo['folderPath'];
	}
	
	/**
	 * 
	 * @param $file //Relative path
	 * @return unknown_type
	 */
	function packageGetFolderPathFull() {
		return $this->_packageInfo['folderPathFull'];
	}
	
	/**
	 * 
	 * @param $file //Relative path
	 * @return unknown_type
	 */
	function packageGetFolderPathInBrowser() {
		return $this->_packageInfo['folderPathBrowser'];
	}
}