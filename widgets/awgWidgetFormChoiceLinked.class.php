<?php
/**
 * @author io
 */
 
abstract class awgWidgetFormChoiceLinked extends sfWidgetFormChoice
{

  /**
   * This method should generate a valid javascript callback function.
   * Generated javascript function will be used every time the master widget value will be changed.
   * Function is called with one argument passed: new master widget value.
   *
   * @abstract
   * @param $masterWidgetId
   * @param $slaveWidgetId
   * @return string
   */
  protected abstract function renderChoicesJavascriptCallback($masterWidgetId, $slaveWidgetId);
  
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addRequiredOption('parent_widget'); //widget to get value from
    $this->addOption('disable_empty', false);
    $this->addOption('disabled_text', null);
    $this->addOption('js_events', 'keyup click change');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $html = parent::render($name, $value, $attributes, $errors);

    $masterWidgetName = $this->getOption("parent_widget");
    $masterWidgetId = $this->getParent()->generateId( $this->getParent()->generateName($masterWidgetName) );

    $slaveWidgetId = $this->generateId($name, $value);
    $javascript = $this->renderJavascript($masterWidgetId, $slaveWidgetId, $value);

    return $html . $javascript;
  }

  protected function renderJavascript($masterWidgetId, $slaveWidgetId, $value)
  {
    $callback = $this->renderChoicesJavascriptCallback($masterWidgetId, $slaveWidgetId);
    $initialValue = json_encode($value);

    if ($this->getOption('disable_empty'))
    {
      if ($this->getOption('disabled_text'))
      {
        $disableWidget = sprintf(
          "{ slave.attr('disabled', 'disabled'); slave.append('<option data-disabled-text-option=\"true\"/>').find('option').text(%s); }",
          json_encode((string) $this->translate($this->getOption('disabled_text')))
        );
      }
      else
      {
        $disableWidget = "{ slave.attr('disabled', 'disabled'); }";
      }
      $enableWidget = "{ slave.removeAttr('disabled'); }";
      $disableEmpty = "if (slave.find('option').not('[data-disabled-text-option]').length == 0) $disableWidget else $enableWidget;";
    }
    else
    {
      $disableEmpty = '';
    }

    return <<<JAVASCRIPT

<script type="text/javascript">
$(function(){
    var slave = jQuery('#$slaveWidgetId');

    var callback = $callback;

    var set_widget_values = function(widget, values) {
      jQuery.each(values, function(key, value) {
        jQuery('<option/>').attr('value', value[0]).text(value[1]).appendTo(widget);
      });
    }

    var update_slave_widget = function(values, old_value) {
      if (values === false) {
        // do nothing
      } else {
        // remove old options
        slave.find('option').remove();
        // fill new options
        if (values) { // not empty array of values
          set_widget_values(slave, values);
          // try to preserve old widget value
          slave.val(old_value).change();
        } else {
          slave.change();
        }
        {$disableEmpty}
      }
    }

    var update_values = function() {
      var master_value = jQuery(this).val();
      var old_slave_value = slave.val();
      var new_slave_values = callback(master_value);

      if (jQuery.when) // check if Deferred is supported
        // bulletproof deferred-not-deferred result using
        jQuery.when(new_slave_values).done(function(x){ update_slave_widget(x,old_slave_value); });
      else
        update_slave_widget(new_slave_values, old_slave_value);
    }
    jQuery('#{$masterWidgetId}').bind('{$this->getOption('js_events')}',update_values).each(update_values);
    slave.val({$initialValue}).change();
    {$disableEmpty}
})
</script>

JAVASCRIPT;
  }
}
