<?php
/**
 * @file docImage.php
 * @brief Contains the docContentImage model class.
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

require_once('interface.docContentMedia.php');

/**
 * @ingroup borromeo
 * @brief docContentImage model class.
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **revision** int(8): revision primary key value
 * - **caption** text: caption
 * - **filename** varchar: file name
 * - **path** varchar: file path
 * - **thumb_path** varchar: thumb file path

 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContentImage extends model implements DocContentMedia {

  /**
   * @brief thumb height
   */
  private static $_thumb_height = 80;

  /**
   * @brief allowed extensions
   */
  private static $_extensions = array('jpg', 'jpeg', 'png', 'tif', 'gif');

  /**
   * @brief Class constructor
   * @param int $id the image identifier
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CONTENT_IMAGE;

    parent::__construct($id);

  }

  /**
   * @brief Get image objects tied to the given revision
   * @param int $revision_id the revision identifier
   * @return array of image objects
   */
  public static function getFromRevision($revision_id) {

    $db = db::instance();
    $res = array();
    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT_IMAGE, "revision='".$revision_id."'", 'id');
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new docContentImage($row['id']);
      }
    }

    return $res;
  }

  /**
   * @brief Visualization of the images tied to the revision
   * @param docContentRevision $revision the revision object
   */
  public static function revisionTab($revision) {

    $images = self::getFromRevision($revision->id);

    $view = new view();
    $view->setTpl('borromeo_doc_image_tab');
    $view->assign('revision', $revision);
    $view->assign('images', $images);

    return $view->render();

  }

  /**
   * @brief Form to add/edit images of the revision
   * @param form $form the form object
   * @param docContentRevision $revision the revision object
   */
  public static function revisionForm($form, $revision) {

    $rows = array();
    $images = self::getFromRevision($revision->id);
    foreach($images as $image) {
      $input_del = $form->checkbox('del_image[]', false, $image->id, null);
      $image_preview = "<img src=\"".$image->thumb_path."\" alt=\"".jsVar($image->title)."\" />";
      $rows[] = array($input_del, $image_preview, htmlVar($image->title), htmlVar($image->caption));
    }

    if(count($images)) {
      $view = new view();
      $view->setTpl('table');
      $view->assign('class', 'generic');
      $view->assign('caption', '');
      $view->assign('heads', array(__('delete'), __('image'), __('title'), __('caption')));
      $view->assign('rows', $rows);
      $delete_images = $view->render();
    }
    else {
      $delete_images = '';
    }

    $onchange = "onchange=\"addImageFieldset.call(this)\"";
    $form_add_image = $form->cinput_file('image-file[]', '', __('file'), array('js'=>$onchange));
    $form_add_image .= $form->cinput('image-title[]', 'text', '', __('title'), null);
    $form_add_image .= $form->ctextarea('image-caption[]', '', __('caption'), null);
    $form_add_image_fieldset = $form->fieldset(__('addImage'), $form_add_image);

    $view = new view();
    $view->setTpl('borromeo_doc_revision_form_image');
    $view->assign('delete_images', $delete_images);
    $view->assign('form_add_image_fieldset', $form_add_image_fieldset);

    return $view->render();

  }

  /**
   * @brief Copy all images from one revision to another
   * @param int $from_revision_id the from revision identifier
   * @param int $to_revision_id the to revision identifier
   * @param array $del_image list of images id which shouldn't be copied
   */
  public static function copyFromToRevision($from_revision_id, $to_revision_id, $del_image) {

    $db = db::instance();

    $from_images = self::getFromRevision($from_revision_id);
    foreach($from_images as $image) {
      if(!in_array($image->id, $del_image)) {
        $image_copy = new docContentImage(null);
        $image_copy->revision = $to_revision_id;
        $image_copy->title = $db->escapeString($image->title);
        $image_copy->caption = $db->escapeString($image->caption);
        $image_copy->filename = $image->filename;
        $image_copy->path = $image->path;
        $image_copy->thumb_path = $image->thumb_path;
        $image_copy->saveData();
      }
    }

  }

  /**
   * @brief Save images for the revision
   */
  public static function saveRevision($revision, $starting_revision) {

    $edit = $revision->id != $starting_revision->id ? false : true;
    $del_image = cleanInputArray('post', 'del_image', 'int');

    // if starting revision is different from revision copy all not deleted images
    if(!$edit) {
      self::copyFromToRevision($starting_revision->id, $revision->id, $del_image);
    }
    else {
      // delete images
      if(count($del_image)) {
        foreach($del_image as $image_id) {
          $image = new docContentImage($image_id);
          $image->delete(true);
        }
      }
    }


    $path = $revision->path();
    $path = substr($path, -1) == DS ? $path : $path.DS;

    if(!is_dir($path)) mkdir($path, 0755, true);

    $myform = new form('post', 'form_revision', null);

    $titles = cleanInputArray('post', 'image-title', 'string');
    $captions = cleanInputArray('post', 'image-caption', 'string');

    foreach($_FILES['image-file']['name'] as $k => $fname) {
      if($fname) {

        $tmp_file = $_FILES['image-file']['tmp_name'][$k];
        $init_name = $fname;
        $n_name = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $init_name);

        $p_files = scandir($path);
        $i=1;
        while(in_array($n_name, $p_files)) { 
          $n_name = substr($n_name, 0, strrpos($n_name, '.')+1).$i.substr($n_name, strrpos($n_name, '.')); $i++; 
        }
        $nfile = $n_name;

        if(!$myform->checkExtension($nfile, self::$_extensions) || preg_match('#%00#', $nfile)) {
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

        // thumb
        $image = new image();
        $image->load($file);

        $nthumb_file = 'thumb_'.$nfile;
        $opts = array("enlarge"=>true);
        $image->resizeToHeight(self::$_thumb_height, $opts);

        $image->save($path.$nthumb_file, $image->type());

        // query
        if($res) {
          $doc_image = new docContentImage(null);
          $doc_image->revision = $revision->id;
          $doc_image->title = $titles[$k];
          $doc_image->caption = $captions[$k];
          $doc_image->filename = $nfile;
          $doc_image->path = relativePath($file);
          $doc_image->thumb_path = relativePath($path.$nthumb_file);
          $doc_image->saveData();
        }
      }
    }
  }

  /**
   * @brief Delete an image
   * @param bool $delete_file whether to delete also the file from filesystem or not. Default false.
   * @return the result of the operation
   */
  public function delete($delete_file = false) {

    if($delete_file) {
      require_once('docContentRevision.php');
      $revision = new docContentRevision($this->revision);

      $path = $revision->path();
      $path = substr($path, -1) == DS ? $path : $path.DS;

      if(@unlink($path.$this->filename) and @unlink($path.'thumb_'.$this->filename)) {
        $this->deleteData();
        return true;
      }

      return false;
    }
    else {
      $this->deleteData();
      return true;
    }

  }

}
