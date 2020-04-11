<?php
$ago = time() - $o->fields['ts_start'];
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


$b = $o->getData();
foreach($b as $binNo => $bAry) {
	$bs[] 	= $bAry['b_temp'];
	$as[] 	= $bAry['sg'];
	$vol[] 	= $bAry['sg_sd']*10;
	
	$label = date('j M H:i', $binNo);
	$labels[] = "'$label'";
}

$b_temp = end($bs);
$sg = end($as);

reset($bs);
reset($as);

?>
<script src="/js/Chart.bundle.min.js"></script>
<script src="/js/chartjs-plugin-annotation.min.js"></script>
<script src="/js/chartjs-plugin-zoom.min.js"></script>
<div class="content">
	<figure class="image is-64x64 is-pulled-left" style="margin-bottom:0px"><img class="" src="/lib/identicon.php?size=128&hash=<?php print $o->getHash();?>"></figure>
	<h2 class="title is-3">Brew <?php print $o->fields['name']; ?></h2>
	<p class="subtitle is-5">Started: <?php print date("D jS M Y H:i",$o->fields['ts_start']) ; if ($o->fields['ts_end']) { print "&nbsp; Bottled: ".date("D jS M Y H:i",$o->fields['ts_end']) ; }?></p>
	
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
				<p class="title"><?php print number_format($o->getABV(),2); ?>%</p>
			</div>
		</div>
		<div class="level-item has-text-centered">
			<div>
				<p class="heading">Attenuation</p>
				<p class="title"><?php print number_format($o->getAttenuation()); ?>%</p>
			</div>
		</div>
	</nav>
	<article class="box">
		<canvas id="myChart" width="900" height="400"></canvas>
	</article>
	
	<form method="POST" action="?">
		
		<div class="columns">
			<div class="column">
				<div class="field">
					<label class="label" for="name-input">Name</label>
					<div class="control">
						<input class="input" name="field_name" id="name-input" type="text" value="<?php print $o->fields['name']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="g_orig-input">Original Gravity</label>
					<div class="control">
						<input class="input" name="field_g_orig" id="g_orig-input" type="text" value="<?php print $o->fields['g_orig']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="g_final-input">Expected Final Gravity</label>
					<div class="control">
						<input class="input" name="field_g_final" id="g_final-input" type="text" value="<?php print $o->fields['g_final']; ?>">
					</div>
				</div>
				
			</div>
			
			<div class="column">
				<div class="field is-horizontal" style="margin-bottom:0px">
					<div class="field" style="margin-right: 1rem">
						<label class="label">Tilt Color</label>
						<div class="select">
							<select name="field_color">
								<option value="20"<?php print ($o->fields['color'] == 20 ? ' selected="selected"' : ''); ?>>Green</option>
								<option value="10"<?php print ($o->fields['color'] == 10 ? ' selected="selected"' : ''); ?>>Red</option>
								<option value="30"<?php print ($o->fields['color'] == 30 ? ' selected="selected"' : ''); ?>>Black</option>
								<option value="40"<?php print ($o->fields['color'] == 40 ? ' selected="selected"' : ''); ?>>Purple</option>
								<option value="50"<?php print ($o->fields['color'] == 50 ? ' selected="selected"' : ''); ?>>Orange</option>
								<option value="60"<?php print ($o->fields['color'] == 60 ? ' selected="selected"' : ''); ?>>Blue</option>
								<option value="70"<?php print ($o->fields['color'] == 70 ? ' selected="selected"' : ''); ?>>Yellow</option>
								<option value="80"<?php print ($o->fields['color'] == 80 ? ' selected="selected"' : ''); ?>>Pink</option>
							</select>
						</div>
					</div>
					<div class="field">
						<label class="label">Recipe</label>
						<div class="select">
							<select name="field_recipe_id">
								<option value=""></option>
								<?php
									$r = new recipe($db);
									if($r->find()) {
										while($r->load()) {
											print '<option value="'.$r->fields['id'].'"'.($o->fields['recipe_id'] == $r->fields['id'] ? ' selected="selected"' : '').'>'.$r->fields['name']."</option>\n";
										}
									}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="field">
					<label class="label" for="vol_ferment-input">Volume into Fermenter</label>
					<div class="control">
						<input class="input" name="field_vol_ferment" id="vol_ferment-input" type="text" value="<?php print $o->fields['vol_ferment']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="vol_bottle-input">Bottled Volume</label>
					<div class="control">
						<input class="input" name="field_vol_bottle" id="vol_bottle-input" type="text" value="<?php print $o->fields['vol_bottle']; ?>">
					</div>
				</div>
			</div>

		</div>
		<div class="field">
			<label class="label">Notes</label>
			<textarea class="textarea" name="field_notes" placeholder="Recipe Details"><?php print $o->fields['notes']; ?></textarea>
		</div>
		<div class="field">
			<a class="button" href="?view=brew">Cancel</a><input type="hidden" name="field_id" value="<?php print $o->fields['id']; ?>">
			<input type="submit" class="button is-primary is-pulled-right" value="Save">
			<input type="hidden" name="do" value="save">
			<input type="hidden" name="field_ts_end" value="<?php print $o->fields['ts_end']; ?>">
			<input type="hidden" name="field_ts_start" value="<?php print $o->fields['ts_start']; ?>">
			<input type="hidden" name="view" value="brew">
			<input type="hidden" name="pks" value="<?php print $o->fields['id']; ?>">
		</div>
	</form>
</div>
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
		},
		zoom: {
			enabled: false,
			mode: 'y'
		}
    }
});
</script>
