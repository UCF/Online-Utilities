<?php
/**
 * General utility functions for UCF Online
 */


/**
 * Returns a Online Utilities option's default value.
 *
 * @since 1.0.0
 * @param string $option_name The name of the option
 * @return mixed Option default value, or false if a default is not set
 **/
function ou_get_option_default( $option_name ) {
	$defaults = unserialize( OU_OPTION_DEFAULTS );
	if ( $defaults && isset( $defaults[$option_name] ) ) {
		return $defaults[$option_name];
	}
	return false;
}


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


/**
 * Parses the information in the Google Analytics cookie
 * and returns it as an array
 *
 * Ported from Online-Theme
 *
 * @author Jim Barnes
 * @return array
 **/
function ou_parse_google_analytics_cookie() {
	$cookie = $_COOKIE['__utmz'];
	$retval = array();
	if ( $cookie ) {
		$pairs = explode( '|', $cookie );
		$pairs[0] = strstr( $pairs[0], 'utmcsr' );
		foreach( $pairs as $pair) {
			list( $k, $v ) = explode( '=', $pair );
			switch( $k ) {
				case 'utmcsr':
					$retval['source'] = $v;
					break;
				case 'utmccn':
					$retval['campaign'] = $v;
					break;
				case 'utmcmd':
					$retval['medium'] = $v;
					break;
				case 'utmcct':
					$retval['content'] = $v;
					break;
				case 'utmctr':
					$retval['term'] = $v;
					break;
			}
		}
	}
	return $retval;
}

if ( ! function_exists( 'ou_get_remote_response' ) ) {
	/**
	 * Helper function for retrieving a remote response
	 *
	 * @author Jim Barnes
	 * @since 1.5.0
	 * @param mixed $remote The URL to retrieve, or a response array from wp_remote_get()
	 * @param int $timeout Timeout for the request, in seconds
	 * @return mixed Array of response data, or null on failure/bad response
	 */
	function ou_get_remote_response( $url, $timeout=5 ) {
		$retval = null;
		if ( ! $url || ! is_string( $url ) ) return $retval;

		$args = array(
			'timeout' => $timeout
		);
		$response = wp_remote_get( $url, $args );

		if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) < 400 ) {
			$retval = $response;
		}

		return $retval;
	}
}

if ( ! function_exists( 'ou_get_remote_response_json' ) ) {
	/**
	 * Helper function for getting remote json
	 *
	 * @author Jim Barnes
	 * @since 3.4.0
	 * @param mixed $remote The URL to retrieve, or a response array from wp_remote_get()
	 * @param mixed $default A default value to return if the response is invalid
	 * @param int $timeout Timeout for the request, in seconds
	 * @return mixed The serialized JSON object, or $default
	 */
	function ou_get_remote_response_json( $remote, $default=null, $timeout=5 ) {
		$retval   = $default;
		$response = is_array( $remote ) ? $remote : ou_get_remote_response( $remote, $timeout );

		if ( $response ) {
			$json = json_decode( wp_remote_retrieve_body( $response ) );
			if ( $json ) {
				$retval = $json;
			}
		}

		return $retval;
	}
}
