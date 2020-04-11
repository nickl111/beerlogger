# Beer Logger for Tilt 

Beerlogger is a simple linux service that logs temperature and gravity from a Tilt hydrometer. As we are running on a Pi however we can also run a web server and make the results available to the world (though router config and system capacity for this I'm afraid is your problem).

It was designed to stop me having to go out to the garage to see what was happening all the time.

![Graph](docs/Screenshot%202020-04-11%20at%2009.04.46.png "Web Interface Graph")

## Installation
Install it on a fresh copy of Raspbian on a Pi Zero W. Make sure you get wifi working reliably first and raspbian updated. Then
```
git clone https://github.com/nickl111/beerlogger.git
cd beerlogger
bash install.sh install
```

This uses Bluez. Bluez is... temperamental. Restarting the bluetooth service maybe occasionally be necessary.

Should you decide you don't want it `bash install.sh uninstall` should remove it cleanly but leave the data in /usr/share/beerlog.

## Usage
Stick the Tilt in the beer, plug in the pi nearby and then head to a web browser. Hit http://[yourpiip]:8336/ and follow the instructions. Don't forget to set the color of your Tilt on the new brew:
 Red	10
 Green	20
 Black	30
 Purple	40
 Orange	50
 Blue	60
 Yellow	70
 Pink	80

Now you should shortly have some pretty graphs. It is pretty much fire and forget, but don't forget your data is on the pi, not in the cloud.


## Notes

I have an Inkbird temperature controller actually controlling the heating and cooling (a brewbelt on the fermenter and the whole thing is in a fridge). In principle it wouldn't be much work to get the Pi to control the heating via a relay but in practice it's simpler and safer and as cheap to just to run the Inkbird.

## Known Issues
- The web interface is basic, buggy and probably hideously insecure. Don't expose it to the world. Feel free to improve.
