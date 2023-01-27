<?php

class Dongtrader_qr_metas
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
					'title' => __('Products QR Code', 'cpm-dongtrader'),
					'buttonClass' => 'generate-product-qr',
					'variable' => false,
					'callback' => 'render_metabox_product_qr_code',

				),
				array(
					'slug' => '_product-qr-direct-checkouts',
					'title' => __('Products Direct Checkout QR Code', 'cpm-dongtrader'),
					'buttonClass' => 'generate-product-qr-direct-checkout',
					'variable' => false,
					'callback' => 'render_metabox_direct_checkout'
				),
				array(
					'slug' => '_product-qr-variabled',
					'title' => __('Variable Products QR Code', 'cpm-dongtrader'),
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
				'low'

			);
		}
	}


	public function generate_qr_generator_button($slug, $productNum, $btnclass)
	{
		$product = wc_get_product($productNum);
		$productType =  $product->get_type();
		if ($productType === 'variable') {
			$variations =  $product->get_children();
			print_r($variations);
			$encoded_variations_ids =  json_encode($variations);
			var_dump($encoded_variations_ids);
			echo '<button data-ids="' . $encoded_variations_ids . '" data-initiator= "' . esc_attr($slug) . '" data-productid="' . esc_attr($productNum) . '" class="' . esc_attr($btnclass) . ' button button-primary button-large">Generate QR</button>';
		} else {

			echo '<button  data-ids="' . esc_attr(array($productNum)) . '"  data-initiator= "' . esc_attr($slug) . '" data-productid="' . esc_attr($productNum) . '" class="' . esc_attr($btnclass) . ' button button-primary button-large">Generate QR</button>';
		}
	}

	public function render_generator_button_with_image($datas, $productNum)
	{

		$qr_datas = get_post_meta($productNum, $datas['slug'], true);
		$html_decode = htmlspecialchars_decode($qr_datas);
		$decoded_json = json_decode($html_decode,true);
		$product = wc_get_product($productNum);
		$variations = $product->get_available_variations();
		$variations_id = wp_list_pluck($variations, 'variation_id');
		$str_variations = implode(",", $variations_id);
		echo '<div id= "" class= "qr-containers-dong-' . $datas['slug'] . '">';
		// get meta according to the slug form top array
		if($datas['variable']){
			if(!empty($decoded_json)){
				foreach($decoded_json as $d){
					$html_decoder = htmlspecialchars_decode($d);
					$json_decodes = json_decode($html_decoder, true);
					echo '<div class = "qr-components-dongs">';
						if(!empty($json_decodes)){
							echo '<img src="' . $json_decodes['qr_image_url'] . '' . '" alt="" width="200" height="200">';
					//copy url button
							echo '<button data-url="'.$json_decodes['qr_image_url'] . '" class="button-primary button-large url-copy" >Copy QR URL</button>';
							//echo '<button data-url="'.$json_decodes['qr_image_url'] . '" class="button-primary button-large url-copy" >Copy QR URL</button>';
						}
					echo '</div>';
				}
			}else{
				echo '<button data-variable="'.$str_variations.'" data-initiator= "' . esc_attr($datas['slug']) . '" data-id="' . esc_attr($productNum) . '" class="' . esc_attr($datas['buttonClass']) . ' button button-primary button-large">Generate Variable Product QR</button>';
			}
		

		}else{
			echo '<div class = "qr-components-dongs">';
			if (!empty($decoded_json) ) :
				//image block
				echo '<img src="' . $decoded_json['qr_image_url'] . '' . '" alt="" width="200" height="200">';
				//copy url button
				echo '<button data-url="'.$decoded_json['qr_image_url'] . '" class="button-primary button-large url-copy" >Copy QR URL</button>';

				//echo '<button data-remove="'.$productNum.'" class="button-primary button-large">Delete</button>';
			else :
				if($datas['variable']){
					echo '<button data-variable="'.$str_variations.'" data-initiator= "' . esc_attr($datas['slug']) . '" data-id="' . esc_attr($productNum) . '" class="' . esc_attr($datas['buttonClass']) . ' button button-primary button-large">Generate Product QR</button>';
				}else{
					echo '<button data-variable= "false" data-initiator= "' . esc_attr($datas['slug']) . '" data-id="' . esc_attr($productNum) . '" class="' . esc_attr($datas['buttonClass']) . ' button button-primary button-large">Generate Product QR</button>';
				}
				// $this->generate_qr_generator_button($datas['slug'], $productNum, $datas['buttonClass']);
			endif;
			echo '<input data-id="' . esc_attr($productNum) . '" type="hidden" name ="' . esc_attr($datas['slug']) . '" value="' . esc_attr($qr_datas) . '">';
			echo '</div>';
		}
		echo '</div>';
	}

	public function render_metabox_product_qr_code($post)
	{
		global $post;
		$this->render_generator_button_with_image($this->generators[0], $post->ID);
	}

	public function render_metabox_direct_checkout($post)
	{
		$this->render_generator_button_with_image($this->generators[1], $post->ID);
	}

	public function render_metabox_product_variable_qr_code($post)
	{
		$product = wc_get_product($post->ID);
		$productType =  $product->get_type();
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
		// // Sanitize user input.
		$product_qr_code = isset($_POST['_product_qr_codes']) ? esc_attr($_POST['_product_qr_codes']) : '';
		update_post_meta($post_id, '_product_qr_codes', $product_qr_code);
		$direct_checkout_code = isset($_POST['_product-qr-direct-checkouts']) ? esc_attr($_POST['_product-qr-direct-checkouts']) : '';
		update_post_meta($post_id, '_product-qr-direct-checkouts', $direct_checkout_code);
		$variable_qr_code = !empty($_POST['_product-qr-variabled']) ? esc_attr(json_encode($_POST['_product-qr-variabled'])): '';
		update_post_meta($post_id,'_product-qr-variabled',	$variable_qr_code);
		
	}
}
new Dongtrader_qr_metas;
