<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of awgWidgetMultipleForm
 *
 * @author io
 */
class awgWidgetMultipleForm extends sfWidgetForm
{

	public function configure($options = array(), $attributes = array())
	{
		parent::configure($options, $attributes);

		$this->addRequiredOption('form');
		$this->addOption('decorator');
		$this->addOption('min_rows', 1);
		$this->addOption('template', '<div class="multiple-form-widget" id="%id%">%forms%</div>');
		$this->addOption('form_template', '<div class="multiple-form-widget-form">%form%</div>');
		$this->addOption('confirmation_message', 'Are you sure you want to remove this row?');
	}

	public function render($name, $value = null, $attributes = array(), $errors = array())
	{
		if ($value)
		{
			if (is_array($value))
			{
				$actualValue = array_values($value);
			}
			else
			{
				$actualValue = unserialize($value);
			}
		}
		else
		{
			$actualValue = array();
		}

		$minRows = intval($this->getOption('min_rows'));

		for ( $i = count($actualValue); $i<$minRows; $i++)
		{
			$actualValue[] = array();
		}

		$helper = new awgWidgetMultipleFormHelper($this->getOption('form'), $name);
		$helper->bindForms($actualValue);
		
		$forms = $helper->getForms();
		return $this->renderWithTemplate($name, $this->getOption('template'), $helper->getSampleForm(), $forms);
	}

	protected function renderWithTemplate($field_name, $template, sfForm $sample_form, array $forms)
	{
		$formsHtml = array();
		foreach ($forms as $name => $form)
		{
			$formsHtml[$name] = $this->renderForm($name, $form);
		}

		if ($template)
		{
			if (strpos($template,'%') === FALSE)
			{
				sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
				$vars = array('forms' => $formsHtml);
				return get_partial($template, $vars) . $this->renderJavascript($sample_form);
			}
		}
		
		$id = $this->generateId($field_name).'_wrapper';
		$render = implode(' ', $formsHtml);
		return str_replace(array('%id%','%forms%'), array($id, $render), $template) . $this->renderJavascript($field_name, $sample_form);
	}

	protected function renderJavascript($field_name, sfForm $sample_form)
	{
		$sampleFormHtml = $this->renderForm($field_name.'[000]', $sample_form);
		
		$id = $this->generateId($field_name);
		$options = json_encode(array(
				'id' => $id.'_wrapper',
				'sample' => $sampleFormHtml,
				'form_wrapper_class' => 'multiple-form-widget-form',
				'add_button_template' => '<div class="multiple-form-widget-controls"><span class="add-row">Add Row</span></div>',
				'remove_button_template' => '<div class="multiple-form-widget-controls"><span class="remove-row">Remove Row</span></div>',
				'confirmation_message' => $this->getOption('confirmation_message')
		));

		return <<<JAVASCRIPT
<script type="text/javascript">
jQuery(function(){
	var $ = jQuery;
	var options = $options;

	var container = $('#'+options.id);
	var num = $('.' + options.form_wrapper_class ,container).each(addRemoveRowControls).length;

	var addRowControlsDiv = $(options.add_button_template);
	container.append(addRowControlsDiv);
	
	$('.multiple-form-widget-controls .add-row', container).click(function() {
		var template = options.sample.replace(/000/g, ++num);
		var formRow = $(template).each(addRemoveRowControls);
		addRowControlsDiv.before(formRow);
	});

	function addRemoveRowControls() {
		var formRowControls = $(options.remove_button_template);
		$('.remove-row', formRowControls).click(function() {
			if (confirm(options.confirmation_message)) {
				$(this).parents('.' + options.form_wrapper_class).remove();
			}
		});
		$(this).append(formRowControls);
	}
});
</script>
JAVASCRIPT;
	}

	protected function renderForm($name, sfForm $form)
	{
		$formTemplate = $this->getOption('form_template');
		
		if (strpos($formTemplate, '%') === FALSE)
		{
			sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
			$vars = array('form' => $form, 'name'=> $name);
			
			return get_partial($formTemplate, $vars);
		}
		else
		{
			$widgetSchema = $form->getWidgetSchema();
			$decorator = $this->getOption('decorator');
			$decorator = null === $decorator ? $widgetSchema->getFormFormatter()->getDecoratorFormat() : $decorator;
			$formSchemaDecorator = new sfWidgetFormSchemaDecorator($widgetSchema, $decorator);

			if ($form->isValid())
			{
				$values = $form->getValues();
			}
			else
			{
				$values = $form->getTaintedValues();
			}
			$render = $formSchemaDecorator->render($name, $values, array(), $form->getErrorSchema()->getErrors());
			
			return str_replace('%form%', $render, $formTemplate);
		}
	}
}
?>
