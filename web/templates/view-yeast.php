<div class="content">
<div class="is-pulled-right">
	<a class="edit" href="?view=yeast&amp;do=edit&amp;pks=<?php print $o->fields['id']; ?>"><span class="icon has-text-grey"><i class="fas fa-edit"></i></span></a>
</div>
<h1 class="title" style="margin-top: 0">
  <?php print $o->getDisplayName(); ?>
</h1>

<blockquote><?php print $o->fields['description']; ?></blockquote>

<?php
$r = new recipe($db);
if($r->find("yeast_id = ".$o->fields['id'])) {
	?>
	<h4>Recipes that use this yeast</h4>
	<?php
	while($r->load()) {
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
        <strong><a href="?view=recipe&amp;do=view&amp;pks=<?php print $r->fields['id']; ?>"><?php print $r->getDisplayName(); ?></a></strong>
        <br>
		<?php print $r->fields['notes'] ;?>
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
	<h4>No Recipes Yet</h4>
	<?php
}
?>
</div>