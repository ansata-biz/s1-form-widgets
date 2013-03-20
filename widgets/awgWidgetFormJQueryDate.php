<?php

/**
 * Description of awgWidgetFormJQueryDate
 *
 * @author io
 */
class awgWidgetFormJQueryDate extends sfWidgetFormJQueryDate
{
  public function __construct($options = array(), $attributes = array())
  {
    parent::__construct($options, $attributes);

    $dateWidget = $this->getOption('date_widget');
    $this->passWidgetOptions($dateWidget);
  }


  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('with_clearing', false);

    $this->addOption('date_widget_attributes', array());
    $this->addOption('date_widget_options', array());

    parent::configure($options, $attributes);
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $render = parent::render($name, $value, $attributes, $errors);

    $id = $this->generateId($name);
    $prefix = str_replace('-', '_', $id);

    $inject = <<<JAVASCRIPT
$('#{$id}_jquery_control').each(function(){ $(this).attr('name', $(this).attr('id')) });
wfd_{$prefix}_read_linked();
JAVASCRIPT;

    $render = str_replace('</script>', ";\n{$inject}\n</script>", $render);

    if ($this->getOption('with_clearing', false))
    {
      $render .= <<<JAVASCRIPT
	  <span class="date-clearing" id="{$id}_date_clearing" data-widget-id-prefix="{$id}">Clear</span>
		<script type="text/javascript">
		jQuery(function(){
			$('#{$id}_date_clearing').click(function(){
					var idPrefix = $(this).attr('data-widget-id-prefix');
					var yearWidgetId = '#'+idPrefix+'_year';
					var monthWidgetId = '#'+idPrefix+'_month';
					var dayWidgetId = '#'+idPrefix+'_day';
					var hourWidgetId = '#'+idPrefix+'_hour';
					var minuteWidgetId = '#'+idPrefix+'_minute';
					$(yearWidgetId).add(monthWidgetId).add(dayWidgetId).add(hourWidgetId).add(minuteWidgetId).val('');
			});
		});
		</script>
JAVASCRIPT;
    }

    return $render;
  }

  public function passWidgetOptions($dateWidget)
  {
    /* @var $dateWidget sfWidgetFormJQueryDate */
    foreach ($this->getOption('date_widget_options') as $option => $value)
    {
      $dateWidget->setOption($option, $value);
    }
    foreach ($this->getOption('date_widget_attributes') as $attr => $value)
    {
      $dateWidget->setAttribute($attr, $value);
    }
  }

  public function setOptions($options)
  {
    parent::setOptions($options);
    $this->passWidgetOptions($this->getOption('date_widget'));
  }

}