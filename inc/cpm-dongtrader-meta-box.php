<?php

/* generate product qr iamge url without direct check out */
if (!function_exists('cpm_dongtraders_product_qr_generator_fields')) {
	function cpm_dongtraders_product_qr_generator_fields()
	{
		add_meta_box('dongtraders_qr_generator_meta_fields', 'Product QR code [URL]', 'dongtraders_qr_generator_field_callback', 'product', 'side', 'default');
	}
	add_action('add_meta_boxes', 'cpm_dongtraders_product_qr_generator_fields');
}


/* Callback function for product url  */
if (!function_exists('dongtraders_qr_generator_field_callback')) {
	function dongtraders_qr_generator_field_callback($post)
	{
		$get_product_qr_image = get_post_meta($post->ID, '_product_qr_code', true);
		if (!empty($get_product_qr_image)) {
			echo '<img src="' . $get_product_qr_image . '" alt="" width="200" height="200">';
			echo '<p class="generate_product_url">' . $get_product_qr_image . '</p>';

?>
			<button data-url="<?php echo $get_product_qr_image ?> " class="button button-primary button-large url-copy generate_product">Copy QR URL</button>

		<?php
		}
		if (empty($get_product_qr_image)) {
			$loader_img = plugins_url() . '/cpm-dongtrader/img/loader.gif';

			echo '
		<input type="hidden" value="500" name="qr-size" class="qr-size-product widefat">
		<input type="hidden" value="' . get_permalink($post->ID) . '" name="qr-product-url" class="qr-url-product widefat">
		<input type="hidden" value="' . $post->ID . '" name="qr-product-id" class="qr-url-product-id widefat">
		<input type="hidden" value="' . get_the_title($post->ID) . '" name="qr-product-title" class="qr-url-product-title widefat">
		<button class="cpm-dongtraders-product-qr button button-primary button-large">Generate Product QR</button>
		<span class="product-form-loader" style="display: none;"><img src="' . $loader_img . '"></span>
		<div class="generate_qr_success"></div>
		';

		?>
			<script>
				jQuery(document).ready(function() {
					jQuery('.cpm-dongtraders-product-qr').on('click', function(dt) {
						dt.preventDefault();
						jQuery('.product-form-loader').css('display', '');
						//console.log('On click generate');
						var qRsize = jQuery(".qr-size-product").val(),
							qRurl = jQuery(".qr-url-product").val(),
							qRpid = jQuery(".qr-url-product-id").val(),
							qRtext = jQuery(".qr-url-product-title").val(),
							ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

						jQuery.ajax({
							type: "POST",
							url: ajaxUrl,
							data: {

								action: 'cpm_dongtraders_product_qr_generator_ajax',
								qrsize: qRsize,
								qrurl: qRurl,
								qrpid: qRpid,
								qrtext: qRtext,

							},
							dataType: "html",
							success: function(data) {
								if (data == "") {
									jQuery('.generate_qr_success').html();
									return;
								} else {
									jQuery('.product-form-loader').css('display', 'none');
									jQuery(".product_success").fadeOut(2000, "swing");
									jQuery('.cpm-dongtraders-product-qr').css('display', 'none');
									jQuery('.generate_qr_success').html(data);

								}
							}
						});


					});
				});
			</script>

		<?php
		}
	}
}

/* A callback function for the ajax request. */
if (!function_exists('cpm_dongtraders_product_qr_generator_ajax')) {
	function cpm_dongtraders_product_qr_generator_ajax()
	{
		$qr_size =  sanitize_text_field($_POST['qrsize']);
		$qr_url  =  sanitize_url($_POST['qrurl']);
		$qr_text =  sanitize_text_field($_POST['qrtext']);
		$qr_pid =  sanitize_text_field($_POST['qrpid']);

		$response = !empty($qr_size) && !empty($qr_url) && !empty(trim($qr_text)) ? true : false;
		$notify_to_js = array(
			'dataStatus' => $response,
			'apistatus' => false
		);

		if ($response) {
			$qrtiger_array = [
				"qr" => [
					"size" => $qr_size,
					"text" => $qr_text
				],
				"qrUrl" => $qr_url,
				"qrType" => "qr2",
				"qrCategory" => "url"
			];
			$qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

			if ($qrtiger_api_call) {
				$product_qr_image = $qrtiger_api_call->data->qrImage;

				update_post_meta($qr_pid, '_product_qr_code', $product_qr_image);

				echo '<p class="product_success">Product QR Generated Successfully</p>';
				echo '<img src="' . $product_qr_image . '" alt="" width="200" height="200">';
				echo '<p class="generate_product_dcheckout_url">' . $product_qr_image . '</p>';
			}
		}

		wp_die();
	}
	add_action('wp_ajax_cpm_dongtraders_product_qr_generator_ajax', 'cpm_dongtraders_product_qr_generator_ajax');
}



/* generate product qr image url For direct check out link */



if (!function_exists('cpm_dongtraders_product_qr_generator_direct_checkout_fields')) {
	function cpm_dongtraders_product_qr_generator_direct_checkout_fields()
	{

		add_meta_box('dongtraders_qr_generator_direct_checkout_meta_fields', 'Product QR code [Direct Checkout]', 'dongtraders_qr_generator_field_direct_checkout_callback', 'product', 'side', 'default');
	}
	add_action('add_meta_boxes', 'cpm_dongtraders_product_qr_generator_direct_checkout_fields');
}

/* Metabox callback functions for qr generator fro direct checkout   */
if (!function_exists('dongtraders_qr_generator_field_direct_checkout_callback')) {
	function dongtraders_qr_generator_field_direct_checkout_callback($post)
	{
		$get_product_qr_image = get_post_meta($post->ID, '_product_direct_checkout_qr_code', true);
		if (!empty($get_product_qr_image)) {
			echo '<img src="' . $get_product_qr_image . '" alt="" width="200" height="200">';
			echo '<p class="generate_product_dcheckout_url">' . $get_product_qr_image . '</p>';

		?>
			<button data-url="<?php echo $get_product_qr_image ?>" class="url-copy button button-primary button-large generate_product">Copy QR URL</button>

		<?php
		}
		if (empty($get_product_qr_image)) {
			$loader_img = plugins_url() . '/cpm-dongtrader/img/loader.gif';

			echo '
		<input type="hidden" value="500" name="qr-size" class="qr-size-product-dcheckout widefat">
		<input type="hidden" value="' . get_permalink($post->ID) . '?add=1' . '" name="qr-product-url" class="qr-url-product-dcheckout widefat">
		<input type="hidden" value="' . $post->ID . '" name="qr-product-id" class="qr-url-product-dcheckout-id widefat">
		<input type="hidden" value="' . get_the_title($post->ID) . '" name="qr-product-title" class="qr-url-product-dcheckout-title widefat">
		<button class="cpm-dongtraders-product-qr-direct-checkout button button-primary button-large">Generate Product QR</button>
		<span class="product-form-loader-dcheckout" style="display: none;"><img src="' . $loader_img . '"></span>
		<div class="generate_qr_success-dcheckout"></div>
		';

		?>
			<script>
				jQuery(document).ready(function() {
					jQuery('.cpm-dongtraders-product-qr-direct-checkout').on('click', function(dt) {
						dt.preventDefault();
						jQuery('.product-form-loader-dcheckout').css('display', '');
						//console.log('On click generate');
						var qRsize = jQuery(".qr-size-product-dcheckout").val(),
							qRurl = jQuery(".qr-url-product-dcheckout").val(),
							qRpid = jQuery(".qr-url-product-dcheckout-id").val(),
							qRtext = jQuery(".qr-url-product-dcheckout-title").val(),
							ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

						jQuery.ajax({
							type: "POST",
							url: ajaxUrl,
							data: {

								action: 'cpm_dongtraders_product_qr_generator_dcheckout_ajax',
								qrsize: qRsize,
								qrurl: qRurl,
								qrpid: qRpid,
								qrtext: qRtext,

							},
							dataType: "html",
							success: function(data) {
								if (data == "") {
									jQuery('.generate_qr_success-dcheckout').html();
									return;
								} else {
									jQuery('.product-form-loader-dcheckout').css('display', 'none');
									jQuery(".product_dcheckout_success").fadeOut(2000, "swing");
									jQuery('.cpm-dongtraders-product-qr').css('display', 'none');
									jQuery('.generate_qr_success-dcheckout').html(data);

								}
							}
						});


					});
				});
			</script>

		<?php
		}
	}
}



/* A callback function for the ajax request. */
if (!function_exists('cpm_dongtraders_product_qr_generator_dcheckout_ajax')) {
	function cpm_dongtraders_product_qr_generator_dcheckout_ajax()
	{
		$qr_size =  sanitize_text_field($_POST['qrsize']);
		$qr_url  =  sanitize_url($_POST['qrurl']);
		$qr_text =  sanitize_text_field($_POST['qrtext']);
		$qr_pid =  sanitize_text_field($_POST['qrpid']);

		$response = !empty($qr_size) && !empty($qr_url) && !empty(trim($qr_text)) ? true : false;
		$notify_to_js = array(
			'dataStatus' => $response,
			'apistatus' => false
		);

		if ($response) {
			$qrtiger_array = [
				"qr" => [
					"size" => $qr_size,
					"text" => $qr_text
				],
				"qrUrl" => $qr_url,
				"qrType" => "qr2",
				"qrCategory" => "url"
			];
			$qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

			if ($qrtiger_api_call) {
				$product_qr_image = $qrtiger_api_call->data->qrImage;

				update_post_meta($qr_pid, '_product_direct_checkout_qr_code', $product_qr_image);

				echo '<p class="product_dcheckout_success">Product QR Generated Successfully</p>';
				echo '<img src="' . $product_qr_image . '" alt="" width="200" height="200">';
				echo '<p class="generate_product_url">' . $product_qr_image . '</p>';
			}
		}

		wp_die();
	}
	add_action('wp_ajax_cpm_dongtraders_product_qr_generator_dcheckout_ajax', 'cpm_dongtraders_product_qr_generator_dcheckout_ajax');
}


/* qr generator for varition product */

if (!function_exists('cpm_dongtraders_product_qr_generator_varition_product_fields')) {
	function cpm_dongtraders_product_qr_generator_varition_product_fields()
	{

		add_meta_box('dongtraders_qr_generator_varition_product_meta_fields', 'Variation Product QR code', 'dongtraders_qr_generator_field_variation_product_callback', 'product', 'side', 'default');
	}
	add_action('add_meta_boxes', 'cpm_dongtraders_product_qr_generator_varition_product_fields');
}

/* Metabox callback functions for qr generator fro direct checkout   */
if (!function_exists('dongtraders_qr_generator_field_variation_product_callback')) {
	function dongtraders_qr_generator_field_variation_product_callback($post)
	{
		$show = false;
		$product = wc_get_product($post->ID);
		$variations_check = get_post_meta($post->ID, 'variation_data_checks', true);
		$variations = $product->get_available_variations();
		$variations_id = wp_list_pluck($variations, 'variation_id');

		// echo '<img src="https://www.facebook.com" alt="" width="200" height="200">';

		if ($variations_check == 'ok') {
			foreach ($variations_id as $key => $v_id) {

				$meta = get_post_meta($v_id, '_variation_qr_datas', true);
				if (empty($meta)) continue;

				echo '<div id="' . $key . '" class="qr-component">
				<img src="' . $meta['qr_image_url'] . '" alt="" width="200" height="200">
				<button data-url= "' . $meta['qr_image_url'] . '" class="button button-primary button-large url-copy" >Copy QR URL</button></div>';
			}
		} else {
			$loader_img = plugins_url() . '/cpm-dongtrader/img/loader.gif';
			$all_elms = array();
			foreach ($variations_id as $key => $v_id) {
				# code...
				$get_url = get_permalink($v_id);
				$query = parse_url($get_url, PHP_URL_QUERY);
				parse_str($query, $queryArray);
				$get_color = $queryArray['attribute_color'];
				array_push(
					$all_elms,
					array(
						"size" => "500",
						"url" => esc_url($get_url),
						"qrPid" => $v_id,
						"color" => $get_color,
						"productTitle" => get_the_title($v_id),
						"yamID" => $post->ID,
					)
				);
			}

			$items = wp_json_encode($all_elms);
			echo '<div class="test-var">
		<button  ajax_url = "' . admin_url('admin-ajax.php') . '" data-items="' . esc_attr($items) . '" class="cpm-dongtraders-product-qr-variable button button-primary button-large">Generate Variable Product QR</button>
		<span class="product-form-loader-variation_product" style="display: none;"><img src="' . $loader_img . '"></span>
		<div class="generate_qr_success-variation_product"></div>
		</div>
		';
		}


		?>
		<script>
			jQuery(document).ready(function() {
				jQuery('.cpm-dongtraders-product-qr-variable').on('click', function(dt) {
					dt.preventDefault();
					var loader = $('.product-form-loader-variation_product'),
						mainContainer = $('.test-var'),
						all_data = $(this).attr('data-items'),
						variable_data = JSON.parse(all_data),
						ajaxUrl = $(this).attr('ajax_url');
					loader.show();
					jQuery.ajax({
						type: "POST",
						url: ajaxUrl,
						dataType: "JSON",
						data: {
							action: 'cpm_dongtraders_product_qr_generator_variation_product_ajax',
							varaibleProducts: variable_data
						},
						success: function(responseObj) {
							console.log(responseObj);
							mainContainer.empty();
							mainContainer.append(responseObj.html);
							loader.hide();
						}
					});


				});
			});
		</script>

<?php
	}



	function dongtrader_get_rgb_color($color)
	{
		switch ($color) {
			case 'Orange':
				$color = 'rgb(255, 51, 0)';
				break;
			case 'Purple':
				$color = 'rgb(245,66,245)';
				break;
			case 'Red':
				$color = 'rgb(255, 0, 0)';
				break;

			case 'Blue':
				$color = 'rgb(36, 36, 143)';
				break;

			case 'Green':
				$color = 'rgb(0, 204, 0)';
				break;

			default:
				$color = 'rgb(38, 38, 38)';
		}

		return $color;
	}


	/* A callback function for the ajax request. */
	if (!function_exists('cpm_dongtraders_product_qr_generator_variation_product_ajax')) {
		function cpm_dongtraders_product_qr_generator_variation_product_ajax()
		{
			$json_obj_data = $_POST['varaibleProducts'];

			//$html = '<div class="qr-component">';
			$check = [];
			$s = 1;
			foreach ($json_obj_data as $vad) {
				extract($vad);
				$qrtiger_array = [
					"qr" => [
						"size" => $size,
						"text" => 'text',
						"colorDark" => dongtrader_get_rgb_color($color)
					],
					"qrUrl" => $url,
					"qrType" => "qr2",
					"qrCategory" => "url"
				];

				$qrtiger_api_call =  qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

				if ($qrtiger_api_call) {

					$current_dong_qr_array = array(
						'qr_image_url'  => $qrtiger_api_call->data->qrImage,
						'created_at'    => $qrtiger_api_call->data->createdAt,
						'updated_at'    => $qrtiger_api_call->data->updatedAt,
						'qr_id'         => $qrtiger_api_call->data->qrId,
					);
					// $current_dong_qr_array = array(
					// 	'qr_image_url'  => 'https://qrtiger.com/qr/OXX3.png',
					// 	'created_at'    => 'date',
					// 	'updated_at'    => 'date',
					// 	'qr_id'         => $i,
					// );
					update_post_meta($qrPid, '_variation_qr_datas', $current_dong_qr_array);
					update_post_meta($productId, 'variation_data_checks', 'ok');
					$html .= '<div id="' . $qrtiger_api_call->data->qrId . '" class="qr-component">
					<img src="' . $qrtiger_api_call->data->qrImage . '" alt="" width="200" height="200">
					<button data-url= "' . $qrtiger_api_call->data->qrImage . '" class="button button-primary button-large url-copy" >Copy QR URL</button></div>';
					// 	$html .= '<div id="' . $s . '" class="qr-component">
					// <img src="https://qrtiger.com/qr/OXX3.png" alt="" width="200" height="200">
					// <button class="button button-primary button-large url-copy" data-url="https://www.facebook.com" >Copy QR URL</button></div>';
				} else {
					array_push(
						$check,
						array(
							array('updated' => false, 'variation_id' => $qr_pid)
						)
					);
					$response['failedfor'][] = $qr_pid;
					$html .= '<div id="' . $s . '" class="qr-component">Error</div>';
					update_post_meta($qrPid, '_variation_qr_data', 'error');
				}

				$s++;
			}


			$response = array(
				'success' => true,
				'html' => $html,
				'productID' => $productId,
				'check' => $check
			);
			$json = wp_json_encode($response);
			echo $json;
			exit;
		}
		add_action('wp_ajax_cpm_dongtraders_product_qr_generator_variation_product_ajax', 'cpm_dongtraders_product_qr_generator_variation_product_ajax');
	}
}
