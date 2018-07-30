#! /bin/bash
THIS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. $THIS_DIR/../config.sh

GRAPH_PERIOD=1
PERIODS="hour day week month year"
RRA_DEF="RRA:AVERAGE:0.5:1:120 RRA:AVERAGE:0.5:5:576 RRA:AVERAGE:0.5:30:672 RRA:AVERAGE:0.5:120:744 RRA:AVERAGE:0.5:1440:732 RRA:MAX:0.5:1:120 RRA:MAX:0.5:5:576 RRA:MAX:0.5:30:672 RRA:MAX:0.5:120:744 RRA:MAX:0.5:1440:732"
HEARTBEAT=300
WIDTH=620
HEIGHT=280
#OPTIONS=" --only-graph"
OPTIONS=""
case $1 in
	create)
		rrdtool create $DATA_DIR/beerlog.rrd \
			--step 60 --no-overwrite \
			DS:BLOOP:DERIVE:$HEARTBEAT:0:U   \
			DS:BEERTEMP:GAUGE:$HEARTBEAT:0:U  \
			DS:AMBTEMP:GAUGE:$HEARTBEAT:0:U  \
			$RRA_DEF
		;;
	update)
		rrdtool update $DATA_DIR/beerlog.rrd `cat $OUTRRD`
	;;
	graph)
		for PERIOD in $PERIODS
		do
			rrdtool graph $OUT_DIR/html/beerlog-$PERIOD.png \
				--start now-${GRAPH_PERIOD}$PERIOD \
				--vertical-label "Temperature C" \
				--title "Beer measurements for the last $PERIOD" \
				--width ${WIDTH} \
				--height ${HEIGHT} \
				--lower-limit 0 \
				--rigid \
				--right-axis 1:0 \
				--right-axis-label "Bloops / min" \
				--right-axis-format %1.1lf \
				--alt-autoscale ${OPTIONS} \
				DEF:BLOOP=$DATA_DIR/beerlog.rrd:BLOOP:AVERAGE \
				DEF:BEER=$DATA_DIR/beerlog.rrd:BEERTEMP:AVERAGE \
				DEF:AMB=$DATA_DIR/beerlog.rrd:AMBTEMP:AVERAGE \
				CDEF:BPM=BLOOP,60,* \
				AREA:BPM#66ee66:"Bloops/min\t" \
				GPRINT:BPM:AVERAGE:"Avg\:%4.2lf" \
				GPRINT:BPM:MAX:"Max\:%4.2lf" \
				GPRINT:BPM:MIN:"Min\:%4.2lf\n" \
				LINE:BEER#660000:"Beer Temp\t" \
				GPRINT:BEER:AVERAGE:"Avg\:%4.2lf" \
				GPRINT:BEER:MAX:"Max\:%4.2lf" \
				GPRINT:BEER:MIN:"Min\:%4.2lf\n" \
				LINE:AMB#0000cc:"Ambient Temp\t" \
				GPRINT:AMB:AVERAGE:"Avg\:%4.2lf" \
				GPRINT:AMB:MAX:"Max\:%4.2lf" \
				GPRINT:AMB:MIN:"Min\:%4.2lf\n" \
				COMMENT:"Beerlogger\l" \
				COMMENT:"\u" \
				COMMENT:"`date | sed "s/\:/\\\\\:/g"`\r" > /dev/null
		done
	;;
esac