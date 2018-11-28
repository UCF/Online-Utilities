<?php

/**
 * Filter for overriding schedule codes for imported tuition data
 *
 * @author Jo Dickson
 * @since 2.0.0
 * @param mixed $schedule_code  The schedule code string, or null
 * @param object $degree  Degree post
 * @param string $program_type  The degree's primary program type (first program_type term with no children)
 * @param string $plan_code  The degree's plan code
 * @param string $subplan_code  The degree's subplan code
 * @param string $is_online  Whether or not the degree is an online program
 * @param bool $mapped_found  Whether or not a mapped schedule code was found for this degree
 * @return mixed  The schedule code string, or null
 */
function online_tuition_fees_get_schedule_code( $schedule_code, $degree, $program_type, $plan_code, $subplan_code, $is_online, $mapped_found ) {
	$override = get_post_meta( $degree->ID, 'degree_tuition_code', true );

	if ( $override ) {
		$schedule_code = $override;
	}
	elseif ( ! $mapped_found ) {
		// Handle exceptions for online programs. Online uses unique
		// program type names, which we have to accommodate for here.
		// Assume ALL programs imported with this filter applied are
		// online programs.
		if ( $program_type === 'Online Bachelor' ) {
			$schedule_code = 'UOU';
		}
		elseif ( in_array( $program_type, array( 'Online Master', 'Online Certificate', 'Online Doctorate' ) ) ) {
			$schedule_code = 'UOG';
		}
	}

	return $schedule_code;
}

add_filter( 'ucf_tuition_fees_get_schedule_code', 'online_tuition_fees_get_schedule_code', 10, 7 );
