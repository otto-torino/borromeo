<?php
/**
 * @file interface.docMedia.php
 * @brief Contains the doc media interface.
 *
 * Defines a common interface for all doc media
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @ingroup borromeo
 * @brief Doc media interface. 
 *
 * Defines a common interface for all doc media.
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.1
 * @date 2013
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
interface DocContentMedia {

  function __construct($id);

  public static function revisionForm($form, $revision);

  public static function saveRevision($revision, $starting_revision);

  public static function revisionTab($revision);

  public static function copyFromToRevision($from_revision_id, $to_revision_id, $del_image);

}

?>
