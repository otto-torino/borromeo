<?php
/**
 * @file docContentPublicNoteAdminTable.php
 * @brief Contains the docContentPublicNoteAdminTable class
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Extension of the core adminTable class for the docContentPublicNote model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContentPublicNoteAdminTable extends adminTable {

  /**
   * @brief Class constructor
   * @param string $table @see adminTable
   * @param array $opts @see adminTable
   */
  function __construct($table, $opts=null) {
    parent::__construct($table, $opts);
  }

	/**
	 * @brief Returns the table of the records to be displayed 
	 * 
	 * @param array $fields_names the fields to be displayed 
	 * @return the table
	 */
	protected function viewTable($fields_names, $records) {

		$order = cleanInput('get', 'order', 'string');

		$tot_fk = count($this->_fkeys);
		$tot_sf = count($this->_sfields);
		$tot_pf = count($this->_pfields);
		$tot_ff = count($this->_filter_fields);

    $toggle = "<span class=\"uncheck_all_toggle\" onclick=\"toggleAllChecks($('atbl_form'), this)\"></span>";
		$heads = ($this->_edit_deny != 'all' || $this->_export) ? array("0"=>$toggle) : array();
		foreach($fields_names as $fn) {
			if(!$this->_changelist_fields || in_array($fn, $this->_changelist_fields)) {
				$ord = $order == $fn." ASC" ? $fn." DESC" : $fn." ASC";

				if($order == $fn." ASC") {
					$jsover = "$(this).getNext('img').setProperty('src', '$this->_arrow_down_path')";
					$jsout = "$(this).getNext('img').setProperty('src', '$this->_arrow_up_path')";
					$a_style = "visibility:visible";
					$apath = $this->_arrow_up_path;
				}
				elseif($order == $fn." DESC") {
					$jsover = "$(this).getNext('img').setProperty('src', '$this->_arrow_up_path')";
					$jsout = "$(this).getNext('img').setProperty('src', '$this->_arrow_down_path')";
					$js = "$(this).getNext('img').getNext('img').setStyle('visibility', 'visible')";
					$a_style = "visibility:visible";
					$apath = $this->_arrow_down_path;
				}
				else {
					$js = '';
					$jsover = "$(this).getNext('img').setStyle('visibility', 'visible')";
					$jsout = "$(this).getNext('img').setStyle('visibility', 'hidden')";
					$a_style = "visibility:hidden";
					$apath = $this->_arrow_up_path;
				}

				$link = preg_replace("#/p/\d+/#", "/", $_SERVER['REQUEST_URI']);
				$link = preg_replace("#\?.*#", "", $link);

        $label = isset($this->_fields_labels[$fn]['label']) ? $this->_fields_labels[$fn]['label'] : __($fn);

				$head_t = anchor($link."?order=$ord", $label, array('over'=>$jsover, 'out'=>$jsout));
        $heads[] = $head_t." <img src=\"$apath\" alt=\"down\" style=\"$a_style\" />";
			}
		}

    $heads[] = __('document');
    $heads[] = __('chapter');
    $heads[] = __('subchapter');

		$rows = array();
		foreach($records as $r) {
			$input = "<input type=\"checkbox\" name=\"f[]\" value=\"".$r[$this->_primary_key]."\" />";
			if($tot_fk) $r = $this->parseForeignKeys($r);
			if($tot_sf) $r = $this->parseSpecialFields($r);
			if($tot_pf) $r = $this->parsePluginFields($r);
			$r = $this->parseDateFields($r);

			$pk = $r[$this->_primary_key];
			if(!in_array($this->_primary_key, $fields_names)) {
				// remove primary key
				array_shift($r);
			}
			if($this->_edit_deny=='all' && !$this->_export) $row = $r;
			elseif(is_array($this->_edit_deny) && in_array($pk, $this->_edit_deny)) $row = array_merge(array(""), $r);
      else $row = array_merge(array($input), $r);

      $content = new docContent($r['content']);
      $subchapter = $content->subchapter();
      $chapter = $subchapter->chapter();
      $doc = $chapter->doc();

      $row = array_merge($row, array(htmlVar($doc->title), htmlVar($chapter->title), htmlVar($subchapter->title)));

      $rows[] = $row;
		}
		

		$this->_view->setTpl('table');
		$this->_view->assign('class', 'generic wide');
		$this->_view->assign('caption', __("RecordInTable")." ".$this->_table);
		$this->_view->assign('heads', $heads);
		$this->_view->assign('rows', $rows);

		return $this->_view->render();

	}

}


