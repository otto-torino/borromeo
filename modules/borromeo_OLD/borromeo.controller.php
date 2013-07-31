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

    access::check($this->_class_privilege, array($this->_admin_doc_privilege, $this->_admin_insert_doc_privilege), array("exitOnFailure"=>true));

    if(!access::check($this->_class_privilege,$this->_admin_doc_privilege, array("exitOnFailure"=>false))) {
      $permission = 'create';
    }
    else {
      $permission = 'manage';
    }

    $this->_view->setTpl('borromeo_home_admin');
    $this->_view->assign('permission', $permission);
    $this->_view->assign('manage_doc_ctg_link', anchor($this->_router->linkHref($this->_mdl_name, 'manageDocCtg'), __('manageDocCtg')));
    $this->_view->assign('manage_doc_link', anchor($this->_router->linkHref($this->_mdl_name, 'manageDoc'), __('manageDoc')));

    return $this->_view->render();
  }

  /**
   *  @brief Index in the private area
   *  @return private index
   */
  public function index() {

    require_once('doc.php');

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $docs = doc::get();

    $this->_view->setTpl('borromeo_index');
    $this->_view->assign('registry', $this->_registry);
    $this->_view->assign('docs', $docs);
    $this->_view->assign('base_doc_url', $this->_router->linkHref($this->_mdl_name, 'doc'));

    return $this->_view->render();

  }

  /**
   *  @brief Visualization and modification a document chapter
   *  @description if a GET chapter is not passed the first is taken
   *  @return document view
   */
  public function doc() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $this->_registry->addJs(REL_JSLIB.'/borromeo.js');
    $this->_registry->addJs(REL_JSLIB.'/mootab.js');
    $this->_registry->addCss(REL_CSS.'/mootab.css');

    require_once('doc.php');
    require_once('chapter.php');
    require_once('subchapter.php');
    require_once('docContent.php');

    $id = cleanInput('get', 'id', 'int');
    $chapter_id = cleanInput('get', 'chapter', 'int');
    $subchapter_id = cleanInput('get', 'subchapter', 'int');

    $doc = new doc($id);

    // if doc doesn't exist or can't be viewed raise 404
    if(!$doc->id) {
      error::raise404();
    }
    if(!$doc->canView()) {
      error::raise403();
    }

    $chapter = null;
    $subchapter = null;
    $doc_subchapter = null;

    if($subchapter_id) {
      $subchapter = new subchapter($subchapter_id);
      $doc_subchapter = $this->docSubchapter($subchapter);
    }
    else {
      $chapter_ids = $doc->chapters();
      if(count($chapter_ids)) {
        $chapter = new chapter($chapter_ids[0]);
        $subchapter_ids = $chapter->subchapters();
        if(count($subchapter_ids)) {
          $subchapter = new subchapter($subchapter_ids[0]);
          $doc_subchapter = $this->docSubchapter($subchapter);
        }
      }
    }

    $this->_view->setTpl('borromeo_doc', array('css' => 'borromeo'));
    $this->_view->assign('doc', $doc);
    $this->_view->assign('controllers', $this->docControllers($doc, $chapter, $subchapter));
    $this->_view->assign('chapter', $chapter);
    $this->_view->assign('doc_subchapter', $doc_subchapter);

    return $this->_view->render();

  }

  private function docControllers($doc, $chapter, $subchapter) {

    // if no subchapter exists there is nothing to control
    if(is_null($subchapter)) {
      return '';
    }

    $content = $subchapter->content();

    // if neihter author nor tutor there is nothing to control
    if(!$content->canRevise()) {
      return '';
    }

    $current_revision = new docContentRevision($content->revision);
    $pending_revision = $content->pendingRevision();

    $controllers = array();

    // no pending revision so a new revision can be created
    if(!$pending_revision->id) {
      $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'editRevision', array('content_id' => $content->id))."\" class=\"right link icon icon_edit\" title=\"".__('edit')."\"></a>";
    }

    $view = new view();
    $view->setTpl('borromeo_doc_controllers', array('css' => 'borromeo'));
    $view->assign('controllers', $controllers);

    return $view->render();

  }

  /**
   * @brief document index
   */
  public function docIndex() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    $this->_registry->addJs(REL_JSLIB.'/Scrollable.js');
    $this->_registry->addCss(REL_CSS.'/Scrollable.css');
    $this->_registry->addJs(REL_JSLIB.'/borromeo.js');

    require_once('doc.php');

    $id = cleanInput('get', 'id', 'int');
    $content_id = cleanInput('get', 'content_id', 'int');
    $revision_id = cleanInput('get', 'revision_id', 'int');

    if($id) {
      $doc = new doc($id);
    }
    elseif($content_id) {
      require_once('docContent.php');
      $content = new docContent($content_id);
      $doc = $content->subchapter()->chapter()->doc();
    }
    elseif($revision_id) {
      require_once('docContentRevision.php');
      $revision = new docContentRevision($revision_id);
      $doc = $revision->content()->subchapter()->chapter()->doc();
    }

    $view = new view();
    $view->setTpl('borromeo_doc_index');
    $view->assign('doc', $doc);
    $view->assign('view_chapter_url', $this->_router->linkHref($this->_mdl_name, 'doc', array('id'=>$doc->id)));
    $view->assign('form_chapter_url', $this->_router->linkAjax($this->_mdl_name, 'formChapter', array('doc_id'=>$doc->id)));
    $view->assign('delete_chapter_url', $this->_router->linkAjax($this->_mdl_name, 'deleteChapter', array('doc_id'=>$doc->id)));
    $view->assign('form_subchapter_url', $this->_router->linkAjax($this->_mdl_name, 'formSubchapter', array('doc_id'=>$doc->id)));
    $view->assign('delete_subchapter_url', $this->_router->linkAjax($this->_mdl_name, 'deleteSubchapter', array('doc_id'=>$doc->id)));
    $view->assign('apply_order_url', $this->_router->linkAjax($this->_mdl_name, 'applyIndexOrder', array('doc_id'=>$doc->id)));
    $index = $view->render();

    return $index;

  }

  public function deleteChapter() {
    
    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('doc.php');
    require_once('chapter.php');

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

  public function deleteSubchapter() {
    
    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('doc.php');
    require_once('subchapter.php');

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
   * @brief Document annotations panel
   */
  public function docAnnotations() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));
    
    require_once('doc.php');
    require_once('chapter.php');
    require_once('subchapter.php');

    $id = cleanInput('get', 'id', 'int');
    $chapter_id = cleanInput('get', 'chapter_id', 'int');
    $subchapter_id = cleanInput('post', 'subchapter_id', 'int');
    $content_id = cleanInput('get', 'content_id', 'int');
    $revision_id = cleanInput('get', 'revision_id', 'int');

    $subchapter = null;

    if($content_id) {
      require_once('docContent.php');
      $content = new docContent($content_id);
      $subchapter_id = $content->subchapter()->id;
      $doc = $content->subchapter()->chapter()->doc();
    }
    elseif($revision_id) {
      require_once('docContentRevision.php');
      $revision = new docContentRevision($revision_id);
      $subchapter_id = $revision->content()->subchapter()->id;
      $doc = $revision->content()->subchapter()->chapter()->doc();
    }
    elseif($id) {
      $doc = new doc($id);
    }

    if($subchapter_id) {
      $subchapter = new subchapter($subchapter_id);
      $doc = $subchapter->chapter()->doc();
      $chapter_id = $subchapter->chapter()->id;
    }

    if(!$doc->id) return '';

    if($chapter_id) {
      $chapter = new chapter($chapter_id);
    }
    else {
      $chapters = $doc->chapters();
      if(count($chapters)) {
        $chapter = new chapter($chapters[0]);
      }
      else {
        return '';
      }
    }

    $subchapters = $chapter->subchapters();
    if(count($subchapters)) {
      $select_subchapters = array();
      $i = 0;
      foreach($subchapters as $sid) {
        $s = new subchapter($sid);
        $c = $s->content();
        if($c->canRevise()) {
          if($i == 0 && !$subchapter_id) {
            $subchapter = $s;
          }
          $select_subchapters[$s->id] = htmlVar($s->title);
          $i++;
        }
      }
    }
    else {
      return '';
    }

    if(!$subchapter or !$subchapter->id) return '';

    $content = $subchapter->content();

    if($content->canRevise()) {
      require_once('docContentNote.php');
      $notes = docContentNote::getFromContent($content->id);
      $view = new view();
    }
    else return '';

    $view = new view();
    $view->setTpl('borromeo_doc_annotations');
    $view->assign('select_subchapters', $select_subchapters);
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
   * @brief Visualization of a single chapter
   */
  private function docChapter($chapter) {

    require_once('chapter.php');
    require_once('subchapter.php');
    require_once('docContentRevision.php');

    $this->_registry->addJs(REL_JSLIB.'/moogallery.js');
    $this->_registry->addCss(REL_CSS."/moogallery.css");

    $contents = array();
    $subchapters = $chapter->subchapters();
    foreach($subchapters as $subchapter_id) {
      $subchapter = new subchapter($subchapter_id);
      $content = $subchapter->content();

      $current_revision = new docContentRevision($content->revision);
      $pending_revision = $content->pendingRevision();

      // pad 
      if($content->active_pad) {
        require_once(ABS_PHPLIB.DS.'etherpad-lite-client.php');
        if($content->canRevise()) {
          $instance = new EtherpadLiteClient($this->_etherpad_api_key, $this->_etherpad_api_url);

          try {
            $mappedGroup = $instance->createGroupIfNotExistsFor($content->id);
            $groupID = $mappedGroup->groupID;
            $author = $instance->createAuthorIfNotExistsFor($this->_registry->user->id, $this->_registry->user->username); 
            $authorID = $author->authorID;

            if(!isset($_COOKIE['sessionID']) or !$_COOKIE['sessionID']) {
              $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
              $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
              $sessionID = $sessionID->sessionID;
              setcookie("sessionID", $sessionID, time()+3600, "/");
            }

            $padList = $instance->listPads($groupID);
            $padList = $padList->padIDs;
            //var_dump($instance->getHtml($padList[0]));
            //
          }
          catch (Exception $e) {
            echo $e;
            $padList = array();
          }
        }
      }

      // current content tab
      $controllers = array();
      if($content->canRevise()) {
        if(!$pending_revision->id && !$content->active_pad) {
          $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'editRevision', array('content_id' => $content->id))."\" class=\"right link icon icon_edit\" title=\"".__('edit')."\"></a>";
        }
      }
      $title = ucfirst(__('publicRevision'));
      $author = null;
      $scontent = $this->revisionTab($current_revision, $title, $controllers, $author);

      // open pad
      if($content->active_pad && in_array($content->active_pad, $padList)) {
        $padID = $content->active_pad;
        $controllers = array();
        if($content->canManage()) {
          $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'deletePad', array('content_id' => $content->id, 'pad_id' => $padID))."\" class=\"right link icon icon_delete\" title=\"".__('delete')."\"></a>";
          $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'mergePad', array('content_id' => $content->id, 'pad_id' => $padID))."\" class=\"right link icon icon_merge\" title=\"".__('merge')."\"></a>";
        }
        $pad_url = $this->_etherpad_pad_url.$padID;
        $view = new view();
        $view->setTpl('borromeo_doc_revision_pad');
        $view->assign('content', $content);
        $view->assign('controllers', $controllers);
        $view->assign('current_revision', $current_revision);
        $view->assign('pad_url', $pad_url);
        $spad = $view->render();
      }
      else {
        $spad = '';
      }

      // current revision
      if($pending_revision->id && $content->canRevise() && !$content->active_pad) {
        $controllers = array();
        if($pending_revision->canEdit() && !$content->active_pad) {
          $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'editRevision', array('revision_id' => $pending_revision->id))."\" class=\"right link icon icon_edit\" title=\"".__('edit')."\"></a>";
          if($pending_revision->canMerge()) {
            $controllers[] = "<span onclick=\"".layerWindowCall(ucfirst(__('mergeRevision')), $this->_router->linkAjax($this->_mdl_name, 'formMergeRevision', array('revision_id' => $pending_revision->id)).'&revision_id='.$pending_revision->id)."\" class=\"right link icon icon_merge\" title=\"".__('merge')."\"></span>";
          }
        }
        $title = ucfirst(__('workRevision'));
        $rauthor = new user($pending_revision->user);
        $author = htmlVar($rauthor->lastname.' '.$rauthor->firstname);
        $srevision = $this->revisionTab($pending_revision, $title, $controllers, $author);
      }
      else {
        $srevision = '';
      }

      // revision history
      if($content->canManage()) {

        $revisions = docContentRevision::get(array('where' => "content='".$content->id."' AND merged IS NOT NULL", 'order' => 'merged_date DESC'));

        $view = new view();
        $view->setTpl('borromeo_doc_revision_history');
        $view->assign('revisions', $revisions);
        $view->assign('current_revision', $current_revision);
        $view->assign('view_url', $this->_router->linkAjax($this->_mdl_name, 'viewRevision'));
        $view->assign('registry', $this->_registry);
        $view->assign('create_from_url', $this->_router->linkHref($this->_mdl_name, 'createRevisionFrom'));
        $revision_history = $view->render();
      }
      else {
        $revision_history = '';
      }

      $view = new view();
      $view->setTpl('borromeo_doc_subchapter');
      $view->assign('subchapter', $subchapter);
      $view->assign('content_tab', $scontent);
      $view->assign('pad_tab', $spad);
      $view->assign('revision_tab', $srevision);
      $view->assign('revision_history_tab', $revision_history);
      $contents[] = $view->render();
    }

    $view = new view();
    $view->setTpl('borromeo_doc_chapter');
    $view->assign('chapter', $chapter);
    $view->assign('contents', $contents);

    return $view->render();

  }

  private function docSubchapter($subchapter) {

    require_once('docContentRevision.php');

    $content = $subchapter->content();

    $current_revision = new docContentRevision($content->revision);
    $pending_revision = $content->pendingRevision();

      // current content tab
      $controllers = array();
      if($content->canRevise()) {
        if(!$pending_revision->id) {
          $controllers[] = "<a href=\"".$this->_router->linkHref($this->_mdl_name, 'editRevision', array('content_id' => $content->id))."\" class=\"right link icon icon_edit\" title=\"".__('edit')."\"></a>";
        }
      }
      $title = ucfirst(__('publicRevision'));
      $author = null;
      $scontent = $this->revisionTab($current_revision, $title, $controllers, $author);

    $srevision = null;
    $revision_history = null;

    $view = new view();
    $view->setTpl('borromeo_doc_subchapter');
    $view->assign('subchapter', $subchapter);
    $view->assign('content_tab', $scontent);
    $view->assign('revision_tab', $srevision);
    $view->assign('revision_history_tab', $revision_history);

    return $view->render();

  }

  /**
   * @brief Create a note
   */
  public function formAnnotation() {

    access::check('main', $this->_registry->public_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentNote.php');
    require_once('docContentNoteFile.php');

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

    require_once('docContent.php');

    $note_id = cleanInput('post', 'note_id', 'int');
    $note = new docContentNote($note_id);
    $content = $note->content();

    if(!$content->canRevise()) {
      error::raise403();
    }

    $note->delete();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id)));
    exit();

  }

  /**
   * @brief Saves an annotation
   */
  public function saveAnnotation() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    $myform = new form('post', 'form_note', array('validation' => false));

    require_once('docContent.php');

    $note_id = cleanInput('post', 'note_id', 'int');
    $content_id = cleanInput('post', 'content_id', 'int');
    $content = new docContent($content_id);

    if(!$content->canRevise()) {
      error::raise403();
    }

    require_once('docContentNote.php');
    require_once('docContentNoteFile.php');

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

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id)));
    exit();

  }

  /**
   * @brief Create a new revision from another one
   */
  public function createRevisionFrom() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContentRevision.php');

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

    // copy media
    foreach($this->_media as $classname) {
      require_once($classname.'.php');
      $classname::copyFromToRevision($revision->id, $nrevision->id, array());
    }

    header('Location: '.$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $revision->content()->subchapter()->chapter()->doc()->id, 'chapter' => $revision->content()->subchapter()->chapter()->id)));
    exit;

  }

  /**
   * @brief Visualization of a revision
   */
  public function viewRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContentRevision.php');

    $revision_id = cleanInput('get', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);

    if(!$revision->content()->canManage()) {
      error::raise403();
    }

    $title = __('revision').'#'.$revision->id;
    $controllers = array();
    $ruser = new user($revision->user);
    $author = htmlVar($ruser->lastname.' '.$ruser->firstname);

    return $this->revisionTab($revision, $title, $controllers, $author);
  
  }

  /**
   * @brief Visualization of the revision tab
   */
  private function revisionTab($revision, $title, $controllers, $author) {

    $media_tabs = array();

    $view = new view();
    $view->setTpl('borromeo_doc_revision');
    $view->assign('title', $title);
    $view->assign('author', $author);
    $view->assign('controllers', $controllers);
    $view->assign('revision', $revision);
    $view->assign('media_tabs', $media_tabs);

    return $view->render();

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
   * @brief Action of merginga revision into the content
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

    $doc = $content->subchapter()->chapter()->doc();
    $doc->updateLastEdit();

    $revision->merged = 1;
    $revision->merged_user = $this->_registry->user->id;
    $revision->merged_date = $this->_registry->dtime->now('%Y-%m-%d %H:%i:%s');
    $revision->merged_comment = cleanInput('post', 'comment', 'string');
    $revision->saveData();

    header('Location: '.$this->_router->linkHref($this->_mdl_name, 'doc', array('id'=>$doc->id, 'chapter' => $content->subchapter()->chapter()->id)));
    exit();

  }

  /**
   * @brief Edit revision form
   */
  public function editRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');

    $revision_id = cleanInput('get', 'revision_id', 'int');
    // existing revision
    if($revision_id) {
      $revision = new docContentRevision($revision_id);
      $content = $revision->content();
    }
    // new revision
    else {
      $content_id = cleanInput('get', 'content_id', 'int');
      $content = new docContent($content_id);
      $revision = new docContentRevision($content->revision);
    }
    if(($revision_id && !$revision->canEdit()) or !$content->canRevise() or $content->active_pad) {
      error::raise403();
    }

    // form text
    $myform = new form('post', 'form_revision', array('validation' => true));
    $sform = $myform->sform($this->_router->linkHref($this->_mdl_name, 'saveRevision'), 'text', array('upload' => true));
    $sform .= $myform->hidden('revision_id', $revision_id);
    $sform .= $myform->hidden('starting_revision_id', $revision->id);
    $sform .= $myform->hidden('content_id', $content->id);
    $text_form = $myform->ctextarea('text', $revision->text, __('text'), array('editor' => true, 'required' => true));
    $text_form .= chargeEditor('.html');
    $cform = $myform->cinput('submit', 'submit', __('submit'), '', null);
    $cform .= $myform->cform();

    // media forms
    $media_forms = array();


    $view = new view();
    $view->setTpl('borromeo_doc_editrevision', array('css' => 'borromeo'));
    $view->assign('content', $content);
    $view->assign('revision_id', $revision_id);
    $view->assign('revision', $revision);
    $view->assign('create_etherpad', anchor($this->_router->linkHref($this->_mdl_name, 'createPad', array('content_id' => $content->id)), __('createNewPad')));
    $view->assign('sform', $sform);
    $view->assign('text_form', $text_form);
    $view->assign('cform', $cform);
    $view->assign('media_forms', $media_forms);

    return $view->render();

  }

  public function createPad() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');
    require_once(ABS_PHPLIB.DS.'etherpad-lite-client.php');

    $content_id = cleanInput('get', 'content_id', 'int');
    $content = new docContent($content_id);
    $current_revision = new docContentRevision($content->revision);
    $pending_revision = $content->pendingRevision();

    $text = $pending_revision->id ? $pending_revision->text : $current_revision->text;

    $text = preg_replace("#\t#", "", $text);
    $text = preg_replace("#\r\n#", "", $text);

    $link_error = $this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id));

    if(!$content->canManage()) {
      error::raise403();
    }

    $instance = new EtherpadLiteClient($this->_etherpad_api_key, $this->_etherpad_api_url);

    // Step 1, get GroupID of the userID where userID is OUR userID and NOT the userID used by Etherpad
    try {
      $mappedGroup = $instance->createGroupIfNotExistsFor($content->id);
      $groupID = $mappedGroup->groupID;
    } 
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadGroup'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    // Create a session
    /* get Mapped Author ID based on a value from your web application such as the userID */
    try {
      $author = $instance->createAuthorIfNotExistsFor($this->_registry->user->id, $this->_registry->user->username); 
      $authorID = $author->authorID;
    } 
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadAuthor'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }
    $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
    $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
    $sessionID = $sessionID->sessionID;
    $_SESSION['borromeo_pad_sid'] = $sessionID;
    echo $sessionID;
    setcookie("sessionID", $sessionID, time()+3600, "/");
    try {
      $newPad = $instance->createGroupPad($groupID, __('realtimeRevision'), ''); 
      $padID = $newPad->padID;
      $instance->setHtml($padID, "<html><head></head><body>".$text."</body></html>");
      echo "Created new pad with padID: $padID\n\n";
      $content->active_pad = $padID;
      $content->saveData();

    }
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadPad'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id)));
    exit();

  }
  
  public function deletePad() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');
    require_once(ABS_PHPLIB.DS.'etherpad-lite-client.php');

    $content_id = cleanInput('get', 'content_id', 'int');
    $content = new docContent($content_id);
    $pad_id = cleanInput('get', 'pad_id', 'string');

    if(!$content->canManage()) {
      error::raise403();
    }

    $link_error = $this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id));

    $instance = new EtherpadLiteClient($this->_etherpad_api_key, $this->_etherpad_api_url);

    // Step 1, get GroupID of the userID where userID is OUR userID and NOT the userID used by Etherpad
    try {
      $mappedGroup = $instance->createGroupIfNotExistsFor($content->id);
      $groupID = $mappedGroup->groupID;
    } 
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadGroup'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    // Create a session
    /* get Mapped Author ID based on a value from your web application such as the userID */
    try {
      $author = $instance->createAuthorIfNotExistsFor($this->_registry->user->id, $this->_registry->user->username); 
      $authorID = $author->authorID;
    } 
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadAuthor'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }
    
    if(!$_COOKIE['sessionID']) {
      $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
      $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
      $sessionID = $sessionID->sessionID;
      setcookie("sessionID", $sessionID, time()+3600, "/");
    }
    try {
      $instance->deletePad($pad_id); 
      $content->active_pad = '';
      $content->saveData();

    }
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantDeleteEtherpadPad'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id)));
    exit();

  }

  public function mergePad() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');
    require_once(ABS_PHPLIB.DS.'etherpad-lite-client.php');

    $content_id = cleanInput('get', 'content_id', 'int');
    $content = new docContent($content_id);
    $pad_id = cleanInput('get', 'pad_id', 'string');

    if(!$content->canManage()) {
      error::raise403();
    }

    $link_error = $this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id));

    $instance = new EtherpadLiteClient($this->_etherpad_api_key, $this->_etherpad_api_url);

    // Step 1, get GroupID of the userID where userID is OUR userID and NOT the userID used by Etherpad
    try {
      $mappedGroup = $instance->createGroupIfNotExistsFor($content->id);
      $groupID = $mappedGroup->groupID;
    } 
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadGroup'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    // Create a session
    /* get Mapped Author ID based on a value from your web application such as the userID */
    try {
      $author = $instance->createAuthorIfNotExistsFor($this->_registry->user->id, $this->_registry->user->username); 
      $authorID = $author->authorID;
    } 
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantCreateEtherpadAuthor'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }
    
    if(!$_COOKIE['sessionID']) {
      $validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future
      $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
      $sessionID = $sessionID->sessionID;
      setcookie("sessionID", $sessionID, time()+3600, "/");
    }
    try {
      $htmlObj = $instance->getHtml($pad_id); 

      $current_revision = new docContentRevision($content->revision);
      $pending_revision = $content->pendingRevision();
      if($pending_revision->id) {
        $pending_revision->text = $htmlObj->html;
        $pending_revision->saveData();
        $pending_revision->updateLastEdit();
      }
      else {
        $revision = new docContentRevision(null);
        $revision->content = $content->id;
        $revision->user = $this->_registry->user->id;
        $revision->text = $htmlObj->html;
        $revision->saveData();
        $revision->updateLastEdit();
      }

      $instance->deletePad($pad_id); 
      $content->active_pad = '';
      $content->saveData();

    }
    catch (Exception $e) {
      exit(Error::errorMessage(array(
        'message' => __('cantMergeEtherpadPad'). ' error: '.$e->getMessage(),
        'hint' => __('contactServerAdmin')
      ), $link_error));
    }

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id)));
    exit();
  }

  /**
   * @brief Save a revision
   */
  public function saveRevision() {

    access::check('main', $this->_registry->private_view_privilege, array("exitOnFailure"=>true));

    require_once('docContent.php');
    require_once('docContentRevision.php');

    $revision_id = cleanInput('post', 'revision_id', 'int');
    $revision = new docContentRevision($revision_id);

    $starting_revision_id = cleanInput('post', 'starting_revision_id', 'int');
    $starting_revision = new docContentRevision($starting_revision_id);

    $content_id = cleanInput('post', 'content_id', 'int');
    $content = new docContent($content_id);

    if(!$content->canRevise() or ($revision_id and !$revision->canEdit())) {
      error::raise403();
    }

    $text = cleanInput('post', 'text', 'html');

    $revision->content = $content->id;
    if(!$revision->id) {
      $revision->user = $this->_registry->user->id;
    }
    $revision->text = $text;
    $revision->saveData();
    $revision->updateLastEdit();

    foreach($this->_media as $classname) {
      require_once($classname.'.php');
      $res = $classname::saveRevision($revision, $starting_revision);
    }

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $content->subchapter()->chapter()->doc()->id, 'chapter' => $content->subchapter()->chapter()->id)));
    exit();

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

    $form = $at->editFields(array('insert'=>$insert, 'f_s'=>$f_s, 'action'=>$this->_router->linkHref($this->_mdl_name, 'saveChapter', array('doc_id' => $doc_id))));

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

    $at = chapter::adminTable($doc);

    $at->saveFields('chapter');

    $doc->updateLastEdit();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $doc_id)));
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

    $form = $at->editFields(array('insert'=>$insert, 'f_s'=>$f_s, 'action'=>$this->_router->linkHref($this->_mdl_name, 'saveSubchapter', array('doc_id' => $doc_id, 'chapter_id' => $chapter_id))));

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

    require_once('docContent.php');
    require_once('subchapter.php');
    require_once('chapter.php');
    require_once('doc.php');

    $insert = !$_POST['id'][0] ? true : false;

    $doc_id = cleanInput('get', 'doc_id', 'int');
    $chapter_id = cleanInput('get', 'doc_id', 'int');
    $doc = new doc($doc_id);
    $chapter = new chapter($chapter_id);

    if(!$doc->canManage()) {
      error::raise403();
    }

    $at = subchapter::adminTable($chapter);

    $sid_a = $at->saveFields('subchapter');

    if($insert) {
      $content_id = docContent::createEmpty($sid_a[0]);
    }

    $doc->updateLastEdit();

    header("Location: ".$this->_router->linkHref($this->_mdl_name, 'doc', array('id' => $doc_id)));
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

    require_once('doc.php');

    $at = doc::adminTable($this);

    $table = $at->manage();

    $this->_view->setTpl('manage_table');
    $this->_view->assign('title', ucfirst(__("manageDoc")));
    $this->_view->assign('table', $table);

    return $this->_view->render();

  }

}

?>
