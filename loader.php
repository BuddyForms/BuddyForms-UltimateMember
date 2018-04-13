<?php
/*
 Plugin Name: BuddyForms Ultimate Member
 Plugin URI: https://themekraft.com/products/ultimate-member/
 Description: Extend Ultimate Member Profiles with BuddyForms
 Version: 1.2.1
 Author: ThemeKraft
 Author URI: https://themekraft.com/buddyforms/
 License: GPLv2 or later
 Network: false

 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */

add_action( 'init', 'buddyforms_ultimate_members_init' );

function buddyforms_ultimate_members_init() {

	// Check if BuddyForms is activated
	if ( ! defined( 'BUDDYFORMS_VERSION' ) ) {
		return;
	}

	// Check if Ultimate Member is activated
	if ( ! class_exists( 'UM' ) ) {
		return;
	}

	// Require all needed files
	require( dirname( __FILE__ ) . '/includes/form-builder.php' );
	require( dirname( __FILE__ ) . '/includes/functions.php' );
	require( dirname( __FILE__ ) . '/includes/redirect.php' );
	require( dirname( __FILE__ ) . '/includes/ultimate-member-extension.php' );

}

//
// Check the plugin dependencies
//
add_action( 'init', function () {

	// Only Check for requirements in the admin
	if ( ! is_admin() ) {
		return;
	}

	// Require TGM
	require( dirname( __FILE__ ) . '/includes/resources/tgm/class-tgm-plugin-activation.php' );

	// Hook required plugins function to the tgmpa_register action
	add_action( 'tgmpa_register', function () {

		// Create the required plugins array
		$plugins['ultimate-member'] = array(
			'name'     => 'Ultimate Member',
			'slug'     => 'ultimate-member',
			'required' => true,
		);

		if ( ! defined( 'BUDDYFORMS_PRO_VERSION' ) ) {
			$plugins['buddyforms'] = array(
				'name'     => 'BuddyForms',
				'slug'     => 'buddyforms',
				'required' => true,
			);
		}
		$config = array(
			'id'           => 'buddyforms-tgmpa',
			// Unique ID for hashing notices for multiple instances of TGMPA.
			'parent_slug'  => 'plugins.php',
			// Parent menu slug.
			'capability'   => 'manage_options',
			// Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,
			// Show admin notices or not.
			'dismissable'  => false,
			// If false, a user cannot dismiss the nag message.
			'is_automatic' => true,
			// Automatically activate plugins after installation or not.
		);

		// Call the tgmpa function to register the required plugins
		tgmpa( $plugins, $config );

	} );
}, 1, 1 );

// Create a helper function for easy SDK access.
function buddyforms_um_fs() {
	global $buddyforms_um_fs;

	if ( ! isset( $buddyforms_um_fs ) ) {
		// Include Freemius SDK.
		if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php' ) ) {
			// Try to load SDK from parent plugin folder.
			require_once dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php';
		} else if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php' ) ) {
			// Try to load SDK from premium parent plugin folder.
			require_once dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php';
		}

		$buddyforms_um_fs = fs_dynamic_init( array(
			'id'                  => '961',
			'slug'                => 'buddyforms-ultimate-member',
			'type'                => 'plugin',
			'public_key'          => 'pk_665b8cfafdebbc7171dd9b787e770',
			'is_premium'          => true,
			// If your addon is a serviceware, set this option to false.
			'has_premium_version' => true,
			'has_paid_plans'      => true,
			'trial'               => array(
				'days'               => 14,
				'is_require_payment' => true,
			),
			'parent'              => array(
				'id'         => '391',
				'slug'       => 'buddyforms',
				'public_key' => 'pk_dea3d8c1c831caf06cfea10c7114c',
				'name'       => 'BuddyForms',
			),
			'menu'                => array(
				'slug'           => 'edit.php?post_type=buddyforms',
				'first-path'     => 'edit.php?post_type=buddyforms&page=buddyforms_welcome_screen',
				'support'        => false,
			),
			// Set the SDK to work in a sandbox mode (for development & testing).
			// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
			'secret_key'          => 'sk_G>msoclc9xO#YCKhVVl4#6$V*p++I',
		) );
	}

	return $buddyforms_um_fs;
}
function buddyforms_um_fs_is_parent_active_and_loaded() {
	// Check if the parent's init SDK method exists.
	return function_exists( 'buddyforms_core_fs' );
}

function buddyforms_um_fs_is_parent_active() {
	$active_plugins = get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$active_plugins         = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
	}

	foreach ( $active_plugins as $basename ) {
		if ( 0 === strpos( $basename, 'buddyforms/' ) ||
		     0 === strpos( $basename, 'buddyforms-premium/' )
		) {
			return true;
		}
	}

	return false;
}

function buddyforms_um_fs_init() {
	if ( buddyforms_um_fs_is_parent_active_and_loaded() ) {
		// Init Freemius.
		buddyforms_um_fs();

		// Parent is active, add your init code here.
	} else {
		// Parent is inactive, add your error handling here.
	}
}

if ( buddyforms_um_fs_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	buddyforms_um_fs_init();
} else if ( buddyforms_um_fs_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'buddyforms_core_fs_loaded', 'buddyforms_um_fs_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	buddyforms_um_fs_init();
}