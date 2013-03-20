<?php

/**
 * awgWidgetFormTextareaLimited
 * Widget for textarea with maximum length js check.
 *
 * ATTENTION!
 * This widget depends on jQuery. Ensure jQuery is linked to page.
 *
 * @author io
 */
class awgWidgetFormTextareaLimited extends sfWidgetFormTextarea {

  protected $widgetId;

  public function __construct($options = array(), $attributes = array()) {
    $this->addOption('max_length', 1000);
    // TODO: ensure proper character escaping. Now ' will break the code.
    $this->addOption('message', '{0} characters left.');

    parent::__construct($options, $attributes);
  }

  public function render($name, $value = null, $attributes = array(), $errors = array()) {
    $return = parent::render($name, $value, $attributes, $errors);

    $message = $this->parent->getFormFormatter()->translate($this->getOption('message'));

    $script = <<<EOT
%s
<script type="text/javascript">
(function() {
  var checkLength = function(){
		var max = %d;
    var value = $(this).val();
		if(value.length > max){
			$(this).val(value.substr(0, max));
		}
    var remaining = max - $(this).val().length;
    var message = '%s'.replace('{0}', remaining );
		$(this).parent().find('.chars-remaining').html(message);
	};
	$('#%s').
    each(checkLength).
    keyup(checkLength).
    keydown(checkLength).
    change(checkLength);
})();
</script>
EOT;

    $messageDiv = "";
    if ($message) {
      $messageDiv = '<div class="chars-remaining">' . $message . '</div>';
    }

    $return .= sprintf( $script,
            $messageDiv,
            $this->getOption('max_length'),
            $message,
            $this->generateId($name)
    );

    return $return;
  }

}

?>
