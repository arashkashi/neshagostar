<?php
/**
* @author Sina Salek
* @changelog
* + Porting default features
* + Detecting offline config by ip
* + Detecting config by wildcard
* + Support parent config attribute. config will load the parent config   
* @todo   
* - Redirecting site to a predefined error page in case of misconfiguration (application has a default page itself)
* - Validate database connection and display erorr page it failed
* @version $Id: configuratorStandAloneMultiAutoV1.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
*/


require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PEAR.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'base.class.inc.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'packageManager/packageManager.class.inc.php');

define('CMF_ConfiguratorStandAloneMultiAutoV1_Ok',true);
define('CMF_ConfiguratorStandAloneMultiAutoV1_Error',2);
define('CMF_ConfiguratorStandAloneMultiAutoV1_Does_No_Exsists',3);

class cmfcConfiguratorStandAloneMultiAutoV1 extends cmfcConfiguratorStandAloneMultiAutoV1Base{
	/**
	 * 
	 * @var unknown_type
	 */
	var $_sessionBaseName='configurator';
	/**
	 * 
	 * @var string
	 */
	var $_configurationsFolderPath;
	/**
	 * 
	 * @var string
	 */
	var $_defaultConfigIntenralName='default';
	/**
	 * User can force a particula config to load via setting this value
	 * @var string
	 */	
	var $_activeConfigInternalName=null;
	/**
	 * Old sites require trailing slash at the end of url , 
	 * @var unknown_type
	 */	
	var $_legacyFormatEnabled=false;
	/**
	 * Reference to $_SERVER variable
	 * @var unknown_type
	 */
	var $_server;
	/**
	 * Reference to $_ws variable
	 * @var unknown_type
	 */
	var $_ws;
	/*
	 * 
	 */
	var $_defaultError=CMF_ConfiguratorStandAloneMultiAutoV1_Error;
	/**
	 * 
	 * @var unknown_type
	 */
	var $_messagesValue=array(
        CMF_ConfiguratorStandAloneMultiAutoV1_Ok	=> 'no error',
        CMF_ConfiguratorStandAloneMultiAutoV1_Error	=> 'unkown error'
	);
	
	/**
	 * Full path of the page.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_pageFolderPath='';
	/**
	 * Page browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_pageFolderPathBrowser='';
	/**
	 * Full path of the site
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPath='';
	/**
	 * Site browser path, excluding site url.
	 * Which will be calculated automatically, but overwriting it value is possible
	 * @var string
	 */
	var $_siteFolderPathBrowser='';
	/**
	 * 
	 * @var unknown_type
	 */
	var $_siteTempFolderPath='';
	/**
	 * 
	 * @var unknown_type
	 */
	var $_siteCacheFolderPath='';
	
	/**
	 * @var string
	 */
	var $_packageFolderPath='';
	/**
	 * @var string
	 */
	var $_packageFolderPathBrowser='';
	
	/**
	 * 
	 * @var string
	 */
	var $_cmfFolderPath='';
	
	/**
	 * @var array
	 */
	var $_index=array();
	
	/**
	 * @var array
	 */
	var $_packagesInfo=array();
	
	/**
	 * In production enviroment only packages require filess will be considered.
	 * @var string
	 */
	var $_projectEnviroment='development';//production
	
	/**
	 * List of files call directly or indirectly via __addPackage
	 */
	var $_usedFiles=array();
	
	/**
	 * 
	 * @var object
	 */
	var $packageManager;
	
	/**
	 * 
	 * @param $options
	 * @return unknown_type
	 */
	function __construct($options) {
		$this->setOptions($options);
		$this->packageManager= cmfcPackageManager::factory('v1',array());
	}
	
	
	/**
	 * 
	 * @see beta/cmfcClassesCore#setOptions($options, $merge)
	 */
	function setOptions($options,$merge=false) {
		
		if (!$merge) {
			if (!isset($options['pageFolderPath'])) {
				$options['pageFolderPath']=dirname($_SERVER['SCRIPT_FILENAME']);
			}
			if (!isset($options['pageFolderPathBrowser'])) {
				$options['pageFolderPathBrowser']=dirname($_SERVER['SCRIPT_NAME']);
			}
			if (!isset($options['siteFolderPath'])) {
				$options['siteFolderPath']=$_SERVER['DOCUMENT_ROOT'];
			} 
			if (!isset($options['siteFolderPathBrowser'])) {
				$options['siteFolderPathBrowser']='';
			}
			if (!isset($options['packageFolderPath'])) {
				$options['packageFolderPath']=dirname(__FILE__);
			}
			if (!isset($options['packageFolderPathBrowser'])) {
				$fileRelativePath='/'.str_replace($options['siteFolderPath'],'',$options['packageFolderPath']);
				$options['packageFolderPathBrowser']=$this->normalizePath($fileRelativePath);
			}
			if (!isset($options['cmfFolderPath'])) {
				$options['cmfFolderPath']=realpath(dirname(__FILE__).'/../../..');
			}
		}

		parent::setOptions($options,$merge);
	}
	
	/**
	 *
	 * @see v2/cmfcClassesCore#setOption($name, $value, $merge)
	 */
	function setOption($name,$value,$merge=false) {
		if ($name=='siteCacheFolderPath') {
			$this->packageManager->setOption('siteCacheFolderPath',$value);
		}
		if ($name=='pageFolderPath') {
			$value=$this->normalizePath($value);
		} elseif ($name=='siteFolderPath') {
			$value=$this->normalizePath($value);
		}
		
		parent::setOption($name,$value,$merge);
	}
	
	/**
	 * 
	 * @return arrry
	 */
	function getVariousConfigs() {
		$portals=array();
		$rows=$this->scanRecursive($this->_configurationsFolderPath);
		foreach ($rows as $key=>$row) {
			$filePath=$row;
			if (preg_match('/([^.\/\\\]*).inc.php$/i', $filePath, $regs)) {
				//@ob_start();
				include($filePath);
				//ob_end_clean();
				$name=$regs[1];
				$__config['internalName']=$name;
				$__config['id']=$name;
				$items[$name]=$__config;
			}
		}
		return $items;
	}
	
	/**
	 * It's able to use the right configuration by domain,ip,domain wildcard
	 * path,domain and url will be generate automatically if not defined
	 * <code>
	 * 'indicators'=>array(
	 * 		'domainWildcard'=>'*.local',
	 * 		'ip'=>'192.168.0.2'
	 * 		'domain'=>'test.com'
	 * )
	 * </code>
	 * @param $_ws
	 * @return array
	 */
	function load($_ws=null) {
		$this->_ws=$_ws;
		#--(Begin)-->Load all config files into an array
		$this->_ws['virtualTables']['variousConfigs']=array(
			'name'=>'variousConfigs',
			'rows'=>$this->getVariousConfigs()
		);
		#--(End)-->Load all config files into an array
		
		#--(Begin)-->Find the matched config
		$domain=str_replace('www.','',strtolower($this->_server['HTTP_HOST']));
		$ip=$this->_server['SERVER_ADDR'];		
		$inName='';
		foreach ($this->_ws['virtualTables']['variousConfigs']['rows'] as $key=>$info) {
			$info['internameName']=$key;
			
			#--(Begin)--> Merge with parent config
			if (isset($info['parentConfig'])) {
				$info=$this->arrayMergeRecursive($this->_ws['virtualTables']['variousConfigs']['rows'][$info['parentConfig']],$info);
				$this->_ws['virtualTables']['variousConfigs']['rows'][$key]=$info;
			}
			#--(Begin)--> Merge with parent config
			
			#--(Begin)-->Validate configuration parameters
			#--(End)-->Validate configuration parameters
			
			if (isset($info['siteInfo']['domain'])) {
				$siteDomain=str_replace('www.','',strtolower($info['siteInfo']['domain']));
				$info['indicators']['domain']=$siteDomain;
			}
			
			if (is_array($info['indicators']))
			foreach ($info['indicators'] as $indicatorName=>$indicatorValue) {
				if ($indicatorName=='domainWildcard') {
					if (fnmatch($indicatorValue,$domain)) {
						$inName=$info['internameName'];
						break;
					}
				}
				if ($indicatorName=='ip') {
					if ($indicatorValue==$ip) {
						$inName=$info['internameName'];
						break;
					}
				} 
				if ($indicatorName=='domain') {
					if ($indicatorValue==$domain) {
						$inName=$info['internameName'];
						break;
					}
				}
			}
			if (!empty($inName)) {
				break;
			}
		}
		
		if (!is_null($this->_activeConfigInternalName)) {
			$inName=$this->_activeConfigInternalName;
			
		} elseif (empty($inName)) {
			$inName=$this->_defaultConfigIntenralName;
			$this->_activeConfigInternalName=$inName;
		}
		#--(End)-->Find the matched config
		
		#--(Begin)-->Analilze and prepare loaded config
		$conf=&$this->_ws['virtualTables']['variousConfigs']['rows'][$inName];
		if (isset($conf['siteInfo']['domain'])) {
	    	$conf['siteInfo']['domain']=$conf['siteInfo']['domain'];
		} else {
			$conf['siteInfo']['domain']=$this->_server['HTTP_HOST'];
		}			    
		if (!isset($conf['siteInfo']['path'])) {
						
			if (!isset($this->_server['DOCUMENT_ROOT'])) {
				$this->_server['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(
			   		$this->_server['SCRIPT_FILENAME'], 0, 0-strlen($this->_server['PHP_SELF'])
			   	));
			}
			$conf['siteInfo']['path']=$this->_server['DOCUMENT_ROOT'];
			if ($this->_legacyFormatEnabled==true) {
				$conf['siteInfo']['path'].='/';
			}
		}
	
		if (!isset($conf['siteInfo']['url'])) {
			if ($this->_server['HTTPS']) {
				$conf['siteInfo']['url']='https://';
			} else {
				$conf['siteInfo']['url']='http://';
			}
			$conf['siteInfo']['url'].=$conf['siteInfo']['domain'];
			if ($this->_legacyFormatEnabled==true) {
				$conf['siteInfo']['url'].='/';
			}
		} else {
			if (strpos(strtolower($this->_server['HTTP_HOST']),'www.')!==false and strpos(strtolower($conf['siteInfo']['url']),'www.')===false) {
				$conf['siteInfo']['url']=str_replace('://','://www.',$conf['siteInfo']['url']);
			}
		}
		$conf['generalLibInfo'] = array(
			'path'         => $conf['siteInfo']['path'].'/general_lib',
			'url'          => $conf['siteInfo']['url'].'/general_lib',
			'inRootPath'   => $conf['siteInfo']['url'].'/general_lib',
		);
		#--(End)-->Analilze and prepare loaded config
		
		#--(Begin)-->Validate configuration parameters
		#--(End)-->Validate configuration parameters
		
		//For backward compatiblity
		$this->_ws['configurations']=&$conf;
		
		if (is_null($_ws)) {
			$this->_ws=$conf;
		} else {
			$this->_ws=$this->arrayMergeRecursive($this->_ws,$conf);
		}
		return $this->_ws;
	}
	
	/**
	 * 
	 * @param $_ws
	 * @return unknown_type
	 */
	function postProcess($_ws) {
		$info=$this->_ws['virtualTables']['variousConfigs']['rows'][$this->_activeConfigInternalName];
	
		$this->_ws=$_ws;
		if (is_array($info['postProcess'])) {
			$this->_ws=$this->arrayMergeRecursive($this->_ws,$info['postProcess']);
		}
		return $this->_ws;
	}
	
	/**
	 * 
	 * @param $extraWs
	 * @return unknown_type
	 */
	function prepare($extraWs) {
		$this->_ws=$this->arrayMergeRecursive($this->_ws,$extraWs);
		return $this->_ws;
	}
	
	
	/**
	 * 
	 * @param $name
	 * @param $value
	 * @param $merge
	 * @return unknown_type
	 */
	function updateWs($name,$value,$merge) {
		if ($merge==true) {
			$this->_ws[$name]=$this->arrayMergeRecursive($this->_ws[$name],$value);
		} else {
			$this->_ws[$name]=$value;
		}
		return $this->_ws;		
	}
}
?>
