<div class="hero">
	<h1 class="title">Welcome to Beerlogger!</h1>

	<h2 class="subtitle">You don't appear to have a fermentation session in progess so click the button below to start one or use the menu on the left to create a recipe</h2>
</div>
<div class="content" style="margin-top: 2em"><a class="button is-large is-primary is-dark is-fullwidth" href="?view=newSession">Start new Session</a></div>
<?php
$s = new Session($db);
if($s->find('1=1 ORDER BY ts_end DESC')) {
	?><div class="has-text-centered"><a class="button" href="?view=session&amp;do=resumePrevSession">Resume Previous Session</a></div>
	<h3>Previous Sessions</h3>
	<?php
	while($s->load()) {
		?>
		<hr/>
		<article class="media">
			<figure class="media-left">
			  <p class="image is-64x64">
				<img src="https://bulma.io/images/placeholders/128x128.png">
			  </p>
			</figure>
			<div class="media-content">
			  <div class="content">
				<p>
				  <strong><a href="?view=session&amp;do=view&amp;pks=<?php print $s->fields['id']; ?>"><?php print $s->getDisplayName(); ?></a></strong> <small><?php print date("jS F Y",$s->fields['ts_start']) ;?></small>	
				  <br>
				  <?php print $s->fields['notes'] ;?>
				</p>
			  </div>
			  </div>
			<div class="media-right">
		  
			</div>
		</article>
		
		<?php
	}
}
?>