<div class="column is-narrow is-2">
	<aside class="menu">
		<ul class="menu-list">
			<li><a<?php print ($this->view == 'home' ? ' class="is-active"' : '')?> href="?view=home">Home</a></li>
			<p class="menu-label">
				Administration
			  </p>
			<li><a<?php print ($this->view == 'brew' ? ' class="is-active"' : '')?> href="?view=brew">Brews</a></li>
			<li><a<?php print ($this->view == 'recipe' ? ' class="is-active"' : '')?> href="?view=recipe">Recipes</a></li>
			<li><a<?php print ($this->view == 'yeast' ? ' class="is-active"' : '')?> href="?view=yeast">Yeasts</a></li>
			<li><a<?php print ($this->view == 'schedule' ? ' class="is-active"' : '')?> href="?view=schedule">Schedules</a></li>
		</ul>
	</aside>
</div>