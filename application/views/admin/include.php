<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url(); ?>css/admin/css/screen.css" type="text/css" media="screen" title="default" />
<!--<link rel="stylesheet" href="<?php echo base_url(); ?>css/admin/css/autocomplete.css" type="text/css" media="screen" title="default" />-->
<link rel="stylesheet" href="<?php echo base_url(); ?>css/admin/js/jquery-ui-1.9.2/css/ui-darkness/jquery-ui-1.9.2.custom.min.css" type="text/css" media="screen" title="default" />
<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/jquery-1.8.1.min.js'></script>
<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/functions.js'></script>
<!--<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/jquery/ui.core.js'></script>
<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/autocomplete.jquery.js'></script>
<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/jquery/jquery.datePicker.js'></script>-->
<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/jquery.validate.min.js'></script>
<script type='text/javascript' src='<?php echo base_url(); ?>css/admin/js/jquery-ui-1.9.2/js/jquery-ui-1.9.2.custom.min.js'></script>
<?php if (isset($headers)) echo $headers; ?>
<script type="text/javascript">
    $(window).load(function(){
        $('#checkAll').click(function() {
            if(this.checked) {
                $('input:checkbox').attr('checked', true);
            }
            else {
                $('input:checkbox').removeAttr('checked');
            }
        });
        $('input:checkbox:not(#checkAll)').click(function() {
            if(!this.checked) {
                $('#checkAll').removeAttr('checked');
            }
            else {
                var numChecked = $('input:checkbox:checked:not(#checkAll)').length;
                var numTotal = $('input:checkbox:not(#checkAll)').length;
                if(numTotal == numChecked) {
                    $('#checkAll').attr('checked', true);
                }
            }
        });
    });
</script>
