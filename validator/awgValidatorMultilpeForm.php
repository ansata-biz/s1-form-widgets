<?php
/**
 * Description of awgValidatorMultilpeForm
 *
 * @author io
 */
class awgValidatorMultilpeForm extends sfValidatorBase
{
	protected function configure($options = array(), $messages = array())
	{
		$this->addOption('form');
	}

	protected function doClean($value)
	{
		$form = $this->getOption('form');

		if ($value && is_array($value))
		{
			$helper = new awgWidgetMultipleFormHelper($form);
			$isValid = $helper->bindForms($value);

			if (!$isValid)
			{
				throw new sfValidatorError($this, 'invalid');
			}
			
			return serialize($helper->getCleanValues());
		}
		
		return '';
	}
}
?>
