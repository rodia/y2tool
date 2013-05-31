<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<?php
$login = $this->session->userdata('login');
$username = $this->session->userdata('username');
$name = $this->session->userdata('name');
echo "$login $username $name";
?>

<body id="login-bg"> 
    <!-- Start: login-holder -->
    <div id="login-holder">
        <!--  start loginbox ................................................................................. -->
        <div id="loginbox">

            <!--  start login-inner -->
            <form method="post" action="<?php echo base_url(); ?>admin/login">
                <div id="login-inner">
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <th>Username</th>
                            <td><input type="text"  class="login-inp" autofocus name="username"/></td>
                        </tr>
                        <tr>
                            <th>Password</th>
                            <td><input type="password"  onfocus="this.value=''" class="login-inp" name="password"/></td>
                        </tr>
                        <tr>
                            <th></th>
                            <td valign="top"><input type="checkbox" class="checkbox-size" id="login-check" /><label for="login-check">Remember me</label></td>
                        </tr>
                        <tr>
                            <th></th>
                            <td><input type="submit" class="submit-login"  /></td>
                        </tr>
                    </table>
                </div>
            </form>
            <!--  end login-inner -->
            <div class="clear"></div>
            <!--<a href="" class="forgot-pwd">Forgot Password?</a>-->
        </div>
        <!--  end loginbox -->

    </div>
    <!-- End: login-holder -->
</body>
