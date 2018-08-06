<section class="section">
	<div class="hero">
		<h1 class="title">Welcome to Beerlogger!</h1>
	
		<h2 class="subtitle">You don't appear to have a fermentation session in progess so click the button below to start one or use the menu on the left to create a recipe</h2>
	</div>
	<div class="content" style="margin-top: 2em"><a class="button is-large is-primary is-dark is-fullwidth" href="?view=newSession">Start new Session</a></div>
	<?php if ($prevSess) {
	?><div class="has-text-centered"><a class="button" href="?view=session&amp;do=resumePrevSession">Resume Previous Session</a></div><?php
	}
	?>
</section>