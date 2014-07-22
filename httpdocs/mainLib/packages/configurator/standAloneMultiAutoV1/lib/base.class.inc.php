<?php class cmfcConfiguratorStandAloneMultiAutoV1Base {
	var $_options;
	var $_defaultError=CMF_Error;
	var $_messagesValue=array(
		CMF_ClassesCore_Ok		=> 'no error',
		CMF_ClassesCore_Error=>'unknown error'
	);
	var $_errorsStack=array();
	

	/**
	 * there is no __construct function in php4 or down , so this function is solution , now it's possible 
	 * for all chid of this base class to have __construct functions
	 * 
	 */
	function cmfcConfiguratorStandAloneMultiAutoV1Base() {
		//$this->PEAR(get_class($this));
		$args = func_get_args();
		if (is_callable(array(&$this, "__construct")))
			call_user_func_array(array(&$this, "__construct"), $args);
	}
	

	
	function setOptions($options,$merge=false) {
		foreach ($options as $name=>$value) {
			$r=$this->setOption($name,$value,$merge);
			//if (PEAR::isError($r))
				//return $r;
		}
	}
	
	function setOption($name,$value,$merge=false) {
		if ($name=='storage') {
			$r=&$this->setStorage(&$value);
		} elseif ($name=='storage') {
			$r=&$this->setLog(&$value);
		} elseif (is_array($value) and $merge==true) {
			$this->{'_'.$name}=&$this->cmfcArray_mergeRecursive($this->{'_'.$name},$value);
		} else {
			$this->{'_'.$name}=&$value;
		}
		$this->_options[$name]=&$value;
		return $r;
	}
		
	/**
	* works fine in both php4 & 5. but you should use & when you call the function. $b=&$ins->getOption('property')
	*/
	function &getOption($name) {
		return $this->{'_'.$name};
	}
		
	
	function getMessageValue($msgCode,$parameters=null) {
		if (isset($this->_messagesValue[$msgCode]))	
			$message=$this->_messagesValue[$msgCode];
		else
			$message=$this->_messagesValue[$this->_defaultError];
		if (is_array($parameters))
			$message=sprintf($message,$parameters);
		return $message;
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
				$list = array_merge($list ,$this->scanRecursive($f));   //RECURSIVE CALL
			}    
		}
		@closedir($directory);  
		return $list ;
	}


	function arrayMergeRecursive($paArray1, $paArray2)
	{
	   if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
	   foreach ($paArray2 as $sKey2 => $sValue2)
	   {
		   $paArray1[$sKey2] = $this->arrayMergeRecursive(@$paArray1[$sKey2], $sValue2);
	   }
	   return $paArray1;
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
	
	
	function versionCompare($version1,$version2,$operand) {
		$v1Parts=explode('.',$version1);
		$version1.=str_repeat('.0',3-count($v1Parts));
		$v2Parts=explode('.',$version2);
		$version2.=str_repeat('.0',3-count($v2Parts));
		$version1=str_replace('.x','.1000',$version1);
		$version2=str_replace('.x','.1000',$version2);		
		return version_compare($version1,$version2,$operand);
	}
	
	
    /**
     * conditionally includes PEAR base class and raise an error
     * @example
     * <code>
     * 		return $this->raiseError('', CMF_Language_Error_Unknown_Short_Name,
	 *						PEAR_ERROR_RETURN,NULL, 
	 *						array('shortName'=>$shortName)
	 *		);
     * </code>
     * @param string $msg  Error message
     * @param int    $code Error code
     * @access private
     */
	function raiseError($message = null, $code = null, $mode = null, $options = null,
                         $userinfo = null, $error_class = null, $skipmsg = false) {
		if (isset($this->_messagesValue[$code]) && empty($message))
			$message=$this->_messagesValue[$code];
			
		if (is_array($userinfo) && !empty($message)) {
			if (is_array($userinfo))
			foreach ($userinfo as $key=>$value) {
				$replacements['%'.$key.'%']=$value;
			}
			$message=$this->cmfcString_replaceVariables($replacements,$message);
		}
		return PEAR::raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
	}
	
	
	function isError($obj,$code=null) {
		return PEAR::isError($obj,$code);
	}
	
	
	function cmfcDirectory_getContentsAsSingleLevelArray2 ($options,$directorylist=array()) {		
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
							if (in_array('folder',$options['includeInResult'])) {
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
							    $list2 = $this->cmfcDirectory_getContentsAsSingleLevelArray2($subOptions,&$directorylist);
							    if(is_array($list2)) {
							        $directorylist = $this->cmfcPhp4_array_merge($directorylist, $list2);
							    }
							}
						} else {
							if (in_array('file',$options['includeInResult'])) {
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
	
	
	function cmfcPhp4_array_merge() {
		$args = func_get_args();
		foreach ($args as $k=>$value) {
			if (!is_array($value)) $value=array();
			$args[$k]=$value;
		}
		return call_user_func_array("array_merge", $args);
	}
	
	function cmfcHtml_printr($var, $echo = true){
		if (!$var)
			$var = 'Empty/False/Null';

		if ($echo){
			echo '<pre dir="ltr" style="text-align:left; background:#FFCC99; color:#000000; font-family:Verdana, Arial, Helvetica, sans-serif; overflow:auto">'.print_r($var, true).'</pre>';
		} else return print_r($var, true);
	}
	
	/*
	$replacements['00username00']='jafar gholi';
	*/
	//last name : replace_variables
	function cmfcString_replaceVariables($replacements,$text) {
		foreach ($replacements as $needle=>$replacement) {
			$text=str_replace($needle,$replacement,$text);
		}
		return $text;
	}
	
	
	/* For PHP 4 but does not understand []
	 * http://mach13.com/loose-and-multiline-parse_ini_file-function-in-php
	 * @
	 */
	function parseIniFile($iIniFile)
	{
		$aResult  =
		$aMatches = array();
	
		$a = &$aResult;
		$s = '\s*([[:alnum:]_\- \*]+?)\s*';	preg_match_all('#^\s*((\['.$s.'\])|(("?)'.$s.'\\5\s*=\s*("?)(.*?)\\7))\s*(;[^\n]*?)?$#ms', @file_get_contents($iIniFile), $aMatches, PREG_SET_ORDER);
	
		foreach ($aMatches as $aMatch)
			{
			if (empty($aMatch[2]))
					$a [$aMatch[6]] = $aMatch[8];
			  else	$a = &$aResult [$aMatch[3]];
			}
	
		return $aResult;
	}
	
	
	function parseIniString($ini, $process_sections = false, $scanner_mode = null)
	{
		if (function_exists('parse_ini_string')) {
			$ini=parse_ini_string($ini, $process_sections,$scanner_mode);	
		} else {
			# Generate a temporary file.
			$tempname = tempnam('/tmp', 'ini');
			$fp = fopen($tempname, 'w');
			fwrite($fp, $ini);
			$ini = parse_ini_file($tempname, $process_sections);
			fclose($fp);
			@unlink($tempname);
		}
		return $ini;
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
	function cmfcFile_getFileExtension($path) {
		$path_parts = pathinfo($path);
	//	echo $path_parts['dirname'], "\n";
	//	echo $path_parts['basename'], "\n";
		return strtolower($path_parts['extension']);
	}
}
