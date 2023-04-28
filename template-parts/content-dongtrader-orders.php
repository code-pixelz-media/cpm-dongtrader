<?php 

defined( 'ABSPATH' ) || exit;
$order_details = get_user_meta(get_current_user_id(),'_buyer_details',true);
$cs            = get_woocommerce_currency_symbol();
$filter_template_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'filter-top.php';
$pagination_template_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'pagination-buttom.php';
?>
<div class="detente-orders cpm-table-wrap">
    <h3><?php esc_html_e('My orders', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
        <?php if(file_exists($filter_template_path) && !empty($order_details))  load_template($filter_template_path,true); ?>
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

                        $items_per_page = 5;

                        // determine current page number from query parameter
                        $current_page = isset($_GET['listpaged']) ? intval($_GET['listpaged']) : 1;
                
                        // calculate start and end indices for items on current page
                        $start_index = ($current_page - 1) * $items_per_page;
                
                        if (isset($_REQUEST['filter'])) {
                
                            //get filter data from url parameters
                            $get_filter = sanitize_text_field($_REQUEST['filter']);
                
                            if ($get_filter == "all") {
                
                                $all_selected = "selected";
                                $date_selected = "";
                
                            } elseif ($get_filter == "within-a-date-range") {
                
                            //get start date
                            $start = sanitize_text_field($_REQUEST['start-month']);
                
                            //get end date
                            $enddate = sanitize_text_field($_REQUEST['end-month']);
                            $date_selected = "selected";
                            $all_selected = "";
                
                            if (strtotime($start) > strtotime($enddate)) {
                                $temp_date = $start;
                                $start = $enddate;
                                $enddate = $temp_date;
                            }
                
                            $start_date_obj = strtotime($start);
                            $end_date_obj = strtotime($enddate);
                
                            if ($start_date_obj && $end_date_obj) {
                
                                $results = array_filter($order_details, function ($item) use ($start_date_obj, $end_date_obj) {
                                    $order = new WC_Order($item['order_id']);
                                    $item_date = strtotime($order->get_date_created()->date('Y-m-d'));
                
                                    return ($item_date >= $start_date_obj && $item_date <= $end_date_obj);
                                });
                
                                $order_details = $results;
                                $start_index = 0;
                
                            }
                        }
                        } else {
                            $start = "";
                            $enddate = "";
                            $date_selected = "";
                            $all_selected = "";
                    
                        }

                        $paginated_array = array_slice($order_details, $start_index, $items_per_page);
                        $rebate_sum  = array_sum(array_column($paginated_array, 'rebate'));
                        $process_sum = array_sum(array_column($paginated_array, 'process'));
                        $profit_sum  = array_sum(array_column($paginated_array, 'seller_profit'));
                        $total_sum   = array_sum(array_column($paginated_array,'total'));

                        foreach($paginated_array as $od) : 
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
    <?php if(!empty($order_details)): ?>
        <div class="dong-pagination user-trading-list-paginate" style="float:right">
            <?php
            $num_items = count($order_details);

            $num_pages = ceil($num_items / $items_per_page);

            echo paginate_links(array(
                'base' => add_query_arg('listpaged', '%#%'),
                'format' => 'list',
                'prev_text' => __('&laquo; Previous', 'cpm-dongtrader'),
                'next_text' => __('Next &raquo;', 'cpm-dongtrader'),
                'total' => $num_pages,
                'current' => $current_page,
            ));
            ?>
        </div>
    <?php endif; ?>
</div>
