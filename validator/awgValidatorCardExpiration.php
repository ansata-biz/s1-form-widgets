<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of awgValidatorCardExpiration
 *
 * @author io
 */
class awgValidatorCardExpiration extends sfValidatorDate {

  public function __construct($options = array(), $messages = array()) {
    $options['min'] = mktime(0, 0, 0, date("m"), 1, date("Y")); // current month
    $options['with_time'] = false;

    parent::__construct($options, $messages);
  }

  protected function convertDateArrayToString($value) {
    if (is_array($value) && isset($value['year']) && isset($value['month'])) {
      $value['day'] = 1;
    }
    return parent::convertDateArrayToString($value);
  }
}
?>
