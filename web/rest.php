<?php

/**
 * This is quick and dirty REST. Sans HTTP methods (who cares?)
 */
require_once('model.php');

$SQL_DB = "/usr/share/beerlog/db/beerlog.db";
$db 	= new SQLite3($SQL_DB);

$r = array();	//response object
$r['meta'] = array('status' => '200' , 'errno' => '0', 'message' => '');

$perm_objects 	= array('session','data','recipe','sample','yeast');
$perm_actions 	= array('','create','read','update','delete','list');

// Security
if(in_array($_GET['o'],$perm_objects)) {
	$o_name = $_GET['o'];
	$o = new $o_name($db);
} else {
	// unpermitted view. Should probably log an error somewhere
	$r['meta'] = array('status' => '400' , 'errno' => '100', 'message' => 'Object does not exist');
	$do = '';
}

if(in_array($_GET['do'],$perm_actions)) {
	$do = $_GET['do'];
} else {
	// unpermitted action. Should probably log an error somewhere
	$r['meta'] = array('status' => '400' , 'errno' => '101', 'message' => 'Action is not recognized');
	$do = '';
}

// keys
if($_GET['pks']) {
	$pks = explode(',', $_GET['pks']);
}


switch ($do) {
	case 'delete':
		if($pks) {
			if($o->load($pks)) {
				if(!$o->destroy() ) {
					$r['meta'] = array('status' => '500' , 'errno' => '1', 'message' => 'Failed to destroy '.get_class($o).' with PK '.implode(',',$pks));
				}
			} else { 
				$r['meta'] = array('status' => '500' , 'errno' => '2', 'message' => 'Failed to load '.get_class($o).' with PK '.implode(',',$pks));
			}
		} else {
			$r['meta'] = array('status' => '400' , 'errno' => '3', 'message' => 'pks not supplied');
		}
		break;
	case 'create':
	case 'update':
		if($pks) {
			if(!$o->load($pks)) {
				$r['meta'] = array('status' => '500' , 'errno' => '4', 'message' => 'Failed to load '.get_class($o).' with PK '.implode(',', $pks));
				break;
			}
		}
		foreach($_POST as $k => $v) {
			if(substr($k,0,6) == 'field_') {
				$o->fields[substr($k,6)] = $v;
			}
		}
		
		if(!$o->save()) {
			$r['meta'] = array('status' => '500' , 'errno' => '5', 'message' => 'Failed to save '.get_class($o));
		}
		
		$r['data'] = $o;
		
		break;
	case 'list':
		if($_POST['where']) {
			$w = $_POST['where'];
		} else {
			$w = '1=1';
		}
		if($o->find($w)) {
			$r['data'] = array();
			while($o->load()) {
				$r['data'][] = clone $o;
			}
		}
		break;
	case 'read':
		if($o->load($pks)) {
			$r['data'] = $o;
		} else {
			$r['meta'] = array('status' => '500' , 'errno' => '6', 'message' => 'Failed to load '.get_class($o).' with PK '.implode(',',$pks));
		}
		break;
		
}
header('Content-Type: application/json',true,$r['meta']['status']);
print json_encode($r);
?>