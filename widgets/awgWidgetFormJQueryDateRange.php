<?php

/**
 * Description of awgWidgetFormJQueryDateRange
 *
 * @author io
 */
class awgWidgetFormJQueryDateRange extends sfWidgetFormDateRange {

  protected function configure($options = array(), $attributes = array()) {
    /*
     * Actually standard sfWidgetFormDateRange can be used with JQueryDateRanges as well.
     * This overrides parent configure() method just to make from_date and to_date options
     * optional (not required).
     */
    $this->addOption('date_widget_options', array());
    $this->addOption('date_widget_arguments', array());

    if (isset($options['date_widget_options'])) {
      $date_widget_options = $options['date_widget_options'];
    } else {
      $date_widget_options = array();
    }

    if (isset($options['date_widget_arguments'])) {
      $date_widget_arguments = $options['date_widget_arguments'];
    } else {
      $date_widget_arguments = array();
    }

    $this->addOption('from_date', new sfWidgetFormJQueryDate($date_widget_options, $date_widget_arguments) );
    $this->addOption('to_date', new sfWidgetFormJQueryDate($date_widget_options, $date_widget_arguments) );

    $this->addOption('template', '<div class="from-date"><span class="caption">from</span> %from_date%</div><div class="to-date"><span class="caption">to</span> %to_date%</div>');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array()) {
    $render = parent::render($name, $value, $attributes, $errors);
    $render = $this->renderTag('div', $attributes) . $render . '</div>';
    return $render;
  }

  
}
?>
