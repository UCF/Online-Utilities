<?php
/**
 * General utility functions for UCF Online
 */


/**
 * Appends a tax_query to a standard get_posts() args array.
 *
 * Ported from Online-Theme
 *
 * @since 2.0.0
 * @param array $args Assoc. array of arguments to pass to get_posts()
 * @param string $term The term slug to append to the tax_query
 * @param string $tax The name of the taxonomy to append to the tax_query
 * @return array The modified array of get_posts() arguments
 */
if ( ! function_exists( 'ou_append_degrees_tax_query' ) ) {
    function ou_append_degrees_tax_query( $args, $term, $tax = 'program_types' ) {
        if ( empty( $args['tax_query'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $tax,
                    'field'    => 'slug',
                    'terms'    => $term
                )
            );
        } else {
            if ( ! isset( $args['tax_query']['relationship'] ) ) {
                $args['tax_query']['relationship'] = 'AND';
            }
            $args['tax_query'][] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => $term
            );
        }
        return $args;
    }
}
