<?php
/**
 * Functions that handle the dynamic population of available form options
 * and field values upon form submission.
 */


/**
 * Modifies GravityForm for Degree Request Info form to populate
 * all available degrees in the available dropdown field and set the
 * default selected value.
 *
 * NOTE: this function assumes that .populate- classes will only ever be used
 * for populating degrees--if other post types need to be made compatible with
 * these classnames, this function will need to be updated.
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 * @param array $form The current form to be filtered
 * @return array The filtered form
 */
if ( ! function_exists( 'ou_forms_populate_degrees' ) ) {
	function ou_forms_populate_degrees( $form ) {
		// Stop now if this form doesn't exist
		if ( ! $form ) { return; }

		$supported_post_types = array(
			'post',
			'page',
			'landing',
			'degree'
		);

		// Existing class names for populating a dropdown with degrees
		$filter_classes = array(
			'populate-degrees',
			'populate-majors',
			'populate-masters',
			'populate-certificates',
			'populate-doctorates'
		);

		// Loop through all fields to find the degrees dropdown:
		foreach ( $form['fields'] as &$field ) {

			if ( ! ( $field->type == 'select' && strpos( $field->cssClass, 'populate-' ) !== false ) ) {
				continue;
			}

			global $post;
			// Try to determine which degree should be pre-selected in the 'degree'
			// dropdown.
			// A degree should only be pre-selected in certain contexts; otherwise the
			// form should pre-select the first option by default.
			$selected_degree = null;

			$default_degree =
				$post
				? get_field( 'default_selected_degree', $post->ID )
				: null;

			if ( $post && $post->post_type == 'degree' ) {
				$selected_degree = $post;
			} else if ( $post && $default_degree ) {
				$selected_degree = $default_degree;
			}

			if ( $post &&
				in_array( $post->post_type, $supported_post_types ) ) {

				// Call the new function
				$populated = ou_forms_populate_rfi_degrees( $form, $post, $field, $selected_degree );

				// If the new function populated the degrees
				// short circuit the rest of the logic.
				if ( $populated ) continue;
			}

			// Populate the 'degree' dropdown with options:
			$args = array(
				'post_type' => 'degree',
				'numberposts' => -1,
				'orderby' => 'title',
				'order' => 'ASC'
			);

			foreach ( explode( ' ', $field->cssClass ) as $class ) {
				// Check for predefined filter class names
				if ( in_array( $class, $filter_classes ) ) {
					switch ( $class ) {
						case 'populate-degrees':
							break;
						case 'populate-majors':
							$args = ou_append_degrees_tax_query( $args, 'online-major' );
							break;
						case 'populate-masters':
							$args = ou_append_degrees_tax_query( $args, 'online-master' );
							break;
						case 'populate-certificates':
							$args = ou_append_degrees_tax_query( $args, 'online-certificate' );
							break;
						case 'populate-doctorates':
							$args = ou_append_degrees_tax_query( $args, 'online-doctorate' );
							break;
					}
				}
				// Try to interpret a compatible taxonomy and term within the
				// classname; expected format is "populate-degrees--TAXNAME--TERMSLUG"
				else {
					$class_tax = $class_term = false;
					if ( strpos( $class, 'populate-degrees--' ) === 0 ) {
						list( $class_tax, $class_term ) = explode( '--', str_replace( 'populate-degrees--', '', $class ), 2 );
					}

					$tax_slug = in_array( $class_tax, get_object_taxonomies( 'degree', 'names' ) ) ? $class_tax : false;
					$term_slug = false;
					if ( $tax_slug ) {
						$term_slug = get_term_by( 'slug', $class_term, $tax_slug );
					}

					if ( $term_slug ) {
						$args = ou_append_degrees_tax_query( $args, $term_slug, $tax_slug );
					}
				}
			}
			if ( $post && $post->post_type === 'page' ) {
				// Force filtered results on specific pages:
				switch ( $post->post_name ) {
					case 'bachelors':
						$args = ou_append_degrees_tax_query( $args, 'online-major' );
						break;
					case 'doctorates':
						$args = ou_append_degrees_tax_query( $args, 'online-doctorate' );
						break;
					case 'masters':
						$args = ou_append_degrees_tax_query( $args, 'online-master' );
						break;
					case 'certificates':
						$args = ou_append_degrees_tax_query( $args, 'online-certificate' );
						break;
					default:
						break;
				}
			}

			$degrees = get_posts( $args );
			$choices = array();

			if ( $degrees ) {
				foreach ( $degrees as $degree ) {
					$attrs = array(
						'text' => $degree->post_title,
						'value' => $degree->post_title,
					);

					if ( $selected_degree && $degree->ID === $selected_degree->ID ) {
						$attrs['isSelected'] = true;
					}
					$choices[] = $attrs;
				}
			}

			$field->choices = $choices;
		}

		return $form;
	}
}

if ( ! function_exists( 'ou_forms_populate_rfi_degrees' ) ) {
	/**
	 * Populates the form fields using the new
	 * ou_rfi fields.
	 *
	 * @author Jim Barnes
	 * @since v2.2.0
	 * @param  array $form The gravity form
	 * @param  WP_Post $post The global post object
	 * @param GF_Field $field The gravity form field
	 * @return bool True if the form field was populated, else false
	 */
	function ou_forms_populate_rfi_degrees( $form, $post, $field, $selected_degree ) {
		$process = get_field( 'ou_rfi_customize_degree_list', $post->ID );

		// Return immediately if the switch is set to false
		if ( ! $process && $post->post_type !== 'degree' ) return false;

		$filter_by = get_field( 'ou_rfi_filter_by', $post->ID );

		$args = array(
			'post_type' => 'degree',
			'numberposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC'
		);

		if ( $process ) {
			switch( $filter_by ) {
				case 'tag':
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'post_tag',
							'terms'    => get_field( 'ou_rfi_tag', $post->ID )
						)
					);
					break;
				case 'college':
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'colleges',
							'terms'    => get_field( 'ou_rfi_colleges', $post->ID )
						)
					);
					break;
				case 'program_type':
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'program_types',
							'terms'    => get_field( 'ou_rfi_program_types', $post->ID )
						)
					);
					break;
				case 'aoi':
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'interests',
							'terms'    => get_field( 'ou_rfi_areas_of_interest', $post->ID )
						)
					);
					break;
				case 'career':
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'career_paths',
							'terms'    => get_field( 'ou_rfi_career_paths', $post->ID )
						)
					);
					break;
				case 'custom':
					$args['post__in'] = get_field( 'ou_rfi_custom_programs', $post->ID );
					break;
				default:
					return false;
			}
		} else if ( ! $process && $post->post_type === 'degree' ) {
			$args['post__in'] = array( $post->ID );
		}

		$degrees = get_posts( $args );
		$choices = array();

		if ( $degrees ) {
			foreach ( $degrees as $degree ) {
				$attrs = array(
					'text' => $degree->post_title,
					'value' => $degree->post_title,
				);

				if ( $selected_degree && $degree->ID === $selected_degree->ID ) {
					$attrs['isSelected'] = true;
				}
				$choices[] = $attrs;
			}
		}

		$field->choices = $choices;
		if ( count( $field->choices ) === 1 ) {
			$field->cssClass .= " gform_hidden";
		}

		return true;
	}
}

if ( ! function_exists( 'ou_hook_forms_populate_degrees' ) ) {
	function ou_hook_forms_populate_degrees() {
		add_filter( 'gform_pre_render', 'ou_forms_populate_degrees' );
		add_filter( 'gform_pre_validation', 'ou_forms_populate_degrees' );
		add_filter( 'gform_pre_submission_filter', 'ou_forms_populate_degrees', 9 );
		add_filter( 'gform_admin_pre_render', 'ou_forms_populate_degrees' );
	}

	ou_hook_forms_populate_degrees();
}


/**
 * Updates input values in the Degree Request Info form
 * based on data submitted by the user.
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 * @param array $form The current form to be filtered
 * @return array The filtered form
 */
if ( ! function_exists( 'ou_forms_set_dynamic_vals' ) ) {
	function ou_forms_set_dynamic_vals( $form ) {
		$field_ids = array();

		foreach( $form['fields'] as $key => $field ) {
			switch( $field->inputName ) {
				case 'contact_email':
					$field_ids['contact_email'] = $field->id;
					break;
				case 'program_type':
					$field_ids['program_type'] = $field->id;
					break;
				case 'degree':
					$field_ids['degree'] = $field->id;
					break;
				case 'degree_id':
					$field_ids['degree_id'] = $field->id;
					break;
				case 'degree_code':
					$field_ids['degree_code'] = $field->id;
					break;
				case 'degree_subplan_code':
					$field_ids['degree_subplan_code'] = $field->id;
					break;
				case 'salesforce_record_id':
					$field_ids['salesforce_record_id'] = $field->id;
					break;
				case 'degree_brochure_document_file':
					$field_ids['degree_brochure_document_file'] = $field->id;
					break;
				case 'ga_source':
					$field_ids['ga_source'] = $field->id;
					break;
				case 'ga_campaign':
					$field_ids['ga_campaign'] = $field->id;
					break;
				case 'ga_medium':
					$field_ids['ga_medium'] = $field->id;
					break;
				case 'ga_content':
					$field_ids['ga_content'] = $field->id;
					break;
				case 'ga_term':
					$field_ids['ga_term'] = $field->id;
					break;
			}
		}

		$ga_cookie = ou_parse_google_analytics_cookie();

		$selected_degree_name = rgpost( 'input_' . $field_ids['degree'] );
		$degree = null;
		$degree_contact_email = $degree_program_type = '';

		if ( $selected_degree_name ) {
			$degree = get_page_by_title( $selected_degree_name, OBJECT, 'degree' );
		}

		if ( $degree ) {
			$degree_contact_email = get_post_meta( $degree->ID, 'degree_contact_email', true );
			$degree_code = get_post_meta( $degree->ID, 'degree_code', true );
			$degree_subplan_code = get_post_meta( $degree->ID, 'degree_subplan_code', true );
			$salesforce_record_id = get_post_meta( $degree->ID, 'salesforce_record_id', true );
			$degree_brochure_document_file = get_field( 'degree_brochure_document_file', $degree->ID );
			$program_types = wp_get_post_terms( $degree->ID, 'program_types' );
			$degree_program_type  = array_shift( $program_types );
		}

		if ( !$degree_contact_email ) {
			// TODO add ability to override this on landing pages
			$degree_contact_email = get_option( 'degree_forms_fallback_email' );
		}

		if ( isset( $field_ids['contact_email'] ) ) {
			$_POST['input_' . $field_ids['contact_email']] = $degree_contact_email;
		}
		if ( ( isset( $field_ids['degree_id'] ) && $degree ) ) {
			$_POST['input_' . $field_ids['degree_id']] = $degree->ID;
		}
		if ( isset( $field_ids['program_type'] ) ) {
			$_POST['input_' . $field_ids['program_type']]  = $degree_program_type->name;
		}
		if ( isset( $field_ids['degree_code'] ) ) {
			$_POST['input_' . $field_ids['degree_code']] = $degree_code;
		}
		if ( isset( $field_ids['degree_subplan_code'] ) ) {
			$_POST['input_' . $field_ids['degree_subplan_code']] = $degree_subplan_code;
		}
		if ( isset( $field_ids['salesforce_record_id'] ) ) {
			$_POST['input_' . $field_ids['salesforce_record_id']] = $salesforce_record_id;
		}
		if ( isset( $field_ids['degree_brochure_document_file'] ) ) {
			$_POST['input_' . $field_ids['degree_brochure_document_file']] = $degree_brochure_document_file;
		}
		if ( isset( $field_ids['ga_source'] ) ) {
			$_POST['input_' . $field_ids['ga_source']] = $ga_cookie['source'];
		}
		if ( isset( $field_ids['ga_campaign'] ) ) {
			$_POST['input_' . $field_ids['ga_campaign']] = $ga_cookie['campaign'];
		}
		if ( isset( $field_ids['ga_medium'] ) ) {
			$_POST['input_' . $field_ids['ga_medium']] = $ga_cookie['medium'];
		}
		if ( isset( $field_ids['ga_content'] ) ) {
			$_POST['input_' . $field_ids['ga_content']] = $ga_cookie['content'];
		}
		if ( isset( $field_ids['ga_term'] ) ) {
			$_POST['input_' . $field_ids['ga_term']] = $ga_cookie['term'];
		}

		return $form;
	}
}

if ( ! function_exists( 'ou_hook_forms_set_dynamic_vals' ) ) {
	function ou_hook_forms_set_dynamic_vals() {
		// NOTE the priority should be greater than the priority for 'ou_forms_populate_degrees'!
		add_filter( 'gform_pre_submission_filter', 'ou_forms_set_dynamic_vals', 10 );
	}
	ou_hook_forms_set_dynamic_vals();
}
