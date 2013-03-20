<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of awgWidgetFormCardExpiration
 *
 * @author io
 */
class awgWidgetFormCardExpiration extends sfWidgetFormDate {

  protected function configure($options = array(), $attributes = array()) {
    parent::configure($options, $attributes);
    $years = range(date('Y'), date('Y') + 8);
    $this->addOption('years', array_combine($years, $years));
  }

  public function render($name, $value = null, $attributes = array(), $errors = array()) {
    $str = parent::render($name, $value, $attributes, $errors);
    return '<div class="exp-date">' . $str . '</div>';
  }

}

?>
