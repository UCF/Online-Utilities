<?php
/**
 * Commands for creating and updating degrees
 **/
class Online_Degrees extends WP_CLI_Command {
	/**
	 * Updates tuition and fee information on degrees
	 *
	 * ## OPTIONS
	 *
	 * <api>
	 * : The url of the tuition and fees feed you want to pull from. (Required)
	 *
	 * ## EXAMPLES
	 *
	 * # Imports tuition for main site degrees.
	 * $ wp mainsite degrees tuition http://www.studentaccounts.ucf.edu/feed/feed.cfm
	 *
	 * @when after_wp_load
	 */
	public function tuition( $args, $assoc_args ) {
		$api = $args[0];

		$import = new Online_Tuition_Fees_Importer( $api );

		try {
			$import->import();
		}
		catch( Exception $e ) {
			WP_CLI::error( $e->getMessage(), $e->getCode() );
		}

		WP_CLI::success( $import->get_stats() );
	}
}
