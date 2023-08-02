<?php 
$commission_details       = get_user_meta(get_current_user_id(),'_commission_details',true);
$cs                       = get_woocommerce_currency_symbol();
$filter_template_path     = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'filter-top.php';
$pagination_template_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'pagination-buttom.php';
extract($args);
?>
<div class="detente-commission cpm-table-wrap">
    <h3><?php esc_html_e('Commission ', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
    <?php 
    if(!empty($commission_details)) 
        if(file_exists($filter_template_path) && !empty($commission_details))  load_template($filter_template_path,true,$commission_details); 

    ?>
        <table class="wp-list-table widefat striped fixed trading-history" width="100%" cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th><?php esc_html_e('Order ID/Date', 'cpm-dongtrader'); ?>
                    <th><?php esc_html_e('Buyer', 'cpm-dongtrader'); ?>
                    <th><?php esc_html_e('Product', 'cpm-dongtrader'); ?></th>
                    <!-- <th><?php //esc_html_e('Individual Commission', 'cpm-dongtrader'); ?></th> -->
                    <th><?php esc_html_e('Group Commission', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Site Commission', 'cpm-dongtrader'); ?></th>
                    <th><?php esc_html_e('Total', 'cpm-dongtrader'); ?></th>
                </tr>
            </thead>
                <?php 
                echo '<tbody>';
                    if(!empty($commission_details)):
                        $paginated_commission = dongtrader_pagination_array($commission_details,10,true);
                        $seller_com_sum   = array_sum(array_column($commission_details, 'seller_com'));
                        $group_com_sum    = array_sum(array_column($commission_details, 'group_com'));
                        $site_com_sum     = array_sum(array_column($commission_details, 'site_com'));
                        $total_sum        = array_sum(array_column($commission_details, 'total'));
                        foreach($paginated_commission as $od) : 

                            if(get_post_type($od['order_id']) != 'shop_order') continue;
                            $order = new WC_Order($od['order_id']);
                            $formatted_order_date = wc_format_datetime($order->get_date_created(), 'Y-m-d');
                            echo '<tr>';
                            echo '<td>'.$od['order_id'].'/'.$formatted_order_date.'</td>';
                            echo '<td>'.$od['name'].'</td>';
                            echo '<td>'.$od['product_title'].'</td>';
                            // echo '<td>'.$symbol .$od['seller_com']*$vnd_rate.'</td>';
                            echo '<td>'.$symbol .$od['group_com']*$vnd_rate.'</td>';
                            echo '<td>'.$symbol .$od['site_com']*$vnd_rate.'</td>';
                            echo '<td>'.$symbol .$od['total']*$vnd_rate.'</td>';
                            echo '</tr>';
                        
                        endforeach;
                        echo '<tfoot>';
                            echo '<tr>';
                            echo '<td colspan="3">All Totals</td>';
                            // echo '<td>'.$symbol.$seller_com_sum*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$group_com_sum*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$site_com_sum*$vnd_rate.'</td>';
                            echo '<td>'.$symbol.$total_sum*$vnd_rate.'</td>';
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

    <?php if(file_exists($pagination_template_path) && !empty($commission_details))  load_template($pagination_template_path,true , $commission_details); ?>
</div>
