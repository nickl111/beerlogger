<?php
require_once('model.php');
require_once('db.php');

// find active brews
$b = new brew();
if($b->findActive() > 0) {
	while($b->load()) {
		// check associated schedules for triggers
		
		if($s = new $b->getSchedule()) {
			$actionTaken = false;
			foreach($s->steps as $step) {
				switch($step->fields['stepTrigger']) {
					case 'gravity':
						// get most recent archived gravity data
						// see if we're lower (gravity can only go down)
						// take some action
					break;
					case 'time':
						// decipher time string
						// see if current time is more than trigger time (time only goes up)
						// take some action
					break;
					case 'attenuation':
						// get most recent archived gravity data
						// work out attenuation
						// see if we're higher (attenuation can only go up)
						// take some action
					break;
					default:
						// unrecognized trigger!
						// log an error
						
				}
				
				// break out of step loop (we don't need to do more than one step per execution)
				if($actionTaken) {
					break;
				}
			}
		}
	}
}



?>