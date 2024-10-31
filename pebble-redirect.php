<?php
/*
Plugin Name: Pebble 301 Redirects
Plugin URI: https://www.pebbleltd.co.uk/
Description: Simple to use 301 Redirect Manager. As simple as 1. Old,  2. New, 3. Redirect!
Version: 1.2.1
Author: Pebble Ltd
*/

if (!class_exists("PebbleRedirects")){
    class PebbleRedirects{

        function create_admin_menu(){
            add_options_page('Pebble 301 Redirects', '301 Redirects', 'edit_posts','pebble-301-redirects',  array($this,'redirect_menu'));
        }

        function redirect_menu(){
            if (!current_user_can('edit_posts')) {
                wp_die('You do not have sufficient permissions to access this page.');
            }

            if (isset($_POST["update_settings"])) {
                $redirect_array = array();
                $i = 0;
                foreach($_POST["redirects"] as $redirect){
                    if(sanitize_text_field($redirect['old']) != ""){
                        $redirect_array[$i]['old'] = str_replace(home_url(), '',sanitize_text_field($redirect['old']));
                        $redirect_array[$i]['new'] = sanitize_text_field($redirect['new']);
                        $i++;
                    }
                }
                update_option('pebble_301_redirects', $redirect_array);
                echo '<div id="message" class="updated">Redirects Saved</div>';
            }
            $current_redirects = get_option("pebble_301_redirects");
            ?>
            <div class="wrap">
                <a href="https://www.pebbleltd.co.uk" target="_blank"><img src="<?php echo plugins_url() ; ?>/pebble-301-redirects/img/pebbleicon.png" class="pebblelogo" alt="Visit Pebble" title="Visit Pebble"></a><h2>Pebble 301 Redirects</h2>
                <div style="clear:both;"></div>
                <p>Manage your 301 redirects by adding the old URL and the new URL in the fields below and clicking "Save Redirects".</p>
                <p>Don't forget to save your changes if you delete any rows.</p>
                <p><button class="button-secondary" id="add-row" data-location="top">&#43; Add another row</button></p>
                <form method="POST" action="">
                <table class="form-table redirect-table">
                    <thead>
                        <tr valign="top">
                            <th scope="row">Old Url<br/><i>Example: /old-url</i></th>
                            <th scope="row">New Url<br/><i>Example: <?php echo home_url('new-url');?></i></th>
                            <th scrope="row"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" name="redirects[0][old]" value=""/>
                            </td>
                            <td>
                                <input type="text" name="redirects[0][new]" value=""/>
                            </td>
                            <td class="del-redirect">Delete</td>
                        </tr>
                        <?php $j = 1;?>
                        <?php if(is_array($current_redirects)){ ?>
                            <?php foreach($current_redirects as $row) { ?>
                                <tr>
                                    <td>
                                        <input type="text" name="redirects[<?php echo $j;?>][old]" value="<?php echo $row['old'];?>"/>
                                    </td>
                                    <td>
                                        <input type="text" name="redirects[<?php echo $j;?>][new]" value="<?php echo $row['new'];?>"/>
                                    </td>
                                    <td class="del-redirect">Delete</td>
                                </tr>
                                <?php $j++;?>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
                <input type="hidden" name="update_settings" value="Y" />
                <p><input id="submit-redirects" type="submit" value="Save & Update Redirects" class="button-primary"/></p>
            </form>
            </div>
            <style>
                .form-table{ margin-top:20px;width:95%;}
                input[type="text"]{padding:5px 10px;width:90%;}
                .form-table th{width:50%;padding:0px 10px;line-height:1.7;}
                .form-table th{width:50%;padding:0px 10px;line-height:1.7;}
                .form-table th i{font-weight:normal;}
                .form-table td{padding:8px 10px;}
                .pebblelogo{width:50px;float:left;margin-right:15px;}
                h2{padding-top:15px;margin-bottom:35px;}
                #message{margin-top:20px;padding:10px;}
                #add-row{cursor:pointer;margin:20px 10px 10px;}
                .form-table .del-redirect{cursor:pointer;}
                #submit-redirects{margin:15px 10px;}
            </style>
            <script>
                jQuery(document).ready(function(){
                   jQuery('#add-row').on('click', function(){
                       var nextElem = jQuery('.redirect-table tr').length;
                       var newField = '<tr><td><input type="text" name="redirects['+nextElem+'][old]"/></td><td><input type="text" name="redirects['+nextElem+'][new]"/></td><td class="del-redirect">Delete</td></tr>';
                       if(jQuery(this).attr('data-location') == "bottom"){
                           jQuery('.redirect-table tbody').append(newField);
                       } else {
                           jQuery('.redirect-table tbody').prepend(newField);
                       }
                   });
                    jQuery('body').on('click', '.del-redirect', function(){
                        jQuery(this).parent('tr').remove();
                    });
                });
            </script>
            <?php
        }

        function check_redirect(){
            $full_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $request_url = str_replace(home_url(), '', $full_url);
            $redirect_urls = get_option('pebble_301_redirects');
            if(is_array($redirect_urls)) {
                foreach ($redirect_urls as $_url) {
                    $test1 = trim($_url['old'], '/');
                    $test2 = trim($request_url, '/');
                    if (trim($_url['old'], '/') == trim($request_url, '/') && strpos($request_url, 'wp-admin') === FALSE) {
                        $redirect_to = home_url(str_replace(home_url(), '', $_url['new']));
                        break;
                    }
                }
            }
            if(isset($redirect_to)){
                header ('HTTP/1.1 301 Moved Permanently');
                header ('Location: ' . $redirect_to);
                exit();
            }
        }
    }

}

$pebble_redirects = new PebbleRedirects();

add_action('admin_menu', array($pebble_redirects,'create_admin_menu'));
add_action('init', array($pebble_redirects,'check_redirect'), 1);