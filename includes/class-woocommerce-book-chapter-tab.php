<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Book_Chapter_Tab {

	/**
	 * The single instance of WooCommerce_Book_Chapter_Tab.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	public $_dev = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $license = null;
	public $notices = null;
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;
	public $views;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */

	public static $plugin_prefix;
	public static $plugin_url;
	public static $plugin_path;
	public static $plugin_basefile;

	public $tab_data = null;
	
	public $woo_version = '2.0';
	
	public $title 		= 'Chapters';
	public $accordion 	= 'no';
	public $showEmpty 	= 'yes';
	public $showDots 	= 'no';
	public $priority 	= 20;
	 
	public function __construct ( $file = '', $version = '1.0.0' ) {

		$this->_version = $version;
		$this->_token 	= 'woocommerce-book-chapter-tab';
		$this->_base 	= 'wbch_';
		
		$this->premium_url = 'https://code.recuweb.com/download/woocommerce-book-chapter-tab/';

		// Load plugin environment variables
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		WooCommerce_Book_Chapter_Tab::$plugin_prefix = $this->_base;
		WooCommerce_Book_Chapter_Tab::$plugin_basefile = $this->file;
		WooCommerce_Book_Chapter_Tab::$plugin_url = plugin_dir_url($this->file); 
		WooCommerce_Book_Chapter_Tab::$plugin_path = trailingslashit($this->dir);

		// register plugin activation hook
		
		//register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		
		$this->admin = new WooCommerce_Book_Chapter_Tab_Admin_API($this);
		
		$this->license = new WooCommerce_Book_Chapter_Tab_License($this);
		
		if( $this->license->is_valid() ){

			// get premium options
			
			$this->title 		= get_option('wbch_tab_title','Chapters');
			$this->priority 	= get_option('wbch_tab_priority',20);
			$this->accordion 	= get_option('wbch_enable_accordion','yes');
			$this->showEmpty 	= get_option('wbch_show_empty','yes');		
			$this->showDots 	= get_option('wbch_show_dots','yes');		
		}

		/* Localisation */
		
		$locale = apply_filters('plugin_locale', get_locale(), 'woocommerce-book-chapter-tab');
		load_textdomain('wc_book_chapter', WP_PLUGIN_DIR . "/".plugin_basename(dirname(__FILE__)).'/lang/wc_book_chapter-'.$locale.'.mo');
		load_plugin_textdomain('wc_book_chapter', false, dirname(plugin_basename(__FILE__)).'/lang/');
		
		add_action('woocommerce_init', array($this, 'init'));
		
		$this->woo_settings = array(
			
			array(
			
				'name' 	=> __( 'Book Chapter Tab', 'wc_book_chapter' ),
				'type' 	=> 'title',
				'desc' 	=> '',
				'id' 	=> 'product_book_chapter_tab'
			),
			array(  
				'name' => __('Tab Name', 'wc_book_chapter'),
				'desc' 		=> __('The name of the tab in the product page', 'wc_book_chapter'),
				'id' 		=> 'wbch_tab_title',
				'type' 		=> 'text',
				'default'	=> __('Chapters', 'wc_book_chapter'),
			),
			array(  
				'name' => __('Tab Position', 'wc_book_chapter'),
				'desc' 		=> __('The position of the tab in the list', 'wc_book_chapter'),
				'id' 		=> 'wbch_tab_priority',
				'type' 		=> 'number',
				'default'	=> 20,
			),
			array(  
				'name' => __('Enable Accordion', 'wc_book_chapter'),
				'desc' 		=> __('Enable Accordion for chapters in the tab', 'wc_book_chapter'),
				'id' 		=> 'wbch_enable_accordion',
				'default' 	=> 'yes',
				'type' 		=> 'checkbox',
			),
			array(  
				'name' => __('Show Empty Tab', 'wc_book_chapter'),
				'desc' 		=> __('Show empty table of content', 'wc_book_chapter'),
				'id' 		=> 'wbch_show_empty',
				'default' 	=> 'no',
				'type' 		=> 'checkbox',
			),
			array(  
				'name' => __('Show Dotted Line', 'wc_book_chapter'),
				'desc' 		=> __('Show a line of dots between section and page number', 'wc_book_chapter'),
				'id' 		=> 'wbch_show_dots',
				'default' 	=> 'yes',
				'type' 		=> 'checkbox',
			),
			array(
			
				'title' 		=> __( 'Header background color', 'wc_book_chapter' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Default background color of the accordion header', 'wc_book_chapter' ),
				'class' 		=> 'colorpick',
				'default' 		=> '#ccc',
				'id' 			=> 'wbch_head_bkg_color'
			),
			array(
			
				'title' 		=> __( 'Header text color', 'wc_book_chapter' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Default text color of the accordion header', 'wc_book_chapter' ),
				'class' 		=> 'colorpick',
				'default' 		=> '#000',
				'id' 			=> 'wbch_head_txt_color'
			),
			array(
			
				'title' 		=> __( 'Opened header background color', 'wc_book_chapter' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Background color of the opened accordion header', 'wc_book_chapter' ),
				'class' 		=> 'colorpick',
				'default' 		=> '#000',
				'id' 			=> 'wbch_op_head_bkg_color'
			),
			array(
			
				'title' 		=> __( 'Opened header text color', 'wc_book_chapter' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Default text color of the opened accordion header', 'wc_book_chapter' ),
				'class' 		=> 'colorpick',
				'default' 		=> '#fff',
				'id' 			=> 'wbch_op_head_txt_color'
			),			
			array(
				'title' 		=> __( 'Header border top color', 'wc_book_chapter' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Color of the accordion header border', 'wc_book_chapter' ),
				'class' 		=> 'colorpick',
				'default' 		=> '#f0f0f0',
				'id' 			=> 'wbch_bd_top_color'
			),
			array(
			
				'title' 		=> __( 'Body background color', 'wc_book_chapter' ),
				'type' 			=> 'text',
				'description' 	=> __( 'Background color of the accordion body', 'wc_book_chapter' ),
				'class' 		=> 'colorpick',
				'default' 		=> '#fff',
				'id' 			=> 'wbch_body_bkg_color'
			),
			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'product_book_chapter_tab'
			),
		);
		
	} // End __construct ()

	/**
	 * Init WooCommerce Book Chapter Tab extension once we know WooCommerce is active
	 */
	public function init(){
		
		if( version_compare(WOOCOMMERCE_VERSION, $this->woo_version, '>=') ){ 
			
			// backend
			
			add_filter('plugin_row_meta', array($this, 'add_support_link'), 10, 2);
			
			if( $this->license->is_valid() ){
			
				// Settings
			
				add_action('woocommerce_settings_catalog_options_after', array($this, 'book_chapter_admin_settings'));
				add_action('woocommerce_update_options', array($this, 'save_book_chapter_admin_settings'));			
			}
			
			// Product options
			
			add_filter( 'woocommerce_product_data_tabs', array($this, 'custom_product_tabs') );
				
			if( version_compare(WOOCOMMERCE_VERSION, "2.6", '>=') ){
				
				add_filter( 'woocommerce_product_data_panels', array($this, 'book_chapter_options_product_tab_content') ); // WC 2.6 and up
			}
			else{
				
				add_filter( 'woocommerce_product_data_tabs', array($this, 'book_chapter_options_product_tab_content') ); // WC 2.5 and below
		
			}

			add_action( 'woocommerce_process_product_meta_simple', array($this, 'save_book_chapter_option_fields')  );
			add_action( 'woocommerce_process_product_meta_variable', array($this, 'save_book_chapter_option_fields')  );

			//frontend
			
			add_filter('woocommerce_product_tabs', array($this, 'chapters'));
			
			if( $this->license->is_valid() ){
				
	
			}
		}
		else{		
			
			$this->notices->add_error('WooCommerce Book Chapter Tab '.__('requires at least <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce ' . $this->woo_version . '</a> in order to work. Please upgrade <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.', 'wc_book_chapter'));			
		}
	}

	// Adds a few settings to control the images in the tab.
	
	function book_chapter_admin_settings(){
		
		global $settings;

		woocommerce_admin_fields($this->woo_settings);
	}

	function save_book_chapter_admin_settings(){
		
		woocommerce_update_options($this->woo_settings);
	}
	
	/**
	 * Add a custom product tab.
	 */
	 
	public function custom_product_tabs( $tabs) {
		
		$tabs['chapter'] = array(
		
			'label'		=> __( 'Chapters', 'wc_book_chapter' ),
			'target'	=> 'book_chapter_options',
			'class'		=> array( 'show_if_simple', 'show_if_variable'  ),
		);
		
		return $tabs;
	}
	
	/**
	 * Contents of the gift card options product tab.
	 */
	 
	function book_chapter_options_product_tab_content() {
		
		global $post;
		
		// Note the 'id' attribute needs to match the 'target' parameter set above
		?><div id='book_chapter_options' class='panel woocommerce_options_panel'><?php
			?><div class='options_group'>
			
			<?php
					
				$this->admin->display_field( array(
				
					'type'				=> 'chapter_section',
					'id'				=> 'wbch_items',
					'name'				=> 'wbch_items',
					'description'		=> ''
					
				), $post );
			?>
			
			</div>

		</div><?php
	}
	
	/**
	 * Save the custom fields.
	 */
	 
	function save_book_chapter_option_fields( $post_id ) {
		
		if( !empty($_POST['wbch_items']) && is_array($_POST['wbch_items']) && isset($_POST['wbch_items']['chapter']) && is_array($_POST['wbch_items']['chapter']) ){
			
			$items = array();
			
			if( isset($_POST['wbch_items']['sections']) && count($_POST['wbch_items']['chapter']) == count($_POST['wbch_items']['sections']) ){
				
				// free version array
				
				$items = $_POST['wbch_items'];
			}
			elseif( count($_POST['wbch_items']['chapter']) > 1 ){
				
				$i = 0;
				
				$items = ['chapter' => [ $i => '' ], 'sections' => [ $i => '' ], 'pages' => [ $i => '' ], 'urls' => [ $i => '' ]];
				
				foreach( $_POST['wbch_items']['chapter'] as $row ){

					$type 	= key($row);
					$value 	= trim($row[$type]);
					
					if( $type == 'name' ){
						
						if( $i == 0 ){

							++$i;
						}
						elseif( !empty($value) ){
							
							++$i;
							$items['chapter'][$i] = $value;				
						}
						elseif( isset($items['chapter'][$i+1]) && key($items['chapter'][$i+1]) != 'name' ){
							
							++$i;
							$items['chapter'][$i] = $value;							
						}
					}
					elseif( $type == 'section' ){
						
						if( empty($items['sections'][$i]) ){
							
							$items['sections'][$i] = $value;
						}
						else{
							
							$items['sections'][$i] .= PHP_EOL . $value;
						}
					}
					elseif( $type == 'page' ){
						
						if( empty($items['pages'][$i]) ){
							
							$items['pages'][$i] = $value;
						}
						else{
							
							$items['pages'][$i] .= PHP_EOL . $value;
						}						
					}
					elseif( $type == 'url' ){
						
						if( empty($items['urls'][$i]) ){
							
							$items['urls'][$i] = $value;
						}
						else{
							
							$items['urls'][$i] .= PHP_EOL . $value;
						}							
					}
				}
				
				//echo'<pre>';var_dump($items);exit;
			}
			
			if( !empty($items) ){
				
				update_post_meta( $post_id, 'wbch_items', $items );
			}
		}
	}
	
	/**
	 * Add links to plugin page.
	 */
	 
	public function add_support_link($links, $file){
		
		if(!current_user_can('install_plugins')){
			
			return $links;
		}
		
		if($file == WooCommerce_Book_Chapter_Tab::$plugin_basefile){
			
			$links[] = '<a href="https://code.recuweb.com" target="_blank">'.__('Docs', 'wc_book_chapter').'</a>';
		}
		
		return $links;
	}
	
	public function get_product_book_chapters($product_id){
		
		$chapters = array();
		
		if( is_null($this->tab_data) ){
		
			$this->tab_data = get_post_meta( $product_id, 'wbch_items', true );
		}
		
		if( !empty($this->tab_data['chapter']) ){
			
			foreach( $this->tab_data['chapter'] as $e => $chapter ){

				if( !empty($chapter) ){
					
					$chapters[$chapter]['sections'] = $this->tab_data['sections'][$e];
					
					if( isset($this->tab_data['pages'][$e]) ){
						
						$chapters[$chapter]['pages'] = $this->tab_data['pages'][$e];
					}
					
					if( isset($this->tab_data['urls'][$e]) ){
						
						$chapters[$chapter]['urls'] = $this->tab_data['urls'][$e];
					}
				}
			}
		}
		
		return $chapters;
	}
	
	/**
	 * Write the images tab on the product view page for WC 2.0+.
	 * In WooCommerce these are handled by templates.
	 */
	public function chapters($tabs){
		
		global $post, $wpdb, $product;
		
		$chapters = $this->get_product_book_chapters( $post->ID );
		
		$countItems = count($chapters);

		if( $this->showEmpty == 'yes' || $countItems > 0 ){

			$tabs['chapters'] = array(
			
				'title'    => __($this->title, 'woocommerce-book-chapter-tab').' ('.$countItems.')',
				'priority' => $this->priority,
				'callback' => array($this, 'book_chapters_panel')
			);
		}
		
		return $tabs;
	}

	/**
	 * Write the images tab panel on the product view page.
	 * In WooCommerce these are handled by templates.
	 */
	public function book_chapters_panel(){
		
		global $post, $wpdb, $product;
		
		$chapters = $this->get_product_book_chapters( $post->ID );

		/**
		 * Checks if any images are attached to the product.
		 */
		$countItems = count($chapters);

		if( $this->showEmpty == 'yes' || $countItems > 0  ){
			
			echo '<h2>' . __($this->title, 'wc_book_chapter') . '</h2>';
			
			echo '<div id="wbch_items">';
				
				$i=1;

				foreach( $chapters as $chapter => $data ){
					
					echo '<h3 id="wbch-chapter-'.$i.'" class="wbch-accordion wbch-chapter">Chapter ' . ' ' . $i . ' - ' . $chapter .'<span></span></h3>';
					
					$sections = $pages = $urls = array();
					
					if( is_string($data['sections']) ){
						
						$sections = explode(PHP_EOL,$data['sections']);
					}

					if( is_array($sections) && !empty($sections) ){

						if( isset($data['pages']) && is_string($data['pages']) ){
							
							$pages = explode(PHP_EOL,$data['pages']);
						}
					
						if( isset($data['urls']) && is_string($data['urls']) ){
							
							$urls = explode(PHP_EOL,$data['urls']);
						}						
						 
						echo '<ol class="wbch-sections wbch-sections-'.$i.'">';
							
							foreach( $sections as $j => $section  ){

								$section = strip_tags($section);

								if( !empty($section) ){
									
									$page = '';
									
									if( isset($pages[$j]) ){
										
										$page = $pages[$j];
									}
									
									$url = '';
									
									if( isset($urls[$j]) ){
									
										$url = $urls[$j];
									}									
									
									echo '<li class="wbch-section"'.( $this->showDots == 'yes' ? ' style="background: url(' . $this->assets_url . 'images/dot.png) center center repeat-x;"' : '' ) . '>';
										

										if( $this->license->is_valid() && !empty($url) ){
											
											echo '<a style="float:left;padding-right:5px;" href="'.$url.'">' . $section . '</a>';
										}
										else{
											
											echo '<span style="float:left;padding-right:5px;">' . $section . '</span>';
										}
										
										if( $this->license->is_valid() && !empty($page) ){
										
											echo '<span style="float:right;padding-left:5px;">'.$page.'</span>';
										}
										
									echo '</li>';
								}
							}
							
						echo '</ol>';
					}
					++$i;
				}
				
			echo '</div>';
		}
	}

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new WooCommerce_Book_Chapter_Tab_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new WooCommerce_Book_Chapter_Tab_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}
	
	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend-1.0.1.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );		
		
		if( $this->license->is_valid() ){
		
			wp_register_style( $this->_token . '-custom-style', false );
			wp_enqueue_style( $this->_token . '-custom-style' );
			wp_add_inline_style( $this->_token . '-custom-style', '
				.wbch-accordion {
					border-top: '.get_option('wbch_bd_top_color','#f0f0f0').' 1px solid;
					background: '.( $this->accordion == 'yes' ? get_option('wbch_head_bkg_color','#ccc') : get_option('wbch_op_head_bkg_color','#000') ).';
					color: '.( $this->accordion == 'yes' ? get_option('wbch_head_txt_color','#000') : get_option('wbch_op_head_txt_color','#fff') ).';
				}			
				.accordion-open {
					background: '.get_option('wbch_op_head_bkg_color','#000').';
					color: '.get_option('wbch_op_head_txt_color','#fff').';
				}
				.wbch-section a, .wbch-section span{
					background: '.get_option('wbch_body_bkg_color','#fff').';
				}
			');
		}
		
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		if( $this->accordion == 'yes' ){
		
			wp_register_script( $this->_token . '-jquery-accordion', esc_url( $this->assets_url ) . 'js/jquery.accordion.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-jquery-accordion' );			
			
			wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-frontend' );	
		}
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
		
		if( isset($_GET['page']) && $_GET['page'] == 'woocommerce-book-chapter-tab' ){
		
			wp_register_style( $this->_token . '-simpleLightbox', esc_url( $this->assets_url ) . 'css/simpleLightbox.min.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-simpleLightbox' );
		}		
		
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
		 
		if( isset($_GET['page']) && $_GET['page'] == 'woocommerce-book-chapter-tab' ){
		
			wp_register_script( $this->_token . '-simpleLightbox', esc_url( $this->assets_url ) . 'js/simpleLightbox.min.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-simpleLightbox' );
		
			wp_register_script( $this->_token . '-lightbox-admin', esc_url( $this->assets_url ) . 'js/lightbox-admin.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-lightbox-admin' );			
		}		

	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'woocommerce-book-chapter-tab', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		
	    $domain = 'woocommerce-book-chapter-tab';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()
	
	/**
	 * Main WooCommerce_Book_Chapter_Tab Instance
	 *
	 * Ensures only one instance of WooCommerce_Book_Chapter_Tab is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WooCommerce_Book_Chapter_Tab()
	 * @return Main WooCommerce_Book_Chapter_Tab instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $file, $version );
		}
		
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()
}
