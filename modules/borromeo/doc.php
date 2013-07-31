<?php
/**
 * @file doc.php
 * @brief Contains the document model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Document model class
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **creation_date** datetime: document creation date
 * - **last_edit_date** datetime: document last edit date
 * - **ctgs** varchar: comma separated list of categories ids
 * - **title** varchar: document title
 * - **abstract** varchar: document abstract
 * - **tags** varchar: document tags
 * - **tutor_groups** varchar: comma separated list of system group ids with tutor functionality
 * - **tutor_users** varchar: comma separated list of system users ids with tutor functionality
 * - **author_groups** varchar: comma separated list of system group ids with author functionality
 * - **author_users** varchar: comma separated list of system users ids with author functionality
 * - **published** boolean: whether the document is public or not
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class doc extends model {

  /**
   * @brief List of chapter objects
   */
  private $_chapters;

  /**
   * @brief Constructs a document instance
   * @param int $id doc identifier
   * 
   * @return document instance
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC;

    parent::__construct($id);

    $this->_chapters = chapter::get(array('get_id'=>true, 'where'=>"document='".$this->id."'"));

  }

  /**
   * @brief Gets the document chapters ids
   * @return chapters id
   */
  public function chapters() {
    
    return $this->_chapters;

  }

  /**
   * @brief Checks if the user can view the document
   * @ return true if can view it false otherwise
   */
  public function canView() {
    $registry = registry::instance();
    $user = $registry->user;
    $controller = new borromeoController();

    // user is document administrator or can view all documents
    if($controller->hasAdminDocPrivilege() or $this->published) {
      return true;
    }

    // user is a tutor or an author
    if(preg_match("#\b".$user->id."\b#", $this->tutor_users) or preg_match("#\b".$user->id."\b#", $this->author_users)) {
      return true;
    }

    // user belong to a tutor or author group
    foreach(explode(",", $user->groups) as $ugid) {
      if(preg_match("#\b".$ugid."\b#", $this->tutor_groups) or preg_match("#\b".$ugid."\b#", $this->author_groups)) {
        return true;
      }
    }

    return false;

  }

  /**
   * @brief Checks if the user can manage the document (tutor)
   * @ return true if can view it false otherwise
   */
  public function canManage() {

    $registry = registry::instance();
    $user = $registry->user;
    $controller = new borromeoController();

    // user is document administrator
    if($controller->hasAdminDocPrivilege()) {
      return true;
    }

    // user is a tutor
    if(preg_match("#\b".$user->id."\b#", $this->tutor_users)) {
      return true;
    }

    // user belong to a tutor group
    foreach(explode(",", $user->groups) as $ugid) {
      if(preg_match("#\b".$ugid."\b#", $this->tutor_groups)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @brief Defines the adminTable instance for the chapter
   * @param borromeoController $controller the controller object
   * @return the adminTable instance
   */
  public static function adminTable($controller) {

    $registry = registry::instance();

    $s_fields = array(
      "ctgs"=>array(
        "type"=>"multicheck",
        "required"=>true,
        "value_type"=>'int',
        "table"=>TBL_B_DOC_CTG,
        "field"=>"name",
        "where"=>null,
        "order"=>"name"
      ),
      "tutor_groups"=>array(
        "type"=>"multicheck",
        "required"=>false,
        "value_type"=>'int',
        "table"=>TBL_SYS_GROUPS,
        "field"=>"label",
        "where"=>null,
        "order"=>"label"
      ),
      "tutor_users"=>array(
        "type"=>"multicheck",
        "required"=>false,
        "value_type"=>'int',
        "table"=>TBL_USERS,
        "field"=>"CONCAT(lastname, ' ', firstname)",
        "where"=>null,
        "order"=>"lastname, firstname"
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
      ),
      'creation_date'=>array(
        'type' => 'datetime',
        'autonow' => false,
        'autonow_add' => true,
      ),
      'last_edit_date'=>array(
        'type' => 'datetime',
        'autonow' => true,
        'autonow_add' => true,
      ),
      "published"=>array(
        "type"=>"bool",
        "required"=>true,
        "true_label"=>__("yes"),
        "false_label"=>__("no")	
      )
    );

    if(!$controller->hasAdminDocPrivilege()) {
      $permission = 'create';
      $edit_deny = self::get(array(
        'where' => "id NOT IN(SELECT id FROM ".TBL_B_DOC." WHERE tutor_users REGEXP '[[:<:]]".$registry->user->id."[[:>:]]')",
        'get_id' => true
      ));
    }
    else {
      $permission = 'manage';
      $edit_deny = '';
    }

    $at = new docAdminTable(TBL_B_DOC, $permission, array("insertion"=>true, "edit_deny"=>$edit_deny, 'cls_callback_delete' => 'doc', 'mth_callback_delete' => 'deleteMore'));
    $at->setSpecialFields($s_fields);

    return $at;

  }

  /**
   * @brief Returns document objects
   * @param array $opts associative array of options:
   *    - where: the where clause
   *    - order: the order clause
   *    - get_id: whether to return only the id
   * @return array of document objects
   */
  public static function get($opts = array()) {

    $db = db::instance();
    $res = array();

    $where = gOpt($opts, 'where', '');
    $order = gOpt($opts, 'order', '');
    $get_id = gOpt($opts, 'get_id', false);

    $rows = $db->autoSelect('id', TBL_B_DOC, $where, $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] =$get_id ? $row['id'] : new doc($row['id']);
      }
    }

    return $res;

  }

  /**
   * @brief Update the last edit date
   */
  public function updateLastEdit() {

    $this->last_edit_date = $this->_registry->dtime->now('%Y-%m-%d %H:%i:%s');
    $this->saveData();

  }

  /**
   * @brief Content upload folder path
   * @return path
   */
  public function path() {
    return ABS_UPLOAD.DS.'doc'.DS.$this->id;
  }

  /**
   * @brief Deletes a document
   */
  public function delete() {

    if(!$this->id) {
      error::raise(404);
    }

    $path = $this->path();

    $controller = new borromeoController();

    // user is not document administrator
    if(!$controller->hasAdminDocPrivilege()) {
      error:raise403();
    }

    foreach($this->_chapters as $chapter_id) {
      $chapter = new chapter($chapter_id);
      $chapter->delete();
    }

    $this->deleteData();
    deleteDirectory($path);

  }

  /**
   * @brief Deletes a set of documents
   * @param registry $registry the registry singleton
   * @param array $docs_id the array of id of documents that have to be deleted
   */
  public static function deleteMore($redistry, $docs_id) {

    foreach($docs_id as $doc_id) {
      $doc = new doc($doc_id);
      $doc->delete();
    }

  }

}

?>
