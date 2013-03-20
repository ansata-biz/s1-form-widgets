<?php
/**
 * @author io
 */
 
abstract class awgValidatorSchemaChoiceLinked extends sfValidatorSchema
{
  public function __construct($parentField, $linkedField, $options = array(), $messages = array())
  {
    $this->addOption('parent_widget', $parentField);
    $this->addOption('linked_widget', $linkedField);

    $this->addOption('throw_global_error', false);

    parent::__construct(null, $options, $messages);
  }

  protected function doClean($values)
  {
    if (null === $values)
    {
      $values = array();
    }

    if (!is_array($values))
    {
      throw new InvalidArgumentException('You must pass an array parameter to the clean() method');
    }
    
    $parentValue  = isset($values[$this->getOption('parent_widget')]) ? $values[$this->getOption('parent_widget')] : null;
    $linkedValue = isset($values[$this->getOption('linked_widget')]) ? $values[$this->getOption('linked_widget')] : null;

    $valid = $this->isPairValid($parentValue, $linkedValue);
    
    if (!$valid)
    {
      $error = new sfValidatorError($this, 'invalid', array(
        'parent_widget'  => $parentValue,
        'linked_widget' => $linkedValue
      ));
      if ($this->getOption('throw_global_error'))
      {
        throw $error;
      }

      throw new sfValidatorErrorSchema($this, array($this->getOption('linked_widget') => $error));
    }

    return $values;
  }

  protected abstract function isPairValid($parentValue, $linkedValue);
}
