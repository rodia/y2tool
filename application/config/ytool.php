<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
  settings for new users
 */
$config['um_accountactive'] = false;
$config['um_accountblocked'] = false;
$config['um_profileprivacy'] = 'public';
$config['um_appearonline'] = 1;

/*
  users who didnt show any activity more than this time will be considered as logged out
  value is in minutes. works only when the hook is enabled.900=15mins
 */
$config['login_timeout'] = 900;

//default country that will be selected on the registration form.
$config['default_country'] = 'United States';

//email settings
$config['email_from'] = 'admin@localhost';
$config['email_from_name'] = 'User manager for CI';
$config['email_activate_subject'] = 'Activate your account';
$config['email_reset_subject'] = 'Did you requested a password reset?';

/*
  this array defines the rules for the registration form
  you may add your own. but it should match the database and the model
 */
$config['register_rules'] = array(
    array(
        'field' => 'username',
        'label' => 'Username',
        'rules' => 'trim|required|is_unique[yt_admin_user.username]|min_length[5]|max_length[20]|xss_clean'
    ),
    array(
        'field' => 'password',
        'label' => 'Password',
        'rules' => 'trim|required|min_length[5]|max_length[20]'
    ),
    array(
        'field' => 'password2',
        'label' => 'Confirm Password',
        'rules' => 'trim|required|min_length[5]|max_length[20]|matches[password]'
    ),
    array(
        'field' => 'name',
        'label' => 'Full Name',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'email',
        'label' => 'Email',
        'rules' => 'trim|required|is_unique[yt_admin_user.email]|xss_clean'//valid_email
    )

);
$config['edit_rules'] = array(

    array(
        'field' => 'password',
        'label' => 'Password',
        'rules' => 'trim|min_length[5]|max_length[20]'
    ),
    array(
        'field' => 'password2',
        'label' => 'Confirm Password',
        'rules' => 'trim|min_length[5]|max_length[20]|matches[password]'
    ),
    array(
        'field' => 'name',
        'label' => 'Full Name',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'email',
        'label' => 'Email',
        'rules' => 'trim|required|xss_clean|callback_check_unique_pass'//valid_email
    )

);
$config['video_id_rule'] = array(
    array(
        'field' => 'video_ids',
        'label' => 'Video ID',
        'rules' => 'trim|required|min_length[11]|max_length[60]|xss_clean'
    )
);
$config['video_id_and_comment'] = array(
    array(
        'field' => 'video_id',
        'label' => 'Video ID',
        'rules' => 'trim|required|min_length[11]|max_length[60]|xss_clean'
    ),
    array(
        'field' => 'comment',
        'label' => 'Comment',
        'rules' => 'trim|required'
    )
);
$config['message_rule'] = array(
    array(
        'field' => 'message',
        'label' => 'Message',
        'rules' => 'trim|required|min_length[6]|max_length[150]'
    )
);
$config['video_edit_rules'] = array(
    array(
        'field' => 'video_title',
        'label' => 'Title',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'video_description',
        'label' => 'Description',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'video_tags',
        'label' => 'Tags',
        'rules' => 'trim|required'
    )
);
$config['new_video_rules'] = array(
    array(
        'field' => 'video_title',
        'label' => 'Title',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'video_description',
        'label' => 'Description',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'video_tags',
        'label' => 'Tags',
        'rules' => 'trim|required'
    )
);
$config['rule_for_title'] = array(
    array(
        'field' => 'play_title',
        'label' => 'Title',
        'rules' => 'trim|required'
    )
);

/*
 * regla para título
  this array defines the rules for the password reset form 2
 */
$config['pwd_reset_rules'] = array(
    array(
        'field' => 'password',
        'label' => 'Password',
        'rules' => 'trim|required|min_length[6]|max_length[10]'
    ),
    array(
        'field' => 'password2',
        'label' => 'Password2',
        'rules' => 'trim|required|min_length[6]|max_length[10]|matches[password]'
    ),
    array(
        'field' => 'email',
        'label' => 'Email',
        'rules' => 'trim|required|xss_clean'//valid_email
    ),
    array(
        'field' => 'token',
        'label' => 'token',
        'rules' => 'trim|required'
    )
);

/*
  this array defines the rules for the profile editor
 */
$config['profile_rules'] = array(
    array(
        'field' => 'password',
        'label' => 'Password',
        'rules' => 'trim|min_length[6]|max_length[10]'
    ),
    array(
        'field' => 'password2',
        'label' => 'Password2',
        'rules' => 'trim|min_length[6]|max_length[10]|matches[password]'
    ),
    array(
        'field' => 'email',
        'label' => 'Email',
        'rules' => 'trim|required|xss_clean|callback_check_unique_pass'//valid_email
    ),
    array(
        'field' => 'firstname',
        'label' => 'Firstname',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'secondname',
        'label' => 'Secondname',
        'rules' => 'trim'
    ),
    array(
        'field' => 'lastname',
        'label' => 'Lastname',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'country',
        'label' => 'Country',
        'rules' => 'trim|required'
    ),
    array(
        'field' => 'interests',
        'label' => 'Interests',
        'rules' => 'trim'
    ),
    array(
        'field' => 'address',
        'label' => 'Address',
        'rules' => 'trim'
    ),
    array(
        'field' => 'dateofbirth',
        'label' => 'Date of birth',
        'rules' => 'trim|required'
    )
);

// country list that appears on the form
$config['country_list'] = array(
    "GB" => "United Kingdom",
    "US" => "United States",
    "AL" => "Albania",
    "DZ" => "Algeria",
    "AD" => "Andorra",
    "AO" => "Angola",
    "AG" => "Antigua And Barbuda",
    "AR" => "Argentina",
    "AM" => "Armenia",
    "AW" => "Aruba",
    "AU" => "Australia",
    "AT" => "Austria",
    "AZ" => "Azerbaijan",
    "BS" => "Bahamas",
    "BH" => "Bahrain",
    "BD" => "Bangladesh",
    "BB" => "Barbados",
    "BY" => "Belarus",
    "BE" => "Belgium",
    "BZ" => "Belize",
    "BJ" => "Benin",
    "BM" => "Bermuda",
    "BT" => "Bhutan",
    "BO" => "Bolivia",
    "BA" => "Bosnia And Herzegowina",
    "BW" => "Botswana",
    "BV" => "Bouvet Island",
    "BR" => "Brazil",
    "IO" => "British Indian Ocean Territory",
    "BN" => "Brunei Darussalam",
    "BG" => "Bulgaria",
    "BF" => "Burkina Faso",
    "BI" => "Burundi",
    "KH" => "Cambodia",
    "CM" => "Cameroon",
    "CA" => "Canada",
    "CV" => "Cape Verde",
    "KY" => "Cayman Islands",
    "CF" => "Central African Republic",
    "TD" => "Chad",
    "CL" => "Chile",
    "CN" => "China",
    "CX" => "Christmas Island",
    "CC" => "Cocos (Keeling) Islands",
    "CO" => "Colombia",
    "KM" => "Comoros",
    "CG" => "Congo",
    "CD" => "Congo",
    "CK" => "Cook Islands",
    "CR" => "Costa Rica",
    "CI" => "Cote D'Ivoire",
    "HR" => "Croatia",
    "CU" => "Cuba",
    "CY" => "Cyprus",
    "CZ" => "Czech Republic",
    "DK" => "Denmark",
    "DJ" => "Djibouti",
    "DM" => "Dominica",
    "DO" => "Dominican Republic",
    "TP" => "East Timor",
    "EC" => "Ecuador",
    "EG" => "Egypt",
    "SV" => "El Salvador",
    "GQ" => "Equatorial Guinea",
    "ER" => "Eritrea",
    "EE" => "Estonia",
    "ET" => "Ethiopia",
    "FK" => "Falkland Islands (Malvinas)",
    "FO" => "Faroe Islands",
    "FJ" => "Fiji",
    "FI" => "Finland",
    "FR" => "France",
    "FX" => "France, Metropolitan",
    "GF" => "French Guiana",
    "PF" => "French Polynesia",
    "TF" => "French Southern Territories",
    "GA" => "Gabon",
    "GM" => "Gambia",
    "GE" => "Georgia",
    "DE" => "Germany",
    "GH" => "Ghana",
    "GI" => "Gibraltar",
    "GR" => "Greece",
    "GL" => "Greenland",
    "GD" => "Grenada",
    "GP" => "Guadeloupe",
    "GU" => "Guam",
    "GT" => "Guatemala",
    "GN" => "Guinea",
    "GW" => "Guinea-Bissau",
    "GY" => "Guyana",
    "HT" => "Haiti",
    "HM" => "Heard And Mc Donald Islands",
);
// country list that appears on the Filter
$config['countrys_list'] = array(
    "United Kingdom" => "United Kingdom",
    "United States" => "United States",
    "Albania" => "Albania",
    "Algeria" => "Algeria",
    "Andorra" => "Andorra",
    "Angola" => "Angola",
    "Antigua And Barbuda" => "Antigua And Barbuda",
    "Argentina" => "Argentina",
    "Armenia" => "Armenia",
    "Aruba" => "Aruba",
    "Australia" => "Australia",
    "Austria" => "Austria",
    "Azerbaijan" => "Azerbaijan",
    "Bahamas" => "Bahamas",
    "Bahrain" => "Bahrain",
    "Bangladesh" => "Bangladesh",
    "Barbados" => "Barbados",
    "Belarus" => "Belarus",
    "Belgium" => "Belgium",
    "Belize" => "Belize",
    "Benin" => "Benin",
    "Bermuda" => "Bermuda",
    "Bhutan" => "Bhutan",
    "Bolivia" => "Bolivia",
    "Bosnia And Herzegowina" => "Bosnia And Herzegowina",
    "Botswana" => "Botswana",
    "Bouvet Island" => "Bouvet Island",
    "Brazil" => "Brazil",
    "British Indian Ocean Territory" => "British Indian Ocean Territory",
    "Brunei Darussalam" => "Brunei Darussalam",
    "Bulgaria" => "Bulgaria",
    "Burkina Faso" => "Burkina Faso",
    "Burundi" => "Burundi",
    "Cambodia" => "Cambodia",
    "Cameroon" => "Cameroon",
    "Canada" => "Canada",
    "Cape Verde" => "Cape Verde",
    "Cayman Islands" => "Cayman Islands",
    "Central African Republic" => "Central African Republic",
    "Chad" => "Chad",
    "Chile" => "Chile",
    "China" => "China",
    "Christmas Island" => "Christmas Island",
    "Cocos (Keeling) Islands" => "Cocos (Keeling) Islands",
    "Colombia" => "Colombia",
    "Comoros" => "Comoros",
    "Congo" => "Congo",
    "Cook Islands" => "Cook Islands",
    "Costa Rica" => "Costa Rica",
    "Cote D'Ivoire" => "Cote D'Ivoire",
    "Croatia" => "Croatia",
    "Cuba" => "Cuba",
    "Cyprus" => "Cyprus",
    "Czech Republic" => "Czech Republic",
    "Denmark" => "Denmark",
    "Djibouti" => "Djibouti",
    "Dominica" => "Dominica",
    "Dominican Republic" => "Dominican Republic",
    "East Timor" => "East Timor",
    "Ecuador" => "Ecuador",
    "Egypt" => "Egypt",
    "El Salvador" => "El Salvador",
    "Equatorial Guinea" => "Equatorial Guinea",
    "Eritrea" => "Eritrea",
    "Estonia" => "Estonia",
    "Ethiopia" => "Ethiopia",
    "Falkland Islands (Malvinas)" => "Falkland Islands (Malvinas)",
    "Faroe Islands" => "Faroe Islands",
    "Fiji" => "Fiji",
    "Finland" => "Finland",
    "France" => "France",
    "France, Metropolitan" => "France, Metropolitan",
    "French Guiana" => "French Guiana",
    "French Polynesia" => "French Polynesia",
    "French Southern Territories" => "French Southern Territories",
    "Gabon" => "Gabon",
    "Gambia" => "Gambia",
    "Georgia" => "Georgia",
    "Germany" => "Germany",
    "Ghana" => "Ghana",
    "Gibraltar" => "Gibraltar",
    "Greece" => "Greece",
    "Greenland" => "Greenland",
    "Greenland" => "Greenland",
    "Guadeloupe" => "Guadeloupe",
    "Guam" => "Guam",
    "Guatemala" => "Guatemala",
    "Guinea" => "Guinea",
    "Guinea-Bissau" => "Guinea-Bissau",
    "Guyana" => "Guyana",
    "Haiti" => "Haiti",
    "Heard And Mc Donald Islands" => "Heard And Mc Donald Islands",
);



