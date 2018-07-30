# Beer Logger for Braubot

## !THIS IS A WORK IN PROGRESS!

Beer logger is a simple linux service that logs temperature and airlock bloops from my Braubot (a raspbian running pi zero w with some gidgets attached). As we are running on a pi however we can also run a web server and make the results available to the world (though router config and system capacity for this I'm afraid is your problem).

## Configuration
The only basic configuration that is required is to put your correct 1-wire temperature sensor addresses in config.sh (`ls -l /sys/bus/w1/devices/`)

## Installation
`bash install.sh install`

Now you have it logging constantly to file /usr/share/beerlog/templog

Cheers!

## Implementation Details
The bloop counter is a simple incrementer and will reset to 0 every time the service restarts. If you're reading the output from outlog directly you need to look at the difference between readings rather than the absolute value (and obviously extrapolate when the reading is less than the previous one). 



Wait, what? You want to *see* the results? And store them in sessions? Ok, well in that case you need to install rrdtool, apache2, sqlite3 and php.

