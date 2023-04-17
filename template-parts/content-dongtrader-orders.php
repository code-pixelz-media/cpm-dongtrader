<?php 

$order_details = get_user_meta(get_current_user_id(),'_buyer_details',true);
$cs            = get_woocommerce_currency_symbol();
?>
<h3><?php esc_html_e('My orders', 'cpm-dongtrader'); ?></h3>
<br class="clear" />
<div id="member-history-orders" class="widgets-holder-wrap">
    <table class="wp-list-table widefat striped fixed trading-history" width="100%" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                <th><?php esc_html_e('Order ID/Date', 'cpm-dongtrader'); ?>
                <th><?php esc_html_e('Buyer', 'cpm-dongtrader'); ?>
                <th><?php esc_html_e('Product', 'cpm-dongtrader'); ?></th>
                <th><?php esc_html_e('7% Rebate', 'cpm-dongtrader'); ?></th>
                <th><?php esc_html_e('3% Process', 'cpm-dongtrader'); ?></th>
                <th><?php esc_html_e('Profit', 'cpm-dongtrader'); ?></th>
                <th><?php esc_html_e('Total', 'cpm-dongtrader'); ?></th>
            </tr>
        </thead>
            <?php 
            echo '<tbody>';
                if(!empty($order_details)):
                    $rebate_sum  = array_sum(array_column($order_details, 'rebate'));
                    $process_sum = array_sum(array_column($order_details, 'process'));
                    $profit_sum  = array_sum(array_column($order_details, 'seller_profit'));
                    $total_sum   = array_sum(array_column($order_details,'total'));
                    foreach($order_details as $od) : 
                        echo '<tr>';
                        echo '<td>'.$od['order_id'].'</td>';
                        echo '<td>'.$od['name'].'</td>';
                        echo '<td>'.$od['product_title'].'</td>';
                        echo '<td>'.$cs.$od['rebate'].'</td>';
                        echo '<td>'.$cs.$od['process'].'</td>';
                        echo '<td>'.$cs.$od['seller_profit'].'</td>';
                        echo '<td>'.$cs.$od['total'].'</td>';
                        echo '</tr>';
                    endforeach;
                    echo '<tfoot>';
                        echo '<tr>';
                        echo "<td colspan='3'>All Totals</td>";
                        echo "<td>$cs$rebate_sum</td>";
                        echo "<td>$cs$process_sum</td>";
                        echo "<td>$cs$profit_sum</td>";
                        echo "<td>$cs$total_sum</td>";
                        echo '</tr>';
                    echo '</tfoot>';
                else:
                    echo '<tr>';
                    echo '<td style="text-align:center;"colspan="7" >Details Not Found</td>';
                    echo '</tr>';
                endif;
            echo '</tbody>';
        
 ?>

        
    </table>
</div>
