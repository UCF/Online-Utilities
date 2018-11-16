<?php
/**
 * Handle all plugin configuration here
 **/

define( 'OU_CUSTOMIZER_PREFIX', 'online_' ); // Match Online-Child-Theme prefix to share Customizer sections
define( 'OU_OPTION_DEFAULTS', serialize( array(
	'degree_forms_fallback_email' => 'online@ucf.edu'
) ) );


/**
 * Defines settings and controls used in the WordPress Customizer.
 *
 * @author Jo Dickson
 * @since 2.0.0
 */
function ou_define_customizer_fields( $wp_customize ) {
	// Forms
	$wp_customize->add_setting(
		'degree_forms_fallback_email',
		array(
			'default' => ou_get_option_default( 'degree_forms_fallback_email' ),
			'type'    => 'option'
		)
	);

	$wp_customize->add_control(
		'degree_forms_fallback_email',
		array(
			'type'        => 'text',
			'label'       => 'Fallback Email for Degree Information Forms',
			'description' => 'Email address to send submission notifications to if a degree information request form has no other email address set to send to.  Degree information forms will send to the selected degree\'s contact email, if available, or a landing page\'s custom fallback email, if available, before falling back to this email address.',
			'section'     => OU_CUSTOMIZER_PREFIX . 'forms'
		)
	);
}

add_action( 'customize_register', 'ou_define_customizer_fields', 11 );


/**
 * Add filters which will force get_option() calls to return our
 * defined defaults when necessary
 *
 * @since 2.0.0
 * @author Jo Dickson
 */
function add_option_default_filters() {
	$options = unserialize( OU_OPTION_DEFAULTS );
	foreach ( $options as $option_name => $option_default ) {
		add_filter( 'default_option_{$option_name}', function( $get_option_default, $option, $passed_default ) {
			// If get_option() was passed a unique default value, prioritize it
			if ( $passed_default ) {
				return $get_option_default;
			}
			return $option_default;
		}, 10, 3 );
	}
}

add_option_default_filters(); // TODO wrap in a hook?
