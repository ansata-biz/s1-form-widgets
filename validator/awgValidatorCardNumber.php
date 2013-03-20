<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of awgValidatorCardNumber
 *
 * @author io
 */
class awgValidatorCardNumber extends sfValidatorBase {

  private static $CARDS = array(
    'ae' => array(
      'length' => '15',
      'prefixes' => '34,37',
      'checkdigit' => true
    ),
    'discover' => array(
      'length' => '16',
      'prefixes' => '6011',
      'checkdigit' => true
    ),
    'master' => array(
      'length' => '16',
      'prefixes' => '51,52,53,54,55',
      'checkdigit' => true
    ),
    'visa' => array(
      'length' => '13,16',
      'prefixes' => '4',
      'checkdigit' => true
    ),
  );

  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('form');
    $this->addRequiredOption('card_type_var_name');
    
    $this->addOption('allowed_card_types', null);
    $this->setOption('required', false);

    $this->addMessage('cc_error_type', 'Unknown card type');
    $this->addMessage('cc_error_missing', 'No card number provided');
    $this->addMessage('cc_error_format', 'Credit card number has invalid format');
    $this->addMessage('cc_error_number', 'Credit card number is invalid');
    $this->addMessage('cc_error_length', 'Credit card number is wrong length');
  }

  protected function doClean($value)
  {
    $is_required = $this->getOption('required');

    $form = $this->getOption('form');
    $values = $form->getTaintedValues();

    $card_type_var_name = $this->getOption('card_type_var_name');

    if (!isset($values[$card_type_var_name])) {
      throw new sfValidatorError($this, 'cc_error_type');
    }

    $allowed_card_types = $this->getOption('allowed_card_types');
    if (!$allowed_card_types) {
      $allowed_card_types = array_values(self::$CARDS);
    }

    $card_type = $values[$card_type_var_name];

    if (!in_array($card_type, array_keys($allowed_card_types) )) {
      throw new sfValidatorError($this, 'cc_error_type');
    }

    // Remove any non-digits   from the credit card number
    $card_number = preg_replace('/[^0-9]/', '', $value);

    $error = $this->verify($card_type, $card_number);

    if ($error != 1) {
      throw new sfValidatorError($this, $error);
    }

    return $card_number;
  }

  protected function verify( $cardType, $cardNo ) {

    // If card type not found, report an error
    if ($cardType == -1) {
      $error = 'cc_error_type';
      return $error;
    }

    // Ensure that the user has provided a credit card number
    if (strlen($cardNo) == 0) {
      $error = 'cc_error_missing';
      return $error;
    }

    // Check that the number is numeric and of the right sort of length.
    if (!preg_match('/^[0-9]{13,19}$/', $cardNo)) {
      $error = 'cc_error_format';
      return $error;
    }

    $cardType = strtolower($cardType);

    // Now check the modulus 10 check digit - if required
    if (self::$CARDS[$cardType]['checkdigit'] && !$this->isCheckSumValid($cardNo)) {
      $error = 'cc_error_number';
      return $error;
    }

    // If it isn’t a valid prefix there’s no point at looking at the length
    if (!$this->isPrefixValid($cardType, $cardNo)) {
      $error = 'cc_error_number';
      return $error;
    }

    // See if all is OK by seeing if the length was valid.
    if (!$this->isLengthValid( $cardType, $cardNo )) {
      $error = 'cc_error_length';
      return $error;
    }

    // The credit card is in the required format.
    return true;
  }

  protected function isPrefixValid( $cardType, $cardNo ) {
    // The following are the card-specific checks we undertake.
    // Load an array with the valid prefixes for this card
    $prefix = preg_split('/[^\d]+/', self::$CARDS[$cardType]['prefixes']);

    // Now see if any of them match what we have in the card number
    $prefixValid = false;
    for ($i = 0; $i < sizeof($prefix); $i++) {
      $exp = '/^' . $prefix[$i] . '/';
      if (preg_match($exp, $cardNo)) {
        $prefixValid = true;
        break;
      }
    }

    return $prefixValid;
  }

  protected function isLengthValid( $cardType, $cardNo ) {
    // See if the length is valid for this card
    $lengthValid = false;
    $lengths = preg_split('/[^\d]+/', self::$CARDS[$cardType]['length']);
    for ($j = 0; $j < sizeof($lengths); $j++) {
      if (strlen($cardNo) == $lengths[$j]) {
        $lengthValid = true;
        break;
      }
    }

    return $lengthValid;
  }

  protected function isCheckSumValid( $cardNo ) {
    $checksum = 0;   // running checksum total
    $mychar = '';    // next char to process
    $j = 1;          // takes value of 1 or 2
    // Process each digit one by one starting at the right
    for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {
      // Extract the next digit and multiply by 1 or 2 on alternative digits.
      $calc = $cardNo{$i} * $j;

      // If the result is in two digits add 1 to the checksum total
      if ($calc > 9) {
        $checksum = $checksum + 1;
        $calc = $calc - 10;
      }

      // Add the units element to the checksum total
      $checksum = $checksum + $calc;

      // Switch the value of j
      if ($j == 1) {
        $j = 2;
      } else {
        $j = 1;
      };
    }

    // All done - if checksum is divisible by 10, it is a valid modulus 10.
    // If not, report an error.
    if ($checksum % 10 != 0) {
      return false;
    }

    return true;
  }  
}
?>
