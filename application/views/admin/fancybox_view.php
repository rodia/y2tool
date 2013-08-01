<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php $this->load->helper("views_helper"); ?>
<h3><?php echo $entry["title"]; ?></h3>
<?php echo $entry["embedHtml"]; ?>
