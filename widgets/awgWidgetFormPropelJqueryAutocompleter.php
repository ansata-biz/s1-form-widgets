<?php
/**
 * Created by AWG, Alex <alex@ansata.biz>
 * Date: 24.04.12
 * Time: 21:52
 */
class awgWidgetFormPropelJQueryAutocompleter extends sfWidgetFormPropelJQueryAutocompleter
{

  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('query_methods', array());

    parent::configure($options, $attributes);
  }


  function toString($value)
  {
    $criteria = PropelQuery::from($this->getOption('model'));

    foreach ($this->getOption('query_methods') as $methodName => $methodParams)
    {
      if(is_array($methodParams))
      {
        call_user_func_array(array($criteria, $methodName), $methodParams);
      }
      else
      {
        $criteria->$methodParams($value);
      }
    }


    $object = $criteria->findOne();

    $method = $this->getOption('method');

    if (!method_exists($this->getOption('model'), $method))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->getOption('model'), $method, __CLASS__));
    }

    return !is_null($object) ? $object->$method() : '';
  }
}
