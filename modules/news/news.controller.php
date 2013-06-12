<?php

require_once('news.php');

class newsController extends controller {

	private $_efp;

	function __construct() {

		parent::__construct();

		$this->_cpath = dirname(__FILE__);
		$this->_mdl_name = "news";

		// privileges
		$this->_class_privilege = $this->_mdl_name;
		$this->_admin_privilege = 1;

		$this->_efp = 10;
	}

	public function last() {
	
		$ns = news::get(array("where"=>"published='1'", "limit"=>array(0, 3)));

		$news = array();
		foreach($ns as $n) {
			$news[] = array(
				"date"=>$this->_registry->dtime->view($n->date, 'date'),
				"title"=>htmlVar($n->title),
				"text"=>htmlVar($n->text),
				"image"=>$n->image ? REL_UPLOAD."/news/".$n->image : null,		
				"thumb"=>$n->image ? REL_UPLOAD."/news/thumb_".$n->image : null		
			);
		}

		$this->_registry->addJs('http://ajs.otto.to.it/sources/dev/ajs/ajs.js');

		$link_archive = anchor($this->_router->linkHref($this->_mdl_name, 'archive'), __("archive"));

		$this->_view->setTpl('news_last', array("css"=>'news'));
		$this->_view->assign('news', $news);
		$this->_view->assign('link_archive', $link_archive);

		return $this->_view->render();
	}

	public function archive() {
		
		$where = "published='1'";
		$pag = new pagination($this->_efp, $this->_registry->db->getNumRecords(TBL_NEWS, $where, 'id'));
		
		$limit = array($pag->start(), $this->_efp);

		$ns = news::get(array("where"=>$where, "order"=>"date DESC", "limit"=>$limit));

		$news = array();
		foreach($ns as $n) {
			$news[] = array(
				"date"=>$this->_registry->dtime->view($n->date, 'date'),	
				"title"=>htmlVar($n->title),	
				"text"=>htmlVar($n->text),	
				"image"=>$n->image ? REL_UPLOAD."/news/".$n->image : null		
			);
		}

		$this->_view->setTpl('news_archive', array("css"=>'news'));
		$this->_view->assign('news', $news);
		$this->_view->assign('psummary', $pag->summary());
		$this->_view->assign('pnavigation', $pag->navigation());

		return $this->_view->render();
	}

	public function manage() {
	
		access::check($this->_class_privilege, $this->_admin_privilege, array("exitOnFailure"=>true));

		$s_fields = array(
			"image"=>array(
				"type"=>"image",
				"label"=>__('image'),
				"path"=>ABS_UPLOAD.DS.'news',
				"preview"=>true,
				"rel_path"=>REL_UPLOAD.'/news',
				"extensions"=>array("jpg", "png", "gif"),
				"resize"=>true,
				"resize_width"=>600,
				"make_thumb"=>true,
				"thumb_width"=>100	
			),
			"published"=>array(
				"type"=>"bool",
				"required"=>true,
				"true_label"=>__("yes"),
				"false_label"=>__("no")	
			)
		);

		$html_fields = array("text");

		$at = new adminTable(TBL_NEWS, array("changelist_fields"=>array("date", "title", "image", "published"), "editor"=>true));
		$at->setSpecialFields($s_fields);
		$at->setHtmlFields($html_fields);

		$table = $at->manage();

		$this->_view->setTpl('manage_table');
		$this->_view->assign('title', __("ManageNews"));
		$this->_view->assign('table', $table);

		return $this->_view->render();
	}



}

?>
