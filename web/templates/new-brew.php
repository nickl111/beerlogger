<div class="content">
	<h2 class="title is-2">New Brew</h2>
	<form method="POST" action="/">
		
	<div class="field is-size-6">
		<label class="label" for="name-input">Name</label>
		<div class="control">
			<input class="input" name="field_name" id="name-input" type="text" placeholder="A name for your brew, eg Red IPA 2 or VPA Version 4">
		</div>
	</div>
	<div class="field">
		<label class="label" for="name-input">Recipe</label>
		<div class="select">
			
			<select name="field_recipe_id">
				<option value="">None</option>
				<?php
					$r = new recipe($db);
					if($r->find()) {
						while($r->load()) {
							print '<option value="'.$r->fields['id'].'">'.$r->fields['name']."</option>\n";
						}
					}
				?>
			</select>
		</div>
	</div>
	<div class="field">
		<label class="label">Start Time</label>
		<div class="field is-horizontal">
			<div class="control" style="margin-right: 1rem">
				<input class="input" name="start_date" id="start_date" type="date" value="<?php print date('Y-m-d');?>">
			</div>
			<div class="control">
				<input class="input" name="start_time" id="start_time" type="time" value="<?php print date('H:i');?>">
			</div>
		</div>
	</div>
	<div class="field">
		<label class="label">Notes</label>
		<textarea class="textarea" name="field_notes" placeholder="Recipe Details"></textarea>
	</div>
	<hr>
	<div class="content">
		<div class="field is-horizontal">
			<div class="field is-size-3" style="margin-right: 1rem">
				<label class="label" for="name-input">Original Gravity</label>
				<div class="control">
					<input class="input" name="field_g_orig" id="g_orig-input" type="text" placeholder="1.000">
				</div>
			</div>
			<div class="field is-size-3">
				<label class="label" for="name-input">Batch Size (litres)</label>
				<div class="control">
					<input class="input" name="field_vol_ferment" id="vol_ferment-input" type="text" placeholder="19">
				</div>
			</div>
		</div>
	</div>

	<input type="submit" class="button is-primary is-pulled-right" value="Save">
	<input type="hidden" name="do" value="newBrew">
	<input type="hidden" name="view" value="brew">
		
	</form>
</div>