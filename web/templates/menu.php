<div class="column is-narrow is-2">
	<aside class="menu">
		<ul class="menu-list">
			<li><a<?php print ($this->view == 'home' ? ' class="is-active"' : '')?> href="?view=home">Home</a></li>
			<li><a<?php print ($this->view == 'monitor' ? ' class="is-active"' : '')?> href="?view=monitor">Monitor</a></li>
			<li><a<?php print ($this->view == 'session' ? ' class="is-active"' : '')?> href="?view=session">Sessions</a></li>
			<li><a<?php print ($this->view == 'recipe' ? ' class="is-active"' : '')?> href="?view=recipe">Recipes</a></li>
			<li><a<?php print ($this->view == 'sample' ? ' class="is-active"' : '')?> href="?view=sample">Samples</a></li>
			<li><a<?php print ($this->view == 'data' ? ' class="is-active"' : '')?> href="?view=data">Data</a></li>
		</ul>
	</aside>
</div>