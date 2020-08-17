# ping graph

long-running php script that will generate a chart of your latency times, one image per day

each pixel is one second; each line line is five minutes; tick marks are generated to help
identify hours

![sample graph](https://i.imgur.com/r2UiaPt.png)

the graphed colors go from black (0ms) to full green (100ms) to yellow (500ms) to "lost" (> 500 ms).
in the image above, there was constant mild packet loss through the entire afternoon, with two
total losses of connectivity just before 5 PM and right at 6 PM.

## usage

* start by running `./start.sh [ip] [name]`
* this will start a long-running background task. you will have to kill it manually if you need to change it
* make the `pings` folder accessible by symlinking to web server path or w/e
* optionally, run another ping graph instance against a known-good connection like google or cloudflare
* adjust `pings/index.php` to draw from the names you are using
* opionally adust the ping graph drawing to omit your ip if you care about that sort of thing

## requirements

* php
* php-gd
* fping
* a bad internet connection


## license

this is licensed under the wtfpl. the text of the wtfpl can be found in the `LICENSE` file.

* you can do whatever the fuck you want with it.
* no warranty exists, implied or otherwise, use at own risk, etc. not responsible for disapointment in your internet 
service provider.
