<?php
declare(strict_types = 1);

namespace apex\core\form;

use apex\app;
use apex\libc\db;
use apex\libc\encrypt;


class repo 
{




    public $allow_post_values = 0;

/**
 * Defines the form fields included within the HTML form. 
 *
 * @param array $data An array of all attributes specified within the e:function tag that called the form.
 *
 * @return array Keys of the array are the names of the form fields.
 */
public function get_fields(array $data = array()):array
{ 

    // Set form fields
    $form_fields = array(
        'repo_is_ssl' => array('field' => 'boolean', 'label' => 'Is SSL?', 'value' => 1),
        'repo_host' => array('field' => 'textbox', 'label' => 'Hostname', 'placeholders' => 'repo.domai.com'),
    );

    // Check if local
$is_local = $data['is_local'] ?? 0;
    if (isset($data['record_id']) && $data['record_id'] > 0) { 
        $is_local = db::get_field("SELECT is_local FROM internal_repos WHERE id = %i", $data['record_id']);
    }

    // Check for local
    if ($is_local == 1) { 
        $form_fields['repo_alias'] = array('field' => 'textbox', 'label' => 'Repo Alias', 'datatype' => 'alphanum', 'placeholder' => 'public');
        $form_fields['repo_name'] = array('field' => 'textbox', 'label' => 'Repo Name');
        $form_fields['repo_description'] = array('field' => 'textarea', 'label' => 'Description');
        $form_fields['sep_login'] = array('field' => 'seperator', 'label' => 'Login Credentials');
    }

    // Add login fields
    $form_fields['repo_username'] = array('field' => 'textbox', 'label' => 'Username');
    $form_fields['repo_password'] = array('field' => 'textbox', 'label' => 'Password');

    // Add submit button
    if (isset($data['record_id'])) { 
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'update_repo', 'label' => 'Update Repository');
    } else { 
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'add_repo', 'label' => 'Add New Repository');
    }

    // Return
    return $form_fields;

}

/**
 * Get record from database. 
 *
 * Gathers the necessary row from the database for a specific record ID, and 
 * is used to populate the form fields.  Used when modifying a record. 
 *
 * @param string $record_id The value of the 'record_id' attribute from the e:function tag.
 *
 * @return array An array of key-value pairs containg the values of the form fields.
 */
public function get_record(string $record_id):array
{ 

    // Get record
    $row = db::get_idrow('internal_repos', $record_id) ?? array();

    // Decrypt, as needed
    $row['username'] = encrypt::decrypt_basic($row['username']);
    $row['password'] = encrypt::decrypt_basic($row['password']);

    // Format
    foreach ($row as $key => $value) { 
        $row['repo_' . $key] = $value;
    }

    // Return
    return $row;

}

}


