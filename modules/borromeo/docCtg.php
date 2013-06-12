<?php
/**
 * @file docCtg.php
 * @brief Contains the document category model
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Document category model class
 *
 * Model fields:
 * - **id** int(1): primary key
 * - **name** varchar: category name
 * - **description** text: category description
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
class docCtg extends model {

  /**
   * @brief Constructs a document category instance
   * @param int $id doc ctg identifier
   * 
   * @return document instance
   */
  function __construct($id) {

    $this->_tbl_data = TBL_B_DOC_CTG;

    parent::__construct($id);

  }

  /**
   * @brief Returns document ctg objects
   * @param array $opts associative array of options:
   *    - where: the where clause
   *    - order: the order clause
   *    - get_id: whether to return only the id
   * @return array of document ctg objects
   */
  public static function get($opts = array()) {

    $db = db::instance();
    $res = array();

    $where = gOpt($opts, 'where', '');
    $order = gOpt($opts, 'order', '');
    $get_id = gOpt($opts, 'get_id', false);

    $rows = $db->autoSelect('id', TBL_B_DOC_CTG, $where, $order);
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] =$get_id ? $row['id'] : new docCtg($row['id']);
      }
    }

    return $res;

  }

}

?>
