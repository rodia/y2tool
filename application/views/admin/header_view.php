<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>


<!-- Start: page-top-outer -->
<div id="page-top-outer">

    <!-- Start: page-top -->
    <div id="page-top">
        <a href="https://www.buzzmyvideos.com" target="_parent"><img alt="Home" src="<?php echo base_url(); ?>css/admin/images/buzzlogo.png"></a>
        <div class="clear"></div>

    </div>
    <!-- End: page-top -->

</div>
<!-- End: page-top-outer -->

<div class="clear">&nbsp;</div>

<!--  start nav-outer-repeat................................................................................................. START -->
<div class="nav-outer-repeat">
    <!--  start nav-outer -->

    <nav>
        <ul>
            <!--<li><a href="<?php echo base_url(); ?>admin/users">Dashboard</a></li>-->
            <li><a href="<?php echo base_url(); ?>video/bulk">Dashboard</a></li>
            <li><a href="#">Individual actions</a>
                <ul>
                    <li><a href="<?php echo base_url(); ?>video/commenting">Commenting</a></li>
                    <li><a href="<?php echo base_url(); ?>video/like">Liking</a></li>
                </ul>
            </li>
<!--            <li><a href="#" onclick="return false;">Bulk actions</a>
            <li><a href="<?php echo base_url(); ?>video/bulk">Bulk actions</a>
                <ul>
                    <li><a href="<?php echo base_url(); ?>video/sharing">Sharing videos</a></li>
                    <li><a href="<?php echo base_url(); ?>video/liking">Liking videos</a></li>
                    <li><a href="<?php echo base_url(); ?>video/grabbing">Playlist(Grabbing)</a></li>
                    <li><a href="<?php echo base_url(); ?>video/favorites">Favoriting</a></li>
                    <li><a href="<?php echo base_url(); ?>video/likingnvideos">Liking videos (n videos)</a></li>
                    <li><a href="<?php echo base_url(); ?>video/playlistnvideos">Playlist (n videos)</a></li>
                </ul>
            </li>-->

            <li><a href="<?php echo base_url(); ?>admin/admins">List admins</a>
                <?php if ($this->session->userdata('type')) { ?>
                    <ul>
                        <li><a href="<?php echo base_url(); ?>admin/register">Add admin</a></li>
                    </ul>
                <?php } ?>
            </li>
            <li><a href="<?php echo base_url(); ?>admin/setting">Setup FB</a>
                <ul>
                    <li></li>
                </ul>
            </li>
            <li><a href="<?php echo base_url(); ?>admin/logout">Logout<em><?php echo " (Welcome " . $this->session->userdata('name').")" ?></em></a></li>
        </ul>
    </nav>
    <div class="clear"></div>
    <!--  start nav-outer -->
</div>
<!--  start nav-outer-repeat................................................... END -->

<div class="clear"></div>

<!-- start content-outer ........................................................................................................................START -->
<div id="content-outer">
    <!-- start content -->
    <div id="content">

        <!--  start page-heading -->
        <div id="page-heading">
            <h1>
<!--                <img src="<?php // echo base_url();           ?>images/video_icon.png" height="60" style="vertical-align:middle;"  />-->
                <?php echo $title; ?>
            </h1>
        </div>
        <!-- end page-heading -->

        <table border="0" width="100%" cellpadding="0" cellspacing="0" id="content-table">
            <tr>
                <th rowspan="3" class="sized"><img src="<?php echo base_url(); ?>css/admin/images/shared/side_shadowleft.jpg" width="20" height="300" alt="" /></th>
                <th class="topleft"></th>
                <td id="tbl-border-top">&nbsp;</td>
                <th class="topright"></th>
                <th rowspan="3" class="sized"><img src="<?php echo base_url(); ?>css/admin/images/shared/side_shadowright.jpg" width="20" height="300" alt="" /></th>
            </tr>
            <tr>
                <td id="tbl-border-left"></td>
                <td>
                    <!--  start content-table-inner ...................................................................... START -->
                    <div id="content-table-inner">

                        <!--  start table-content  -->
                        <div id="table-content">

                            <!--  start message-yellow -->
