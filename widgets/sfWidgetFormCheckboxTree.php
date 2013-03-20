<?php
/**
 * Description of sfWidgetFormCheckboxTree
 *
 * @author io
 */
class sfWidgetFormCheckboxTree extends sfWidgetFormSelectCheckbox {

  public function configure($options = array(), $attributes = array()) {
    parent::configure($options, $attributes);
    $this->addOption('class', 'checkbox_tree');
  }

  public function  getStylesheets() {
    return array_merge(parent::getStylesheets(), array(
          'jquery.checkboxtree.css' => 'screen'
        ));
  }

  /**
   * Returns the translated choices configured for this widget
   *
   * @return array  An array of strings
   */
  public function getChoices()
  {
    $choices = $this->getOption('choices');

    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    return $choices;
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ('[]' != substr($name, -2))
    {
      $name .= '[]';
    }

    if (null === $value)
    {
      $value = array();
    }

    $choices = $this->getChoices();

    return $this->formatChoices($name, $value, $choices, $attributes, 1);
  }

  protected function formatChoices($name, $value, $choices, $attributes, $level = 1)
  {
    $inputs = array();
    foreach ($choices as $optionData)
    {
      $key = $optionData['id'];
      $option = $optionData['text'];
      
      $baseAttributes = array(
        'name'  => $name,
        'type'  => 'checkbox',
        'value' => self::escapeOnce($key),
        'id'    => $id = $this->generateId($name, self::escapeOnce($key)),
      );

      if ((is_array($value) && in_array(strval($key), $value)) || strval($key) == strval($value))
      {
        $baseAttributes['checked'] = 'checked';
      }

      $inputs[$id] = array(
        'input' => $this->renderTag('input', array_merge($baseAttributes, $attributes)),
        'label' => $this->renderContentTag('label', self::escapeOnce($option), array('for' => $id)),
        'innercontent' => '',
      );

      if (array_key_exists('children', $optionData)) {
        $inputs[$id]['innercontent'] = $this->formatChoices($name, $value, $optionData['children'], $attributes, $level+1);
      }
    }

    return call_user_func($this->getOption('formatter'), $this, $inputs, $level );
  }

  public function formatter($widget, $inputs, $level = 1)
  {
    $rows = array();
    foreach ($inputs as $input)
    {
      $rows[] = $this->renderContentTag('li', $input['input'].$this->getOption('label_separator').$input['label'].$input['innercontent']);
    }
    if ($level==1) {
      $ulAttrs = array('class' => $this->getOption('class'));
    } else {
      $ulAttrs = array();
    }
    return !$rows ? '' : $this->renderContentTag('ul', implode($this->getOption('separator'), $rows), $ulAttrs );
  }
}
?>
