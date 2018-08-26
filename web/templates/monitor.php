
	<div class="tabs">
		<ul>
			<li<?php print ($graph == 'hour' ? ' class="is-active"' : ''); ?>><a href="?view=monitor&amp;graph=hour">Hour</a></li>
			<li<?php print ($graph == 'day' ? ' class="is-active"' : ''); ?>><a href="?view=monitor&amp;graph=day">Day</a></li>
			<li<?php print ($graph == 'week' ? ' class="is-active"' : ''); ?>><a href="?view=monitor&amp;graph=week">Week</a></li>
			<li<?php print ($graph == 'month' ? ' class="is-active"' : ''); ?>><a href="?view=monitor&amp;graph=month">Month</a></li>
			<li<?php print ($graph == 'year' ? ' class="is-active"' : ''); ?>><a href="?view=monitor&amp;graph=year">Year</a></li>
		</ul>
	</div>
	<div class="content">
	<img src="/beerlog-<?php print $graph;?>.png">
</div>