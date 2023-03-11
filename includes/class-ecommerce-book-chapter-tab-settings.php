<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class ECommerce_Book_Chapter_Tab_Settings {

	/**
	 * The single instance of ECommerce_Book_Chapter_Tab_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		
		$this->parent = $parent;

		$this->base = 'wbch_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
		add_action( 'admin_menu' , array( $this, 'add_menu_items' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links', array($this,'add_settings_link'),10,2);
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add Setting SubPage
	 *
	 * add Setting SubPage to wordpress administrator
	 *
	 * @return array validate input fields
	 */
	public function add_setting_page() {

		global $admin_page_hooks;

		$logo = $this->parent->assets_url . 'images/recuweb-icon.png';

		if( ! isset( $admin_page_hooks['rew_plugin_panel'] ) ) {
			
			$position = apply_filters( 'rew_plugins_menu_item_position', '62.31' );
			add_menu_page( 'rew_plugin_panel', 'Code Market', 'nosuchcapability', 'rew_plugin_panel', NULL, $logo, $position );
		}

		add_submenu_page( 'rew_plugin_panel', 'Book Chapter Tab', 'Book Chapter Tab', 'manage_options', 'ecommerce-book-chapter-tab', array( $this, 'settings_page' ) );
		remove_submenu_page( 'rew_plugin_panel', 'rew_plugin_panel' );
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		
		//add menu in wordpress dashboard
		
		
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link( $links, $file ) {
		
		if( strpos( $file, basename( $this->parent->file ) ) !== false ) {
			
			$settings_link = '<a href="' . get_admin_url(null,'admin.php?page=wc-settings&tab=products&section=rew-tabs') . '">' . __( 'Settings', 'ecommerce-book-chapter-tab' ) . '</a>';
			
			array_push( $links, $settings_link );
  		}
		
		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				
				$current_section = sanitize_text_field($_POST['tab']);
			} 
			else {
				
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					
					$current_section = sanitize_text_field($_GET['tab']);
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo wp_kses_normalize_entities($html);
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {
		
		$plugin_data = get_plugin_data( $this->parent->file );
		
		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			
			$html .= '<h2>' . __( $plugin_data['Name'] , 'ecommerce-book-chapter-tab' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}
			
			// Get premium view

			$premium_tab_template = $this->parent->views . $this->parent->_dev . '/premium.php';
			
			if( file_exists( $premium_tab_template ) ) {
				
				ob_start();
				
				include_once( $premium_tab_template );
				
				$html .= ob_get_clean();
			}
		
		$html .= '</div>';

		echo wp_kses_normalize_entities($html);
	}

	/**
	 * Main ECommerce_Book_Chapter_Tab_Settings Instance
	 *
	 * Ensures only one instance of ECommerce_Book_Chapter_Tab_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see ECommerce_Book_Chapter_Tab()
	 * @return Main ECommerce_Book_Chapter_Tab_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
