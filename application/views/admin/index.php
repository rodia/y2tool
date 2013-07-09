<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>YouTube Tool</title>
        <?php include "include.php"; ?>
    </head>
    <body>
        <?php
        if ( ! $this->session->userdata('logged_in')) {
            include "login_view.php";
        } else {
            include "header_view.php";
            include $page_name . ".php";
            include 'footer_view.php';
        }
        ?>
    </body>
</html>
