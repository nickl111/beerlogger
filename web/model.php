<?php
 /**
  * A Very Basic Class to handle CRUD+ functions on an object
  * @var $db a database handle
  */
class vbc {
	public $db;
	protected $tablename;
	protected $collection;
	protected $tableinfo = array();
	public $fields = array();
	public $pk = array();

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
			if($results = $this->db->query($q)) {
				while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
					foreach($row as $k => $v) {
						$this->fields[$k] = $v;
					}
				}
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
	 * @param boolean $force Force recreating if it's already set
	 * @return boolean Always true
	 */
	private function setTableInfo($force=false) {
		if($this->tableinfo && !$force) {
			return true;
		}
		$r = $this->db->query("PRAGMA table_info(".$this->tablename.")");
		while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
			 $this->tableinfo[$row['name']] = $row;
		}
		
		foreach($this->tableinfo as $rownum => $v) {
			if($v['pk'] == 1) {
				$this->pk[] = $v['name'];
			}
			$this->fields[$v['name']] = null ;
		}
		return true;
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

/***********/

/**
 * This is a Fermenting/Brewing session
 * @package beerlogger
 */
class Session extends vbc {
	
	protected $tablename = 'session';
	
	/**
	 * If there's a current session load it
	 * @return boolean Current session or no
	 */
	function getCurrent() {
		if($this->find('ts_end = "" AND ts_start <> "" ORDER BY ts_start DESC LIMIT 0,1')) {
			if(count($this->collection) > 0) {
				$this->load();
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Get samples associated with this session
	 * @return array An array of Sample objects
	 */
	function getSamples() {
		$samples = array();
		$sample = new Sample($this->db);
		$sample->find('session_id = '.$this->fields['id']);
		while($sample->load()) {
			$samples[] = $sample;
		}
		return $samples;
	}
	
	/**
	 * get data that happened between this session's start and end (if any)
	 * @return array An array of Data objects
	 */
	function getData() {
		$datas = array();
		$data = new Data($this->db);
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

/**
 * This is a beer sample taken
 * @package beerlogger
 */
class sample extends vbc {
	protected $tablename = 'sample';
	
	/**
	 * Return a sensible string for the name
	 * @return string The "name" of this sample
	 */
	function getDisplayName() {
		return $this->getSessionName()." ".date('jS F Y H:i',$this->fields['ts']);
	}
	
	/**
	 * Get the name of the Session this sample was for
	 * @return string The name (or false on fail)
	 */
	function getSessionName() {
		$s = new Session($this->db);
		if($s->load($this->fields['session_id'])){
			return $s->getDisplayName();
		} else {
			return false;
		}
	}
}


/**
 * This is a beer recipe
 * @package beerlogger
 */
class recipe extends vbc {
	protected $tablename = 'recipe';
}


/**
 * This is data from the logger.
 * @package beerlogger
 */
class data extends vbc {
	protected $tablename = 'data';
	
	/**
	 * Calculate the current data values (an average of the last few anyway)
	 * @return array A 3 value array : 'b_temp' => $beer_temp, 'a_temp' => $ambient_temp, 'avg_bloop' => $average_bloop_rate/min
	 */
	function getCurrent(){
		$b_temp_tot = 0;
		$a_temp_tot = 0;
		$bloop_tot = 0;
		$steps = 10;
		$actual_steps = 0; // for the edge case where we have less than 10 readings!
		$bloop_gap = 0;
		
		$c_bloop = false;
		$bloops = array();
		if($this->find('1=1 ORDER BY ts DESC LIMIT 0,'.$steps)) {
			while($this->load()) {
				$actual_steps++;
				$b_temp_tot += $this->fields['beer_temp'];
				$a_temp_tot += $this->fields['amb_temp'];
				$last_bloop = $c_bloop;
				$c_bloop = $this->fields['bloops'];
				
				if($last_bloop !== false) {
					if($c_bloop >= $last_bloop) { // if this isn't true then the counter has reset and have to assume this gap is the same as the previous one.
						$bloop_gap = $c_bloop - $last_bloop;
					}
					
					$bloop_tot += $bloop_gap;
				}
				
			}
		} else {
			return false;
		}
		
		if($steps != $actual_steps) {
			error_log('Data : getCurrent : Warning: Actual data readings were less than requested');
		}
		
		$b_temp 	= round($b_temp_tot / $actual_steps, 1);
		$a_temp 	= round($a_temp_tot / $actual_steps, 1);
		$avg_bloop 	= round($bloop_tot / $actual_steps, 0);
		return array('b_temp' => $b_temp, 'a_temp' => $a_temp, 'avg_bloop' => $avg_bloop);
	}
	
	/**
	 * @param int $binLength Bin size in seconds (min 120)
	 * @param int $start Timestamp of start time.
	 * @param int $end Timestamp of end time. Default is now.
	 * @return array An array of arrays of binned data : 'b_temp' => $beer_temp, 'a_temp' => $ambient_temp, 'avg_bloop' => $average_bloop_rate/min
	 */
	function getBins($binLength, $start, $end=false){
		if($binLength < 120) {
			error_log('Bin Length cannot be less than 120 seconds');
			return false;
		}
		
		$ts_start = $start;
		if(!$end) {
			$end = time();
		}

		$bins = array();
		$thisbin = 0;
		for($bin_start=$ts_start;$bin_start<=$end;$bin_start+=$binLength) {
			$thisbin++;
			$b_temp_tot = 0;
			$a_temp_tot = 0;
			$bloop_tot = 0;
			$actual_steps = 0;
			$bloop_gap = 0;
			
			$c_bloop = false;
			$bloops = array();
			if($this->find("ts >= $bin_start AND ts < ".($bin_start + $binLength)." ORDER BY ts ASC")) {
				$actual_steps = count($this->collection);
				while($this->load()) {
					$b_temp_tot += $this->fields['beer_temp'];
					$a_temp_tot += $this->fields['amb_temp'];
					$last_bloop = $c_bloop;
					$c_bloop = $this->fields['bloops'];
					
					if($last_bloop !== false) {
						if($c_bloop >= $last_bloop) { // if this isn't true then the counter has reset and have to assume this gap is the same as the previous one.
							$bloop_gap = $c_bloop - $last_bloop;
						}
						
						$bloop_tot += $bloop_gap;
					}
				}
			} else {
				error_log("Data : getBins : Data finding query failed");
				return false;
			}
			
			$b_temp 	= round($b_temp_tot / $actual_steps, 1);
			$a_temp 	= round($a_temp_tot / $actual_steps, 1);
			$avg_bloop 	= round($bloop_tot / $actual_steps, 0);
			$bins[] =  array('b_temp' => $b_temp, 'a_temp' => $a_temp, 'avg_bloop' => $avg_bloop);
		}
		return $bins;
	}
}


?>