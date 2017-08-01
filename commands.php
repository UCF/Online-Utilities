<?php
/*
Plugin Name: Online Utilities
Version: 1.0.1
Author: UCF Web Communications
Description: Utilities for the Online-Theme
*/
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once 'importers/tuition-fees-importer.php';
	require_once 'includes/degrees.php';

	WP_CLI::add_command( 'online degrees', 'Online_Degrees' );
}
