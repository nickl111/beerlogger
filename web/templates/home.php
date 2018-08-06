
	<div class="hero">
		<h1 class="title">Session <?php print $s->fields['name'];?> in progress</h1>
	
	</div>
	<div class="tile is-ancestor" style="margin-top: 2em">
		<div class="tile is-8 is-vertical">
			<div class="tile">
				<div class="tile is-parent">
					<article class="tile is-child box">
						<p class="subtitle">Temperature</p>
						<div class="title">99 &deg;C</div>
					</article>
				</div>
				<div class="tile is-parent">
					<article class="tile is-child box">
						<p class="subtitle">Activity</p>
						<div class="title">High</div>
					</article>
				</div>
			</div>
			<div class="tile is-parent">
				<article class="tile box is-child">
					<div class="ct-chart ct-perfect-fourth"></div>
				</article>
			</div>
			<div class="tile is-parent">
				<div class="is-child has-text-centered container"><a class="button is-info is-large is-centered" href="?view=session&amp;do=endSession">Bottle!</a></div>
			</div>
		</div>
	</div>
	<?php
	$d = $s->getData();
	foreach($d as $drow){
		// we need to bin the data here for the chart.
	}
	?>
<script language="javascript">
	var data = {
	// A labels array that can contain any sort of values
	labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
	// Our series array that contains series objects or in this case series data arrays
	series: [
		[5, 2, 4, 2, 0]
	]
	};
	
	// Create a new line chart object where as first parameter we pass in a selector
	// that is resolving to our chart container element. The Second parameter
	// is the actual data object.
	new Chartist.Line('.ct-chart', data);
</script>