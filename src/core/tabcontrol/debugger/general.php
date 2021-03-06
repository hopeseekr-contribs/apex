<?php
declare(strict_types = 1);

namespace apex\core\tabcontrol\debugger;

use apex\app;
use apex\libc\db;
use apex\libc\debug;

/**
 * Handles the specifics of the one tab page, and is 
 * executed every time the tab page is displayed.
 */
class general 
{

    // Page variables
    public $position = 'top';
    public $name = 'General';

/**
 * Process the tab page.
 *
 * Executes every time the tab control is displayed, and used 
 * to execute any necessary actions from forms filled out 
 * on the tab page, and mianly to treieve variables and assign 
 * them to the template.
 *
 *     @param array $data The attributes containd within the <e:function> tag that called the tab control
 */
public function process(array $data = array()) 
{


}

}

