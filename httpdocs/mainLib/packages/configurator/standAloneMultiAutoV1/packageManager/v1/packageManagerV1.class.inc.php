<?php
/**
* @author Sina Salek
* @changelog
* + Porting default features
* + Including packages and dependencies and including their dependencies automatically
* + Support old package and path names  
* + Including non php files, using another method, like considerPackageExternal 
* + If a package didn't have info file, packageManager should scan it deeply and update the packages map
* + Support for lazy loading
* + If cache folder is set, it should be writable
* + an interface for packages to know if a certain package is avaialbe and is loaded
* + returning package instance, packageInit('package','ajax','v1',options())
* + Cache system, on each build configurator has a full list of all dependencies
*         - Extensive caching which require cache folder and creates pure php include list by default
*             it generate md5 of addPackage packageslist or user can set a unqiue identifier and change it
*             whenever he modifies packages list
*         - Map caching, one for generating pack one for only including         
* @todo
* - Optional Files to support files as well
* - Runtime cache, it will be generated based on included package and wil be put inside cache folder
*     It's a php file which includes require files. user can manually enter a hash number or package generates
*     a unique hash based on packages array
* - new parameters getPackageInfo, ignoreDependencies
* - An option to disable dependency resolving in case of only the specified package changed
* - Support for dependencies revers! package which are using that package
* - Port part of the builder package in here. for example generating documentation or preparing list of files
* - Support for including files related to a core function
* - By default two versions of the same externalPackage can't be used unless indicated in info file
* - Validate altnames, existance folder names can't be used as altnames and altnames can't 
*   be used as version names anywhere else 
* - Trowing fatal error if there was anything wrong with config 
* - Full mode, for sites which need full general lib
* - Support for standalone packages 
* - Adding packages automatically via ftp to the projects but check the version compatibility
*         - on automatic download mode, it tries to download the backup from repositroy if wasn't
*           exists. it should also sync the the local and remote package for fixing in case of
*           timeout while downloading or implment auto refresh method.  
* @version $Id: configuratorStandAloneMultiAutoV1.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
*/


if (!class_exists('cmfcPackageManagerV1Wrapper')) {
    require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'packageWrapper.class.inc.php');
}


define('CMF_PackageManagerV1_Ok',true);
define('CMF_PackageManagerV1_Error',2);
define('CMF_PackageManagerV1_Does_No_Exsists',3);

class cmfcPackageManagerV1 extends cmfcConfiguratorStandAloneMultiAutoV1Base{

    var $_server;
    var $_defaultError=CMF_PackageManagerV1_Error;
    var $_messagesValue=array(
        CMF_PackageManagerV1_Ok    => 'no error',
        CMF_PackageManagerV1_Error    => 'unkown error'
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
     * @var array
     */
    var $_index=array();
    
    /**
     * @var array
     */
    var $_packagesInfo=array();
    /**
     * When we're just including package for using, there is no need to have a 
     * full map of every file in each package.
     * @var array
     */
    var $_packagesInfoCompact=array();
    
    
    /**
     * 
     * @var string
     */
    var $_cmfFolderPath='';
    
    /**
     * In production enviroment only packages require filess will be considered.
     * @var string
     */
    var $_projectEnviroment='production';//production,development
    
    /**
     * 
     * @var boolean
     */
    var $_buildModeEnabled=false;
    
    /**
     * List of files call directly or indirectly via __addPackage
     */
    var $_usedFiles=array();
    
    function __construct($options) {
        $this->setOptions($options);
        $this->structureCache('load');
    }
    
    
    /**
     * 
     * @see v2/cmfcClassesCore#setOptions($options, $merge)
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
                if (substr($options['siteFolderPath'],strlen($options['siteFolderPath'])-1,1)==DIRECTORY_SEPARATOR) {
                    $options['siteFolderPath']=substr($options['siteFolderPath'],0,strlen($options['siteFolderPath'])-1);
                }
            } 
            if (!isset($options['siteFolderPathBrowser'])) {
                $options['siteFolderPathBrowser']='';
            }
            if (!isset($options['packageFolderPath'])) {
                $options['packageFolderPath']=dirname(__FILE__);
            }
            if (!isset($options['packageFolderPathBrowser'])) {
                $fileRelativePath=str_replace($options['siteFolderPath'],'',$options['packageFolderPath']);
                $options['packageFolderPathBrowser']=$this->normalizePath($fileRelativePath);
            }
            if (!isset($options['cmfFolderPath'])) {
                $options['cmfFolderPath']=realpath(dirname(__FILE__).'/../../../../../');
            }
            if (!isset($options['cmfFolderPathBrowser'])) {
                $fileRelativePath=str_replace($options['siteFolderPath'],'',$options['cmfFolderPath']);
                $options['cmfFolderPathBrowser']=$this->normalizePath($fileRelativePath);
            }
        }

        parent::setOptions($options,$merge);
    }
    
    /**
     *
     * @see beta/cmfcClassesCore#setOption($name, $value, $merge)
     */
    function setOption($name,$value,$merge=false) {
        if ($name=='pageFolderPath') {
            $value=$this->normalizePath($value);
        } elseif ($name=='siteFolderPath') {
            $value=$this->normalizePath($value);
        }
        
        parent::setOption($name,$value,$merge);
    }
    

    
    /**
     * Scan the package files and sources and generate info file.
     * for example package description can be generated via doc description
     * If it was already exists, update it.
     * It also check version of info file and upgrade it to the latest version
     * @return unknown_type
     */
    function generateUpdateUpgradePackageInfoFile($packagePathInfo) { 
    }

    /**
     * Scan CMF strucutre and prepare a map for other functions to work.
     * This function also check the structure of CMF folders and info files.
     * It throw error if it find any error in structure or info files.
     * - AltNames are only useful for external usage, they can't be used inside info file.
     * - parameters with ! at the begining are packageExternal specific
     * Sample package,packageExternal ini :
     * <code>
     *         !nameOriginal=Array 2 XML
     *         altMachineNames=gaim
     *         isDeprecated=true //if deprecated, and some other package consider it as altName this package will be considered as non existed
     *         author=Johnny Brochard
     *         website=http://www.phpclasses.org/browse/package/1826.html
     *         onlineDocumentationUrl=
     *         downloadUrl=http://www.phpclasses.org/browse/package/1826.html
     *         directDownloadUrl=
     *         descriptionShort=Store associative array data on file in XML
     *         description="This class is capable of storing the data of associative arrays in XML files using DOM PHP 5 extension API.
     *         It supports specifying the XML output file name, the document root name, the output encoding. It also supports storing nested associtive arrays."
     *         infoVersion=1
     *         defaultPhpFile=//If ignored packageName.class.inc.php will be included
      *   
     *         [version-multiValueV1]
     *             !legacyPathInDependenciesFolder=myclass/myclass.class.inc,myclass/myclass.class.inc2
     *             altMachineNames=bestmultiValueV1,multiValueV1Beta,multiValueBeta 
     *             descriptionShort=""
     *             description=""
     *             requireIncludes=class.php,test.php ;use {main} to indicate where the main file should be include  (by default at the end), this files will be included in order,path is relative
     *             optionalFiles=examples/*,sample.php ;by default it consider examples,sample,document,documentation as optional
     *             dependencies[]=packageExternal:xinha:v0:test.php:!exact:!onlyConsider:!optional
     *             dependencies[]=packageExternal:xinha:v0:!files:!exact:!onlyConsider:!optional
     *             dependencies[]=package:sync:v2:exact
     *            phpMinVersion=5;User 5.x to target all version of 5 or 5 to target 5.0.0
     *            phpMaxVersion=5
     *        [version-multiValueV2]
     *             altMachineNames=
     * </code>
     * @changelog 
     * - Ignore non require files on production mode
     * @todo
     * - preventing duplicate altnames. name of an existing version or package can't be used as
     *      alt name on other version and packages
     * - cache results into a file to improve performance and decrease the need to access
     *      for base cmf
     * @param $mode //development,production,productionBildMode   
     * @return array|boolean
     */
    function structureScan($mode) {
        
        $this->_index=array();
        $this->_packagesInfo=array();
        
        $packageFolders=$this->cmfcDirectory_getContentsAsSingleLevelArray2(array(
            'startDirectory'=>$this->_cmfFolderPath,
            'includeInResult'=>array('folder'),
            'resultRelativeToStartDirectoryEnabled'=>true,
            'startDirectoryOriginal'=>$this->_cmfFolderPath,
            'maxLevel'=>2,
            'fastModeEnabled'=>true,
            'whiteList'=>array(
                $this->_cmfFolderPath=>array('path'),
                $this->_cmfFolderPath.DIRECTORY_SEPARATOR.'packages'.'*'=>array('path','wildcard'),
                $this->_cmfFolderPath.DIRECTORY_SEPARATOR.'packageExternal'.'*'=>array('path','wildcard'),
                //'*.ini'=>array('file','wildcard','name')
            ),
            'blackList'=>array(
                '.svn'
            ),
        ));
        //cmfcHtml::printr($packageFolders);

        $infos=array();
        foreach ($packageFolders as $packagePath=>$packagePathInfo) {
            $infoFilePath=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$packagePath.DIRECTORY_SEPARATOR.'info.ini';
            $info=null;
            if (!file_exists($infoFilePath)) {
                //Generate info file if possible
                $info=$this->generateUpdateUpgradePackageInfoFile($packagePathInfo);
                $info=array();
            } else {
                $ini=file_get_contents($infoFilePath);
                $ini=str_replace(':!',':no',$ini);
                //$info=parse_ini_file($infoFilePath,true);
                $info=$this->parseIniString($ini,true);
            }

            if (is_array($info) and $packagePathInfo['level']==2) {
                if (isset($info['isDepricated'])) {
                    $info['isDeprecated']=$info['isDepricated'];
                }
                
                
                $info['folderPath']=$packagePath;
                
                $_t=basename($packagePathInfo['path']);
                if ($_t=='packagesExternal') {
                    $info['type']='packageExternal';
                } elseif ($_t=='packages') {
                    $info['type']='package';
                } elseif ($_t=='dependencies') {
                    $info['type']='dependency';
                } else {
                    //Unkown!!
                }
                
                if (!isset($info['machineName'])) {
                    $info['machineName']=$packagePathInfo['name'];
                }
                
                if (!isset($info['name'])) {
                    $info['name']=basename($infoFileInfo['path']);
                }
                if (empty($info['name'])) {
                    $info['name']=$info['machineName'];
                }
                
                /*
                if ($info['machineName']=='userSystem') {
                    print_r($info);
                    exit;
                }
                */
                
                #--(Begin)-->See if required and optional parameters exist
                #--( End )-->See if required and optional parameters exist
    
                #--(Begin)-->Build index for packages altNames
                if (!empty($info['altMachineNames'])) {
                    $info['altMachineNames']=explode(',',$info['altMachineNames']);
                    foreach ($info['altMachineNames'] as $n) {
                        if (!isset($this->_index['altMachineNames'][$info['type']][strtolower($n)])) {
                            $this->_index['altMachineNames'][$info['type']][strtolower($n)]=$info['machineName'];
                        } else {
                            //Duplicate altname?!
                        }
                    }
                }
                #--(End)-->Build index for packages altNames
                
                $blackListGeneric=array('.svn','info.ini');
                if ($mode=='productionBuildMode') {
                    $blackListGeneric['*example*']=array('wildcard','path');
                    $blackListGeneric['*examples*']=array('wildcard','path');
                    $blackListGeneric['*samples*']=array('wildcard','path');
                    $blackListGeneric['*sample*']=array('wildcard','path');
                    $blackListGeneric['*document*']=array('wildcard','path');
                    $blackListGeneric['*documents*']=array('wildcard','path');
                    $blackListGeneric['*resource*']=array('wildcard','path');
                    $blackListGeneric['*resources*']=array('wildcard','path');
                    $blackListGeneric['todo.txt']=array('wildcard','file');
                    $blackListGeneric['info.txt']=array('wildcard','file');
                    $blackListGeneric['info.ini']=array('wildcard','file');
                }
                
                #--(Begin)-->Scan versions
                $blackListVersions=array();
                $info['versionsAltNamesIndex']=array();
                foreach ($info as $versionName=>$versionInfo) {
                    if (is_array($versionInfo) and strpos($versionName,'version-')!==false) {
                        $versionInfo['machineName']=str_replace('version-','',$versionName);
                        
                        #--(Begin)--> Make sure version files really exist
                        #--( End )--> Make sure version files really exist
                        
                        #--(Begin)--> Calculate package version folder path
                        $versionInfo['folderPath']=$info['folderPath'].DIRECTORY_SEPARATOR.$versionInfo['machineName'];
                        #--( End )--> Calculate package version folder path
                        
                        #--(Begin)--> Convert dependencies string to array
                        if (isset($versionInfo['dependencies'])) {
                            foreach ($versionInfo['dependencies'] as $k=>$dependencyInfo) {
                                $ar=explode(':',$dependencyInfo);
                                $rar=array();
                                if ($ar[0]=='package' or $ar[0]=='packageExternal') {
                                    
                                    $rar['type']=$ar[0];
                                    $rar['machineName']=$ar[1];
                                    $rar['version']=$ar[2];
                                    $rar['file']=$ar[3];
                                    if ($rar['file']!='!files' and $rar['file']!='nofiles') {
                                        $rar['file']=explode(',',$rar['file']);
                                    } else {
                                        $rar['file']='';
                                    }
                                    $rar['exact']=($ar[4]=='exact')?true:false;
                                    $rar['onlyConsider']=($ar[5]=='onlyConsider')?true:false;
                                    $rar['optional']=($ar[6]=='optional')?true:false;
                                } else {
                                    //Incorrect structure
                                }
                                $versionInfo['dependencies'][$k]=$rar;//its here to make sure the type is always array
                            }
                        } else {
                            
                        }
                        #--( End )--> Convert dependencies string to array
                        
                        #--(Begin)-->Build index for packages version altNames
                        if (!empty($versionInfo['altMachineNames'])) {
                            $versionInfo['altMachineNames']=explode(',',$versionInfo['altMachineNames']);
                            foreach ($versionInfo['altMachineNames'] as $n) {
                                if (!isset($this->_index['versionsAltMachineNamesIndex'][strtolower($info['machineName'])][strtolower($n)])) {
                                    $this->_index['versionsAltMachineNamesIndex'][strtolower($info['machineName'])][strtolower($n)]=$versionInfo['machineName'];
                                } else {
                                    //Duplicate altname?!
                                }
                            }
                        }
                        #--(End)-->Build index for packages version altNames
                        
                        #--(Begin)-->Build index legacy files in depenencies
                        if (!empty($versionInfo['legacyPathInDependenciesFolder'])) {
                            $versionInfo['legacyPathInDependenciesFolder']=explode(',',$versionInfo['legacyPathInDependenciesFolder']);
                            foreach ($versionInfo['legacyPathInDependenciesFolder'] as $n) {
                                if (strpos($n,'*')!==false) {
                                    $this->_index['legacyPathInDependenciesFolderIndexWildcard'][$n]=array(
                                        'machineName'=>$info['machineName'],
                                        'type'=>$info['type'],
                                        'version'=>$versionInfo['machineName']
                                    );
                                } elseif (!isset($this->_index['legacyPathInDependenciesFolderIndex'][$n])) {
                                    $this->_index['legacyPathInDependenciesFolderIndex'][$n]=array(
                                        'machineName'=>$info['machineName'],
                                        'type'=>$info['type'],
                                        'version'=>$versionInfo['machineName']
                                    );
                                }
                            }
                        }
                        #--(End)-->Build index legacy files in depenencies
                        
                        #--(Begin)--> Make a list of files to be packaged
                        $whiteList=array();
                        $blackList=$blackListGeneric;
                        if ($mode=='productionBuildMode') {
                            if (!empty($versionInfo['optionalFiles'])) {
                                $versionInfo['optionalFiles']=explode(',',$versionInfo['optionalFiles']);
                                foreach ($versionInfo['optionalFiles'] as $_f) {
                                    $blackList[$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$versionInfo['folderPath'].DIRECTORY_SEPARATOR.$_f]=array('wildcard','path');
                                }
                            }
                        }

                        if ($mode=='development' or $mode=='productionBuildMode') {
                            $versionInfo['files']=$this->cmfcDirectory_getContentsAsSingleLevelArray2(array(
                                'startDirectory'=>$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$versionInfo['folderPath'],
                                'resultRelativeToStartDirectoryEnabled'=>true,
                                'startDirectoryOriginal'=>$this->_cmfFolderPath,
                                'includeInResult'=>array('folder','file'),
                                'maxLevel'=>'all',
                                'detailedResultsEnabled'=>false,
                                'fastModeEnabled'=>true,
                                'whiteList'=>$whiteList,
                                'blackList'=>$blackList
                            ));
                            $blackListVersions[$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$versionInfo['folderPath']]=array('path','folder');
                        }
                        #--(End)--> Make a list of files to be packaged
                        
                        $info['versions'][strtolower($versionInfo['machineName'])]=$versionInfo;
                        unset($info[$versionName]);
                    }
                }
                #--(End)-->Scan versions
                
                #--(Begin)--> Make a list of files to be packaged
                if ($mode=='development' or $mode=='productionBuildMode') {
                    $whiteList=array();
                    $blackList=array_merge($blackListGeneric,$blackListVersions);
                    $info['files']=$this->cmfcDirectory_getContentsAsSingleLevelArray2(array(
                        'startDirectory'=>$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$info['folderPath'],
                        'resultRelativeToStartDirectoryEnabled'=>true,
                        'startDirectoryOriginal'=>$this->_cmfFolderPath,
                        'includeInResult'=>array('folder','file'),
                        'maxLevel'=>'all',
                        'detailedResultsEnabled'=>false,
                        'fastModeEnabled'=>true,
                        'whiteList'=>$whiteList,
                        'blackList'=>$blackList
                    ));
                }
                #--(End)--> Make a list of files to be packaged
                
                if (!isset($infos[strtolower($info['machineName'])])) { 
                    $infos[$info['type']][strtolower($info['machineName'])]=$info;
                } else {
                    //Duplicate package name!!
                }
            } else {
                //No info file!!
            }
        }
        //$this->cmfcHtml_printr($infos);
        //exit;
        $this->_packagesInfo=$infos;
                
        return $infos;
    }
    
    
    /**
     * 
     * @param $action //save,load,clear
     * @return unknown_type
     */
    function structureCache($action) {
        $cacheFolderPath=realpath($this->_packageFolderPath.'/../../cache');
        $cacheFile=$cacheFolderPath.'/cmfStructure.cache.php';
        $cacheProductionFile=$cacheFolderPath.'/cmfStructureProduction.cache.php';
        $cacheCompactFile=$cacheFolderPath.'/cmfStructureCompact.cache.php';
                
        if (!file_exists($cacheFolderPath)) {
            $result=$this->raiseError("Cache folder \"$cacheFolderPath\" does not exists");
        }

        if ($action=='save') {
            
            if (!is_writable($cacheFolderPath)) {
                $result=$this->raiseError("Cache folder \"$cacheFolderPath\" is not writable.");
            }

            #--(Begin)-->Fixing bug with missing cache files!
            $result=file_put_contents($cacheFile,$content);
            $result=file_put_contents($cacheCompactFile,$content);
            $result=file_put_contents($cacheProductionFile,$content);
            #--(End)-->Fixing bug with missing cache files!
            
            if (!$this->isError($result)) {
                if (!$this->isError($result)) {
                    $result=$this->structureScan('development');
                    $content='<?php'."\n";
                    $content.='$this->_index='.var_export($this->_index,true).";\n";
                    $content.='$this->_packagesInfo='.var_export($this->_packagesInfo,true).";\n";
                    $result=file_put_contents($cacheFile,$content);
                }
            }
            
            if (!$this->isError($result)) {
                $result=$this->structureScan('production');
                if (!$this->isError($result)) {
                    $content='<?php'."\n";
                    $content.='$this->_index='.var_export($this->_index,true).";\n";
                    $content.='$this->_packagesInfo='.var_export($this->_packagesInfo,true).";\n";
                    $result=file_put_contents($cacheCompactFile,$content);
                }
            }
            
            if (!$this->isError($result)) {
                $result=$this->structureScan('productionBuildMode');
                if (!$this->isError($result)) {
                    $content='<?php'."\n";
                    $content.='$this->_index='.var_export($this->_index,true).";\n";
                    $content.='$this->_packagesInfo='.var_export($this->_packagesInfo,true).";\n";
                    $result=file_put_contents($cacheProductionFile,$content);
                }
            }
            
        } elseif ($action=='load') {

            if (!$this->isError($result)) {
                if ($this->_projectEnviroment=='development') {
                    $cacheFile=$cacheFile;
                    
                } elseif ($this->_projectEnviroment=='production' && $this->_buildModeEnabled==true) {
                    $cacheFile=$cacheProductionFile;
                    
                } else {
                    $cacheFile=$cacheCompactFile;
                }
                
                if (file_exists($cacheFile)) {
                    include($cacheFile);
                } else {
                    $result=$this->structureCache('save');
                }
            }
            
        } elseif ($action=='clear') {
            @unlink($cacheFile);
            @unlink($cacheCompactFile);

        }
        
        return $result;
    }
    

    
    /**
     * It understands altnames
     * 
     * @param $type
     * @param $name
     * @param $version
     * @return array
     */
    function __getPackageInfo($type,$name,$version,$exact=false,$files=array()) {
        $result=null;
        if ($version=='*' or empty($version)) {
            $version=null;
        }
        
        $packagesInfo=&$this->_packagesInfo;
        
        $packageAddress="\"$type > $name > $version\"";
        
        #--(Begin)-->Check if it's moved to packageExternal and find the new package
        if (!$this->isError($result)) {
            if ($type=='packageExternalOld' and !empty($files)) {
                foreach ($files as $file) {
                    $fileRelativeToDependencies=str_replace(array('dependencies'.'/'),'',$file);
                    
                    if (isset($this->_index['legacyPathInDependenciesFolderIndex'][$fileRelativeToDependencies])) {
                        $_a=$this->_index['legacyPathInDependenciesFolderIndex'][$fileRelativeToDependencies];
                        break;
                        //cmfcHtml::printr($this->_packagesInfo[$type][strtolower($name)]);
                    } elseif (is_array($this->_index['legacyPathInDependenciesFolderIndexWildcard'])) {
                        foreach ($this->_index['legacyPathInDependenciesFolderIndexWildcard'] as $pattern=>$_atemp) {
                            $__file=$name.'/'.$fileRelativeToDependencies;
                            if ($name=='pear') {
                                //$pattern=str_replace(array($name.'/'),'',$file);
                                //echo "$pattern,$__file\n";
                            }
                            if (fnmatch($pattern,$__file)) {
                                $_a=$_atemp;
                                break;
                            }
                        }
                    }
                }
                $packageAddress="\"$type > $name > $version\"";
                if (empty($_a)) {
                    $result=$this->raiseError("$packageAddress, this old packager has no new alternative");
                } else {
                    $type=$_a['type'];
                    $name=$_a['machineName'];
                    $version=$_a['version'];
                }
            }
        }
        #--(End)-->Check if it's moved to packageExternal and find the new package
        
        #--(Begin)-->Check package type
        if (!$this->isError($result)) {
            if (strtolower($type)=='package') {
                $type='package';
            } elseif (strtolower($type)=='packageexternal') {
                $type='packageExternal';
            } else {
                $result=$this->raiseError("$packageAddress, Invalid package type");
            }
        }
        #--( End )-->Check package type
        
        #--(Begin)-->Check package name
        if (!$this->isError($result)) {
            if (empty($name)) {
                $result=$this->raiseError("$packageAddress, Package name can not be empty");    
            }
            if (!$this->isError($result)) {
                $canBeAltName=false;        
                if (isset($packagesInfo[$type][strtolower($name)])) {
                    if ($packagesInfo[$type][strtolower($name)]['isDeprecated']==true) {
                        $canBeAltName=true;
                    }
                } else {
                    $canBeAltName=true;
                }
                    
                if ($canBeAltName) {
                    //Look for alternative names if it wasn't exists
                    if (isset($this->_index['altMachineNames'][$type][strtolower($name)])) {
                        $name=$this->_index['altMachineNames'][$type][strtolower($name)];
                    } else {
                        $result=$this->raiseError("$packageAddress, Package does not exists");
                    }
                }
            }
            
            if (!$this->isError($result)) {
                $packageInfo=$packagesInfo[$type][strtolower($name)];
                if (!empty($packageInfo)) {
                    $packageInfo['folderPathFull']=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$packageInfo['folderPath'];
                    $packageInfo['folderPathBrowser']=$this->_cmfFolderPathBrowser.DIRECTORY_SEPARATOR.$packageInfo['folderPath'];
                }
            }
            
        }
        #--(End)-->Check package name


        #--(Begin)-->Check package version info
        if (!$this->isError($result)) {
            if (empty($version)) {
                //$result=$this->raiseError('Package version can not be empty');    
            }
            
            if (!$this->isError($result) and !empty($version)) {
                if (!isset($packageInfo['versions'][strtolower($version)]) and !empty($version)) {
                    if ($exact===true) {
                        $result=$this->raiseError($packageAddress.' : Package version does not exists');
                    } elseif (isset($this->_index['versionsAltMachineNamesIndex'][strtolower($packageInfo['machineName'])][strtolower($version)])) {
                        //Look for alternative names if it wasn't exists
                        $version=$this->_index['versionsAltMachineNamesIndex'][strtolower($packageInfo['machineName'])][strtolower($version)];
                    } else {
                        $result=$this->raiseError($packageAddress.' : no version or alternative version exists');
                        //Or Check for newer compatible versions
                    }
                }
            }
            
            if (!$this->isError($result)) {
                $packageVersionInfo=$packageInfo['versions'][strtolower($version)];
                    
                if (!empty($packageVersionInfo)) {
                    $packageVersionInfo['folderPathFull']=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$packageVersionInfo['folderPath'];
                    $packageVersionInfo['folderPathBrowser']=$this->_cmfFolderPathBrowser.DIRECTORY_SEPARATOR.$packageVersionInfo['folderPath'];
                }
            }
        }
        #--(End)-->Check package version info

        
        if (!$this->isError($result)) {
            $result=array('packageInfo'=>$packageInfo,'packageVersionInfo'=>$packageVersionInfo);
        }

        return $result; 
    }
    
    
    
    /**
     * - Version can be string or null, if null the whole is assumed
     * - If the exact version wasn't availalbe and $strict was false, it tried to find
     *   the alt version via altVersions parameters, it didn't succeed then i tried to 
     *    guess and find the closest version. like 2.5 and picking 2.1 because it's the 
     *    version which exists. in case of date it tried to pick the closet date.
     * - exact mode is only valid for package version and the package name, because package name may change
     *   without anychange in behaviour, and if it does , it eliminates the code flexibility
     * @changelog
     * @todo
     * - Support for default version name
     * - Prevent include multi version of a externalPackage, unless they've had separate name spaces
     * - Support wild cards or even regex on files includes parameters
     *       
     * @param $type string //package, package-external, dependency
     * @param $name string //case insensitive 
     * @param $version string //case insensitive
     * @param $file string|array
     * @return boolean
     */
    function __addPackage($type,$name,$version,$file,$exact=false,$optional=false,$onlyConsider=false) {
        $result=false;
        
        if ($version=='*' or empty($version)) {
            $version=null;
        }

        $packageAddress="\"$type > $name > $version\"";
                
        if ($type=='package' or $type=='packageExternal') {
            #--(Begin)--> Fetch package info
            if (!$this->isError($result)) {
                $r=$this->__getPackageInfo($type,$name,$version,$exact);

                if (!$this->isError($r)) {
                    $packageInfo=$r['packageInfo'];
                    $packageVersionInfo=$r['packageVersionInfo'];

                } else {
                    $result=$r;
                }
            }
            #--( End )--> Fetch package info
            
            #--(Begin)--> Add dependencies
            if (!$this->isError($result)) {
                $dependencies=array();
                if (empty($packageVersionInfo)) {
                    if (is_array($packageInfo['versions'])) {
                        foreach ($packageInfo['versions'] as $__packageVersionInfo) {
                            if (is_array($__packageVersionInfo['dependencies'])) {
                                $dependencies=array_merge($dependencies,$__packageVersionInfo['dependencies']);
                            }
                        }
                    }
                } else {
                    $dependencies=$packageVersionInfo['dependencies'];
                }
                
                if ($name=='userSystem') {
                    //print_r($dependencies);
                    //exit;
                }
                if (!empty($dependencies)) {
                    foreach ($dependencies as $dependencyInfo) {
                        $r=$this->__addPackage($dependencyInfo['type'],$dependencyInfo['machineName'],$dependencyInfo['version'],$dependencyInfo['file'],$dependencyInfo['exact'],$optional,$onlyConsider);
                        if ($this->isError($result)) {
                            $result=$r;
                            break;
                        }
                    }
                }
            }
            #--( End )--> Add dependencies
            
            
            #--(Begin)--> Check PHP version compatibility
            if (!$this->isError($result)) {
                $compatibleWithPHPVersion=true;

                if (!empty($packageVersionInfo['phpMinVersion'])) {
                    if (!$this->versionCompare(PHP_VERSION,$packageVersionInfo['phpMinVersion'],'>=')) {
                        $compatibleWithPHPVersion=false;
                    }
                }
                if (!empty($packageVersionInfo['phpMaxVersion'])) {
                    if (!$this->versionCompare(PHP_VERSION,$packageVersionInfo['phpMaxVersion'],'<=')) {
                        $compatibleWithPHPVersion=false;
                    }
                }                
                if (!$compatibleWithPHPVersion) {
                    $result=$this->raiseError("$packageAddress is not compatible with ".PHP_VERSION." min,max compatible versions are : {$packageVersionInfo['phpMinVersion']},{$packageVersionInfo['phpMaxVersion']} ");
                }
            }
            #-- (End) --> Check PHP version compatibility
                                
            #--(Begin)--> Include the package require files
            if (!$this->isError($result) and $onlyConsider!=true) {
                $filesToInclude=array();
                
                #--(Begin)--> Set a flag that indicates this package has been included
                $packageInfo['included']=true;
                if (!empty($packageVersionInfo)) {
                    $packageVersionInfo['included']=true;
                }
                #--(Begin)--> Set a flag that indicates this package has been included
                
                #--(Begin)--> including package version files
                if (!is_null($version) and !empty($packageVersionInfo['requireIncludes'])) {
                    $__includes=explode(';',$packageVersionInfo['requireIncludes']);
                    foreach ($__includes as $__include) {
                            if (!empty($__include)) {
                            $filesToInclude[]=$packageVersionInfo['machineName'].DIRECTORY_SEPARATOR.$__include;
                        }
                    }
                }
                #--(End)--> including package version files
                
                #--(Begin)--> Include package main php files
                if ($packageInfo['type']=='package' and $packageInfo['machineName']!='cmf') {
                    
                    #--(Begin)--> including package main file is optional when package does not have info file
                    if (1==1) {
                        $folderPath=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$packageInfo['folderPath'];
                        $mainFile=$packageInfo['machineName'].'.class.inc.php';
                        $mainFilePath=$folderPath.DIRECTORY_SEPARATOR.$mainFile;
                        
                        if (file_exists($mainFilePath) or isset($packageInfo['infoVersion'])) {
                            if (isset($packageInfo['defaultPhpFile'])) {
                                if (!empty($packageInfo['defaultPhpFile'])) { 
                                    $filesToInclude[]=$packageInfo['defaultPhpFile'];
                                }
                            } else {
                                $filesToInclude[]=$mainFile;
                            }
                        }
                        /*
                        if (file_exists($mainFilePath)) {
                            if (strpos($packageVersionInfo['requireIncludes'],'{main}')!==false) {
                                $packageVersionInfo['requireIncludes']=str_replace('{main}',$mainFile,$packageVersionInfo['requireIncludes']);
                            } else {
                                $packageVersionInfo['requireIncludes'].=";$mainFile";
                            }
                        }
                        */
                        /*
                        if ($packageInfo['machineName']=='ajax') {
                            //print_r($packageVersionInfo);
                            echo $mainFilePath;
                            echo $packageVersionInfo['requireIncludes'];
                            exit;
                        }
                        */
                    }
                    #--(End)--> including package main file is optional when package does not have info file

                }
                #--( End )--> Include package main php files
    
                #--(Begin)--> Include the package additinal files
                if (is_array($file)) {
                    foreach ($file as $include) {
                        if (!empty($include)) {
                            if (!is_null($version)) {
                                $filesToInclude[]=$packageVersionInfo['machineName'].DIRECTORY_SEPARATOR.$include;
                            } else {
                                $filesToInclude[]=$include;
                            }
                        }
                    }
                }
                #--( End )--> Include the package additinal files
                
                #--(Begin)--> Include package files
                if (!empty($filesToInclude)) {
                    foreach ($filesToInclude as $include) {
                        if (!empty($include)) {
                            $include=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$packageInfo['folderPath'].DIRECTORY_SEPARATOR.$include;
                            //echo "$include<br />";
                            
                            if (strpos($include,'PEAR.php') and class_exists('PEAR')) {
                                continue;
                            }
                            
                            if (!$this->includeFile($include,$optional)) {
                                $result=$this->raiseError("$packageAddress : \"$include\" file does not exists");
                                break;
                            }
                        }
                    }
                }
                #--( End )--> Include package files
            }
            #--( End )--> Include the package require files
            
            //global $___after;
            //if ($___after) {
                //$this->cmfcHtml_printr($packageInfo);
            //}

            #--(Begin)--> Add package files to list of used files
            if (!empty($packageInfo['files']) or !empty($packageVersionInfo['files'])) {
                if (empty($packageVersionInfo)/*($name=='userSystem'()*/) {
                    if (is_array($packageInfo['versions'])) {
                        foreach ($packageInfo['versions'] as $__packageVersionInfo) {
                            foreach ($__packageVersionInfo['files'] as $_k=>$_f) {
                                $this->_usedFiles[$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$_k]=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$_f;
                            }
                        }
                    }
                } else {
                    foreach ($packageVersionInfo['files'] as $_k=>$_f) {
                        $this->_usedFiles[$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$_k]=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$_f;
                    }
                }
                
                foreach ($packageInfo['files'] as $_k=>$_f) {
                    $this->_usedFiles[$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$_k]=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$_f;
                }
            }
            #--( End )--> Add package files to list of used files
                        
            #--(Begin)--> Update/Save changes made to packageInfo
            if (!empty($packageVersionInfo)) {
                $packageInfo['versions'][$version]=$packageVersionInfo;
            }
            
            $this->_packagesInfo[$type][strtolower($name)]=$packageInfo;
            #--(End)--> Update/Save changes made to packageInfo
        }
                
        return $result;
    }
    
    /**
     * 
     * @param $file
     * @param $optional
     * @return unknown_type
     */
    function includeFile($file,$optional=false) {
        $r=true;
        if ($this->cmfcFile_getFileExtension($file)=='php') {
            if ($optional) {
                $r=@include_once($file);
            } else {
                if (!file_exists($file)) {
                    return false;
                } else {
                    $r=require_once($file);
                }
            }
        } else {
            //Just add to files list
        }
        return $r;
    }
    
    /**
     * returns :
     * - name
     * - path
     * - folderPath
     * - url
     * @param $fileRelativePack
     * @param $packagName
     * @param $packageVersion
     * @return unknown_type
     */
    function getPackageFileInfo($packageType,$packagName,$packageVersion,$fileRelativePath,$exact=false) {
        $result=$this->__getPackageInfo($packageType,$packagName,$packageVersion,$exact);
        
        if (!$this->isError($result)) {
            $result=array();
            $result['name']=baseName($fileRelativePath);
            if (!empty($result['packageVersionInfo'])) {
                $folderPath=$result['packageVersionInfo']['folderPath'];
            } else {
                $folderPath=$result['packageInfo']['folderPath'];
            }
            $result['path']=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.$folderPath.DIRECTORY_SEPARATOR.$fileRelativePath;
            $result['url']=$this->_cmfFolderPathBrowser.DIRECTORY_SEPARATOR.$folderPath.DIRECTORY_SEPARATOR.$fileRelativePath;
            $result['folderPath']=dirname($result['path']);
        }
        return $result;
    }
    
    /**
     * Array containing information about package or packageVersion
     * @param $type
     * @param $name
     * @param $version
     * @param $exact
     * @param $files
     * @return unknown_type
     */
    function getPackageInfo($type,$name,$version=null,$exact=false,$files=array()) {
        $result=$this->__getPackageInfo($type,$name,$version,$exact,$files);

        if (!$this->isError($result)) {
            if (!empty($result['packageVersionInfo'])) {
                $__result=$result['packageVersionInfo'];
                $__result['packageInfo']=$result['packageInfo'];
                $result=$__result;
            } else {
                $result=$result['packageInfo'];
            }        
        }
        
        return $result;
    }
    
    /**
     * 
     * @param $type
     * @param $name
     * @param $version
     * @param $exact
     * @param $files
     * @return unknown_type
     */
    function isPackageExists($type,$name,$version=null,$exact=false,$files=array()) {
        $r=$this->getPackageInfo($type,$name,$version,$exact,$files);
        
        if ($this->isError($r)) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * For retrieving package files , and specially static files
     * @param $type
     * @param $name
     * @param $version
     * @param $exact
     * @param $files
     * @return cmfcPackageManagerV1Wrapper
     */
    function getPackageWrapper($type,$name,$version=null,$exact=false,$files=array()) {
        $result=$this->getPackageInfo($type,$name,$version,$exact,$files);
        if (!$this->isError($result)) {
            $__result=new cmfcPackageManagerV1Wrapper();
            $__result->_packageInfo=$result;
            $result=$__result;
        }
                
        return $result;
    }
    
    /**
     * Instead of using the exact class name of package , it's possible
     * to get an instance only defining their name
     * @param $type
     * @param $name
     * @param $version
     * @param $exact
     * @param $files
     * @return object
     */
    function getPackageInstance($type,$name) {
        $result=$this->getPackageInfo($type,$name,null);

        if (!$this->isError($result)) {
            if (class_exists(strtolower('cmfc'.ucfirst($result['machineName'])))) {
                $result=eval('new cmfc'.ucfirst($result['machineName']));
            } else {
                $result=$this->getPackageWrapper($type,$name);
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * @param $name
     * @param $version
     * @param $options
     * @param $exact
     * @return unknown_type
     */
    function getPackageVersionInstance($type,$name,$version,$options=array(),$exact=false) {
        $result=$this->getPackageInfo($type,$name,$version,$exact,array());

        if (!$this->isError($result)) {
            $packageAddress="\"$type > $name > $version\"";
            $info=$result;

            if ($info['packageInfo']['included']) {
                if (class_exists('cmfc'.ucfirst($info['packageInfo']['machineName']))) {
                    eval('$result=cmfc'.ucfirst($info['packageInfo']['machineName']).'::factory($info[\'machineName\'],$options);');
                    $result->_packageInfo=$info;
                } else {
                    $result=$this->getPackageWrapper($type,$name,$version,$exact);
                }
            } else {
                $result=$this->raiseError('Package '.$packageAddress.' is avaialble but it is not included',0,PEAR_ERROR_DIE);
            }
        }
        
        return $result;        
    }
    
    /**
     * $packages=array(
     *    array('type'=>'package','name'=>'ajax','version'=>'v1','file'=>'','exact'=>true,'optional'=>true,'onlyConsider'=>true)  
     * )
     * @param $packages
     * @return unknown_type
     */
    function addPackages($packages) {
        $packages[]=array(
            'type'=>'package',
            'name'=>'configurator',
            'version'=>'standAloneMultiAutoV1',
            'onlyConsider'=>true
        );
        foreach ($packages as $package) {

            if ($package['type']=='packageExternalOld') {
                #--(Begin)-->Check package and package version altNames
                $__r=$this->__getPackageInfo($package['type'],$package['name'],$package['version'],$package['exact'],$package['file']);
                if (!$this->isError($__r)) {
                    $package['name']=$__r['packageInfo']['machineName'];
                    $package['type']=$__r['packageInfo']['type'];
                    $package['version']=$__r['packageVersionInfo']['machineName'];
                }
                #--(End)-->Check package and package version altNames
            }
            
            if ($package['type']=='packageExternalOld') {
                $r=$this->addDependency(
                    $package['name'],
                    $package['file'],
                    $package['exact'],
                    $package['optional'],
                    $package['onlyConsider']
                );
            } else {
                $r=$this->__addPackage(
                    $package['type'],
                    $package['name'],
                    $package['version'],
                    $package['file'],
                    $package['exact'],
                    $package['optional'],
                    $package['onlyConsider']
                );
            }
            if ($this->isError($r)) {
                return $r;
            }
        }
        global $___after;
        if ($___after) {
            //cmfcHtml::printr($this->_usedFiles);
        }
        
        return $r;
    }
    
    
    /**
     * For supporting old dependencies folder, which will be removed in GeneralLib3
     * @param $name
     * @param $file
     * @param $exact
     * @param $optional
     * @param $onlyConsider
     * @return unknown_type
     */
    function addDependency($name,$file,$exact=false,$optional=false,$onlyConsider=false) {
        $result=true;
        $packageAddress="\"packageExternalOld > $name > ".print_r($file,true)."\"";
        
        #--(Begin)--> Prepare a default black list
        $blackListGeneric=array('.svn');
        if ($this->_projectEnviroment=='development' or $this->_buildModeEnabled==true) {
            $blackListGeneric['*example*']=array('wildcard','path');
            $blackListGeneric['*examples*']=array('wildcard','path');
            $blackListGeneric['*samples*']=array('wildcard','path');
            $blackListGeneric['*sample*']=array('wildcard','path');
            $blackListGeneric['*document*']=array('wildcard','path');
            $blackListGeneric['*documents*']=array('wildcard','path');
            $blackListGeneric['*resource*']=array('wildcard','path');
            $blackListGeneric['*resources*']=array('wildcard','path');
            $blackListGeneric['todo.txt']=array('wildcard','file');
        }
        #--(End)--> Prepare a default black list
        
        $info['files']=array();
        if (!empty($name)) {
            $packageFolderPath=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.'dependencies'.DIRECTORY_SEPARATOR.$name;
            
            #--(Begin)--> Make a list of files to be packaged
            if ($this->_projectEnviroment=='development' or $this->_buildModeEnabled==true) {
                $whiteList=array();
                $blackList=$blackListGeneric;
                $info['files']=$this->cmfcDirectory_getContentsAsSingleLevelArray2(array(
                    'startDirectory'=>$packageFolderPath,
                    'includeInResult'=>array('folder','file'),
                    'maxLevel'=>'all',
                    'detailedResultsEnabled'=>false,
                    'fastModeEnabled'=>true,
                    'whiteList'=>$whiteList,
                    'blackList'=>$blackList
                ));
            }
            #--(End)--> Make a list of files to be packaged
    
        } else {
            $packageFolderPath=$this->_cmfFolderPath.DIRECTORY_SEPARATOR.'dependencies';
        }
        
        #--(Begin)--> Include the package additinal files
        $toInclude=array();
        if (is_array($file)) {
            foreach ($file as $include) {
                if (!empty($include)) {
                    $include=$packageFolderPath.DIRECTORY_SEPARATOR.$include;
                    //echo "$include<br />";
                    $toInclude[]=$include;
                    $info['files'][$include]=$include;
                }                        
            }
        } elseif (!empty($file)) {
            $include=$packageFolderPath.DIRECTORY_SEPARATOR.$file;
            $toInclude[]=$include;
            $info['files'][$include]=$include;
        }
        #--( End )--> Include the package additinal files
        
        #--(Begin)--> Include package files
        if (!$onlyConsider) {
            if (!empty($toInclude)) {
                foreach ($toInclude as $include) {
                    if (!empty($include)) {
                        //echo "$include<br />";
                        if (strtolower(basename($include))=='pear.php' and class_exists('PEAR')) {
                            continue;
                        }
                        
                        if ($name=='pear') {//Special for PEAR package
                            $previousIncludePath=get_include_path();
                            set_include_path($packageFolderPath);
                        }
                        
                        if (!$this->includeFile($include,$optional)) {
                            $result=$this->raiseError("$packageAddress : \"$include\" file does not exists");
                            break;
                        }
                        
                        if ($name=='pear') {//Special for PEAR package
                            //set_include_path($previousIncludePath);
                        }
                    }
                }
            }
        }
        #--( End )--> Include package files
                
        #--(Begin)--> Add package files to list of used files
        if (!empty($info)) {
            foreach ($info['files'] as $_k=>$_f) {
                $this->_usedFiles[$_k]=$_f;
            }
        }
        #--( End )--> Add package files to list of used files

        return $result;
    }

    
    /**
     * Convert old include to new 
     * @param $includes
     * @return string
     */
    function convertLegacyIncluesToNewFormat($includes) {
        $result=false;
        $packages=array();
        
        $includes=preg_replace('%//(require|include)(_once)? *\(? *([\'"]([^\'"]*)[\'"]) *\)? *;%si','', $includes);
        if (preg_match_all('%(require|include)(_once)? *\(? *([\'"]([^\'"]*)[\'"]) *\)? *;%si', $includes, $files, PREG_PATTERN_ORDER)) {
            //$this->cmfcHtml_printr($files);
            foreach ($files[4] as $file) {
                $filePathParts=explode('/',$file);
                $type=$filePathParts[0];
                $name=null;
                $version=null;
                
                if ($type=='packages') {
                    $type='package';
                    $name=$filePathParts[1];
                    if (count($filePathParts)==4) {
                        $version=$filePathParts[2];
                        $file=str_replace(array('packages'.'/'.$name.'/'.$version.'/'),'',$file);
                    } else {
                        $version=null;
                        $file=str_replace(array('packages'.'/'.$name.'/'),'',$file);
                    }
                    
                    if (basename($file)==$name.'.class.inc.php') {
                        $file=null;
                    }
                    
                    if (basename($file)=='phpCompatibility.inc.php') {
                        $file=str_replace(basename($file),'compatibility.inc.php',$file);
                    }
                    
                    if (basename($file)=='userPermissionSystem.class.inc.php') {
                        $file=null;
                    }
                    
                    #--(Begin)-->Check package and package version altNames
                    $__r=$this->__getPackageInfo($type,$name,$version,false,$fileFull);
                    if (!$this->isError($__r)) {
                        $name=$__r['packageInfo']['machineName'];
                        $type=$__r['packageInfo']['type'];
                        $version=$__r['packageVersionInfo']['machineName'];
                    } else {
                        //echo $__r->getMessage().'<br />';
                    }
                    #--(End)-->Check package and package version altNames

                    $packages["$type-$name-$version"]['type']=$type;
                    $packages["$type-$name-$version"]['name']=$name;
                    $packages["$type-$name-$version"]['version']=$version;
                    if (!empty($file) and !in_array($file,$packages["$type-$name-$version"]['file'])) {
                        $packages["$type-$name-$version"]['file'][]=$file;
                    }
                } elseif ($type=='dependencies' and strpos(strtolower($file),'dependencies/pear/pear.php')===false) {
                    $type='packageExternalOld';
                    
                    $fileFull=str_replace(array('dependencies'.'/'),'',$file);
                    if (count($filePathParts)>2) {
                        $name=$filePathParts[1];
                        $file=str_replace(array('dependencies'.'/'.$name.'/'),'',$file);
                    } else {
                        $file=str_replace(array('dependencies'.'/'),'',$file);
                    }
                    
                    #--(Begin)-->Check if it's moved to packageExternal
                    $__r=$this->__getPackageInfo($type,$name,$version,false,array($fileFull));
                    if (!$this->isError($__r)) {
                        $name=$__r['packageInfo']['machineName'];
                        $type=$__r['packageInfo']['type'];
                        $version=$__r['packageVersionInfo']['machineName'];
                    } else {
                        //echo $__r->getMessage().'<br />';
                    }
                    #--(End)-->Check if it's moved to packageExternal
                    
                    $packages["$type-$name-$version"]['type']=$type;
                    $packages["$type-$name-$version"]['name']=$name;
                    $packages["$type-$name-$version"]['version']=$version;
                    if (!empty($file) and !in_array($file,$packages["$type-$name-$version"]['file'])) {
                        $packages["$type-$name-$version"]['file'][]=$file;
                    }
                } elseif (strpos(strtolower($file),'dependencies/pear/pear.php')!==false) {
                    $packages["package-cmf-v2"]['type']='package';
                    $packages["package-cmf-v2"]['name']='cmf';
                    $packages["package-cmf-v2"]['version']='v2';
                    $packages["package-cmf-v2"]['file'][]='PEAR.php';
                }
            }
        }
        //$this->cmfcHtml_printr($packages);
        
        if (!$this->isError($result)) {
            $result='//--(Begin)-->Loading generalLib'."\n".
            '$_ws[\'configurator\']->setOption(\'siteCacheFolderPath\',$_ws[\'siteInfo\'][\'path\'].\'/files/cache\');'."\n";
            
            $result.='$r=$_ws[\'configurator\']->packageManager->addPackages(array('."\n";
            foreach ($packages as $package) {
                if (!empty($package['file'])) {
                    $__s='array(';
                    $_comma='';
                    foreach ($package['file'] as $f) {
                        $__s.="$_comma'$f'";
                        $_comma=',';
                    }
                    $__s.=')';
                    $package['file']=$__s;
                } else {
                    $package['file']='\'\'';
                }
                if (empty($package['version'])) {
                    $package['version']='*';
                }
                $result.="    array('type'=>'{$package['type']}','name'=>'{$package['name']}','version'=>'{$package['version']}','file'=>{$package['file']},'exact'=>false,'optional'=>false,'onlyConsider'=>false),"."\n";
            }
            $result.='));'."\n";
            $result.=''.
                'if ($_ws[\'configurator\']->isError($r)){'."\n".
                '    echo $r->getMessage();'."\n".
                '    exit;'."\n".
                '};'."\n".
                '//--(End)-->Loading generalLib';
        }
        
        return $result;
        /*
        require_once('dependencies/pear/PEAR.php');
        require_once 'dependencies/pear/PHP/Compat/Function/scandir.php';
        require_once('dependencies/class.phpmailer.php');
        require_once('dependencies/excelWriter/excelwriter.inc.php');
        require_once('dependencies/jdf.php');
        
        require_once('packages/cmf/beta/classesCore.class.inc.php');
        require_once('packages/cmf/beta/utf8.php');
        require_once('packages/cmf/beta/common.inc.php');
        require_once('packages/cmf/beta/datetime.class.inc.php');
        require_once('packages/cmf/beta/phpCompatibility.inc.php');
        require_once('packages/cmf/beta/base.class.inc.php');
        require_once('packages/cmf/beta/mysql.class.inc.php');
        require_once('packages/cmf/beta/tableClassesBase2.class.inc.php');
        
        require_once('packages/userSystem/userSystem.class.inc.php');
        require_once('packages/userSystem/userPermissionSystem.class.inc.php');
        require_once('packages/emailTemplate/emailTemplate.class.inc.php');
        require_once('packages/emailSender/emailSender.class.inc.php');
        require_once('packages/javascript/javascript.class.inc.php');
        require_once('packages/richPaging/richPaging.class.inc.php');
        require_once('packages/paging/paging.class.inc.php');
        require_once('packages/validation/validation.class.inc.php');
        require_once('packages/search/search.class.inc.php');
        require_once('packages/wysiwyg/wysiwyg.class.inc.php');
        require_once('packages/imageManipulator/imageManipulator.class.inc.php');
        
        require_once('packages/hierarchicalSystem/hierarchicalSystem.class.inc.php');
        require_once('packages/hierarchicalSystem/dbOld/requirements/dbMySql/dbMySql.class.inc.php'); 
        */        
    }
    
    /*
     * It changes general_lib path to base and tries to resolve files. which means that
     * even if project does no have general_lib it can generate list of files to include
     */
    function printIncludedFiles() {
        
    }
    
}