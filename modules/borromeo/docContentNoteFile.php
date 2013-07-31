<?php
/**
 * @file docContentNoteFile.php
 * @brief Contains the docContentNoteFile model class.
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief docContentNoteFile model class.
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
class docContentNoteFile extends model {

  /**
   * @brief allowed extensions
   */
  public static $extensions = array('jpg', 'jpeg', 'png', 'tif', 'gif', 'pdf', 'doc', 'docx', 'odt', 'xls', 'csv', 'ppt');

  /**
   * @brief Class constructor
   * @param int $id the image identifier
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CONTENT_NOTE_FILE;

    parent::__construct($id);

  }

  /**
   * @brief Get note objects tied to the given content
   * @param int $content_id the content identifier
   * @return array of note objects
   */
  public static function getFromNote($note_id, $opts = array()) {

    $db = db::instance();

    $order = gOpt($opts, 'order', 'id');

    $res = array();
    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT_NOTE_FILE, "note='".$note_id."'", $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new docContentNoteFile($row['id']);
      }
    }

    return $res;
  }

  public static function saveFiles($myform, $note) {

    $path = $note->content()->path();
    $path = substr($path, -1) == DS ? $path : $path.DS;

    if(!is_dir($path)) mkdir($path, 0755, true);

    $titles = cleanInputArray('post', 'file-title', 'string');
    $captions = cleanInputArray('post', 'file-caption', 'string');

    foreach($_FILES['file']['name'] as $k => $fname) {
      if($fname) {

        $tmp_file = $_FILES['file']['tmp_name'][$k];
        $init_name = $fname;
        $n_name = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $init_name);

        $p_files = scandir($path);
        $i=1;
        while(in_array($n_name, $p_files)) { 
          $n_name = substr($n_name, 0, strrpos($n_name, '.')+1).$i.substr($n_name, strrpos($n_name, '.')); $i++; 
        }
        $nfile = $n_name;

        if(!$myform->checkExtension($nfile, self::$extensions) || preg_match('#%00#', $nfile)) {
          $nfile = ''; // do not upload
        }
      }
      else {
        $nfile = '';
        $tmp_file = '';
      }

      $upload = !empty($nfile);

      if($upload) {
        $file = $path.$nfile;
        $res = move_uploaded_file($tmp_file, $file) ? true : false;

        // query
        if($res) {
          $doc_note= new docContentNoteFile(null);
          $doc_note->note = $note->id;
          $doc_note->title = $titles[$k];
          $doc_note->description = $captions[$k];
          $doc_note->filename = $nfile;
          $doc_note->path = relativePath($file);
          $doc_note->saveData();
        }

      }
    }
  }

  /**
   * @brief Delete a note file
   * @return the result of the operation
   */
  public function delete() {

    $note = new docContentNote($this->note);

    $path = $note->content()->path();
    $path = substr($path, -1) == DS ? $path : $path.DS;

    if(@unlink($path.$this->filename)) {
      $this->deleteData();
      return true;
    }

    return false;

  }


}
