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
 * - **revision** int(8): revision primary key value
 * - **title** varchar: title
 * - **caption** text: caption
 * - **filename** varchar: file name
 * - **path** varchar: file path
 * - **thumb_path** varchar: thumb file path

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

    require_once('docContent.php');

    return new docContent($this->content);
  }

  public function files() {

    require_once('docContentNoteFile.php');

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

    $order = gOpt($opts, 'order', 'id');

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
