<?php

/**
 *  A class to handle sessions by using a mySQL database for session related data storage providing better
 *  security then the default session handler used by PHP.
 *
 *  To prevent session hijacking, don't forget to use the {@link regenerate_id} method whenever you do a
 *  privilege change in your application
 *
 *  <i>Before usage, make sure you use the session_data.sql file from the <b>install</b> folder to set up the table
 *  used by the class</i>
 *
 *  After instantiating the class, use sessions as you would normally
 *
 *  This class is an adaptation of John Herren's code from the "Trick out your session handler" article
 *  ({@link http://devzone.zend.com/node/view/id/141}) and Chris Shiflett's code from Chapter 8, Shared Hosting - Pg 78-80,
 *  of his book - "Essential PHP Security" ({@link http://phpsecurity.org/code/ch08-2})
 *
 *  <i>Note that the class assumes that there is an active connection to a mySQL database and it does not attempt to create
 *  one. This is due to the fact that, usually, there is a config file that holds the database connection related
 *  information and another class, or function that handles database connection. If this is not how you do it, you can
 *  easily adapt the code by putting the database connection related code in the "open" method of the class.</i>
 *
 *  See the documentation for more info.
 *
 *  Read the LICENSE file, provided with the package, to find out how you can use this PHP script.
 *
 *  If you don't find this file, please write an email to noname at nivelzero dot ro and you will be sent a copy of the license file
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @todo should remove in favor of session packge general lib 3
 *  @version    1.0.3 (last revision: December 13, 2006)
 *  @copyright  (c) 2006 - 2007 Stefan Gabos
 *  @package    dbSession
 *  @example    example.php
*/

//error_reporting(E_ALL);

class dbSession extends cmfcClassesCore
{

	var $_tableName;
	/**
	 * Should be ip by default because of some incompatibility with flash based requests
	 * used to be userAgent
	 */
	var $_detectionIndicator='ip';  
	
    /**
     *  Constructor of class
     *
     *  Initializes the class and starts a new session
     *
     *  There is no need to call start_session() after instantiating this class
     *
     *  @param  integer     $gc_maxlifetime     (optional) the number of seconds after which data will be seen as 'garbage' and
     *                                          cleaned up on the next run of the gc (garbage collection) routine
     *
     *                                          Default is specified in php.ini file
     *
     *  @param  integer     $gc_probability     (optional) used in conjunction with gc_divisor, is used to manage probability that
     *                                          the gc routine is started. the probability is expressed by the formula
     *
     *                                          probability = $gc_probability / $gc_divisor
     *
     *                                          So if $gc_probability is 1 and $gc_divisor is 100 means that there is
     *                                          a 1% chance the the gc routine will be called on each request
     *
     *                                          Default is specified in php.ini file
     *
     *  @param  integer     $gc_divisor         (optional) used in conjunction with gc_probability, is used to manage probability
     *                                          that the gc routine is started. the probability is expressed by the formula
     *
     *                                          probability = $gc_probability / $gc_divisor
     *
     *                                          So if $gc_probability is 1 and $gc_divisor is 100 means that there is
     *                                          a 1% chance the the gc routine will be called on each request
     *
     *                                          Default is specified in php.ini file
     *
     *  @return void
     */
     
    function __construct($options)
    {
		$this->setOptions($options);
    	$gc_maxlifetime=$options['gc_maxlifetime'];
    	$gc_probability=$options['gc_probability'];
    	$gc_divisor=$options['gc_divisor'];

        // if $gc_maxlifetime is specified and is an integer number
        if ($gc_maxlifetime != "" && is_integer($gc_maxlifetime)) {
        
            // set the new value
            @ini_set('session.gc_maxlifetime', $gc_maxlifetime);
            
        }

        // if $gc_probability is specified and is an integer number
        if ($gc_probability != "" && is_integer($gc_probability)) {

            // set the new value
            @ini_set('session.gc_probability', $gc_probability);

        }

        // if $gc_divisor is specified and is an integer number
        if ($gc_divisor != "" && is_integer($gc_divisor)) {

            // set the new value
            @ini_set('session.gc_divisor', $gc_divisor);

        }
         
        // get session lifetime
        $this->sessionLifetime = ini_get("session.gc_maxlifetime");
        
        // register the new handler
        session_set_save_handler(
            array(&$this, 'open'),
            array(&$this, 'close'),
            array(&$this, 'read'),
            array(&$this, 'write'),
            array(&$this, 'destroy'),
            array(&$this, 'gc')
        );
        //register_shutdown_function('session_write_close');
        register_shutdown_function(array(&$this, 'gc'));
        
        // start the session
        session_start();
        
    }
    
    /**
     *  Deletes all data related to the session
     *
     *  @since 1.0.1
     *
     *  @return void
     */          
    function stop()
    {
        $this->regenerate_id();
        session_unset();
        session_destroy();
    }
    
    /**
     *  Regenerates the session id.
     *
     *  <b>Call this method whenever you do a privilege change!</b>
     *
     *  @return void
     */
    function regenerate_id()
    {

        // saves the old session's id
        $oldSessionID = session_id();
        
        // regenerates the id
        // this function will create a new session, with a new id and containing the data from the old session
        // but will not delete the old session
        session_regenerate_id();
        
        // because the session_regenerate_id() function does not delete the old session,
        // we have to delete it manually
        $this->destroy($oldSessionID);
        
    }
    
    /**
     *  Get the number of online users
     *
     *  @return integer     number of users currently online
     */
    function get_users_online()
    {

        // call the garbage collector
        $this->gc($this->sessionLifetime);

        // counts the rows from the database
        $result = @cmfcMySql::fetchAssoc(@cmfcMySql::query("
            SELECT
                COUNT(session_id) as count
            FROM ".$this->_tableName."
        "));
        
        // return the number of found rows
        return $result["count"];
        
    }
    
    /**
     *  Custom open() function
     *
     *  @access private
     */
    function open($save_path, $session_name)
    {
    
        return true;
        
    }
    
    /**
     *  Custom close() function
     *
     *  @access private
     */
    function close()
    {
        return true;
    }
    
    /**
     *  Custom read() function
     *
     *  @access private
     */
    function read($session_id)
    {              

        // reads session data associated with the session id
        // but only if the HTTP_USER_AGENT is the same as the one who had previously written to this session
        // and if session has not expired
		$agent='';
        if ($this->_detectionIndicator=='userAgent') {
	    $agent=$_SERVER["HTTP_USER_AGENT"];
        } elseif ($this->_detectionIndicator=='ip') {
	    $agent=$_SERVER['REMOTE_ADDR'];
        }
        
		$sqlQuery="SELECT
                *
            FROM
                ".$this->_tableName."
            WHERE
                session_id = '".cmfcMySql::realEscapeString($session_id)."' 
                AND session_expire > '".time()."'
           LIMIT 1
        ";
        $result = @cmfcMySql::query($sqlQuery);
        
        // if anything was found
        if (is_resource($result) && @cmfcMySql::numRows($result) > 0) {

            // return found data
            $fields = @cmfcMySql::fetchAssoc($result);
            //cmfcHtml::printr($fields);exit;
            // prevent session fixation
            
            if ($fields['http_user_agent']!=cmfcMySql::realEscapeString($agent)) {
            	$_SESSION = array();
	            // If it's desired to kill the session, also delete the session cookie.
				// Note: This will destroy the session, and not just the session data!
				if (ini_get("session.use_cookies")) {
				    $params = session_get_cookie_params();
				    setcookie(session_name(), '', time() - 42000,
				        $params["path"], $params["domain"],
				        $params["secure"]//, $params["httponly"]
				    );
				}
				// Finally, destroy the session.
				@session_destroy();
				
            	$result="";
            } else {
            	$result=$fields["session_data"];
            }
            
            // don't bother with the unserialization - PHP handles this automatically
            return $result;
            
        }
        
        // if there was an error return an empty string - this HAS to be an empty string
        return "";

    }
    
    /**
     *  Custom write() function
     *
     *  @access private
     */
    function write($session_id, $session_data)
    {
    
        // first checks if there is a session with this id
        $result = @cmfcMySql::query("
            SELECT
                *
            FROM
                ".$this->_tableName."
            WHERE
                session_id = '".cmfcMySql::realEscapeString($session_id)."'
        ");
        
        // if there is
        if (@cmfcMySql::numRows($result) > 0) {

            // update the existing session's data
            // and set new expiry time
            $result = @cmfcMySql::exec("
                UPDATE
                    ".$this->_tableName."
                SET
                    session_data = '".cmfcMySql::realEscapeString($session_data)."',
                    session_expire = '".cmfcMySql::realEscapeString(time() + $this->sessionLifetime)."'
                WHERE
                    session_id = '".cmfcMySql::realEscapeString($session_id)."'
            ");
            
            // if anything happened
            if (@cmfcMySql::affectedRows()) {
            
                // return true
                return true;
                
            }

        // if this session id is not in the database
        } else {

	$agent='';
	  if ($this->_detectionIndicator=='userAgent') {
	      $agent=$_SERVER["HTTP_USER_AGENT"];
	  } elseif ($this->_detectionIndicator=='ip') {
	      $agent=$_SERVER['REMOTE_ADDR'];;
	  }

            // insert a new record
            $result = @cmfcMySql::exec("
                INSERT INTO
                    ".$this->_tableName."
                        (
                            session_id,
                            http_user_agent,
                            session_data,
                            session_expire
                        )
                    VALUES
                        (
                            '".cmfcMySql::realEscapeString($session_id)."',
                            '".cmfcMySql::realEscapeString($agent)."',
                            '".cmfcMySql::realEscapeString($session_data)."',
                            '".cmfcMySql::realEscapeString(time() + $this->sessionLifetime)."'
                        )
            ");
            
            // if anything happened
            if (@cmfcMySql::affectedRows()) {
            
                // return an empty string
                return "";
                
            }
            
        }
        
        // if something went wrong, return false
        return false;
        
    }
    
    /**
     *  Custom destroy() function
     *
     *  @access private
     */
    function destroy($session_id)
    {
        // deletes the current session id from the database
        $result = @cmfcMySql::exec("
            DELETE FROM
                ".$this->_tableName."
            WHERE
                session_id = '".cmfcMySql::realEscapeString($session_id)."'
        ");
        
        // if anything happened
        if (@cmfcMySql::affectedRows()) {
        	
            // return true
            return true;
            
        }

        // if something went wrong, return false
        return false;
        
    }
    
    /**
     *  Custom gc() function (garbage collector)
     *
     *  @access private
     */
    function gc($maxlifetime=null)
    {
    	$sqlQuery="
            DELETE FROM
                ".$this->_tableName."
            WHERE
                session_expire < '".cmfcMySql::realEscapeString(time() - $maxlifetime)."'
        ";
        // it deletes expired sessions from database
        $result = @cmfcMySql::query($sqlQuery);
    }
    
    
    function createTable() {
    	$sqlQuery="
			CREATE TABLE `".$this->_tableName."` (
			  `session_id` varchar(32) NOT NULL default '',
			  `http_user_agent` text NOT NULL default '',
			  `session_data` blob NOT NULL,
			  `session_expire` int(11) NOT NULL default '0',
			  PRIMARY KEY  (`session_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
    	";
    	return cmfcMySql::exec($sqlQuery);
	}
    
}
?>