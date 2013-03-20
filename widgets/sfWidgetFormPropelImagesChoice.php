<?php

class sfWidgetFormPropelImagesChoice extends sfWidgetFormPropelChoice
{
  protected $pictures = array();
  protected $titles = array(); //I need the list of choices to render titles

  public function __construct($options = array(), $attributes = array())
  {
    $this->addOption("picture_path_method", "getWebPath");
    parent::__construct($options, $attributes);


  }

  public function getJavaScripts()
  {
    $parent = parent::getJavaScripts();
    if (!$this->getOption('multiple'))
    {
      return array_merge($parent, array('jquery/jquery.jgd.dropdown.js'));
    }
    else
    {
      //return $parent;
      return array_merge($parent, array('jquery/jquery.imagePicker.js'));
    }
  }

  public function getStylesheets()
  {
    $parent = parent::getStylesheets();

    if (!$this->getOption('multiple'))
    {
      return array_merge($parent, array('jquery.jgd.dropdown.css' => "screen"));
    }
    else
    {
      //return $parent;
      return array_merge($parent, array('jquery.imagePicker.css' => "screen"));
    }
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ($this->getOption('multiple'))
    {
      return $this->renderMultiple($name, $value, $attributes, $errors);
    }
    else
    {
      return $this->renderOne($name, $value, $attributes, $errors);
    }
  }

  protected function renderOne($name, $value = null, $attributes = array(), $errors = array())
  {
    //parent::render()
    //$choices = $this->getChoices();
    $text = parent::render($name, $value, $attributes, $errors);
    //$text = parent::render($name, $value, $attributes, $errors);
    $scr = "<script type=\"text/javascript\">
    $(\"#%s\").jgdDropdown({selected: '%s'});
    </script>";
    $scr = sprintf($scr, $this->generateId($name), $value);

    return $text . "\n" . $scr;
  }

  protected function renderMultiple($name, $value = null, $attributes = array(), $errors = array())
  {
    if (is_null($value))
    {
      $value = array();
    }
    $text = parent::render($name, $value, $attributes, $errors);
    $scr = "<script type=\"text/javascript\">
    $(\"#%s\").jqImagePicker({images: %s, titles: %s});
    </script>";
    $images = json_encode($this->pictures);
    foreach ($value as $k => $v)
    {
      $v = (string)$v;
      $value[$k] = $v;
    }

    $titles = json_encode($this->titles);

    $scr = sprintf($scr, $this->generateId($name), $images, $titles);
    //return $text;
    return $text . "\n" . $scr;
  }

  /**
   * Returns the choices associated to the model.
   *
   * @return array An array of choices
   */
  public function getChoices()
  {
    $choices = array();
    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->translate($this->getOption('add_empty'));
    }

    $criteria = PropelQuery::from($this->getOption('model'));
    if ($this->getOption('criteria'))
    {
      $criteria->mergeWith($this->getOption('criteria'));
    }
    foreach ($this->getOption('query_methods') as $methodName => $methodParams)
    {
      if (is_array($methodParams))
      {
        call_user_func_array(array($criteria, $methodName), $methodParams);
      }
      else
      {
        $criteria->$methodParams();
      }
    }

    if ($order = $this->getOption('order_by'))
    {
      $method = sprintf('add%sOrderByColumn', 0 === strpos(strtoupper($order[1]), 'ASC') ? 'Ascending' : 'Descending');
      $criteria->$method(
        call_user_func(
          array($class, 'translateFieldName'),
          $order[0],
          BasePeer::TYPE_PHPNAME,
          BasePeer::TYPE_COLNAME));
    }
    $objects = $criteria->find($this->getOption('connection'));

    $methodKey = $this->getOption('key_method');
    if (!method_exists($this->getOption('model'), $methodKey))
    {
      throw new RuntimeException(sprintf(
        'Class "%s" must implement a "%s" method to be rendered in a "%s" widget',
        $this->getOption('model'),
        $methodKey,
        __CLASS__));
    }

    $methodValue = $this->getOption('method');
    if (!method_exists($this->getOption('model'), $methodValue))
    {
      throw new RuntimeException(sprintf(
        'Class "%s" must implement a "%s" method to be rendered in a "%s" widget',
        $this->getOption('model'),
        $methodValue,
        __CLASS__));
    }

    $pictureMethodValue = $this->getOption('picture_path_method');
    if (!method_exists($this->getOption('model'), $pictureMethodValue))
    {
      throw new RuntimeException(sprintf(
        'Class "%s" must implement a "%s" method to be rendered in a "%s" widget',
        $this->getOption('model'),
        $pictureMethodValue,
        __CLASS__));
    }

    $picture = "";
    $titles = array();
    foreach ($objects as $object)
    {
      $this->pictures[$object->$methodKey()] = $object->$pictureMethodValue();
      if (!$this->getOption('multiple'))
      {
        $picture = $this->renderContentTag('img', "", array("src" => $this->pictures[$object->$methodKey()]));
      }

      $title = $object->$methodValue();
      $titles[$object->$methodKey()] = $title;
      $choices[$object->$methodKey()] = $picture . " " . $title;
    }

    $this->titles = $titles;

    return $choices;
  }
}

?>