<?php
/**
 * @author io
 */
 
class awgWidgetFormChoiceLinkedMap extends awgWidgetFormChoiceLinked
{
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addRequiredOption('map'); // map master widget values to slave widget options collections
  }

  /**
   * @param array $map
   * @return array
   */
  protected function convertChoicesMap($map)
  {
    $converted_map = array();
    foreach ($map as $master_value => $slave_map)
    {
      $conveted_slave_map = array();
      foreach ($slave_map as $slave_key => $slave_value)
      {
        $conveted_slave_map[] = array($slave_key, $slave_value);
      }
      $converted_map[$master_value] = $conveted_slave_map;
    }
    return $converted_map;
  }

  protected function renderChoicesJavascriptCallback($masterWidgetId, $slaveWidgetId)
  {
    $map = $this->convertChoicesMap($this->getOption('map'));
    $jsMap = json_encode($map);
    return <<<JAVASCRIPT
function(master_value) {
  var map = $jsMap;
  if (typeof map[master_value] !== 'undefined') {
    return map[master_value];
  }
  return null;
}
JAVASCRIPT;
  }
}
