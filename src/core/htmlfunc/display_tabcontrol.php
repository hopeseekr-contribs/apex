<?php
declare(strict_types = 1);

namespace apex\core\htmlfunc;

use apex\app;
use apex\libc\db;
use apex\libc\view;
use apex\libc\components;


class display_tabcontrol
{




/**
 * Process the HTML function. 
 *
 * Replaces the calling <a:function> tag with the contents generated by this 
 * HTML function. 
 *
 * @param string $html The contents of the TPL file, if exists, located at /views/htmlfunc/<package>/<alias>.tpl
 * @param array $data The attributes within the calling e:function> tag.
 *
 * @return string The resulting HTML code, which the <e:function> tag within the template is replaced with.
 */
public function process(string $html, array $data = array()):string
{ 

    // Perform checks
    if (!isset($data['tabcontrol'])) { 
        return "<b>ERROR:</b> No 'tabcontrol' attribute was defined in the 'function' tag.";
    }

    // Get package/  alias
    if (!list($package, $parent, $alias) = components::check('tabcontrol', $data['tabcontrol'])) { 
        return "<b>ERROR:<?b> The tab control '$data[tabcontrol]' either does not exist, or more than one with the same alias exist and you did not specify the package to use.";
    }

    // Load tab control
    $tabcontrol = components::load('tabcontrol', $alias, $package, '', $data);

    // Process tab control, if needed
    if (method_exists($tabcontrol, 'process')) { 
        components::call('process', 'tabcontrol', $alias, $package, '', ['data' => $data]);
    }

    // Get tab pages
    if ($package == 'core' && $alias == 'dashboard') { 
        $tab_pages = $tabcontrol->tabpages;
    } else { 
        $tab_pages = $this->get_tab_pages($tabcontrol->tabpages, $alias, $package);
    }
$tab_dir = SITE_PATH . '/src/' . $package . '/tabcontrol/' . $alias;

    // Check / confirm tab pages, if necessary.
    if (method_exists($tabcontrol, 'check_tab_pages')) { 
        $tab_pages = $tabcontrol->check_tab_pages($tab_pages, $data);
    }

    // Go through tab pages
    $tab_html = "<a:tab_control>\n";
    foreach ($tab_pages as $tab_page => $tab_name) { 

        // Check if tpl file exists
        $tpl_file = SITE_PATH . '/' . components::get_tpl_file('tabpage', $tab_page, $package, $alias);
        if (!file_exists($tpl_file)) { continue; }

        // Get HTML
        $page_html = file_get_contents($tpl_file);

        // Load PHP, if needed
        if ($page_client = components::load('tabcontrol', $tab_page, $package, $alias)) { 

            // Process HTML
            if (method_exists($page_client, 'process')) { 
                if ($page_client->process($data) === false) { continue; }
            }
        }

        /// Add to tab html
        $tab_name = tr($tab_name);
        $tab_html .= "\t<a:tab_page name=\"$tab_name\">\n\n$page_html\n\t</a:tab_page>\n\n";
    }

    // Return
    $tab_html .= "</a:tab_control>\n";
    return view::parse_html($tab_html);

}

/**
 * Get tab pages.  Goes through all additional tab pages added by other 
 * packages, and positions them correctly. 
 *
 * @param array $tab_pages The current tab pages from the tab control PHP class.
 * @param string $parent The alias of the tab control.
 * @param string $package The package of the tab control.
 */
protected function get_tab_pages(array $tab_pages, string $parent, string $package)
{ 

    // Return, if dashboard
    if ($parent == 'dashboard' && $package == 'core') { 
        return $tab_pages;
    }

    // Set variables
    $pages = array_keys($tab_pages);
    $tab_dir = SITE_PATH . '/src/' . $package . '/tabcontrol/' . $parent;

    // Go through extra pages
    $extra_pages = db::get_column("SELECT alias FROM internal_components WHERE type = 'tabpage' AND package = %s AND parent = %s ORDER BY order_num", $package, $parent);
    foreach ($extra_pages as $alias) { 
        if (isset($tab_pages[$alias])) { continue; }

        // Try to load
        $php_file = $tab_dir . '/' . $alias . '.php';
        if (!file_exists($php_file)) { 
            $pages[] = $alias;
            $page_names[$alias] = ucwords(str_replace("_", " ", $alias));
            continue;
        }

        // Load file
        $class_name = "\\apex\\" . $package . "\\tabcontrol\\" . $parent . "\\" . $alias;
        require_once($php_file);
        $page = new $class_name();

// Set variables
    $position = $page->position ?? 'bottom';
        $tab_pages[$alias] = $page->name ?? ucwords(str_replace("_", " ", $alias));

        // Check before / after position
        if (preg_match("/^(before|after) (.+)$/i", $position, $match)) { 

            if ($num = array_search($match[2], $pages)) { 
                if ($match[1] == 'after') { $num++; }
                array_splice($pages, $num, 0, $alias);
            } else { 
                $position = 'bottom';
            }

        }

        // Top / bottom position
        if ($position == 'top') { 
            array_unshift($pages, $alias);
        } else { 
            array_push($pages, $alias);
        }

    }

    // Get new pages
    $new_pages = array();
    foreach ($pages as $alias) { 
        $new_pages[$alias] = $tab_pages[$alias];
    }

    // Return
    return $new_pages;

}


}

