<?php
class Symbic_View_Helper_FormDateRangePicker extends Symbic_View_Helper_FormInput
{
    public function formDateRangePicker($name, $value = null, $attribs = null)
    {
		return $this->renderInput($name, $value, $attribs);
    }
}

/*
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="moment.js"></script>
<script type="text/javascript" src="daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="bootstrap.css" />
<link rel="stylesheet" type="text/css" href="daterangepicker-bs3.css" />

<script type="text/javascript">
$(document).ready(function() {
  $('input[name="daterange"]').daterangepicker();
});
</script>
 */