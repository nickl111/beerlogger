<?php
$d = new data($db,20);
if($cur = $d->getCurrent()) {
	$ary = end($cur);
	$b_temp 	= $ary['b_temp'];
	$sg 		= $ary['sg'];
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
<script src="/js/chartjs-plugin-annotation.min.js"></script>
<script src="/js/chartjs-plugin-zoom.min.js"></script>
<div class="content">
	<h1 class="title">Fermenting <a href="?view=brew&do=view&pks=<?php print implode(",",$s->getPKValues());?>"><?php print $s->fields['name'];?></a></h1>
	<p class="subtitle"><?php print $s->getRecipe()->getDisplayname();?></p>
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
		<a class="button is-large" href="?view=sample&amp;do=edit&amp;brew_id=<?php print $s->fields['id'] ;?>">New Sample</a>
		<a class="button is-info is-large" href="?view=brew&amp;do=endBrew">Bottle it!</a>
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
    }
});
</script>