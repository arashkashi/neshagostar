<?php



class CTreeSystemBase {
	var $dynamicSystem=false;

	function load($id) {
		$this->clear();
		$sql_query="SELECT * FROM `$this->tableName` WHERE ";
		$sql_query.="`$this->colnId`='$id' ";

		$sql_query.="LIMIT 1";
		$sql_result=mysql_query($sql_query);

		if ($sql_result)
		if (mysql_num_rows($sql_result)>0)
		{
			$row=mysql_fetch_array($sql_result);
			$this->arrayToProperties($row);
			return true;
		}
		return false;
	}
	
	function clear()
	{
		if ($this->dynamicSystem) {
			$vars=get_object_vars($this);
			$code='';
			foreach ($vars as $var_name=>$var_value) {
				if (preg_match('/^'.$this->column_value_propery_name_prefix.'[a-zA-Z][a-zA-Z0-9_]*+$/',$var_name)>0)
					$code.='@$this->'.$this->column_value_propery_name_prefix.$var_name.'=null;';
			}
			eval($code);
		} else {
			$this->cvId=null;
		}
	}
	
	function arrayToProperties($columns_values,$except_nulls=false) {
		if (is_array($columns_values)) {
			if ($this->dynamicSystem) {
				$code='';
				foreach($columns_values as $column_name=>$column_value) {
					if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*+$/',$column_name)>0)
						$code.='@$this->'.$this->column_value_propery_name_prefix.$column_name.'=$column_value';
				}
				eval($code);
			} else {
				if ($except_nulls==false or ($except_nulls and !is_null($columns_values[$this->colnId])))
					@$this->cvId=$columns_values[$this->colnId];
			}
			return true;
		}
		return false;
	}

	function propertiesToArray($except_nulls=false) {
		$columns_values=array();

		if ($this->dynamicSystem) {
			$vars=get_object_vars($this);
			$code='';
			foreach ($vars as $var_name=>$var_value) {
				if (preg_match('/^'.$this->column_value_propery_name_prefix.'[a-zA-Z][a-zA-Z0-9_]*+$/',$var_name)>0)
					$code.='$columns_values[$this->'.$this->column_name_propery_name_prefix.colnId.']=$var_value;';
			}
			eval($code);
		} else {
			if ($except_nulls==false or ($except_nulls and !is_null($this->cvId)))
				$columns_values[$this->colnId]=$this->cvId;
		}

		return $columns_values;
	}

	function getRows($filter_column=null,$filter_value=null,$limit=null,$sort_column_name=null,$sort_type='DESC') {
		$sql_query="SELECT * FROM `$this->tableName` ";
		if (!is_null($filter_column)) {$sql_query.="WHERE `$filter_column`='$filter_value' ";}
		if (!is_null($sort_column_name)) {$sql_query.="ORDER BY `$sort_column_name` $sort_type ";}
		if (!is_null($limit)) {$sql_query.="LIMIT $limit ";}

		return exec_query_as_array($sql_query,null,false);
	}

	function getRow($id) {
		$sql_query="SELECT * FROM `$this->tableName` WHERE `$this->colnId`='$id'";
		return exec_query_as_array($sql_query);
	}
	
	
	function add($columnsValues=null) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToArray();}

		$sqlQuery="INSERT INTO `$this->tableName` SET ";

		foreach ($columnsValues as $columnName=>$columnValue) {
			if (preg_match('/[^\\\](\'|")/', $columnValue)) {
				$columnValue=mysql_real_escape_string($columnValue);
			}
			$sqlQuery.="$comma `$columnName`='$columnValue'";
			if (!isset($comma)) {$comma=',';}
		}
/*
		echo '<pre>';
		echo $sql_query;
		echo '</pre>';
	*/	
		$sqlQueryResult=mysql_query($sqlQuery);
		echo mysql_error();
		if ($sqlQueryResult!==false) {
			$this->cvId=mysql_insert_id();
			return true;
		} else {
			return false;
		}
	}


	function delete($id=null) {
		if (is_null($id)) {$id=$this->cvId;}
		$sqlQuery="DELETE FROM `$this->tableName` WHERE `$this->colnId`='$id' ";
		$sqlQueryResult=mysql_query($sqlQuery);
		echo mysql_error();
		if ($sqlQueryResult!==false)
			return true;
		else
			return false;
	}
	
	
	function update($columnsValues=null,$id=null) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToArray();}
		if (is_null($id)) {$id=$this->cvId;}
		if (is_null($id)) {$id=$columnsValues[$this->colnId];unset($columnsValues[$this->colnId]);}
		$sqlQuery="UPDATE `$this->tableName` SET ";

		foreach ($columnsValues  as $columnName=>$columnValue) {
			if (preg_match('/[^\\\](\'|")/', $columnValue)) {
				$columnValue=mysql_real_escape_string($columnValue);
			}

			$sqlQuery.="$comma `$columnName`='$columnValue'";
			if (!isset($comma)) {$comma=',';}
		}

		$sqlQuery.=" WHERE `$this->colnId`='$id'";

		$sqlQueryResult=mysql_query($sqlQuery);
		//$this->debugMessage(mysql_error().'|'.$sqlQuery);
		if ($sqlQueryResult!==false) {
			if (mysql_affected_rows()>0) return true; else return false;
		} else {
			return false;
		}
	}
	
}
/*
based on this article :
http://dev.mysql.com/tech-resources/articles/hierarchical-data.html


INSERT INTO nested_category
VALUES(1,'ELECTRONICS',1,20),(2,'TELEVISIONS',2,9),(3,'TUBE',3,4),
(4,'LCD',5,6),(5,'PLASMA',7,8),(6,'PORTABLE ELECTRONICS',10,19),
(7,'MP3 PLAYERS',11,14),(8,'FLASH',12,13),
(9,'CD PLAYERS',15,16),(10,'2 WAY RADIOS',17,18);


SELECT * FROM nested_category ORDER BY category_id;


+-------------+----------------------+-----+-----+
| category_id | name                 | lft | rgt |
+-------------+----------------------+-----+-----+
|           1 | ELECTRONICS          |   1 |  20 |
|           2 | TELEVISIONS          |   2 |   9 |
|           3 | TUBE                 |   3 |   4 |
|           4 | LCD                  |   5 |   6 |
|           5 | PLASMA               |   7 |   8 |
|           6 | PORTABLE ELECTRONICS |  10 |  19 |
|           7 | MP3 PLAYERS          |  11 |  14 |
|           8 | FLASH                |  12 |  13 |
|           9 | CD PLAYERS           |  15 |  16 |
|          10 | 2 WAY RADIOS         |  17 |  18 |
+-------------+----------------------+-----+-----+

We use lft and rgt because left and right are reserved words in MySQL, 
see http://dev.mysql.com/doc/mysql/en/reserved-words.html for the full
 list of reserved words.

So how do we determine left and right values? We start numbering at the 
leftmost side of the outer node and continue to the right:

When working with a tree, we work from left to right, one layer at a time, 
descending to each node's children before assigning a right-hand number and 
moving on to the right. This approach is called the modified preorder tree traversal algorithm.
*/

define('Tree_System_Err_Empty_Parent_Id_Only_One_Root_Allowed',1);
define('Tree_System_Err_Deleteing_A_Node_With_Childs_Does_Not_Allowed_In_This_Mode',2);
define('Tree_System_Err_Unable_To_Delete',3);
define('Tree_System_Err_Moving_To_Child_Of_Current_Node_Does_No_Allowed',4);
define('Tree_System_Err_Deleteing_Root_Node_Does_Not_Allowed',5);
define('Tree_System_Err_Editing_Root_Note_Does_Not_Allowed',6);

			
class CDatabaseTreeSystemV2 extends CTreeSystemBase
{
	var $tableName='sample_tree';

	var $colnId='id';
	var $colnName='name';
	var $colnParentId='parent_id';
	var $colnLeftNumber='left_number';
	var $colnRightNumber='right_number';

	var $cvId;
	var $cvName;
	var $cvParentId;
	var $cvLeftNumber;
	var $cvRightNumber;

	var $sortByColn;

	/*
		traversal : preorder tree traversal algorithm
		recursive : recursive
	*/
	var $mode='traversal';

	var $autoRebuild=true;
	
	var $errorNo;
	var $errorMsg;
	
	function errorMessages($errorNo) {
		$messages=array(
			Tree_System_Err_Empty_Parent_Id_Only_One_Root_Allowed => "The parent_id field is empty, you cannot have more than one root!",
			Tree_System_Err_Deleteing_A_Node_With_Childs_Does_Not_Allowed_In_This_Mode => "this node has childs, you cannot delete it unless you set $mode parameter to includeChilds or moveChildsToParent. you do not want orphan nodes , do you?",
			Tree_System_Err_Unable_To_Delete => "unable to delete and move",
			Tree_System_Err_Moving_To_Child_Of_Current_Node_Does_No_Allowed => "You cannot use child of current node as new parent!",
			Tree_System_Err_Deleteing_Root_Node_Does_Not_Allowed => "Deleting root node does not allowed",
			Tree_System_Err_Editing_Root_Note_Does_Not_Allowed => "Editing root note does not allowed"
			
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
				`$this->colnRightNumber` INT( 11 ) NOT NULL
			) TYPE = MYISAM;";
		$sql_result=mysql_query($sql_query);
	}
	
	/* TESTED */
	function getRootNodeId() {
		$sqlQuery="SELECT * FROM `$this->tableName`
					WHERE
						`$this->colnParentId`='' or 
						`$this->colnParentId`=0 or 
						`$this->colnParentId` IS NULL 
					ORDER BY `$this->colnId` ASC";
		$rows=db_get_rows_custom($sqlQuery);
		if (!is_array($rows) and mysql_errno()!=0)
			trigger_error('Unable to find root node becuase of query error',E_USER_ERROR);
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
	We can retrieve the full tree through the use of a self-join that links 
	parents with nodes on the basis that a node's lft value will always appear 
	between its parent's lft and rgt values:
	+----------------------+
	| name                 |
	+----------------------+
	| ELECTRONICS          |
	| TELEVISIONS          |
	| TUBE                 |
	| LCD                  |
	| PLASMA               |
	| PORTABLE ELECTRONICS |
	| MP3 PLAYERS          |
	| FLASH                |
	| CD PLAYERS           |
	| 2 WAY RADIOS         |
	+----------------------+
	
	Unlike our previous examples with the adjacency list model, this query will 
	work regardless of the depth of the tree. We do not concern ourselves with the 
	rgt value of the node in our BETWEEN clause because the rgt value will always fall 
	within the same parent as the lft values.
	*/

	function getAll($rootId=null) {
		$sqlQuery="SELECT * 
					FROM `$this->tableName` AS node,`$this->tableName` AS parent
					WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
						AND parent.`$this->colnId` = '$rootId'
						ORDER BY node.`$this->colnLeftNumber`;";
		return db_get_rows_custom($sqlQuery);
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
		return db_get_rows_custom($sqlQuery);
	}
	
	/*
	Retrieving a Single Path
	
	With the nested set model, we can retrieve a single path without having multiple self-joins:
	
	SELECT parent.name
	FROM nested_category AS node,
	nested_category AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
	AND node.name = 'FLASH'
	ORDER BY node.lft;
	
	+----------------------+
	| name                 |
	+----------------------+
	| ELECTRONICS          |
	| PORTABLE ELECTRONICS |
	| MP3 PLAYERS          |
	| FLASH                |
	+----------------------+
	*/
	/* Tested */
	function getPath($nodeId) {
		$sqlQuery="SELECT *
				FROM `$this->tableName` AS node,`$this->tableName` AS parent
				WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
				AND node.`$this->colnId` = '$nodeId'
				ORDER BY node.`$this->colnLeftNumber`;";
						
		return db_get_rows_custom($sqlQuery);
	}
	
	
	/*
	Finding the Depth of the Nodes

	We have already looked at how to show the entire tree, but what if we want to also show 
	the depth of each node in the tree, to better identify how each node fits in the hierarchy? 
	This can be done by adding a COUNT function and a GROUP BY clause to our existing query for 
	showing the entire tree:
	+----------------------+-------+
	| name                 | depth |
	+----------------------+-------+
	| ELECTRONICS          |     0 |
	| TELEVISIONS          |     1 |
	| TUBE                 |     2 |
	| LCD                  |     2 |
	| PLASMA               |     2 |
	| PORTABLE ELECTRONICS |     1 |
	| MP3 PLAYERS          |     2 |
	| FLASH                |     3 |
	| CD PLAYERS           |     2 |
	| 2 WAY RADIOS         |     2 |
	+----------------------+-------+
	
	We can use the depth value to indent our category names with the CONCAT and REPEAT string functions:
	
	SELECT CONCAT( REPEAT(' ', COUNT(parent.name) - 1), node.name) AS name
	FROM nested_category AS node,
	nested_category AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
	GROUP BY node.name
	ORDER BY node.lft;
	
	+-----------------------+
	| name                  |
	+-----------------------+
	| ELECTRONICS           |
	|  TELEVISIONS          |
	|   TUBE                |
	|   LCD                 |
	|   PLASMA              |
	|  PORTABLE ELECTRONICS |
	|   MP3 PLAYERS         |
	|    FLASH              |
	|   CD PLAYERS          |
	|   2 WAY RADIOS        |
	+-----------------------+
	
	Of course, in a client-side application you will be more likely to use the depth value 
	directly to display your hierarchy. Web developers could loop through the tree, 
	adding <li></li> and <ul></ul> tags as the depth number increases and decreases.
	*/
	/* Tested */
	function getAllWithDepth($nodeId=null) {
		$sqlQuery="
			SELECT node.*, (COUNT(parent.`$this->colnId`) - 1) AS depth
			FROM `$this->tableName` AS node,`$this->tableName` AS parent
			WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber` ";
		if (!is_null($nodeId)) {
			$sqlQuery.=" AND parent.`$this->colnId`='$nodeId' ";
		}
			
		$sqlQuery.="
			GROUP BY node.`$this->colnId`
			ORDER BY node.`$this->colnLeftNumber`;
		";
		$rows=db_get_rows_custom($sqlQuery);
		if (!is_array($rows)) {
			$this->raiseError(Tree_View_Structure_Is_Corrupted);
			return false;
		}
		return $rows;
	}
	
	/*
	Depth of a Sub-Tree
	
	When we need depth information for a sub-tree, we cannot limit either the node or 
	parent tables in our self-join because it will corrupt our results. Instead, we add 
	a third self-join, along with a sub-query to determine the depth that will be the new
	 starting point for our sub-tree:
	
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
	ORDER BY node.lft;
	
	
	+----------------------+-------+
	| name                 | depth |
	+----------------------+-------+
	| PORTABLE ELECTRONICS |     0 |
	| MP3 PLAYERS          |     1 |
	| FLASH                |     2 |
	| CD PLAYERS           |     1 |
	| 2 WAY RADIOS         |     1 |
	+----------------------+-------+
	
	This function can be used with any node name, including the root node. The depth 
	values are always relative to the named node.
	*/
	 /* needs sub query MySQL 1.4.17 or more*/
	function getSubTreeDepth($id) {
		$sqlQuery="
			SELECT node.*, (COUNT(parent.`$this->colnId`) - (sub_tree.depth + 1)) AS depth
			FROM `$this->tableName` AS node,
				`$this->tableName` AS parent,
				`$this->tableName` AS sub_parent,
				(
					SELECT node.`$this->colnId`, (COUNT(parent.`$this->colnId`) - 1) AS depth
					FROM nested_category AS node,
					nested_category AS parent
					WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
					AND node.`$this->colnId` = '$id'
					GROUP BY node.`$this->colnId`
					ORDER BY node.`$this->colnLeftNumber`
				)AS sub_tree
			WHERE node.`$this->colnLeftNumber` BETWEEN parent.`$this->colnLeftNumber` AND parent.`$this->colnRightNumber`
				AND node.`$this->colnLeftNumber` BETWEEN sub_parent.`$this->colnLeftNumber` AND sub_parent.`$this->colnRightNumber`
				AND sub_parent.`$this->colnId` = sub_tree.`$this->colnId`
			GROUP BY node.`$this->colnId`
			ORDER BY node.`$this->colnLeftNumber`;
		";
		return db_get_rows_custom($sqlQuery);
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
	
	function getNodeSubordinates($id) {
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
		if ($includeParentNode)
			$sqlQuery.=" HAVING depth <= 1 ";
		else
			$sqlQuery.=" HAVING depth = 1 ";
		$sqlQuery.="
			ORDER BY node.`$this->colnLeftNumber`;
		";
		return db_get_rows_custom($sqlQuery);
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
		return db_get_rows_custom($sqlQuery);
	}
	
	/* Tested */
	function insert($columnsValues=null,$mode=null) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToArray();}
		
		if (!is_numeric($columnsValues[$this->colnParentId]) or empty($columnsValues[$this->colnParentId])) {
			$rootNodeId=$this->getRootNodeId();
			if (!empty($rootNodeId)) {
				//trigger_error('The parent_id field is empty, you cannot have more than one root!',E_USER_ERROR);
				$this->raiseError(Tree_System_Err_Empty_Parent_Id_Only_One_Root_Allowed);
				return false;
			}
		}

		if ($this->mode=='traversal') {
			/*
			$sqlQuery="
				LOCK TABLE nested_category WRITE;
				
				SELECT @myLeft := lft FROM nested_category
				
				WHERE name = '2 WAY RADIOS';
				
				UPDATE nested_category SET rgt = rgt + 2 WHERE rgt > @myLeft;
				UPDATE nested_category SET lft = lft + 2 WHERE lft > @myLeft;
				
				INSERT INTO nested_category(name, lft, rgt) VALUES('FRS', @myLeft + 1, @myLeft + 2);
				
				UNLOCK TABLES;
			";
			*/
			mysql_query("LOCK TABLE `$this->tableName` WRITE");
			//--(BEGIN)-->fetching parent left and right number
			$parentRow=$this->getRow($columnsValues[$this->colnParentId]);
			$parentRightNumber=$parentRow[$this->colnRightNumber];
			$parentLeftNumber=$parentRow[$this->colnLeftNumber];
			//--(END)-->fetching parent left and right number

			//--(BEGIN)-->making some space to add new node
			$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber`=`$this->colnRightNumber`+2
							WHERE `$this->colnRightNumber`>'$parentLeftNumber'";
			mysql_query($sqlQuery);
			$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber`=`$this->colnLeftNumber`+2
							WHERE `$this->colnLeftNumber`>'$parentLeftNumber'";
			mysql_query($sqlQuery);
			//--(END)-->making some space to add new node

			//--(BEGIN)-->calculating right and left value of new node
			$columnsValues[$this->colnLeftNumber]=$parentLeftNumber+1;
			$columnsValues[$this->colnRightNumber]=$parentLeftNumber+2;
			//--(END)-->calculating right and left value of new node
		}
		//var_dump($columnsValues);
		$result=parent::add($columnsValues);
		
		if ($this->mode=='recursive') {
			$this->rebuild($columnsValues[$this->colnParentId],1);
		}
		
		mysql_query("UNLOCK TABLES");

		return $result;
	}
	
	
	
	function update($columnsValues=null,$id=null) {
		if (is_null($columnsValues)) {$columnsValues=$this->propertiesToArray();}
		if (is_null($id)) {$id=$this->cvId;}

		$rootNodeId=$this->getRootNodeId();
		if ($id==$rootNodeId) {
			$this->raiseError(Tree_System_Err_Editing_Root_Note_Does_Not_Allowed);
			return false;
		}

		if ($this->mode=='traversal') {
			unset($columnsValues[$this->colnLeftNumber]);
			unset($columnsValues[$this->colnRightNumber]);
			
			mysql_query("LOCK TABLE `$this->tableName` WRITE");
			if (isset($columnsValues[$this->colnParentId]))
				$isParentChanged=$this->isParentChanged($columnsValues[$this->colnParentId],$id);
			//--(BEGIN)-->if parent changes it means user wants to move the node with its childs
			if ($isParentChanged) {
				$this->move($id,$columnsValues[$this->colnParentId]);
			}
			//--(END)-->if parent changes it means user wants to move the node with its childs
			
			$result=parent::update($columnsValues,$id);
			mysql_query("UNLOCK TABLES");
		}
		$isParentChanged=$this->isParentChanged($columnsValues[$this->colnParentId],$id);
		/*
		if ($isParentChanged) {
			$this->rebuild($columnsValues[$this->colnParentId],1);
		}
		*/
		return $result;
	}

	/*
	Deleting Nodes
	
	The last basic task involved in working with nested sets is the removal of nodes.
	The course of action you take when deleting a node depends on the node's position in
	the hierarchy; deleting leaf nodes is easier than deleting nodes with children because 
	we have to handle the orphaned nodes.
	
	When deleting a leaf node, the process if just the opposite of adding a new node, we delete
	the node and its width from every node to its right:
	
	LOCK TABLE nested_category WRITE;
	
	
	SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
	FROM nested_category
	WHERE name = 'GAME CONSOLES';
	
	
	DELETE FROM nested_category WHERE lft BETWEEN @myLeft AND @myRight;
	
	
	UPDATE nested_category SET rgt = rgt - @myWidth WHERE rgt > @myRight;
	UPDATE nested_category SET lft = lft - @myWidth WHERE lft > @myRight;
	
	UNLOCK TABLES;
	
	And once again, we execute our indented tree query to confirm that our node has been deleted
	without corrupting the hierarchy:
	
	SELECT CONCAT( REPEAT( ' ', (COUNT(parent.name) - 1) ), node.name) AS name
	FROM nested_category AS node,
	nested_category AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
	GROUP BY node.name
	ORDER BY node.lft;
	
	
	+-----------------------+
	| name                  |
	+-----------------------+
	| ELECTRONICS           |
	|  TELEVISIONS          |
	|   TUBE                |
	|   LCD                 |
	|   PLASMA              |
	|  PORTABLE ELECTRONICS |
	|   MP3 PLAYERS         |
	|    FLASH              |
	|   CD PLAYERS          |
	|   2 WAY RADIOS        |
	|    FRS                |
	+-----------------------+
	
	This approach works equally well to delete a node and all its children:
	
	LOCK TABLE nested_category WRITE;
	
	
	SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
	FROM nested_category
	WHERE name = 'MP3 PLAYERS';
	
	
	DELETE FROM nested_category WHERE lft BETWEEN @myLeft AND @myRight;
	
	
	UPDATE nested_category SET rgt = rgt - @myWidth WHERE rgt > @myRight;
	UPDATE nested_category SET lft = lft - @myWidth WHERE lft > @myRight;
	
	UNLOCK TABLES;
	
	And once again, we query to see that we have successfully deleted an entire sub-tree:
	
	SELECT CONCAT( REPEAT( ' ', (COUNT(parent.name) - 1) ), node.name) AS name
	FROM nested_category AS node,
	nested_category AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
	GROUP BY node.name
	ORDER BY node.lft;
	
	
	+-----------------------+
	| name                  |
	+-----------------------+
	| ELECTRONICS           |
	|  TELEVISIONS          |
	|   TUBE                |
	|   LCD                 |
	|   PLASMA              |
	|  PORTABLE ELECTRONICS |
	|   CD PLAYERS          |
	|   2 WAY RADIOS        |
	|    FRS                |
	+-----------------------+
	
	The other scenario we have to deal with is the deletion of a parent node but not the children.
	 In some cases you may wish to just change the name to a placeholder until a replacement is presented,
	 such as when a supervisor is fired. In other cases, the child nodes should all be moved up to the 
	 level of the deleted parent:
	
	LOCK TABLE nested_category WRITE;
	
	
	SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
	FROM nested_category
	WHERE name = 'PORTABLE ELECTRONICS';
	
	
	DELETE FROM nested_category WHERE lft = @myLeft;
	
	
	UPDATE nested_category SET rgt = rgt - 1, lft = lft - 1 WHERE lft BETWEEN @myLeft AND @myRight;
	UPDATE nested_category SET rgt = rgt - 2 WHERE rgt > @myRight;
	UPDATE nested_category SET lft = lft - 2 WHERE lft > @myRight;
	
	UNLOCK TABLES;
	
	In this case we subtract two from all elements to the right of the node 
	(since without children it would have a width of two), and one from the nodes that 
	are its children (to close the gap created by the loss of the parent's left value). 
	Once again, we can confirm our elements have been promoted:
	
	SELECT CONCAT( REPEAT( ' ', (COUNT(parent.name) - 1) ), node.name) AS name
	FROM nested_category AS node,
	nested_category AS parent
	WHERE node.lft BETWEEN parent.lft AND parent.rgt
	GROUP BY node.name
	ORDER BY node.lft;
	
	
	+---------------+
	| name          |
	+---------------+
	| ELECTRONICS   |
	|  TELEVISIONS  |
	|   TUBE        |
	|   LCD         |
	|   PLASMA      |
	|  CD PLAYERS   |
	|  2 WAY RADIOS |
	|   FRS         |
	+---------------+
	
	Other scenarios when deleting nodes would include promoting one of the 
	children to the parent position and moving the child nodes under a sibling of the 
	parent node, but for the sake of space these scenarios will not be covered in this article.
	*/

	/*
		$mode :
			alsoChilds : delete parent and its childs
			onlyParent : delete only seleted node (it's not possible if it has childs)
			onlyChilds : keep the parent and delete all it's childs
			keepChilds : move childs to parent of their parent then delete ths current parent
	*/
	
	/* Tested */
	function delete($id=null,$mode='onlyParent') {
		$result=false;
		if ($this->mode=='traversal') {
			//--(BEGIN)-->fetching parent left and right number
			$row=$this->getRow($id);
			$rightNumber=$row[$this->colnRightNumber];
			$leftNumber=$row[$this->colnLeftNumber];
			$width=$rightNumber-$leftNumber+1;
			//--(END)-->fetching parent left and right number

			if (is_array($row)) {
				if (empty($row[$this->colnParentId])) {
					$this->raiseError(Tree_System_Err_Deleteing_Root_Node_Does_Not_Allowed);
					return false;
				}
					
				mysql_query("LOCK TABLE `$this->tableName` WRITE");
				
				if ($mode=='onlyChilds') {
					trigger_error('this mode is not availabe yet!',E_USER_ERROR);
					mysql_query("UNLOCK TABLES");
					
				} elseif ($mode=='onlyParent' or ($rightNumber-$leftNumber>1 and $mode=='alsoChilds')) {
					if ($rightNumber-$leftNumber<=1 or $mode=='alsoChilds') {
						$sqlQuery="DELETE FROM `$this->tableName` WHERE `$this->colnLeftNumber` BETWEEN $leftNumber AND $rightNumber;";
						mysql_query($sqlQuery);
						$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber` = `$this->colnRightNumber` - $width WHERE `$this->colnRightNumber` > $rightNumber;";
						mysql_query($sqlQuery);
						$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber` = `$this->colnLeftNumber` - $width WHERE `$this->colnLeftNumber` > $rightNumber;";
						mysql_query($sqlQuery);
						$result=true;
					} else {
						$this->raiseError(Tree_System_Err_Deleteing_A_Node_With_Childs_Does_Not_Allowed_In_This_Mode);
						mysql_query("UNLOCK TABLES");
						return false;
					}
					
				} elseif ($mode=='keepChilds' and $rightNumber-$leftNumber>1) {
					if (1==1) {
						$sqlQuery="DELETE FROM `$this->tableName` WHERE `$this->colnLeftNumber` = $leftNumber;";
						mysql_query($sqlQuery);
						$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber` = `$this->colnRightNumber` - 1, `$this->colnLeftNumber` = `$this->colnLeftNumber` - 1 
									WHERE `$this->colnLeftNumber` BETWEEN  $leftNumber AND $rightNumber;";
						mysql_query($sqlQuery);
						$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber` = `$this->colnRightNumber` - 2 WHERE `$this->colnRightNumber` > $rightNumber;";
						mysql_query($sqlQuery);
						$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber` = `$this->colnLeftNumber` - 2 WHERE `$this->colnLeftNumber` > $rightNumber;";
						mysql_query($sqlQuery);
						$result=true;
					} else {
						$this->raiseError(Tree_System_Err_Unable_To_Delete);
						mysql_query("UNLOCK TABLES");
						return false;
					}
				}
				
				mysql_query("UNLOCK TABLES");
			}
		} else {

		}

		return $result;
	}
	
	/* Tested */
	function move($id,$newParentId) {
		if (empty($id) or empty($newParentId))
			return false;
			
		mysql_query("LOCK TABLE `$this->tableName` WRITE");
		//--(BEGIN)-->fetching node and node parent left and right number
		$row=$this->getRow($id);
		$rightNumber=$row[$this->colnRightNumber];
		$leftNumber=$row[$this->colnLeftNumber];
		$width=$rightNumber-$leftNumber+1;
		
		$parentRow=$this->getRow($newParentId);
		$parentRightNumber=$parentRow[$this->colnRightNumber];
		$parentLeftNumber=$parentRow[$this->colnLeftNumber];
		$parentWidth=$parentRightNumber-$parentLeftNumber+1;
		//--(END)-->fetching node and node parent left and right number
		
		//--(BEGIN)-->check to prevent moveing node to one of its childs
		if ($parentLeftNumber>=$leftNumber and $parentRightNumber<=$rightNumber) {
			$this->raiseError(Tree_System_Err_Moving_To_Child_Of_Current_Node_Does_No_Allowed);
			return false;
		}
		//--(END)-->check to prevent moveing node to one of its childs
		
		//--(BEGIN)-->make enought space for putting node and its childs
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber`=`$this->colnRightNumber`+$width
						WHERE `$this->colnRightNumber`>'$parentLeftNumber'";
		mysql_query($sqlQuery);
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber`=`$this->colnLeftNumber`+$width
						WHERE `$this->colnLeftNumber`>'$parentLeftNumber'";
		mysql_query($sqlQuery);
		//recalculate parent width and right number
		$parentRightNumber=$parentRightNumber+$width-1;
		$parentWidth=$parentRightNumber-$parentLeftNumber+1;
		//--(END)-->make enought space for putting node and its childs
		
		//--(BEGIN)-->move node and its childs to new position
		$prevLeftNumber=$leftNumber;
		if ($leftNumber>$parentLeftNumber) {
			$width=$width;
			$leftNumber=$leftNumber+$width;
			$rightNumber=$rightNumber+$width;
		} else {
			
		}
		$distance=$parentLeftNumber-$leftNumber+1;
		
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber`=`$this->colnRightNumber`+($distance),
							`$this->colnLeftNumber`=`$this->colnLeftNumber`+($distance)
						WHERE `$this->colnLeftNumber`>='$leftNumber' AND `$this->colnRightNumber`<='$rightNumber'";
		mysql_query($sqlQuery);
		//--(END)-->move node and its childs to new position
		
		//--(BEGIN)-->delete previous space
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnRightNumber`=`$this->colnRightNumber`-$width
						WHERE `$this->colnRightNumber`>'$leftNumber'";
		mysql_query($sqlQuery);
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber`=`$this->colnLeftNumber`-$width
						WHERE `$this->colnLeftNumber`>'$leftNumber'";
		mysql_query($sqlQuery);
		//--(END)-->delete previous space
		/*
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber`=`$this->colnLeftNumber`+($distance)
						WHERE `$this->colnLeftNumber`>='$leftNumber'";
		mysql_query($sqlQuery);
		*/
		mysql_query("UNLOCK TABLES");
		
		return true;
	}
	
	/* Tested */
	function rebuild($parentId=0, $left=0) {
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
			$right = $this->rebuild($row[$this->colnId], $right);
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$sqlQuery="UPDATE `$this->tableName` SET `$this->colnLeftNumber`='$left', `$this->colnRightNumber`='$right' WHERE `$this->colnId`='$parentId'";
		mysql_query($sqlQuery);

		// return the right value of this node + 1
		return $right+1;
	}


	/* for recursive mode */
	function get_path($node_id,$row_info=false) {
		// look up the parent of this node
		$result = mysql_query("SELECT * FROM `$this->table_name` ".
								"WHERE `$this->coln_id`='$node_id';");
		$row = mysql_fetch_array($result);

		// save the path in this array [5]
		$path = array();

		// only continue if this $node isn't the root node
		// (that's the node with no parent)
		if ($row[$this->coln_parent_id]!='') {
			// the last part of the path to $node, is the name
			// of the parent of $node
			if ($row_info==false)
				$path[] = $row[$this->coln_parent_id];
			else
				$path[] = $row;
			// we should add the path to the parent of this node
			// to the path
			$path = array_merge($this->get_path($row[$this->coln_parent_id],$row_info), $path);
		}

		// return the path
		return $path;
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
		//$result=round(($right – $left - 1) / 2);
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
			$columnsValues[$this->colnParentId]=$this->cvParentId;

		}

		return $columnsValues;
	}

	function clear() {
		parent::clear();
		if (!$this->dynamicSystem) {
			$this->cvName=null;
			$this->cvNameFarsi=null;
			$this->cvLeftNumber=null;
			$this->cvRightNumber=null;
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


define('Tree_View_Structure_Is_Corrupted',20);

class CTreeView extends CDatabaseTreeSystemV2 {
	var $_prefix='myTree';
	var $_mainId='root';
	var $_nodeHtmlTemplate;
	var $_columnsNames;
	var $_titleColumnName;
	var $_displayMode;
	var $editIconPath;
	var $saveIconPath;
	var $_selectedRows;
	
	var $_jsFunctionOnSelectRow;
	var $_jsFunctionOnSelectRows;
	
	//require in getNodeNestedPositionByRow function
	var $_prevRow;
	
	var $_autoCollapseAtStart=true;

	var $_startNodeId=null;
	
	/*
		TreeView has some inline templates which will append some require 
		html to user template , sometimes user wants to make a very different template
		from scrach, in this case he show change this property. 
		availabe modes
		onlyCustom,
		onlyDefault,
		mergeCustomAndDefault,
		mergeCustomAndDefaultNecessaryParts,
		mergeCustomAndDefaultInvisibleParts
	*/
	var $_templateMode='mergeCustomAndDefault';
	
	function errorMessages($errorNo) {
		$messages=array(
			Tree_View_Structure_Is_Corrupted => "Tree structure has problem (probably corrupted, you may use rebuild method)!",
		);
		
		return $messages[$errorNo];
	}
	
	function setAutoCollapseAtStart($autoCollapseAtStart) {
		$this->_autoCollapseAtStart=$autoCollapseAtStart;
	}
	
	function setTemplateMode($templateMode) {
		$modes=array(
			'onlyCustom','onlyDefault',
			'mergeCustomAndDefault','mergeCustomAndDefaultNecessaryParts','mergeCustomAndDefaultInvisibleParts'
		);
		if (!in_array($templateMode,$modes)) {
			trigger_error("`$templateMode` is not a valid template mode (CTreeView), available modes are : ".implode(' , ',$modes),E_USER_ERROR);
		}
		
		$this->_templateMode=$templateMode;
	}
	
	function setStartNodeId($startNodeId) {
		$this->_startNodeId=$startNodeId;
	}
	
	function setTitleColumnName($name) {
		$this->_titleColumnName=$name;
	}
	
	function setDisplayMode($mode) {
		$this->_displayMode=$mode;
	}
	
	function setColumnsNames($columnsNames) {
		$this->_columnsNames=$columnsNames;
	}
	
	function setMainId($mainId) {
		$this->_mainId=$mainId;
	}
	
	function setPrefix($prefix) {
		$this->_prefix=$prefix;
	}
	
	function setJsFunctionOnSelectRow($functionName) {
		$this->_jsFunctionOnSelectRow=$functionName;
	}
	
	function setJsFunctionOnSelectRows($functionName) {
		$this->_jsFunctionOnSelectRows=$functionName;
	}
	
	function setSelectedRows($selectedRows) {
		$this->_selectedRows=$selectedRows;
	}
	
	function printJavaScripts() {?>
		<script language="javascript" type="text/javascript">
		//general scripts
		function <?=$this->_prefix?>GetElements(doc_obj) 
		{
			if (doc_obj==null) {doc_obj=document;}
			var all = doc_obj.all ? doc_obj.all :
					doc_obj.getElementsByTagName('*');
			var elements = new Array();
			for (var e = 0; e < all.length; e++)
					elements[elements.length] = all[e];
			return elements;
		}
		
		
		function <?=$this->_prefix?>Replace(s, t, u) {
		  /*
		  **  Replace a token in a string
		  **    s  string to be processed
		  **    t  token to be found and removed
		  **    u  token to be inserted
		  **  returns new String
		  */
		  i = s.indexOf(t);
		  r = "";
		  if (i == -1) return s;
		  r += s.substring(0,i) + u;
		  if ( i + t.length < s.length)
			r += <?=$this->_prefix?>Replace(s.substring(i + t.length, s.length), t, u);
		  return r;
		}
		
		function <?=$this->_prefix?>GetElementsByName(doc_obj) 
		{
			if (doc_obj==null) {doc_obj=document;}
			var all = doc_obj.all ? doc_obj.all :
					doc_obj.getElementsByTagName('*');
			var elements = new Array();
			for (var e = 0; e < all.length; e++)
			{
				elements[all[e].name] = all[e];
			}
			return elements;
		}
		
		function <?=$this->_prefix?>ToggleBullet(elm) {
			<?=$this->_prefix?>ToggleOpenCloseIndicator(elm);
			
			var newDisplay = "none";
			var e = elm.nextSibling;
			while (e != null) {
				if (e.tagName == "UL" || e.tagName == "ul") {
					if (e.style.display == "none") newDisplay = "block";
					break;
				}
				e = e.nextSibling;
			}
			while (e != null) {
				if (e.tagName == "UL" || e.tagName == "ul") e.style.display = newDisplay;
				e = e.nextSibling;
			}
		}
		
		function <?=$this->_prefix?>CollapseAll(id) {
			if (id=='') id='root';
			var lists = document.getElementsByTagName('UL');
			for (var j = 0; j < lists.length; j++) 
				lists[j].style.display = "none";
			lists = document.getElementsByTagName('ul');
			for (var j = 0; j < lists.length; j++) 
				lists[j].style.display = "none";
			var e = document.getElementById(id);
			e.style.display = "block";
		}
		
		function  <?=$this->_prefix?>ShowHideElement(id) {
			var elm=document.getElementById(id);
			if (elm.style.display=='none')
				elm.style.display='';
			else
				elm.style.display='none';
		}
		</script>
		  
		<script language="javascript" type="text/javascript">
		//special scripts
	
		function <?=$this->_prefix?>TriggerSave(id,titleId,valueId,imageIndicatorId) {
			var elm=document.getElementById(id);
			var imageIndicator=document.getElementById(imageIndicatorId);
			if (elm.style.display=='none') {
				elm.style.display='';
				if (imageIndicator)
					imageIndicator.src="<?=$this->saveIconPath?>";
			} else {
				if (imageIndicator)
					imageIndicator.src="<?=$this->editIconPath?>";
				elm.style.display='none';
				document.getElementById(titleId).innerHTML=document.getElementById(valueId).value;
			}
		}
		
		function <?=$this->_prefix?>ToggleOpenCloseIndicator(elm) {
			//elm.innerHTML=<?=$this->_prefix?>Replace(elm.innerHTML,' ','');
			if (elm.innerHTML=='[+]')
				elm.innerHTML='[-]';
			else
				elm.innerHTML='[+]'
		}
		
		
		function <?=$this->_prefix?>HighlightParents(elm)
		{
			$hightlight=false;
			if (elm.checked) {$hightlight=true;}
			var idName=<?=$this->_prefix?>Replace(elm.id,'[selected]','');
			var parentId=document.getElementById(idName+'[parent_id]').value;
			var currElm=elm;
			var selectedTreeItemsStr='';
		
			while (parentId!='' & parentId!='1') {
				mainId='row['+parentId+']';
				currElm=document.getElementById(mainId);
				if ($hightlight) {color='red';} else {color='';}
				if ($hightlight)
					document.getElementById(mainId+'[flag]').value++;
				else 
					document.getElementById(mainId+'[flag]').value--;
				if (document.getElementById(mainId+'[flag]').value>0)
					document.getElementById(mainId).className='<?=$this->_prefix?>HighlightTreeItem';
				else 
					document.getElementById(mainId).className='<?=$this->_prefix?>TreeItem';
				id=mainId+'[parent_id]';
				parentId=document.getElementById(id).value;
				
				var parentElement=document.getElementById('row'+parentId);
				if (typeof(parentElement)!='object')
				{
					parentId='';
				}
			}
			
			var selectedTreeItemsId=document.getElementById('<?=$this->_prefix?>SelectedTreeItemsId');
			var selectedTreeItems=document.getElementById('<?=$this->_prefix?>SelectedTreeItems');
		
			var id=<?=$this->_prefix?>Replace(idName,'<?=$this->_prefix?>[','');
			id=<?=$this->_prefix?>Replace(id,']','');
			
			document.getElementById('<?=$this->_prefix?>SelectedTreeItemId').value=id;
			document.getElementById('<?=$this->_prefix?>SelectedTreeItemPath').innerHTML=document.getElementById('<?=$this->_prefix?>Row['+id+'][item]').innerHTML;
			
			if (selectedTreeItemsId.value=='') {selectedTreeItemsId.value=',';}
			if (elm.checked) {
				if (selectedTreeItemsId.value.indexOf(','+id+',')<0)
					{ selectedTreeItemsId.value+=id+','; }
				selectedTreeItems.innerHTML+=document.getElementById(idName).innerHTML+' | ';
			} else {
				selectedTreeItemsId.value=<?=$this->_prefix?>Replace(selectedTreeItemsId.value,<?=$this->_prefix?>Replace(idName,'<?=$this->_prefix?>Row','')+',','');
				selectedTreeItems.innerHTML=<?=$this->_prefix?>Replace(selectedTreeItems.innerHTML,document.getElementById(idName).innerHTML+' | ','');
			}
		}
		
		
		function <?=$this->_prefix?>EmptyTree()
		{
			var lists = <?=$this->_prefix?>GetElements(document.getElementById('root'));
			var element;
			if (document.getElementById('<?=$this->_prefix?>SelectedTreeItems'))
				document.getElementById('<?=$this->_prefix?>SelectedTreeItems').innerHTML='';
			if (document.getElementById('<?=$this->_prefix?>SelectedTreeItemsId'))
				document.getElementById('<?=$this->_prefix?>SelectedTreeItemsId').value='';
			if (document.getElementById('<?=$this->_prefix?>SelectedTreeItemId'))	
				document.getElementById('<?=$this->_prefix?>SelectedTreeItemId').value='';
			for (var j = 0; j < lists.length; j++)
			{
				element=lists[j];
				if (element.type=='checkbox') lists[j].checked = false;
				if (element.tagName=='A') lists[j].className='<?=$this->_prefix?>TreeItem';
				if (element.type=='hidden' && element.id.indexOf('flag')>0)	lists[j].value='0';
			}
		}
		
		function <?=$this->_prefix?>CheckSelectedTreeItems(controlName)
		{
			var indexes=document.getElementById(controlName).value;
			var lastIndexes=document.getElementById(controlName).value;
			indexes=indexes.split(",");
			document.getElementById('<?=$this->_prefix?>SelectedTreeItems').innerHTML='';
			var myitem;
			for (var i=0; i<indexes.length; i++)
				if (indexes[i]!='') {
					myitem=document.getElementById('<?=$this->_prefix?>Row['+indexes[i]+'][selected]');
					if (typeof(myitem)=='object') {
						myitem.checked=true;
						<?=$this->_prefix?>HighlightParents(myitem)
					}
				}
		}
		
		function <?=$this->_prefix?>AddNewTreeItem(id,liId,baseName,prefix)
		{
			var elmLi=document.getElementById(liId);
			var data='';
			var rowId;
			parentId=id;
			
		
			var ranUnrounded=Math.random()*45234634563;
			var ranNumber=Math.round(ranUnrounded);
			id='new___'+ranNumber;
			
			var rowName=baseName+'Row['+id+']';
		
			data='<li id=\''+rowName+'[item]\'>';

			var replacements=new Array();
			replacements['%{item_base_name}%']=baseName;
			replacements['%{prefix}%']=prefix;
			replacements['%column_parent_id_value%']=parentId;
			replacements['%{item_number}%']=id;	
			
			data+=<?=$this->_prefix?>ParseTemplate('','<?=$this->_prefix?>HtmlTemplateBox',replacements);

			data+='</li>';
		
			if (elmLi.innerHTML.indexOf('<ul>')>=0) {
				elmLi.innerHTML=<?=$this->_prefix?>Replace(elmLi.innerHTML,'<ul>','<ul>'+data);
			} else {
				data='<ul>'+data+'</ul>';
				elmLi.innerHTML+=data;
			}
		}
		
		function <?=$this->_prefix?>ParseTemplate(boxContainerId,tempBoxId,replacements) {
			var items_borad=document.getElementById(boxContainerId);
			var template_item_box=document.getElementById(tempBoxId).innerHTML;
			var key;
			
			for (key in replacements) {
				//if (document.all) mkey='"'+key+'"';
				var myregexp = new RegExp(key, "gmi");
				template_item_box=template_item_box.replace(myregexp,replacements[key]);
			}
			
			myregexp = new RegExp("%[^ %{}]*%", "gmi");
			if (document.all) {
				template_item_box=template_item_box.replace(myregexp,'');
				template_item_box.replace(/(id=)([^ ]*)/g, "$1\"$2\"");
				template_item_box.replace(/(name=)([^ ]*)/g, "$1\"$2\"");
			} else {
				template_item_box=template_item_box.replace(myregexp,'');
			}

			return template_item_box;
		}
		
		function <?=$this->_prefix?>AddNewBox(boxContainerId,tempBoxId,baseName,prefix) {
			var item_number=0;
			var element_name;
			var element;
			
			do {
				item_number++;
				element_name=baseName+'['+item_number+'][columns][id]';
				element=document.product_form.elements[element_name];
			} while (element);
		
			template_item_box=<?=$this->_prefix?>ParseTemplate(item_number,boxContainerId,tempBoxId,baseName,prefix);
	
			items_borad.innerHTML=items_borad.innerHTML+template_item_box;
		}
		
		
		function  <?=$this->_prefix?>GetRowIdById(id) {
			var myregexp = /[^\[\]]*\[([^\[\]]*)\].*/;
			var match = myregexp.exec(id);
			if (match != null && match.length > 1) {
				return match[1];
			} else {
				return  false;
			}
		}
		
		
		function <?=$this->_prefix?>SelectRow(object,auto) {
			var selectedRowId=<?=$this->_prefix?>GetRowIdById(object.id);
			var baseName=object.id.replace(/([^\[\]]*)\[([^\[\]]*)\].*/g, "$1[$2]");
			<?=$this->_prefix?>OnSelectRow(object,selectedRowId,baseName,'<?=$this->_displayMode?>',auto);
			
			return true;
			/*
			<?=$this->_prefix?>HighlightParents(object);
			selectedTreeItemPath
			sendSelectRow(object);
			*/
		}

		var <?=$this->_prefix?>SelectedRows=new Array();

		function <?=$this->_prefix?>OnSelectRow(object,selectedRowId,baseName,displayMode,auto) {
			var titleElm=document.getElementById(baseName+'[title]');
			var keyVar;

			if (object.checked) {
				var rowInfo=new Array();
				rowInfo['id']=selectedRowId;
				rowInfo['title']=titleElm.innerHTML;
				 <?=$this->_prefix?>SelectedRows[selectedRowId]=rowInfo;
			} else {
				for ( keyVar in  <?=$this->_prefix?>SelectedRows ) {
					if ( <?=$this->_prefix?>SelectedRows[keyVar]['id']==selectedRowId)
						 <?=$this->_prefix?>SelectedRows.splice(keyVar,1);
				}
			}
			
			if (!auto) {
				<? if (!empty($this->_jsFunctionOnSelectRow)) {?>
					<?=$this->_jsFunctionOnSelectRow?>(object,selectedRowId,baseName,displayMode);
				<? }?>
			}
		}
		
		
		function <?=$this->_prefix?>UpdateSelectedRowsArray(baseName)
		{
			if (baseName=='') baseName='<?=$this->_prefix?>Row';
			var all = <?=$this->_prefix?>GetElements(document);
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];
				if ((elm.type=='checkbox' || elm.type=='radio') && elm.id.match(/myTreeRow\[.*/i)) 
				if (elm.checked) {
					//elm.checked=true;
					<?=$this->_prefix?>SelectRow(elm);
				}
			}
		}
		
		/* selectedRowsId is like ",5,43,35,"*/
		function <?=$this->_prefix?>SelectRows(selectedRowsId) {
			selectedRowsId=','+selectedRowsId+',';
			var all = <?=$this->_prefix?>GetElements(document);
			for (var e = 0; e < all.length; e++) {
				var elm=all[e];

				if (elm.type=='checkbox' || elm.type=='radio') {
					rowId=<?=$this->_prefix?>GetRowIdById(elm.id);
					
					if (selectedRowsId.indexOf(','+rowId+',')>=0) {
						elm.checked=true;
						<?=$this->_prefix?>SelectRow(elm,true);
					}
				}
			}
		}		
		</script>
		
	<?
	}
	
	
	function printOnloadScript() {?>
		<script language="javascript" type="text/javascript">
			function <?=$this->_prefix?>OnloadFunctions()
			{
				<? if ($this->_autoCollapseAtStart) {?>
					<?=$this->_prefix?>CollapseAll('<?=$this->_mainId?>');
				<? }?>
				<? if (!empty($this->_jsFunctionOnSelectRows)) {?>
					<?=$this->_jsFunctionOnSelectRows?>();
				<? }?>
				//check_selected_tree_items('selected_tree_items_id');
			}
			 <?=$this->_prefix?>OnloadFunctions();
			/*window.onload=<?=$this->_prefix?>OnloadFunctions();*/
		</script>
	<?
	}

	
	function printTemplate() {?>
		<div id="<?=$this->_prefix?>HtmlTemplateBox" style="display:none">
			<?=$this->_nodeHtmlTemplate;?>
		</div>
		<?
	}
	
	function getNodeNestedPositionByRow($row) {
	/*
		$depth=$row['depth'];
		$lastDepth=$this->_prevRow['depth'];
		if (!isset($lastDepth)) $lastDepth=$depth;
		
		if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1) {
			$status='hasChild|';
		}
		$status.='normal';
		if ($depth<$lastDepth) {//parentEnd
			$status.='parentEnd';
		}
		$this->_prevRow=$row;
	*/
	}
	
	function printTree() {
		$rows=$this->getAllWithDepth($this->_startNodeId);
		if ($rows==false) {
			$this->raiseError(Tree_View_Structure_Is_Corrupted);
			return false;
		}
		$displayMode=$this->_displayMode;/*view ; singleSelection ; multiSelection*/
	?>		
		<!-- (BEGIN) : Tree -->
		<ul id="<?=$this->_mainId?>" class="<?=$this->_prefix?>">
		<?
		foreach ($rows as $row) {
			$rowName=$this->_prefix."Row[".$row[$this->colnId]."]";
			
			$depth=$row['depth'];
			
			if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1)
				$hasChild=true; else $hasChild=false;
			if (!isset($lastDepth)) $lastDepth=$depth;
			
			if ($depth<$lastDepth) {/*parentEnd*/
				echo str_repeat("</li></ul>\n",$lastDepth-$depth);
			}
			
			echo '<li id="'.$rowName.'[item]">';
			
			if ($displayMode=='edit') {
				echo $this->useTemplate($row);
			} elseif ($displayMode=='singleSelect' or $displayMode=='multiSelect') {
				echo $this->useTemplate($row);
			} else {
				echo $this->useTemplate($row);
			}
			
			
			if ($hasChild) {
				echo "<ul>\n";
			} else {
				echo "</li>\n";
			}
			$lastDepth=$depth;
		} 
		echo str_repeat("</li></ul>\n",$depth);
		?>
		</ul>
		<!-- (END) : Tree -->
	<?
	}
	function setTemplate($nodeHtmlTemplate=null,$displayMode=null) {
		if (is_null($displayMode)) $displayMode=$this->_displayMode;

		$editSaveIndicator='...';
		if (!empty($this->editIconPath))
			$editSaveIndicator='<img id="%{item_base_name}%[%{item_number}%][edit_save_icon]" src="'.$this->editIconPath.'" alt="edit/save" border="0"/>';

		if ($this->_templateMode=='onlyCustom') {
			$this->_nodeHtmlTemplate=$nodeHtmlTemplate;
			return true;
		}
		
		if ($displayMode=='singleSelect' or $displayMode=='multiSelect') {
			if ($displayMode=='singleSelect') {
				$selectControl='<input style="margin-bottom:0px" name="%{item_base_name}%[selectedRow][id]" id="%{item_base_name}%[%{item_number}%][selected]" type="radio" onchange="%{prefix}%SelectRow(this)" value="%{item_number}%" %checked%>&nbsp;';
			} elseif($displayMode=='multiSelect') {
				$selectControl='<input style="margin-bottom:0px" name="%{item_base_name}%[%{item_number}%][selected]" id="%{item_base_name}%[%{item_number}%][selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" value="true" %checked%>&nbsp;';
			}

			$this->_nodeHtmlTemplate=<<<EOT
				<input name="%{item_base_name}%[%{item_number}%][id]" id="%{item_base_name}%[%{item_number}%][id]" type="hidden" value="%column_id_value%"/>
				<input name="%{item_base_name}%[%{item_number}%][parent_id]" id="%{item_base_name}%[%{item_number}%][parent_id]" type="hidden" value="%column_parent_id_value%"/>
				<input name="%{item_base_name}%[%{item_number}%][flag]" id="%{item_base_name}%[%{item_number}%][flag]" type="hidden" value="0"/>
				&raquo;
				<a href="?" id="%{item_base_name}%[%{item_number}%][openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[%{item_number}%][container]">
					$selectControl
					<span id="%{item_base_name}%[%{item_number}%][title]">%title%</span>
				</span>
EOT;
		} elseif ($displayMode=='edit') {
			$this->_nodeHtmlTemplate=<<<EOT
				<input name="%{item_base_name}%[%{item_number}%][id]" id="%{item_base_name}%[%{item_number}%][id]" type="hidden" value="%column_id_value%"/>
				<input name="%{item_base_name}%[%{item_number}%][parent_id]" id="%{item_base_name}%[%{item_number}%][parent_id]" type="hidden" value="%column_parent_id_value%"/>
				<input name="%{item_base_name}%[%{item_number}%][flag]" id="%{item_base_name}%[%{item_number}%][flag]" type="hidden" value="0"/>
				&raquo;
				<a href="?" id="%{item_base_name}%[%{item_number}%][openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[%{item_number}%][container]">
					<input style="margin-bottom:0px" name="%{item_base_name}%[%{item_number}%][selected]" id="%{item_base_name}%[%{item_number}%][selected]" type="checkbox" onchange="%{prefix}%SelectRow(this)" %checked% value="true">&nbsp;
					<a href="javascript:void(0)" id="%{item_base_name}%" onclick="%{prefix}%AddNewTreeItem('%column_id_value%','%{item_base_name}%[%{item_number}%][item]','%{item_base_name}%','%{prefix}%'); return false;" class="%{prefix}%TreeItem" title="Add new item">%addIndicator%</a>
					<span id="%{item_base_name}%[%{item_number}%][title]">%title%</span>
					
					<a href="" onclick="%{prefix}%TriggerSave('%{item_base_name}%[%{item_number}%][moreFields]','%{item_base_name}%[%{item_number}%][title]','%{item_base_name}%[%{item_number}%][columns][$this->_titleColumnName]','%{item_base_name}%[%{item_number}%][edit_save_icon]');return false;">$editSaveIndicator</a>
					<div id="%{item_base_name}%[%{item_number}%][moreFields]" style="display:none" class="%{prefix}%EditBox">
						$nodeHtmlTemplate
					</div>
				</span>
EOT;
		} else {
			$this->_nodeHtmlTemplate=<<<EOT
				&raquo;
				<a href="?" id="%{item_base_name}%[%{item_number}%][openCloseButton]" onclick="%{prefix}%ToggleBullet(this); return false;" class="%{prefix}%OpenCloseButton" title="Open / Close">%openCloseIndicator%</a>
				<span id="%{item_base_name}%[%{item_number}%][container]">
					<span id="%{item_base_name}%[%{item_number}%][title]">%title%</span>
					$nodeHtmlTemplate
				</span>
EOT;
		}
	}
	
	function useTemplate($row) {
		$boxTemplate=$this->_nodeHtmlTemplate;
		$item_number=$row[$this->colnId];
		$openCloseIndicator="";
		if ($row[$this->colnRightNumber]-$row[$this->colnLeftNumber]>1) {
			if ($this->_autoCollapseAtStart) {
				$openCloseIndicator='[+]';
			} else {
				$openCloseIndicator='[-]';
			}
			$hasChild=true;
		} else $hasChild=false;
		
		if (is_array($this->_selectedRows))
		if  (in_array($row[$this->colnId],$this->_selectedRows)) {
			$rowSelected='checked';
		}
		
		//--(BEGIN)-->use template
		$holders=array(  
			"%title%",
			"%{prefix}%",
			"%openCloseIndicator%",
			"%{item_base_name}%",
			"%{item_number}%",
			"%checked%",
			"%column_parent_id_value%",
			"%column_id_value%",
			"%column_name_value%",
			"%addIndicator%"
		);
		
		$replacements=array(
			$row[$this->_titleColumnName],
			$this->_prefix,
			$openCloseIndicator,
			$this->_prefix.'Row',
			$item_number,
			$rowSelected,
			$row[$this->colnParentId],
			$row[$this->colnId],
			$row[$this->colnName],
			"+"
		);
		
		foreach($this->_columnsNames as $columnName) {
			$holders[]="%column_$columnName"."_value%";
			$replacements[]=$row[$columnName];
			
			//--(Begin)--> defaults -->
			$boxTemplate=$boxTemplate.'<input name="%{item_base_name}%[%{item_number}%][columns_default]['.$columnName.']" type="hidden" value="%column_'.$columnName.'_value%"/>'."\n";
			//--(End)--> defaults -->
		}

		$itemHtml=str_replace($holders,$replacements,$boxTemplate);
		/*
		show_custom_row('گواهی نامه ها '.'<a href="javascript:glAddNewBox(\'certificate_images\',\'template_certificate_image_box\',\'certificate_image\')">+</a>',
			'<div id="certificate_images">'.$items_html.'</div>');	*/
		//--(END)-->use template
		
		return $itemHtml;
	}
	
	function printAll() {
		$this->printJavaScripts();
		if ($this->_displayMode=='edit')
			$this->printTemplate();
		$this->printTree();
		$this->printOnloadScript();
	}
	
	
	function printDefaultStyles() {?>
		<style>
		.<?=$this->_prefix?>HighlightTreeItem{ 
			font-weight:bold;
			color:red;
			text-decoration:none;
		 }
		.<?=$this->_prefix?>OpenCloseButton{ 
			font-weight:normal;
			color:inherit;
			text-decoration:none;
			font-family: "Courier New", Courier, monospace;
			font-size:15px;
		 }
		 
		.<?=$this->_prefix?> {
			
		}
		
		.<?=$this->_prefix?> li {
			margin-top:8px;
		}
		
		.<?=$this->_prefix?>EditBox {
			padding:4px;
			margin-top:3px;
			margin-right:10px;
			border:1px dotted gray;
			width:300px
		}
		</style>
	<?
	}
}
?>