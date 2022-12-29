<?php
class Dongtrader_Apis_Settings
{
	/**
	 * Name of the plugin
	 * @var string
	 */
	public $plugin_name;

	/**
	 * Slugified version of plugin name
	 * @var string
	 */
	public $plugin_slug;

	/**
	 * Array of tabs for the settings page
	 * @var array
	 */
	public $tabs;

	/**
	 * Array of option names (one per tab) for storing the settings on each tab.
	 * Each corresponds to an "option_name" key in the "wp_options" table.
	 * @var array
	 */
	public $options;


	public function __construct()
	{
		// setup
		$this->init();

		// add actions and filters
		$this->add_hooks();
	}

	/**
	 * Setup procedures
	 *
	 * Put stuff here that needs to happen before any hooks fire, or that can
	 * be done without a hook (e.g. define properties, add shortcodes, etc.)
	 */
	public function init()
	{
		// define slugs
		$this->plugin_name = 'Dongtrader API Settings';
		$this->plugin_slug = str_replace(' ', '_', strtolower($this->plugin_name));

		$this->define_tabs();
		$this->add_options();
	}

	/**
	 * Defines tabs for the settings page
	 */
	public function define_tabs() 
	{
		$this->tabs = array(
			array(
				'id' => 'glassfrog',
				'title' => 'Glassfrog Settings',
				'has_fields' => true,
			),
			array(
				'id' => 'qrtiger',
				'title' => 'QrTiger Settings',
				'has_fields' => true,
			),
			// array(
			// 	'id' => 'others',
			// 	'title' => 'Miscalleneous',
			// 	'has_fields' => false,
			// ),
		);
	}

	/**
	 * Adds options to the database to store the settings
	 */
	public function add_options() 
	{
		foreach ($this->tabs as $tab) {
			// skip if no fields to save
			if (empty($tab['has_fields'])) {
				continue;
			}
			// define option name
			$this->options[$tab['id']] = sprintf('%s_%s', $this->plugin_slug, $tab['id']);
			// add it to the database
			add_option($this->options[$tab['id']]);
		}
	}

	/**
	 * Adds action and filter hooks
	 * 
	 * This is the control center. Use hooks to call other methods. Everything
	 * below this point can be called from a hook.
	 */
	public function add_hooks() 
	{
		add_action('admin_menu', array($this, 'add_settings_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Adds a top-level menu page for our plugin settings
	 */
	public function add_settings_page()
	{
		add_menu_page(
			$this->plugin_name // page title
			, $this->plugin_name // menu title
			, 'activate_plugins'// capability
			, $this->plugin_slug // menu slug
			, array($this, 'render_settings_page') // function
			, null // icon url
			, null // position
		);
	}

	/**
	 * Registers settings (one for each tab)
	 */
	public function register_settings() 
	{
		foreach ($this->tabs as $tab) {
			// skip if no fields to save
			if (empty($tab['has_fields'])) {
				continue; 
			}

			// register setting
			register_setting(
				$this->options[$tab['id']] // option group
				, $this->options[$tab['id']] // option name
			);

			// add sections
			$this->add_sections($tab['id']);
		}
	}

	/**
	 * Adds sections to each tab
	 * 
	 * @param string $tab - tab id
	 */
	public function add_sections($tab) 
	{
		$sections = array();

		switch ($tab) {
			case 'glassfrog':
				$sections = array(
					array(
						'id' => 'glassfrog-apicredentials',
						'title' => 'Glassfrog API Credentials',
					),
					// array(
					// 	'id' => 'section_2',
					// 	'title' => 'Section 2',
					// ),
				);
				break;
			
			case 'qrtiger':
				$sections = array(
                    array(
						'id' => 'qrtiger-apicredentials',
						'title' => 'QrTiger API Credentials',
					),
					// array(
					// 	'id' => 'section_4',
					// 	'title' => 'Section 4',
					// ),
				);
				break;
		}

		if (!empty($sections)) {
			foreach ($sections as &$section) {
				// add page to section (we'll need it when adding fields)
				$section['page'] = $this->options[$tab];

				// add section
				extract($section);
			    add_settings_section(
			    	$id // id
			    	, $title // title
			    	, array($this, 'section_text') // callback
			    	, $page // page
		    	);

				// add fields to that section
				$this->add_fields($section);
			}
		}
	}

	/**
	 * Outputs any HTML you want under the section heading
	 * 
	 * @param array $section - array of section data automatically provided (includes 'id' and 'title')
	 */
	public function section_text($section) 
	{
		switch ($section['id']) {
			// something unique for this case
			case 'glassfrog':
				echo '<p>This section rocks!</p>';
				break;
			
			default:
				//printf('<p>Default instructions for %s</p>', $section['title']);
				break;
		}
	}

	/**
	 * Adds fields to each section
	 *
	 * @param array $section - array of section data defined in add_sections()
	 */
	public function add_fields($section) 
	{
		$fields = array();
		$option_name = $section['page'];
//glassfrog-apicredentials
//qrtiger-apicredentials
		switch ($section['id']) {
			case 'glassfrog-apicredentials':
				$fields = array(
					array(
		    			'id' => 'glassfrog-api-key',
		    			'title' => 'Glassfrog API Key',
			    		'type' => 'text',
			    		'size' => '30',
			    		'help_text' => 'API key provided by glassfrog',
					),
					array(
		    			'id' => 'api-url',
		    			'title' => 'Glassfrog API URL',
			    		'type' => 'text',
			    		'size' => '30',
			    		'help_text' => 'API URL',
					),
				);
				break;
			
			case 'qrtiger-apicredentials':
				$fields = array(
					array(
		    			'id' => 'qrtiger-api-key',
		    			'title' => 'QrTiger API Key',
			    		'type' => 'text',
			    		'size' => '30',
			    		'help_text' => 'API key provided by QrTiger',
					),
					array(
		    			'id' => 'qrtiger-api-url',
		    			'title' => 'QrTiger API URL',
			    		'type' => 'text',
			    		'size' => '30',
			    		'help_text' => 'API URL',
					),
				);
				break;

					}

		if (!empty($fields)) {
			foreach ($fields as &$field) {
				// add option name to field (we'll need it when rendering the field)
				$field['option_name'] = $option_name;

				// add each field the the section
				extract($field);
				add_settings_field(
					$id // id
					, $title // title
					, array($this, 'render_settings_field') // callback
					, $section['page'] // page
					, $section['id'] // section
					, $field // args
				);
			}
		}
	}

	/**
	 * Outputs the HTML for each form field
	 * 
	 * @param array $field - array of field data defined in add_fields()
	 */
	public function render_settings_field($field) 
	{
		// get option from database
		extract($field);
		$option = get_option($option_name);

		// get field name and value
		$field_name = sprintf('%s[%s]', $option_name, $id);
		$field_value = (!empty($option[$id])) ? $option[$id] : '';

		// render based on type
		switch ($type) {

			case 'text':
				printf('<input type="text" name="%s" value="%s" size="%s" />', $field_name, $field_value, $size);
				if (isset($help_text)) {
					printf('<span style="font-style: italic; padding-left: 5px;">%s</span>', $help_text);
				}
				break;

			case 'textarea':
				printf('<textarea name="%s" id="%s" rows="%d" cols="%d">', $field_name, $field_name, $rows, $cols);
					echo $field_value;
				echo '</textarea>';
				if (isset($help_text)) {
					printf('<p style="font-style: italic;">%s</p>', $help_text);
				}
				break;

			case 'checkbox':
				printf('<label for="%s">', $field_name);
					printf('<input type="hidden" name="%s" value="0" />', $field_name); // save even if unchecked
					printf('<input type="checkbox" name="%s" id="%s" value="1" %s/>', $field_name, $field_name, checked($field_value, 1, false));
				printf('<span>%s</span></label>', $label_text);
				break;

			case 'checkbox_array':
				// make it an array
				$field_name = $field_name . '[]';
				if (!empty($choices)) {
					if (isset($help_text)) {
						printf('<p style="font-style: italic;">%s</p>', $help_text);
					}
					echo '<ul style="list-style-type: none;">';
					foreach ($choices as $choice) {
						echo '<li>';
							echo '<label>';
								printf('<input type="checkbox" name="%s" value="%s" %s />', $field_name, $choice['value'], checked(in_array($choice['value'], $field_value), 1, false));
							printf('<span>%s</span></label>', $choice['label_text']);
						echo '</li>';
					}
					echo '</ul>';
				}
				break;

			case 'radio':
				if (!empty($choices)) {
					if (isset($help_text)) {
						printf('<p style="font-style: italic;">%s</p>', $help_text);
					}
					echo '<ul style="list-style-type: none;">';
					foreach ($choices as $choice) {
						echo '<li>';
							echo '<label>';
								printf('<input type="radio" name="%s" value="%s" %s />', $field_name, $choice['value'], checked($choice['value'], $field_value, false));
							printf('<span>%s</span></label>', $choice['label_text']);
						echo '</li>';
					}
					echo '</ul>';
				}
				break;

			case 'select':
				if (!empty($choices)) {
					printf('<select name="%s" id="%s">', $field_name, $field_name);
					foreach ($choices as $choice) {
						printf('<option %s value="%s">%s</option>', selected($choice['value'], $field_value, false), $choice['value'], $choice['label_text']);
					}
					echo '</select>';
					if (isset($help_text)) {
						printf('<span style="font-style: italic; padding-left: 5px;">%s</span>', $help_text);
					}
				}
				break;
		}
	}

	/**
	 * Outputs the HTML for the settings page
	 */
	public function render_settings_page() 
	{
		// default to first tab
		$active_tab = (!empty($_GET['tab'])) ? $_GET['tab'] : $this->tabs[0]['id'];

		// page contents
		echo '<div class="wrap">';
			// heading
			echo '<div class="col-plugin-icon icon32"></div>';
			printf('<h2>%s</h2>', $this->plugin_name);
			// tabs
			echo '<h2 class="nav-tab-wrapper">';
				foreach ($this->tabs as $tab) {
					$tab_class = ($tab['id'] == $active_tab) ? ' nav-tab-active' : '';
					printf('<a href="?page=%s&tab=%s" class="nav-tab%s">%s</a>', $this->plugin_slug, $tab['id'], $tab_class, $tab['title']);
				}
			echo '</h2>';
			// tab contents
			switch ($active_tab) {
				// special cases
				case 'instructions':
					echo '<p>Instructions go here. This tab has no fields.</p>';
					break;
				
				// output the settings form
				default:
					settings_errors();
					echo '<form action="options.php" method="post">';
						settings_fields($this->options[$active_tab]);
				        do_settings_sections($this->options[$active_tab]);
						submit_button();
					echo '</form>';
					break;
			}
		echo '</div>';
	}

}


$plugin_settings = new Dongtrader_Apis_Settings();