<?php

 /**
  * A Very Basic Class to handle CRUD+ functions on an object
  * @var $db a database handle
  */
class vbc {
	public $db;
	protected $tablename;
	protected $collection;
	protected $results;
	protected $tableinfo = array();
	public $fields = array();
	public $pk = array();
	private $is_loaded = false;
	protected $iterator = 0;
	
	/**
	 * The constructor
	 * @param $db A usable database handle
	 * @return boolean true unless no DB handle supplied
	 */
	function __construct($db=false) {
		if(!$db) {
			error_log("VBC Constructor : No DB handle supplied");
			return false;
		} else {
			$this->db = $db;
		}
		$this->setTableInfo();
		return true;
	}
	
	/**
	 * Query abstraction
	 */
	private function query($q) {
		error_log("Query: $q");
		return $this->db->query($q);
	}
	
	/**
	 * Instantiate an object.
	 * @param array $id Array of primary key values. Non-arrays will be turned into single value arrays.
	 * @return boolean Success or no
	 */
	function load($id=false) {
		if($id !==  false) {
			if(!is_array($id)) {
				$id = array($id);
			}
			
			$q = "SELECT * FROM ".$this->tablename." WHERE ".$this->sqlpk($id);
			
			if($results = $this->query($q)) {
				while ($row = $results->fetch_assoc()) {
					foreach($row as $k => $v) {
						$this->fields[$k] = $v;
					}
				}
				$this->is_loaded = true;
				return true;
			} else {
				error_log("Load : Query: $q failed");
			}
		} else {
			// load the next one in the collection;
			if($this->collection) {
				if($mine = current($this->collection)) {
					$this->load($mine);
					next($this->collection);
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Save this object, creating it if necessary
	 * @return boolean Success or no
	 */
	function save() {
		if ($this->is_loaded) {
			// is an object instantiated
			$q = 'UPDATE '.$this->tablename.' SET ';
			foreach($this->fields as $k => $v) {
				if(in_array($k,$this->pk)){ continue; }
				if($v == '') {
					$q .= "$k = NULL, ";
				} else {
					$q .= "$k = '".$v."', ";
				}
			}
			$q = substr($q,0,-2);
			$q .= " WHERE ".$this->sqlpk($this->getPKValues());
		} else {
			// new object
			$c = $p = '';
			$q = 'INSERT INTO '.$this->tablename.' (';
			foreach($this->fields as $k => $v) {
				if(in_array($k,$this->pk)){
					if(!$v) { 
						continue;
					}
				}
				$c .= "$k,";
				if($v == '') {
					$p .= "NULL,";
				} else {
					$p .= "'$v',";
				}
			}
			$q .= substr($c,0,-1).') VALUES ('.substr($p,0,-1).')';
		}
		if(!$this->query($q)){
			error_log("Save : query failed: $q");
			return false;
		}
		// go through values and if the primary key is an autonumber field (there can only be one) set it to the last insert id
		if(count($this->pk) == 1) {
			$p = reset($this->pk);
			if(!$this->fields[$p]) {
				$this->fields[$p] = $this->db->insert_id;
			}
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
			$this->query($q);
			$this->is_loaded = false;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Create a collection of objects based on a sql query
	 * collection is used by load(), results is used by iterate()
	 * @param string $sqlwhere A SQL string
	 * @return boolean Number of results or false (beware 0 !== false)
	 */
	function find($sqlwhere='1=1', $what='*') {
		$q = 'SELECT '.$what.' FROM '.$this->tablename.' WHERE '.$this->db->real_escape_string($sqlwhere);
		$this->collection = array();
		$this->results = array();
		if($r = $this->query($q)) {
			$counter = 0;
			while($row = $r->fetch_assoc()) {
				foreach($this->pk as $k) {
					$myrow[$k] = $row[$k];
				}
				$this->collection[$counter] = $myrow;
				$this->results[$counter++] = $row;
			}
		} else {
			error_log("find : DB error: $q");
			return false;
		}
		
		return count($this->collection);
	}
	
	/**
	 * Iterate over a result set. More efficient than calling load repeatedly
	 * @return boolean True unless at the end of the results
	 */
	function iterate() {
		$colcount = count($this->results);
		if(count($this->results) > 0) {
			
			if($this->iterator == $colcount) {
				return false;
			}

			$row = $this->results[$this->iterator];
			
			foreach($row as $k => $v) {
				$this->fields[$k] = $v;
			}

			$this->is_loaded = true;
			$this->iterator++;
			return true;

		} else {
			return false;
		}
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
	 * @param boolean $force Force recreating if it's already set
	 * @return boolean Always true
	 */
	private function setTableInfo($force=false) {
		if($this->tableinfo && !$force) {
			return true;
		}
		if($r = $this->query("DESCRIBE ".$this->tablename)) {
			while ($row = $r->fetch_assoc()) {
				 $this->tableinfo[$row['Field']] = $row;
			}
		
			foreach($this->tableinfo as $rownum => $v) {
				if($v['Key'] == 'PRI') {
					$this->pk[] = $v['Field'];
				}
				$this->fields[$v['Field']] = null ;
			}
			return true;
		} else {
			error_log("Error: setTableInfo: DESCRIBE query failed. Table ".$this->tablename." probably doesn't exist");
			return false;
		}
	}
	
	/**
	 * Return a sensible string for the name
	 * You should overload this probably
	 * @return string A sensible name
	 */
	function getDisplayName() {
		if($this->fields['name']) {
			return $this->fields['name'];
		} else {
			return implode(', ', $this->getPKValues());
		}
	}
}

?>