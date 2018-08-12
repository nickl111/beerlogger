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
<div class="content">
	<h2 class="title is-3">Session <?php print $o->fields['name']; ?></h2>
	<p class="subtitle is-5">Started: <?php print date("D jS M Y h:i",$o->fields['ts_start']) ; if ($o->fields['ts_end']) { print "&nbsp; Finished: ".date("D jS M Y h:i",$o->fields['ts_end']) ; }?></p>
	<article class="box">
		<div class="ct-chart ct-octave"></div>
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
						<td><?php print date("D jS M Y h:i",$sample->fields['ts']);?></td>
						<td><?php print $sample->fields['sg'];?></td>
						<td><?php print $sample->fields['note'];?></td>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</article>
		<hr />
		<?php
	}
	?>
	
<?php
$d = new data($db);
$b = $d->getBins(3600, $o->fields['ts_start'], $o->fields['ts_end']);
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

	<form method="POST">
		
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
			<a class="button" href="?view=session">Cancel</a><input type="hidden" name="field_id" value="<?php print $o->fields['id']; ?>">
			<input type="submit" class="button is-primary is-pulled-right" value="Save">
			<input type="hidden" name="do" value="save">
			<input type="hidden" name="field_ts_end" value="<?php print $o->fields['ts_end']; ?>">
			<input type="hidden" name="field_ts_start" value="<?php print $o->fields['ts_start']; ?>">
		</div>
	</form>
</div>
