<?php

/**
 * This is quick and dirty. I will put something better in place in time.
 * Yes, I know it's wildy inefficient, insecure, inelegant and unscalable. Don't make it public.
 * I need to write a sqlite backend for mana
 *
 * actions: CRUD - create, read, update ,delete
 *
 * screens:
 * 		Index: Show the current session if exists
 * 				-> previous sessions
 * 				-> Start a new session
 * 				-> create new sample
 * 		
 */

class vbc {
	private $db;
	private $tablename;
	private $collection;
	private $pk = 'id';
	public $fields = array();
	
	private $SQL_DB = "/usr/share/beerlog/beerlog.db";  // TODO: get this from the config file

	function __construct($db=false) {
		if(!$db) {
			$this->db = new SQLite3($SQL_DB);
		}
	}
	
	function load($id=false) {
		if($id) {
			$q = "SELECT * FROM ".$this->tablename." WHERE ".$this->pk." = $id";
			$results = $this->db->query($q);
			while ($row = $results->fetchArray()) {
				foreach($row as $k => $v) {
					$this->fields[$k] = $v;
				}
			}
		} else {
			// load the next one in the collection;
			if($this->collection) {
				$this->load(current($this->collection));
				next($this->collection);
			} else {
				return false;
			}
		}
	}
	
	function save() {
		if ($this->fields[$this->pk]) {
			// is an object instantiated
			$q = 'UPDATE '.$this->tablename.' SET ';
			foreach($this->fields as $k => $v) {
				$q .= "$k = '".$v."'";
			}
			$q .= "WHERE ".$this->pk." = ".$this->fields[$this->pk];
		} else {
			// new object
			$q = 'INSERT INTO '.$this->tablename.' VALUES (';
			foreach($this->fields as $k => $v) {
				$q .= "$k,";
				$p .= "'$v',";
			}
			$q .= substr($q,0,-1).') VALUES ('.substr($p,0,-1).')';
		}
		$this->db->query($q);
	}
	
	function destroy() {
		if ($this->fields['id']) {
			$q = 'DELETE FROM '.$this->tablename.' WHERE id ='.$this->fields['id'];
			$this->db->query($q);
		} else {
			return false;
		}
	}
	
	function find($sqlwhere='') {
		$q = 'SELECT id FROM '.$this->tablename.' WHERE '.$this->db->escapeString($sqlwhere);
		$r = $this->db->query($q);
		while($row = $r->fetchArray()) {
			$this->collection[] = $row[$this->pk];
		}
	}
}

class Session extends vbc {
	private $tablename = 'session';
	
	function getCurrent() {
		//  see if there is a current session
		$results = $this->db->query("SELECT id FROM session WHERE end = '' ORDER BY start DESC LIMIT 0,1");
		if($row = $results->fetchArray()) {
			$this->load($row['id']);
			return true;
		}
		return false;
	}
	
	function getSamples() {
		$samples = array();
		$sample = new Sample();
		$sample->find('session_id = '.$this->fields['id']);
		while($sample->load()) {
			$samples[] = $sample;
		}
		return $samples;
	}
	
	function getData() {
		$datas = array();
		$data = new Data();
		$data->find('ts >= '.$this->fields['start'].' AND $ts <= '.$this->fields['end']);
		while($data->load()) {
			$datas[] = $data;
		}
		return $datas;
	}
}

class Sample extends vbc {
	private $tablename = 'sample';
}

class Data extends vbc {
	private $tablename = 'date';
	private $pk = 'ts';
}


/*********/
$SQL_DB = "/usr/share/beerlog/beerlog.db";
$db = new SQLite3($SQL_DB);

$s = new Session($db);
if($s->getCurrent()) {
	// We have an existing session.
	// - Show details
	// - Show End Session Button
	// - Show compare with previous sessions on this recipe
	// - Show "Log sample" button
	print_r($s);
	
} else {
	// No current session
	// - Show "Start New Session" button
	// - Show "Resume previous Session" button (oopsy)
	// - List previous sessions
	// - List Recipes
	print 'No existing session';
}


?>