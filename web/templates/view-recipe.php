<?php
$y = new yeast($db);
$y->load($o->fields['yeast_id']);
?>
<div class="content">
<div class="is-pulled-right">
	<a class="edit" href="?view=recipe&amp;do=edit&amp;pks=<?php print $o->fields['id']; ?>"><span class="icon has-text-grey"><i class="fas fa-edit"></i></span></a>
</div>
<h1 class="title" style="margin-top: 0">
  <?php print $o->fields['name']; ?>
</h1>
<h3 class="subtitle">
 <a href="?view=yeast&amp;do=view&amp;pks=<?php print $o->fields['yeast_id'];?>"><?php print $y->getDisplayName(); ?></a>
</h3>

<blockquote><?php print $o->fields['notes']; ?></blockquote>

<?php
$s = new session($db);
if($s->find("recipe_id = ".$o->fields['id']." ORDER BY ts_start DESC")) {
	?>
	<h4>Sessions</h4>
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
} else {
	?>
	<h4>No Sessions Yet</h4>
	<?php
}
?>
</div>