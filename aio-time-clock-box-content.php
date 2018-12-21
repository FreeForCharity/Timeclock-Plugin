<div>
    <?php
    global $post;
    $custom = get_post_custom($post->ID);
    $clock_in = $custom["employee_clock_in_time"][0];
    $clock_out = $custom["employee_clock_out_time"][0];
    $location = $custom["location"][0];
    $ip_address_in = $custom["ip_address_in"][0];
    $ip_address_out = $custom["ip_address_out"][0];
    $employee_id = get_post_field( 'post_author', $post->ID );
    $recent_author = get_user_by( 'ID', $employee_id );
    $employee_name = $recent_author->last_name . ", " . $recent_author->first_name;
    $selected_employee = "";
    ?>
     <table class="widefat fixed" cellspacing="0">
        <tr class="alternate">
            <th scope="col" class="manage-column column-columnname"><strong><?php _e('Employee:'); ?> </strong></th>
            <td>
                <?php 
                    if ($employee_id != null){
                        $selected_employee = $employee_id;
                        echo $employee_name;
                    }
                    
                ?>
            </td>
            <td>
                <a class="button" onclick="editEmployee()" title="Edit Employee"><span class="dashicons dashicons-admin-users vmiddle"></span></a>
                <select id="employee_id" name="employee_id" style="display:none;">
                    <?php 
                        $FFC_timeclock->get_employee_select($selected_employee);
                    ?>
                </select>
            </td>
        </tr>
        <tr class="">
            <th scope="col" class="manage-column column-columnname"><strong><?php _e('IP Address:'); ?> </strong></th>
            <td>
                <strong>In: </strong>
                <?php 
                    if ($ip_address_in != null){
                        echo $ip_address_in; 
                    }
                    else{
                         _e('Blank');
                    }
                ?>
                <br />
                <strong>Out: </strong>
                <?php 
                    if ($ip_address_out != null){
                        echo $ip_address_out; 
                    }
                    else{
                        _e('Blank');
                    }
                ?>
            </td>
            <td>
            </td>
        </tr>
        <tr class="alternate">
            <th scope="col" class="manage-column column-columnname"><strong><?php _e('Clock In:'); ?> </strong></th>
            <td>
                <?php 
                    if ($clock_in != null){
                        echo $clock_in; 
                    }
                    else{
                        _e('Blank');
                    }
                ?>
            </td>
            <td>
                <a class="button" onclick="editClockTime('in')" title="Edit Clock In Time"><span class="dashicons dashicons-clock vmiddle"></span></a>                
                <input type="text" id="clock_in" name="clock_in" style="display:none;" value="<?php echo $clock_in; ?>"/>
            </td>
        </tr>
        <tr class="">
            <th scope="col" class="manage-column column-columnname"><strong><?php _e('Clock Out:'); ?> </strong></th>
            <td>
                <?php 
                    if ($clock_out != null){
                        echo $clock_out; 
                    }
                    else{
                        _e('Blank');
                    }
                ?>
            </td>
            <td>
                <a class="button" onclick="editClockTime('out')" title="Edit Clock Out Time"><span class="dashicons dashicons-clock vmiddle"></span></a>                
                <input type="text" id="clock_out" name="clock_out" style="display:none;" value="<?php echo $clock_out; ?>"/>
            </td>
        </tr>
        <tr class="alternate">
                <th scope="col" class="manage-column column-columnname"><strong><?php _e('Total Shift Time:'); ?> </strong></th>
            <td>
                <?php echo $FFC_timeclock->get_shift_total($post->ID); ?>
            </td>
            <td>
                <i></i>
            </td>
        </tr>        
    </table>
    
</div>