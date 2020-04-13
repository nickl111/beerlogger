<?php

$d = $s->getCurrentData();

if($d) {
	$b_temp 	= $d['b_temp'];
	$sg 		= $d['sg'];
}

$ago = time() - $s->fields['ts_start'];
if($ago < 3600) {
	// minutes
	$agoStr = round($ago / 60).(round($ago / 60) == 1 ? ' Minute' : ' Minutes');
} elseif($ago < 86400) {
	// hours
	$agoStr = round($ago / 3600).(round($ago / 3600) == 1 ? ' Hour' : ' Hours');
} else {
	// days
	$agoStr = round($ago / 86400).(round($ago / 86400) == 1 ? ' Day' : ' Days');
}

?>
<script src="/js/Chart.bundle.min.js"></script>
<script src="/js/chartjs-plugin-annotation.js"></script>
<script src="/js/chartjs-plugin-zoom.min.js"></script>
<div class="content">
	<h1 class="title">Fermenting <a href="?view=brew&do=view&pks=<?php print implode(",",$s->getPKValues());?>"><?php print $s->fields['name'];?></a></h1>
	<p class="subtitle"><?php print $s->getRecipe()->getDisplayname();?><?php print($s->fields['ts_dryhop'] ?  ' - Dry Hopped on '.date("jS M Y H:m", $s->fields['ts_dryhop'] ) : ''); ?></p>
	<nav class="level box">
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Age</p>
				<p class="title"><?php print $agoStr; ?></p>
			</div>
		</div>
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Temp</p>
				<p class="title"><?php print $b_temp; ?> &deg;C</p>
			</div>
		</div>

		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Gravity</p>
				<p class="title"><?php print $sg; ?></p>
			</div>
		</div>
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">ABV</p>
				<p class="title"><?php print number_format($s->getABV(),2); ?>%</p>
			</div>
		</div>
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Attenuation</p>
				<p class="title"><?php print number_format($s->getAttenuation()); ?>%</p>
			</div>
		</div>
	</nav>
	
	<article class="box">
		<canvas id="myChart" width="900" height="400"></canvas>
	</article>

	<div class="has-text-centered">
		<?php if(!$s->fields['ts_dryhop']) { ?>
		<a class="button is-large" href="?view=brew&amp;do=dryhop&amp;pks=<?php print $s->fields['id'] ;?>">Dry Hop</a>
		<?php } ?>
		<a class="button is-info is-large" href="?view=brew&amp;do=endBrew&amp;pks=<?php print $s->fields['id'] ;?>">Bottle it!</a>
	</div>
</div>
<?php


$b = $s->getData();
foreach($b as $binNo => $bAry) {
	$bs[] 	= $bAry['b_temp'];
	$as[] 	= $bAry['sg'];
	$vol[] 	= $bAry['sg_sd']*10;
	
	$label = date('j M H:i', $binNo);
	$labels[] = "'$label'";

}
?>

<script language="javascript">
var ctx = document.getElementById("myChart").getContext('2d');
var myChart = new Chart(ctx, {
	type: 'line',
	data: { 
		labels: [<?php print implode(', ',$labels) ;?>],
		datasets: [
			{
				label: 'Beer Temperature',
				borderColor: 'rgba(255, 0, 0, 0.2)',
				backgroundColor: 'rgba(255, 0, 0, 0.2)',
				radius: 1,
				fill: false,
				yAxisID: 'y-axis-1',
				data: [<?php print implode(', ',$bs);?>]
			},
			{
				label: 'Specific Gravity',
				borderColor: 'rgba(13, 99, 255, 0.2)',
				backgroundColor: 'rgba(13, 99, 255, 0.2)',
				radius: 1,
				fill: false,
				yAxisID: 'y-axis-2',
				data: [<?php print implode(', ',$as);?>]
			},
			{
				label: 'Activity',
				borderColor: 'rgba(230,230,230,0.5)',
				backgroundColor: 'rgba(230,230,230,0.5)',
				fill: 'origin',
				radius: 0,
				yAxisID: 'y-axis-1',
				data: [<?php print implode(', ',$vol);?>]
			}
		]
	},
	options: {
		responsive: true,
		scales: {
			yAxes: [{
				type: 'linear',
				display: true,
				position: 'left',
				id: 'y-axis-1',
				ticks: {
					beginAtZero:true
				},
				scaleLabel: {
					display: true,
					labelString: '°C'
				}
			}, {
				type: 'linear',
				display: true,
				position: 'right',
				id: 'y-axis-2',
				// grid line settings
				gridLines: {
					drawOnChartArea: false, // only want the grid lines for one axis to show up
				},
				scaleLabel: {
					display: true,
					labelString: 'Gravity'
				}
			}]
		}
		<?php if($s->fields['ts_dryhop']) { ?>
		,annotation: {
			// Defines when the annotations are drawn.
			// This allows positioning of the annotation relative to the other
			// elements of the graph.
			//
			// Should be one of: afterDraw, afterDatasetsDraw, beforeDatasetsDraw
			// See http://www.chartjs.org/docs/#advanced-usage-creating-plugins
			drawTime: 'afterDatasetsDraw', // (default)

			// Mouse events to enable on each annotation.
			// Should be an array of one or more browser-supported mouse events
			// See https://developer.mozilla.org/en-US/docs/Web/Events
			events: ['click'],

			// Double-click speed in ms used to distinguish single-clicks from
			// double-clicks whenever you need to capture both. When listening for
			// both click and dblclick, click events will be delayed by this
			// amount.
			dblClickSpeed: 350, // ms (default)

			// Array of annotation configuration objects
			// See below for detailed descriptions of the annotation options
			annotations: [{
				drawTime: 'afterDraw', // overrides annotation.drawTime if set
				id: 'a-line-1', // optional
				type: 'line',
				mode: 'vertical',
				scaleID: 'x-axis-0',
				value: '<?php print date('j M H:i', floor($s->fields['ts_dryhop']/600)*600); ?>',
				borderColor: 'green',
				borderWidth: 2,

				// Fires when the user clicks this annotation on the chart
				// (be sure to enable the event in the events array below).
				onClick: function(e) {
					// `this` is bound to the annotation element
				}
			}]
		}
		<?php } ?>
    }
});
</script>