<?php

/**
 * This is quick and dirty MVC.
 */
require_once('model.php');
require_once('page.php');

$SQL_DB = "/usr/share/beerlog/db/beerlog.db";
$db 	= new SQLite3($SQL_DB);

$perm_views 	= array('','home','monitor','brew','data','recipe','sample','newBrew','yeast');
$perm_actions 	= array('','view','edit','save','delete','resumePrevBrew','newBrew','endBrew','newSample');
$perm_graphs 	= array('day','hour','week','month','year');

// keys
if($_REQUEST['pks']) {
	$pks = explode(',', $_REQUEST['pks']);
}

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
		$title = 'Home';
		$s = new Brew($db);
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
			ob_start();
			include 'templates/home.php';
			$content = ob_get_clean();
			
		} else {
			// No current session
			// - Show "Start New Session" button
			// - -> New Session page
			// - Show "Resume previous Session" button (oopsy)
			// - -^ Set end date to null on newest session
			// - List previous sessions
			
			// Is there a previous session
			ob_start();
			include 'templates/home-new.php';
			$content = ob_get_clean();
		}
		break;
	case 'monitor':
		$title = 'Monitor';
		$content = $p->showMonitor($graph);
		break;
	case 'newBrew':
		$title = 'New Brew';
		ob_start();
		include 'templates/new-brew.php';
		$content = ob_get_clean();
		break;
	default:
		// this should be retired in favour of the rest script
		switch ($do) {
			case 'delete':
 				$o = new $view($db);
				if($o->load($pks)) {
					$o->destroy();
				}
			
			case '':	//list
				$title = $view;
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
				if($pks) {
					$o->load($pks);
				}
				foreach($_POST as $k => $v) {
					if(substr($k,0,6) == 'field_') {
						$o->fields[substr($k,6)] = $v;
					}
				}
				$o->save();
			case 'view':
				$do = 'view';	// reset if we've flowed from save
			case 'edit':
				$o = new $view($db);
				if($o->load($pks)) {
					// see if we have a custom template first
					if(file_exists('templates/'.$do.'-'.$view.'.php')){
						ob_start();
						include 'templates/'.$do.'-'.$view.'.php';
						$content = ob_get_clean();
					} else {
						$content = $p->editObject($o);
					}
				} else {
					$content = "Unknown object";
				}
				$title = 'Edit '.$view;
			break;
			case 'resumePrevBrew':
				//special case
				$s = new Brew($db);
				if($s->find('1=1 ORDER BY ts_end DESC LIMIT 0,1')) {
					if($s->load()) {
						$s->fields['ts_end'] = '';
						$s->save();
					}
				}
				header("Location: /?view=home");
				exit;
			break;
			case 'newBrew':
				$s = new Brew($db);
				if($pks) {
					$s->load($pks);
				}
				$s->fields['ts_start'] 	= strtotime($_POST['start_date'].' '.$_POST['start_time']);
				foreach($_POST as $k => $v) {
					if(substr($k,0,6) == 'field_') {
						$s->fields[substr($k,6)] = $v;
					}
				}
				$s->save();
				header("Location: /?view=home");
				exit;
				break;
			case 'endBrew':
				$s = new Brew($db);
				if($s->getCurrent()) {
					$s->fields['ts_end'] = time();
					$s->save();
				}
				header("Location: /?view=home");
				exit;
				break;
			case 'newSample':
				$s = new sample($db);
				if($pks) {
					$s->load($pks);
				}
				$s->fields['ts'] 	= strtotime($_POST['start_date'].' '.$_POST['start_time']);
				foreach($_POST as $k => $v) {
					if(substr($k,0,6) == 'field_') {
						$s->fields[substr($k,6)] = $v;
					}
				}
				$s->save();
				header("Location: /?view=sample");
				exit;
				break;
			default:
				break;
		}
	break;
}


$p->output(($title ? "Beerlogger - $title" : "Beerlogger"), $content);


?>