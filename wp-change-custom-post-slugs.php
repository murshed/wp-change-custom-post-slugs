<?php
/*
  Plugin Name: CPT Slug Changer
  Plugin URI: http://fahimm.com/plugin/cpt-slug-changer
  Description: The plugin allows to can easily change Custom Post Type slug from WordPress admin panel.
  Version: 1.0
  Author: FahimMurshed
  Author URI: http://www.fahimm.com
 */
  
add_action( 'admin_menu', 'wpccps_th_custom_post_slug_menu' );
function wpccps_th_custom_post_slug_menu() {
    add_options_page( __('CPT Slug Changer', 'wpccps_th_custom_slugs' ), __('CPT Slug Changer', 'wpccps_th_custom_slugs' ), 'manage_options', 'th-wp-change-custom-post-slugs', 'wpccps_th_custom_slugs_plugin_options' );
}
add_action( 'admin_init', 'wpccps_th_custom_post_slug_admin_init' );
function wpccps_th_custom_post_slug_admin_init() {
  
 	

	flush_rewrite_rules();
  	//register_setting( 'wpccps-settings-group', 'th-wp-change-custom-post-slugs-settings', 'wpccps_th_my_settings_validate_and_sanitize' );    
  	register_setting( 'wpccps-settings-group', 'th-wp-change-custom-post-slugs-settings' );
 	
 	add_filter( 'update_option_th-wp-change-custom-post-slugs-settings', 'wpccps_th_flush_rewreite_rules_after_save', 10, 2);
	
  	add_settings_section( 'section-list-of-custom-post-types', __( 'Configure custom slugs', 'wpccps_th_custom_slugs' ), 'wpccps_th_section_1_callback', 'th-wp-change-custom-post-slugs' );
	
	$post_types = get_post_types( array( '_builtin' => false, 'publicly_queryable' => true, 'show_ui' => true ) );
	$settings = (array) get_option( 'th-wp-change-custom-post-slugs-settings' );
	foreach ($post_types as $key => $post_type) {
		add_settings_field( 
			'field-'.$post_type, 
			__( $post_type, 'wpccps_th_custom_slugs' ), 
			'wpccps_th_setting_structure_callback_function', 
			'th-wp-change-custom-post-slugs', 
			'section-list-of-custom-post-types' , 
			array( 'label_for' => $post_type . '_structure_th', 
				'post_type' => $post_type,
				'settings' => $settings
				)
		);
	}
}

function wpccps_th_flush_rewreite_rules_after_save( $old_value, $new_value )
{
    flush_rewrite_rules();
}

function wpccps_th_setting_structure_callback_function($option){
	$post_type  = $option['post_type'];
	$settings   = $option['settings'];
	$name       = $option['label_for'];
	$pt_object  = get_post_type_object( $post_type );
	$slug       = $pt_object->rewrite['slug'];
	$wiwpccps_th_front = $pt_object->rewrite['with_front'];
	$value = '';	
	if( !empty($settings[$post_type] )){
		$value = esc_attr( $settings[$post_type] );
	}


		$disabled = false;
		if ( isset( $pt_object->cptp_permalink_structure ) and $pt_object->cptp_permalink_structure ) {
			$disabled = true;
		}

		global $wp_rewrite;
		$front = substr( $wp_rewrite->front, 1 );
		if ( $front and $wiwpccps_th_front ) {
			$slug = $front . $slug;
		}
?>
		<p>
			<input name="th-wp-change-custom-post-slugs-settings[<?php echo esc_attr( $post_type );?>]" id="<?php echo esc_attr( $name );?>" type="text" class="regular-text code " value="<?php echo esc_attr( $value ) ;?>" <?php  disabled( $disabled, true, true );?> />
			<br />
			<span>Use alphabets only. Leave empty to use default slug.</span>
		</p>
		<?php
}
/* 
 * THE ACTUAL PAGE 
 * */
function wpccps_th_custom_slugs_plugin_options() {
?>
  <div class="wrap">
  <iframe src="https://murshidalam.com/" width="100%" height="250"></iframe>
  <hr />
      <h2><?php _e('Custom Post Type Slug Changer', 'wpccps_th_custom_slugs'); ?></h2>
      <?php 
      $post_types = get_post_types( array( '_builtin' => false, 'publicly_queryable' => true, 'show_ui' => true ) ); 
      if( count($post_types )){
      ?>
      <form action="options.php" method="POST">
        <?php settings_fields('wpccps-settings-group'); ?>
        <?php do_settings_sections('th-wp-change-custom-post-slugs'); ?>
        <?php submit_button(); ?>
      </form>
  <?php }else{
  		echo _e('There is not custom post type registered in the system', 'wpccps_th_custom_slugs');

  	} ?>


  </div>
<?php }
/*
* THE SECTIONS
* Hint: You can omit using add_settings_field() and instead
* directly put the input fields into the sections.
* */
function wpccps_th_section_1_callback() {
	_e( 'You can set the custom slug for following post types. After saving go \'Permalink\' page and click on \'Save Changes\'.', 'wpccps_th_custom_slugs' );
}


/*
* INPUT VALIDATION:
* */
function wpccps_th_my_settings_validate_and_sanitize( $input ) {
	$settings = (array) get_option( 'th-wp-change-custom-post-slugs-settings' );
	
	// if ( $some_condition == $input['field_1_1'] ) {
	// 	$output['field_1_1'] = $input['field_1_1'];
	// } else {
	// 	add_settings_error( 'th-wp-change-custom-post-slugs-settings', 'invalid-field_1_1', 'You have entered an invalid value into Field One.' );
	// }

	return $input;
}	


function wpccps_th_add_custom_rewrite_rule() {
	$settings = (array) get_option( 'th-wp-change-custom-post-slugs-settings' );
	foreach ($settings as $post_type => $slug) {
		if(!empty(  $slug)){
			$args = get_post_type_object($post_type);
			$args->rewrite["slug"] = $slug;
			register_post_type($args->name, $args);
		}
	}

} // end wpccps_th_add_custom_rewrite_rule
add_action('init', 'wpccps_th_add_custom_rewrite_rule');
