<?php
if (!defined('ABSPATH')) exit;
$dong_qr_array = get_option('dong_user_qr_values');
?>

<div class="cpm-plugin-wrap">
	<div class="header-wrap">
		<div class="row">
			<div class="logo-wrap col-md-3">
				<img src="<?php echo plugin_dir_url(__FILE__) . '../img/settings-logo.png'; ?>" alt="logo" class="logo">

			</div>

			<div class="addbanner-wrap col-md-6">
				<div class="title">
					<h1><?php _e('Dongtraders APIs and QR generator Settings', 'dongtraders'); ?></h1>
				</div>
			</div>

			<div class="btn-wrap col-md-3">
				<!-- <a href="#" class="cpm-btn dashicons-before dashicons-heart">support</a>
				<a href="#" class="cpm-btn btn2 dashicons-before dashicons-star-filled">Rate us</a> -->
			</div>
		</div>
	</div>

	<div class="body-wrap">
		<div id="tabs-wrap" class="tabs-wrap">

			<ul class="tab-menu" id="dongtrader-tabs">
				<li class="nav-tab" id="first"><a href="#qr-code-generator" class="dashicons-before dashicons-editor-alignleft"><?php _e('QR Code', 'dongtraders'); ?></a></li>
				<?php if (!empty($dong_qr_array)) : ?>
					<li class="nav-tab" id="second"><a href="#qr-lists" class="dashicons-before dashicons-admin-generic"><?php _e('QR Lists', 'dongtraders'); ?></a></li>
				<?php endif; ?>
				<li class="nav-tab" id="third"><a href="#api-integration" class="dashicons-before dashicons-admin-generic"><?php _e('Integration API', 'dongtraders'); ?></a></li>

				<li class="nav-tab" id="fourth"><a href="#advanced" class="dashicons-before dashicons-admin-settings"><?php _e('Orders', 'dongtraders'); ?></a></li>
				<li class="nav-tab" id="fifth"><a href="#import-order" class="dashicons-before dashicons-admin-tools"><?php _e('Import Order', 'dongtraders'); ?></a></li>
			</ul>

			<div class="tab-content">
				<div id="qr-code-generator">
					<h3 class=" tab-title"><?php _e('Generate new QR code ', 'cpm-dongtrader') ?></h2>
						<p>Add new QR image by providing color ,size and URL of QR code.</p>
						<form action="" method="POST" class="qrtiger-form">
							<div class="dong-notify-msg">
								<!-- 
										purple(planning) rgb(153,0,153) 
										budget(orange) rgb(241 104 60),
										media(red) rgb(204,0,0),
										distribution(green) rgb(0,153,0),
										membership(blue) rgb(0,0,204)
										-->
							</div>
							<div class="form-group">
								<label for=""><?php _e('QR Size', 'cpm-dongtrader') ?></label>
								<div class="form-control-wrap">
									<input name="qrtiger-size" class="form-control qrtiger-size" type="number" placeholder="<?php _e('Actual size of QR Code', 'cpm-dongtrader') ?>" onfocus="this.placeholder=''" onblur="this.placeholder='<?php _e('Actual size of QR Code', 'cpm-dongtrader') ?>'" required value="500">
								</div>
							</div>
							<div class="form-group">
								<label for=""><?php _e('Select Color', 'cpm-dongtrader') ?></label>
								<div class="form-control-wrap">
									<select name="qrtiger-color" id="" class="form-control qrtiger-color" required>
										<option value=""><?php _e('Default', 'cpm-dongtrader') ?></option>
										<option value="rgb(153,0,153)"><?php _e('Planning(Purple)', 'cpm-dongtrader') ?></option>
										<option value="rgb(241,104,60)"><?php _e('Budget(Orange)', 'cpm-dongtrader') ?></option>
										<option value="rgb(204,0,0)"><?php _e('Media(Red)', 'cpm-dongtrader') ?></option>
										<option value="rgb(0,153,0)"><?php _e('Distribution(Green)', 'cpm-dongtrader') ?></option>
										<option value="rgb(0,0,204)"><?php _e('Membership(Blue)', 'cpm-dongtrader') ?></option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for=""><?php _e('QR URL', 'cpm-dongtrader') ?></label>
								<div class="form-control-wrap">
									<input name="qrtiger-url" class="form-control qrtiger-url" type="url" placeholder="<?php _e('Url to redirect after scanning qr code', 'cpm-dongtrader') ?>" onfocus="this.placeholder=''" onblur="this.placeholder='<?php _e('Url to redirect after scanning qr code', 'cpm-dongtrader') ?>'" required>
								</div>
							</div>

							<div class="form-group">
								<button type="submit" class="cpm-btn submit qrtiger-form-submit real-button">
									Generate
								</button>
								<button style="display: none;" type="submit" class="cpm-btn submit qrtiger-form-submit anim-button">
									Generating <i class="fa fa-spinner fa-spin custom-load"></i>
								</button>
							</div>
						</form>
				</div>

				<div id="api-integration">
					<h2 class="tab-title">Set Your API keys</h2>

					<hr>

					<?php $dongtraders_api_setting_data = get_option('dongtraders_api_settings_fields');
					?>
					<form action="options.php" method="POST" enctype="multipart/form-data" id="save-settings">
						<?php
						settings_errors();

						settings_fields('dongtraders_api_setting_page');


						?>
						<h3>QrTiger API Credentials</h3>
						<div class="form-group">
							<label for="">QrTiger API Key</label>
							<div class="form-control-wrap">
								<input class="form-control" name="dongtraders_api_settings_fields[qrtiger-api-key]" type="text" placeholder="Enter API Key" onfocus="this.placeholder=''" onblur="this.placeholder='Enter API Key'" value="<?php echo esc_html($dongtraders_api_setting_data['qrtiger-api-key']); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="">QrTiger API URL</label>
							<div class="form-control-wrap">
								<input class="form-control" name="dongtraders_api_settings_fields[qrtiger-api-url]" type="text" placeholder="Enter API URL" onfocus="this.placeholder=''" onblur="this.placeholder='Enter API URL'" value="<?php echo esc_html($dongtraders_api_setting_data['qrtiger-api-url']); ?>">
							</div>
						</div>
						<hr>
						<h3>Glassfrog API Credentials</h3>
						<div class="form-group">
							<label for="">Glassfrog API Key</label>
							<div class="form-control-wrap">
								<input class="form-control" name="dongtraders_api_settings_fields[glassfrog-api-key]" type="text" placeholder="Enter API Key" onfocus="this.placeholder=''" onblur="this.placeholder='Enter API Key'" value="<?php echo esc_html($dongtraders_api_setting_data['glassfrog-api-key']); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="">Glassfrog API URL</label>
							<div class="form-control-wrap">
								<input class="form-control" name="dongtraders_api_settings_fields[glassfrog-api-url]" type="text" placeholder="Enter API URL" onfocus="this.placeholder=''" onblur="this.placeholder='Enter API URL'" value="<?php echo esc_html($dongtraders_api_setting_data['glassfrog-api-url']); ?>">
							</div>
						</div>
						<hr>
						<h3>Crowdsignal API Credentials</h3>
						<div class="form-group">
							<label for="">Crowdsignal API Key</label>
							<div class="form-control-wrap">
								<input class="form-control" name="dongtraders_api_settings_fields[crowdsignal-api-key]" type="text" placeholder="Enter API Key" onfocus="this.placeholder=''" onblur="this.placeholder='Enter API Key'" value="<?php echo esc_html($dongtraders_api_setting_data['crowdsignal-api-key']); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="">crowdsignal API URL</label>
							<div class="form-control-wrap">
								<input class="form-control" name="dongtraders_api_settings_fields[crowdsignal-api-url]" type="text" placeholder="Enter API URL" onfocus="this.placeholder=''" onblur="this.placeholder='Enter API URL'" value="<?php echo esc_html($dongtraders_api_setting_data['crowdsignal-api-url']); ?>">
							</div>
						</div>

						<div class="form-group settings-submit">
							<input type="submit" class="cpm-btn submit save-settings-dash" name="submit" value="Save changes">
						</div>
					</form>
				</div>
				<?php if (!empty($dong_qr_array)) : ?>
					<div id="qr-lists">
						<?php if ($dong_qr_array) : ?>
							<h3 class="tab-title"><?php _e('Generated QR codes', 'cpm-dongtrader') ?></h2>
								<div class="cpm-table-wrap" style="overflow: hidden;">
									<table id="qr-all-list">
										<thead>
											<tr>
												<th>S.N</th>
												<th>QR ID</th>
												<th>QR Image</th>
												<th>QR URL</th>
												<th>Remove</th>

											</tr>
										</thead>
										<tbody>
											<?php $i = 1;
											foreach ($dong_qr_array as $key => $value) :

											?>
												<tr>
													<td><?= $i ?></td>
													<td><?php echo $value['qr_id'] ?></td>
													<td><img src="<?php echo $value['qr_image_url']; ?>" width="50" height="50"></td>
													<td><button class="cpm-btn url-copy" data-url="<?= $value['qr_image_url']; ?>">Copy</button></td>
													<td><button class="cpm-btn qr-delete" data-index="<?= $key ?>">Delete</button></td>
												</tr>
											<?php $i++;
											endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>
					</div>
				<?php endif; ?>
				<div id="advanced">
					<h2 class="tab-title">Order</h2>

					<?php dongtraders_list_order_meta_table(); ?>



				</div>

				<!-- import order tab -->
				<div id="import-order">
					<h2 class="tab-title">Import Order From CSV file </h2>
					<form action="" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for=""><?php _e('Select CSV file', 'cpm-dongtrader') ?></label>
							<div class="form-control-wrap">
								<input type="file" name="get_file" class="form-control" accept=".csv" required>

							</div>

						</div>
						<div class="form-group settings-submit">
							<input type="submit" class="cpm-btn submit save-settings-dash" name="import_csv" value="Import CSV">
						</div>

					</form>


					<?php

					if (isset($_POST['import_csv'])) {

						$upload_dir = wp_upload_dir();
						// $upload_dir = $uploads['baseurl'];
						$file = $upload_dir['basedir'] . '/' . $_FILES['get_file']['name'];
						$fileurl = $upload_dir['baseurl'] . '/' . $_FILES['get_file']['name'];
						if (!move_uploaded_file(
							$_FILES['get_file']['tmp_name'],
							$file
						)) {
							print_r('Failed to move uploaded file.');
						}

						if (($open = fopen($fileurl, "r")) !== FALSE) {
							// var_dump('file-open');
							// Skip the first line
							$first_row = true;
							$get_orders = array();
							$headers = array();

							while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {

								if ($first_row) {
									$headers = $data;
									$first_row = false;
								} else {
									$get_orders[] = array_combine($headers, array_values($data));
								}
							}

							/* fclose($open);
							echo "<pre>";
							var_dump($get_orders);
							echo "</pre>"; */
							foreach ($get_orders as $get_order) {
								# code...
								/* $order_csv_id = $get_order['csv_id']; */
								$order_date = $get_order['order_date'];
								$billing_first_name = $get_order['billing_first_name'];
								$billing_last_name = $get_order['billing_last_name'];
								$billing_full_name = $billing_first_name . ' ' . $billing_last_name;
								$billing_phone = $get_order['billing_phone'];
								$billing_address_1 = $get_order['billing_address_1'];
								$billing_postcode = $get_order['billing_postcode'];
								$billing_city = $get_order['billing_city'];
								$billing_state = $get_order['billing_state'];
								$billing_country = $get_order['billing_country'];
								$product_id = $get_order['product_id'];
								$variation_id = $get_order['variation_id'];

								$paid_date = $get_order['paid_date'];
								$status = $get_order['status'];
								$order_total = $get_order['order_total'];
								$order_currency = $get_order['order_currency'];
								$payment_method = $get_order['payment_method'];
								$shipping_method = $get_order['shipping_method'];
								$customer_email = $get_order['customer_email'];
								$affiliate_user_id = $get_order['affilate_user_id'];
								$time = new DateTime($order_date);
								$date = $time->format("F d, Y");
								$time = $time->format('h:i A');
								$csv_paid_date = new DateTime($paid_date);
								$final_paid_date = $csv_paid_date->format("d-m-Y h:i:s");

								//echo $final_paid_date . '<br>';

								$post_title = 'Order &ndash; ' . $date . '@ ' . $time . '';
								if ($variation_id == '0') {

									$product_name = get_the_title($product_id);
									$product_id_csv =  $product_id;
								} else {
									$product_name = get_the_title($variation_id);
									$product_id_csv = $variation_id;
								}

								/* echo $customer_email . '<br>'; */
								if (email_exists($customer_email)) {
									/* exit; */
								} else {
									$random_password = wp_generate_password();


									$user_id = wc_create_new_customer($customer_email, $billing_first_name . rand(10, 100), $random_password);


									$order_id = wp_insert_post(array(
										'post_type' => 'shop_order',
										'post_title' => $post_title,
										'post_status'    => $status,
										'meta_input' => array(
											/* '_csv_order_id' => $order_csv_id, */
											'_billing_email' => $customer_email,
											'_billing_first_name' => $billing_first_name,
											'_billing_last_name' => $billing_last_name,
											'_billing_address_1' => $billing_address_1,
											'_billing_city' => $billing_city,
											'_shipping_first_name' => $billing_first_name,
											'_shipping_last_name' => $billing_last_name,
											'_shipping_address_1' => $billing_address_1,
											'_shipping_city' => $billing_city,
											'_billing_postcode' => $billing_postcode,
											'_payment_method' => $payment_method,
											'_billing_state' => $billing_state,
											'_billing_country' => $billing_country,
											'_shipping_state' => $billing_state,
											'_shipping_country' => $billing_country,
											'_order_currency' => $order_currency,
											'_order_total' => $order_total,
											'_billing_phone' => $billing_phone,
											'_paid_date' => $final_paid_date,
											'dong_affid' => $affiliate_user_id
										),
									));

									/* distrubution function  */
									$price = $order_total;
									$proId = $product_id_csv;
									$oid = $order_id;
									$cid = $user_id;

									dongtrader_product_price_distribution($price, $proId, $oid, $cid);
									// create an order item first
									$order_item_id = wc_add_order_item(
										$order_id,
										array(
											'order_item_name' => $product_name, // may differ from the product name
											'order_item_type' => 'line_item', // product
										)
									);
									if ($order_item_id) {
										// provide its meta information
										wc_add_order_item_meta($order_item_id, '_qty', 1, true); // quantity

										wc_add_order_item_meta($order_item_id, '_product_id', $product_id, true); // ID of the product
										wc_add_order_item_meta($order_item_id, '_variation_id', $variation_id, true); // ID of the product
										// you can also add "_variation_id" meta
										wc_add_order_item_meta($order_item_id, '_line_subtotal', $order_total, true); // price per item
										wc_add_order_item_meta($order_item_id, '_line_total', $order_total, true); // total price
									}

									$email = $customer_email;
									$address = array(
										'first_name' => $billing_first_name,
										'last_name'  => $billing_last_name,
										'email'      => $email,
										'phone'      => $billing_phone,
										'address_1'  => $billing_address_1,
										'city'       => $billing_city,
										'state'      => $billing_state,
										'postcode'   => $billing_postcode,
										'country'    => $billing_country,
										//'affiliate_user' => $affiliate_user
									);

									update_post_meta($order_id, '_customer_user', $user_id);


									update_user_meta($user_id, "billing_first_name", $address['first_name']);
									update_user_meta($user_id, "billing_last_name", $address['last_name']);
									update_user_meta($user_id, "billing_email", $address['email']);
									update_user_meta($user_id, "billing_address_1", $address['address_1']);
									update_user_meta($user_id, "billing_city", $address['city']);
									update_user_meta($user_id, "billing_postcode", $address['postcode']);
									update_user_meta($user_id, "billing_country", $address['country']);
									update_user_meta($user_id, "billing_state", $address['state']);
									update_user_meta($user_id, "billing_phone", $address['phone']);
									update_user_meta($user_id, "shipping_first_name", $address['first_name']);
									update_user_meta($user_id, "shipping_last_name", $address['last_name']);
									update_user_meta($user_id, "shipping_address_1", $address['address_1']);
									update_user_meta($user_id, "shipping_city", $address['city']);
									update_user_meta($user_id, "shipping_postcode", $address['postcode']);
									update_user_meta($user_id, "shipping_country", $address['country']);
									update_user_meta($user_id, "shipping_state", $address['state']);
									//update_user_meta($user_id, "dong_affid", $address['affiliate_user']);
								}
							}
						}
						echo '
						 <div class="success-box">
                        Order Imported
                    </div>';
					}

					?>




				</div>
			</div>

			<div class="footer-wrap">
				<div class="row">
					<div class="creator col-md-3">
						<span>Proudly Created by</span>
						<a href="codepixelzmedia.com"><img src="<?php echo plugin_dir_url(__FILE__) . '../img/cpm-logo.png'; ?>" alt="cpm-logo" class="cpm-logo"></a>
					</div>

					<div class="col-md-6">
					</div>

					<div class="copyright col-md-3">
						<span>All rights reserved</span>
						&copy; <?php echo date("Y"); ?>
					</div>
				</div>
			</div>

		</div>
		<div class="clear"></div>