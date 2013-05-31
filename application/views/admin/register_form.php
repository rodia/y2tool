<div id="fb-root"></div>
<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>

<center>    

    <?php echo form_open("admin/register"); ?>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left" colspan="2"><a href="#">Edit admin information</a></th>
        </tr>
        <tr class="alternate-row">
            <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
        </tr>
        <tr>
            <td width="300" align="right">
                <h2>Username *</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="text" name="username" value="<?php echo $username; ?>"  />
                <span><?php echo form_error('username'); ?></span>
            </td>
        </tr>
        <tr class="alternate-row">
            <td align="right">
                <h2>Password *</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="password" name="password" value=""  />
                <span><?php echo form_error('password'); ?></span>
            </td>
        </tr>
        <tr>
            <td align="right">
                <h2>Confirm Password *</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="password" name="password2" value=""  />
                <span><?php echo form_error('password2'); ?></span>
            </td>
        </tr >
        <tr class="alternate-row">
            <td width="300" align="right">
                <h2>Full Name *</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="text" name="name" value="<?php echo $name; ?>"  />
                <span><?php echo form_error('name'); ?></span>
            </td>
        </tr>
        <tr >
            <td align="right">
                <h2>Email *</h2>
            </td>
            <td>
                <input class="inp-form inp-admin" size="6" type="text" name="email" value="<?php echo $email; ?>"  />
                <span><?php echo form_error('email'); ?></span>
            </td>
        </tr>
        <tr >
            <td align="right">
                <h2>User type *</h2>
            </td>
            <td>
                <select  class="select_style" name="type" id="type">                                                       
                    <option value="0">Administrator</option>
                    <option value="1">Super Administrator</option>
                </select>
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <input id="button" type="submit" value="Submit" name="submit" class="form-submit"/>
            </td>
        </tr>
    </table>
    <?php echo form_close(); ?>
</center>
