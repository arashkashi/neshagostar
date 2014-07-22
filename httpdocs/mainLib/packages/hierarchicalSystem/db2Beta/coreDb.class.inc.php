<?php
require(dirname(__FILE__).'/base.class.inc.php');
/**
* $Id: coreDb.class.inc.php 184 2008-10-23 07:58:31Z sinasalek $
*
* Copyright (C) 2005 Kuzma Feskov <kuzma@russofile.ru>
*
* KF_SITE_VERSION
*
* CLASS DESCRIPTION:
* This class can be used to manipulate nested sets of database table
* records that form an hierarchical tree.
* 
* It provides means to initialize the record tree, insert record nodes
* in specific tree positions, retrieve node and parent records, change
* position of nodes and delete record nodes.
* 
* It uses ANSI SQL statements and abstract DB libraryes, such as:
* ADODB      Provides full functionality of the class: to make it
*            work with many database types, support transactions,
*            and caching of SQL queries to minimize database
*            access overhead
* DB_MYSQL   The class-example showing variant of creation of the own
*            engine for dialogue with a database, it's emulate
*            some ADODB functions (ATTENTION, class only shows variant
*            of a spelling of the driver, use it only as example)
* 
* The library works with support multilanguage interface of
* technology GetText (GetText autodetection).
* 
* This source file is part of the KFSITE Open Source Content
* Management System.
*
* This file may be distributed and/or modified under the terms of the
* "GNU General Public License" version 2 as published by the Free
* Software Foundation and appearing in the file LICENSE included in
* the packaging of this file.
*
* This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
* THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
* PURPOSE.
*
* The "GNU General Public License" (GPL) is available at
* http://www.gnu.org/copyleft/gpl.html.
* 
* CHANGELOG:
*
* v2.0
*
* [+] GetText autodetect added
* 
* [+] DB libraries abstraction added
*/
// previous name : dbtree
class cmfcHierarchicalSystemDbBetaDbTree extends cmfcHierarchicalSystemDbBetaBase{
    /**
    * Detailed errors of a class (for the programmer and log-files)
    * array('error type (1 - fatal (write log), 2 - fatal (write log, send email)',
    * 'error info string', 'function', 'info 1', 'info 2').
    * 
    * @var array
    */
    var $ERRORS = array();

    /**
    * The information on a error for the user
    * array('string (error information)').
    * 
    * @var array
    */
    var $ERRORS_MES = array();

    /**
    * Name of the table where tree is stored.
    * 
    * @var string
    */
    var $table;

    /**
    * Unique number of node.
    * 
    * @var bigint
    */
    var $_colnId;

    /**
    * @var integer
    */
    var $_colnLeftNumber;

    /**
    * @var integer
    */
    var $_colnRightNumber;

    /**
    * Level of nesting.
    * 
    * @var integer
    */
    var $_colnLevelNumber;

    /**
    * DB resource object.
    * 
    * @var object
    */
    var $res;

    /**
    * Databse layer object.
    * 
    * @var object
    */
    var $_db;

    /**
    * The class constructor: initializes dbtree variables.
    * 
    * @param string $table Name of the table
    * @param string $prefix A prefix for fields of the table(the example, mytree_id. 'mytree' is prefix)
    * @param object $db
    * @return object
    */
	/*
    function cmfcHierarchicalSystemDbTree($table, $prefix, &$db) {
        $this->_db = &$db;
        $this->_tableName = $table;
        $this->_colnId = $prefix . '_id';
        $this->_colnLeftNumber = $prefix . '_left';
        $this->_colnRightNumber = $prefix . '_right';
        $this->_colnLevelNumber = $prefix . '_level';
        unset($prefix, $table);
    }
	*/
    /**
    * Sets initial parameters of a tree and creates root of tree
    * ATTENTION, all previous values in table are destroyed.
    * 
    * @param array $data Contains parameters for additional fields of a tree (if is): 'filed name' => 'importance'
    * @return bool TRUE if successful, FALSE otherwise.
    */
    function clear($data = array()) {
        $sql = 'TRUNCATE ' . $this->_tableName;
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        $sql = 'DELETE FROM ' . $this->_tableName;
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        if (!empty($data)) {
            $fld_names = implode(', ', array_keys($data)) . ', ';
            $fld_values = '\'' . implode('\', \'', array_values($data)) . '\', ';
        }
        $fld_names .= $this->_colnLeftNumber . ', ' . $this->_colnRightNumber . ', ' . $this->_colnLevelNumber;
        $fld_values .= '1, 2, 0';
        $id = $this->_db->GenID($this->_tableName . '_seq', 1);
        $sql = 'INSERT INTO ' . $this->_tableName . ' (' . $this->_colnId . ', ' . $fld_names . ') VALUES (' . $id . ', ' . $fld_values . ')';
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        return TRUE;
    }

    /**
    * Receives left, right and level for unit with number id.
    *
    * @param integer $section_id Unique section id
    * @param integer $cache Recordset is cached for $cache microseconds
    * @return array - left, right, level
    */
    function getNodeInfo($section_id, $cache = FALSE) {
        $sql = 'SELECT ' . $this->_colnLeftNumber . ', ' . $this->_colnRightNumber . ', ' . $this->_colnLevelNumber . ' FROM ' . $this->_tableName . ' WHERE ' . $this->_colnId . ' = ' . (int)$section_id;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute((int)$cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        if (0 == $res->recordCount()) {
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('no_element_in_tree') : 'no_element_in_tree';
            return FALSE;
        }
        $data = $res->FetchRow();
        unset($res);
        return array($data[$this->_colnLeftNumber], $data[$this->_colnRightNumber], $data[$this->_colnLevelNumber]);
    }

    /**
    * Receives parent left, right and level for unit with number $id.
    *
    * @param integer $section_id
    * @param integer $cache Recordset is cached for $cache microseconds
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @return array - left, right, level
    */
    function getParentInfo($section_id, $condition = '', $cache = FALSE) {
        $node_info = $this->getNodeInfo($section_id);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId, $rightId, $level) = $node_info;
        $level--;
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        $sql = 'SELECT * FROM ' . $this->_tableName
        . ' WHERE ' . $this->_colnLeftNumber . ' < ' . $leftId
        . ' AND ' . $this->_colnRightNumber . ' > ' . $rightId
        . ' AND ' . $this->_colnLevelNumber . ' = ' . $level
        . $condition
        . ' ORDER BY ' . $this->_colnLeftNumber;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute((int)$cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        return $res->FetchRow();
    }


    /**
    * Add a new element in the tree to element with number $section_id.
    *
    * @param integer $section_id Number of a parental element
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @param array $data Contains parameters for additional fields of a tree (if is): array('filed name' => 'importance', etc)
    * @return integer Inserted element id
    */
    function insert($section_id, $condition = '', $data = array()) {
        $node_info = $this->getNodeInfo($section_id);
		
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId, $rightId, $level) = $node_info;
        $data[$this->_colnLeftNumber] = $rightId;
        $data[$this->_colnRightNumber] = ($rightId + 1);
        $data[$this->_colnLevelNumber] = ($level + 1);
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        $sql = 'UPDATE ' . $this->_tableName . ' SET '
        . $this->_colnLeftNumber . '=CASE WHEN ' . $this->_colnLeftNumber . '>' . $rightId . ' THEN ' . $this->_colnLeftNumber . '+2 ELSE ' . $this->_colnLeftNumber . ' END, '
        . $this->_colnRightNumber . '=CASE WHEN ' . $this->_colnRightNumber . '>=' . $rightId . ' THEN ' . $this->_colnRightNumber . '+2 ELSE ' . $this->_colnRightNumber . ' END '
        . 'WHERE ' . $this->_colnRightNumber . '>=' . $rightId;
        $sql .= $condition;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE ' . $this->_colnId . ' = -1';
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $data[$this->_colnId] = $this->_db->GenID($this->_tableName . '_seq', 2);
        $sql = $this->_db->GetInsertSQL($res, $data);
        if (!empty($sql)) {
            $res = $this->_db->Execute($sql);
            if (FALSE === $res) {
                $this->ERRORS[] = array(2, 'SQL query error', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
                $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
                $this->_db->FailTrans();
                return FALSE;
            }
        }
        $this->_db->CompleteTrans();

        return $data[$this->_colnId];
    }

    /**
    * Add a new element in the tree near element with number id.
    *
    * @param integer $ID Number of a parental element
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @param array $data Contains parameters for additional fields of a tree (if is): array('filed name' => 'importance', etc)
    * @return integer Inserted element id
    */
    function insertNear($ID, $condition = '', $data = array()) {
        $node_info = $this->getNodeInfo($ID);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId, $rightId, $level) = $node_info;
        $data[$this->_colnLeftNumber] = ($rightId + 1);
        $data[$this->_colnRightNumber] = ($rightId + 2);
        $data[$this->_colnLevelNumber] = ($level);
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        $sql = 'UPDATE ' . $this->_tableName . ' SET '
        . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' > ' . $rightId . ' THEN ' . $this->_colnLeftNumber . ' + 2 ELSE ' . $this->_colnLeftNumber . ' END, '
        . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnRightNumber . '> ' . $rightId . ' THEN ' . $this->_colnRightNumber . ' + 2 ELSE ' . $this->_colnRightNumber . ' END, '
        . 'WHERE ' . $this->_colnRightNumber . ' > ' . $rightId;
        $sql .= $condition;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE ' . $this->_colnId . ' = -1';
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $data[$this->_colnId] = $this->_db->GenID($this->_tableName . '_seq', 2);
        $sql = $this->_db->GetInsertSQL($res, $data);
        if (!empty($sql)) {
            $res = $this->_db->Execute($sql);
            if (FALSE === $res) {
                $this->ERRORS[] = array(2, 'SQL query error', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
                $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
                $this->_db->FailTrans();
                return FALSE;
            }
        }
        $this->_db->CompleteTrans();
        return $data[$this->_colnId];
    }

    /**
    * Assigns a node with all its children to another parent.
    *
    * @param integer $ID node ID
    * @param integer $newParentId ID of new parent node
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @return bool TRUE if successful, FALSE otherwise.
    */
    function moveAll($ID, $newParentId, $condition = '') {
        $node_info = $this->getNodeInfo($ID);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId, $rightId, $level) = $node_info;
        $node_info = $this->getNodeInfo($newParentId);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftIdP, $rightIdP, $levelP) = $node_info;
        if ($ID == $newParentId || $leftId == $leftIdP || ($leftIdP >= $leftId && $leftIdP <= $rightId) || ($level == $levelP+1 && $leftId > $leftIdP && $rightId < $rightIdP)) {
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('cant_move_tree') : 'cant_move_tree';
            return FALSE;
        }
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        if ($leftIdP < $leftId && $rightIdP > $rightId && $levelP < $level - 1) {
            $sql = 'UPDATE ' . $this->_tableName . ' SET '
            . $this->_colnLevelNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLevelNumber.sprintf('%+d', -($level-1)+$levelP) . ' ELSE ' . $this->_colnLevelNumber . ' END, '
            . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnRightNumber . ' BETWEEN ' . ($rightId+1) . ' AND ' . ($rightIdP-1) . ' THEN ' . $this->_colnRightNumber . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnRightNumber . '+' . ((($rightIdP-$rightId-$level+$levelP)/2)*2+$level-$levelP-1) . ' ELSE ' . $this->_colnRightNumber . ' END, '
            . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId+1) . ' AND ' . ($rightIdP-1) . ' THEN ' . $this->_colnLeftNumber . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLeftNumber . '+' . ((($rightIdP-$rightId-$level+$levelP)/2)*2+$level-$levelP-1) . ' ELSE ' . $this->_colnLeftNumber . ' END '
            . 'WHERE ' . $this->_colnLeftNumber . ' BETWEEN ' . ($leftIdP+1) . ' AND ' . ($rightIdP-1);
        } elseif ($leftIdP < $leftId) {
            $sql = 'UPDATE ' . $this->_tableName . ' SET '
            . $this->_colnLevelNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLevelNumber.sprintf('%+d', -($level-1)+$levelP) . ' ELSE ' . $this->_colnLevelNumber . ' END, '
            . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $rightIdP . ' AND ' . ($leftId-1) . ' THEN ' . $this->_colnLeftNumber . '+' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLeftNumber . '-' . ($leftId-$rightIdP) . ' ELSE ' . $this->_colnLeftNumber . ' END, '
            . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnRightNumber . ' BETWEEN ' . $rightIdP . ' AND ' . $leftId . ' THEN ' . $this->_colnRightNumber . '+' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_colnRightNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnRightNumber . '-' . ($leftId-$rightIdP) . ' ELSE ' . $this->_colnRightNumber . ' END '
            . 'WHERE (' . $this->_colnLeftNumber . ' BETWEEN ' . $leftIdP . ' AND ' . $rightId. ' '
            . 'OR ' . $this->_colnRightNumber . ' BETWEEN ' . $leftIdP . ' AND ' . $rightId . ')';
        } else {
            $sql = 'UPDATE ' . $this->_tableName . ' SET '
            . $this->_colnLevelNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLevelNumber.sprintf('%+d', -($level-1)+$levelP) . ' ELSE ' . $this->_colnLevelNumber . ' END, '
            . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $rightId . ' AND ' . $rightIdP . ' THEN ' . $this->_colnLeftNumber . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLeftNumber . '+' . ($rightIdP-1-$rightId) . ' ELSE ' . $this->_colnLeftNumber . ' END, '
            . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnRightNumber . ' BETWEEN ' . ($rightId+1) . ' AND ' . ($rightIdP-1) . ' THEN ' . $this->_colnRightNumber . '-' . ($rightId-$leftId+1) . ' '
            . 'WHEN ' . $this->_colnRightNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnRightNumber . '+' . ($rightIdP-1-$rightId) . ' ELSE ' . $this->_colnRightNumber . ' END '
            . 'WHERE (' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightIdP . ' '
            . 'OR ' . $this->_colnRightNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightIdP . ')';
        }
        $sql .= $condition;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $this->_db->CompleteTrans();
        return TRUE;
    }

    /**
    * Change items position.
    *
    * @param integer $id1 first item ID
    * @param integer $id2 second item ID
    * @return bool TRUE if successful, FALSE otherwise.
    */
    function changePosition($id1, $id2) {
        $node_info = $this->getNodeInfo($id1);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId1, $rightId1, $level1) = $node_info;
        $node_info = $this->getNodeInfo($id2);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId2, $rightId2, $level2) = $node_info;
        $sql = 'UPDATE ' . $this->_tableName . ' SET '
        . $this->_colnLeftNumber . ' = ' . $leftId2 .', '
        . $this->_colnRightNumber . ' = ' . $rightId2 .', '
        . $this->_colnLevelNumber . ' = ' . $level2 .' '
        . 'WHERE ' . $this->_colnId . ' = ' . (int)$id1;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $sql = 'UPDATE ' . $this->_tableName . ' SET '
        . $this->_colnLeftNumber . ' = ' . $leftId1 .', '
        . $this->_colnRightNumber . ' = ' . $rightId1 .', '
        . $this->_colnLevelNumber . ' = ' . $level1 .' '
        . 'WHERE ' . $this->_colnId . ' = ' . (int)$id2;
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $this->_db->CompleteTrans();
        return TRUE;
    }

    /**
    * Swapping nodes within the same level and limits of one parent with all its children: $id1 placed before or after $id2.
    *
    * @param integer $id1 first item ID
    * @param integer $id2 second item ID
    * @param string $position 'before' or 'after' $id2
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @return bool TRUE if successful, FALSE otherwise.
    */
    function changePositionAll($id1, $id2, $position = 'after', $condition = '') {
        $node_info = $this->getNodeInfo($id1);
        if (FALSE === $node_info) {
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('cant_change_position') : 'cant_change_position';
            return FALSE;
        }
        list($leftId1, $rightId1, $level1) = $node_info;
        $node_info = $this->getNodeInfo($id2);
        if (FALSE === $node_info) {
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('cant_change_position') : 'cant_change_position';
            return FALSE;
        }
        list($leftId2, $rightId2, $level2) = $node_info;
        if ($level1 <> $level2) {
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('cant_change_position') : 'cant_change_position';
            return FALSE;
        }
        if ('before' == $position) {
            if ($leftId1 > $leftId2) {
                $sql = 'UPDATE ' . $this->_tableName . ' SET '
                . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnRightNumber . ' - ' . ($leftId1 - $leftId2) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId2 . ' AND ' . ($leftId1 - 1) . ' THEN ' . $this->_colnRightNumber . ' +  ' . ($rightId1 - $leftId1 + 1) . ' ELSE ' . $this->_colnRightNumber . ' END, '
                . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnLeftNumber . ' - ' . ($leftId1 - $leftId2) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId2 . ' AND ' . ($leftId1 - 1) . ' THEN ' . $this->_colnLeftNumber . ' + ' . ($rightId1 - $leftId1 + 1) . ' ELSE ' . $this->_colnLeftNumber . ' END '
                . 'WHERE ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId2 . ' AND ' . $rightId1;
            } else {
                $sql = 'UPDATE ' . $this->_tableName . ' SET '
                . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnRightNumber . ' + ' . (($leftId2 - $leftId1) - ($rightId1 - $leftId1 + 1)) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId1 + 1) . ' AND ' . ($leftId2 - 1) . ' THEN ' . $this->_colnRightNumber . ' - ' . (($rightId1 - $leftId1 + 1)) . ' ELSE ' . $this->_colnRightNumber . ' END, '
                . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnLeftNumber . ' + ' . (($leftId2 - $leftId1) - ($rightId1 - $leftId1 + 1)) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId1 + 1) . ' AND ' . ($leftId2 - 1) . ' THEN ' . $this->_colnLeftNumber . ' - ' . ($rightId1 - $leftId1 + 1) . ' ELSE ' . $this->_colnLeftNumber . ' END '
                . 'WHERE ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . ($leftId2 - 1);
            }
        }
        if ('after' == $position) {
            if ($leftId1 > $leftId2) {
                $sql = 'UPDATE ' . $this->_tableName . ' SET '
                . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnRightNumber . ' - ' . ($leftId1 - $leftId2 - ($rightId2 - $leftId2 + 1)) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId2 + 1) . ' AND ' . ($leftId1 - 1) . ' THEN ' . $this->_colnRightNumber . ' +  ' . ($rightId1 - $leftId1 + 1) . ' ELSE ' . $this->_colnRightNumber . ' END, '
                . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnLeftNumber . ' - ' . ($leftId1 - $leftId2 - ($rightId2 - $leftId2 + 1)) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId2 + 1) . ' AND ' . ($leftId1 - 1) . ' THEN ' . $this->_colnLeftNumber . ' + ' . ($rightId1 - $leftId1 + 1) . ' ELSE ' . $this->_colnLeftNumber . ' END '
                . 'WHERE ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId2 + 1) . ' AND ' . $rightId1;
            } else {
                $sql = 'UPDATE ' . $this->_tableName . ' SET '
                . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnRightNumber . ' + ' . ($rightId2 - $rightId1) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId1 + 1) . ' AND ' . $rightId2 . ' THEN ' . $this->_colnRightNumber . ' - ' . (($rightId1 - $leftId1 + 1)) . ' ELSE ' . $this->_colnRightNumber . ' END, '
                . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN ' . $this->_colnLeftNumber . ' + ' . ($rightId2 - $rightId1) . ' '
                . 'WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . ($rightId1 + 1) . ' AND ' . $rightId2 . ' THEN ' . $this->_colnLeftNumber . ' - ' . ($rightId1 - $leftId1 + 1) . ' ELSE ' . $this->_colnLeftNumber . ' END '
                . 'WHERE ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId1 . ' AND ' . $rightId2;
            }
        }
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        $sql .= $condition;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $this->_db->CompleteTrans();
        return TRUE;
    }

    /**
    * Delete element with number $id from the tree wihtout deleting it's children.
    *
    * @param integer $ID Number of element
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @return bool TRUE if successful, FALSE otherwise.
    */
    function delete($ID, $condition = '') {
        $node_info = $this->getNodeInfo($ID);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId, $rightId) = $node_info;
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        $sql = 'DELETE FROM ' . $this->_tableName . ' WHERE ' . $this->_colnId . ' = ' . (int)$ID;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $sql = 'UPDATE ' . $this->_tableName . ' SET '
        . $this->_colnLevelNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLevelNumber . ' - 1 ELSE ' . $this->_colnLevelNumber . ' END, '
        . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnRightNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnRightNumber . ' - 1 '
        . 'WHEN ' . $this->_colnRightNumber . ' > ' . $rightId . ' THEN ' . $this->_colnRightNumber . ' - 2 ELSE ' . $this->_colnRightNumber . ' END, '
        . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId . ' THEN ' . $this->_colnLeftNumber . ' - 1 '
        . 'WHEN ' . $this->_colnLeftNumber . ' > ' . $rightId . ' THEN ' . $this->_colnLeftNumber . ' - 2 ELSE ' . $this->_colnLeftNumber . ' END '
        . 'WHERE ' . $this->_colnRightNumber . ' > ' . $leftId;
        $sql .= $condition;
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $this->_db->CompleteTrans();
        return TRUE;
    }

    /**
    * Delete element with number $ID from the tree and all it childret.
    *
    * @param integer $ID Number of element
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @return bool TRUE if successful, FALSE otherwise.
    */
    function deleteAll($ID, $condition = '') {
        $node_info = $this->getNodeInfo($ID);
        if (FALSE === $node_info) {
            return FALSE;
        }
        list($leftId, $rightId) = $node_info;
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition);
        }
        $sql = 'DELETE FROM ' . $this->_tableName . ' WHERE ' . $this->_colnLeftNumber . ' BETWEEN ' . $leftId . ' AND ' . $rightId;
        $this->_db->StartTrans();
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $deltaId = (($rightId - $leftId) + 1);
        $sql = 'UPDATE ' . $this->_tableName . ' SET '
        . $this->_colnLeftNumber . ' = CASE WHEN ' . $this->_colnLeftNumber . ' > ' . $leftId.' THEN ' . $this->_colnLeftNumber . ' - ' . $deltaId . ' ELSE ' . $this->_colnLeftNumber . ' END, '
        . $this->_colnRightNumber . ' = CASE WHEN ' . $this->_colnRightNumber . ' > ' . $leftId . ' THEN ' . $this->_colnRightNumber . ' - ' . $deltaId . ' ELSE ' . $this->_colnRightNumber . ' END '
        . 'WHERE ' . $this->_colnRightNumber . ' > ' . $rightId;
        $sql .= $condition;
        $res = $this->_db->Execute($sql);
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            $this->_db->FailTrans();
            return FALSE;
        }
        $this->_db->CompleteTrans();
        return TRUE;
    }

    /**
    * Returns all elements of the tree sortet by left.
    *
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @param array $fields needed fields (if is): array('filed1 name', 'filed2 name', etc)
    * @param integer $cache Recordset is cached for $cache microseconds
    * @return array needed fields
    */
    function full($fields, $condition = '', $cache = FALSE) {
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition, TRUE);
        }
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        } else {
            $fields = '*';
        }
        $sql = 'SELECT ' . $fields . ' FROM ' . $this->_tableName;
        $sql .= $condition;
        $sql .= ' ORDER BY ' . $this->_colnLeftNumber;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute((int)$cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        $this->res = $res;
        return TRUE;
    }

    /**
    * Returns all elements of a branch starting from an element with number $ID.
    *
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @param array $fields needed fields (if is): array('filed1 name', 'filed2 name', etc)
    * @param integer $cache Recordset is cached for $cache microseconds
    * @param integer $ID Node unique id
    * @return array - [0] => array(id, left, right, level, additional fields), [1] => array(...), etc.
    */
    function branch($ID, $fields, $condition = '', $cache = FALSE) {
        if (is_array($fields)) {
            $fields = 'A.' . implode(', A.', $fields);
        } else {
            $fields = 'A.*';
        }
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition, FALSE, 'A.');
        }
        $sql = 'SELECT ' . $fields . ', CASE WHEN A.' . $this->_colnLeftNumber . ' + 1 < A.' . $this->_colnRightNumber . ' THEN 1 ELSE 0 END AS nflag FROM ' . $this->_tableName . ' A, ' . $this->_tableName . ' B WHERE B.' . $this->_colnId . ' = ' . (int)$ID . ' AND A.' . $this->_colnLeftNumber . ' >= B.' . $this->_colnLeftNumber . ' AND A.' . $this->_colnRightNumber . ' <= B.' . $this->_colnRightNumber;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->_colnLeftNumber;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute((int)$cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        $this->res = $res;
        return TRUE;
    }

    /**
    * Returns all parents of element with number $ID.
    *
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @param array $fields needed fields (if is): array('filed1 name', 'filed2 name', etc)
    * @param integer $cache Recordset is cached for $cache microseconds
    * @param integer $ID Node unique id
    * @return array - [0] => array(id, left, right, level, additional fields), [1] => array(...), etc.
    */
    function parents($ID, $fields, $condition = '', $cache = FALSE) {
        if (is_array($fields)) {
            $fields = 'A.' . implode(', A.', $fields);
        } else {
            $fields = 'A.*';
        }
        if (!empty($condition)) {
            $condition = $this->_prepareCondition($condition, FALSE, 'A.');
        }
        $sql = 'SELECT ' . $fields . ', CASE WHEN A.' . $this->_colnLeftNumber . ' + 1 < A.' . $this->_colnRightNumber . ' THEN 1 ELSE 0 END AS nflag FROM ' . $this->_tableName . ' A, ' . $this->_tableName . ' B WHERE B.' . $this->_colnId . ' = ' . (int)$ID . ' AND B.' . $this->_colnLeftNumber . ' BETWEEN A.' . $this->_colnLeftNumber . ' AND A.' . $this->_colnRightNumber;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->_colnLeftNumber;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute((int)$cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        $this->res = $res;
        return TRUE;
    }

    /**
    * Returns a slightly opened tree from an element with number $ID.
    *
    * @param array $condition Array structure: array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc where array key - condition (AND, OR, etc), value - condition string
    * @param array $fields needed fields (if is): array('filed1 name', 'filed2 name', etc)
    * @param integer $cache Recordset is cached for $cache microseconds
    * @param integer $ID Node unique id
    * @return array - [0] => array(id, left, right, level, additional fields), [1] => array(...), etc.
    */
    function ajar($ID, $fields, $condition = '', $cache = FALSE) {
        if (is_array($fields)) {
            $fields = 'A.' . implode(', A.', $fields);
        } else {
            $fields = 'A.*';
        }
        $condition1 = '';
        if (!empty($condition)) {
            $condition1 = $this->_prepareCondition($condition, FALSE, 'B.');
        }
        $sql = 'SELECT A.' . $this->_colnLeftNumber . ', A.' . $this->_colnRightNumber . ', A.' . $this->_colnLevelNumber . ' FROM ' . $this->_tableName . ' A, ' . $this->_tableName . ' B '
        . 'WHERE B.' . $this->_colnId . ' = ' . (int)$ID . ' AND B.' . $this->_colnLeftNumber . ' BETWEEN A.' . $this->_colnLeftNumber . ' AND A.' . $this->_colnRightNumber;
        $sql .= $condition1;
        $sql .= ' ORDER BY A.' . $this->_colnLeftNumber;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute((int)$cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        if (0 == $res->recordCount()) {
            $this->ERRORS_MES[] = _('no_element_in_tree');
            return FALSE;
        }
        $alen = $res->recordCount();
        $i = 0;
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        } else {
            $fields = '*';
        }
        if (!empty($condition)) {
            $condition1 = $this->_prepareCondition($condition, FALSE);
        }
        $sql = 'SELECT ' . $fields . ' FROM ' . $this->_tableName . ' WHERE (' . $this->_colnLevelNumber . ' = 1';
        while ($row = $res->FetchRow()) {
            if ((++$i == $alen) && ($row[$this->_colnLeftNumber] + 1) == $row[$this->_colnRightNumber]) {
                break;
            }
            $sql .= ' OR (' . $this->_colnLevelNumber . ' = ' . ($row[$this->_colnLevelNumber] + 1)
            . ' AND ' . $this->_colnLeftNumber . ' > ' . $row[$this->_colnLeftNumber]
            . ' AND ' . $this->_colnRightNumber . ' < ' . $row[$this->_colnRightNumber] . ')';
        }
        $sql .= ') ' . $condition1;
        $sql .= ' ORDER BY ' . $this->_colnLeftNumber;
        if (FALSE === DB_CACHE || FALSE === $cache || 0 == (int)$cache) {
            $res = $this->_db->Execute($sql);
        } else {
            $res = $this->_db->CacheExecute($cache, $sql);
        }
        if (FALSE === $res) {
            $this->ERRORS[] = array(2, 'SQL query error.', __FILE__ . '::' . __CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__, 'SQL QUERY: ' . $sql, 'SQL ERROR: ' . $this->_db->ErrorMsg());
            $this->ERRORS_MES[] = extension_loaded('gettext') ? _('internal_error') : 'internal_error';
            return FALSE;
        }
        $this->res = $res;
        return TRUE;
    }

    /**
    * Returns amount of lines in result.
    *
    * @return integer
    */
    function recordCount() {
        return $this->res->recordCount();
    }

    /**
    * Returns the current row.
    *
    * @return array
    */
    function nextRow() {
        return $this->res->FetchRow();
    }

    /**
    * Transform array with conditions to SQL query
    * Array structure:
    * array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc
    * where array key - condition (AND, OR, etc), value - condition string.
    *
    * @param array $condition
    * @param string $prefix
    * @param bool $where - True - yes, flase - not
    * @return string
    */
    function _prepareCondition($condition, $where = FALSE, $prefix = '') {
        if (!is_array($condition)) {
            return $condition;
        }
        $sql = ' ';
        if (TRUE === $where) {
            $sql .= 'WHERE ' . $prefix;
        }
        $keys = array_keys($condition);
        for ($i = 0;$i < count($keys);$i++) {
            if (FALSE === $where || (TRUE === $where && $i > 0)) {
                $sql .= ' ' . strtoupper($keys[$i]) . ' ' . $prefix;
            }
            $sql .= implode(' ' . strtoupper($keys[$i]) . ' ' . $prefix, $condition[$keys[$i]]);
        }
        return $sql;
    }
}
?>