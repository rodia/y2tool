<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php if ($type == "error"): ?>
<div class="forgot-pwd error">
	<p>Sorry! The user Not enable your channel for this tools. please try with another user.</p>
	<p><a href="<?php echo base_url(); ?>admin/users">Go back!</a></p>
</div>
<?php endif; ?>