<?php
/**
 * @file docContentPublicNote.php
 * @brief Contains the docContentPublicNote model class.
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief docContentPublicNote model class.
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **content** int(8): content primary key value
 * - **title** varchar: title
 * - **text** varchar: note text
 * - **creation_date** datetime: creation date
 * - **published** bool: whether the note is published or not

 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContentPublicNote extends model {

  /**
   * @brief Class constructor
   * @param int $id the image identifier
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CONTENT_PUBLIC_NOTE;

    parent::__construct($id);

  }

  public function content() {

    return new docContent($this->content);
  }

  /**
   * @brief Get note objects tied to the given content
   * @param int $content_id the content identifier
   * @return array of note objects
   */
  public static function getFromContent($content_id, $opts = array()) {

    $db = db::instance();

    $order = gOpt($opts, 'order', 'creation_date DESC');

    $res = array();
    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT_PUBLIC_NOTE, "content='".$content_id."' AND published='1'", $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new docContentPublicNote($row['id']);
      }
    }

    return $res;
  }

  /**
   * @brief Deletes a note and all associated files
   */
  public function delete() {

    return $this->deleteData();

  }

}
