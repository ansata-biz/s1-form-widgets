<?php
/**
 * @author io
 */
 
class awgValidatorSchemaChoiceLinkedMap extends awgValidatorSchemaChoiceLinked
{
  public function __construct($parentField, $linkedField, $map, $options = array(), $messages = array())
  {
    $this->addOption('map', $map);
    parent::__construct($parentField, $linkedField, $options, $messages);
  }

  protected function isPairValid($parentValue, $linkedValue)
  {
    $map = $this->getOption('map');
    return isset($map[$parentValue]) && in_array($linkedValue, $map[$parentValue]);
  }
}
