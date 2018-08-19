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
		$str = '<h2 class="title is-2">'.ucfirst(get_class($o)).'</h2><form method="POST" action="?">';
		foreach($o->fields as $name => $value) {
			if(in_array($name,$o->pk)){ continue; }	// don't allowing editing of any primary key fields
			$str .= $this->field($name, $value);
		}
		$str .= '<a class="button" href="?view='.get_class($o).'">Cancel</a>';
		foreach($o->pk as $k) {
			$str .= '<input type="hidden" name="field_'.$k.'" value="'.$o->fields[$k].'">';
		}
		$str .= '<input type="submit" class="button is-primary is-pulled-right" value="Save"><input type="hidden" name="do" value="save"><input type="hidden" name="view" value="'.get_class($o).'"><input type="hidden" name="pks" value="'.implode(',', $o->getPKValues()).'"></form>';
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
		$str = '<tr><td><a href="?view='.$view.'&amp;do=edit&amp;pks='.implode(',',$id).'">'.$value.'</a></td><td><a class="open-modal button is-danger is-small" data-do="delete" data-view="'.$view.'" data-pks="'.implode(',',$id).'" data-modal-id="#my-modal">X</a></td></tr>';
		return $str;
	}
	
	/**
	 * A header and footer for the list
	 * @param string $content The string to wrap
	 * @return string HTML
	 */
	function listWrapper($content) {
		$s = '<div class="column"><table class="table is-striped"><tbody>'.$content."</tbody></table></div>\n";
		$s .= '<div id="my-modal" class="modal" data-do="" data-view="" data-pks="">
  <div class="modal-background" data-modal-id="#my-modal"></div>
  <div class="modal-content">
    <div class="box">
          <div class="content">
            <p>
              Are you sure you want to delete <span id="whatamideleting">this</span>?
            </p>
          </div>
		  <button class="button is-danger delete-modal" data-modal-id="#my-modal">Yes, Delete it</button>
		 <button class="button open-modal" data-modal-id="#my-modal">Cancel</button>
    </div>
  </div>
</div>
<script>
function toggleModalClasses(event) {
    var modalId = event.currentTarget.dataset.modalId;
    var modal = $(modalId);
    modal.toggleClass(\'is-active\');
    $(\'html\').toggleClass(\'is-clipped\');
	$(\'#my-modal\').data.pks = event.currentTarget.dataset.pks;
	$(\'#my-modal\').data.view = event.currentTarget.dataset.view;
	$(\'#my-modal\').data.do = event.currentTarget.dataset.do;
  };
function doDelete(event) {
	var href = "?do="+$(\'#my-modal\').data.do+"&view="+$(\'#my-modal\').data.view+"&pks="+$(\'#my-modal\').data.pks;
	window.location.href = href;
};

$(\'.open-modal\').click(toggleModalClasses);

$(\'.delete-modal\').click(doDelete);</script>';
		return $s;
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