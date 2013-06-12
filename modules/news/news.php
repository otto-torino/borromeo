<?php

class news extends model {

	function __construct($id) {
	
		$this->_tbl_data = TBL_NEWS;
		parent::__construct($id);

	}

	public static function get($opts=null) {
		
		$registry = registry::instance();

		$objs = array();

		$where = gOpt($opts, 'where', '');
		$limit = gOpt($opts, 'limit', null);
		$order = gOpt($opts, 'order', 'date DESC');

		$rows = $registry->db->autoSelect("id", TBL_NEWS, $where, $order, $limit);

		foreach($rows as $row) $objs[] = new news($row['id']);

		return $objs;

	}

}

?>
