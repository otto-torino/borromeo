<?php
/**
 * @file docContentNote.php
 * @brief Contains the docContentNote model class.
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief docContentNote model class.
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **user** int(8): user primary key value
 * - **content** int(8): content primary key value
 * - **title** varchar: title
 * - **text** text: text
 * - **creation_date** datetime: creation date
 * - **last_edit_date** datetime: last edit date

 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContentNote extends model {

  /**
   * @brief Class constructor
   * @param int $id the image identifier
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CONTENT_NOTE;

    parent::__construct($id);

  }

  public function content() {

    return new docContent($this->content);
  }

  public function files() {

    $files = docContentNoteFile::getFromNote($this->id);

    return $files;

  }

  /**
   * @brief Get note objects tied to the given content
   * @param int $content_id the content identifier
   * @return array of note objects
   */
  public static function getFromContent($content_id, $opts = array()) {

    $db = db::instance();

    $order = gOpt($opts, 'order', 'last_edit_date DESC');

    $res = array();
    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT_NOTE, "content='".$content_id."'", $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new docContentNote($row['id']);
      }
    }

    return $res;
  }

  /**
   * @brief Deletes a note and all associated files
   */
  public function delete() {

    foreach($this->files() as $file) {
      $file->delete();
    }

    return $this->deleteData();

  }

}
