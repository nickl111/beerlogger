# Beer Logger for Braubonnet (in Alpha)

Beer logger is a simple linux service that logs temperature and airlock bloops from my Braubonnet (a bonnet for a Pi zero). As we are running on a Pi however we can also run a web server and make the results available to the world (though router config and system capacity for this I'm afraid is your problem). It is designed to fit on the large Speidels style airlock.

It was designed to stop me having to go out to the garage to see what was happening all the time.

**Warning! This currently is not stable! The hardware is unreliable! Working on it!** 

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

Obviously you can set about altering apache configs, gpio pins, and whatever you want if you feel like it.

### Hardware
Stick the pi & board firmly to the airlock. I used the bottom half of a pi case so I could still remove the board when required.

Once fitted you need to tune the potentiometer to detect bloops correctly. The correct setting is going to depend on your situation.
If you turn it back and forth you will find a zone where the orange LED flashes rapidly. You need to move it to just outside this zone (I find just "below" it seems best) far enough so it's not randomly triggering but close enough that the tiny vibration from the airlock blooping does trigger it. This is fiddly but possible and needs to be done while some real fermenting is happening. You may find it needs tuning a bit more when the bloops get down to one every 5 minutes or so as they produce less vibration. To help with this a bit I weighted my airlock lid with some large washers, approx 30g worth.

The airlock can be a bit inconsistent, especially if you have StarSan or something that foams as the liquid. I am working on this...

## Usage
Now you should now have a service on your pi logging constantly to a sqlite database and producing pretty graphs. It is pretty much fire and forget, but don't forget your data is on the pi, not in the cloud. If you reclaim the pi for something else and want to keep the data you will need to move it (/usr/share/beerlog by default).
### LEDs
After a little dance on start up the green led should be on when the service is running (it's enabled on boot by default so this should be always and systemd should recover it if something unexpected happens). It will unflash every minute when a temperature reading is taken.
The orange LED flashes whenever a bloop is detected. The red LED will only come on if an error is detected. Your first (and hopefully only) step in troubleshooting this is to turn it off and then on again. Restarting the service/losing power will not affect the data other than missing a few readings.

### Web Interface
It's basic at the moment but usable and it should be largely self explanatory. Go to http://[yourpiip]:8336/ and browse around. "Monitor" shows you constant monitoring whereas "Session" will only show you what has happened in a given session.
It is a Work in Progress atm.

## Implementation Details
The bloop counter is a simple incrementer and will reset to 0 every time the service restarts. If you're reading the output from the db directly you need to look at the difference between readings rather than the absolute value (and obviously extrapolate when the reading is less than the previous one).

There is a rate limit on the counter that will stop it counting more than 2 per second. You can change this in the config file if you think this is too low. This was introduced to limit the data pollution from accidental knocks and the like. I found, probably because of my terrible soldering, that putting my fingers near the sensor was enough to trigger it hundreds of times per second.
	
## Braubonnet
This is the DIY part! Here's the circuit diagram:
![Schematic](https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/schematic.png "Braubonnet Schematic")

I've substituted some parts that were missing or broken in Eagle so don't pay attention to the names on the diagram. The actual part list is:

| Name | Part | Value |
| --- | --- | --- |
| Vibro | Minisense 100 Vertical | |
| R1 | Resistor | 100M Ohm |
| D1 & D2 | Zener Diode | 5.1V |
| R2 | Potentiometer | 10k Ohm |
| IC1 | LM293 Comparator | |
| IC2 & IC3 | DS18B20 | |
| R6 | Resistor | 4.7k Ohm |
| R3, R4, R5 | Resistor | 560 Ohm |
| LED1, 2, 3 | 3mm LED | ? |
| C1 | Capacitor | 0.1uF |

(I've forgotten to connect up the capacitor in the schematic: it just goes from live to ground.)

There is also a molex connector not shown for the temp sensors, which are on the end of a wire and covered in heatshrink.

In physically building it I used an Adafruit Pi Zero Bonnet as the base so that it fitted directly on top of the Pi (and it was important that it was small enough to fit on the airlock). This was a bit of a stretch to fit on so I used Fritzing eventually to fit the pieces on. As you can see below Fritzing isn't exactly ideal for this (or anything) but it's easier than doing it on paper.

<img src="https://raw.github.com/nickl111/beerlogger/master/docs/BrauBot-Bonnet_bb.png" width="200"> <img src="https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/BB Photo 1.jpg" width="200"> <img src="https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/BB Photo 2.jpg" width="200"> <img src="https://raw.githubusercontent.com/nickl111/beerlogger/master/docs/BB Photo 3.jpg" width="200">

Note that originally I had the ambient temp sensor directly on the board but this picked up too much heat from the pi cpu so I moved it so both were on the remote wire, one just shorter than the other.

I have an Inkbird temperature controller with a brew belt that is actually controlling the heating (and no cooling because I live in Devon). In principle it wouldn't be much work to get the Pi to control the heating via a relay but in practice it's simpler and safer and as cheap to just to run the Inkbird. 

## Acknowledgments
- I borrowed part of the circuit design for the piezo sensors from here: https://scienceprog.com/thoughts-on-interfacing-piezo-vibration-sensor/
- I borrowed the idea of counting airlock bloops from Speidels themselves. Their [GÄRSPUNDmobil](https://www.speidels-braumeister.de/en/braumeister/gaerspundmobil-and-gaermeister-control.html) is basically the same thing (though note I've not actually seen one so I don't know how it actually works internally. And I would have just bought one if they were €50 instead of €150). 

## Known Issues
- Using apache is overkill
- Producing RRD graphs every minute is probably overkill
- The web interface is basic and probably hideously insecure. Don't expose it to the world.
- I used 5.1V Zeners instead of 3.6V because that's what I had. I don't know how much difference this makes. (But I do know that 3.3V Zeners don't work)
- I used a smaller resistor across the piezo than recommended (320M) because the larger one was physically too large to fit on the board. I don't think this makes much difference for this application.
- Airlock activity doesn't completely represent fermentation activity. I know. This is a guide only.
- Electricity and beer do not mix. Be careful.