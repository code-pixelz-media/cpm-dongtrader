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
					<h1><?php _e('Detente 2.0 Settings', 'dongtraders'); ?></h1>
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
				<li class="nav-tab" id="seven"><a href="#export-order-list" class="dashicons-before dashicons-admin-tools"><?php _e('Export Order List', 'dongtraders'); ?></a></li>

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
					<?php
					$dongtraders_api_setting_data = get_option('dongtraders_api_settings_fields');
					?>
					<form action="options.php" method="POST" enctype="multipart/form-data" id="save-settings">
						<?php
						settings_errors();

						settings_fields('dongtraders_api_setting_page');
						

						?>
					
						<h3>Change Welcome Email Text</h3>
						<div class="form-group">
							<label for="">Change Welcome Email Text</label>
							<div class="form-control-wrap">
								
								<?php 
									$content = $dongtraders_api_setting_data['dong_change_welcome_text'];
									$custom_editor_id = "dongchange_welcome_email";
									$custom_editor_name = "dongtraders_api_settings_fields[dong_change_welcome_text]";
									$args = array(
											'media_buttons' => false, // This setting removes the media button.
											'textarea_name' => $custom_editor_name, // Set custom name.
											'textarea_rows' => get_option('default_post_edit_rows', 10), //Determine the number of rows.
											'quicktags' => true, // Remove view as HTML button.
										);
									wp_editor( $content, $custom_editor_id, $args );
								?>
							</div>
						</div>
						<hr>
						<h3><?php _e('Currency Conversion','cpm-dongtrader') ?></h3>
                        <div class="form-group">
							
                            <label for=""><?php _e('Enable','cpm-dongtrader') ?></label>
                            <div class="form-control-wrap">
                                <div class="checkbox-wrap custom">
								  	<input type="checkbox" id="dong_currency_check" name="dongtraders_api_settings_fields[dong_enable_currency]" 
									value="<?php echo esc_attr('on'); ?>"
									<?php if (isset($dongtraders_api_setting_data['dong_enable_currency'])) { echo checked($dongtraders_api_setting_data['dong_enable_currency'], 'on', false);}?>>
                                    <span class="custom-checkbox"></span>
                                </div>
                            </div>
                        </div>
						
						<div class="currency-convert">
							<label for=""><?php _e('1 Dollar is equal to (x) V.N.D ','cpm-dongtrader') ?></label>
							<div class="form-group">
								<div class="form-control-wrap">
									<input class="form-control" name="dongtraders_api_settings_fields[vnd_rate]" type="text" placeholder="1 dollar equals to (x) vnd"
									 onfocus="this.placeholder=''" onblur="this.placeholder='1 dollar equals to (x) vnd'"
									  value="<?php echo esc_html($dongtraders_api_setting_data['vnd_rate']); ?>" >
								</div>
							</div>
						</div>
						<hr>
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
												<tr id="<?php echo 'tr-index-'.$key?>">
													<td><?= $i ?></td>
													<td><?php echo $value['qr_id'] ?></td>
													<td><img src="<?php echo $value['qr_image_url']; ?>" width="50" height="50"></td>
													<td><button class="cpm-btn url-copy" data-url="<?= $value['qr_image_url']; ?>">Copy</button></td>
													<td><button id="<?php echo 'delete-item-'.$key ?>"class="cpm-btn qr-delete" data-index="<?= $key ?>">Delete</button></td>
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
					<form action="#" method="post" enctype="multipart/form-data" onSubmit="window.location.reload()">
						<div class=" form-group">
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
					dongtraders_csv_order_importer();
					?>

				</div>
				<!-- order export List -->

				<div id="export-order-list">
					<h2 class="tab-title">Export Order List</h2>
					<?php
					dongtraders_custom_order_created_list();
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