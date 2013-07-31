<?php
/**
 * @file docContentRevision.php
 * @brief Contains the document content revision model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Document content revision model class
 *
 * Model fields:
 * - **id** int(8): primary key
 * - **user** int(8): user who creates the revision
 * - **content** int(8): content id
 * - **last_edit_date** datetime: last edit date
 * - **text** text: text
 * - **merged** int(1): whether the revision has been merged in the document or not
 * - **merged_user** int(8): the user id who decided to merge or not the revision
 * - **merged_date** datetime: datetime when the revision has been merged or rejected
 * - **merged_comment** text: a comment to the merge/reject action
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContentRevision extends model {

  /**
   * @brief controller instance
   */
  private $_controller;

  /**
   * @brief Constructs a document content revision instance
   * @param int $id doc content revision identifier
   * 
   * @return document content instance
   */
  function __construct($id) {

    $this->_controller = new borromeoController();
    $this->_tbl_data = TBL_B_DOC_CONTENT_REVISION;

    parent::__construct($id);

  }

  public function media() {
    return $this->_media;
  }

  public function canMerge() {

    $registry = registry::instance();
    $user = $registry->user;

    $doc = $this->content()->subchapter()->chapter()->doc();

    // user is document administrator
    if($this->_controller->hasAdminDocPrivilege()) {
      return true;
    }

    // user is a document tutor
    if(preg_match("#\b".$user->id."\b#", $doc->tutor_users)) {
      return true;
    }

    return false;

  }

  public function canEdit() {

    $registry = registry::instance();
    $user = $registry->user;

    if($this->canMerge()) {
      return true;
    }

    if($user->id == $this->user) {
      return true;
    }

    return false;

  }

  /**
   * @brief Returns document content revision objects
   * @param array $opts associative array of options:
   *    - where: the where clause
   *    - order: the order clause
   *    - get_id: whether to return only the id
   * @return array of document content revision objects
   */
  public static function get($opts = array()) {

    $db = db::instance();
    $res = array();

    $where = gOpt($opts, 'where', '');
    $order = gOpt($opts, 'order', 'id');
    $get_id = gOpt($opts, 'get_id', false);

    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT_REVISION, $where, $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = $get_id ? $row['id'] : new docContentRevision($row['id']);
      }
    }

    return $res;

  }

  /**
   * @brief Content
   * @return content object
   */
  public function content() {

    return new docContent($this->content);

  }

  /**
   * @brief Update the last edit date
   */
  public function updateLastEdit() {

    $this->last_edit_date = $this->_registry->dtime->now('%Y-%m-%d %H:%i:%s');
    $this->saveData();

  }

  /**
   * @brief Revision upload folder path
   * @return path
   */
  public function path() {
    return $this->content()->subchapter()->chapter()->doc()->path().DS.'revision'.DS.$this->id;
  }

  /**
   * @brief Deletes a revision
   */
  public function delete() {

    if(!$this->content()->subchapter()->chapter()->doc()->canManage()) {
      error::raise403();
    }

    // delete db record
    $this->deleteData();

  }
}

?>
