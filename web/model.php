<?php

require_once('vbc.php');

/**
 * This is a Fermenting/Brewing session
 * @package beerlogger
 */
class brew extends vbc {
	
	protected $tablename = 'brew';
	
	/**
	 * If there's a current brew load it
	 * @return array Array of current brew objects. Or false.
	 */
	function findActive() {
		return $this->find('ts_end IS NULL AND ts_start <= NOW() ORDER BY ts_start DESC');
	}
	
	/**
	 * Get most recent data for this brew
	 */
	function getCurrentData() {
		$d = new data($this->db, $this->fields['color']);
		
		if(!$this->fields['ts_end']) {
			return end($d->getBins(600, $this->fields['ts_start'], time()-600));
		} else {
			return end($d->getBins(600, $this->fields['ts_start'], $this->fields['ts_end']-600));
		}
	}
	
	/**
	 * get data that happened between this brew's start and end (if any)
	 * @return array An array of data
	 */
	function getData($binLength=600) {
		$d = new data($this->db, $this->fields['color']);
		return $d->getBins($binLength,$this->fields['ts_start'],$this->fields['ts_end']);
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

	/**
	 * Calculate ABV based on the formula
	 * ABV =(76.08 * (og-fg) / (1.775-og)) * (fg / 0.794)
	 * @param decimal Gravity to use or current grav if 0
	 * @return float ABV as a percentage. False on error.
	 */
	function getABV($gravity=0){
		if(!$this->fields['g_orig']) {
			return false;
		}
		
		$og = $this->fields['g_orig'];
		
		if(!$gravity) {
			$ld = $this->getCurrentData();
			$fg = $ld['sg'];
		} else {
			$fg = $gravity;
		}
		
		$abv = (76.08 * ($og-$fg) / (1.775-$og)) * ($fg / 0.794);
		return $abv;
	}
	
	/**
	 * Calculate apparent attenuation from the formula
	 * (OG - FG) / OG
	 * @param boolean $actual Return anticipated or actual
	 * @return float Attenuation as a percentage. False on error.
	 */
	function getAttenuation($actual=true) {
		if(!$this->fields['g_orig']) {
			return false;
		}
		
		$og = $this->fields['g_orig'];
		
		if($actual) {
			$ld = $this->getCurrentData();
			$fg = $ld['sg'];
		} else {
			if(!$this->fields['g_final']) {
				return false;
			}
			$fg = $this->fields['g_final'];
		}
		
		return (($og - $fg) / ($og - 1)) * 100;
	}
	
	/**
	 * Get any associated schedule
	 */
	function getSchedule() {
		if($this->fields['schedule_id']) {
			$s = new schedule($this->fields['schedule_id']);
			return $s;
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
 * This is a fermentation schedule/profile
 * @package beerlogger
 * @var $steps array Array of scheduleStep Objects
 */
class schedule extends vbc {
	protected $tablename = 'schedule';
	public $steps = array();
	
	/**
	 * Overload load method so we can load up steps on load. (load)
	 */
	function load($id=false){
		if(parent::load($id)) {
			// load children;
			if($this->fields['id']) {
				$ss = new scheduleStep($this->db);
				if($ss->find('schedule_id = '.$this->fields['id']." ORDER BY sortOrder") > 0) {
					while($ss->load()) {
						$this->steps[] = clone $ss;
					}
				}
			}
			return true;
		} else {
			return false;
		}
	}
}

/**
 * This is a step/trigger in the schedule
 *@package beerlogger
 */
class scheduleStep extends vbc {
	protected $tablename = 'schedule_step';
}

/**
 * This is data that is archived into bins
 * @package beerlogger
 */
class archive extends vbc {
	protected $tablename = 'archive';
}

/**
 * Notes/notifications
 * @package beerlogger
 */
class note extends vbc {
	protected $tablename = 'note';
	
	function sendEmail($recipient, $subject="Beerlogger Alert") {
		return mail($recipient, $subject, $this->fields['content']);
	}
}

/**
 * This is data from the logger.
 * @package beerlogger
 */
class data extends vbc {
	protected $tablename = 'data';
	protected $color;
	
	function __construct($db,$color) {
		$this->color = $color;
		parent::__construct($db);
	}
	
	/**
	 * @param int $binLength Bin size in seconds (min 120)
	 * @param int $start Timestamp of start time.
	 * @param int $end Timestamp of end time. Default is now.
	 * @return array An array of arrays of binned data : $bin_start => [ 'b_temp' => $beer_temp, 'sg' => $sg, 'sg_sd' => $sg_std-dev ]
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
		if($archive->find("color = ".$this->color." AND ts >= $ts_start AND ts < $end AND binLength = $binLength ORDER BY ts ASC")) {
			while($archive->iterate()) {
				$bins[$archive->fields['ts']] = array('b_temp' 		=> number_format(($archive->fields['beer_temp']-32) * (5/9),2),
													  'sg' 			=> number_format($archive->fields['sg']/1000,4),
													  'sg_sd'		=> number_format($archive->fields['sg_sd'],2),
													  'datacount'	=> $archive->fields['datacount']
													 );
				$ts_start = $archive->fields['ts'] + $binLength;
			}
		}
		unset($archive);
	
		if($this->find("color = ".$this->color." AND ts > $ts_start AND ts < $end ORDER BY ts ASC")) {
			$bin_start 		= $ts_start;
			$actual_steps 	= 0;
			$b_temp_tot 	= 0;
			$sg_tot 		= 0;

			while($this->load()) {
				$b_temp_tot += $this->fields['beer_temp'];
				$sg_tot 	+= $this->fields['sg'];
				$actual_steps++;
			}
			
			$b_temp 	= ($actual_steps > 0 ? round($b_temp_tot / $actual_steps, 1) : 0 );
			$sg 		= ($actual_steps > 0 ? round($sg_tot / $actual_steps, 1) : 0 );
			
			$bins[$bin_start] = array('b_temp' 			=> number_format(($b_temp-32) * (5/9),2),
									  'sg' 			=> number_format($sg/1000,4),
									  'sg_sd'		=> 0,
									  'datacount'	=> $actual_steps
									);
		}
		return $bins;
	}
}

/**
 * This is a tank in which brew can be stored
 * @package beerlogger
 */
class tank extends vbc {
	protected $tablename = 'tank';
	
	/**
	 * Get the current temperature
	 */
	function getTemp(){
		return false;
	}
	
	/**
	 * Set the temperature of this tank
	 */
	function setTemp($targetTemp){
		return false;
	}
}

/**
 * Temperature Controller.
 * This will need to know which GPIO pin is linked to which channel.
 * Either we assume there are only two and give them a db field each,
 * allow more and build an extra table or force this class to be overloaded with specifics?
 * @package beerlogger
 */
class controller extends vbc {
	protected $tablename = 'controller';
	
	function __construct() {
		# load the data from controllerData field
		$this->loadData();
	}
	
	/**
	 * load the data from controllerData field
	 */
	function loadData() {
		
	}
	
	/**
	 * Should this be here?
	 */
	function setTemp($targetTemp) {
		;
	}
	
	/**
	 *  
	 */
	function getTemp() {
		;
	}
	
	/**
	 * Turn on the heat
	 * (implies turning off the cold)
	 */
	function heat() {
		;
	}
	
	/**
	 * Turn on the cold
	 * (implies turning off the heat)
	 */
	function cool() {
		;
	}
}

class TwoChannelController extends controller {
	
	/**
	 * Do the specified action to the specified channel
	 * @param int $channel Which channel to controll
	 * @param int $action What to do (0=off, 1=on)
	 */
	function setChannelStatus($channel,$action) {
		;
	}
	
	/**
	 * Get the current status of the channel. We can't physically poll the relay but we can check if the the GPIO pin is high or low
	 * @param int $channel Which channel to poll
	 */
	function getChannelStatus($channel) {
		;
	}
}

?>