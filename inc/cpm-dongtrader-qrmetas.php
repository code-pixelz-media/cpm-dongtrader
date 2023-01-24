<?php

class Car_Info_Meta_Box
{

	public $generators;

	public $type;

	public function __construct()
	{
		if (is_admin()) {
			add_action('load-post.php',     array($this, 'init_metabox'));
			add_action('load-post-new.php', array($this, 'init_metabox'));

			$this->generators = array(
				array(
					'slug' => '_product_qr_codes',
					'title' => __('Product QR Code', 'cpm-dongtrader'),
					'buttonClass' => 'generate-product-qr',
					'variable' => false,
					'callback' => 'render_metabox_product_qr_code',

				),
				array(
					'slug' => '_product-qr-direct-checkouts',
					'title' => __('Product QR Code (Direct Checkout)', 'cpm-dongtrader'),
					'buttonClass' => 'generate-product-qr-direct-checkout',
					'variable' => false,
					'callback' => 'render_metabox_direct_checkout'
				),
				array(
					'slug' => '_product-qr-variable',
					'title' => __('Variable Product ', 'cpm-dongtrader'),
					'buttonClass' => 'generate-product-variable-qr-code',
					'variable' => true,
					'callback' => 'render_metabox_product_variable_qr_code'
				),

			);
		}
	}



	public function init_metabox()
	{
		add_action('add_meta_boxes', array($this, 'add_metabox'));
		add_action('save_post',      array($this, 'save_metabox'), 10, 2);
	}

	public function add_metabox()
	{

		$req_fields = $this->filterArrayByKeys($this->generators, ['slug', 'title', 'callback']);
		foreach ($req_fields as $k => $g) {
			add_meta_box(
				$g['slug'],
				$g['title'],
				array($this, $g['callback']),
				'product',
				'side',
				'default'

			);
		}
	}

	public function render_generator_button_with_image($datas, $productNum)
	{

		if (!$datas['variable']) {
			echo '<div class= "qr-containers">';
			$qr_datas = get_post_meta($productNum, $datas['slug'], true);
			if (!empty($qr_datas['image'])) :
				echo '<img src="' . $qr_datas['image'] . '' . '" alt="" width="200" height="200">';
				echo '<button data-url="' . $qr_datas['image'] . '" class="button-primary button-large" >Copy QR URL</button>';
			else :
				echo '<button data-initiator= "' . esc_attr($datas['slug']) . '" data-id="' . esc_attr($productNum) . '" class="' . esc_attr($datas['buttonClass']) . ' button button-primary button-large">Generate Product QR</button>';

			endif;
			echo '<input data-id="' . esc_attr($productNum) . '" type="hidden" name ="' . esc_attr($datas['slug']) . '" value="' . $qr_datas . '">';
			echo '</div>';
		}
	}

	public function render_metabox_product_qr_code($post)
	{
		$this->render_generator_button_with_image($this->generators[0], $post->ID);
	}

	public function render_metabox_direct_checkout($post)
	{
		$this->render_generator_button_with_image($this->generators[1], $post->ID);
	}

	public function render_metabox_product_variable_qr_code($post)
	{
		$this->render_generator_button_with_image($this->generators[2], $post->ID);
	}

	public function filterArrayByKeys(array $input, array $column_keys)
	{
		$result      = array();
		$column_keys = array_flip($column_keys);
		foreach ($input as $key => $val) {

			$result[$key] = array_intersect_key($val, $column_keys);
		}
		return $result;
	}



	public function save_metabox($post_id, $post)
	{
		// Add nonce for security and authentication.
		$nonce_name   = $_POST['car_nonce'];
		$nonce_action = 'car_nonce_action';

		// Check if a nonce is set.
		if (!isset($nonce_name))
			return;

		// Check if a nonce is valid.
		if (!wp_verify_nonce($nonce_name, $nonce_action))
			return;

		// Check if the user has permissions to save data.
		if (!current_user_can('edit_post', $post_id))
			return;

		// Check if it's not an autosave.
		if (wp_is_post_autosave($post_id))
			return;

		// Check if it's not a revision.
		if (wp_is_post_revision($post_id))
			return;

		// // Sanitize user input.
		// $car_new_year = isset($_POST['car_year']) ? sanitize_text_field($_POST['car_year']) : '';
		// $car_new_mileage = isset($_POST['car_mileage']) ? sanitize_text_field($_POST['car_mileage']) : '';
		// $car_new_cruise_control = isset($_POST['car_cruise_control']) ? 'checked' : '';
		// $car_new_power_windows = isset($_POST['car_power_windows']) ? 'checked' : '';
		// $car_new_sunroof = isset($_POST['car_sunroof']) ? 'checked' : '';

		// // Update the meta field in the database.
		// update_post_meta($post_id, 'car_year', $car_new_year);
		// update_post_meta($post_id, 'car_mileage', $car_new_mileage);
		// update_post_meta($post_id, 'car_cruise_control', $car_new_cruise_control);
		// update_post_meta($post_id, 'car_power_windows', $car_new_power_windows);
		// update_post_meta($post_id, 'car_sunroof', $car_new_sunroof);
	}
}
new Car_Info_Meta_Box;
