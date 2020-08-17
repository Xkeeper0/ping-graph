#!/bin/bash
# ./start.sh ip name
nohup php ping.php $1 $2 >/dev/null &
