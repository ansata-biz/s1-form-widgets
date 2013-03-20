<?php
/**
 * Date: 19.03.13
 * Time: 22:56
 * Author: Ivan Voskoboynyk
 */

class awgWidgetFormChoiceLinkedAjax extends awgWidgetFormChoiceLinkedMap
{
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addRequiredOption('url'); // map master widget values to slave widget options collections
    $this->addOption('map', array());
    $this->addOption('choices', array());
    $this->addOption('use_cache', true);
  }

  /**
   * This method should generate a valid javascript callback function.
   * Generated javascript function will be used every time the master widget value will be changed.
   * Function is called with one argument passed: new master widget value.
   *
   * @param $masterWidgetId
   * @param $slaveWidgetId
   * @return void
   */
  protected function renderChoicesJavascriptCallback($masterWidgetId, $slaveWidgetId)
  {
    $url = json_encode($this->getOption('url'));
    $masterName = $this->getOption('parent_widget');
    $masterJsKey = json_encode($masterName);

    if ($map = $this->getOption('map'))
    {
      $initial = json_encode($this->convertChoicesMap($map));
    }
    else
    {
      $initial = '{}';
    }

    $use_cache = $this->getOption('use_cache') ? 'true' : 'false';

    return <<<JAVASCRIPT
(function() {
  var use_cache = $use_cache;
  var cache = $initial;
  return function(master_value) {

    if (use_cache && cache[master_value])
      return cache[master_value];

    return jQuery.ajax({
      url: $url,
      data: { $masterJsKey: master_value },
      dataType: 'json',
      type: 'get',
      success: function(data) {
        if (use_cache) cache[master_value] = data;
      }
    });
  }
})()
JAVASCRIPT;
  }
}