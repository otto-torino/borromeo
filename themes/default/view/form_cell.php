<?php
/**
 * @file /var/www/jeff.git/themes/default/view/form_cell.php
 * @ingroup default_theme forms
 * @brief Template for form custom user inputs, see @ref form::freeInput
 *
 * Available variables:
 * - **idleft**: id attribute of the left content
 * - **cleft**: left content
 * - **idright**: id attribute of the right content
 * - **cright**: right content
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.99
 * @date 2011-2012
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */
?>
<label<?= $idleft ? " id=\"".$idleft."\"" : ""?> class=""><?= $cleft ?></label>
<div<?= $idright ? " id=\"".$idright."\"" : ""?> style="display:inline-block"><?= $cright ?></div>
<br class="formRowBreak" />
