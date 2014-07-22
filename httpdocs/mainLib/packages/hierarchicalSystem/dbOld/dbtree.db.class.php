<?php
require_once(dirname(__FILE__).'/requirements/dbtree/dbtree.class.php');

define(Tree_System_Err_Empty_Parent_Id_Only_One_Root_Allowed,1);
define(Tree_System_Err_Deleteing_A_Node_With_Childs_Does_Not_Allowed_In_This_Mode,2);
define(Tree_System_Err_Unable_To_Delete,3);
define(Tree_System_Err_Moving_To_Child_Of_Current_Node_Does_No_Allowed,4);
define(Tree_System_Err_Deleteing_Root_Node_Does_Not_Allowed,5);
define(Tree_System_Err_Editing_Root_Note_Does_Not_Allowed,6);
define(Tree_System_Err_Attempting_To_Delete_Protected_Node,7);
define(Tree_System_Err_Attempting_To_Move_Protected_Node,8);


class cmfcDbTree extends cmfcHierarchicalSystemDbTree
{
	var $tableName='sample_tree';

	var $colnId='id';
	var $colnName='name';
	var $colnLink='link';
	var $colnVisible='show';
	var $colnParentId='parent_id';
	var $colnLeftNumber='lft';
	var $colnRightNumber='rgt';
	var $colnLevelNumber='level';
	var $colnInternalName='internal_name';

	var $cvId;
	var $cvVisible;
	var $cvName;
	var $cvLink;
	var $cvParentId;
	var $cvLeftNumber;
	var $cvRightNumber;
	var $cvInternalName;

	var $sortByColn;

	/*
		traversal : preorder tree traversal algorithm
		recursive : recursive
	*/
	var $mode='traversal';

	var $autoRebuild=true;
	var $_protectedNodes=array();
	
	var $errorNo;
	var $errorMsg;
	
	
	function setTableName($name) {
		$this->tableName=$name;
		$this->table=$this->tableName;
	}
	
	function setProtectedNodes($value) {
		$this->_protectedNodes=$value;
	}

	function setColnId($name) {
		$this->colnId=$name;
		$this->table_id=$this->colnId;
	}	
	
	function setColnName($name) {
		$this->colnName=$name;
	}
	
	function setColnLink($name) {
		$this->colnLink=$name;
	}
	
	function setColnVisible($name) {
		$this->colnVisible=$name;
	}
	
	function setColnParentId($name) {
		$this->colnParentId=$name;
	}
	
	function setColnInternalName($name) {
		$this->colnInternalName=$name;
	}
	
	function setColnLeftNumber($name) {
		$this->colnLeftNumber=$name;
		$this->table_left=$this->colnLeftNumber;
	}
	
	function setColnRightNumber($name) {
		$this->colnRightNumber=$name;
		$this->table_right=$this->colnRightNumber;
	}
	
	function setColnLevelNumber($name) {
		$this->colnLevelNumber=$name;
		$this->table_level=$this->colnLevelNumber;
	}
	
	
	function errorMessages($errorNo) {
		$messages=array(
			Tree_System_Err_Empty_Parent_Id_Only_One_Root_Allowed => "The parent_id field is empty, you cannot have more than one root!",
			Tree_System_Err_Deleteing_A_Node_With_Childs_Does_Not_Allowed_In_This_Mode => "this node has childs, you cannot delete it unless you set $mode parameter to includeChilds or moveChildsToParent. you do not want orphan nodes , do you?",
			Tree_System_Err_Unable_To_Delete => "unable to delete and move",
			Tree_System_Err_Moving_To_Child_Of_Current_Node_Does_No_Allowed => "You cannot use child of current node as new parent!",
			Tree_System_Err_Deleteing_Root_Node_Does_Not_Allowed => "Deleting root node does not allowed",
			Tree_System_Err_Editing_Root_Note_Does_Not_Allowed => "Editing root note does not allowed",
			Tree_System_Err_Attempting_To_Delete_Protected_Node => "Deleting protected node does not allowed",
			Tree_System_Err_Attempting_To_Move_Protected_Node => "Moving protected node does not allowed"
		);
		
		return $messages[$errorNo];
	}
	
	function raiseError($errorNo) {
		$this->errorNo=$errorNo;
		$this->errorMsg=$this->errorMessages($errorNo);
	}

	function createTable()
	{
		$sql_query="
			CREATE TABLE `$this->tableName` (
				`$this->colnId` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`$this->colnName` TEXT NOT NULL ,
				`$this->colnParentId` INT( 11 ) NOT NULL ,
				`$this->colnLeftNumber` INT( 11 ) NOT NULL ,
				`$this->colnRightNumber` INT( 11 ) NOT NULL ,
				`$this->colnLevelNumber` INT( 11 ) NOT NULL
			) TYPE = MYISAM;";
		$sql_result=mysql_query($sql_query);
	}
	
    function cmfcDbTree($table, $prefix, &$db) {
        $this->db = &$db;
        $this->table = $this->tableName=$table;
        $this->table_id = $this->colnId;
        $this->table_left = $this->colnLeftNumber;
        $this->table_right = $this->colnRightNumber;
        $this->table_level = $this->colnLevelNumber;
        unset($prefix, $table);
    }
	
	function clear($data = array()) {
		return parent::Clear($data);
	}
	
	function getNodeInfo($section_id, $cache = FALSE) {
		return parent::GetNodeInfo($section_id, $cache);
	}
	
	function getParentInfo($section_id, $condition = '', $cache = FALSE) {
		return parent::GetParentInfo($section_id, $condition , $cache);
	}
	
	function insert($section_id, $condition = '', $data = array()) {
		return parent::Insert($section_id, $condition , $data);
	}
	
	function insertNear($ID, $condition = '', $data = array()) {
		return parent::InsertNear($ID, $condition , $data);
	}
	
    function moveAll($ID, $newParentId, $condition = '') {
		if (in_array($ID,$this->_protectedNodes))  {
			$this->raiseError(Tree_System_Err_Attempting_To_Move_Protected_Node);
			return false;
		}
		return parent::MoveAll($ID, $newParentId, $condition );
	}
	
	function changePosition($id1, $id2) {
		return parent::ChangePosition($id1, $id2);
	}

	function changePositionAll($id1, $id2, $position = 'after', $condition = '') {
		return parent::ChangePositionAll($id1, $id2, $position , $condition );
	}
	
	function changePositionTop($id1, $condition = '') {
		$parentInfo=$this->getParentInfo($id1);
		$sqlQuery='SELECT * FROM '.$this->tableName.' WHERE '.
			$this->colnLeftNumber.'>'.$parentInfo[$this->colnLeftNumber].' AND '.
			$this->colnRightNumber.'<'.$parentInfo[$this->colnRightNumber].' AND '.
			$this->colnId.'<>'.$id1.
		' ORDER BY '.$this->colnLeftNumber.' LIMIT 1';
		
		$id2=cmfcMySql::query($sqlQuery,$this->db->conn);

		if ($id2) {
			$id2=cmfcMySql::fetchAssoc($id2);
			$id2=$id2[$this->colnId];
		}
		if (!empty($id2))
			return parent::ChangePositionAll($id1, $id2, 'before' , $condition );
	}
	
	function delete($ID, $condition = '') {
		if (in_array($ID,$this->_protectedNodes))  {
			$this->raiseError(Tree_System_Err_Attempting_To_Delete_Protected_Node);
			return false;
		}
		return parent::Delete($ID, $condition );
	}
	
	function deleteAll($ID, $condition = '') {
		if (in_array($ID,$this->_protectedNodes))  {
			$this->raiseError(Tree_System_Err_Attempting_To_Delete_Protected_Node);
			return false;
		}
		return parent::DeleteAll($ID, $condition );
	}
	
	function full($fields, $condition = '', $cache = FALSE) {
		return parent::Full($fields, $condition , $cache);
	}
	
	function branch($ID, $fields, $condition = '', $cache = FALSE) {
		return parent::Branch($ID, $fields, $condition, $cache );
	}
	
	function branchAsArray($ID, $fields, $condition = '', $cache = FALSE) {
		if ($this->branch($ID, $fields, $condition, $cache )) {
			$rows=array();
			while ($row=$this->nextRow()) {
				$row['hasChild']=false;
				if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1) $row['hasChild']=true;
				$rows[]=$row;
			}
			return $rows;
		}
		return false;
	}
	
	function parents($ID, $fields, $condition = '', $cache = FALSE) {
		return parent::Parents($ID, $fields, $condition, $cache );
	}
	
	function ajar($ID, $fields, $condition = '', $cache = FALSE) {
		return parent::Ajar($ID, $fields, $condition, $cache );
	}
	
	function nextRow() {
		return parent::NextRow();
	}
	
	
	/**
	* @desc get all fetched rows as array
	*/
	function allRows() {
		$rows=array();
		while ($row=parent::NextRow()) {
			$rows[]=$row;
		}
		return $rows;
			
	}
	
	function recordCount() {
		return parent::RecordCount();
	}

	/* TESTED */
	function getRootNodeId() {
		$sqlQuery="SELECT * FROM `$this->tableName`
					WHERE
						`$this->colnParentId`='' or 
						`$this->colnParentId`=0 or 
						`$this->colnParentId` IS NULL 
					ORDER BY `$this->colnId` ASC";
		$rows=cmfcMySql::getRowsCustom($sqlQuery);
		if (!is_array($rows) and mysql_errno()!=0){
			//echo mysql_error();
			trigger_error('Unable to find root node becuase of query error',E_USER_ERROR);
		}
		elseif (count($rows)<1 or !is_array($rows)) {
			trigger_error('There is no root node! parent_id field of root node should be 0 or NULL',E_USER_ERROR);
		} elseif(count($rows)>1) {
			trigger_error('There is more than one root node! please delete one of them',E_USER_ERROR);
		} else {
			reset($rows);
			$row=current($rows);
			//var_dump($row);
			return intval($row[$this->colnId]);
		}

		return false;
	}


	/*
	Finding all the Leaf Nodes
	
	Finding all leaf nodes in the nested set model even simpler than the 
	LEFT JOIN method used in the adjacency list model. If you look at the nested_category 
	table, you may notice that the lft and rgt values for leaf nodes are consecutive numbers. 

	To find the leaf nodes, we look for nodes where rgt = lft + 1:
	
	SELECT name
	FROM nested_category
	WHERE rgt = lft + 1;
	
	
	+--------------+
	| name         |
	+--------------+
	| TUBE         |
	| LCD          |
	| PLASMA       |
	| FLASH        |
	| CD PLAYERS   |
	| 2 WAY RADIOS |
	+--------------+
	*/
	function getLeafNodes($rootId=null) {
		$sqlQuery="SELECT * 
					FROM `$this->tableName`
					WHERE `$this->colnRightNumber` = `$this->colnLeftNumber` + 1;";
		return cmfcMySql::getRowsCustom($sqlQuery);
	}
	
	/*
	Find the Immediate Subordinates of a Node
	
	Imagine you are showing a category of electronics products on a retailer web site. When a user clicks on a category, you would want to show the products of that category, as well as list its immediate sub-categories, but not the entire tree of categories beneath it. For this, we need to show the node and its immediate sub-nodes, but no further down the tree. For example, when showing the PORTABLE ELECTRONICS category, we will want to show MP3 PLAYERS, CD PLAYERS, and 2 WAY RADIOS, but not FLASH.
	
	This can be easily accomplished by adding a HAVING clause to our previous query:
	
	SELECT node.name, (COUNT(parent.name) - (sub_tree.depth + 1)) AS depth
	FROM nested_category AS node,
		nested_category AS parent,
		nested_category AS sub_parent,
		(
			SELECT node.name, (COUNT(parent.name) - 1) AS depth
			FROM nested_category AS node,
			nested_category AS parent
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			AND node.name = 'PORTABLE ELECTRONICS'
			GROUP BY node.name
			ORDER BY node.lft
		)AS sub_tree
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
		AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
		AND sub_parent.name = sub_tree.name
	GROUP BY node.name
	HAVING depth <= 1
	ORDER BY node.lft;
	
	+----------------------+-------+
	| name                 | depth |
	+----------------------+-------+
	| PORTABLE ELECTRONICS |     0 |
	| MP3 PLAYERS          |     1 |
	| CD PLAYERS           |     1 |
	| 2 WAY RADIOS         |     1 |
	+----------------------+-------+
	
	If you do not wish to show the parent node, change the HAVING depth <= 1 line to HAVING depth = 1.
	*/
	
	function getNodeSubordinates($id,$includeParentNode=false,$nested=false) {
		$sqlQuery="
			SELECT node.*, (COUNT(parent.`$this->colnId`) - (sub_tree.depth + 1)) AS depth
			FROM `$this->tableName` AS node,
				`$this->tableName` AS parent,
				`$this->tableName` AS sub_parent,
				(
					SELECT node.`$this->colnId`, (COUNT(parent.`$this->colnId`) - 1) AS depth
					FROM `$this->tableName` AS node,
					`$this->tableName` AS parent
					WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
					AND node.`$this->colnId` = '$id'
					GROUP BY node.`$this->colnId`
					ORDER BY node.`$this->colnLeftNumber`
				)AS sub_tree
			WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
				AND node.`$this->colnLeftNumber` BETWEEN sub_parent.`$this->colnLeftNumber` AND sub_parent.`$this->colnRightNumber`
				AND sub_parent.`$this->colnId` = sub_tree.`$this->colnId`
			GROUP BY node.`$this->colnId` ";
		if ($nested==false) {
			if ($includeParentNode)
				$sqlQuery.=" HAVING depth <= 1 ";
			else
				$sqlQuery.=" HAVING depth = 1 ";
		} else {
			if (!$includeParentNode)
				$sqlQuery.=" HAVING depth > 1 ";
		}
		$sqlQuery.="
			ORDER BY node.`$this->colnLeftNumber`;
		";
		return cmfcMySql::getRowsCustom($sqlQuery);
	}
	
	/*
	Aggregate Functions in a Nested Set
	
	Let's add a table of products that we can use to demonstrate aggregate functions with:
	
	CREATE TABLE product(
	product_id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(40),
	category_id INT NOT NULL
	);
	
	
	INSERT INTO product(name, category_id) VALUES('20" TV',3),('36" TV',3),
	('Super-LCD 42"',4),('Ultra-Plasma 62"',5),('Value Plasma 38"',5),
	('Power-MP3 5gb',7),('Super-Player 1gb',8),('Porta CD',9),('CD To go!',9),
	('Family Talk 360',10);
	
	SELECT * FROM product;
	
	+------------+-------------------+-------------+
	| product_id | name              | category_id |
	+------------+-------------------+-------------+
	|          1 | 20" TV            |           3 |
	|          2 | 36" TV            |           3 |
	|          3 | Super-LCD 42"     |           4 |
	|          4 | Ultra-Plasma 62"  |           5 |
	|          5 | Value Plasma 38"  |           5 |
	|          6 | Power-MP3 128mb   |           7 |
	|          7 | Super-Shuffle 1gb |           8 |
	|          8 | Porta CD          |           9 |
	|          9 | CD To go!         |           9 |
	|         10 | Family Talk 360   |          10 |
	+------------+-------------------+-------------+
	
	Now let's produce a query that can retrieve our category tree, along with a product
	 count for each category:
	
	SELECT parent.name, COUNT(product.name)
	FROM nested_category AS node ,
	nested_category AS parent,
	product
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
	AND node.category_id = product.category_id
	GROUP BY parent.name
	ORDER BY node.lft;
	
	
	+----------------------+---------------------+
	| name                 | COUNT(product.name) |
	+----------------------+---------------------+
	| ELECTRONICS          |                  10 |
	| TELEVISIONS          |                   5 |
	| TUBE                 |                   2 |
	| LCD                  |                   1 |
	| PLASMA               |                   2 |
	| PORTABLE ELECTRONICS |                   5 |
	| MP3 PLAYERS          |                   2 |
	| FLASH                |                   1 |
	| CD PLAYERS           |                   2 |
	| 2 WAY RADIOS         |                   1 |
	+----------------------+---------------------+
	
	This is our typical whole tree query with a COUNT and GROUP BY added, along with a reference
	 to the product table and a join between the node and product table in the WHERE clause. 
	 As you can see, there is a count for each category and the count of subcategories is reflected
	  in the parent categories.
	*/
	
	function getNodesRelatedRowsCount($tableName,$colnCategoryId) {
		$sqlQuery="
			SELECT node.*, COUNT(product.`$colnCategoryId`)
			FROM `$this->tableName` AS node ,
				 `$this->tableName` AS parent,
				 `$tableName` as product
			WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
					AND node.`$this->colnId` = product.`$colnCategoryId`
			GROUP BY parent.`$this->colnId`
			ORDER BY node.`$this->colnLeftNumber`;
		";
		return cmfcMySql::getRowsCustom($sqlQuery);
	}
	


	
	/* Tested */
	function rebuild($parentId=0, $left=0,$level=0) {
		// the right value of this node is the left value + 1
		$right = $left+1;

		// get all children of this node
		$sqlQuery="SELECT `$this->colnId` FROM `$this->tableName` WHERE ";
		if (empty($parentId))
			$sqlQuery.= " `$this->colnParentId` IS NULL or `$this->colnParentId`='$parentId'";
		else 
			$sqlQuery.= " `$this->colnParentId`='$parentId' ";
		$result = mysql_query($sqlQuery);

		if (@mysql_num_rows($result)>0)
		while ($row = mysql_fetch_array($result)) {
			// recursive execution of this function for each
			// child of this node
			// $right is the current right value, which is
			// incremented by the rebuild_tree function
			$right = $this->rebuild($row[$this->colnId], $right,$level+1);
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber`='$left', `$this->colnRightNumber`='$right', `$this->colnLevelNumber`='$level' WHERE `$this->colnId`='$parentId'";
		mysql_query($sqlQuery);



		// return the right value of this node + 1
		return $right+1;
	}


	function getNumberOfChilds($id=null) {
		if (is_null($id)) {
			$row=$this->getRow($id);
			$left=$row[$this->colnLeft];
			$right=$row[$this->colnRight];
		} else {
			$left=$this->cvLeft;
			$right=$this->cvRight;
		}
		//$result=round(($right � $left - 1) / 2);
		$result=round(($right-$left-1)/2);
		return $result;
	}

	function isItChild($id=null) {
		if ($this->cvRightNumber-$this->cvLeftNumber<=1) {
			return true;
		}
		return false;
	}

	function isItParent($id=null) {
		if ($this->cvRightNumber-$this->cvLeftNumber>1) {
			return true;
		}
		return false;
	}
	
	function isChildOf($childId,$parentId) {
		$rchild=$this->GetNodeInfo($childId);
		$rparent=$this->GetNodeInfo($parentId);
		if ($rchild!==false and $rparent!==false) {
			list($childLeft,$childRight)=$rchild;
			list($parentLeft,$parentRight)=$rparent;
			if ($parentLeft<$childLeft and $childRight<$parentRight) {
				return true;
			}
		}
		return false;
	}
	
	function hasChild($id=null,$leftNumber=null,$rightNumber=null) {
		if (is_null($leftNumber)) {

			$this->cvRightNumber=$rightNumber;
			$this->cvLeftNumber=$leftNumber;
		}
		if ($this->cvRightNumber-$this->cvLeftNumber>1) {
			return true;
		}
		return false;
	}


	function isParentChanged($newParentId=null,$id=null) {
		if (is_null($id)) {$id=$this->cv_id;}
		if (is_null($newParentId)) {$newParentId=$this->cvParentId;}

		$row=$this->getRow($id);
		if (is_array($row)) {
			if ($newParentId!=$row[$this->colnParentId])
				return true;
		}
		return false;
	}


	// $parent is the parent of the children we want to see
	// $level is increased when we go deeper into the tree,
	//        used to display a nice indented tree
	function displayChildren($parent_id, $level) {
		// retrieve all children of $parent
		$result = mysql_query("SELECT `$this->coln_id` FROM `$this->table_name` ".
							  "WHERE `$this->coln_parent_id`='$parent_id';");

		// display each child
		while ($row = mysql_fetch_array($result)) {
			// indent and display the title of this child
			echo str_repeat('  ',$level).$row[$this->coln_id]."\n";

			// call this function again to display this
			// child's children
			display_children($row[$this->coln_id], $level+1);
		}
	}

	function arrayToProperties($columnsValues) {
		if (parent::arrayToProperties($columnsValues)) {
			if (!$this->dynamicSystem) {
				$this->cvName=$columnsValues[$this->colnName];
				$this->cvLeftNumber=$columnsValues[$this->colnLeftNumber];
				$this->cvRightNumber=$columnsValues[$this->colnRightNumber];
				$this->cvLevelNumber=$columnsValues[$this->colnLevelNumber];
				$this->cvParentId=$columnsValues[$this->colnParentId];
			}
			return true;
		}
		return false;
	}

	function propertiesToArray() {
		$columnsValues=parent::propertiesToArray();

		if (!$this->dynamicSystem) {
			$columnsValues[$this->colnName]=$this->cvName;
			$columnsValues[$this->colnLeftNumber]=$this->cvLeftNumber;
			$columnsValues[$this->colnRightNumber]=$this->cvRightNumber;
			$columnsValues[$this->colnLevelNumber]=$this->cvLevelNumber;
			$columnsValues[$this->colnParentId]=$this->cvParentId;

		}

		return $columnsValues;
	}

	function clearProperties() {
		parent::clearProperties();
		if (!$this->dynamicSystem) {
			$this->cvName=null;
			$this->cvNameFarsi=null;
			$this->cvLeftNumber=null;
			$this->cvRightNumber=null;
			$this->cvLevelNumber=null;
			$this->cvParentId=null;
		}
	}
    

	/*
	$status=> 'begin_parent' or 'end_parent' or 'begin_child_parent' or 'end_child_parent'
	$data=array('id'=>'1','name'=>'first',...)
	$function_name="item_found($data,$status)"
	*/
	function treeview($id,$function_name='',$data=null)
	{
		$tree='';
//		$tree=$this->generated_html_tree;
		$sql_query = "SELECT * FROM `$this->table_name` WHERE `$this->coln_parent_id` = '$id' ORDER BY `$this->sort_by_coln` ";
		$sql_query_result=mysql_query($sql_query);
		if (mysql_num_rows($sql_query_result)>0)
		{
			if (!function_exists($function_name))
				$tree .= "<ul>"; else $tree .=$function_name($data,'begin_parent');

			while($row=mysql_fetch_array($sql_query_result))
			{
				if (!function_exists($function_name))
					$tree .= '<li><a href="">'.$row['name']."</a>"; else
						$tree .=$function_name($row,'begin_child_parent');
				$tree .= $this->treeview($row['id'],$function_name,$row);
				if (!function_exists($function_name))
					$tree .= "</li>"; else $tree .=$function_name($row,'end_child_parent');
			}

			if (!function_exists($function_name))
				$tree .= "</ul>"; else $tree .=$function_name($data,'end_parent');
		}

		//$this->generated_html_tree=$tree;
		return $tree;
	}
}