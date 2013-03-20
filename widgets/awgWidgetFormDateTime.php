<?php

/**
 * Description of awgWidgetFormJQueryDate
 *
 * @author io
 */
class awgWidgetFormDateTime extends sfWidgetFormDateTime
{
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addOption('can_be_empty', true);
  }


  public function getDateWidget($attributes = array())
  {
    return parent::getDateWidget($attributes);
  }

  public function getTimeWidget($attributes = array())
  {
    return parent::getTimeWidget($attributes);
  }
}