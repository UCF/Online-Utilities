<?php

/**
 * Returns a Degree post object used in the given degree-related
 * Gravityform.
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 * @param int $form_id The ID of the Gravityform
 * @return mixed WP_Post object for the degree, or null
 */
function ou_get_degree_from_form_id( $form_id ) {

	// only proceed if Gravity Forms is enabled.
	if ( class_exists( 'GFAPI' ) ) {
		$form = GFAPI::get_form( $form_id );
		foreach ( $form['fields'] as $field ) {
			if ( $field->label === 'Degree' ) {
				$degree_name = $field->defaultValue;
				return get_page_by_title( $degree_name, OBJECT, 'degree' );
			}
		}
	}

	return null;
}


/**
 * Filters the next, previous and submit buttons.
 * Replaces the forms <input> buttons with <button> while maintaining attributes from original <input>.
 *
 * https://www.gravityhelp.com/documentation/article/gform_submit_button/
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 * @param string $button Contains the <input> tag to be filtered.
 * @param object $form Contains all the properties of the current form.
 *
 * @return string The filtered button.
 */
function ou_gf_input_to_button( $button, $form ) {
    $dom = new DOMDocument();
    $dom->loadHTML( $button );
    $input = $dom->getElementsByTagName( 'input' )->item(0);
    $new_button = $dom->createElement( 'button' );
    $value = $dom->createTextNode( $input->getAttribute( 'value' ) );
    $new_button->appendChild( $value );
    $input->removeAttribute( 'value' );
    foreach( $input->attributes as $attribute ) {
        $new_button->setAttribute( $attribute->name, $attribute->value );
    }
    $input->parentNode->replaceChild( $new_button, $input );
    return $dom->saveHTML();
}
add_filter( 'gform_next_button', 'ou_gf_input_to_button', 10, 2 );
add_filter( 'gform_previous_button', 'ou_gf_input_to_button', 10, 2 );
add_filter( 'gform_submit_button', 'ou_gf_input_to_button', 10, 2 );


/**
 * Disable page jump when navigating between multi-step forms.
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 */
add_filter( 'gform_confirmation_anchor', '__return_false' );


/**
 * Adds a Privacy Policy link underneath form pagination and submit buttons.
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 * @param string $input The string containing the <input> tag to be filtered.
 * @param object $form The form currently being processed.
 * @return string
 */
function ou_add_privacy_policy( $input, $form ) {
	ob_start();
?>
	<div>
		<p class="mb-0 mt-3 pull-right small"><a class="privacy-policy-link" href="#" onclick="window.open('https://www.ucf.edu/internet-privacy-policy/','Internet Privacy Policy','resizable,height=750,width=768'); return false;">Privacy Policy</a></p>
	</div>
	<div class="clearfix"></div>
<?php
	return $input . ob_get_clean();
}
add_action( 'gform_submit_button', 'ou_add_privacy_policy', 10, 2 );
add_action( 'gform_next_button', 'ou_add_privacy_policy', 10, 2 );
