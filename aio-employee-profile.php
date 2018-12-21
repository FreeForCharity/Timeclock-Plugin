<?php
global $wordpress;
global $wpdb;
global $current_user;
$timeclock_button = null;
if (is_user_logged_in() == true) {
    wp_get_current_user();
    $tc_page = $FFC_timeclock->check_tc_shortcode();
?>
<table>
<?php 
if ($tc_page != null){
    echo '<a class="button" href="'.get_permalink($tc_page).'">Back to Time Clock</a>';
}
?>
<?php $loop = new WP_Query(array('post_type' => 'shift', 'author' => $current_user->ID, 'posts_per_page' => -1));?>
<?php while ($loop->have_posts()): $loop->the_post();?>
	    <?php
            $custom = get_post_custom($loop->post->ID);
            $employee_clock_in_time = $custom["employee_clock_in_time"][0];
            $employee_clock_out_time = $custom["employee_clock_out_time"][0];
            $employee_clock_in_time = date('Y-m-d', strtotime($employee_clock_in_time));
            $shift_sum = '';
            ?>
	        <tr valign="top">
	            <?php
                $last_name = get_the_author_meta('last_name');
                $first_name = get_the_author_meta('first_name');
                ?>
	            <td scope="row"><?php echo ucfirst($last_name) . ", " . ucfirst($first_name); ?></td>
	            <td>
	                <?php if (get_post_meta(get_the_ID(), 'employee_clock_in_time', true)): ?>
	                    <?php $clock_in = get_post_meta(get_the_ID(), 'employee_clock_in_time', true);
                    if ($clock_in != null) {
                        $newDate = date(get_option('date_format') . " " . get_option('time_format'), strtotime($clock_in));
                        echo $newDate;
                    } else {
                        echo 'Clock IN Null';
                    }
                    ?>
	                <?php endif;?>
                </td>
                <td>
                <?php if (get_post_meta(get_the_ID(), 'employee_clock_out_time', true)): ?>
                    <?php $clock_out = get_post_meta(get_the_ID(), 'employee_clock_out_time', true);?>
                    <?php
                    if ($clock_out != null) {
                            $outDate = date(get_option('date_format') . " " . get_option('time_format'), strtotime($clock_out));
                            echo $outDate;
                        } else {
                            echo 'Clock Out Null';
                        }
                    ?>
                <?php endif;?>
                </td>
                <td>
                    <?php if (get_post_meta(get_the_ID(), 'employee_clock_out_time', true)): ?>
                        <?php $shift_sum = $FFC_timeclock->date_difference(get_post_meta(get_the_ID(), 'employee_clock_out_time', true), get_post_meta(get_the_ID(), 'employee_clock_in_time', true));
                        echo $shift_sum . "<br />";
                        $shift_total_time = $FFC_timeclock->sum_the_time($shift_total_time, $shift_sum);
                        ?>
                    <?php endif;?>
                </td>
            </tr>
        <?php $count++;
    endwhile;
} else {
    _e("Must be logged in to view this page.");
}
?>
</table>