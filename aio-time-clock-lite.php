<?php
/**
 * Plugin Name: Volunteer Timeclock
 * Plugin URI:  https://freeforcharity.org
 * Description: Keep track of volunteer hours, sync hours from your WordPress site to your Salesforce instance. Based on CodeBanger's All-in-One Timeclock Plugin
 * Author:      Codebangers, Desiree Bruce, Shawn Sargent, Gavin Glowacki
 * Author URI:  https://codebangers.com, https://freeforcharity.org
 * Version:     1.0.8
 */

class FFC_Volunteer_Timeclock {
    public function __construct() {
        add_shortcode('show_aio_time_clock_lite', array($this, 'show_time_clock'));
        add_shortcode('show_aio_employee_profile_lite', array($this, 'show_employee_profile'));
        add_action('wp_ajax_aio_time_clock_js', array($this, 'time_clock_js'));
        add_action('wp_ajax_nopriv_aio_time_clock_js', array($this, 'time_clock_js'));
        add_action('wp_ajax_aio_time_clock_admin_js', array($this, 'time_clock_admin_js'));
        add_action('wp_ajax_nopriv_aio_time_clock_admin_js', array($this, 'time_clock_admin_js');
        add_action('admin_init', array($this, 'custom_post_shift'));
        add_action("admin_init", array($this, 'admin_init');
        add_action('add_meta_boxes', array($this, 'shift_info_box_meta'));
        add_action('admin_menu', array($this, 'remove_my_post_metaboxes'));
        add_action('admin_init', array($this, 'registertimeclocksettings'));
        add_action('init', array($this, 'script_enqueuer'));
        add_action('admin_init', array($this, 'admin_script_enqueuer'));
        add_filter('user_contactmethods', array($this, 'modify_employee_wage'));
        add_filter('manage_edit-department_columns', array($this, 'manage_department_user_column'));
        add_action('show_user_profile', array($this, 'edit_user_department_section'));
        add_action('edit_user_profile', array($this, 'edit_user_department_section'));
        add_action('manage_department_custom_column', array($this, 'manage_department_column'), 10, 3);
        add_action('personal_options_update', array($this, 'save_user_department_terms'));
        add_action('edit_user_profile_update', array($this, 'save_user_department_terms'));
        add_action('admin_menu', array($this, 'add_department_admin_page'));
        add_action('init', array($this, 'user_taxonomy'));
        add_action('admin_menu',array($this, 'remove_my_post_metaboxes'));

        define_roles();

        if ( is_admin() ) {
            admin_actions();
        }

        if (get_option('aio_timeclock_redirect_employees') == "enabled") {
            add_filter('login_redirect', array($this, 'member_login_redirect'), 10, 3);
        }

        add_action('plugins_loaded', array($this, 'init')); 

        add_filter( 'post_row_actions', array($this, 'remove_row_actions'), 10, 1 );
    }

    private function admin_actions(){
        add_action('save_post', array($this, 'save_shift_meta'));
        add_action('admin_menu', array($this, 'plugin_admin_menu'));
    }

    public function init() {
        load_plugin_textdomain( 'aio-timeclock-lite', false, dirname(plugin_basename(__FILE__)).'/languages/' );
    }

    public function remove_my_post_metaboxes() {
        remove_meta_box( 'authordiv','shift','normal' );
        remove_meta_box( 'commentstatusdiv', 'shift', 'normal' );
        remove_meta_box( 'commentsdiv', 'shift', 'normal' );
    }

    public function remove_row_actions( $actions )
    {
        if( get_post_type() === 'shift' )
            unset( $actions['view'] );
        return $actions;
    }

    public function show_time_clock($atts)
    {
        $tc_page = check_tc_shortcode();
        $nonce = wp_create_nonce("clock_in_nonce");
        $link = admin_url('admin-ajax.php?action=clock_in_nonce&post_id=' . get_the_ID() . '&nonce=' . $nonce);
        require_once "templates/time-clock-style1.php";
    }

    public function show_employee_profile($atts)
    {
        $ep_page = check_eprofile_shortcode();
        $nonce = wp_create_nonce("clock_in_nonce");
        $link = admin_url('admin-ajax.php?action=clock_in_nonce&post_id=' . get_the_ID() . '&nonce=' . $nonce);
        require_once "aio-employee-profile.php";
    }

    public function plugin_admin_menu()
    {
        $page_hook_suffix = add_menu_page('Time Clock Lite', 'Time Clock Lite', 'edit_posts', 'aio-tc-lite', array($this, 'timeclock_settings_page'), 'dashicons-clock');
        add_submenu_page('aio-tc-lite', 'General Settings', 'General Settings', 'edit_posts', 'aio-tc-lite', array($this, 'timeclock_settings_page'));
        add_submenu_page('aio-tc-lite', 'Real Time Monitoring', 'Real Time Monitoring', 'edit_posts', 'aio-monitoring-sub', array($this, 'timeclock_monitoring_page'));
        add_submenu_page('aio-tc-lite', 'Employees', 'Employees', 'edit_posts', 'aio-employees-sub', array($this, 'timeclock_employee_page'));
        add_submenu_page('aio-tc-lite', 'Departments', 'Departments', 'edit_posts', 'aio-department-sub', array($this, 'timeclock_department_page'));    
        add_submenu_page('aio-tc-lite', 'Shifts', 'Shifts', 'edit_posts', 'aio-shifts-sub', array($this, 'timeclock_shifts_page'));
        add_submenu_page('aio-tc-lite', 'Reports', 'Reports', 'edit_posts', 'aio-reports-sub', array($this, 'timeclock_reports_page'));
    }

    public function script_enqueuer()
    {
        wp_register_script("aio_time_clock_js", plugins_url('/js/time-clock-lite.js', __FILE__), array('jquery'));
        wp_localize_script('aio_time_clock_js', 'timeClockAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_enqueue_script('jquery');
        wp_register_style('datetimepicker-style', plugins_url('js/datetimepicker/jquery.datetimepicker.css', __FILE__));
        wp_register_style('aio-tc-site-style', plugins_url('css/aio-site.css', __FILE__));
        wp_enqueue_style('datetimepicker-style');
        wp_enqueue_script('nert-aio-timepicker', plugins_url('js/datetimepicker/jquery.datetimepicker.js', __FILE__));
        wp_enqueue_script('aio_time_clock_js');
        wp_enqueue_style('aio-tc-site-style');
    }

    public function admin_script_enqueuer()
    {
        wp_register_script("aio_time_clock_admin_js", plugins_url('/js/time-clock-lite-admin.js', __FILE__), array('jquery'));
        wp_localize_script('aio_time_clock_admin_js', 'timeClockAdminAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
        wp_register_style('aio-tc-admin-style', plugins_url('css/aio-admin.css', __FILE__));
        wp_enqueue_script('jquery');
        wp_enqueue_script('aio_time_clock_admin_js');
        wp_enqueue_style('aio-tc-admin-style');
    }

    public function time_clock_js()
    {
        $clock_action = sanitize_text_field($_POST["clock_action"]);
        $employee_clock_in_time = null;
        $employee_clock_out_time = null;
        $is_clocked_in = false;
        $open_shift_id = sanitize_key(intval($_POST["open_shift_id"]));
        $new_shift_created = false;
        global $current_user;
        get_currentuserinfo();
        $current_user = wp_get_current_user();
        $employee = $current_user->ID;
        $message = null;
        $time_total = 0;

        $open_shift_id = sanitize_key(intval($open_shift_id));

        if ($clock_action == "check_shifts") {

            $args = array(
                'post_type' => 'shift',
                'orderby' => 'ID',
                'author' => $employee,
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $custom = get_post_custom($query->post->ID);
                    $employee_clock_in_time = $custom["employee_clock_in_time"][0];
                    $employee_clock_out_time = $custom["employee_clock_out_time"][0];
                    if ($employee_clock_in_time != null && $employee_clock_out_time == null) {
                        $open_shift_id = $query->post->ID;
                        $is_clocked_in = true;
                        break;
                    }
                }
            }

            echo json_encode(
                array(
                    "response" => "success",
                    "message" => $message,
                    "employee" => $employee,
                    "clock_action" => $clock_action,
                    "is_clocked_in" => $is_clocked_in,
                    "open_shift_id" => $open_shift_id,
                    "employee_clock_in_time" => $employee_clock_in_time,
                    "employee_clock_out_time" => $employee_clock_out_time,
                )
            );
        } elseif ($clock_action == "clock_in") {
            $device_time = $_POST["device_time"];
            $timezone_option = get_option('aio_timeclock_time_zone');
            if ($timezone_option != null){
                if ($device_time != null && $timezone_option == 'dynamic'){
                    $device_time = strtotime($device_time);
                    $date = date('Y/m/d h:i:s A',$device_time);
                    $time_type = "device";
                }
                else{
                    date_default_timezone_set($timezone_option);
                    $date = date('Y/m/d h:i:s A');
                    $time_type = "timezone option";
                }
                    
            }
            else{
                $timezone = 'America/New_York';
                date_default_timezone_set($timezone);
                $date = date('Y/m/d h:i:s A');
                $time_type = "defualt";
            }  
                
            $aio_new_shift = array(
                'post_type' => 'shift',
                'post_title' => 'Employee Shift',
                'post_status' => 'publish',
                'post_author' => $employee,
            );
            $new_post_id = wp_insert_post($aio_new_shift);
            $department = "";
            $terms = get_the_terms($current_user->ID, 'department');
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $department = $term->name;
                }
            }
            $SalesforceID = get_usermeta($employee, 'SalesforceID', true);

            add_post_meta($new_post_id, 'SalesforceID', $SalesforceID, true);
            add_post_meta($new_post_id, 'employee_clock_in_time', $date, true);
            $day = date("Y-m-d");
            add_post_meta($new_post_id, 'start_date', $day, true);
            add_post_meta($new_post_id, 'number_of_volunteers', 1, true);
            add_post_meta($new_post_id, 'volunteer_job', 'a0T3B000001lr0NUAQ', true);
            add_post_meta($new_post_id, 'hours_status', 'Completed', true);
            add_post_meta($open_shift_id, 'total_time', 0, true);
            if ($department != null) {
                add_post_meta($new_post_id, 'department', $department, true);
            }

            add_post_meta($new_post_id, 'ip_address_in', $_SERVER['REMOTE_ADDR'], true);
            $open_shift_id = $new_post_id;
            $is_clocked_in = true;

            wp_update_post(array('ID' => $new_post_id, 'post_modified_gmt' => $date));

            echo json_encode(
                array(
                    "response" => "success",
                    "message" => $message,
                    "employee" => $employee,
                    "open_shift_id" => $open_shift_id,
                    "clock_action" => $clock_action,
                    "is_clocked_in" => $is_clocked_in,
                    "employee_clock_in_time" => $date,
                    "employee_clock_out_time" => null,
                )
            );
        } elseif ($clock_action == "clock_out") {
            $device_time = $_POST["device_time"];
            $timezone_option = get_option('aio_timeclock_time_zone');
            if ($timezone_option != null){
                if ($device_time != null && $timezone_option == 'dynamic'){
                    $device_time = strtotime($device_time);
                    $date = date('Y/m/d h:i:s A',$device_time);
                    $time_type = "device";
                }
                else{
                    date_default_timezone_set($timezone_option);
                    $date = date('Y/m/d h:i:s A');
                    $time_type = "timezone option";
                }
                    
            }
            else{
                $timezone = 'America/New_York';
                date_default_timezone_set($timezone);
                $date = date('Y/m/d h:i:s A');
                $time_type = "defualt";
            }  
            $is_clocked_in = false;  
            add_post_meta($open_shift_id, 'employee_clock_out_time', $date, true);
            add_post_meta($open_shift_id, 'ip_address_out', $_SERVER['REMOTE_ADDR'], true);   
            $employee_clock_in_time = get_post_meta($open_shift_id, 'employee_clock_in_time', true);                   
            $time_total = date_difference($date, $employee_clock_in_time);
            $day = date("Y-m-d");
            add_post_meta($open_shift_id, 'end_date', $day, true);
            $hours_total = date_to_hours($time_total);
            update_post_meta($open_shift_id, 'total_time', $hours_total);

            wp_update_post(array('ID' => $open_shift_id, 'post_modified_gmt' => $date));

            echo json_encode(
                array(
                    "response" => "success",
                    "message" => $message,
                    "employee" => $employee,
                    "clock_action" => $clock_action,
                    "employee_clock_in_time" => $employee_clock_in_time,
                    "employee_clock_out_time" => $date,
                    "time_total" => $time_total,
                    "is_clocked_in" => $is_clocked_in
                )
            );
        } else {
            echo json_encode(
                array(
                    "response" => "failed",
                    "message" => "action does not exist",
                    "employee" => $employee,
                    "clock_action" => $clock_action,
                )
            );
        }

        wp_reset_postdata();
        die();
    }

    public function time_clock_admin_js()
    {
        $report_type = sanitize_text_field($_POST["report_type"]);
        $employee = sanitize_key(intval($_POST["employee"]));
        $date_range_start = date("Y/m/d h:i:s A", strtotime($_POST["aio_pp_start_date"]));
        $date_range_end = date("Y/m/d h:i:s A", strtotime($_POST["aio_pp_end_date"]));
        $errors = "";

        if ($errors == null) {
            echo json_encode(
                array(
                    "response" => "success",
                    "employee" => $employee,
                    "date_range_start" => $date_range_start,
                    "date_range_end" => $date_range_end,
                    "shifts" =>
                    get_shift_total_from_range(
                        $employee,
                        $date_range_start,
                        $date_range_end
                    ),
                )
            );
        } else {
            echo json_encode(
                array(
                    "response" => "failed",
                    "employee" => $employee,
                    "date_range_start" => $date_range_start,
                    "date_range_end" => $date_range_end,
                )
            );
        }

        die();
    }

    public function custom_post_shift()
    {
        $labels = array(
            'name' => _x('Shifts', 'post type general name'),
            'singular_name' => _x('Shift', 'post type singular name'),
            'add_new' => _x('Add New', 'book'),
            'add_new_item' => __('Clock Out'),
            'edit_item' => __('Edit Shift'),
            'new_item' => __('Clock In'),
            'all_items' => __('All Shifts'),
            'view_item' => __('View Shift'),
            'search_items' => __('Search Shifts'),
            'not_found' => __('No shifts found'),
            'not_found_in_trash' => __('No shifts found in the Trash'),
            'parent_item_colon' => '',
            'menu_name' => 'Employee Shifts',
        );
        $args = array(
            'labels' => $labels,
            'description' => 'Employee shifts dates and times',
            'query_var' => true,
            'public' => true,
            'show_ui' => true,
            'supports' => array('title', 'author'),
            'has_archive' => true,
            'show_tagcloud' => false,
            'rewrite' => array('slug' => 'shifts'),
            'show_in_nav_menus' => false,
            'supports' => false
        );
        register_post_type('shift', $args);
    }

    private function define_roles()
    {
        remove_role('manager');
        $result = add_role(
            'manager',
            __('Manager'),
            array(
                'read' => true,
                'create_posts' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'publish_posts' => true,
                'manage_categories' => true,
            )
        );

        remove_role('volunteer');
        $result = add_role(
            'volunteer',
            __('Volunteer'),
            array(
                'read' => true,
            )
        );

        remove_role('employee');
        $result = add_role(
            'employee',
            __('Employee'),
            array(
                'read' => true,
            )
        );

        remove_role('time_clock_admin');
        $result = add_role(
            'time_clock_admin',
            __('Time Clock Admin'),
            array(
                'read'              => true, // Allows a user to read        
                'create_shifts'      => true, // Allows user to create new posts
                'edit_posts'        => true, // Allows user to edit their own posts
                'edit_shifts'        => true, // Allows user to edit their own posts
                'edit_others_posts' => true, // Allows user to edit others posts too
                'edit_others_shifts' => true, // Allows user to edit others posts too
                'publish_posts'     => true, // Allows the user to publish posts
                'publish_shifts'     => true, // Allows the user to publish posts
                'manage_categories' => true, // Allows user to manage post categories        
                'edit_private_posts' => true, // Allows user to manage post categories                
                'edit_private_shifts' => true, // Allows user to manage post categories                
                'read_private_posts' => true, // Allows user to manage post categories                
                'read_private_shifts' => true, // Allows user to manage post categories                
                'edit_published_posts' => true, // Allows user to manage post categories                
                'edit_published_shifts' => true, // Allows user to manage post categories                
            )
        );
    }

    public function admin_init()
    {
        add_filter('manage_edit-shift_columns', array($this, 'shift_columns_filter'), 10, 1);
        add_action('manage_shift_posts_custom_column', array($this, 'shift_column'), 10, 2);
    }

    public function shift_columns_filter($columns)
    {
        unset($columns['date']);
        unset($columns['author']);
        $columns['employee'] = __('Employee', 'employee');
        $columns['department'] = __('Department', 'department');
        $columns['employee_clock_in_time'] = __('Clock In Time', 'employee_clock_in_time');
        $columns['employee_clock_out_time'] = __('Clock Out Time', 'employee_clock_out_time');
        $columns['total_shift_time'] = __('Total Time', 'total_shift_time');
        return $columns;
    }

    public function shift_column($column, $post_id)
    {
        global $post;
        $custom = get_post_custom($post_id);
        switch ($column) {
            case 'employee':
                $first_name = get_the_author_meta('first_name');
                $last_name = get_the_author_meta('last_name');
                echo $last_name . ", " . $first_name;
                break;
            case 'department':
                echo DepartmentColumn(get_the_author_meta('ID'), $post_id);
                break;
            case 'employee_clock_in_time':
                echo get_post_meta($post_id, 'employee_clock_in_time', true);
                break;
            case 'employee_clock_out_time':
                echo get_post_meta($post_id, 'employee_clock_out_time', true);
                break;
            case 'total_shift_time':
                echo get_shift_total($post_id);
                break;
        }
    }

    private function DepartmentColumn($author_id, $p_id)
    {
        $department = "";    
        if (get_option("aio_timeclock_show_current_dept") == "enabled"){
            $user_groups = wp_get_object_terms($author_id, 'department', array('fields' => 'all_with_object_id'));  // Get user group detail
            foreach($user_groups as $user_gro)
            {
                $department = $user_gro->name; // Get current user group name            
            }        
        }
        else{
            $department = get_post_meta($p_id, 'department', true);        
        }

        return $department;
    }

    private function get_shift_total($post_id)
    {
        $employee_clock_in_time = get_post_meta($post_id, 'employee_clock_in_time', true);
        $employee_clock_out_time = get_post_meta($post_id, 'employee_clock_out_time', true);

        if ($employee_clock_in_time != null && $employee_clock_out_time != null) {
            $total_shift_time = date_difference($employee_clock_out_time, $employee_clock_in_time);
        } else {
            $total_shift_time = '00:00:00';
        }

        return $total_shift_time;
    }

    private function date_difference($end, $start)
    {
        $dteStart = new DateTime($start);
        $dteEnd = new DateTime($end);

        $dteDiff = $dteStart->diff($dteEnd);

        return $dteDiff->format("%H:%I:%S");
    }

    private function date_to_hours($time)
    {
        $timeSplit = explode (':', $time);
        $hours = intval($timeSplit[0]);
        $minutes = intval($timeSplit[1]);
        $seconds = intval($timeSplit[2]);
        return round($hours + ($minutes / 60) + ($seconds / 3600), 2);
    }

    public function shift_info_box_meta()
    {
        add_meta_box(
            'shift_info_box',
            __('Shift Info', 'aio-timeclock'),
            array($this, 'shift_info_box_content'),
            'shift',
            'normal',
            'high'
        );
    }

    public function shift_info_box_content()
    {
        include "aio-time-clock-box-content.php";
    }



    private function get_employee_select($selected)
    {
        $selected = json_decode($selected);
        $count = 0;
        $users = get_users('fields=all_with_meta');
        usort($users, create_function('$a, $b', 'if($a->last_name == $b->last_name) { return 0;} return ($a->last_name > $b->last_name) ? 1 : -1;'));
        foreach (array_filter($users, array($this, 'filter_roles')) as $user) {
            $active = "";
            if ($selected == $user->ID) {
                $active = "selected";
            }
            echo '<option value="' . $user->ID . '" ' . $active . '>' . $user->last_name . ", " . $user->first_name . '</option>';
            $count++;
        }
    }

    public function get_build_in_roles(){
        return array('employee', 'manager', 'volunteer', 'contractor', 'administrator');
    }

    public function filter_roles($user)
    {
        $roles = get_build_in_roles();
        return array_intersect($user->roles, $roles);
    }

    public function save_shift_meta($post_id)
    {

    if (isset($_REQUEST['clock_in'])) {
        update_post_meta($post_id, 'employee_clock_in_time', sanitize_text_field($_REQUEST['clock_in']));
        $employee_clock_out_time = get_post_meta($post_id, 'employee_clock_out_time', true);
    }

    if (isset($_REQUEST['clock_out'])) {
        update_post_meta($post_id, 'employee_clock_out_time', sanitize_text_field($_REQUEST['clock_out']));
    }

    if (isset($_REQUEST['employee_id'])) {
        remove_action('save_post', array($this, 'save_shift_meta'));
        $arg = array(
            'ID' => $post_id,
            'post_author' => sanitize_key(intval($_REQUEST['employee_id'])),
        );
        wp_update_post($arg);
        add_action('save_post', array($this, 'save_shift_meta'));
    }

    public function timeclock_settings_page()
    {
        include "aio-settings.php";
    }



    public function timeclock_monitoring_page()
    {
        include "aio-monitoring.php";
    }

    public function timeclock_reports_page()
    {
        include "aio-reports.php";
    }

    public function register_timeclock_settings()
    {
        register_setting('nertworks-timeclock-settings-group', 'aio_company_name');    
        register_setting('nertworks-timeclock-settings-group', 'aio_pay_schedule');
        register_setting('nertworks-timeclock-settings-group', 'aio_wage_manage');
        register_setting('nertworks-timeclock-settings-group', 'aio_timeclock_time_zone');
        register_setting('nertworks-timeclock-settings-group', 'aio_timeclock_text_align');
        register_setting('nertworks-timeclock-settings-group', 'aio_timeclock_redirect_employees');
        register_setting('nertworks-timeclock-settings-group', 'aio_timeclock_show_avatar');
        register_setting('nertworks-timeclock-settings-group', 'aio_timeclock_show_current_dept');
        register_setting('nertworks-timeclock-settings-group', 'aio_use_javascript_redirect');
    }

    public function check_tc_shortcode()
    {
        $loop = new WP_Query(array('post_type' => 'page', 'posts_per_page' => -1));
        while ($loop->have_posts()): $loop->the_post();
            $content = get_the_content();
            if (has_shortcode($content, 'show_aio_time_clock_lite')) {
                return $loop->post->ID;
                break;
            } else {
                //echo "none";
            }
        endwhile;
        wp_reset_query();
    }

    public function check_eprofile_shortcode()
    {
        $loop = new WP_Query(array('post_type' => 'page', 'posts_per_page' => -1));
        while ($loop->have_posts()) : $loop->the_post();
            $content = get_the_content();
            if (has_shortcode($content, 'show_aio_employee_profile_lite')) {
                return $loop->post->ID;
            } else {
                //echo "none";
            }
        endwhile;
        wp_reset_query();
    }

    public function member_login_redirect($redirect_to, $request, $user)
    {
        $tc_page = check_tc_shortcode();
        $role = 'employee';
        if (is_array($user->roles) && in_array($role, $user->roles)) {
            return get_permalink($tc_page);
        } else {
            return $redirect_to;
        }
    }

    private function get_time_zone_list()
    {
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return $tzlist;
    }

    private function timeclock_shifts_page()
    {    
        if (get_option("aio_use_javascript_redirect") == "enabled"){
            echo '<script>
                window.location="'.admin_url().'/edit.php?post_type=shift";
            </script>';
        }
        else{
            wp_redirect( admin_url().'/edit.php?post_type=shift' );
            exit;
        }
    }

    public function timeclock_employee_page()
    {    
        include("aio-employees.php");
    }

    public function timeclock_department_page()
    {
        if (get_option("aio_use_javascript_redirect") == "enabled"){
            echo '<script>
                window.location="'.admin_url().'/edit-tags.php?taxonomy=department";
            </script>';
        }
        else{
            wp_redirect( admin_url().'/edit-tags.php?taxonomy=department' );
            exit;
        }
    }

    private function timeclock_manager_page()
    {
        if (get_option("aio_use_javascript_redirect") == "enabled"){
            echo '<script>
                window.location="'.admin_url().'/users.php?role=manager";
            </script>';
        }
        else{
            wp_redirect( admin_url().'/users.php?role=manager' );
            exit;
        }
    }

    private function timeclock_tc_admin_page()
    { 
        if (get_option("aio_use_javascript_redirect") == "enabled"){
            echo '<script>
                window.location="'.admin_url().'/users.php?role=time_clock_admin";
            </script>';
        }
        else{
            wp_redirect( admin_url().'/users.php?role=time_clock_admin' );
            exit;
        }
    }

    public function get_shift_total_from_range($employee, $date_range_start, $date_range_end)
    {
        $shift_total_time = 0;
        $shift_sum = '';
        $shift_array = array();
        $count = 0;
        $loop = new WP_Query(array('post_type' => 'shift', 'author' => $employee, 'posts_per_page' => -1));

        while ($loop->have_posts()): $loop->the_post();
            $shift_id = $loop->post->ID;
            $custom = get_post_custom($shift_id);
            $employee_clock_in_time = $custom["employee_clock_in_time"][0];
            $employee_clock_out_time = $custom["employee_clock_out_time"][0];
            if ($employee_clock_in_time != null) {
                $employee_clock_in_time = date('Y/m/d h:i:s A', strtotime($employee_clock_in_time));
            }
            if ($employee_clock_out_time != null) {
                $employee_clock_out_time = date('Y/m/d h:i:s A', strtotime($employee_clock_out_time));
            }
            $searchDateBegin = date('Y/m/d h:i:s A', strtotime($date_range_start));
            $searchDateEnd = date('Y/m/d h:i:s A', strtotime($date_range_end));
            if ((strtotime($employee_clock_in_time) >= strtotime($searchDateBegin)) && (strtotime($employee_clock_in_time) <= strtotime($searchDateEnd))) {
                $author_id = $loop->post->post_author;
                $last_name = get_the_author_meta('last_name', $author_id);
                $first_name = get_the_author_meta('first_name', $author_id);

                if ($employee_clock_in_time != null && $employee_clock_out_time != null) {
                    $shift_sum = date_difference($employee_clock_out_time, $employee_clock_in_time);
                } else {
                    $shift_sum = '00:00:00';
                }
                $shift_total_time = sum_the_time($shift_total_time, $shift_sum);
                array_push($shift_array,
                    array(
                        "shift_id" => $shift_id,
                        "employee_clock_in_time" => $employee_clock_in_time,
                        "employee_clock_out_time" => $employee_clock_out_time,
                        "first_name" => $first_name,
                        "last_name" => $last_name,
                        "shift_sum" => $shift_sum,
                    )
                );
                $count++;
            }
        endwhile;
        wp_reset_query();
        return array(
            "response" => "success",
            "shift_count" => $count,
            "shift_total_time" => $shift_total_time,
            "shift_array" => $shift_array,
        );
    }

    public function sum_the_time($time1, $time2)
    {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time) {
            list($hour, $minute, $second) = explode(':', $time);
            $seconds += $hour * 3600;
            $seconds += $minute * 60;
            $seconds += $second;
        }
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function modify_employee_wage($profile_fields)
    {
        $profile_fields['employee_wage'] = 'Wage';
        return $profile_fields;
    }

    public function user_taxonomy()
    {
        register_taxonomy(
            'department',
            'user',
            array(
                'public' => true, 'show_admin_column' => true,
                'labels' => array(
                    'name' => __('Departments'),
                    'singular_name' => __('Department'),
                    'menu_name' => __('Departments'),
                    'search_items' => __('Search Departments'),
                    'popular_items' => __('Popular Departments'),
                    'all_items' => __('All Departments'),
                    'edit_item' => __('Edit Department'),
                    'update_item' => __('Update Department'),
                    'add_new_item' => __('Add New Department'),
                    'new_item_name' => __('New Department Name'),
                    'separate_items_with_commas' => __('Separate Departments with commas'),
                    'add_or_remove_items' => __('Add or remove Departments'),
                    'choose_from_most_used' => __('Choose from the most popular Departments'),
                ),
                'rewrite' => array(
                    'with_front' => true,
                    'slug' => 'department' // Use 'author' (default WP user slug).
                ),
                'capabilities' => array(
                    'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
                    'edit_terms' => 'edit_users',
                    'delete_terms' => 'edit_users',
                    'assign_terms' => 'read',
                ),
                'update_count_callback' => array($thus, 'update_department_count') // Use a custom function to update the count.
            )
        );
    }

    public function add_department_admin_page()
    {
        $tax = get_taxonomy('department');
        add_users_page(
            esc_attr($tax->labels->menu_name),
            esc_attr($tax->labels->menu_name),
            $tax->cap->manage_terms,
            'edit-tags.php?taxonomy=' . $tax->name
        );
    }

    public function manage_department_user_column($columns)
    {
        unset($columns['posts']);
        $columns['users'] = __('Users');
        return $columns;
    }

    public function update_department_count($terms, $taxonomy)
    {
        global $wpdb;
        foreach ((array)$terms as $term) {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term));
            do_action('edit_term_taxonomy', $term, $taxonomy);
            $wpdb->update($wpdb->term_taxonomy, compact('count'), array('term_taxonomy_id' => $term));
            do_action('edited_term_taxonomy', $term, $taxonomy);
        }
    }

    public function manage_department_column($display, $column, $term_id)
    {
        if ('users' === $column) {
            $term = get_term($term_id, 'department');
            echo $term->count;
        }
    }

    add_action( 'object_sync_for_salesforce_pull_success', array($this, 'FFC_pull_success'), 10, 3 );

     public function FFC_pull_success( $op, $result, $synced_object ) {
        $map = $synced_object['mapping_object'];
        $salesforce_id = $map['salesforce_id'];
        $user_id = $map['wordpress_id'];

        update_user_meta( $user_id, 'SalesforceID',$saleforce_id );
    }

    public function edit_user_department_section($user)
    {
        $tax = get_taxonomy('department');
        /* Make sure the user can assign terms of the department taxonomy before proceeding. */
        if (!current_user_can($tax->cap->assign_terms))
            return;
        /* Get the terms of the 'department' taxonomy. */
        $terms = get_terms('department', array('hide_empty' => false)); ?>
        <h3><?php _e('Department'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="department"><?php _e('Select Department'); ?></label></th>
                <td><?php
                    /* If there are any department terms, loop through them and display checkboxes. */
                    if (!empty($terms)) {
                        foreach ($terms as $term) { ?>
                            <input type="radio" name="department" id="department-<?php echo esc_attr($term->slug); ?>"
                                value="<?php echo esc_attr($term->slug); ?>" <?php checked(true, is_object_in_term($user->ID, 'department', $term)); ?> />
                            <label for="department-<?php echo esc_attr($term->slug); ?>"><?php echo $term->name; ?></label>
                            <br/>
                        <?php }
                    } /* If there are no department terms, display a message. */
                    else {
                        _e('There are no departments available.');
                    }
                    ?></td>
            </tr>
        </table>
    <?php 
    }

    public function save_user_department_terms($user_id)
    {
        $tax = get_taxonomy('department');
        if (!current_user_can('edit_user', $user_id) && current_user_can($tax->cap->assign_terms))
            return false;
        $term = esc_attr(sanitize_text_field($_POST['department']));
        wp_set_object_terms($user_id, array($term), 'department', false);
        clean_object_term_cache($user_id, 'department');
    }
}

$FFC_timeclock = new FFC_Volunteer_Timeclock();