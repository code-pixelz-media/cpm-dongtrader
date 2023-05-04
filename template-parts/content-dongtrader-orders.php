<?php 

defined( 'ABSPATH' ) || exit;

$order_details              = get_user_meta(get_current_user_id(),'_buyer_details',true);
$cs                         = get_woocommerce_currency_symbol();
$filter_template_path       = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'filter-top.php';
$pagination_template_path   = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'pagination-buttom.php';
?>
<div class="detente-orders cpm-table-wrap">
    <h3><?php esc_html_e('My orders', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
        <?php if(file_exists($filter_template_path) && !empty($order_details))  load_template($filter_template_path,true , $order_details); ?>
        <table class="wp-list-table widefat striped fixed trading-history" width="100%" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('Order ID/Date', 'cpm-dongtrader'); ?>
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
                        $paginated_orders = dongtrader_pagination_array($order_details,10,true);
                        $rebate_sum  = array_sum(array_column($paginated_orders, 'rebate'));
                        $process_sum = array_sum(array_column($paginated_orders, 'process'));
                        $profit_sum  = array_sum(array_column($paginated_orders, 'seller_profit'));
                        $total_sum   = array_sum(array_column($paginated_orders,'total'));

                        foreach($paginated_orders as $od) : 
                            $order = new WC_Order($od['order_id']);
                            $formatted_order_date = wc_format_datetime($order->get_date_created(), 'Y-m-d');
                            echo '<tr>';
                            echo '<td>'.$od['order_id'].'/'.$formatted_order_date.'</td>';
                            echo '<td>'.$od['product_title'].'</td>';
                            echo '<td>'.$cs.$od['rebate'].'</td>';
                            echo '<td>'.$cs.$od['process'].'</td>';
                            echo '<td>'.$cs.$od['seller_profit'].'</td>';
                            echo '<td>'.$cs.$od['total'].'</td>';
                            echo '</tr>';
                        endforeach;
                        echo '<tfoot>';
                            echo '<tr>';
                            echo "<td colspan='2'>All Totals</td>";
                            echo "<td>$cs$rebate_sum</td>";
                            echo "<td>$cs$process_sum</td>";
                            echo "<td>$cs$profit_sum</td>";
                            echo "<td>$cs$total_sum</td>";
                            echo '</tr>';
                        echo '</tfoot>';
                else:
                        echo '<tr>';
                        echo '<td style="text-align:center;"colspan="6" >Details Not Found</td>';
                        echo '</tr>';
                endif;
                echo '</tbody>';

           
            
    ?>
        </table>
    </div>
    <?php if(file_exists($pagination_template_path) && !empty($order_details))  load_template($pagination_template_path,true , $order_details); ?>
</div>
