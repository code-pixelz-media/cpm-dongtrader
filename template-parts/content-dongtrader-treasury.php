<?php 

$treasury_details = get_user_meta(get_current_user_id(),'_treasury_details',true);
$cs               = get_woocommerce_currency_symbol();
?>
<div class="detente-treasury">
    <h3><?php esc_html_e('Treasury', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
        <table class="wp-list-table widefat striped fixed trading-history" width="100%" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('Order ID/Date', 'cpm-dongtrader'); ?>
                    <th><?php esc_html_e('Buyer', 'cpm-dongtrader'); ?>
                    <th><?php esc_html_e('Product', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Total Order Amount', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Amount Distributed', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Remaining Amount', 'cpm-dongtrader'); ?></th>
                </tr>
            </thead>
                <?php 
                echo '<tbody>';
                    if(!empty($treasury_details)):
                        $total_order_amt_sum  = array_sum(array_column($treasury_details, 'total_amt'));
                        $distrb_amt_aum = array_sum(array_column($treasury_details, 'distrb_amt'));
                        $rem_amt_sum  = array_sum(array_column($treasury_details, 'rem_amt'));
                        foreach($treasury_details as $od) : 
                            echo '<tr>';
                            echo '<td>'.$od['order_id'].'</td>';
                            echo '<td>'.$od['name'].'</td>';
                            echo '<td>'.$od['product_title'].'</td>';
                            echo '<td>'.$cs.$od['total_amt'].'</td>';
                            echo '<td>'.$cs.$od['distrb_amt'].'</td>';
                            echo '<td>'.$cs.$od['rem_amt'].'</td>';
                            echo '</tr>';
                        endforeach;
                        echo '<tfoot>';
                            echo '<tr>';
                            echo "<td colspan='3'>All Totals</td>";
                            echo "<td>$cs$total_order_amt_sum</td>";
                            echo "<td>$cs$distrb_amt_aum</td>";
                            echo "<td>$cs$rem_amt_sum</td>";
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
</div>
