<?php
/**
 * some fix for php default functions
 * @package cmf
 * @subpackage beta
 * @author Sina Salek
 * @version $Id: compatibility.inc.php 505 2010-01-23 12:07:33Z salek $
 */

/*
if $auto_length was true, function will
automatically recognize appropriate cut text length when mb_substr was callable
*/

//last name : 
if (!function_exists('substr_i')) {
	function substr_i($string,$start,$length,$encoding='UTF-8',$dot='...',$auto_length=true) {
			if (is_null($encoding)) {$encoding='UTF-8';}
	
			if (is_callable('mb_substr')) {
					if ($auto_length) {
							$length=$length-round($length/3);
					}
					$result=mb_substr($string,$start,$length,$encoding);
					if (mb_strlen($result)<mb_strlen($string))
							$result.=$dot;
			} else {
					$result=substr($string,$start,$length);
					if (strlen($result)<strlen($string))
							$result.=$dot;
			}
			return $result;
	}
}

//php5 has this function but as long as we use php4, below function is a good replacment
if (!function_exists('file_put_contents')) {
    // Define flags related to file_put_contents(), if necessary
    if (!defined('FILE_USE_INCLUDE_PATH')) {
        define('FILE_USE_INCLUDE_PATH', 1);
    }
    if (!defined('FILE_APPEND')) {
        define('FILE_APPEND', 8);
    }

    function file_put_contents($filename, $data, $flags = 0) {
        // Handle single dimensional array data
        if (is_array($data)) {
            // Join the array elements
            $data = implode('', $data);
        }

        // Flags should be an integral value
        $flags = (int)$flags;
        // Set the mode for fopen(), defaulting to 'wb'
        $mode = ($flags & FILE_APPEND) ? 'ab' : 'wb';
        $use_include_path = (bool)($flags & FILE_USE_INCLUDE_PATH);

        // Open file with filename as a string
        if ($fp = fopen("$filename", $mode, $use_include_path)) {
            // Acquire exclusive lock if requested
            if ($flags & LOCK_EX) {
                if (!flock($fp, LOCK_EX)) {
                    fclose($fp);
                    return false;
                }
            }

            // Write the data as a string
            $bytes = fwrite($fp, "$data");

            // Release exclusive lock if it was acquired
            if ($flags & LOCK_EX) {
                flock($fp, LOCK_UN);
            }

            fclose($fp);
            return $bytes; // number of bytes written
        } else {
            return false;
        }
    }
}


if (!function_exists('div')) {
        function div($a,$b) {
            return (int) ($a / $b);
        }
}


if (!function_exists('array_combine')) {
        function array_combine($a, $b) {
                $c = array();
                if (is_array($a) && is_array($b))
                        while (list(, $va) = each($a))
                                if (list(, $vb) = each($b))
                                        $c[$va] = $vb;
                                else
                                        break 1;
                return $c;
        }
}


/**
* Simple function to replicate PHP 5 behaviour
*/


//last name : get_file_extension
if (!function_exists('microtime_float')) {
        function microtime_float()
        {
           list($usec, $sec) = explode(" ", microtime());
           return ((float)$usec + (float)$sec);
        }
}


/*
very nice function for backward compatibilty when we want to use php5 'clone' keyword in php4
*/
if (version_compare(phpversion(), '5.0') < 0) {
    eval('
    function clone($object) {
      return $object;
    }
    ');
  }

  
  
/**
* array_merge_recursive2()
*
* Similar to array_merge_recursive but keyed-valued are always overwritten.
* Priority goes to the 2nd array.
*
* @static yes
* @public yes
* @param $paArray1 array
* @param $paArray2 array
* @return array
*/
if (!function_exists('array_merge_recursive2')) {
	function array_merge_recursive2($paArray1, $paArray2)
	{
	   if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
	   foreach ($paArray2 AS $sKey2 => $sValue2)
	   {
		   $paArray1[$sKey2] = array_merge_recursive2(@$paArray1[$sKey2], $sValue2);
	   }
	   return $paArray1;
	}
}

                  
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Tom Buskens <ortega@php.net>                                |
// |          Aidan Lister <aidan@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id: compatibility.inc.php 505 2010-01-23 12:07:33Z salek $


/**
 * Replace array_walk_recursive()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_walk_recursive
 * @author      Tom Buskens <ortega@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
if (!function_exists('array_walk_recursive')) {
    function array_walk_recursive(&$input, $funcname)
    {
        if (!is_callable($funcname)) {
            if (is_array($funcname)) {
                $funcname = $funcname[0] . '::' . $funcname[1];
            }
            user_error('array_walk_recursive() Not a valid callback ' . $user_func,
                E_USER_WARNING);
            return;
        }

        if (!is_array($input)) {
            user_error('array_walk_recursive() The argument should be an array',
                E_USER_WARNING);
            return;
        }

        $args = func_get_args();

        foreach ($input as $key => $item) {
            if (is_array($item)) {
                array_walk_recursive($item, $funcname, $args);
                $input[$key] = $item;
            } else {
                $args[0] = &$item;
                $args[1] = &$key;

                call_user_func_array($funcname, $args);

                $input[$key] = $item;
            }
        }
    }
}




/**
 * Replace str_ireplace()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.str_ireplace
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.18 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 * @note        count not by returned by reference, to enable
 *              change '$count = null' to '&$count'
 */
if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subject, $count = null)
    {
        // Sanity check
        if (is_string($search) && is_array($replace)) {
            user_error('Array to string conversion', E_USER_NOTICE);
            $replace = (string) $replace;
        }

        // If search isn't an array, make it one
        if (!is_array($search)) {
            $search = array ($search);
        }
        $search = array_values($search);

        // If replace isn't an array, make it one, and pad it to the length of search
        if (!is_array($replace)) {
            $replace_string = $replace;

            $replace = array ();
            for ($i = 0, $c = count($search); $i < $c; $i++) {
                $replace[$i] = $replace_string;
            }
        }
        $replace = array_values($replace);

        // Check the replace array is padded to the correct length
        $length_replace = count($replace);
        $length_search = count($search);
        if ($length_replace < $length_search) {
            for ($i = $length_replace; $i < $length_search; $i++) {
                $replace[$i] = '';
            }
        }

        // If subject is not an array, make it one
        $was_array = false;
        if (!is_array($subject)) {
            $was_array = true;
            $subject = array ($subject);
        }

        // Loop through each subject
        $count = 0;
        foreach ($subject as $subject_key => $subject_value) {
            // Loop through each search
            foreach ($search as $search_key => $search_value) {
                // Split the array into segments, in between each part is our search
                $segments = explode(strtolower($search_value), strtolower($subject_value));

                // The number of replacements done is the number of segments minus the first
                $count += count($segments) - 1;
                $pos = 0;

                // Loop through each segment
                foreach ($segments as $segment_key => $segment_value) {
                    // Replace the lowercase segments with the upper case versions
                    $segments[$segment_key] = substr($subject_value, $pos, strlen($segment_value));
                    // Increase the position relative to the initial string
                    $pos += strlen($segment_value) + strlen($search_value);
                }

                // Put our original string back together
                $subject_value = implode($replace[$search_key], $segments);
            }

            $result[$subject_key] = $subject_value;
        }

        // Check if subject was initially a string and return it as a string
        if ($was_array === true) {
            return $result[0];
        }

        // Otherwise, just return the array
        return $result;
    }
}


if (!function_exists('fnmatch')) {
	function fnmatch($pattern, $string) {
		return @preg_match(
			'/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
			array('*' => '.*', '?' => '.?')) . '$/i', $string
		);
	}
}


if(!function_exists('parse_ini_string'))
{
	function parse_ini_string($ini, $process_sections = false, $scanner_mode = null)
	{
		# Generate a temporary file.
		$tempname = tempnam('/tmp', 'ini');
		$fp = fopen($tempname, 'w');
		fwrite($fp, $ini);
		$ini = parse_ini_file($tempname, !empty($process_sections));
		fclose($fp);
		@unlink($tempname);
		return $ini;
	}
}

if (!is_callable('div')) {
    function div($a,$b) {
        return (int) ($a / $b);
    }
}


/**
 * Replace stripos()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.stripos
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null)
    {
        if (!is_scalar($haystack)) {
            user_error('stripos() expects parameter 1 to be string, ' .
                gettype($haystack) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_scalar($needle)) {
            user_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
            return false;
        }

        if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
            user_error('stripos() expects parameter 3 to be long, ' .
                gettype($offset) . ' given', E_USER_WARNING);
            return false;
        }

        // Manipulate the string if there is an offset
        $fix = 0;
        if (!is_null($offset)) {
            if ($offset > 0) {
                $haystack = substr($haystack, $offset, strlen($haystack) - $offset);
                $fix = $offset;
            }
        }

        $segments = explode(strtolower($needle), strtolower($haystack), 2);

        // Check there was a match
        if (count($segments) === 1) {
            return false;
        }

        $position = strlen($segments[0]) + $fix;
        return $position;
    }
}