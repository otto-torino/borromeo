<?php
/**
 * @file borromeo.controller.php
 * @brief Contains the controller of the borromeo module
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @defgroup borromeo Web application dedicated to collaborative editing of documents
 * @ingroup modules
 */

require_once('chapter.php');
require_once('chapterAdminTable.php');
require_once('doc.php');
require_once('docAdminTable.php');
require_once('docContent.php');
require_once('docContentNote.php');
require_once('docContentNoteFile.php');
require_once('docContentPublicNote.php');
require_once('docContentPublicNoteAdminTable.php');
require_once('docContentRevision.php');
require_once('docCtg.php');
require_once('subchapter.php');
require_once('subchapterAdminTable.php');

/**
 * @ingroup borromeo
 * @brief Borromeo module controller
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class borromeoController extends controller {

  /**
   * module's administration privilege class
   */
  private $_class_privilege;

  /**
   * privilege to administrate documents' categories
   */
  private $_admin_doc_ctg_privilege;

  /**
   * privilege to administrate documents
   */
  private $_admin_doc_privilege;

  /**
   * privilege to create new documents, new documents are tied to the creator
   */
  private $_admin_insert_doc_privilege;

  /**
   * privilege to publish public notes
   */
  private $_admin_note_publication_privilege;

  /**
   * @brief supported media
   */
  private $_media;

  /**
   * Etherpad api key
   */
  private $_etherpad_api_key;

  /**
   * Etherpad api url
   */
  private $_etherpad_api_url;

  /**
   * Etherpad pad url
   */
  private $_etherpad_pad_url;

  /**
   * @brief Constructs a menu controller instance 
   *
   * @return menu controller instance
   */
  function __construct() {

    parent::__construct();

    $this->_cpath = dirname(__FILE__);

    $this->_mdl_name = "borromeo";

    // privileges
    $this->_class_privilege = $this->_mdl_name;
    $this->_admin_doc_ctg_privilege = 1;
    $this->_admin_doc_privilege = 2;
    $this->_admin_insert_doc_privilege = 3;
    $this->_admin_note_publication_privilege = 4;

    // ETHERPAD
    $this->_etherpad_api_key = ETHERPAD_API_KEY;
    $this->_etherpad_api_url = ETHERPAD_API_URL;
    $this->_etherpad_pad_url = ETHERPAD_PAD_URL;

  }

  /**
   * @brief Cheks if the user has the admin doc privilegre
   * @ return true if he has it, false otherwise
   */
  public function hasAdminDocPrivilege() {

     return access::check($this->_class_privilege, $this->_admin_doc_privilege, array("exitOnFailure"=>false));

  }

  /**
   * @brief Cheks if the user has the admin insert doc privilegre
   * @ return true if he has it, false otherwise
   */
  public function hasAdminInsertDocPrivilege() {

     return access::check($this->_class_privilege, $this->_admin_doc_privilege, array("exitOnFailure"=>false));

  }

  /**
   * @brief Control panel in the administrative area
   * @ return control panel
   */
  public function homeAdmin() {

    access::check($this->_class_privilege, array($this->_admin_doc_privilege, $this->_admin_insert_doc_privilege, $this->_admin_note_publication_privilege), array("exitOnFailure"=>true));

    $can_manage = $can_create = $can_publish = false;

    if(access::check($this->_class_privilege,$this->_admin_doc_privilege, array("exitOnFailure"=>false))) {
      $can_manage = true;
      $can_create = true;
      $can_publish = true;
    }
    if(access::check($this->_class_privilege,$this->_admin_insert_doc_privilege, array("exitOnFailure"=>false))) {
      $can_create = true;
    }
    if(access::check($this->_class_privilege,$this->_admin_note_publication_privilege, array("exitOnFailure"=>false))) {
      $can_publish = true;
    }

    $this->_view->setTpl('borromeo_home_admin');
    $this->_view->assign('can_manage', $can_manage);
    $this->_view->assign('can_create', $can_create);
    $this->_view->assign('can_publish', $can_publish);
    $this->_view->assign('manage_doc_ctg_link', anchor($this->_router->linkHref($this->_mdl_name, 'manageDocCtg'), __('manageDocCtg')));
    $this->_view->assign('manage_doc_link', anchor($this->_router->linkHref($this->_mdl_name, 'manageDoc'), __('manageDoc')));
    $this->_view->assign('manage_notes_link', anchor($this->_router->linkHref($this->_mdl_name, 'managePublicAnnotation'), __('managePublicAnnotation')));

    return $this->_view->render();
  }

  /**
   *  @brief Index in the private area
   *  @return private index
   */
  public function index() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $docs = doc::get();

    $this->_view->setTpl('borromeo_index');
    $this->_view->assign('registry', $this->_registry);
    $this->_view->assign('docs', $docs);
    $this->_view->assign('base_doc_url', $this->_router->linkHref($this->_mdl_name, 'doc'));

    return $this->_view->render();

  }

  public function docPad() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $link_error = $this->_router->linkHref(null, null);

    $this->_router->getModule(null);
    $method = $this->_router->method();

    $id = cleanInput('get', 'id', 'int');

    if($method == 'doc') {
      $doc = new doc($id);
      list($chapter, $subchapter) = $this->getChapterAndSubchapter($doc);
    }
    elseif($method == 'revision') {
      $revision = new docContentRevision($id);
      $subchapter = $revision->content()->subchapter();
      $chapter = $subchapter->chapter();
      $doc = $chapter->doc();
    }

    $content = $subchapter->content();

    // only authors and tutora can use the pad
    if(!$content->canRevise()) {
      return '';
    }

    require_once(ABS_PHPLIB.DS.'etherpad-lite-client.php');

    $instance = new EtherpadLiteClient($this->_etherpad_api_key, $this->_etherpad_api_url);

    // Step 1, get GroupID of the userID where userID is OUR userID and NOT the userID used by Etherpad
    try {
      $mappedGroup = $instance->createGroupIfNotExistsFor($content->id);
      $groupID = $mappedGroup->groupID;
    }
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'error' => __('cantCreateEtherpadGroup'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    /* get Mapped Author ID based on a value from your web application such as the userID */
    try {
      $author = $instance->createAuthorIfNotExistsFor($this->_registry->user->id, $this->_registry->user->username); 
      $authorID = $author->authorID;
    }
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'error' => __('cantCreateEtherpadAuthor'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    if(!$content->active_pad) {
      try {
        $newPad = $instance->createGroupPad($groupID, __('realtimeNotes'), ''); 
        $padID = $newPad->padID;
        $instance->setHtml($padID, "<html><head></head><body></body></html>");
        $content->active_pad = $padID;
        $content->saveData();
      }
      catch (Exception $e) {
        exit(Error::errorMessage(array(
          'error' => __('cantCreateEtherpadPad'). ' error: '.$e->getMessage(),
          'hint' => __('contactServerAdmin')
        ), $link_error));
      }
    }

    try {
      $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
      $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
      $sessionID = $sessionID->sessionID;
      $this->sessionCookie($sessionID);
      $padList = $instance->listPads($groupID);
      $padList = $padList->padIDs;
    }
    catch (Exception $e) {
      echo $e;
      $padList = array();
    }

    $padID = $content->active_pad;
    $pad_url = $this->_etherpad_pad_url.$padID;

    $view = new view();
    $view->setTpl('borromeo_doc_pad');
    $view->assign('pad_url', $pad_url);

    return $view->render();

  }

  private function sessionCookie($sessionID) {

    $id = null;

    if(!isset($_COOKIE['sessionID']) or !$_COOKIE['sessionID']) {
      $id = $sessionID;
    }
    else {
      $session_ids_a = explode(',', $_COOKIE['sessionID']);
      if(!in_array($sessionID, $session_ids_a)) {
        $session_ids_a[] = $sessionID;
        $id = implode(',', $session_ids_a);
      }
    }

    if($id) {
      setcookie("sessionID", $id, time()+3600, "/");
    }

  }

  public function deletePads() {

    access::check('main', $this->_registry->admin_privilege, array("exitOnFailure"=>true));

    $link_error = $this->_router->linkHref(null, null);

    require_once(ABS_PHPLIB.DS.'etherpad-lite-client.php');

    $content_id = cleanInput('get', 'content', 'int');
    $content = new docContent($content_id);

    $instance = new EtherpadLiteClient($this->_etherpad_api_key, $this->_etherpad_api_url);

    $all_groups = $instance->listAllGroups();

    var_dump($all_groups);

  }

  /**
   * @brief document index
   */
  public function docIndex() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $this->_registry->addJs(REL_JSLIB.'/Scrollable.js');
    $this->_registry->addCss(REL_CSS.'/Scrollable.css');
    $this->_registry->addJs(REL_JSLIB.'/borromeo.js');

    $this->_router->getModule(null);
    $method = $this->_router->method();

    $id = cleanInput('get', 'id', 'int');

    if($method == 'doc') {
      $doc = new doc($id);
      list($chapter, $subchapter) = $this->getChapterAndSubchapter($doc);
    }
    elseif($method == 'revision') {
      $revision = new docContentRevision($id);
      $subchapter = $revision->content()->subchapter();
      $chapter = $subchapter->chapter();
      $doc = $chapter->doc();
    }

    $view = new view();
    $view->setTpl('borromeo_doc_index');
    $view->assign('doc', $doc);
    $view->assign('view_subchapter_url', $this->_router->linkHref($this->_mdl_name, 'doc', array('id'=>$doc->id)));
    $view->assign('form_chapter_url', $this->_router->linkAjax($this->_mdl_name, 'formChapter', array('doc_id'=>$doc->id)));
    $view->assign('delete_chapter_url', $this->_router->linkAjax($this->_mdl_name, 'deleteChapter', array('doc_id'=>$doc->id)));
    $view->assign('form_subchapter_url', $this->_router->linkAjax($this->_mdl_name, 'formSubchapter', array('doc_id'=>$doc->id)));
    $view->assign('delete_subchapter_url', $this->_router->linkAjax($this->_mdl_name, 'deleteSubchapter', array('doc_id'=>$doc->id)));
    $view->assign('apply_order_url', $this->_router->linkAjax($this->_mdl_name, 'applyIndexOrder', array('doc_id'=>$doc->id)));
    $index = $view->render();

    return $index;

  }

  /**
   * @brief update the order of chapters and subchapters (dragging)
   * @return boolean result of the opertation
   */
  public function applyIndexOrder() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('subchapter.php');
    require_once('chapter.php');
    require_once('doc.php');

    $doc_id = cleanInput('get', 'doc_id', 'int');
    $doc = new doc($doc_id);

    if(!$doc->canManage()) {
      error::raise403();
    }

    $chapters_str = cleanInput('post', 'chapters', 'string', array('escape' => false));
    $subchapters_str = cleanInput('post', 'subchapters', 'string', array('escape' => false));
    $slists_str = cleanInput('post', 'slists', 'string', array('escape' => false));

    $chapters_order = json_decode($chapters_str);
    $subchapters_order = json_decode($subchapters_str);
    $slists = json_decode($slists_str);

    $res = chapter::applyOrder($chapters_order);
    $res = $res and subchapter::applyOrder($subchapters_order, $slists);

    if($res) {
      $doc->updateLastEdit();
    }

    return $res;

  }


  /**
   * Document annotations, public and private (revise)
   */
  public function docAnnotations() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    if($this->_registry->user->id) {
      return $this->docAnnotationsRevise();
    }
    else {
      return $this->docAnnotationsPublic();
    }
  }

  /**
   * @brief Public annotations
   */
  public function docAnnotationsPublic() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $id = cleanInput('get', 'id', 'int');

    $doc = new doc($id);
    list($chapter, $subchapter) = $this->getChapterAndSubchapter($doc);

    if(!$doc->id or !$subchapter or !$subchapter->id) return '';
    $content = $subchapter->content();

    $notes = docContentPublicNote::getFromContent($content->id);

    $view = new view();
    $view->setTpl('borromeo_doc_public_annotations');
    $view->assign('subchapter', $subchapter);
    $view->assign('new_note_url', $this->_router->linkAjax($this->_mdl_name, 'formPublicAnnotation', array('content_id' => $content->id)));
    $view->assign('notes', $notes);
    $view->assign('registry', $this->_registry);
    $view->assign('content', $content);
    $annotations = $view->render();

    return $annotations;

  }

  /**
   * @brief Create a public note
   */
  public function formPublicAnnotation() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $content_id = cleanInput('get', 'content_id', 'int');
    $content = new docContent($content_id);

    $myform = new form('post', 'form_public_note', array('validation' => true));
    $form = $myform->sform($this->_router->linkHref($this->_mdl_name, 'savePublicAnnotation'), 'title', array('upload' => true));
    $form .= $myform->hidden('content_id', $content->id);
    $form .= $myform->cinput('title', 'text', '', __('title'), array('required'=>true));
    $form .= $myform->ctextarea('text', '', __('text'), array('required' => false, 'cols' => 40, 'rows' => 6));
    $form .= $myform->ccaptcha();

    $form .= $myform->cinput('submit', 'submit', __('save'), '', null);
    $form .= $myform->cform();

    $view = new view();
    $view->setTpl('borromeo_doc_form_annotation');
    $view->assign('content', $content);
    $view->assign('information', __('publicAnnotationFormInformation'));
    $view->assign('form', $form);

    return $view->render();

  }

  /**
   * @brief Saves an annotation
   */
  public function savePublicAnnotation() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $content_id = cleanInput('post', 'content_id', 'int');
    $content = new docContent($content_id);

    $myform = new form('post', 'form_public_note', array('validation' => false));

    if($myform->checkRequired()) {
      error::errorMessage(array(
        'error' => 1
      ), $content->subchapter()->getUrl());
    }

    if(!$myform->checkCaptcha()) {
      error::errorMessage(array(
        'error' => __('captchaError')
      ), $content->subchapter()->getUrl());
    }

    $note = new docContentPublicNote(null);
    $note->content = $content->id;
    $note->title = cleanInput('post', 'title', 'string');
    $note->text = cleanInput('post', 'text', 'string');
    $note->creation_date = $this->_registry->dtime->now('%y-%m-%d %H:%i:%s');
    $note->published = 0;

    $note->saveData();

    header("Location: ".$content->subchapter()->getUrl());
    exit();

  }

  /**
   * @brief Authors and tutors annotations
   */
  public function docAnnotationsRevise() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $this->_router->getModule(null);
    $method = $this->_router->method();

    $id = cleanInput('get', 'id', 'int');

    if($method == 'doc') {
      $doc = new doc($id);
      list($chapter, $subchapter) = $this->getChapterAndSubchapter($doc);
    }
    elseif($method == 'revision') {
      $revision = new docContentRevision($id);
      $subchapter = $revision->content()->subchapter();
      $chapter = $subchapter->chapter();
      $doc = $chapter->doc();
    }

    if(!$doc->id or !$subchapter or !$subchapter->id) return '';
    $content = $subchapter->content();

    if($content->canRevise()) {
      $notes = docContentNote::getFromContent($content->id);
      $view = new view();
    }
    else return '';

    $view = new view();
    $view->setTpl('borromeo_doc_annotations');
    $view->assign('subchapter', $subchapter);
    $view->assign('new_note_url', $this->_router->linkAjax($this->_mdl_name, 'formAnnotation', array('content_id' => $content->id)));
    $view->assign('notes', $notes);
    $view->assign('registry', $this->_registry);
    $view->assign('content', $content);
    $view->assign('edit_url', $this->_router->linkAjax($this->_mdl_name, 'formAnnotation'));
    $view->assign('delete_url', $this->_router->linkAjax($this->_mdl_name, 'deleteAnnotation'));
    $annotations = $view->render();

    return $annotations;

  }

  /**
   * @brief Create a note
   */
  public function formAnnotation() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $note_id = cleanInput('get', 'note', 'int');
    $note = new docContentNote($note_id);

    if($note->id) {
      $content = $note->content();
    }
    else {
      $content_id = cleanInput('get', 'content_id', 'int');
      $content = new docContent($content_id);
    }

    if(!$content->canRevise()) {
      error::raise403();
    }

    $myform = new form('post', 'form_note', array('validation' => true));
    $form = $myform->sform($this->_router->linkHref($this->_mdl_name, 'saveAnnotation'), 'text', array('upload' => true));
    $form .= $myform->hidden('note_id', $note->id);
    $form .= $myform->hidden('content_id', $content->id);
    $form .= $myform->cinput('title', 'text', htmlInput($note->title), __('title'), array('required'=>true));
    $form .= $myform->ctextarea('text', htmlInput($note->text), __('text'), array('required' => false, 'cols' => 40, 'rows' => 6));

    $note_files = docContentNoteFile::getFromNote($note->id);

    if(count($note_files)) {

      foreach($note_files as $note_file) {
        $input_del = $myform->checkbox('del_file[]', false, $note_file->id, null);
        $rows[] = array($input_del, htmlVar($note_file->title), htmlVar($note_file->description));
      }
      $view = new view();
      $view->setTpl('table');
      $view->assign('class', 'generic');
      $view->assign('caption', '');
      $view->assign('heads', array(__('delete'), __('title'), __('description')));
      $view->assign('rows', $rows);
      $delete_note_files = $view->render();
    }
    else {
      $delete_note_files = '';
    }

    $form .= $myform->freeInput('', "<p><b>".__('LoadedFiles')."</b></p>");
    $form .= $myform->freeInput('', $delete_note_files);
    $onchange = "onchange=\"addFileFieldset.call(this)\"";
    $form_file = $myform->cinput_file('file[]', '', __('file'), array('js'=>$onchange));
    $form_file .= $myform->cinput('file-title[]', 'text', '', __('title'), null);
    $form_file .= $myform->ctextarea('file-caption[]', '', __('caption'), null);

    $form .= $myform->freeInput('', $myform->fieldset(__('addFile'), $form_file));

    $form .= $myform->cinput('submit', 'submit', __('save'), '', null);
    $form .= $myform->cform();

    $view = new view();
    $view->setTpl('borromeo_doc_form_annotation');
    $view->assign('content', $content);
    $view->assign('form', $form);

    return $view->render();

  }

  /**
   * @brief Deletes an annotation
   */
  public function deleteAnnotation() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $note_id = cleanInput('get', 'note', 'int');
    $note = new docContentNote($note_id);
    $content = $note->content();

    if(!$content->canRevise()) {
      error::raise403();
    }

    $subchapter = $content->subchapter();

    $note->delete();

    header("Location: ".$subchapter->getUrl());
    exit();

  }

  /**
   * @brief Saves an annotation
   */
  public function saveAnnotation() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $myform = new form('post', 'form_note', array('validation' => false));

    $note_id = cleanInput('post', 'note_id', 'int');
    $content_id = cleanInput('post', 'content_id', 'int');
    $content = new docContent($content_id);

    if(!$content->canRevise()) {
      error::raise403();
    }

    $del_file = cleanInputArray('post', 'del_file', 'int');
    if(count($del_file)) {
      foreach($del_file as $file_id) {
        $note_file = new docContentNoteFile($file_id);
        $note_file->delete(true);
      }
    }

    $note = new docContentNote($note_id);
    $note->content = $content->id;
    $note->title = cleanInput('post', 'title', 'string');
    $note->text = cleanInput('post', 'text', 'string');
    if(!$note->id) {
      $note->creation_date = $this->_registry->dtime->now('%y-%m-%d %H:%i:%s');
      $note->user = $this->_registry->user->id;
    }
    $note->last_edit_date = $this->_registry->dtime->now('%y-%m-%d %H:%i:%s');

    $note->saveData();

    docContentNoteFile::saveFiles($myform, $note);

    header("Location: ".$content->subchapter()->getUrl());
    exit();

  }


  /**
   * @brief authoring and tutoring tools controllers
   */
  public function docControllers() {

    $this->_router->getModule(null);
    $method = $this->_router->method();

    $id = cleanInput('get', 'id', 'int');

    if($method == 'doc') {
      $doc = new doc($id);
      list($chapter, $subchapter) = $this->getChapterAndSubchapter($doc);
    }
    elseif($method == 'revision') {
      $revision = new docContentRevision($id);
      $subchapter = $revision->content()->subchapter();
      $chapter = $subchapter->chapter();
      $doc = $chapter->doc();
    }

    // no subchapter
    if(!$doc->id or !$chapter->id or !$subchapter->id) {
      return '';
    }
    $content = $subchapter->content();
    // no privilege to revise contents
    if(!$subchapter->content()->canRevise()) {
      return '';
    }

    // set session path for ckeditor
    $_SESSION['ckeditor_upload_path'] = '/upload/doc/'.$doc->id.'/'.$chapter->id.'/'.$subchapter->id.'/';

    $controllers = array();
    $links = array();

    // revision pending, not yet merged
    $pending_revision = $content->pendingRevision();

    // if no pending revision then a new one can be created
    if(!$pending_revision->id) {
      $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'createRevision', array('content_id' => $content->id))."\" class=\"right link icon icon_edit\" title=\"".__('edit')."\"></a>";
    }
    // links and controllers
    else {
      $links[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'doc', array('id'=>$doc->id, 'chapter'=>$chapter->id, 'subchapter'=>$subchapter->id))."\" class=\"left ctrl-link link".($method == 'doc' ? ' selected' : '')."\">".__('consolidatedRevision')."</a>";
      $ruser = new user($pending_revision->user);
      $links[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'revision', array('id'=>$pending_revision->id))."\" class=\"left link ctrl-link".($method == 'revision' ? ' selected' : '')."\">".__('workingRevision').' '.htmlVar($ruser->firstname.' '.$ruser->lastname)."</a>";
      // save working revision
      if($pending_revision->canEdit() and $method == 'revision') {
        $controllers[] = "<span onclick=\"borromeo.saveRevision('".$this->_router->linkHref($this->_mdl_name, 'saveRevision', array('revision_id' => $pending_revision->id))."', 'editable', '".__('saveComplete')."', document.id('borromeo-doc-controllers').getElements('.icon_save')[0]);\" class=\"right link icon icon_save inactive\" title=\"".__('save')."\">".__('save')."</span>";
      $controllers[] = "<span onclick=\"if(confirmSubmit('".jsVar(__('continueDeletingRevisionAlert'))."')) location.href='".$this->_router->linkHref($this->_mdl_name, 'deleteRevision', array('revision_id' => $pending_revision->id))."'\" class=\"right link icon icon_delete\" title=\"".__('delete')."\"></span>";
      }
      // merge working revision controller
      if($pending_revision->canMerge() and $method == 'revision') {
        $controllers[] = "<span onclick=\"".layerWindowCall(ucfirst(__('mergeRevision')), $this->_router->linkAjax($this->_mdl_name, 'formMergeRevision', array('revision_id' => $pending_revision->id)))."\" class=\"right link icon icon_merge\" title=\"".__('merge')."\"></span>";
      }
    }

    // link to revision history
    if($content->canManage()) {
      $links[] = "<span onclick=\"".layerWindowCall(ucfirst(__('revisionHistory')), $this->_router->linkAjax($this->_mdl_name, 'revisionHistory', array('subchapter_id' => $subchapter->id)))."\" class=\"left ctrl-link link\">".__('revisionHistory')."</span>";
    }

    $view = new view();
    $view->setTpl('borromeo_doc_controllers');
    $view->assign('controllers', $controllers);
    $view->assign('links', $links);

    return $view->render();

  }

  /**
   * @brief saves the revision text
   */
  public function saveRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $revision_id = cleanInput('get', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);

    if(!$revision->id) {
      error::raise404();
    }

    $content = $revision->content();

    if(!$content->canRevise()) {
      error::raise403();
    }

    $text = cleanInput('post', 'text', 'html');
    $revision->text = $text;
    $revision->saveData();

    exit();

  }

  /**
   * @brief deletes a working revision
   */
  public function deleteRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $revision_id = cleanInput('get', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);

    if(!$revision->id) {
      error::raise404();
    }

    $content = $revision->content();

    if(!$content->canRevise()) {
      error::raise403();
    }

    $subchapter = $content->subchapter();
    $chapter = $subchapter->chapter();
    $doc = $chapter->doc();

    $revision->delete();

    header('Location: '.$this->_router->linkHref($this->_mdl_name, 'doc', array('id'=>$doc->id, 'chapter'=>$chapter->id, 'subchapter'=>$subchapter->id)));
    exit();

  }

  /**
   * @brief form to merge a revision in the content
   */
  public function formMergeRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');

    $revision_id = cleanInput('get', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);
    $content = $revision->content();

    if(!$revision->canMerge()) {
      error::raise403();
    }

    $myform = new form('post', 'form_merge_revision', array('validation' => true));
    $form = $myform->sform($this->_router->linkHref($this->_mdl_name, 'mergeRevision'), 'text', array('upload' => true));
    $form .= $myform->hidden('revision_id', $revision->id);
    $form .= $myform->ctextarea('comment', '', __('comment'), array('required' => true, 'cols' => 40, 'rows' => 6));
    $form .= $myform->cinput('submit', 'submit', __('merge'), __('mergeAlert'), null);
    $form .= $myform->cform();

    return $form;

  }

  /**
   * @brief Action of merging a revision into the content
   */
  public function mergeRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');

    $revision_id = cleanInput('post', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);
    $content = $revision->content();

    if(!$revision->canMerge()) {
      error::raise403();
    }

    $content = $revision->content();
    $content->revision = $revision->id;
    $content->saveData();
    $content->updateLastEdit();

    $subchapter = $content->subchapter();
    $chapter = $subchapter->chapter();
    $doc = $chapter->doc();
    $doc->updateLastEdit();

    $revision->merged = 1;
    $revision->merged_user = $this->_registry->user->id;
    $revision->merged_date = $this->_registry->dtime->now('%Y-%m-%d %H:%i:%s');
    $revision->merged_comment = cleanInput('post', 'comment', 'string');
    $revision->saveData();

    header('Location: '.$this->_router->linkHref($this->_mdl_name, 'doc', array('id'=>$doc->id, 'chapter' => $chapter->id, 'subchapter' => $subchapter->id)));
    exit();

  }


  /**
   * @brief Gets the actual chapter and subchapter
   * @param doc $doc the document
   * @return chapter and subchapter objects as elements of an array
   */
  private function getChapterAndSubchapter($doc) {

    $chapter_id = cleanInput('get', 'chapter', 'int');
    $subchapter_id = cleanInput('get', 'subchapter', 'int');

    if(!$chapter_id and count($chapter_ids = $doc->chapters())) {
      $chapter = new chapter($chapter_ids[0]);
    }
    else {
      $chapter = new chapter($chapter_id);
    }

    if(!$subchapter_id and $chapter->id and count($subchapter_ids = $chapter->subchapters())) {
      $subchapter = new subchapter($subchapter_ids[0]);
    }
    else {
      $subchapter = new subchapter($subchapter_id);
    }

    return array($chapter, $subchapter);

  }

  public function doc() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $doc_id = cleanInput('get', 'id', 'int');
    $doc = new doc($doc_id);

    // if not a document return 404
    if(!$doc->id) {
      error::raise404();
    }

    list($chapter, $subchapter) = $this->getChapterAndSubchapter($doc);

    if(!$chapter->id) {
      $content = __('noChapters');
    }
    elseif(!$subchapter->id) {
      $content = '';
    }
    // a subchapter exists
    else {
      $view = new view();
      $view->setTpl('borromeo_doc_subchapter');
      $view->assign('subchapter', $subchapter);
      $content = $view->render();
    }

    $view = new view();
    $view->setTpl('borromeo_doc', array('css' => 'borromeo'));
    $view->assign('doc', $doc);
    $view->assign('chapter', $chapter);
    $view->assign('content', $content);

    return $view->render();

  }

  public function revision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $this->_registry->addJs(REL_JSLIB.'/ckeditor/ckeditor.js');

    $revision_id = cleanInput('get', 'id', 'int');
    $revision = new docContentRevision($revision_id);

    if(!$revision->id) {
      error::raise404();
    }

    $content = $revision->content();

    if(!$content->canRevise()) {
      error::raise403();
    }

    $subchapter = $content->subchapter();
    $chapter = $subchapter->chapter();
    $doc = $chapter->doc();

    $view = new view();
    $view->setTpl('borromeo_doc_revision', array('css' => 'borromeo'));
    $view->assign('doc', $doc);
    $view->assign('chapter', $chapter);
    $view->assign('subchapter', $subchapter);
    $view->assign('revision', $revision);
    $view->assign('can_edit', $revision->canEdit());

    return $view->render();

  }

  public function revisionHistory() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $subchapter_id = cleanInput('get', 'subchapter_id', 'int');
    $subchapter = new subchapter($subchapter_id);
    $content = $subchapter->content();

    if(!$content->canManage()) {
      error::raise403();
    }

    $revisions = docContentRevision::get(array('where' => "content='".$content->id."' AND merged IS NOT NULL", 'order' => 'merged_date DESC'));
    $current_revision = new docContentRevision($content->revision);
    $pending_revision = $content->pendingRevision();

    $can_create = $pending_revision->id ? false : true;

    $view = new view();
    $view->setTpl('borromeo_doc_revision_history', array('css' => 'borromeo'));
    $view->assign('subchapter', $subchapter);
    $view->assign('revisions', $revisions);
    $view->assign('current_revision', $current_revision);
    $view->assign('view_url', $this->_router->linkAjax($this->_mdl_name, 'viewRevision'));
    $view->assign('registry', $this->_registry);
    $view->assign('can_create', $can_create);
    $view->assign('create_from_url', $this->_router->linkHref($this->_mdl_name, 'createRevisionFrom'));

    return $view->render();

  }

  /**
   * @brief Visualization of a revision
   */
  public function viewRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $revision_id = cleanInput('get', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);

    if(!$revision->content()->canManage()) {
      error::raise403();
    }

    $title = __('revision').'#'.$revision->id;
    $ruser = new user($revision->user);
    $author = htmlVar($ruser->lastname.' '.$ruser->firstname);

    $view = new view();
    $view->setTpl('borromeo_doc_revision_view');
    $view->assign('title', $title);
    $view->assign('revision', $revision);
    $view->assign('author', $author);

    return $view->render();

  }

  /**
   * @brief Create a new revision from another one
   */
  public function createRevisionFrom() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $revision_id = cleanInput('get', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);

    if(!$revision->content()->canManage()) {
      error::raise403();
    }

    $nrevision = new docContentRevision(null);
    $nrevision->user = $revision->user;
    $nrevision->content = $revision->content;
    $nrevision->last_edit_date = $this->_registry->dtime->now('%Y-%m-%d %H:%i:%s');
    $nrevision->text = $this->_registry->db->escapeString($revision->text);
    $nrevision->saveData();

    header('Location: '.$this->_router->linkHref($this->_mdl_name, 'revision', array('id' => $nrevision->id)));
    exit;

  }

  /**
   * @brief creates a new revision
   */
  public function createRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $content_id = cleanInput('get', 'content_id', 'int');
    $content = new docContent($content_id);
    $pending_revision = $content->pendingRevision();

    if(!$content->canRevise() or $pending_revision->id) {
      error::raise403();
    }

    $content_revision = new docContentRevision($content->revision);

    $revision = new docContentRevision(null);
    $revision->text = $this->_registry->db->escapeString($content_revision->text);
    $revision->user = $this->_registry->user->id;
    $revision->content = $content->id;
    $revision->last_edit_date = $this->_registry->dtime->now('%Y-%m-%d %H:%i:%s');

    $revision->saveData();

    header('Location: '.$this->_router->linkHref($this->_mdl_name, 'revision', array('id' => $revision->id)));
    exit();
  }

  /**
   * @brief Chapter insertion and modification form
   * @return chapter form
   */
  public function formChapter() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('chapter.php');
    require_once('doc.php');

    $id = cleanInput('get', 'id', 'int');
    $chapter = new chapter($id);

    $doc_id = cleanInput('get', 'doc_id', 'int');
    $doc = new doc($doc_id);

    if(!$doc->canManage()) {
      error::raise403();
    }

    $fieldsets = array(
      'author_groups' => __('privileges')
    );

    $at = chapter::adminTable($doc);
    $at->setFieldsets($fieldsets);

    if($id) {
      $f_s = array($id);
      $insert = false;
    }
    else {
      $f_s = null;
      $insert = true;
    }

    $form = $at->editFields(array('insert'=>$insert, 'f_s'=>$f_s, 'action'=>$this->_router->linkAjax($this->_mdl_name, 'saveChapter', array('doc_id' => $doc_id))));

    $form .= "<script>
                $$('.inside_record').each(function(f) {
                  var legend = f.getChildren('legend');
                  legend.addClass('link');
                  f.getChildren('label, div, br').setStyle('display', 'none');
                  f.store('collapsed', true);
                  legend.addEvent('click', function() {
                    if(f.retrieve('collapsed')) {
                      f.getChildren('label, div').setStyle('display', 'inline-block');
                      f.getChildren('br').setStyle('display', 'inline');
                      f.store('collapsed', false);
                    }
                    else {
                      f.getChildren('label, div, br').setStyle('display', 'none');
                      f.store('collapsed', true);
                    }
                  });
                })
              </script>";

    return $form;

  }

  /**
   * @brief Saves a chapter record
   */
  public function saveChapter() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('chapter.php');
    require_once('doc.php');

    $doc_id = cleanInput('get', 'doc_id', 'int');
    $doc = new doc($doc_id);

    if(!$doc->canManage()) {
      error::raise403();
    }

    $insert = $_POST['id'][0] ? false : true;

    $at = chapter::adminTable($doc);

    $result = $at->saveFields('chapter');

    if($insert) {
      mkdir(ABS_UPLOAD.DS.'doc'.DS.$doc->id.DS.$result[0], 0755, true);
    }

    $doc->updateLastEdit();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $doc_id)));
    exit();

  }


  public function deleteChapter() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $chapter_id = cleanInput('get', 'chapter_id', 'int');
    $chapter = new chapter($chapter_id);
    if(!$chapter->id) {
      error::raise404();
    }
    $doc = $chapter->doc();
    if(!$doc->canManage()) {
      error::raise403();
    }

    $chapter->delete();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $doc->id)));
    exit();

  }

  /**
   * @brief Subchapter insertion and modification form
   * @return subchapter form
   */
  public function formSubchapter() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('chapter.php');
    require_once('subchapter.php');
    require_once('doc.php');

    $id = cleanInput('get', 'id', 'int');
    $subchapter = new subchapter($id);

    $chapter_id = cleanInput('get', 'chapter_id', 'int');
    $chapter = new chapter($chapter_id);

    $doc_id = cleanInput('get', 'doc_id', 'int');
    $doc = new doc($doc_id);

    if(!$doc->canManage()) {
      error::raise403();
    }

    $fieldsets = array(
      'author_groups' => __('privileges')
    );

    $at = subchapter::adminTable($chapter);
    $at->setFieldsets($fieldsets);

    if($id) {
      $f_s = array($id);
      $insert = false;
    }
    else {
      $f_s = null;
      $insert = true;
    }

    $form = $at->editFields(array('insert'=>$insert, 'f_s'=>$f_s, 'action'=>$this->_router->linkAjax($this->_mdl_name, 'saveSubchapter', array('doc_id' => $doc_id, 'chapter_id' => $chapter_id))));

    $form .= "<script>
                $$('.inside_record').each(function(f) {
                  var legend = f.getChildren('legend');
                  legend.addClass('link');
                  f.getChildren('label, div, br').setStyle('display', 'none');
                  f.store('collapsed', true);
                  legend.addEvent('click', function() {
                    if(f.retrieve('collapsed')) {
                      f.getChildren('label, div').setStyle('display', 'block');
                      f.getChildren('br').setStyle('display', 'inline');
                      f.store('collapsed', false);
                    }
                    else {
                      f.getChildren('label, div, br').setStyle('display', 'none');
                      f.store('collapsed', true);
                    }
                  });
                })
              </script>";

    return $form;

  }

  /**
   * @brief Saves a subchapter record
   * @description Creates a new subchapter, a new empty content and a working revision, or modifies a subchapter. Updates doc last edit.
   */
  public function saveSubchapter() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $insert = !$_POST['id'][0] ? true : false;

    $doc_id = cleanInput('get', 'doc_id', 'int');
    $chapter_id = cleanInput('get', 'chapter_id', 'int');
    $doc = new doc($doc_id);
    $chapter = new chapter($chapter_id);

    if(!$doc->canManage()) {
      error::raise403();
    }

    $at = subchapter::adminTable($chapter);

    $sid_a = $at->saveFields('subchapter');

    if($insert) {
      $content_id = docContent::createEmpty($sid_a[0]);
      mkdir(ABS_UPLOAD.DS.'doc'.DS.$doc->id.DS.$chapter->id.DS.$sid_a[0], 0755, true);
    }

    $doc->updateLastEdit();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $doc_id)));
    exit();

  }


  public function deleteSubchapter() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $subchapter_id = cleanInput('get', 'subchapter_id', 'int');
    $subchapter = new subchapter($subchapter_id);
    if(!$subchapter->id) {
      error::raise404();
    }
    $doc = $subchapter->chapter()->doc();
    if(!$doc->canManage()) {
      error::raise403();
    }

    $subchapter->delete();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $doc->id)));
    exit();

  }


  /**
   * @brief Documents category backoffice
   *
   * @access public
   * @return documents category backoffice
   */
  public function manageDocCtg() {

    access::check($this->_class_privilege, $this->_admin_doc_ctg_privilege, array("exitOnFailure"=>true));

    $at = new adminTable(TBL_B_DOC_CTG, array("insertion"=>true));

    $table = $at->manage();

    $this->_view->setTpl('manage_table');
    $this->_view->assign('title', ucfirst(__("manageDocCtg")));
    $this->_view->assign('table', $table);

    return $this->_view->render();

  }

  /**
   * @brief Documents backoffice
   *
   * @access public
   * @return documents backoffice
   */
  public function manageDoc() {

    access::check($this->_class_privilege, array($this->_admin_doc_privilege, $this->_admin_insert_doc_privilege), array("exitOnFailure"=>true));

    $at = doc::adminTable($this);

    $table = $at->manage();

    $this->_view->setTpl('manage_table');
    $this->_view->assign('title', ucfirst(__("manageDoc")));
    $this->_view->assign('table', $table);

    return $this->_view->render();

  }

  /**
   * @brief Public annotation backoffice
   *
   * @access public
   * @return public annotation backoffice
   */
  public function managePublicAnnotation() {

    access::check($this->_class_privilege, array($this->_admin_doc_privilege, $this->_admin_note_publication_privilege), array("exitOnFailure"=>true));

    $s_fields = array(
      "published"=>array(
        "type"=>"bool",
        "required"=>true,
        "true_label"=>__("yes"),
        "false_label"=>__("no")	
      )
    );

    $f_keys = array(
      'content' => array(
        "table"=>TBL_B_DOC_CONTENT,
        "field"=>"id",
        "where"=>"",
        "order"=>""
      )
    );

    $at = new docContentPublicNoteAdminTable(TBL_B_DOC_CONTENT_PUBLIC_NOTE, array());
    $at->setSpecialFields($s_fields);
    $at->setForeignKeys($f_keys);

    $table = $at->manage();

    $this->_view->setTpl('manage_table');
    $this->_view->assign('title', ucfirst(__("managePublicAnnotation")));
    $this->_view->assign('table', $table);

    return $this->_view->render();

  }


}
