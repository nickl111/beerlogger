<div class="content">
	<h2 class="title is-2">Recipe</h2>
	<form method="POST" action="?">
	<div class="field is-size-6">
		<label class="label" for="name-input">Name</label>
		<div class="control">
			<input class="input" name="field_name" id="name-input" type="text" value="<?php print $o->fields['name']; ?>">
		</div>
	</div>
	<div class="field">
		<label class="label">Details</label>
		<textarea class="textarea" name="field_notes" placeholder="Recipe Details"><?php print $o->fields['notes']; ?></textarea>
	</div>
	<div class="field">
		<label class="label">Yeast</label>
		<div class="select">
			<select name="field_yeast_id">
				<option value=""></option>
				<?php
					$y = new yeast($db);
					if($y->find()) {
						while($y->load()) {
							print '<option value="'.$y->fields['id'].'"'.($o->fields['yeast_id'] == $y->fields['id'] ? ' selected="selected"' : '').'>'.$y->fields['name']."</option>\n";
						}
					}
				?>
			</select>
		</div>
	</div>
	<a class="button" href="?view=recipe">Cancel</a><input type="hidden" name="field_id" value="<?php print $o->fields['id']; ?>">
	<input type="submit" class="button is-primary is-pulled-right" value="Save">
	<input type="hidden" name="do" value="save">
	<input type="hidden" name="view" value="recipe">
	<input type="hidden" name="pks" value="<?php print $o->fields['id']; ?>">
	</form>
	
</div>
