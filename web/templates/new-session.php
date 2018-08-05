<div class="content">
	<h2 class="title is-2">New Session</h2>
	<form method="POST" action="/">
		<div class="field is-size-6">
			<label class="label" for="name-input">Name</label>
			<div class="control">
				<input class="input" name="field_name" id="name-input" type="text" placeholder="A name for your session, eg Red IPA 2 or VPA Version 4">
			</div>
		</div>
		<div class="field is-size-6">
			<label class="label">Start</label>
			<div class="control">
				<input class="input" name="start_date" id="start_date" type="date" value="<?php print date('Y-m-d');?>">
				<input class="input" name="start_time" id="start_time" type="time" value="<?php print date('H:i');?>">
			</div>
		</div>
		<div class="field">
			<label class="label">Notes</label>
			<textarea class="textarea" name="field_notes" placeholder="Recipe Details"></textarea>
		</div>
		<input type="submit" class="button is-primary is-pulled-right" value="Save">
		<input type="hidden" name="do" value="newSession">
		<input type="hidden" name="view" value="session">
	</form>
</div>