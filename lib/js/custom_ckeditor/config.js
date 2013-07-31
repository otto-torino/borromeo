/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

var config_css = [];
$$('link[rel=stylesheet]').each(function(el) {
  config_css.push(el.get('href'));
})

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
  config.allowedContent = true;
  config.contentsCss = config_css;
  config.filebrowserBrowseUrl = '/lib/php/ckfinder/ckfinder.html';
  config.filebrowserImageBrowseUrl = '/lib/php/ckfinder/ckfinder.html?Type=Images';
  config.filebrowserFlashBrowseUrl = '/lib/php/ckfinder/ckfinder.html?Type=Flash';
  config.filebrowserUploadUrl = '/lib/php/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
  config.filebrowserImageUploadUrl = '/lib/php/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
  config.filebrowserFlashUploadUrl = '/lib/php/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
};
