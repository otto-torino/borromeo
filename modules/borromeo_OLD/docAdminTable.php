<?php

class docAdminTable extends adminTable {

  private $_permission;

  /**
   * @param {String} $permission permission of the user: manage | create
   */
  function __construct($table, $permission, $opts=null) {

    $this->_permission = $permission;
    parent::__construct($table, $opts);

  }

  protected function formElement($myform, $fname, $field, $id, $opts=null) {

    $id_f = preg_replace("#\s#", "_", $id); // replace spaces with '_' in form names as POST do itself

    $records = $this->_registry->db->autoSelect("*", $this->_table, $this->_primary_key."='$id'", null);
    $value = count($records) ? $records[0][$fname] : null;

    if($this->_permission == 'create' && $fname == 'tutor_groups') {
      return $myform->hidden($fname."_".$id_f.'[]', $value ? $value : '');
    }
    elseif($this->_permission == 'create' && $fname == 'tutor_users') {
      return $myform->hidden($fname."_".$id_f.'[]', $value ? $value : $this->_registry->user->id);
    }
    else {
      return parent::formElement($myform, $fname, $field, $id, $opts);
    }
  }
  
  protected function saveRecord($pk, $pkeys, $model_name = null) {
    
    if(!in_array($pk, $this->_edit_deny)) {
      if(!$pk) {
        $insert = true;
      }
      else {
        $insert = false;
      }
    }

    $id = parent::saveRecord($pk, $pkeys, $model_name);

    if($insert) {
      mkdir(ABS_UPLOAD.DS.'doc'.DS.$id, 0755, true);
    }

  }

}
