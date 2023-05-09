<?php 

$treasury_details         = get_user_meta(get_current_user_id(),'_treasury_details',true);
$cs                       = get_woocommerce_currency_symbol();
$filter_template_path     = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'filter-top.php';
$pagination_template_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'pagination-buttom.php';
extract($args);
?>
<div class="detente-treasury cpm-table-wrap">
    <h3><?php esc_html_e('Treasury', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
    <?php
    if(!empty($treasury_details)) 
        if(file_exists($filter_template_path) && !empty($treasury_details))  load_template($filter_template_path,true,$treasury_details); 
    ?>
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
                        $paginated_treasury   = dongtrader_pagination_array($treasury_details,10,true);
                        $total_order_amt_sum  = array_sum(array_column($paginated_treasury, 'total_amt'));
                        $distrb_amt_aum       = array_sum(array_column($paginated_treasury, 'distrb_amt'));
                        $rem_amt_sum          = array_sum(array_column($paginated_treasury, 'rem_amt'));
                        foreach($paginated_treasury as $od) : 
                            $order = new WC_Order($od['order_id']);
                            $formatted_order_date = wc_format_datetime($order->get_date_created(), 'Y-m-d');
                            echo '<tr>';
                            echo '<td>'.$od['order_id'].'/'.$formatted_order_date.'</td>';
                            echo '<td>'.$od['name'].'</td>';
                            echo '<td>'.$od['product_title'].'</td>';
                            echo '<td>'.$symbol.$od['total_amt']*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$od['distrb_amt']*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$od['rem_amt']*$vnd_rate.'</td>';
                            echo '</tr>';
                        endforeach;
                        echo '<tfoot>';
                            echo '<tr>';
                            echo '<td colspan="3">All Totals</td>';
                            echo '<td>'.$symbol.$total_order_amt_sum*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$distrb_amt_aum*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$rem_amt_sum*$vnd_rate.'</td>';
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
    <?php if(file_exists($pagination_template_path) && !empty($treasury_details))  load_template($pagination_template_path,true , $treasury_details); ?>
</div>
