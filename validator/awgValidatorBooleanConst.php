<?php
/**
 * Date: 02.11.12
 * Time: 20:21
 * Author: Ivan Voskoboynyk
 */
class awgValidatorBooleanConst extends sfValidatorBoolean
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addOption('restrict_to', true);
  }

  protected function doClean($value)
  {
    $value = parent::doClean($value);

    if ($this->getOption('restrict_to') !== $value)
    {
      throw new sfValidatorError($this, 'required', array('value' => $value));
    }

    return $value;
  }
}
