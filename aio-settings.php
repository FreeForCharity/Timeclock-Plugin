<div class="wrap">
    <?php $logo = plugins_url('/images/logo.png', __FILE__); ?>
    <a href="https://codebangers.com" target="_blank"><img src="<?php echo $logo; ?>" style="width:15%;"></a>
    <h1>All in One Time Clock Lite</h1>
    <div class="about-text">
        <?php _e('Keeping track of time was never easier'); ?>
    </div>
    <h2 class="nav-tab-wrapper">
        <?php settings_errors(); ?>
        <?php $tab = $_GET['tab'];
        if ($tab == NULL) {
            $tab = "general_settings";
        }
        ?>
        <a href="?page=aio-tc-lite&tab=general_settings" class="nav-tab<?php if ($tab == "general_settings") {
            echo " nav-tab-active";
        } ?>">
            <i class="dashicons dashicons-admin-generic"></i>
            <?php _e('General Settings'); ?>
        </a>
        <a href="?page=aio-tc-lite&tab=help" class="nav-tab<?php if ($tab == "help") {
            echo " nav-tab-active";
        } ?>">
            <i class="dashicons dashicons-phone"></i>
            <?php _e('Help'); ?>
        </a>
        <a href="?page=aio-tc-lite&tab=get_pro" class="nav-tab<?php if ($tab == "get_pro") {
            echo " nav-tab-active";
        } ?>">
            <i class="dashicons dashicons-yes"></i>
            <?php _e('Get Pro'); ?>
        </a>
    </h2>
    <!--Handle the Tabs-->
    <?php if ($tab == "general_settings") {
        if (get_option('permalink_structure')) {
            //echo 'Permalinks enabled';
        } else {
            _e('<div id="setting-error-settings_updated" class="updated settings-error">WARNING!! Permalinks have to be set to anything other than default for the Timeclock to work properly.  We recommend you user the Post Name setting. <br /><a class="button" href="' . get_site_url() . '/wp-admin/options-permalink.php">Configure Permalinks</a></div>');
        }
        if ($_GET['job'] == "create_timeclock_page") {
            $tc_page = $FFC_timeclock->check_tc_shortcode();
            if ($tc_page == null) {
                $my_post = array(
                    'post_type' => 'page',
                    'post_title' => 'Time Clock',
                    'post_status' => 'publish',
                    'post_content' => '[show_aio_time_clock_lite]',
                    'comment_status' => 'closed',
                    'post_author' => $current_user->ID
                );
                // Insert the post into the database
                $new_post_id = wp_insert_post($my_post);
            }
            echo '<div id="setting-error-settings_updated" class="updated settings-error">';
            if ($new_post_id != null) {
                _e('TimeClock Page Created Sucessfully<br />');
                _e('<a href="' . get_permalink($new_post_id) . '" class="button small_button" target="_blank"><i class="dashicons dashicons-search"></i> View Page</a>');
            } else {
                _e('Something went wrong.  Timeclock was not created successfully.');
                if ($tc_page != null) {
                    _e('You already have a TimeClock page created.<br />');
                    _e('<a href="' . get_permalink($tc_page) . '" class="button small_button" target="_blank"><i class="dashicons dashicons-search"></i> View Page</a>');
                }
            }
            echo '</div>';
        }
        if ($_GET['job'] == "create_eprofile_page") {
            $eprofile_page = $FFC_timeclock->check_eprofile_shortcode();
            if ($eprofile_page == null) {
                $my_post = array(
                    'post_type' => 'page',
                    'post_title' => 'Employee Profile',
                    'post_status' => 'publish',
                    'post_content' => '[show_aio_employee_profile_lite]',
                    'comment_status' => 'closed',
                    'post_author' => 1
                );
                // Insert the post into the database
                $new_eprofile_id = wp_insert_post($my_post);
            }
            echo '<div id="setting-error-settings_updated" class="updated settings-error">';
            if ($new_eprofile_id != null) {
                _e('Employee Profile Page Created Sucessfully<br />');
                _e('<a href="' . get_permalink($new_eprofile_id) . '" class="button small_button" target="_blank"><i class="dashicons dashicons-search"></i> View Page</a>');
            } else {
                echo 'Something went wrong.  Employee Profile Page was not created successfully. ';
                if ($eprofile_page != null) {
                    _e('You already have a Employee Profile page created.<br />');
                    _e('<a href="' . get_permalink($eprofile_page) . '" class="button small_button" target="_blank"><i class="dashicons dashicons-search"></i> View Page</a>');
                }
            }
            echo '</div>';
        }
        ?><h3><?php _e('General Settings'); ?></h3>
        <form method="post" action="options.php">
            <?php settings_fields('nertworks-timeclock-settings-group'); ?>

            <?php do_settings_sections('nertworks-timeclock-settings-group');
            $options = get_option('nertworks-timeclock-settings-group');
            ?>
            <table class="widefat fixed" cellspacing="0">
                <tr class="alternate">
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Company Name'); ?>: </strong></th>
                    <td>
                        <input type="text" name="aio_company_name" value="<?php echo get_option('aio_company_name'); ?>"
                               placeholder="My Company Name"/>
                    </td>
                    <td>
                        <i><?php _e('The company name associated with this Account'); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Enable Employee Wage Management'); ?>: </strong></th>
                    <td>
                        <input type="radio" name="aio_wage_manage"
                               value="enabled" <?php if (get_option('aio_wage_manage') == "enabled") {
                            echo "checked";
                        } ?> /><?php _e('Enabled'); ?>
                        <input type="radio" name="aio_wage_manage"
                               value="disabled" <?php if (get_option('aio_wage_manage') == "disabled" || get_option('aio_wage_manage') == "") {
                            echo "checked";
                        } ?>/><?php _e('Disabled'); ?>
                    </td>
                    <td>
                        <i><?php _e('This allows you to track wages as well as time. Making your reports and graphs much more
                            valuable'); ?></i>
                    </td>
                </tr>
                <tr class="alternate">
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('TimeClock'); ?>: </strong></th>
                    <td>
                        <?php
                        $tc_page = $FFC_timeclock->check_tc_shortcode();
                        if ($tc_page != null) {
                            _e('<a href="' . get_permalink($tc_page) . '" class="button small_button" target="_blank"><i class="dashicons dashicons-search"></i> View Page</a>');
                            _e('<a href="/wp-admin/post.php?post=' . $tc_page . '&action=edit" class="button small_button" target="_blank"><i class="dashicons dashicons-edit"></i> Edit Page</a>');
                        } else {
                            _e('Timeclock page not found. Create one? <a href="?page=aio-tc-lite&tab=general_settings&job=create_timeclock_page" class="button small_button vmiddle"><span class="dashicons dashicons-plus"></span></a>');
                        }
                        ?>
                    </td>
                    <td>
                        <i><?php _e('Where employees can clock in and out of their shifts.'); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Employee Profile');?>: </strong></th>
                    <td>
                        <?php
                        $eprofile_page = check_eprofile_shortcode_lite();
                        if ($eprofile_page != null) {
                            echo '<a href="' . get_permalink($eprofile_page) . '" class="button small_button" target="_blank"><i class="dashicons dashicons-search"></i> View Page</a>';
                            echo '<a href="/wp-admin/post.php?post=' . $eprofile_page . '&action=edit" class="button small_button" target="_blank"><i class="dashicons dashicons-edit"></i> Edit Page</a>';
                        } else {
                            _e('Employee Profile page not found. Create one? <a href="?page=aio-tc-lite&tab=general_settings&job=create_eprofile_page" class="button small_button vmiddle"><span class="dashicons dashicons-plus"></span></a>');
                        }
                        ?>
                    </td>
                    <td>
                        <i>Profile where employees can access their shifts.  Shortcode: [show_aio_employee_profile]</i>
                    </td>
                </tr>
                <tr class="alternate">
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Redirect Employees to Time Clock Page'); ?>: </strong></th>
                    <td>
                        <input type="radio" name="aio_timeclock_redirect_employees"
                               value="enabled" <?php if (get_option('aio_timeclock_redirect_employees') == "enabled") {
                            echo "checked";
                        } ?> /><?php _e('Enabled'); ?>
                        <input type="radio" name="aio_timeclock_redirect_employees"
                               value="disabled" <?php if (get_option('aio_timeclock_redirect_employees') == "disabled" || get_option('aio_timeclock_redirect_employees') == "") {
                            echo "checked";
                        } ?>/><?php _e('Disabled'); ?>
                    </td>
                    <td>
                        <i>
                            <?php _e('If a user with the role \'Employee\' logs in. They will be redirected to the time clock
                            page. '); ?>
                        </i>
                    </td>
                </tr>            
                <tr>
                    <th scope="col" class="manage-column column-columnname"><strong>Show Employee Avatar: </strong></th>
                    <td>
                        Available in <a href="https://codebangers.com/product/all-in-one-time-clock/" target="_blank">Pro</a>
                    </td>
                    <td>
                        <i>
                            <?php _e('When enabled, avatar will display on time clock page'); ?>
                        </i>
                    </td>
                </tr>
                <tr class="alternate">
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Current Department On Reports'); ?>: </strong></th>
                    <td>
                        <input type="radio" name="aio_timeclock_show_current_dept"
                               value="enabled" <?php if (get_option('aio_timeclock_show_current_dept') == "enabled") {
                            echo "checked";
                        } ?> /><?php _e('Enabled'); ?>
                        <input type="radio" name="aio_timeclock_show_current_dept"
                               value="disabled" <?php if (get_option('aio_timeclock_show_current_dept') == "disabled" || get_option('aio_timeclock_show_current_dept') == "") {
                            echo "checked";
                        } ?>/><?php _e('Disabled'); ?>
                    </td>
                    <td>
                        <i>
                            <?php _e('Shows the current department of the employee on the reports instead of the department recorded on the shift.'); ?>
                        </i>
                    </td>
                </tr>
                <tr>
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('TimeZone'); ?>: </strong></th>
                    <td>
                        <select name="aio_timeclock_time_zone">
                            <option value="dynamic">Dynamic</option>
                            <?php
                            $tzlist = $FFC_timeclock->get_time_zone_list();
                            foreach ($tzlist as $tz => $label) {
                                $select = '';
                                if (get_option("aio_timeclock_time_zone") == $label) {
                                    $select = "selected";
                                }
                                echo "<option value='$label' $select>$label</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <i>
                            <?php _e('This allows you to track wages as well as time. Making your reports and graphs much more
                            valuable'); ?>
                        </i>
                    </td>
                </tr>
                <tr class="alternate">
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Enable Location'); ?>: </strong></th>
                    <td>
                        <?php _e('Available in <a href="https://codebangers.com/product/all-in-one-time-clock/" target="_blank">Pro</a>'); ?>
                    </td>
                    <td>
                        <i>
                            <?php _e('When enabled, employees can select the location they are clocking in at.'); ?>
                        </i>
                    </td>
                </tr>
                <tr>
                    <th scope="col" class="manage-column column-columnname"><strong><?php _e('Use Javascript Redirect'); ?>: </strong></th>
                    <td>
                        <input type="radio" name="aio_use_javascript_redirect"
                               value="enabled" <?php if (get_option('aio_use_javascript_redirect') == "enabled") {
                            echo "checked";
                        } ?> /><?php _e('Enabled'); ?>
                        <input type="radio" name="aio_use_javascript_redirect"
                               value="disabled" <?php if (get_option('aio_use_javascript_redirect') == "disabled" || get_option('aio_use_javascript_redirect') == "") {
                            echo "checked";
                        } ?>/><?php _e('Disabled'); ?>
                    </td>
                    <td>
                        <i>
                            <?php _e('Uses Javscript redirect instead of the builtin wordpress one. '); ?>
                        </i>
                    </td>
                </tr>
                <tr class="">
                    <th scope="col" class="manage-column column-columnname"><strong>Custom Roles: </strong></th>
                    <td>
                        <?php _e('Available in <a href="https://codebangers.com/product/all-in-one-time-clock/" target="_blank">Pro</a>'); ?>
                    </td>
                    <td>
                        <i>
                            <?php _e('Add your own custom roles to have access to the time clock.'); ?>
                        </i>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }
    if ($tab == "help") {
        _e('<h2>Need Help?</h2>');
        $sad_puppy = plugins_url('/images/sadpuppy.jpg', __FILE__);
        echo '<img src="' . $sad_puppy . '" width="200"><br />';
        _e('<p>Visit this link and we\'ll get you on your way. <a href="https://codebangers.com/support/">Get Support</a></p>');
    }
    if ($tab == "get_pro") {
        echo '<div class="proDiv">';
        echo '<h2>AIO Time Clock Pro</h2>';        
        echo '<hr>';
        _e('<h4>Some Pro Features Include:</h4>
        <ul>
        <li>Add your own custom roles for time clock access</li>        
        <li>Custom Weekly and Monthly shift reports</li>        
        <li>Export Reports to Spreadsheet/CSV</li>
        <li>Manage Wages</li>        
        <li>Monthly/Yearly Charts</li>
        <li>Unlimited Clock in Locations</li>
        <li>Google Analytics</li>    
        <li>Employee IP Address Tracking</li>
        <li>Extensions/Addons Supported</li>
        <li>And much much more</li>
        </ul>
        ');

        _e('<p><a class="button-primary" href="https://codebangers.com/product/all-in-one-time-clock/">Learn More about Pro</a></p>');
        echo '</div>';
    }
    ?>
</div>