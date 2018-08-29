<div class="hero">
	<h1 class="title">Welcome to Beerlogger!</h1>

	<h2 class="subtitle">You don't appear to have a brew in progess so click the button below to start one or use the menu on the left to create a recipe</h2>
</div>
<div class="content" style="margin-top: 2em"><a class="button is-large is-primary is-dark is-fullwidth" href="?view=newBrew">Start new Brew</a></div>
<?php
$s = new Brew($db);
if($s->find('1=1 ORDER BY ts_end DESC')) {
	?><div class="has-text-centered"><a class="button" href="?view=brew&amp;do=resumePrevBrew">Resume Previous Brew</a></div>
	<h3>Previous Brews</h3>
	<?php
	while($s->load()) {
		$ago = time() - $s->fields['ts_end'];
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
		<hr/>
		<article class="media">
			<figure class="media-left">
			  <p class="image is-64x64">
				<a href="?view=brew&amp;do=view&amp;pks=<?php print $s->fields['id']; ?>"><img src="/lib/identicon.php?size=128&hash=<?php print $s->getHash();?>"></a>
			  </p>
			</figure>
			<div class="media-content">
			  <div class="content">
				<p>
				  <strong><a href="?view=brew&amp;do=view&amp;pks=<?php print $s->fields['id']; ?>"><?php print $s->getDisplayName(); ?></a></strong> <small>Brewed: <?php print date("jS F Y",$s->fields['ts_start']) ;?></small>	&mdash; <small>Bottled: <?php print date("jS F Y",$s->fields['ts_end'])." ($agoStr ago)";?></small>
				  <br>
				  <?php print nl2br(htmlentities($s->fields['notes'])) ;?>
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