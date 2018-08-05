<?php

/**
 * This is quick and dirty MVC. I will put something better in place in time.
 */
require_once('model.php');
require_once('page.php');


$SQL_DB = "/usr/share/beerlog/db/beerlog.db";
$db 	= new SQLite3($SQL_DB);

$perm_views = array('','home','monitor','session','data','recipe','sample');
$perm_actions = array('','view','edit','save','delete');
$perm_graphs = array('day','hour','week','month','year');

// keys
$pks = explode(',', $_REQUEST['pks']);

// Security
if(in_array($_REQUEST['view'],$perm_views)) {
	$view = $_REQUEST['view'];
} else {
	// unpermitted view. Should probably log an error somewhere
	$view = '';
}

if(in_array($_REQUEST['do'],$perm_actions)) {
	$do = $_REQUEST['do'];
} else {
	// unpermitted action. Should probably log an error somewhere
	$do = '';
}

if(in_array($_REQUEST['graph'],$perm_graphs)) {
	$graph = $_REQUEST['graph'];
} else {
	// unpermitted action. Should probably log an error somewhere
	$graph = 'day';
}

$p = new Page($view);

switch($view) {
	case '':
	case 'home':
		$s 	= new Session($db);
		if($s->getCurrent()) {
			// We have an existing session.
			// - Show details
			// - - Graph of temps & bloops
			// - - Samples listed and highlighted on graph
			// - - Current temps
			// - - Session details (recipe, start date, notes etc)
			// - Show End Session Button
			// - -^ Set end date to current ts
			// - Show compare with previous sessions on this recipe
			// - - -> Compare sessions page. huh?
			// - Show "Log sample" button
			// - - -> Create Sample Page
			$content = $s->fields['name'];
			
		} else {
			// No current session
			// - Show "Start New Session" button
			// - -> New Session page
			// - Show "Resume previous Session" button (oopsy)
			// - -^ Set end date to null on newest session
			// - List previous sessions
			// - -> View Recipes
			// - -> Raw Data
			$content = 'No existing session';
		}
		break;
	case 'monitor':
		$content = $p->showMonitor($graph);
		break;
	default:
		switch ($do) {
			case 'delete':
 				$o = new $view($db);
				if($o->load($pks)) {
					$o->destroy();
				}
			
			case '':
				$o = new $view($db);
				$content = '<h2 class="title is-2">'.ucfirst($view)."s</h2>\n";
				$content .= '<div class="columns">';
				
				for($i=0;$i<4;$i++) {
					$counter = 0;
					if($o->find('1=1 LIMIT '.($i*15).',15')) {
						$listcontent = '';
						while($o->load()) {
							$listcontent .= $p->listItem($view, $o->getPKValues(),$o->getDisplayName());
							$counter++;
						}
						
					}
					$content .= $p->listWrapper($listcontent);
					if($counter < 15) {
						break;
					}
				}
				$content .= '</div>'.$p->newButton($view);
				break;
			case 'save':
				$o = new $view($db);
				foreach($_POST as $k => $v) {
					if(substr($k,0,6) == 'field_') {
						$o->fields[substr($k,6)] = $v;
					}
				}
				$o->save();
			case 'view':
			case 'edit':
				$o = new $view($db);
				if($o->load($pks)) {
					$content = $p->editObject($o);
				} else {
					$content = "Unknown object";
				}
			break;
			
			break;
			default:
			break;
		}
	break;
}


$p->output(($title ? "Beerlogger - $title" : "Beerlogger"), $content);


?>