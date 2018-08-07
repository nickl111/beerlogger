<?php
/**
 * This is a class to produce various bits of HTML5 output
 */
class Page {
	
	public $str;
	protected $view;
	
	function __construct($view) {
		$this->view = $view;
	}
	
	/**
	 * Output a complete page
	 * @param string $title The page title
	 * @param string $content The content of the page (sans header, footer and menu)
	 * @return void
	 */
	function output($title, $content) {
		print $this->header($title);
		print $this->menu();
		print '<div class="column">'.$content.'</div>';
		print $this->footer();
	}
	
	/**
	 * Return the page header
	 * @param string $title The page title
	 * @return string HTML for the page header
	 */
	function header($title=false) {
		if(!$title) {
			$title = 'Beerlogger';
		}
		ob_start();
		include('templates/header.php');
		$output = ob_get_clean();
		return $output;
	}
	
	/**
	 * Return the page footer
	 * @return string HTML for the page footer
	 */
	function footer() {
		ob_start();
		include('templates/footer.php');
		$output = ob_get_clean();
		return $output;
	}
	
	/**
	 * Return HTML for an edit page
	 * @param object $o The object we're editing
	 * @return string HTML for the page
	 */
	function editObject($o) {
		$str = '<h2 class="title is-2">'.ucfirst(get_class($o)).'</h2><form method="POST">';
		foreach($o->fields as $name => $value) {
			if(in_array($name,$o->pk)){ continue; }	// don't allowing editing of any primary key fields
			$str .= $this->field($name, $value);
		}
		$str .= '<a class="button" href="?view='.get_class($o).'">Cancel</a>';
		foreach($o->pk as $k) {
			$str .= '<input type="hidden" name="field_'.$k.'" value="'.$o->fields[$k].'">';
		}
		$str .= '<input type="submit" class="button is-primary is-pulled-right" value="Save"><input type="hidden" name="do" value="save"></form>';
		return $str;
	}
	
	/**
	 * Return HTML for a field on an edit page
	 * @param string $name A value for the label and form name/id
	 * @param string $value A string for the input
	 * @return string HTML for the page
	 */
	function field($name, $value='') {
		$str = '<div class="field is-size-6"><label class="label" for="'.$name.'-input">'.$name.'</label>';
		$str .= '<div class="control"><input class="input" name="field_'.$name.'" id="'.$name.'-input" type="text" value="'.$value.'"></div></div>'."\n";
		return $str;
	}
	
	/**
	 * Return HTML for a list item
	 * @param string $view The name of the view we are in
	 * @param array $id An array of pk ids
	 * @param string $value What to show ($id will be used if omitted)
	 * @return string HTML
	 */
	function listItem($view, $id, $value='') {
		if(!$value) { $value = implode(', ',$id) ; }
		$str = '<tr><td><a href="?view='.$view.'&amp;do=edit&amp;pks='.implode(',',$id).'">'.$value.'</a></td><td><a href="?do=delete&amp;view='.$view.'&amp;pks='.implode(',',$id).'" class="button is-danger is-small">X</a></td></tr>';
		return $str;
	}
	
	/**
	 * A header and footer for the list
	 * @param string $content The string to wrap
	 * @return string HTML
	 */
	function listWrapper($content) {
		return '<div class="column"><table class="table is-striped"><tbody>'.$content."</tbody></table></div>\n";
	}
	
	/**
	 * The left hand menu
	 * @return string HTML
	 */
	function menu() {
		ob_start();
		include('templates/menu.php');
		$output = ob_get_clean();
		return $output;
	}
	
	/**
	 * The monitor page
	 * @param string $graph The active graph view
	 * @return string HTML
	 */
	function showMonitor($graph) {
		ob_start();
		include('templates/monitor.php');
		$output = ob_get_clean();
		return $output;
	}
	
	/**
	 * A button saying "new"
	 * @param string $view The view we're acting in
	 * @return string HTML
	 */
	function newButton($view) {
		$str = '<a class="button is-primary" href="?do=edit&amp;view='.$view.'">New</a>';
		return $str;
	}
}
?>