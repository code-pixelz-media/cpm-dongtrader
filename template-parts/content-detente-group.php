<?php 
$group_details            = get_user_meta(get_current_user_id(),'_group_details',true);
$cs                       = get_woocommerce_currency_symbol();
$filter_template_path     = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'filter-top.php';
$pagination_template_path = CPM_DONGTRADER_PLUGIN_DIR.'template-parts'.DIRECTORY_SEPARATOR.'partials'. DIRECTORY_SEPARATOR.'pagination-buttom.php';
extract($args);

?>

<div class="detente-groups cpm-table-wrap">
    <h3><?php esc_html_e('Group ', 'cpm-dongtrader'); ?></h3>
    <br class="clear" />
    <div id="member-history-orders" class="widgets-holder-wrap">
    <?php 
    if(!empty($group_details)) 
        if(file_exists($filter_template_path) && !empty($group_details))  load_template($filter_template_path,true,$group_details); 

    ?>
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
                    if(!empty($group_details)):
                        $paginated_gd = dongtrader_pagination_array($group_details,10,true);
                        $i =1;
                        foreach($paginated_gd as $gd) : 
                           
                            echo '<tr>';
                            echo '<td>'.$i.'</td>';
                            echo '<td>'.$gd['order_id'].'</td>' ;
                            echo '<td>'.$gd['order_date'].'</td>';
                            echo '<td>'.$gd['gf_name'].'</td>';
                            echo '<td>'.$symbol.$gd['profit_amount']*$vnd_rate.'</td>';
                            if($gd['release']['enabled'] ){
                                if($gd['release']['note']){
                                    echo '<td>'.$gd['release']['note'].'</td>';
                                }else{
                                    echo '<td>The requested amount has been released</td>';
                                }
                               
                            }else{
                                echo '<td>--</td>';
                            }
                           
                            echo '</tr>';

                        $i++;
                        endforeach;
                    else:
                        echo '<tr>';
                        echo '<td style="text-align:center;"colspan="6" >Details Not Found</td>';
                        echo '</tr>';
                    endif;
                echo '</tbody>';
            
    ?>
        </table>
    </div>
    <?php if(file_exists($pagination_template_path) && !empty($group_details))  load_template($pagination_template_path,true , $group_details ); ?>
</div>