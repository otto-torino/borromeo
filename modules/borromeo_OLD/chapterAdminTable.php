<?php
/**
 * @file chapterAdminTable.php
 * @brief Contains the chapterAdminTable class
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Extension of the core adminTable class for the chapter model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class chapterAdminTable extends adminTable {

  private $_doc;

  /**
   * @brief Class constructor
   * @description gets an additional parameter, doc
   * @param string $table @see adminTable
   * @param doc $doc the doc object
   * @param array $opts @see adminTable
   */
  function __construct($table, $doc, $opts=null) {
    $this->_doc = $doc;
    parent::__construct($table, $opts);
  }

  /**
   * @brief Extends the parent method to calculate the position field value
   * @see adminTable::formElement()
   */
  protected function formElement($myform, $fname, $field, $id, $opts=null) {

    $id_f = preg_replace("#\s#", "_", $id); // replace spaces with '_' in form names as POST do itself
    $required = $field['null']=='NO' ? true : false;

    if(isset($opts['value'])) {
      $value = gOpt($opts, 'value', '');
    }
    else {
      $records = $this->_registry->db->autoSelect("*", $this->_table, $this->_primary_key."='$id'", null);
      $value = count($records) ? $records[0][$fname] : chapter::getNextPosition($this->_doc);
    }

    if($fname === 'position') {
      return $myform->hidden($fname.'_'.$id, $value);
    }
    else {
      return parent::formElement($myform, $fname, $field, $id, $opts);
    }

  }

}

?>
