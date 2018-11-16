<?php
/**
 * https://gist.github.com/spivurno/7029518
 *
 * Gravity Wiz // Gravity Forms // Map Submitted Field Values to Another Field
 *
 * Usage
 *
 * 1 - Enable "Allow field to be populated dynamically" option on field which should be populated.
 * 2 - In the "Parameter Name" input, enter the merge tag (or merge tags) of the field whose value whould be populated into this field.
 *
 * Basic Fields
 *
 * To map a single input field (and most other non-multi-choice fields) enter: {Field Label:1}. It is useful to note that
 * you do not actually need the field label portion of any merge tag. {:1} would work as well. Change the "1" to the ID of your field.
 *
 * Multi-input Fields (i.e. Name, Address, etc)
 *
 * To map the first and last name of a Name field to a single field, follow the steps above and enter {First Name:1.3} {Last Name:1.6}.
 * In this example it is assumed that the name field ID is "1". The input IDs for the first and last name of this field will always be "3" and "6".
 *
 * # Uses
 *
 *  - use merge tags as post tags
 *  - aggregate list of checked checkboxes
 *  - map multiple conditional fields to a single field so you can refer to the single field for the selected value
 *
 * @version	  1.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/...
 * @copyright 2014 Gravity Wiz
 */
class GWMapFieldToField {

	public $lead = null;

	function __construct( ) {

		add_filter( 'gform_pre_submission_filter', array( $this, 'map_field_to_field' ), 11 );

	}

	function map_field_to_field( $form ) {

		foreach( $form['fields'] as $field ) {

			if( is_array( $field['inputs'] ) ) {
				$inputs = $field['inputs'];
			} else {
				$inputs = array(
					array(
					'id' => $field['id'],
					'name' => $field['inputName']
					)
				);
			}

			foreach( $inputs as $input ) {

				$value = rgar( $input, 'name' );
				if( ! $value )
					continue;

				$post_key = 'input_' . str_replace( '.', '_', $input['id'] );
				$current_value = rgpost( $post_key );

				preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $input['name'], $matches, PREG_SET_ORDER );

				// if there is no merge tag in inputName - OR - if there is already a value populated for this field, don't overwrite
				if( empty( $matches ) )
					continue;

				$entry = $this->get_lead( $form );

				foreach( $matches as $match ) {

					list( $tag, $field_id, $input_id, $filters, $filter ) = array_pad( $match, 5, 0 );

					$force = $filter === 'force';
					$tag_field = RGFormsModel::get_field( $form, $field_id );

					// only process replacement if there is no value OR if force filter is provided
					$process_replacement = ! $current_value || $force;

					if( $process_replacement && ! RGFormsModel::is_field_hidden( $form, $tag_field, array() ) ) {

						$field_value = GFCommon::replace_variables( $tag, $form, $entry );
						if( is_array( $field_value ) ) {
							$field_value = implode( ',', array_filter( $field_value ) );
						}

					} else {

						$field_value = '';

					}

					$value = trim( str_replace( $match[0], $field_value, $value ) );

				}

				if( $value ) {
					$_POST[$post_key] = $value;
				}

			}

		}

		return $form;
	}

	function get_lead( $form ) {

		if( ! $this->lead )
			$this->lead = GFFormsModel::create_lead( $form );

		return $this->lead;
	}

}

new GWMapFieldToField();
