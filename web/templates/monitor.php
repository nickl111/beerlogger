<div class="container">
	<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar-brand">
	<a class="navbar-item <?php print ($graph == 'hour' ? 'is-active' : ''); ?>" href="?view=monitor&amp;graph=hour">Hour<a>
	<a class="navbar-item <?php print ($graph == 'day' ? 'is-active' : ''); ?>" href="?view=monitor&amp;graph=day">Day<a>
	<a class="navbar-item <?php print ($graph == 'week' ? 'is-active' : ''); ?>" href="?view=monitor&amp;graph=week">Week<a>
	<a class="navbar-item <?php print ($graph == 'month' ? 'is-active' : ''); ?>" href="?view=monitor&amp;graph=month">Month<a>
	<a class="navbar-item <?php print ($graph == 'year' ? 'is-active' : ''); ?>" href="?view=monitor&amp;graph=year">Year<a>
  </div>
</nav>
<img src="/beerlog-<?php print $graph;?>.png">
</div>