<?php
/**
 * Description of awgMultipleFormHelper
 *
 * @author io
 */
class awgWidgetMultipleFormHelper
{
	/**
	 * Form to be used as a sample
	 * @var sfForm
	 */
	protected $sampleForm;
	protected $forms;
	protected $cleanValues;
	protected $fieldName;

	public function __construct(sfForm $form, $field_name = '')
	{
		$this->sampleForm = clone $form;
		$this->fieldName = $field_name;
		unset($this->sampleForm[sfForm::getCSRFFieldName()]);

		if ($this->fieldName)
		{
			$this->sampleForm->getWidgetSchema()->setNameFormat($field_name.'[000][%s]');
		}
	}
	
	public function bindForms(array $values)
	{
		$isValid = true;
		$this->forms = array();
		$this->cleanValues = array();

		$num = 0;

		foreach ($values as $rowValue)
		{
			$num++;
			/* @var $rowForm sfForm */
			$rowForm = clone $this->sampleForm;

			if ($this->fieldName)
			{
				$key = "{$this->fieldName}[$num]";
				$rowForm->getWidgetSchema()->setNameFormat($key.'[%s]');
			}
			else
			{
				$key = $num;
			}

			$rowForm->setDefaults($rowValue);
			$rowForm->bind($rowValue);
			
			if (!$rowForm->isValid())
			{
				$isValid = false;
			}
			$this->forms[$key] = $rowForm;
			$this->cleanValues[$key] = $rowForm->getValues();
		}

		return $isValid;
	}

	public function getCleanValues()
	{
		return $this->cleanValues;
	}

	public function getForms()
	{
		return $this->forms;
	}

	public function getSampleForm()
	{
	 return $this->sampleForm;
	}
}
?>
