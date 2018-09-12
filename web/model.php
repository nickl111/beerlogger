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
	private $is_loaded = false;
	
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
				while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
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
				$q .= "$k = '".$v."', ";
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
				$p .= "'$v',";
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
				$this->fields[$p] = $this->db->lastInsertRowid();
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
	 * @param string $sqlwhere A SQL string
	 * @return boolean Number of results or false (beware 0 !== false)
	 */
	function find($sqlwhere='1=1') {
		$q = 'SELECT '.$this->sqlpk().' FROM '.$this->tablename.' WHERE '.$this->db->escapeString($sqlwhere);
		$this->collection = array();
		if($r = $this->query($q)) {
			
			while($row = $r->fetchArray(SQLITE3_ASSOC)) {
				$this->collection[] = $row;
			}
		} else {
			error_log("find : DB error: $q");
			return false;
		}
		return count($this->collection);
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
		$r = $this->query("PRAGMA table_info(".$this->tablename.")");
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
class Brew extends vbc {
	
	protected $tablename = 'brew';
	
	/**
	 * If there's a current brew load it
	 * @return boolean Current brew or no
	 */
	function getCurrent() {
		if($this->find('ts_end = "" AND ts_start <> "" ORDER BY ts_start DESC LIMIT 0,1') > 0) {
			if($this->load()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Get samples associated with this brew
	 * @return array An array of Sample objects
	 */
	function getSamples() {
		$samples = array();
		$sample = new Sample($this->db);
		$sample->find('brew_id = '.$this->fields['id']);
		while($sample->load()) {
			$samples[] = clone $sample;
		}
		return $samples;
	}
	
	/**
	 * get data that happened between this brew's start and end (if any)
	 * @return array An array of Data objects
	 */
	function getData($binLength=3600) {
		$d = new data($this->db);
		return $d->getBins($binLength,$this->fields['ts_start'],$this->fields['ts_end']);
	}
	
	/**
	 * Find peak activity (if any).
	 * Must be confirmed by at least five hours following being lower
	 */
	function getPeakActivity() {
		$b = $this->getData(3600);
		$max_threshold = 5; // number of periods post peak need to confirm the peak
		$max = 0;
		$ret = false;
		foreach($b as $ts => $ary) {
			if($ary['avg_bloop'] > $max) {
				$max = $ary['avg_bloop'];
				$max_ts = $ts;
				$max_counter = 0;
			} else {
				$max_counter++;
			}
			if($max_counter >= $max_threshold) {
				$ret = array();
				$ret['ts'] = $max_ts;
				$ret['avg_bloop'] = $max;
				$max_counter = 0;
			}
		}
		return $ret;
	}
	
	function getActivity($level, $direction) {
		
		if($direction != 'up' && $direction != 'down') {
			error_log('Brew : getActivity: direction must be up or down');
			return false;
		}
		$b = $this->getData(3600);
		$hit_threshold = 5; // number of periods post peak need to confirm the level
		
		$peak = $this->getPeakActivity();
		
		foreach($b as $ts => $ary) {
			if($direction == 'down') {
				if($ts < $peak['ts']) {
					continue;
				}
			} else {
				if($ts > $peak['ts']) {
					break;
				}
 			}
			// ok if we're going up we should only see ups and down only downs here
			if($direction == 'down') {
				$hit_counter = 0;
				if($ary['avg_bloop'] < $level) {
					if($hit_counter == 0) {
						$hit = $ary['avg_bloop'];
						$hit_ts = $ts;
					}
					$hit_counter++;
				} else {
					$hit_counter = 0;
				}
				if($hit_counter >= $hit_threshold) {
					break;
				}
				
			} else {
				$hit_counter = 0;
				if($ary['avg_bloop'] > $level) {
					if($hit_counter == 0) {
						$hit = $ary['avg_bloop'];
						$hit_ts = $ts;
					}
					$hit_counter++;
				} else {
					$hit_counter = 0;
				}
				if($hit_counter >= $hit_threshold) {
					break;
				}
			}
		}
		
		return array('ts' => $hit_ts, 'avg_bloop' => $hit);
		
	}
	
	/**
	 * Get recipe associated with this brew
	 * @return array A recipe object (or false)
	 */
	function getRecipe() {
		$r = new Recipe($this->db);

		if(!$r->load($this->fields['recipe_id'])) {
			return false;
		}
		return $r;
	}
	
	function getHash() {
		return md5($this->fields['id']."|".$this->fields['ts_start']."|".$this->fields['recipe_id']);
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
		return $this->getBrewName()." ".date('jS F Y H:i',$this->fields['ts']);
	}
	
	/**
	 * Get the name of the Brew this sample was for
	 * @return string The name (or false on fail)
	 */
	function getBrewName() {
		$s = new brew($this->db);
		if($s->load($this->fields['brew_id'])){
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
 * This is a yeast type
 * @package beerlogger
 */
class yeast extends vbc {
	protected $tablename = 'yeast';
}

/**
 * This is data that is archived into bins
 * @package beerlogger
 */
class archive extends vbc {
	protected $tablename = 'archive';
}

/**
 * Notes/events
 * @package beerlogger
 */
class note extends vbc {
	protected $tablename = 'note';
}

/**
 * This is data from the logger.
 * @package beerlogger
 */
class data extends vbc {
	protected $tablename = 'data';
	
	/**
	 * Calculate the current data values (an average of the last few anyway)
	 */
	function getCurrent(){
		return $this->getBins(600, time()-600);
	}
	
	/**
	 * @param int $binLength Bin size in seconds (min 120)
	 * @param int $start Timestamp of start time.
	 * @param int $end Timestamp of end time. Default is now.
	 * @return array An array of arrays of binned data : $bin_start => [ 'b_temp' => $beer_temp, 'a_temp' => $ambient_temp, 'avg_bloop' => $average_bloop_rate/min ]
	 */
	function getBins($binLength, $start, $end=false){
		if($binLength < 120) {
			error_log('Data : getBins : Bin Length cannot be less than 120 seconds');
			return false;
		}
		
		$ts_start = $start;
		if(!$end) {
			$end = time();
		}

		$bins = array();
		
		// Check the archive first
		$archive = new archive($this->db);
		if($archive->find("ts >= $ts_start AND ts < $end AND binLength = $binLength ORDER BY ts ASC")) {
			while($archive->load()) {
				$bins[$archive->fields['ts']] = array('b_temp' 		=> $archive->fields['beer_temp'],
													  'a_temp' 		=> $archive->fields['amb_temp'],
													  'avg_bloop' 	=> $archive->fields['bloops']);
				$ts_start = $archive->fields['ts'] + $binLength;
			}
		}
		unset($archive);
	
		if($this->find("ts > $ts_start AND ts < $end ORDER BY ts ASC")) {
			$bin_start 		= $ts_start;
			$actual_steps 	= 0;
			$b_temp_tot 	= 0;
			$a_temp_tot 	= 0;
			$bloop_tot 		= 0;
			$bloop_gap 		= 0;
			$c_bloop 		= false;

			while($this->load()) {
				if($this->fields['ts'] >= $bin_start + $binLength) {
					// finished bin.
					if($actual_steps > 0) {
						$b_temp 	= round($b_temp_tot / $actual_steps, 1);
						$a_temp 	= round($a_temp_tot / $actual_steps, 1);
						if($actual_steps > 1) {
							$avg_bloop = round($bloop_tot / ($actual_steps-1), 2);
						} else {
							$avg_bloop = $bloop_tot;
						}
					}
					
					$bins[$bin_start] = array('b_temp' 		=> $b_temp,
											  'a_temp' 		=> $a_temp,
											  'avg_bloop' 	=> $avg_bloop);
					
					// archive this so we never need do it again
					if($a = new archive($this->db)) {
						$a->fields['ts'] 		= $bin_start;
						$a->fields['binLength'] = $binLength;
						$a->fields['beer_temp'] = $b_temp;
						$a->fields['amb_temp'] 	= $a_temp;
						$a->fields['bloops'] 	= $avg_bloop;
						$a->save();
					}
					
					$actual_steps 	= 0;
					$bin_start		+= $binLength;
					$b_temp_tot 	= 0;
					$a_temp_tot 	= 0;
					$bloop_tot 		= 0;
					$bloop_gap 		= 0;
					$c_bloop 		= false;
				}

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

			// last unfinshed bin. Should not be archived
			if($actual_steps > 0) {
				$b_temp 	= ($actual_steps > 0 ? round($b_temp_tot / $actual_steps, 1) : 0 );
				$a_temp 	= ($actual_steps > 0 ? round($a_temp_tot / $actual_steps, 1) : 0 );
				$avg_bloop 	= ($actual_steps > 1 ? round($bloop_tot / ($actual_steps-1), 2) : 0);
				$bins[$bin_start] = array('b_temp' 		=> $b_temp,
										  'a_temp' 		=> $a_temp,
										  'avg_bloop' 	=> $avg_bloop);
				
			}
		}
		return $bins;
	}
}


?>