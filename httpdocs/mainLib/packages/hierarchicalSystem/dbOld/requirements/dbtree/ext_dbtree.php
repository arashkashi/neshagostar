	<script language="javascript1.2">
		function toggleBullet(elm) {
			toggleOpenCloseIndicator(elm);
			
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
		
		function collapseAll(id,exceptRootChilds) {
			if (id=='') id='root';
			if (!exceptRootChilds) exceptRootChilds=false;
			
			var lists = document.getElementsByTagName('UL');
			for (var j = 0; j < lists.length; j++) {
				lists[j].style.display = "none";
				if (lists[j].parentNode.id==id)
					lists[j].style.display = "block";
			}
			lists = document.getElementsByTagName('ul');
			for (var j = 0; j < lists.length; j++) {
				lists[j].style.display = "none";
				if (lists[j].parentNode.id==id)
					lists[j].style.display = "block";
			}
			var e = document.getElementById(id);
			e.style.display = "block";
		}
		
		function toggleOpenCloseIndicator(elm) {
			if (elm.innerHTML=='[+]')
				elm.innerHTML='[-]';
			else
				elm.innerHTML='[+]'
		}
		
		/* tree_root is the id of the first UL/OL in tree*/
		window.onload=function() { collapseAll('tree_root',true); };
	</script>

<?
    if (!isset($_GET['section_id'])) {
        $_GET['section_id'] = 1;
    }
    
    // Prepare data to view ajar tree
    $dbtree->Branch((int)$_GET['section_id'],'*');

    // Check class errors
    if (!empty($dbtree->ERRORS_MES)) {
        echo 'DB Tree Error!';
        echo '<pre>';
        print_r($dbtree->ERRORS_MES);
        if (!empty($dbtree->ERRORS)) {
            print_r($dbtree->ERRORS);
        }
        echo '</pre>';
        exit;
    }

    ?>
	<? echo $navigator . '<br><br>'."\n";?>
	
	
	<ul id="tree_root" style="padding-left:0px">
    <?php
    while ($item = $dbtree->NextRow()) {
		$rowName="treeRow[".$item['section_id']."]";
		//$depth=$item['section_level'];
		$depth=$item['section_level'];
		
		if ($item['section_right']-$item['section_left']>1)
			$hasChild=true; else $hasChild=false;
		if (!isset($lastDepth)) $lastDepth=$depth;
		
		if ($depth<$lastDepth) {/*parentEnd*/
			echo "\n";
			echo str_repeat('    ', 1 * $depth);
			echo str_repeat("</ul>\n".str_repeat('    ', 1 * $depth)."</li>\n",$lastDepth-$depth);
		}
		
        if (@$_GET['section_id'] <> $item['section_id']) {
        	echo "\n";
        	echo str_repeat('    ', 1 * $depth);
			echo '<li id="'.$rowName.'[item]">';
			
			if ($hasChild)
				echo '<a href="?" id="" onclick="toggleBullet(this); return false;" title="Open / Close">[+]</a>';
				
			//--(BEGIN)-->Baraie Taghir dadane title va link node-ha baiad injaro taghir bedi
            echo ' <a href="dbtree_visual_demo.php?mode=expandable_branch&section_id=' . $item['section_id'] . '">' . $item['section_name'] . '</a>';
            //--(END)-->Baraie Taghir dadane title va link node-ha baiad injaro taghir bedi
        }
        
        echo "\n";
		echo str_repeat('    ', 1 * $depth);
		
		if ($hasChild) {
			echo "<ul>";
		} else {
			echo "</li>";
		}
		$lastDepth=$depth;
    }
    
	echo "\n";
	echo str_repeat('    ', 1 * $depth);
	echo str_repeat("</ul>\n".str_repeat('    ', 1 * $depth)."</li>\n",$depth);
    ?>
    
    </ul>