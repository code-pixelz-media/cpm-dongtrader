<?php
/* Woocommerce hooks to add fields to my acount page */
/* Always Update Permalink after adding a new endpoint */
add_filter('woocommerce_account_menu_items', 'cpm_dong_generateqr', 40);
function cpm_dong_generateqr($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('generate-qrtiger' => 'Generate Qr Code')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_add_endpoint');
function cpm_dong_add_endpoint()
{

    add_rewrite_endpoint('generate-qrtiger', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_generate-qrtiger_endpoint', 'cpm_dong_my_account_endpoint_content');
function cpm_dong_my_account_endpoint_content()
{

    $user_id =  get_current_user_id();
    $user_meta_qrs = get_user_meta($user_id, 'dong_user_qr_vals', true);

    //var_dump($user_meta_qrs);
?>
    <div class='qrtiger-tabs-content'>
        <?php dong_get_tables_for_qr($user_meta_qrs); ?>
        <?php echo !empty($user_meta_qrs) ? '' : '<p>' . __('Qr Codes Not Generated Yet') . '</p>';  ?>
        <button class="dong-modal-toggle" data-target="openModal1">Add New QR</button>
    </div>

    <?php
}


function dong_get_tables_for_qr($qr_datas)
{
    if (!empty($qr_datas)) {

    ?>
        <div class="tabl-contents">
            <table>
                <tr>
                    <th><?php _e('S.N.', 'cpm-dongtrader') ?></th>
                    <th><?php _e('QR Id.', 'cpm-dongtrader') ?></th>
                    <th><?php _e('QR Image.', 'cpm-dongtrader') ?></th>
                </tr>
                <?php
                foreach ($qr_datas as $key => $data) {
                ?>
                    <tr>
                        <td><?php echo ++$key; ?></td>
                        <td><?php echo $data['qr_id']; ?></td>
                        <td><img src="<?php echo $data['qr_image_url']; ?>" alt="<?php echo $data['qr_image_url']; ?>" height="50" width="50" srcset=""></td>
                    </tr>

                <?php
                }
                ?>
            </table>
        </div>
    <?php
    }
}

add_action('wp_footer', 'dongtrader_modal_html');

function dongtrader_modal_html()
{
    ?>
    <div id="openModal1" class="dong-modal-wrapper">
        <div class="dong-modal">
            <a href="#close" title="Close" class="dong-close">X</a>
            <div class="dong-modal-header">
                <?php _e('Quick Response Details', 'cpm-dongtrader') ?>
            </div>
            <div class="dong-notify-msg">

            </div>
            <div class="dong-modal-content">
                <form action="" class="qrtiger-form" method="POST" id="modal-form">

                    <!-- <fieldset> -->
                    <!-- Qr Size -->
                    <label for="qrtiger-size"><?php _e('Size', 'cpm-dongtrader') ?></label>
                    <input type="number" class="qrtiger-size" name="qrtiger-size" placeholder="<?php _e('Size Of the Qr image', 'cpm-dongtrader') ?>" required>
                    <!-- Qr Text -->
                    <label for="qrtiger-text"><?php _e('Qr Text', 'cpm-dongtrader') ?></label>
                    <input type="text" class="qrtiger-text" name="qrtiger-text" placeholder="<?php _e('Text that appears after scanning qr code ', 'cpm-dongtrader') ?>" required>
                    <!--Qr URL  -->
                    <label for="qrtiger-url"><?php _e('Qr URL', 'cpm-dongtrader') ?></label>
                    <input type="url" class="qrtiger-url" name="qrtiger-url" placeholder="<?php _e('Url to redirect after scanning qr code', 'cpm-dongtrader') ?>" required>

                    <!-- </fieldset> -->
                    <input type="submit" value="Submit"><span class="form-loader" style="display: none;"><img src="<?php echo plugins_url() . '/cpm-dongtrader/img/loader.gif' ?>"></span>
                </form>
            </div>
        </div>
    </div>
<?php
}

/* Woocommerce hooks to add fields to my acount page  Ends*/



/* show memebership data on woocommerce tab */

add_filter('woocommerce_account_menu_items', 'cpm_dong_show_membership_data', 40);
function cpm_dong_show_membership_data($menu_links)
{

    $menu_links = array_slice($menu_links, 0, 5, true)
        + array('show-membership-data' => 'My Memberships')
        + array_slice($menu_links, 5, NULL, true);

    return $menu_links;
}
// register permalink endpoint
add_action('init', 'cpm_dong_my_membership_endpoint');
function cpm_dong_my_membership_endpoint()
{

    add_rewrite_endpoint('show-membership-data', EP_PAGES);
}
// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
add_action('woocommerce_account_show-membership-data_endpoint', 'cpm_dong_my_membership_endpoint_content');
function cpm_dong_my_membership_endpoint_content()
{

    // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
?>
    <div class='qr-tiger-code-generator'>
        <p><?php _e('My Memberships ', 'cpm-dongtrader') ?></p>
        <?php echo do_shortcode('[pmpro_account]');
        ?>
    </div>

<?php
}
/* show memebership data on woocommerce tab  ends*/
