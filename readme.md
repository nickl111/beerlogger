** V2 of the hardware is on it's way! (V1 is a little unreliable) **

# Beer Logger for Braubonnet 

Beer logger is a simple linux service that logs temperature and airlock bloops from my Braubonnet (a bonnet for a Pi zero). As we are running on a Pi however we can also run a web server and make the results available to the world (though router config and system capacity for this I'm afraid is your problem). It is designed to fit on the large Speidels style airlock.

It was designed to stop me having to go out to the garage to see what was happening all the time.

![Graph](https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/Screen%20Shot%202018-09-03%20at%2012.41.47.png "Web Interface Graph")

## Installation
Install it on a fresh copy of Raspbian on a Pi Zero W. Make sure you get wifi working reliably first and raspbian updated. Then
```
git clone https://github.com/nickl111/beerlogger.git
cd beerlogger
bash install.sh install
```

Other Pi versions probably work and other Debians probably work too but I've not tested. Other linuxes that use systemd may also work except for the apache set up.

Should you decide you don't want it `bash install.sh uninstall` should remove it cleanly but leave the data in /usr/share/beerlog.

## Configuration
### Software
The only software configuration that is required is to select the correct temperature sensors. Unfortunately it is impossible from the outside to determine which sensor is which so trial and error is the only way... You will be asked to select during installation and half of you will need to select again when it turns out you guessed incorrectly. By default the selection script is in `/usr/local/beerlog/selectTemp.sh`, just run this to reselect.

Obviously you can set about altering apache configs, gpio pins, and whatever you want to customise it.

### Hardware
Stick the pi & board firmly to the airlock. You probably want a case of some kind to stop it getting wet if the airlock bubbles over (as it's wont to do with starsan in it). I've yet to find an ideal case but I'm settling on quarter strength starsan in the airlock and zip-tying the board to it (i'd prefer a less wasteful reusable method but I'm yet to find it).

Once fitted you need to tune the trimmer to detect bloops correctly. The correct setting is going to depend on your situation.
If you turn it back and forth you will find a zone where the orange LED flashes rapidly. You need to move it to just below this zone and far enough so it's not randomly triggering but close enough that the tiny vibration from the airlock blooping does trigger it. This is fiddly but possible and needs to be done while some real fermenting is happening. You may find it needs tuning a bit more when the bloops get down to one every 5 minutes or so as they produce less vibration. To help with this a bit I weighted my airlock lid with some large washers, approx 30g worth.

## Usage
Now you should now have a service on your pi logging constantly to a sqlite database and producing pretty graphs. It is pretty much fire and forget, but don't forget your data is on the pi, not in the cloud. If you reclaim the pi for something else and want to keep the data you will need to move it (/usr/share/beerlog by default).
### LEDs
After a little dance on start up the green led should be on when the service is running (it's enabled on boot by default so this should be always and systemd should recover it if something unexpected happens). It will unflash every minute when a temperature reading is taken.
The orange LED flashes whenever a bloop is detected. The red LED will only come on if an error is detected. Your first (and hopefully only) step in troubleshooting this is to turn it off and then on again. Restarting the service/losing power will not affect the data other than missing a few readings.

### Web Interface
It's basic at the moment but usable and it should be largely self explanatory. Go to http://[yourpiip]:8336/ and browse around. "Monitor" shows you constant monitoring whereas "Brew" will only show you what has happened in a given brew. Consider this a constant WIP...

## Implementation Details
The bloop counter is a simple incrementer and will reset to 0 every time the service restarts. If you're reading the output from the db directly for some reason you need to look at the difference between readings rather than the absolute value (and obviously extrapolate when the reading is less than the previous one).

There is a rate limit on the counter that will stop it counting more than 2 per second. You can change this in the config file if you think this is too low. This was introduced to limit the data pollution from accidental knocks and the like.

## Braubonnet
This is the DIY part! Here's the circuit diagram:
![Schematic](https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/schematic.png "Braubonnet Schematic")

I've substituted some parts that were missing or broken in Eagle so don't pay attention to the names on the diagram. The actual part list is:

| Name | Part | Value | 
| --- | --- | --- |
| Vibro | Minisense 100 Horizontal | |
| IC1 | LM293 Comparator | |
| IC2 & IC3 | DS18B20 | |
| D1 & D2 | Zener Diode | 3.9V |
| R1 | Resistor | 100M Ohm |
| R2 | 25 Turn Trimmer | 10k Ohm |
| R3, R4, R5 | Resistor | 560 Ohm |
| R6 | Resistor | 4.7k Ohm |
| R7 | Resistor | 680 Ohm |
| LED1, 2, 3 | 3mm LED | |
| C1 | Capacitor | 0.1uF |
| C2 | Capacitor | 22pF |


The temp sensors are on the end of long wires and covered in heatshrink.

In physically building it I used an Adafruit Pi Zero Bonnet as the base so that it fitted directly on top of the Pi (and it was important that it was small enough to fit on the airlock).

<img src="https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/BB Photo 1.jpg" width="400"> <img src="https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/BB Photo 2.jpg" width="400"> 

Note that originally I had the ambient temp sensor directly on the board but this picked up too much heat from the cpu so I moved it so both were on the remote wire, one just shorter than the other. 

I have an Inkbird temperature controller actually controlling the heating and cooling (a brewbelt on the fermenter and the whole thing is in a fridge). In principle it wouldn't be much work to get the Pi to control the heating via a relay but in practice it's simpler and safer and as cheap to just to run the Inkbird.

Should you be comparing this to a previous version note that I realised the comparator needs a 5V supply to work properly (probably the source of my earlier problems) and I have also added a pull up resistor and a decuopling capacitor on its output (as recommended in the datasheet I finally read properly). I've also used a decent sealed trimmer that allows for much more sensitive tuning.

## Acknowledgments
- I borrowed part of the circuit design for the piezo sensors from here: https://scienceprog.com/thoughts-on-interfacing-piezo-vibration-sensor/
- I borrowed the idea of counting airlock bloops from Speidels themselves. Their [GÄRSPUNDmobil](https://www.speidels-braumeister.de/en/braumeister/gaerspundmobil-and-gaermeister-control.html) is basically the same thing (though note I've not actually seen one so I don't know how it actually works internally. And I would have just bought one if they were €50 instead of €150). 

## Known Issues
- Producing RRD graphs every minute is probably overkill
- The web interface is basic, buggy and probably hideously insecure. Don't expose it to the world. Feel free to improve.
- I used a smaller resistor across the piezo than recommended (320M) because the larger one was physically too large to fit on the board. This amplifies the signal from the peizo less but doesn't really matter to us.
- Airlock activity doesn't completely represent fermentation activity. I know. I am not trying to estimate SG because it's a) hard and b) pointless. It will take as long as it takes. We are monitoring only.
- Electricity and beer do not mix. Be careful.
