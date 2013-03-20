<?php

/**
 * This class is indended to make one widget disabled
 * depending on the state (selected value) of another widget.
 *
 * <b>IMPORTANT</b>: This widget is highly relying on jQuery. It SHOULD be
 * linked to page.
 *
 * <b>Usage</b>. Define widget in your form class:
 * <pre>
 * $this->widgetSchema['test_dependency'] = new awgWidgetFormDependentEnabling(array(
 *    'parent_widget' => 'name',
 *    'dependent_widget_object' => new sfWidgetFormInputText(),
 *    'enable_on' => '/^\d+$/'
 *  ));
 * </pre>
 *
 * "parent_widget" - name of a form variable with a widget
 *
 * "enable_on" option can be one of:
 * - single value
 * - array of possible values (it is used for IN condition)
 * - regexp (a valid JS regexp expression)
 *
 * Also there is a "disable_on" option which works exactly the same way.
 * It can be used for inverted conditions.
 *
 * <b>For example:</b>
 * Instead of defining big list of "good" values:
 * <pre>
 *  "enable_on" => array("excellent", "good", "rather good", "affordable")
 * </pre>
 *
 * you can define a small list of "bad" values:
 * <pre>
 *  "disable_on" => array("bad", "horrible")
 * </pre>
 *
 * @author io
 */
class awgWidgetFormDependentEnabling extends sfWidgetForm {
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('parent_widget');
    $this->addRequiredOption('dependent_widget_object');
    $this->addOption('enable_on', null);
    $this->addOption('disable_on', null);
  }

  /**
   * Render widget + logic.
   * @param string $dependent_widget_name
   * @param mixed $value
   * @param array $attributes
   * @param array $errors
   * @return string
   */
  public function render($dependent_widget_name, $value = null, $attributes = array(), $errors = array())
  {
    /* @var $parent sfWidgetForm */
    $master_widget_name = $this->getOption('parent_widget');
    $parent = $this->getParent();
    $master_widget_id = $parent->generateId( $parent->generateName($master_widget_name) );
    
    /* @var $dependent_widget sfWidgetForm */
    $dependent_widget = $this->getOption('dependent_widget_object');
    $dependent_widget->setIdFormat($this->getIdFormat());
    $dependent_widget_id = $dependent_widget->generateId($dependent_widget_name);
    $dependent_widget->setAttribute('data-dependent', $dependent_widget_id);
    
    $html = $dependent_widget->render($dependent_widget_name, $value, $attributes, $errors);
    $script = $this->renderJavascript($master_widget_id, $this->buildSelectors($dependent_widget_id));
    
    return $html . $script;
  }

  /**
   * Return an array of jQuery selectors to be tried to find a depenendent inputs.
   * The reason of this is impossibility to find an input by ID (especially
   * when there is a complex composed widget). The only way to garantee the input
   * will be found is to try different queries.
   * 
   * @param string $dependent_widget_id
   * @return array A list of selectors
   */
  protected function buildSelectors($dependent_widget_id) {
    return array(
      "#{$dependent_widget_id}",
      "input[data-dependent=\"{$dependent_widget_id}\"]",
      "select[data-dependent=\"{$dependent_widget_id}\"]"
    );
  }

  /**
   * Return a string containing JS needed for proper work.
   * @param string $master_widget_id
   * @param array $selectors
   * @return string Rendered javascript
   */
  protected function renderJavascript($master_widget_id, $selectors) {

    $enable_on = $this->getOption('enable_on');
    $disable_on = $this->getOption('disable_on');

    if (empty($enable_on) && empty($disable_on)) {
      throw new InvalidArgumentException('At least one of "enable_on" or "disable_on" options should be defined.');
    }

    $enable_input = 'enable_input(input)';
    $disable_input = 'disable_input(input)';

    $variables_declaration = '';
    $change_state_code = '';

    if (empty($disable_on)) {
      $enable_block_else = $disable_input;
    } else {
      $enable_block_else = null;
    }
    $enable_block = $this->buildConditionalBlock($enable_on, 'enable_on', $enable_input, $enable_block_else);
    if ($enable_block) {
      $variables_declaration .= $enable_block['vars'] . "\n";
      $change_state_code .= $enable_block['script'] . "\n";
    }

    if (empty($enable_on)) {
      $disable_block_else = $enable_input;
    } else {
      $disable_block_else = null;
    }
    $disable_block = $this->buildConditionalBlock($disable_on, 'disable_on', $disable_input, $disable_block_else );
    if ($disable_block) {
      $variables_declaration .= $disable_block['vars'] . "\n";
      $change_state_code .= $disable_block['script'] . "\n";
    }

    $selectors_json = json_encode($selectors);

    $script = <<<JAVASCRIPT
<script type="text/javascript">
 jQuery(function(){
   (function($){
     var disable_input = function(input) { input.attr('disabled',true); };
     var enable_input = function(input) { input.removeAttr('disabled'); };

     var look_for = function(selectors) {
        var object;
        for (var i=0; i<selectors.length; i++) {
            var selector = selectors[i];
            if (selector instanceof Array) {
              selector = selector.join(',');
            }
            object = $(selector);
            if (object.size()>0) return object;
         }
         return object;
     }
     var selectors = $selectors_json;
     var input = look_for(selectors);
     $variables_declaration
     var check_value = function() {
        var new_value = $(this).val();
        $change_state_code
     };
     $("#{$master_widget_id}").keyup(check_value).click(check_value).change(check_value).each(check_value);
   })(jQuery);
 });
</script>
JAVASCRIPT;

   return $script;
  }

  /**
   * Returns data needed for building resulting JS.
   * @param mixed $values - "enable_on" or "disable_on" option
   * @param string $prefix Prefix to be used for variable naming
   * @param string $action A JS code to be executed if input value passes the test
   * @param string $else_action A JS code to be executed if input value doesn't pass the test
   * @return array An assoc array with variables definitions and JS script containing needed logic
   */
  protected function buildConditionalBlock($values, $prefix, $action, $else_action = null ) {
    
    if (!empty($values)) {
      if (is_array($values)) {
        $vars = "var {$prefix}_array = " . json_encode($values) . ";\n";
        $check = "$.inArray(new_value, {$prefix}_array) >= 0";
      } elseif (is_string($values) && substr($values,0,1)=='/' && substr($values,-1)=='/') {
        $vars = "var {$prefix}_regexp = $values; \n";
        $check = "{$prefix}_regexp.test(new_value)";
      } else {
        $vars = "var {$prefix}_value = " . json_encode($values) . ";\n";
        $check = "{$prefix}_value == new_value";
      }

      $script = "
        if ($check) {
          $action
        }";

      if ($else_action) {
        $script .=
        " else {
          $else_action
        }";
      }
      
      return array(
        'vars' => $vars,
        'script' => $script
      );
    }

    return false;
  }
}
?>
