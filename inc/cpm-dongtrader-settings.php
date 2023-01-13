<?php
if (!defined('ABSPATH')) exit;


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

			<ul class="tab-menu">
				<li class="nav-tab"><a href="#qr-code-generator" class="dashicons-before dashicons-editor-alignleft"><?php _e('QR Code', 'dongtraders'); ?></a></li>
				<li class="nav-tab"><a href="#api-integration" class="dashicons-before dashicons-admin-generic"><?php _e('Integration API', 'dongtraders'); ?></a></li>
				<li class="nav-tab"><a href="#advanced" class="dashicons-before dashicons-admin-settings"><?php _e('Advanced', 'dongtraders'); ?></a></li>
				<li class="nav-tab"><a href="#extra" class="dashicons-before dashicons-admin-tools"><?php _e('Extras', 'dongtraders'); ?></a></li>
			</ul>

			<div class="tab-content">
				<div id="qr-code-generator">
					<h2 class="tab-title">General Title</h2>
					<p>This is a ramdom settings form.Lorem ipsum dolor sit amet, consectetur adipisicing elit. Debitis rem facere doloribus delectus, reiciendis ipsum itaque placeat, eveniet perferendis vel molestiae impedit magnam. Sapiente, distinctio. Laboriosam accusamus odio architecto minus.</p>

				</div>

				<div id="api-integration">
					<h2 class="tab-title">Set Your API keys</h2>
					<p>This is for tab for options. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Qui voluptates odit distinctio perferendis porro aliquam beatae iure laudantium veniam voluptas vero similique ratione mollitia, rerum inventore saepe impedit eveniet necessitatibus!Lorem ipsum dolor sit amet, </p>
					<hr>

					<?php

					settings_errors();

					settings_fields('dongtraders_api_setting_page');

					$dongtraders_api_setting_data = get_option('dongtraders_api_settings_fields');
					?>
					<form action="options.php" method="POST" enctype="multipart/form-data">
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

						<div class="form-group">
							<input type="submit" class="cpm-btn submit" name="submit" value="Save changes">
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