<?php 

$loggedin_user_id = get_current_user_id();


global $wpdb;

//Our Custom table name from the database.
$table_name = $wpdb->prefix . 'manage_glassfrogs_api';

$current_user_role = $wpdb->get_var($wpdb->prepare("SELECT gf_role_assigned FROM $table_name WHERE user_id = %d", $loggedin_user_id));

$all_friends_orders = $wpdb->get_results($wpdb->prepare("SELECT all_orders FROM $table_name WHERE gf_role_assigned = %s", $current_user_role),ARRAY_A);

$combined_order_array = array_reduce($all_friends_orders, function ($result, $order_array) {
  $orders = unserialize($order_array['all_orders']);
  return array_merge($result, $orders);
}, array());




?>

<div class="detente-groups">
    <h3><?php esc_html_e('Group ', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
        <table class="wp-list-table widefat striped fixed trading-history" width="100%" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'cpm-dongtrader'); ?>
                    <th><?php esc_html_e('Order Id', 'cpm-dongtrader'); ?>
                    <th><?php esc_html_e('Date', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Group Name', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Amount For', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Amount', 'cpm-dongtrader'); ?></th>
                </tr>
            </thead>
            <?php 
                echo '<tbody>';
                    if(!empty($combined_order_array)):
                        $i =1;
                        foreach($combined_order_array as $od) : 
                            $order = new WC_Order($od);
                            $formatted_order_date = wc_format_datetime($order->get_date_created(), 'Y-m-d');
                            echo '<tr>';
                            echo '<td>'.$order->get_user_id().'</td>';
                            echo '<td>'.$od.'</td>';
                            echo '<td>'.$formatted_order_date.'</td>';
                            echo '<td>'.$current_user_role.'</td>';
                            echo '<td>'.'-'.'</td>';
                            echo '<td>'.'-'.'</td>';
                            echo '</tr>';
                        $i++;
                        endforeach;
                        // echo '<tfoot>';
                        //     echo '<tr>';
                        //     echo "<td colspan='2'>All Totals</td>";
                        //     echo "<td>$cs$rebate_sum</td>";
                        //     echo "<td>$cs$process_sum</td>";
                        //     echo "<td>$cs$profit_sum</td>";
                        //     echo "<td>$cs$total_sum</td>";
                        //     echo '</tr>';
                        // echo '</tfoot>';
                    else:
                        echo '<tr>';
                        echo '<td style="text-align:center;"colspan="6" >Details Not Found</td>';
                        echo '</tr>';
                    endif;
                echo '</tbody>';
            
    ?>
        </table>
    </div>
</div>