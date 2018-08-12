<?php
$d = new data($db);
if($cur = $d->getCurrent()) {
	$ary = current($cur);
	$b_temp 	= $ary['b_temp'];
	$a_temp 	= $ary['a_temp'];
	$avg_bloop 	= $ary['avg_bloop'];
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
	<style type="text/css">
		.beer_temp {
			stroke: red;
		}
		.amb_temp {
			stroke: orange;
		}
		.avg_bloop {
			stroke: yellow;
		}
	</style>
	<h1 class="title">Fermenting <?php print $s->fields['name'];?></h1>
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
				<p class="heading">Beer</p>
				<p class="title"><?php print $b_temp; ?> &deg;C</p>
			</div>
		</div>
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Ambient</p>
				<p class="title"><?php print $a_temp; ?> &deg;C</p>
			</div>
		</div>
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Activity</p>
				<p class="title"><?php print $avg_bloop; ?></p>
			</div>
		</div>
	</nav>
	
	<article class="box">
		<div class="ct-chart ct-octave"></div>
	</article>

	<div class="has-text-centered">
		<a class="button is-large" href="?view=sample&amp;do=edit&amp;session_id=<?php print $s->fields['id'] ;?>">New Sample</a>
		<a class="button is-info is-large" href="?view=session&amp;do=endSession">Bottle it!</a>
	</div>

<?php
$b = $d->getBins(3600, $s->fields['ts_start'], $s->fields['ts_end']);
foreach($b as $binNo => $bAry) {
	$bs[] 	= $bAry['b_temp'];
	$as[] 	= $bAry['a_temp'];
	$bcs[] 	= $bAry['avg_bloop'];
	
	$day = date('D',$binNo);
	if($oldDay != $day) {
		$label = $day;
	} else {
		$label = '';
	}
	$labels[] = "'$label'";
	$oldDay = $day;
}
?>

<script language="javascript">
	var data = {
	// A labels array that can contain any sort of values
	labels: [<?php print implode(', ',$labels) ;?>],
	// Our series array that contains series objects or in this case series data arrays
	series: [
		{
			className: 'beer_temp',
			name: 'Beer Temperature',
			data: [<?php print implode(', ',$bs);?>]
		},
		{
			className: 'amb_temp',
			name: 'Ambient Temperature',
			data: [<?php print implode(', ',$as);?>]
		},
		{
			className: 'avg_bloop',
			name: 'Bloops/min',
			data: [<?php print implode(', ',$bcs);?>]
		}
	]
	};
	
	// Create a new line chart object where as first parameter we pass in a selector
	// that is resolving to our chart container element. The Second parameter
	// is the actual data object.
	 new Chartist.Line('.ct-chart', data);
	
</script>