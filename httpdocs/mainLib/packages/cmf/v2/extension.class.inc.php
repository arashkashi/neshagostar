<?php

/**
 * phpPlugin
 * 
 * Dynamicly extends any object with new functions (plugins)
 * or collections of functions that can be retrieved from files
 * outside the main class or from variables. 
 *
 * The name of the loaded plugins are stored in the object so
 * the same plugin is not going to be loaded more than once, but
 * you still can force a reload so a new plugin can replace an old one.
 *
 * @author   David Bittencourt <bemcomplicado-php@yahoo.com.br>
 * @author   Sina Salek
 * @todo 
 * 	- support for plugin and extension and easy implemntation of hooks
 * @version  1.0
 * @package  phpPlugin
 */


interface cmfcExtension
{
    /**
     * Array that keep track of the loaded plugins.
     *
     * @var     array
     * @access  private
     * @see     check_object()
     */
    var $_extensionsLoaded = array();
    
    /**
     * Configuration array for phpPlugin.
     *
     * @var     array
     * @access  private
     */
	var $_extensionsConfig=array(
		//path to the plugins files 
		'path'=>'plugins/',
		'fileName'=>"?.ext.inc.php",
	);

	
	function loadExtension($name,$sourceCode=null) {
		if (is_null($sourceCode)) {
			include_once($this->_extensionsConfig['path'].'/'.str_replace('?',$name,$this->_extensionsConfig['fileName']));
		} else {
			eval($sourceCode);
		}
	}
	
    /**
     * Loads a plugin in the object passed by $obj.
     *
     * @param   string   $strPlugin     Plugin's name. Will be used to define
     *                                  the filename.
     * @param   object   $obj           Object that will receive the plugin.
     * @param   string   $strClassName  Class name of the object.
	 * @param   boolean  $booForce      Force reload of the plugin.
     *
     * @return  string   New class name with the extended plugin.
     * @access  public
     * @uses    _check_object()
     * @uses    load_string()
     */
    function load($strPlugin, &$obj, $strClassName=null, $booForce=false)
    {
        $boo = $this->_check_object($strPlugin, $obj, $strClassName);

        if ($boo === false && $booForce === false) {
            return get_class($obj);
        }
        
        if (isset($this->_arrConfig["filename"]["classes"][$strClassName])) {
            $arrTemplate = $this->_arrConfig["filename"]["classes"][$strClassName];
        } else {
            $arrTemplate = $this->_arrConfig["filename"]["default"];        
        } 
        
        $strFileName = str_replace("?", $strPlugin, $arrTemplate[0]);

        if (!is_null($arrTemplate[1])) {
            array_unshift($arrTemplate[2], $strFileName);
            $strFileName = call_user_func_array($arrTemplate[1], $arrTemplate[2]);
        }

        $strFileName = $this->_arrConfig["plugins_path"] . $strFileName;
        
        if (!file_exists($strFileName)) {
            print "<br /><b>File doesn't exist '" . $strFileName . "'</b><br />";
            exit;
        }
        
        $arrContent = file($strFileName);
        
        $booContinue = true;
        $i=0;
        while ($booContinue) {
            $i++;
            foreach ($arrContent as $intLine => $strLine) {
                $strLine = trim($strLine);
                array_shift($arrContent);
                if (!empty($strLine)) {
                    break;
                } 
            }
            $arrContent = array_reverse($arrContent);
            if ($i==2) {
                $booContinue = false;
            }
        }
        unset($booContinue);

        $strContent = implode("\r\n", $arrContent);
        unset($arrConteudo);

        return $this->load_string($strPlugin, $strContent, $obj, $strClassName, $booForce);
        
    }

    /**
     * Loads a plugin from a variable in the object passed by $obj.
     *
     * @param   string   $strPlugin     Plugin's name. Will be used to define
     *                                  the filename.
     * @param   string   $strContent    Plugin's definition content.
     * @param   object   $obj           Object that will receive the plugin.
     * @param   string   $strClassName  Class name of the object.
 	 * @param   boolean  $booForce      Force reload of the plugin.
     *
     * @return  string   New class name with the extended plugin.
     * @access  public
     * @uses    _check_object()
     * @uses    _extend()
     */    
    function load_string($strPlugin, $strContent, &$obj, $strClassName=null, $booForce=false)
    {
        $boo = $this->_check_object($strPlugin, $obj, $strClassName);

        if ($boo === false && $booForce === false) {
            return get_class($obj);
        }
		
		if ($boo === true) {
	        $this->_arrExtended[$strClassName][] = $strPlugin;
		}
        
        return $this->_extend($obj, $strContent, $strClassName);
    }

    /**
     * Extends the class with the new plugin.
     *
     * @param   object   $obj           Object that will receive the plugin.
     * @param   string   $strContent    Plugin's definition content.
     *
     * @return  string   New class name with the extended plugin.
     * @access  private
     */    
    function _extend(&$obj, $strContent)
    {
        $strClass2Extend = get_class($obj);        

        mt_srand((double)microtime()*1000000);
        $strNewClass = $strClass2Extend . mt_rand(0, 999);
        
        eval("class " . $strNewClass . " extends " . $strClass2Extend . " { " . $strContent . " } ");
        
        unset($strContent);

        $objNew = new $strNewClass();
        
        $arrVars = get_class_vars($strClass2Extend);
        foreach ($arrVars as $key=>$value) {
            $objNew->$key =& $obj->$key;
        }
        unset($arrVars);

        $obj = $objNew;
        unset($objNew);
        
        return $strNewClass;
    }

    /**
     * Check if the plugin is already loaded from the Class passed by $strClassName.
     *
     * @param   object   $obj           Object that will receive the plugin.
     * @param   string   $strPlugin     Plugin's name. 
     * @param   string   $strClassName  Class name of the object.
     *
     * @return  boolean  False if the plugin is already loaded, true otherwise.
     * @access  private
     */
    function _check_object($strPlugin, $obj, &$strClassName)
    {
        if (is_null($strClassName) || empty($strClassName)) {
            $strClassName = get_class($obj);
        } 
        
        if (!isset($this->_arrExtended[$strClassName])) {
        
            $this->_arrExtended[$strClassName] = array();
        
        } else {
            
            $keyExt = array_search($strPlugin, $this->_arrExtended[$strClassName]);
            if ($keyExt !== false) {
                return false;
            }
        }
        return true;
    
    }

    
}

?>