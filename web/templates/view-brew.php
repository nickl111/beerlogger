<script src="/js/Chart.bundle.min.js"></script>
<script src="/js/chartjs-plugin-annotation.min.js"></script>
<script src="/js/chartjs-plugin-zoom.min.js"></script>
<div class="content">
	<figure class="image is-64x64 is-pulled-left"><img class="" src="/lib/identicon.php?size=128&hash=<?php print $o->getHash();?>"></figure>
	<h2 class="title is-3">Brew <?php print $o->fields['name']; ?></h2>
	<p class="subtitle is-5">Started: <?php print date("D jS M Y H:i",$o->fields['ts_start']) ; if ($o->fields['ts_end']) { print "&nbsp; Bottled: ".date("D jS M Y H:i",$o->fields['ts_end']) ; }?></p>
	<article class="box">
		<canvas id="myChart" width="900" height="400"></canvas>
	</article>
	<?php
	$samples = $o->getSamples();
	if(count($samples) > 0) {
		?>
		<article>
			<h3 class="title">Samples</h3>
			<table>
				<thead>
					<tr>
						<th>Date</th>
						<th>Gravity</th>
						<th>Notes</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($samples as $sample) {
					?>
					<tr>
						<td><?php print date("D jS M Y H:i",$sample->fields['ts']);?></td>
						<td><?php print $sample->fields['sg'];?></td>
						<td><?php print $sample->fields['note'];?></td>
					</tr>
					<?php
					}
					// also collect some data for the chart
						$sample_data[] = array($sample->fields['sg'],$sample->fields['ts']);
					?>
				</tbody>
			</table>
		</article>
		<hr />
		<?php
	}
	
$sms = array();
$this_sample = reset($sample_data);
$b = $o->getData();
foreach($b as $binNo => $bAry) {
	$bs[] 	= $bAry['b_temp'];
	$as[] 	= $bAry['sg'];
	$bcs[] 	= $bAry['battery'];
	
	if($this_sample[1] < $binNo) {
		$sms[] = $this_sample[0];
		$this_sample = next($sample_data);
		
	} else {
		$sms[] = '';
	}
	
	$label = date('j M H:i', $binNo);
	$labels[] = "'$label'";
}
// Put OG and FG on the graph for free
if($o->fields['g_orig']) {
	$as[0] = $o->fields['g_orig'];
}
if($o->fields['g_final']) {
	$sms[] = $o->fields['g_final'];
}
?>
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
					<label class="label" for="g_pre_boil-input">Pre-Boil Gravity</label>
					<div class="control">
						<input class="input" name="field_g_pre_boil" id="g_pre_boil-input" type="text" value="<?php print $o->fields['g_pre_boil']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="g_final-input">Final Gravity</label>
					<div class="control">
						<input class="input" name="field_g_final" id="g_final-input" type="text" value="<?php print $o->fields['g_final']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="abv-input">ABV</label>
					<div class="control">
						<input class="input" name="field_abv" id="abv-input" type="text" value="<?php print $o->fields['abv']; ?>">
					</div>
				</div>
			</div>
			
			<div class="column">
				<div class="field is-horizontal">
					<div class="field is-size-3" style="margin-right: 1rem">
				
						<label class="label" for="color-input">Color</label>
						<div class="control">
							<input class="input" name="field_color" id="color-input" type="text" value="<?php print $o->fields['color']; ?>">
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
					<label class="label" for="vol_pre_boil-input">Pre-boil Volume</label>
					<div class="control">
						<input class="input" name="field_vol_pre_boil" id="vol_pre_boil-input" type="text" value="<?php print $o->fields['vol_pre_boil']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="vol_bottle-input">Bottled Volume</label>
					<div class="control">
						<input class="input" name="field_vol_bottle" id="vol_bottle-input" type="text" value="<?php print $o->fields['vol_bottle']; ?>">
					</div>
				</div>
				<div class="field">
					<label class="label" for="carb_level-input">Carbonation Level</label>
					<div class="control">
						<input class="input" name="field_carb_level" id="carb_level-input" type="text" value="<?php print $o->fields['carb_level']; ?>">
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
				fill: false,
				yAxisID: 'y-axis-1',
				data: [<?php print implode(', ',$bs);?>]
			},
			{
				label: 'Specific Gravity',
				borderColor: 'rgba(13, 99, 255, 0.2)',
				backgroundColor: 'rgba(13, 99, 255, 0.2)',
				fill: false,
				yAxisID: 'y-axis-2',
				data: [<?php print implode(', ',$as);?>]
			},
			{
				label: 'Sample Gravity',
				borderColor: 'rgba(0,0,255,0.8)',
				backgroundColor: 'rgba(0,0,255,0.8)',
				fill: false,
				yAxisID: 'y-axis-2',
				data: [<?php print implode(', ',$sms);?>]
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
				ticks: {
					suggestedMin: 1,
					suggestedMax: 1.070
				},
				scaleLabel: {
					display: true,
					labelString: 'Gravity'
				}
			}]
		},
		zoom: {
			enabled: true,
			mode: 'y'
		}
    }
});
</script>
