<?php
/**
 * @file subchapter.php
 * @brief Contains the document subchapter model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Document subchapter model class
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
class subchapter extends model {

  private $_content;

  /**
   * @brief Constructs a document subchapter instance
   * @param int $id doc subchapter identifier
   * 
   * @return document subchapter instance
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_SUBCHAPTER;

    parent::__construct($id);

    require_once('docContent.php');

    $contents = docContent::get(array('where'=>"subchapter='".$this->id."'"));
    $this->_content = ($contents and count($contents)) ? $contents[0] : null;

  }

  /**
   * @brief Subchapter content
   * @return subchapter content
   */
  public function content() {
    return $this->_content;
  }

  /**
   * @brief Parent chapter
   * @return chapter object
   */
  public function chapter() {

    require_once('chapter.php');

    return new chapter($this->chapter);

  }

  /**
   * @brief Returns document subchapter objects
   * @param array $opts associative array of options:
   *    - where: the where clause
   *    - order: the order clause
   *    - get_id: whether to return only the id
   * @return array of document subchapter objects
   */
  public static function get($opts = array()) {

    $db = db::instance();
    $res = array();

    $where = gOpt($opts, 'where', '');
    $order = gOpt($opts, 'order', 'position');
    $get_id = gOpt($opts, 'get_id', false);

    $rows = $db->autoSelect('id', TBL_B_DOC_SUBCHAPTER, $where, $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] =$get_id ? $row['id'] : new subchapter($row['id']);
      }
    }

    return $res;

  }

  /**
   * @brief Defines the adminTable instance for the chapter
   * @param chapter $chapter the chapter object
   * @return the adminTable instance
   */
  public static function adminTable($chapter) {

    require_once(dirname(__FILE__).DS.'subchapterAdminTable.php');

    $s_fields = array(
      "chapter"=>array(
        "type"=>"constant",
        "value_type"=>'int',
        "value"=>$chapter->id
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

    $f_keys = array(
      'chapter' => array(
        "table"=>TBL_B_DOC_CHAPTER,
        "field"=>"title",
        "where"=>"document='".$chapter->document."'",
        "order"=>"position"
      )
    );

    $at = new subchapterAdminTable(TBL_B_DOC_SUBCHAPTER, $chapter, array("insertion"=>true));
    $at->setSpecialFields($s_fields);
    $at->setForeignKeys($f_keys);

    return $at;

  }

  /**
   * @brief Highest available position for the subchapter
   * @param chapter $chapter the chapter object
   * @return the highest position available
   */
  public static function getNextPosition($chapter) {

    $db = db::instance();
    $rows = $db->autoSelect('position', TBL_B_DOC_SUBCHAPTER, "chapter='".$chapter->id."'", 'position DESC', array(0, 1));
    if($rows and count($rows)) {
      return $rows[0]['position'] + 1;
    }
    else {
      return 1;
    }
  }

  /**
   * @brief reorders the subchapters
   * @param array $data the serialized order object passed by js
   * @param array $slists the list of chapters sublists
   * @return boolean result of the operation
   */
  public static function applyOrder($data, $slists) {

    $db = db::instance();

    $ids = array();
    $when_p = array();
    $when_c = array();

    if(!is_array($data[0])) {
      $data = array($data);
    }

    foreach($data as $key => $d) {
      if(!is_array($d)) {
        $d = array();
      }
      $ids = array_merge($ids, $d);
      foreach($d as $k => $id) {
        $when_p[] = 'WHEN '.$id.' THEN '.($k + 1).' ';
        $when_c[] = 'WHEN '.$id.' THEN '.$slists[$key].' ';
      }
    }
    $ids = implode(',', $ids);

    $query = 'UPDATE '.TBL_B_DOC_SUBCHAPTER.' '.
             'SET position = CASE id ';
    foreach($when_p as $wp) {
      $query .= $wp;
    }
    $query .= 'END, '.
             'chapter = CASE id ';
    foreach($when_c as $wc) {
      $query .= $wc;
    }
    $query .= 'END '.
              'WHERE id IN ('.$ids.')';

    return $db->executeQuery($query);

  }

  /**
   * @brief Deletes a subchapter and all associated contents
   */
  public function delete() {

    if(!$this->chapter()->doc()->canManage()) {
      error::raise403();
    }

    // delete associated content (will delete all revisions and notes)
    if(!is_null($this->_content) && $this->_content->id) {
      $this->_content->delete();
    }

    // update document last edit
    $this->chapter()->doc()->updateLastEdit();

    $this->deleteData();

  }


}

?>
