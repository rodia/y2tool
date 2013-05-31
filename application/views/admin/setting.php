<div id="fb-root"></div>
<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<?php foreach ($account_info as $row) {
    
} ?>

<?php
require_once "facebook-php-sdk/facebook.php";


$facebook = new Facebook(array(
            'appId' => $row->facebook_apikey,
            'secret' => $row->facebook_secret
        ));


if (!empty($_GET['l']) && $_GET['l'] == 'logout') {
    $facebook->destroySession();
}

$user = $facebook->getUser();
if ($user) {
    $login = false;
    $logoutUrl = $facebook->getLogoutUrl(array("next" => "http://james.gcgi.net/admin/account?l=logout"));
} else {
    $login = true;
    $loginUrl = $facebook->getLoginUrl(array('scope' => 'publish_stream,publish_actions,manage_pages,create_event,user_events'));
}

if (isset($_GET['code']) and $_GET['code'] != '') {
    if ($user) {
        // update access token
        echo "Please now click on UPDATE for save facebook changes";
        $pageContent = file_get_contents("http://graph.facebook.com/$user");
        $parsedJson = json_decode($pageContent);
        $row->facebook_id = $parsedJson->name;
        $row->facebook_accesstoken = $facebook->getAccessToken();
    }
}
?>


<center>
    <form method="post" action="<?php echo base_url(); ?>admin/setting/update" enctype="multipart/form-data">
        <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
            <tr>
                <th class="table-header-repeat line-left" colspan="2"><a href="#">Edit setting</a></th>
            </tr>

            <tr>
                <td width="300" align="right">
                    <h2>API Key: * : </h2>
                </td>
                <td>
                    <input class="inp-form inp-admin" size="6" type="text" name="facebook_apikey" value="<?php echo $row->facebook_apikey; ?>" />
                </td>
            </tr>
            <tr class="alternate-row">
                <td align="right">
                    <h2>Secret: *</h2>
                </td>
                <td>
                    <input class="inp-form inp-admin" size="6" type="text" name="facebook_secret" value="<?php echo $row->facebook_secret; ?>" />
                </td>
            </tr>
            <tr class="alternate-row">
                <td align="right">
                    <h2>Account:</h2>
                </td>
                <td>
                    <input class="inp-form inp-admin" size="6" type="text" name="facebook_id" value="<?php echo $row->facebook_id; ?>" readonly="readonly"  />
                </td>
            </tr>
            <tr class="alternate-row">
                <td align="right">
                    <h2></h2>
                </td>
                <td>

                    <input type="hidden" name="facebook_accesstoken" value="<?php echo $row->facebook_accesstoken; ?>" />
                    <?php
                    if ($login) {
                        echo '<a href="' . $loginUrl . '">Login</a>';
                    } else {
                        echo '<a href="' . $logoutUrl . '">Logout</a>';
                    }
                    ?>
                </td>
            </tr>

            <tr>
                <td colspan="2" align="center">
                    <input id="button" type="submit" value="Update"  class="form-submit"/>
                </td>
            </tr>
        </table>
    </form>
</center>
