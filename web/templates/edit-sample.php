<div class="content">
	
	<h2 class="title is-2">Sample</h2>
	<form method="POST">
		
	<div class="field">
		<label class="label" for="name-input">Session</label>
		<div class="select">
			
			<select name="field_session_id">
				<?php
					$sessId = intval($_REQUEST['session_id']);
					$s = new session($db);
					if($s->find()) {
						while($s->load()) {
							print '<option value="'.$s->fields['id'].'"'.($sessId == $s->fields['id'] ? ' selected="selected"' : '').'>'.$s->fields['name']."</option>\n";
						}
					}
				?>
			</select>
		</div>
	</div>
	
	<div class="field">
		<label class="label">Time Taken</label>
		<div class="field is-horizontal">
			<div class="control" style="margin-right: 1rem">
				<input class="input" name="start_date" id="start_date" type="date" value="<?php print date('Y-m-d');?>">
			</div>
			<div class="control">
				<input class="input" name="start_time" id="start_time" type="time" value="<?php print date('H:i');?>">
			</div>
		</div>
	</div>
	<div class="field is-size-6">
		<label class="label" for="sg-input">Gravity</label>
		<div class="control">
			<input class="input" name="field_sg" id="sg-input" type="text" placeholder="1.000" value="<?php print $o->fields['sg']; ?>">
		</div>
	</div>
	<div class="field">
		<label class="label">Notes</label>
		<textarea class="textarea" name="field_note" placeholder=""><?php print $o->fields['note']; ?></textarea>
	</div>
	<a class="button" href="?view=recipe">Cancel</a><input type="hidden" name="field_id" value="<?php print $o->fields['id']; ?>">
	<input type="submit" class="button is-primary is-pulled-right" value="Save">
	<input type="hidden" name="do" value="newSample"></form>
</div>
