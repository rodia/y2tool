<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php $this->load->helper("views_helper"); ?>
<?php get_link_relates(array(
	"video/bulk" => "Dashboard",
	$title
)); ?>
<center>
	<?php if (isset($success) && $success === TRUE) : ?>
	<div class="success">
		<p><?php echo $msg; ?></p>
	</div>
	<?php elseif (isset($success) && $success == "false") : ?>
	<div class="error">
		<p>The user(s) not was removed.</p>
	</div>
	<?php endif; ?>
    <table border="0" width="800" cellpadding="0" cellspacing="0" id="product-table">
        <tr>
            <th class="table-header-repeat line-left"><a href="">#</a></th>
            <th class="table-header-repeat line-left"><a href="">Name</a></th>
            <th class="table-header-repeat line-left"><a href="">Username</a></th>
            <th class="table-header-repeat line-left"><a href="">Email</a></th>
            <th class="table-header-repeat line-left"><a href="">User type</a></th>
            <?php if ($this->session->userdata('type')) { ?>
                <th class="table-header-repeat line-left" width="200"><a href="">Tasks</a></th>
            <?php } ?>

        </tr>
        <?php
        $c = 0;
        if (!empty($users)) {
            foreach ($users as $row) {
                $c++;
                ?>
                <tr <?php
        if ($c % 2)
            echo "class=\"alternate-row\"";
                ?>>

                    <td><?php echo $c; ?></td>
                    <td><?php echo $row->name; ?></td>
                    <td><?php echo $row->username; ?></td>
                    <td><?php echo $row->email; ?></td>
                    <td><?php echo ($row->type)? "Super Administrator": "Administrator"; ?></td>
                    <?php if ($this->session->userdata('type')) { ?>
                        <td >
                            <a href="<?php echo base_url(); ?>admin/edit/<?php echo $row->id; ?>"><b>Edit</b></a>&nbsp;
							<?php if ($row->username != $this->session->userdata('name')):?>
                            <a href="<?php echo base_url(); ?>admin/delete/<?php echo $row->id; ?>" onclick="return confirm('You delete this item?')"><b>Delete</b></a>
							<?php endif;?>
                        </td>
                    <?php } ?>
                </tr>
                <?php
            }
        }
        ?>
    </table>
</center>
