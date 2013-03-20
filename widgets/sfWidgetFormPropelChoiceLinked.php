<?php

/**
 * You will have to add a method to your form:
 *
 
  public function getFieldValue($field)
  {
    return isset($this->formFields[$field])?$this->formFields[$field]->getValue():null;
  }


 
 * add routing:
 *
 *
get_shipment_method:
  url:   /ajax/shipment-method
  param:  {  module:  customer, action:  shipment_method  }

 *
 * and create method in action
 *
 *
  public function executeShipment_method(sfWebRequest $request)
  {
    $agent = $request->getParameter('agent');
    $criteria = new Criteria();
    $criteria->add(SpShippingMethodPeer::SHIPPING_AGENT, $agent);
    $list = SpShippingMethodPeer::doSelect($criteria);
    $answer = "";
    $select = (count($list) == 1);
    if ($select) {
    $selected = " selected=\"true\"";
    } else {
      $selected = "";
    }
    if (count($list))
    {
      if (!$select) {
        $answer .= "<option value=\"\">Please select shipping method</option>\n";
      }
      foreach ($list as $method)
      {
          $answer .= "<option value=\"".$method->getPrimaryKey()."\"$selected>[".$method."] ".$method->getDescription()."</option>";
      }
    }
    else
    {
      $answer = "<option value=\"\">***do not use***</option>";
    }
    
    return $this->renderText($answer);
  }
 *
 */
class sfWidgetFormPropelChoiceLinked extends sfWidgetFormPropelChoice
{
  
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addRequiredOption('parent_widget'); //widget to get value from
    $this->addRequiredOption('form');  // use $this->form
    $this->addRequiredOption('child_field');  //use sfThisPeer::PARENT_ID
    $this->addRequiredOption('parent_key');    //variable name to be send via AJAX to the route
    $this->addRequiredOption('request_route'); //the route to send 'parent_key'=$(#parent_widget).val() and get <option>... HTML
  }
  
  public function getChoices()
  {
    $criteria = null === $this->getOption('criteria') ? new Criteria() : clone $this->getOption('criteria');
    $parent_value = $this->options["form"][$this->options['parent_widget']]->getValue();
    $criteria->add($this->options['child_field'], $parent_value);
    $this->setOption("criteria", $criteria);
    return parent::getChoices();
  }
  
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $text = parent::render($name, $value, $attributes, $errors);
    use_helper('jQuery');
    $script = <<<EOT
<script type="text/javascript">
$('##id#').change(function() {
	$('##this#').html('<option>Loading...</option>');
EOT;
    $script .= jq_remote_function(
      array(
        'update' => '#this#',
        'complete' => '$("##this#").removeAttr("disabled")',
        'url' => $this->options["request_route"],
        'with' => '{#param#: $("##id#").val()}'));
    
    $script .= '});
</script>
';
    
    $field = $this->getParent()->getFields();
    $field = $field[$this->options["parent_widget"]];
    $id = $field->generateId($this->getParent()->generateName($this->options["parent_widget"]));
    $__this = $this->generateId($name);
    
    $script = str_replace("#id#", $id, $script);
    $script = str_replace("#this#", $__this, $script);
    $script = str_replace("#param#", $this->options["parent_key"], $script);
    return $text . $script;
    
  }
}

?>

