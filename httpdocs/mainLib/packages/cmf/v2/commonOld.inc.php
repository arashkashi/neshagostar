<?php
/**
 * 
 * @package cmf
 * @subpackage beta
 * @author Sina Salek
 * @version $Id: commonOld.inc.php 184 2008-10-23 07:58:31Z sinasalek $
 * 
 * @todo some of its functions did not port to common.inc.php
 * @todo should remove as of general lib 3
 */


/**
 * Here's a function to get the name of a given variable.  Explanation and examples below

        The problem with figuring out what value is what key in that variables scope is that several variables might have the same value.
        To remedy this, the variable is passed by reference and its value is then modified to a random value to make sure there will be a
        unique match.  Then we loop through the scope the variable is contained in and when there is a match of our modified value, we can
         grab the correct key.

        Examples:

        1.  Use of a variable contained in the global scope (default):append_column
        <?php
          $my_global_variable = "My global string.";
          echo vname($my_global_variable); // Outputs:  my_global_variable
        ?>

        2.  Use of a local variable :
        <?php
                function my_local_func()
                {
                        $my_local_variable = "My local string.";
                        return vname($my_local_variable, get_defined_vars());
                }
                echo my_local_func(); // Outputs: my_local_variable
        ?>

        3.  Use of an object property :
        <?php
                class myclass
                {
                        public function __constructor()
                        {
                                $this->y_object_property = "My object property  string.";
                        }
                }
                $obj = new myclass;
                echo vname($obj->y_object_property, $obj); // Outputs: my_object_property
        ?>

 *
 * @param pointer $var
 * @param boolean $scope
 * @param string $prefix
 * @param string $suffix
 * @return string
 */
//last name : variable_name
function cmfVariableName(&$var, $scope=false, $prefix='unique', $suffix='value')
{
        if($scope) {$vals = $scope;}
                else {$vals = $GLOBALS;}
        $old = $var;
        $var = $new = $prefix.rand().$suffix;
        $vname = FALSE;
        foreach($vals as $key => $val) {
                if($val === $new) {$vname = $key;}
        }
        $var = $old;
        return $vname;
}












/**
* @return array address of founded value, example : $result[1]=() $result[2]=() $result[3]=()
* @param $array array
* @param $function string name of callback function, this function call when found new array index
* @param $glArrayValue variant value that you want to search in array
* @desc walk in all array indexes, childs to parents and parents to childs
* sample :
* $f['a1']['a2']['a3']='test';
* $f['a1']['a5']['a7']['a9']='dtest';
* $f['b1']['b2']['b3']='b';
*
* function test($key, $value,$ancestor_array,$array,$founded) { echo "$key=$value <br>"; }
* if (cmfArrayWalkAll($f, 'test','dtest',$f)) { echo 'true';}
* array(4) { [0]=> string(2) "a1" [1]=> string(2) "a5" [2]=> string(2) "a7" [3]=> string(2) "a9" }
* NOTICE: i tested this function
*/
//last name : array_walk_all
function cmfArrayWalkAll(&$array,$function,$glArrayValue=null,$ancestor_array,$result=false)
{
        $ancestor_array[]=$key;
        foreach ($array as $key => $value)
        {
                $ancestor_array[count($ancestor_array)-1]=$key;
                if ($value==$glArrayValue) { $result=true; $founded=$result;};
                if (function_exists($function)) { $function($key,$value,$ancestor_array,&$array,$founded); }
                if (is_array($value))
                {
                        //i user (&$array[$key]) this code because i need point to original array for feutures use
                        $result=cmfArrayWalkAll(&$array[$key],$function,$glArrayValue,$ancestor_array,$result);
                }
        }
        return $result;
}





