<?php
/**
 * @file chapter.php
 * @brief Contains the document chapter model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Document chapter model class
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **document** int(8): document primary key value
 * - **title** varchar: title
 * - **position** int(3): position
 * - **author_groups** varchar: comma separated list of system group ids with author functionality
 * - **author_users** varchar: comma separated list of system users ids with author functionality
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class chapter extends model {

  /**
   * @brief list of subchapter objects
   */
  private $_subchapters;

  /**
   * @brief Constructs a document chapter instance
   * @param int $id doc chapter identifier
   * 
   * @return document chapter instance
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CHAPTER;

    parent::__construct($id);

    $this->_subchapters = subchapter::get(array('get_id'=>true, 'where'=>"chapter='".$this->id."'"));

  }

  /**
   * @brief Gets the chapter subchapters ids
   * @return subchapter id
   */
  public function subchapters() {

    return $this->_subchapters;

  }

  public function doc() {

    return new doc($this->document);

  }

  /**
   * @brief Returns document chapter objects
   * @param array $opts associative array of options:
   *    - where: the where clause
   *    - order: the order clause
   *    - get_id: whether to return only the id
   * @return array of document chapter objects
   */
  public static function get($opts = array()) {

    $db = db::instance();
    $res = array();

    $where = gOpt($opts, 'where', '');
    $order = gOpt($opts, 'order', 'position');
    $get_id = gOpt($opts, 'get_id', false);

    $rows = $db->autoSelect('id', TBL_B_DOC_CHAPTER, $where, $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] =$get_id ? $row['id'] : new chapter($row['id']);
      }
    }

    return $res;

  }

  /**
   * @brief Defines the adminTable instance for the chapter
   * @param doc $doc the doc object
   * @return the adminTable instance
   */
  public static function adminTable($doc) {

    $s_fields = array(
      "document"=>array(
        "type"=>"constant",
        "value_type"=>'int',
        "value"=>$doc->id
      ),
      "author_groups"=>array(
        "type"=>"multicheck",
        "required"=>false,
        "value_type"=>'int',
        "table"=>TBL_SYS_GROUPS,
        "field"=>"label",
        "where"=>null,
        "order"=>"label"
      ),
      "author_users"=>array(
        "type"=>"multicheck",
        "required"=>false,
        "value_type"=>'int',
        "table"=>TBL_USERS,
        "field"=>"CONCAT(lastname, ' ', firstname)",
        "where"=>null,
        "order"=>"lastname, firstname"
      )
    );

    $at = new chapterAdminTable(TBL_B_DOC_CHAPTER, $doc, array("insertion"=>true));
    $at->setSpecialFields($s_fields);

    return $at;

  }

  /**
   * @brief Highest available position for the chapter
   * @param doc $doc the doc object
   * @return the highest position available
   */
  public static function getNextPosition($doc) {

    $db = db::instance();
    $rows = $db->autoSelect('position', TBL_B_DOC_CHAPTER, "document='".$doc->id."'", 'position DESC', array(0, 1));
    if($rows and count($rows)) {
      return $rows[0]['position'] + 1;
    }
    else {
      return 1;
    }
  }

  /**
   * @brief reorders the chapters
   * @param array $data the serialized order object passed by js
   * @return boolean result of the operation
   */
  public static function applyOrder($data) {

    $db = db::instance();

    $ids = implode(',', $data);

    $query = 'UPDATE '.TBL_B_DOC_CHAPTER.' '.
             'SET position = CASE id ';
    foreach($data as $k => $id) {
      $query .= 'WHEN '.$id.' THEN '.($k + 1).' ';
    }
    $query .= 'END '.
              'WHERE id IN ('.$ids.')';

    return $db->executeQuery($query);

  }

  /**
   * @brief Deletes a chapter and all its subchapters
   * @return the result of the operation
   */
  public function delete() {

    if(!$this->doc()->canManage()) {
      error::raise403();
    }

    $doc_id = $this->doc()->id;
    $id = $this->id;

    // delete all subchapters
    foreach($this->_subchapters as $subchapter_id) {
      $subchapter = new subchapter($subchapter_id);
      $subchapter->delete();
    }

    $this->deleteData();
    deleteDirectory(ABS_UPLOAD.DS.'doc'.DS.$doc_id.DS.$id);

  }

}

?>
