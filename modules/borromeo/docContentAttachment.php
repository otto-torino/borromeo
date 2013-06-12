<?php
/**
 * @file docContentAttachment.php
 * @brief Contains the docContentAttachment model class.
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

require_once('interface.docContentMedia.php');

/**
 * @ingroup borromeo
 * @brief docContentAttachment model class.
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **revision** int(8): revision primary key value
 * - **title** varchar: title
 * - **description** text: description
 * - **filename** varchar: file name
 * - **path** varchar: file path

 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docContentAttachment extends model implements DocContentMedia {

  /**
   * @brief allowed extensions
   */
  private static $_extensions = array('pdf', 'doc', 'docx', 'xls', 'odt', 'zip');

  /**
   * @brief Class constructor
   * @param int $id the attached identifier
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CONTENT_ATTACHMENT;

    parent::__construct($id);

  }

  /**
   * @brief Get attachment objects tied to the given revision
   * @param int $revision_id the revision identifier
   * @return array of attachment objects
   */
  public static function getFromRevision($revision_id) {

    $db = db::instance();
    $res = array();
    $rows = $db->autoSelect('id', TBL_B_DOC_CONTENT_ATTACHMENT, "revision='".$revision_id."'", 'id');
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new docContentAttachment($row['id']);
      }
    }

    return $res;
  }

  /**
   * @brief Visualization of the attachments tied to the revision
   * @param docContentRevision $revision the revision object
   */
  public static function revisionTab($revision) {

    $attachments = self::getFromRevision($revision->id);

    $view = new view();
    $view->setTpl('borromeo_doc_attachment_tab');
    $view->assign('revision', $revision);
    $view->assign('attachments', $attachments);

    return $view->render();

  }

  /**
   * @brief Form to add/edit attachments of the revision
   * @param form $form the form object
   * @param docContentRevision $revision the revision object
   */
  public static function revisionForm($form, $revision) {

    $rows = array();
    $attachments = self::getFromRevision($revision->id);
    foreach($attachments as $attachment) {
      $input_del = $form->checkbox('del_attachment[]', false, $attachment->id, null);
      $rows[] = array($input_del, htmlVar($attachment->title), htmlVar($attachment->description));
    }

    if(count($attachments)) {
      $view = new view();
      $view->setTpl('table');
      $view->assign('class', 'generic');
      $view->assign('caption', '');
      $view->assign('heads', array(__('delete'), __('title'), __('description')));
      $view->assign('rows', $rows);
      $delete_attachments = $view->render();
    }
    else {
      $delete_attachments = '';
    }

    $onchange = "onchange=\"addAttachmentFieldset.call(this)\"";
    $form_add_attachment = $form->cinput_file('attachment-file[]', '', __('file'), array('js'=>$onchange));
    $form_add_attachment .= $form->cinput('attachment-title[]', 'text', '', __('title'), null);
    $form_add_attachment .= $form->ctextarea('attachment-description[]', '', __('description'), null);
    $form_add_attachment_fieldset = $form->fieldset(__('addAttachment'), $form_add_attachment);

    $view = new view();
    $view->setTpl('borromeo_doc_revision_form_attachment');
    $view->assign('delete_attachments', $delete_attachments);
    $view->assign('form_add_attachment_fieldset', $form_add_attachment_fieldset);

    return $view->render();

  }

  /**
   * @brief Copy all attachments from one revision to another
   * @param int $from_revision_id the from revision identifier
   * @param int $to_revision_id the to revision identifier
   * @param array $del_attachment list of images id which shouldn't be copied
   */
  public static function copyFromToRevision($from_revision_id, $to_revision_id, $del_attachment) {

    $db = db::instance();

    $from_attachments = self::getFromRevision($from_revision_id);
    foreach($from_attachments as $attachment) {
      if(!in_array($attachment->id, $del_attachment)) {
        $attachment_copy = new docContentAttachment(null);
        $attachment_copy->revision = $to_revision_id;
        $attachment_copy->title = $db->escapeString($attachment->title);
        $attachment_copy->description = $db->escapeString($attachment->description);
        $attachment_copy->filename = $attachment->filename;
        $attachment_copy->path = $attachment->path;
        $attachment_copy->saveData();
      }
    }
  }

  /**
   * @brief Save attachments for the revision
   */
  public static function saveRevision($revision, $starting_revision) {

    $edit = $revision->id != $starting_revision->id ? false : true;
    $del_attachment = cleanInputArray('post', 'del_attachment', 'int');

    // if starting revision is different from revision copy all not deleted attachments
    if(!$edit) {
      self::copyFromToRevision($starting_revision->id, $revision->id, $del_attachment);
    }
    else {
      // delete attachments
      if(count($del_attachment)) {
        foreach($del_attachment as $attachment_id) {
          $attachment = new docContentAttachment($attachment_id);
          $attachment->delete(true);
        }
      }
    }

    $path = $revision->path();
    $path = substr($path, -1) == DS ? $path : $path.DS;

    if(!is_dir($path)) mkdir($path, 0755, true);

    $myform = new form('post', 'form_revision', null);

    $titles = cleanInputArray('post', 'attachment-title', 'string');
    $descriptions = cleanInputArray('post', 'attachment-description', 'string');

    foreach($_FILES['attachment-file']['name'] as $k => $fname) {
      if($fname) {

        $tmp_file = $_FILES['attachment-file']['tmp_name'][$k];
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

        // query
        if($res) {
          $doc_attachment = new docContentAttachment(null);
          $doc_attachment->revision = $revision->id;
          $doc_attachment->title = $titles[$k];
          $doc_attachment->description = $descriptions[$k];
          $doc_attachment->filename = $nfile;
          $doc_attachment->path = relativePath($file);
          $doc_attachment->saveData();
        }
      }
    }
  }

  /**
   * @brief Delete an attachment
   * @param bool $delete_file whether to delete also the file from filesystem or not. Default false.
   * @return the result of the operation
   */
  public function delete($delete_file = false) {

    if($delete_file) {
      require_once('docContentRevision.php');
      $revision = new docContentRevision($this->revision);

      $path = $revision->path();
      $path = substr($path, -1) == DS ? $path : $path.DS;

      if(@unlink($path.$this->filename)) {
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
