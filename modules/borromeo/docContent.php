<?php
/**
 * @file docContent.php
 * @brief Contains the document content model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Document content model class
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **subchapter** int(8): subchapter primary key value
 * - **revision** int(8): id of the current revision
 * - **last_edit_date** datetime: last_edit_date
 * - **active_pad** varchar(64): id of the active pad
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContent extends model {

  /**
   * @brief controller instance
   */
  private $_controller;

  /**
   * @brief Constructs a document content instance
   * @param int $id doc content identifier
   * 
   * @return document content instance
   */
  function __construct($id) {

    $this->_controller = new borromeoController();
    $this->_tbl_data = TBL_B_DOC_CONTENT;

    parent::__construct($id);

  }

  public function canManage() {
    
    $registry = registry::instance();
    $user = $registry->user;
    $controller = new borromeoController();

    $doc = $this->subchapter()->chapter()->doc();

    // user is document administrator
    if($controller->hasAdminDocPrivilege()) {
      return true;
    }

    // user is a document tutor
    if(preg_match("#\b".$user->id."\b#", $doc->tutor_users)) {
      return true;
    }

    return false;

  }

  public function canRevise() {

    $registry = registry::instance();
    $user = $registry->user;
    $controller = new borromeoController();

    $subchapter = $this->subchapter();
    $chapter = $subchapter->chapter();
    $doc = $chapter->doc();

    if($this->canManage()) {
      return true;
    }

    // subchapter privileges
    if($subchapter->author_users or $subchapter->author_groups) {
      $res = false;
      if($subchapter->author_users && preg_match("#\b".$user->id."\b#", $subchapter->author_users)) {
        $res = true;
      }
      foreach(explode(",", $user->groups) as $ugid) {
        if(preg_match("#\b".$ugid."\b#", $subchapter->author_groups)) {
          $res = true;
        }
      }
      return $res;
    }

    // chapter privileges
    if($chapter->author_users or $chapter->author_groups) {
      $res = false;
      if($chapter->author_users && preg_match("#\b".$user->id."\b#", $chapter->author_users)) {
        $res = true;
      }
      foreach(explode(",", $user->groups) as $ugid) {
        if(preg_match("#\b".$ugid."\b#", $chapter->author_groups)) {
          $res = true;
        }
      }
      return $res;
    }

    // doc privileges
    if($doc->author_users or $doc->author_groups) {
      $res = false;
      if($doc->author_users && preg_match("#\b".$user->id."\b#", $doc->author_users)) {
        $res = true;
      }
      foreach(explode(",", $user->groups) as $ugid) {
        if(preg_match("#\b".$ugid."\b#", $doc->author_groups)) {
          $res = true;
        }
      }
      return $res;
    }

    return false;

  }

  public static function createEmpty($subchapter_id) {

    $registry = registry::instance();

    $data = array(
      'subchapter' => $subchapter_id,
      'last_edit_date' => $registry->dtime->now('%Y-%m-%d %H:%i:%s')
    );

    return $registry->db->insert(TBL_B_DOC_CONTENT, $data);

  }

  /**
   * @brief Returns document content objects
   * @param array $opts associative array of options:
   *    - where: the where clause
   *    - order: the order clause
   *    - get_id: whether to return only the id
   * @return array of document content objects
   */
  public static function get($opts = array()) {

    $db = db::instance();
    $res = array();

    $where = gOpt($opts, 'where', '');
    $order = gOpt($opts, 'order', 'id');
    $get_id = gOpt($opts, 'get_id', false);

    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT, $where, $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = $get_id ? $row['id'] : new docContent($row['id']);
      }
    }

    return $res;

  }

  /**
   * @brief Content subchapter
   * @return subchapter object
   */
  public function subchapter() {

    require_once('subchapter.php');

    return new subchapter($this->subchapter);

  }

  /**
   * @brief Revision which has to be merged or rejected
   * @return the pending revision or null
   */
  public function pendingRevision() {

    require_once('docContentRevision.php');

    $res = docContentRevision::get(array('where' => "content='".$this->id."' && merged IS NULL"));
    if($res and count($res)) {
      return $res[0];
    }
    else {
      return new docContentRevision(null);
    }

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
    return $this->subchapter()->chapter()->doc()->path().DS.'content'.DS.$this->id;
  }

  /**
   * @brief Deletes a content
   * @return the result of the operation
   */
  public function delete() {

    if(!$this->canManage()) {
      error::raise403();
    }

    require_once('docContentRevision.php');

    // delete associated revisions
    $revisions = docContentRevision::get(array('where' => "content='".$this->id."'"));
    if(count($revisions)) {
      foreach($revisions as $revision) {
        $revision->delete();
      }
    }

    // delete associated notes
    require_once('docContentNote.php');
    $notes = docContentNote::getFromContent($this->id);
    foreach($notes as $note) {
      $note->delete();
    }

    // delete upload directory
    deleteDirectory($this->path());

    // delete content record
    return $this->deleteData();

  }

}

?>
