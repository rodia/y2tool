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
    <?php echo validation_errors('<p class="error">');  ?>

    <?php echo form_open("admin/account/update"); ?>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left" colspan="2"><a href="#">Edit admin information</a></th>
        </tr>
        <tr>
            <td width="300" align="right">
                <h2>Username : </h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="text" name="username" value="<?php echo $row->username; ?>"  />
            </td>
        </tr>
        <tr class="alternate-row">
            <td align="right">
                <h2>Password:</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="password" name="password" value=""  />
            </td>
        </tr>
        <tr class="alternate-row">
            <td align="right">
                <h2>Confirm Password:</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="password2" name="password2" value=""  />
            </td>
        </tr>
        <tr>
            <td width="300" align="right">
                <h2>Name : </h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="text" name="name" value="<?php echo $row->name; ?>"  />
            </td>
        </tr>
        <tr class="alternate-row">
            <td align="right">
                <h2>Email:</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="text" name="email" value="<?php echo $row->email; ?>"  />
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <input id="button" type="submit" value="Update"  class="form-submit"/>
            </td>
        </tr>
    </table>
    <?php echo form_close(); ?>
</center>
