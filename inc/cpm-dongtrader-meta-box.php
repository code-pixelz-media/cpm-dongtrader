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
			<button class="generate_product" onclick="dong_traders_url_copy('.generate_product_url')">Copy QR URL</button>

			<script>
				/* js for copy paste qr generated url */

				jQuery('.generate_product').on('click', function(dt) {
					dt.preventDefault();

				});

				function dong_traders_url_copy(element) {
					var $temp = jQuery("<input>");
					jQuery("body").append($temp);
					$temp.val(jQuery(element).text()).select();
					document.execCommand("copy");
					$temp.remove();
				}
			</script>

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
			<button class="generate_product" onclick="dong_traders_url_copy('.generate_product_dcheckout_url')">Copy QR URL</button>

			<script>
				/* js for copy paste qr generated url */

				jQuery('.generate_product').on('click', function(dt) {
					dt.preventDefault();

				});

				function dong_traders_url_copy(element) {
					var $temp = jQuery("<input>");
					jQuery("body").append($temp);
					$temp.val(jQuery(element).text()).select();
					document.execCommand("copy");
					$temp.remove();
				}
			</script>

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

		add_meta_box('dongtraders_qr_generator_varition_product_meta_fields', 'Variation Product QR code [Direct Checkout]', 'dongtraders_qr_generator_field_variation_product_callback', 'product', 'side', 'default');
	}
	add_action('add_meta_boxes', 'cpm_dongtraders_product_qr_generator_varition_product_fields');
}

/* Metabox callback functions for qr generator fro direct checkout   */
if (!function_exists('dongtraders_qr_generator_field_variation_product_callback')) {
	function dongtraders_qr_generator_field_variation_product_callback($post)
	{
		$get_product_qr_image = get_post_meta($post->ID, '_product_variation_qr_code', true);
		if (!empty($get_product_qr_image)) {
			echo '<img src="' . $get_product_qr_image . '" alt="" width="200" height="200">';
			echo '<p class="generate_product_dcheckout_url">' . $get_product_qr_image . '</p>';

		?>
			<button class="generate_product" onclick="dong_traders_url_copy('.generate_product_dcheckout_url')">Copy QR URL</button>

			<script>
				/* js for copy paste qr generated url */

				jQuery('.generate_product').on('click', function(dt) {
					dt.preventDefault();

				});

				function dong_traders_url_copy(element) {
					var $temp = jQuery("<input>");
					jQuery("body").append($temp);
					$temp.val(jQuery(element).text()).select();
					document.execCommand("copy");
					$temp.remove();
				}
			</script>

		<?php
		}
		if (empty($get_product_qr_image)) {
			$loader_img = plugins_url() . '/cpm-dongtrader/img/loader.gif';
			$product = wc_get_product($post->ID);
			$variations = $product->get_available_variations();
			$variations_id = wp_list_pluck($variations, 'variation_id');
			// 	echo '
			// <input type="hidden" value="500" name="qr-size" class="qr-size-product-dcheckout widefat">

			// <input type="hidden" value="' . get_permalink($post->ID) . '?add=1' . '" name="qr-product-url" class="qr-url-product-dcheckout widefat">
			// <input type="hidden" value="' . $post->ID . '" name="qr-product-id" class="qr-url-product-dcheckout-id widefat">
			// <input type="hidden" value="' . get_the_title($post->ID) . '" name="qr-product-title" class="qr-url-product-dcheckout-title widefat">
			// <button class="cpm-dongtraders-product-qr-direct-checkout button button-primary button-large">Generate Product QR</button>
			// <span class="product-form-loader-dcheckout" style="display: none;"><img src="' . $loader_img . '"></span>
			// <div class="generate_qr_success-dcheckout"></div>
			// ';

			foreach ($variations_id as $key => $v_id) {
				# code...
				$get_url = get_permalink($v_id);
				$query = parse_url($get_url, PHP_URL_QUERY);
				parse_str($query, $queryArray);
				$get_color = $queryArray['attribute_color'];
				// echo $get_url . '<br>';
				// echo $get_color . '<br>';
				echo '
				<input type="hidden" value="500" name="qr-size[]" class="qr-size-product-dcheckout widefat">
				<input type="hidden" value="' . $get_url . '" name="qr-product-url[]" class="qr-url-product-variation_product widefat">
				<input type="hidden" value="' . $v_id . '" name="qr-product-id[]" class="qr-url-product-variation_product-id widefat">
				<input type="hidden" value="' . get_the_title($v_id) . '" name="qr-product-title[]" class="qr-url-product-variation_product-title widefat">
				<input type="hidden" value="' .  $get_color . '" name="qr-product-color[]" class="qr-url-product-variation_product-color widefat">
				
				';
			}
			echo '<button class="cpm-dongtraders-product-qr-direct-checkout button button-primary button-large">Generate Product QR</button>
				<span class="product-form-loader-variation_product" style="display: none;"><img src="' . $loader_img . '"></span>
				<div class="generate_qr_success-variation_product"></div>';

		?>
			<script>
				jQuery(document).ready(function() {
					jQuery('.cpm-dongtraders-product-qr-direct-checkout').on('click', function(dt) {
						dt.preventDefault();
						jQuery('.product-form-loader-variation_product').css('display', '');
						//console.log('On click generate');
						// var qRsize = jQuery(".qr-size-product-variation_product").val(),
						// 	qRurl = jQuery(".qr-url-product-variation_product").val(),
						// 	qRpid = jQuery(".qr-url-product-variation_product-id").val(),
						// 	qRtext = jQuery(".qr-url-product-variation_product-title").val(),
						// 	qRcolor = jQuery(".qr-url-product-variation_product-color").val(),
						// ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
						var qRsize = $("input[name='qr-size[]']").map(function() {
							return $(this).val();
						}).get();
						var qRurl = $("input[name='qr-product-url[]']").map(function() {
							return $(this).val();
						}).get();
						var qRpid = $("input[name='qr-product-id[]']").map(function() {
							return $(this).val();
						}).get();
						var qRtext = $("input[name='qr-product-title[]']").map(function() {
							return $(this).val();
						}).get();
						var qRcolor = $("input[name='qr-product-color[]']").map(function() {
							return $(this).val();
						}).get();

						var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

						var combine_all = [];

						for (var i = 0, len = qRsize.length; i < len; i++) {
							// combine_all[combine_all.length] = qRsize[i];
							// combine_all[combine_all.length] = qRurl[i];
							// combine_all[combine_all.length] = qRpid[i];
							var combine_all = [{
									"qr-size": qRsize[i],
									"qr-url": qRurl[i],
									"qp-pid": qRpid[i],
									"qp-text": qRtext[i],
									"qp-color": qRcolor[i]
								},

							];

						}

						//console.log(combine_all);


						// var myobj = [];

						// jQuery.each(myArray, function(i, value) {
						// 	myobj.push({
						// 		firstName: value.firstName,
						// 		lastName: value.lastName
						// 	});
						// });

						// console.log(myobj);
						// console.log(qRpid);
						// console.log(qRtext);
						// console.log(qRcolor);
						// console.log(qRsize);

						jQuery.ajax({
							type: "POST",
							url: ajaxUrl,
							data: {

								action: 'cpm_dongtraders_product_qr_generator_variation_product_ajax',
								setData: combine_all,
								// qrurl: qRurl,
								// qrpid: qRpid,
								// qrtext: qRtext,
								// qrcolor: qRcolor,

							},
							dataType: "html",
							success: function(data) {
								if (data == "") {
									jQuery('.generate_qr_success-variation_product').html();
									return;
								} else {
									jQuery('.product-form-loader-variation_product').css('display', 'none');
									jQuery(".product_variation_product_success").fadeOut(2000, "swing");
									jQuery('.cpm-dongtraders-product-qr').css('display', 'none');
									jQuery('.generate_qr_success-variation_product').html(data);

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
if (!function_exists('cpm_dongtraders_product_qr_generator_variation_product_ajax')) {
	function cpm_dongtraders_product_qr_generator_variation_product_ajax()
	{
		$qr_size =  sanitize_text_field($_POST['qrsize']);
		$qr_url  =  sanitize_url($_POST['qrurl']);
		$qr_text =  sanitize_text_field($_POST['qrtext']);
		$qr_pid =  sanitize_text_field($_POST['qrpid']);
		$qr_color =  sanitize_text_field($_POST['setData']);

		var_dump($qr_color);

		// $response = !empty($qr_size) && !empty($qr_url) && !empty(trim($qr_text)) ? true : false;
		// $notify_to_js = array(
		// 	'dataStatus' => $response,
		// 	'apistatus' => false
		// );

		// if ($response) {
		// 	$qrtiger_array = [
		// 		"qr" => [
		// 			"size" => $qr_size,
		// 			"text" => $qr_text
		// 		],
		// 		"qrUrl" => $qr_url,
		// 		"qrType" => "qr2",
		// 		"qrCategory" => "url"
		// 	];
		// 	$qrtiger_api_call = qrtiger_api_request('/api/campaign/', $qrtiger_array, 'POST');

		// 	if ($qrtiger_api_call) {
		// 		$product_qr_image = $qrtiger_api_call->data->qrImage;

		// 		update_post_meta($qr_pid, '_product_variation_qr_code', $product_qr_image);

		// 		echo '<p class="product_variation_product_success">Product QR Generated Successfully</p>';
		// 		echo '<img src="' . $product_qr_image . '" alt="" width="200" height="200">';
		// 		echo '<p class="generate_product_url">' . $product_qr_image . '</p>';
		// 	}
		// }

		//wp_die();
	}
	add_action('wp_ajax_cpm_dongtraders_product_qr_generator_variation_product_ajax', 'cpm_dongtraders_product_qr_generator_variation_product_ajax');
}
