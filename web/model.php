<?php
 /**
  * A Very Basic Class to handle CRUD+ functions on an object
  */
class vbc {
	public $db;
	protected $tablename;
	protected $collection;
	protected $tableinfo = array();
	public $fields = array();
	public $pk = array();
	
	protected $SQL_DB = "/usr/share/beerlog/db/beerlog.db";  // TODO: get this from the config file

	function __construct($db=false) {
		if(!$db) {
			$this->db = new SQLite3($this->SQL_DB);
		} else {
			$this->db = $db;
		}
		$this->setTableInfo();
	}
	
	/**
	 * Instantiate an object.
	 * @param array $id Array of primary key values. Non-arrays will be turned into single value arrays.
	 * @return boolean Success or no
	 */
	function load($id=false) {
		if($id) {
			if(!is_array($id)) {
				$id = array($id);
			}
			
			$q = "SELECT * FROM ".$this->tablename." WHERE ".$this->sqlpk($id);
			if($results = $this->db->query($q)) {
				while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
					foreach($row as $k => $v) {
						$this->fields[$k] = $v;
					}
				}
			} else {
				error_log("Load : Query: $q failed");
				return false;
			}
		} else {
			// load the next one in the collection;
			if($this->collection) {
				$mine = current($this->collection);
				$this->load($mine);
				if(!next($this->collection)) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Save this object, creating it if necessary
	 * @return boolean Success or no
	 */
	function save() {
		if ($this->getPKValues()) {
			// is an object instantiated
			$q = 'UPDATE '.$this->tablename.' SET ';
			foreach($this->fields as $k => $v) {
				if(in_array($k,$this->pk)){ continue; }
				$q .= "$k = '".$v."', ";
			}
			$q = substr($q,0,-2);
			$q .= "WHERE ".$this->sqlpk($this->getPKValues());
		} else {
			// new object
			$q = 'INSERT INTO '.$this->tablename.' (';
			foreach($this->fields as $k => $v) {
				if(in_array($k,$this->pk)){ continue; }
				$c .= "$k,";
				$p .= "'$v',";
			}
			$q .= substr($c,0,-1).') VALUES ('.substr($p,0,-1).')';
			error_log("Save : query worked: $q");
		}
		if(!$this->db->query($q)){
			error_log("Save : query failed: $q");
			return false;
		}
		return true;
	}
	
	/**
	 * Delete this object from the DB
	 * @return boolean Success or no
	 */ 
	function destroy() {
		// only do this if we have pk values
		if ($vs = $this->getPKValues()) {
			$q = 'DELETE FROM '.$this->tablename.' WHERE '.$this->sqlpk($vs);
			$this->db->query($q);
		} else {
			return false;
		}
	}
	
	/**
	 * Create a collection of objects based on a sql query
	 * @param string $sqlwhere A SQL string
	 * @return boolean Success or no
	 */
	function find($sqlwhere='1=1') {
		$q = 'SELECT '.$this->sqlpk().' FROM '.$this->tablename.' WHERE '.$this->db->escapeString($sqlwhere);
		if($r = $this->db->query($q)) {
			while($row = $r->fetchArray(SQLITE3_ASSOC)) {
				$this->collection[] = $row;
			}
		} else {
			error_log("find : DB error: $q");
			return false;
		}
		return true;
	}
	
	/**
	 * Get the values of the primary key(s) for this object
	 * @return array An array of values or false
	 */
	function getPKValues() {
		$r = array();
		foreach($this->pk as $key) {
			if(!$this->fields[$key] ) {
				// if any value is false then bin out. Primary keys can't evalute to false
				return false;
			}
			$r[] = $this->fields[$key];
		}
		return $r;
	}
	
	/**
	 * Return a SQL string representation of the primary keys
	 * if an array of values is supplied then make this string suitable for WHERE clauses
	 * The order of the value array must obviously match the key order
	 * @param array $v An array of values
	 * @return string A SQL string (or false)
	 **/
	private function sqlpk($v=false) {
		// if the pk is not set we can't do owt
		if(!$this->pk) {
			error_log("Error: sqlpk: pk not set");
			return false;
		}
		// if we're passed some values but the not the same number of values as the $key has fields then we can't do owt
		if($v && count($v) != count($this->pk)) {
			error_log("Error: sqlpk: value count mismatch: ".count($v)." doesn't match ".count($this->pk));
			return false;
		}
		$s = '';
		
		if($v) {
			$count = 0;
			foreach($this->pk as $k) {
				$me = each($v);
				$s .= "$k = '".$me['value']."' AND ";
			}
			$s = substr($s,0,-5);
		} else {
			foreach($this->pk as $k) {
				$s .= "$k,";
			}
			$s = substr($s,0,-1);
		}
		
		return $s;
	}
	
	/**
	 * Set the table info
	 * @return boolean Always true
	 */
	private function setTableInfo($force=false) {
		if($this->tableinfo && !$force) {
			return true;
		}
		$r = $this->db->query("PRAGMA table_info(".$this->tablename.")");
		while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
			 $this->tableinfo[] = $row;
		}
		
		foreach($this->tableinfo as $rownum => $v) {
			if($v['pk'] == 1) {
				$this->pk[] = $v['name'];
			}
			$this->fields[$v['name']] = null ;
		}
		return true;
	}
}

class Session extends vbc {
	
	protected $tablename = 'session';
	
	/**
	 * If there's a current session load it
	 */
	function getCurrent() {
		if($this->find("ts_end IS NULL AND ts_start IS NOT NULL ORDER BY ts_start DESC LIMIT 0,1")) {
			$this->load();
			return true;
		}
		return false;
	}
	
	/**
	 * Get samples associated with this session
	 */
	function getSamples() {
		$samples = array();
		$sample = new Sample();
		$sample->find('session_id = '.$this->fields['id']);
		while($sample->load()) {
			$samples[] = $sample;
		}
		return $samples;
	}
	
	/**
	 * get data that happened between this session's start and end (if any)
	 */
	function getData() {
		$datas = array();
		$data = new Data();
		if($this->fields['end'] == 'NULL') { // current session
			$data->find('ts >= '.$this->fields['start']);
		} else {
			$data->find('ts >= '.$this->fields['start'].' AND $ts <= '.$this->fields['end']);
		}
		while($data->load()) {
			$datas[] = $data;
		}
		return $datas;
	}

}

class sample extends vbc {
	protected $tablename = 'sample';
}

class recipe extends vbc {
	protected $tablename = 'recipe';
}

class data extends vbc {
	protected $tablename = 'data';
}

/**
 *
 */
class Page {
	
	public $str;
	protected $view;
	
	function __construct() {
		$this->view = $_GET['view'];
	}
	
	function output($title, $content) {
		print $this->header($title);
		print $this->menu();
		print '<section>'.$content.'</section>';
		print $this->footer();
	}
	
	function header($title=false) {
		if(!$title) {
			$title = 'Beerlogger';
		}
		include('templates/header.php');
	}
	
	function footer() {
		include('templates/footer.php');
	}
	
	function editObject($o) {
		$str = '<h3>'.ucfirst(get_class($o)).'</h3><form method="POST">';
		foreach($o->fields as $name => $value) {
			if(in_array($name,$o->pk)){ continue; }
			$str .= $this->field($name, $value);
		}
		$str .= '<a class="button" href="?view='.get_class($o).'">Cancel</a>';
		foreach($o->pk as $k) {
			$str .= '<input type="hidden" name="field_'.$k.'" value="'.$o->fields[$k].'">';
		}
		$str .= '<input type="submit" class="button is-primary is-pulled-right" value="Save"><input type="hidden" name="do" value="save"></form>';
		return $str;
	}
	
	function field($name, $value='') {
		$str = '<div class="field is-size-6"><label class="label" for="'.$name.'-input">'.$name.'</label>';
		$str .= '<div class="control"><input class="input" name="field_'.$name.'" id="'.$name.'-input" type="text" value="'.$value.'"></div></div>'."\n";
		return $str;
	}
	
	function listItem($view, $id, $value='') {
		if(!$value) { $value = implode(', ',$id) ; }
		$str = '<div><a href="?view='.$view.'&amp;do=edit&amp;pks='.implode(',',$id).'">'.$value.'</a><a href="?do=delete&amp;view='.$view.'&amp;pks='.implode(',',$id).'" class="button is-danger is-small">X</a></div>';
		return $str;
	}
	
	function menu() {
		$menuList = array('home' 		=> 'Home',
						  'monitor' 	=> 'Monitor',
						  'session' 	=> 'Sessions',
						  'recipe' 		=> 'Recipes',
						  'sample' 		=> 'Samples',
						  'data' 		=> 'Data'
						);
		$str = '<div class="column is-one-quarter sidebar" style="margin: 0;"><aside class="menu"><ul class="menu-list">';
		foreach($menuList as $view => $name) {
			$str .= '<li><a '.($this->view == $view ? 'class="is-active" ' : '').'href="?view='.$view.'">'.$name."</a></li>\n";
		}
		$str .= '</ul></aside></div>';
		return $str;
	}
	
	function showMonitor($graph) {
		ob_start();
		include('templates/monitor.php');
		$output = ob_get_clean();
		return $output;
	}
	
	function newButton($view) {
		$str = '<a class="button is-primary" href="?do=edit&amp;view='.$view.'">New</a>';
		return $str;
	}
}
?>