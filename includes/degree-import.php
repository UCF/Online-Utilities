<?php

if ( ! function_exists( 'ou_degree_formatted_post_data' ) ) {
	/**
	 * Unsets the post_title attribute for existing degrees
	 * to ensure the title doesn't get overwritten.
	 *
	 * @author Jim Barnes
	 * @since v2.2.0
	 * @param  array $post_data The incoming post_data
	 * @param  UCF_Degree_Import $degree A reference to the UCF_Degree_Import object
	 * @return array
	 */
	function ou_degree_formatted_post_data( $post_data, $degree ) {
		if ( ! $degree->is_new ) {
			unset( $post_data['post_title'] );
		}

		return $post_data;
	}

	add_filter( 'ucf_degree_formatted_post_data', 'ou_degree_formatted_post_data', 10, 2 );
}

if ( ! function_exists( 'ou_get_catalog_description' ) ) {
	/**
	 * Gets the catalog description to store in post_meta
	 *
	 * @author Jim Barnes
	 * @since v2.2.0
	 * @param  object $program The program data from the UCF Search Service
	 * @param  string $description_type The description type to be imported
	 * @return string
	 */
	function ou_get_catalog_description( $program, $description_type='Catalog Description' ) {
		$retval = '';

		if ( ! class_exists( 'UCF_Degree_Config' ) ) {
			return $retval;
		}

		$description_types = UCF_Degree_Config::get_description_types();

		$catalog_desc_type_id = null;

		if ( $description_types ) {
			foreach( $description_types as $desc_id => $desc_name ) {
				if ( strtolower( $desc_name ) === strtolower( $description_type ) ) {
					$catalog_desc_type_id = $desc_id;
					break;
				}
			}
		}

		$descriptions = $program->descriptions;

		if ( ! empty( $descriptions ) && $catalog_desc_type_id ) {
			foreach( $descriptions as $d ) {
				if ( $d->description_type->id === $catalog_desc_type_id ) {
					$retval = $d->description;
				}
			}
		}

		return $retval;
	}
}

if ( ! function_exists( 'ou_degree_format_post_data' ) ) {
	/**
	 * Formats and returns the post_meta for the imported degree
	 *
	 * @author Jim Barnes
	 * @since v2.2.0
	 * @param  array $meta The post_meta array
	 * @param  object $program The program object from the UCF Search Service
	 * @return array
	 */
	function ou_degree_format_post_data( $meta, $program ) {
		$meta['degree_import_ignore'] = 'on';
		$meta['degree_description'] = ou_get_catalog_description( $program );

		$outcomes      = ou_get_remote_response_json( $program->outcomes );
		$projections   = ou_get_remote_response_json( $program->projection_totals );
		$deadline_data = ou_get_remote_response_json( $program->application_deadlines );

		$meta['degree_avg_annual_earnings'] = isset( $outcomes->latest->avg_annual_earnings ) ?
			$outcomes->latest->avg_annual_earnings :
			null;

		$meta['degree_employed_full_time'] = isset( $outcomes->latest->employed_full_time ) ?
			$outcomes->latest->employed_full_time :
			null;

		$meta['degree_continuing_education'] = isset( $outcomes->latest->continuing_education ) ?
			$outcomes->latest->continuing_education :
			null;

		$meta['degree_outcome_academic_year'] = isset( $outcomes->latest->academic_year_display ) ?
			$outcomes->latest->academic_year_display :
			null;

		$meta['degree_prj_begin_year'] = isset( $projections->begin_year ) ?
			$projections->begin_year :
			null;

		$meta['degree_prj_end_year'] = isset( $projections->end_year ) ?
			$projections->end_year :
			null;

		$meta['degree_prj_begin_employment'] = isset( $projections->begin_employment ) ?
			$projections->begin_employment :
			null;

		$meta['degree_prj_end_employment'] = isset( $projections->end_employment ) ?
			$projections->end_employment :
			null;

		$meta['degree_prj_change'] = isset( $projections->change ) ?
			$projections->change :
			null;

		$meta['degree_prj_change_percentage'] = isset( $projections->change_percentage ) ?
			$projections->change_percentage :
			null;

		$meta['degree_prj_openings'] = isset( $projections->openings ) ?
			$projections->openings :
			null;

		$meta['degree_application_deadlines'] = array();
		if ( isset( $deadline_data->application_deadlines ) ) {
			foreach ( $deadline_data->application_deadlines as $deadline ) {
				$meta['degree_application_deadlines'][] = array(
					'admission_term' => $deadline->admission_term,
					'deadline_type'  => $deadline->deadline_type,
					'deadline'       => $deadline->display
				);
			}
		}

		$meta['degree_application_requirements'] = array();
		if ( isset( $deadline_data->application_requirements ) ) {
			foreach ( $deadline_data->application_requirements as $requirement ) {
				$meta['degree_application_requirements'][] = array(
					'requirement' => $requirement
				);
			}
		}

		return $meta;
	}

	add_filter( 'ucf_degree_get_post_metadata', 'ou_degree_format_post_data', 10, 2 );
}


if ( ! function_exists( 'ou_filter_existing_post' ) ) {
	/**
	 * Custom filter for finding existing posts
	 * for UCF Online programs
	 *
	 * @author Jim Barnes
	 * @since 2.2.0
	 * @param  WP_Post|null $existing_post The existing post already found
	 * @param  array $args The args used to fetch the post
	 * @param  array $program_types The program types matching the program being imported
	 * @return WP_Post|null
	 */
	function ou_filter_existing_post( $existing_post, $args, $program_types ) {
		if ( $existing_post ) return $existing_post;

		unset( $args['post_parent'] );

		$existing_post = get_posts( $args );
		$existing_post = empty( $existing_post ) ? null : $existing_post[0];

		return $existing_post;
	}

	add_filter( 'ucf_degree_existing_post', 'ou_filter_existing_post', 10, 3 );
}

if ( ! function_exists( 'ou_transform_api_results' ) ) {
	function ou_transform_api_results( $results ) {
		foreach( $results as $result ) {
			$result->parent_program = null;
		}

		return $results;
	}

	add_filter( 'ucf_degree_api_results', 'ou_transform_api_results', 10, 1 );
}

if ( ! function_exists( 'ou_default_program_types' ) ) {
	/**
	 * Returns the default program types for UCF Online
	 *
	 * @author Jim Barnes
	 * @since v2.2.0
	 * @param  array The array of default program types from the degree importer
	 * @return array The filtered array
	 */
	function ou_default_program_types( $program_types ) {
		return array(
			'Online Bachelor',
			'Online Master',
			'Online Certificate',
			'Online Doctorate'
		);
	}

	add_filter( 'ucf_degree_default_program_types', 'ou_default_program_types', 10, 1 );
}

if ( ! function_exists( 'ou_degree_program_types' ) ) {
	/**
	 * Assigns the appropriate program type per incoming program
	 *
	 * @author Jim Barnes
	 * @since v2.2.0
	 * @param  array The precomputed program types from the search service
	 * @param  string $career Whether the degree is an undergraduate or graduate degree
	 * @param  string $level The level of the degree, e.g. Bachelor, Master, etc.
	 * @return array The list of program types to assign
	 */
	function ou_degree_program_types( $program_types, $career, $level ) {
		$program_types = array();

		switch( $level ) {
			case 'Bachelors':
				$program_types[] = 'Online Bachelor';
				break;
			case 'Masters':
				$program_types[] = 'Online Master';
				break;
			case 'Certificate':
				$program_types[]  = 'Online Certificate';
				break;
			case 'Doctoral':
				$program_types[] = 'Online Doctorate';
				break;
		}

		return $program_types;
	}

	add_filter( 'ucf_degree_assign_program_types', 'ou_degree_program_types', 10, 3 );
}
