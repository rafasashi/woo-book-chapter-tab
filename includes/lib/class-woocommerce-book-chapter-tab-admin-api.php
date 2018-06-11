<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Book_Chapter_Tab_Admin_API {

	/**
	 * Constructor function
	 */
	public function __construct ($parent) {
		
		$this->parent = $parent;
		
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array   $field Field data
	 * @param  boolean $echo  Whether to echo the field HTML or return it
	 * @return void
	 */
	public function display_field ( $data = array(), $post = false, $echo = true ) {

		// Get field info
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data
		$data = '';
		if ( $post ) {

			// Get saved field data
			$option_name .= $field['id'];
			$option = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		} 
		else {

			// Get saved option
			$option_name .= $field['id'];
			$option = get_option( $option_name );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		}

		// Show default data if no option saved and default is supplied
		if ( $data === false && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( $data === false ) {
			$data = '';
		}

		$html = '';

		switch( $field['type'] ) {

			case 'text':
			case 'url':
			case 'email':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
			break;

			case 'password':
			case 'number':
			case 'hidden':
				$min = '';
				if ( isset( $field['min'] ) ) {
					$min = ' min="' . esc_attr( $field['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['max'] ) ) {
					$max = ' max="' . esc_attr( $field['max'] ) . '"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
			break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
			break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
			break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( in_array( $k, (array) $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k == $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'image':
				$image_thumb = '';
				if ( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'woocommerce-book-chapter-tab' ) . '" data-uploader_button_text="' . __( 'Use image' , 'woocommerce-book-chapter-tab' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'woocommerce-book-chapter-tab' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'woocommerce-book-chapter-tab' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;

			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;
			
			case 'license':
				
				if( !empty($data) ){
				
					$is_valid = $this->parent->license->is_valid();
				}
				else{
					
					$is_valid = false;
				}
			
				echo '<input class="regular-text" type="text" id="' . esc_attr( $option_name ) . '" name="' . esc_attr( $option_name ) . '"  value="' . $data . '" ' . ( $is_valid ? 'disabled' : '') . '>';
				echo '<p class="submit">';
								
					if( !$is_valid ){
						
						echo '<input type="submit" name="activate_license" value="Activate" class="button-primary" />';
					}
					else{
						
						echo '<input type="hidden" name="' . esc_attr( $option_name ) . '" value="'.$data.'" />';
						echo '<input type="submit" name="deactivate_license" value="Deactivate" class="button" />';
					}
					
				echo '</p>';				
					 
			break;
			
			case 'chapter_section':
			
				if( !isset($data['chapter'][0]) || !isset($data['sections'][0]) || !empty($data['chapter'][0]) || !empty($data['sections'][0]) ){
					
					$arr = ['chapter' => [ 0 => '' ], 'sections' => [ 0 => '' ], 'pages' => [ 0 => '' ], 'urls' => [ 0 => '' ]];

					if( isset($data['chapter']) ){

						$arr['chapter'] = array_merge($arr['chapter'],$data['chapter']);
					}

					if( isset($data['sections']) ){

						$arr['sections'] = array_merge($arr['sections'],$data['sections']);
					}

					if( isset($data['pages']) ){

						$arr['pages'] = array_merge($arr['pages'],$data['pages']);
					}

					if( isset($data['urls']) ){

						$arr['urls'] = array_merge($arr['urls'],$data['urls']);
					}						

					$data = $arr;
				}
				
				$html .= '<div id="'.$field['id'].'" class="'. ( $this->parent->license->is_valid() ? 'sortable' : 'unsortable').'">';
					
					$html .= ' <a href="#" class="wbch-add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add chapter</a>';
				
					$html .= '<ul class="input-group'. ( $this->parent->license->is_valid() ? ' ui-sortable' : ' ui-unsortable').'">';
						
						foreach( $data['chapter'] as $e => $chapter) {

							if( $e > 0 && $this->parent->license->is_valid() ){
								
								$class='input-group-row'. ( $this->parent->license->is_valid() ? ' ui-state-default ui-sortable-handle' : '');
							}
							else{
								
								$class='input-group-row'. ( $this->parent->license->is_valid() ? ' ui-state-default ui-state-disabled' : '');
							}
							
							$sections = $pages = $urls = '';
							
							if( isset($data['sections'][$e]) ){
							
								$sections = str_replace('\\\'','\'',$data['sections'][$e]);
							}
							
							if( isset($data['pages'][$e]) ){
								
								$pages = $data['pages'][$e];
							}
							
							if( isset($data['urls'][$e]) ){
								
								$urls = $data['urls'][$e];
							}							
								
							$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:'.( $e == 0 ? 'none' : 'inline-block' ).';width:97%;background: rgb(255, 255, 255);">';
						
								$html .= '<div style="width:100%;display:inline-block;'.( $this->parent->license->is_valid() ? 'background-image: url(' . $this->parent->assets_url . 'images/dnd-icon.png?3);background-position-y:5px;background-position-x:right;background-repeat: no-repeat;background-color: transparent;' : '' ).'">';
						
									$html .= '<input type="text" placeholder="Chapter" style="width:80%;margin-bottom:5px;" name="'.$option_name.'[chapter][][name]" value="'.$data['chapter'][$e].'">';
									
									if( $e > 0 ){
									
										$html .= '<a class="remove-input-group" href="#">remove</a> ';
									}

								$html .= '</div>';
								
								$html .= '<div style="width:100%;padding: 0 12px;">';
									
									if( $this->parent->license->is_valid() ){
										
										$html .= ' <a href="#" class="wbch-add-input-group-section" data-target="'.$field['id'].'-section-row" style="font-size: 11px;line-height:40px;border-color:#ccc;background: #f7f7f7;box-shadow: 0 1px 0 #ccc;vertical-align: top;padding: 2px 8px;border-radius: 3px;">Add section</a>';
										
										$html .= '<div class="input-group-section">';
										
											if( is_string($sections) ){
												
												if( !empty($sections) ){
													
													$sections = array_merge(array( 0 => ''),explode(PHP_EOL,$sections));
												}
												else{
													
													$sections = array( 0 => '');
												}
												
											}
											
											if( is_string($pages) ){
												
												if( !empty($pages) ){
													
													$pages = array_merge(array( 0 => ''),explode(PHP_EOL,$pages));
												}
												else{
													
													$pages = array( 0 => '');
												}
											}
											
											if( is_string($urls) ){
												
												if( !empty($urls) ){
													
													$urls = array_merge(array( 0 => ''),explode(PHP_EOL,$urls));
												}
												else{
													
													$urls = array( 0 => '');
												}
											}
											
											foreach( $sections as $i => $section ){
												
												if( $i > 0 ){
													
													$class='input-group-section-row'. ( $this->parent->license->is_valid() ? '' : '');
												}
												else{
													
													$class='input-group-section-row'. ( $this->parent->license->is_valid() ? '' : '');
												}											
												
												$html .= '<div class="'.$class.' '.$field['id'].'-section-row" style="display:'.( $i == 0 ? 'none' : 'inline-block' ).';width:97%;background: rgb(255, 255, 255);">';
												
													$html .= '<input type="text" placeholder="Section" style="width:40%;float:left;margin-bottom:5px;height:27px;margin-right: 5px;" name="'.$option_name.'[chapter][][section]" value="'.$section.'"/>';
													$html .= '<input type="url" placeholder="http://" style="width:30%;float:left;margin-bottom:5px;height:27px;margin-right: 5px;" name="'.$option_name.'[chapter][][url]" value="'.( isset($urls[$i]) ? $urls[$i] : '' ).'"/>';
													$html .= '<input type="number" min="1" placeholder="page" style="width:10%;float:left;margin-bottom:5px;height:27px;margin-right: 5px;" name="'.$option_name.'[chapter][][page]" value="'.( isset($pages[$i]) ? $pages[$i] : '' ).'"/>';
												
													if( $i > 0 ){ 
														
														$html .= '<a class="move-up-input-group-section input-group-section-btn" href="#">↑</a> ';
														$html .= '<a class="move-down-input-group-section input-group-section-btn" href="#">↓</a> ';
														$html .= '<a class="remove-input-group-section input-group-section-btn" href="#">x</a> ';
													}
																	
												$html .= '</div>';
											}
										
										$html .= '</div>';
									}
									else{
										
										$html .= '<textarea'.( $e > 0 ? ' id="'.$field['id'].'-content-'.$e.'"' : '' ).' placeholder="Sections (one per line)" name="'.$option_name.'[sections][]" style="width:75%;height:150px;">' . $sections . '</textarea>';
										$html .= '<input type="hidden" name="'.$option_name.'[pages][]" value="'.$pages.'"/>';
										$html .= '<input type="hidden" name="'.$option_name.'[urls][]" value="'.$urls.'"/>';
									}
									

								$html .= '</div>';

							$html .= '</li>';						
						}
					
					$html .= '</ul>';					
					
				$html .= '</div>';

			break;

		}

		switch( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				if ( ! $post ) {
					$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
				}

				$html .= '<span class="description">' . $field['description'] . '</span>' . "\n";

				if ( ! $post ) {
					$html .= '</label>' . "\n";
				}
			break;
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;

	}

	/**
	 * Validate form field
	 * @param  string $data Submitted value
	 * @param  string $type Type of field to validate
	 * @return string       Validated value
	 */
	public function validate_field ( $data = '', $type = 'text' ) {

		switch( $type ) {
			case 'text': $data = esc_attr( $data ); break;
			case 'url': $data = esc_url( $data ); break;
			case 'email': $data = is_email( $data ); break;
		}

		return $data;
	}

	/**
	 * Add meta box to the dashboard
	 * @param string $id            Unique ID for metabox
	 * @param string $title         Display title of metabox
	 * @param array  $post_types    Post types to which this metabox applies
	 * @param string $context       Context in which to display this metabox ('advanced' or 'side')
	 * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
	 * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
	 * @return void
	 */
	public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

		// Get post type(s)
		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		// Generate each metabox
		foreach ( $post_types as $post_type ) {
			add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
		}
	}

	/**
	 * Display metabox content
	 * @param  object $post Post object
	 * @param  array  $args Arguments unique to this metabox
	 * @return void
	 */
	public function meta_box_content ( $post, $args ) {

		$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		echo '<div class="custom-field-panel">' . "\n";

		foreach ( $fields as $field ) {

			if ( ! isset( $field['metabox'] ) ) continue;

			if ( ! is_array( $field['metabox'] ) ) {
				$field['metabox'] = array( $field['metabox'] );
			}

			if ( in_array( $args['id'], $field['metabox'] ) ) {
				$this->display_meta_box_field( $field, $post );
			}

		}

		echo '</div>' . "\n";

	}

	/**
	 * Dispay field in metabox
	 * @param  array  $field Field data
	 * @param  object $post  Post object
	 * @return void
	 */
	public function display_meta_box_field ( $field = array(), $post ) {

		if ( ! is_array( $field ) || 0 == count( $field ) ) return;

		$field = '<p class="form-field"><label for="' . $field['id'] . '">' . $field['label'] . '</label>' . $this->display_field( $field, $post, false ) . '</p>' . "\n";

		echo $field;
	}

	/**
	 * Save metabox fields
	 * @param  integer $post_id Post ID
	 * @return void
	 */
	public function save_meta_boxes ( $post_id = 0 ) {

		if ( ! $post_id ) return;

		$post_type = get_post_type( $post_id );

		$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		foreach ( $fields as $field ) {
			
			if ( isset( $_REQUEST[ $field['id'] ] ) ) {
				
				update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
			} 
			else {
				
				update_post_meta( $post_id, $field['id'], '' );
			}
		}
	}

}
