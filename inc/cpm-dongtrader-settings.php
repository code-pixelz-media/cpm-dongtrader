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

				<li class="nav-tab" id="third"><a href="#api-integration" class="dashicons-before dashicons-admin-generic"><?php _e('Integration API', 'dongtraders'); ?></a></li>
				<li class="nav-tab" id="fourth"><a href="#advanced" class="dashicons-before dashicons-admin-settings"><?php _e('Advanced', 'dongtraders'); ?></a></li>
				<li class="nav-tab" id="fifth"><a href="#extra" class="dashicons-before dashicons-admin-tools"><?php _e('Extras', 'dongtraders'); ?></a></li>
			</ul>

			<div class="tab-content">
				<div id="qr-code-generator">

					<?php if ($dong_qr_array) : ?>
						<h2 class="tab-title"><?php _e('Generated QR Codes', 'cpm-dongtrader') ?></h2>
						<?php

						foreach ($dong_qr_array as $key => $value) :

						?>
							<div class="qr-pic">
								<img width=" 200" height="200" src="<?= $value['qr_image_url']; ?>">
								<div class="copy" data-url="<?= $value['qr_image_url']; ?>">
									<a href="#">
										<i class="fa-solid fa-copy"></i>
									</a>
								</div>
								<div class="delete" data-index="<?= $key ?>" data-qrid="<?= $value['qr_id'] ?>">
									<a href="#">
										<i class="fa-solid fa-trash"></i>
									</a>
								</div>
							</div>
					<?php endforeach;
					endif; ?>
					<h2 class="tab-title"><?php _e('Generate New QR Code ', 'cpm-dongtrader') ?></h2>
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
							<input type="submit" class="cpm-btn submit qrtiger-form-submit" value="<?php _e('Generate', 'cpm-dongtrader') ?>">
							<span class="form-loader" style="display: none;"><img src="<?php echo plugins_url() . '/cpm-dongtrader/img/loader.gif' ?>"></span>
						</div>
					</form>
				</div>
				<div id="api-integration">
					<h2 class="tab-title">Set Your API keys</h2>
					<p>This is for tab for options. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Qui voluptates odit distinctio perferendis porro aliquam beatae iure laudantium veniam voluptas vero similique ratione mollitia, rerum inventore saepe impedit eveniet necessitatibus!Lorem ipsum dolor sit amet, </p>
					<hr>

					<?php $dongtraders_api_setting_data = get_option('dongtraders_api_settings_fields'); ?>
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
				<div id="advanced">
					<h2 class="tab-title">Advanced Title</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, rem dolores earum temporibus nihil consequatur vero perspiciatis accusantium architecto, impedit, aliquam natus. Sequi ad harum tempore, fugit iusto illo nihil.Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, rem dolores earum temporibus nihil consequatur vero perspiciatis accusantium architecto, impedit, aliquam natus. Sequi ad harum tempore, fugit iusto illo nihil</p>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, rem dolores earum temporibus nihil consequatur vero perspiciatis accusantium architecto, impedit, aliquam natus. Sequi ad harum tempore, fugit iusto illo nihil.Lorem ipsum dolor sit amet.</p>
				</div>

				<div id="extra">
					<h2 class="tab-title">Extras Title</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, rem dolores earum temporibus nihil consequatur vero perspiciatis accusantium architecto, impedit, aliquam natus. Sequi ad harum tempore, fugit iusto illo nihil.Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, rem dolores earum temporibus nihil consequatur vero perspiciatis accusantium architecto, impedit, aliquam natus. Sequi ad harum tempore, fugit iusto illo nihil</p>
					<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, rem dolores earum temporibus nihil consequatur vero perspiciatis accusantium architecto, impedit, aliquam natus. Sequi ad harum tempore, fugit iusto illo nihil.Lorem ipsum dolor sit amet.</p>
				</div>
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