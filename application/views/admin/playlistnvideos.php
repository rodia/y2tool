<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$category_id = 2;

$user_options = array();
foreach ($users as $row) {
    $user_options[$row->id] = $row->lastname . " " . $row->firstname;
}
?>
<script type="text/javascript">
       

    $(window).load(function(){
        $(function() {
            var inputsDiv = $('#like_inputs');
            var i = $('#like_inputs p').size() + 1;
        
            $('#addInput').live('click', function() {
                $('<p><label for="video_id"><span>Video ID ' + i + ' * </span><input type="text" class="inp-form" size="50" name="video_ids[]" id="video_ids_' + i + '" value="" placeholder="Enter youtube video id" /></label> <span><a href="#" id="remInput" style="color:#0093F0">Remove</a></span></p>').appendTo(inputsDiv);
                i++;
                return false;
            });
        
            $('#remInput').live('click', function() { 
                if( i > 2 ) {
                    $(this).parents('p').remove();
                    i--;
                }
                return false;
            });
        });
    });

    jQuery.extend(jQuery.validator.prototype, {

        /*
         * Modifica necessaria per controllare tutti i campi nel caso in cui ci sia un array di <input>
         * I campi interessati devono avere l'attributo id valorizzato
         */
        checkForm: function() {
            this.prepareForm();
            for ( var i = 0, elements = (this.currentElements = this.elements()); elements[i]; i++ ) {
                if (this.findByName( elements[i].name ).length != undefined && this.findByName( elements[i].name ).length > 1) {
                    for (var cnt = 0; cnt < this.findByName( elements[i].name ).length; cnt++) {
                        this.check( this.findByName( elements[i].name )[cnt] );
                    }
                } else {
                    this.check( elements[i] );
                }
            }
            return this.valid();
        },
		
        showErrors: function(errors) {
            if(errors) {
                // add items to error list and map
                $.extend( this.errorMap, errors );
                this.errorList = [];
                for ( var name in errors ) {
                    this.errorList.push({
                        message: errors[name],
                        /* NOTE THAT I'M COMMENTING THIS OUT
                                        element: this.findByName(name)[0]
                         */
                        element: this.findById(name)[0]
                    });
                }
                // remove items from success list
                this.successList = $.grep( this.successList, function(element) {
                    return !(element.name in errors);
                });
            }
            this.settings.showErrors
                ? this.settings.showErrors.call( this, this.errorMap, this.errorList )
            : this.defaultShowErrors();
        },

        findById: function( id ) {
            // select by name and filter by form for performance over form.find("[id=...]")
            var form = this.currentForm;
            return $(document.getElementById(id)).map(function(index, element) {
                return element.form == form && element.id == id && element || null;
            });
        }

    });
                
</script>
<center>  
    <?php
    $attributes = array('id' => 'likesForm', 'name' => 'likesForm', 'novalidate' => 'novalidate');
    echo form_open("video/processplaylist", $attributes)
    ?>
    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
        <tr>
            <th colspan="2" class="table-header-repeat line-left"><a href="#">Playlist n videos</a></th>
        </tr> 
        <?php if (!empty($msg)) { ?>
            <tr class="alternate-row" colspan="2">
                <td colspan="2"><h2><em><?php echo $msg; ?></em></h2></td>
            </tr>
        <?php } ?>     

        <tr class="alternate-row">

            <td  colspan="2">                

                <div id="like_inputs">
                    <p>
                        <label for="video_id"><span>Video ID 1 * </span><input type="text" class="inp-form" size="50" name="video_ids[]" id="video_id_1" value="" placeholder="Enter youtube video id" /></label>
                        <br/><span class="url-demo">e.g. http://www.youtube.com/watch?v=</span><b><i>HcTrHo4dk4Q</i></b>
                    </p>
                </div>
                <h2><a href="#" id="addInput" style="color:#0093F0">Add another input box</a></h2>
            </td>
        </tr>




    </table>
    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
        <tbody>
            <tr>
                <th colspan="2" class="table-header-repeat line-left"><a href="#">Playlist Data</a></th>
            </tr>   
            <tr>
                <td align="right">
                    <h2>Title:</h2>
                </td>
                <td>                        
                    <input class="inp-form" size="60px" type="text" name="play_title" id="play_title" value="<?php echo $play_title; ?>"/>
                    <span><?php echo form_error('play_title'); ?></span>
                </td>    
            </tr>
            <tr class="alternate-row">
                <td align="right">
                    <h2>Description:</h2>
                </td>
                <td>
                    <textarea class="form-textarea" name="play_description"><?php echo $play_description; ?></textarea>
                    <span><?php echo form_error('play_description'); ?></span>
                </td>
            </tr>                  
        </tbody>
    </table>
    <table width="800" cellspacing="0" cellpadding="0" border="0" id="product-table">
        <tbody>
            <tr>
                <th colspan="2" class="table-header-repeat line-left"><a href="#">User channel</a></th>
            </tr>   
          
            <tr>
                <td align="right">
                    <h2>User:</h2>
                </td>
                <td>                        
                    <?php
                    $selected = ($this->input->post('user_id')) ? $this->input->post('user_id') : '';
                    echo form_dropdown('user_id', $user_options, $selected, 'class="select_style"');
                    ?>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">                                           
                    <input class="form-submit" type="submit" value="Process" name="submit"/>
                </td>
            </tr>
        </tbody>
    </table>
    <?php echo form_close(); ?>
    <script type="text/javascript">
        <!--
        $('#likesForm').validate({
            rules: {			
                "video_ids[]": {required: true}
            },
            submitHandler: function(form) {form.submit();}
        });
        -->
    </script>
</center>
