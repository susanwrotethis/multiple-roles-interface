<?php
/*
Plugin Name: Multiple Roles Interface
Plugin URI: https://github.com/susanwrotethis/multiple-roles-interface
Description: WordPress supports multiple roles for users but does not have an interface to manage them. Multiple Roles Interface replaces the Roles list on the user-edit.php page with a list of checkboxes so more than one role may be assigned to a user. Works on multisite.
Version: 1.0
Author: Susan Walker
Author URI: https://susanwrotethis.com/
Text Domain: swt-mri
Domain Path: /lang/
License: GPL v2 or later
*/

/* Language support */
function swt_mri_load_textdomain()
{
  load_plugin_textdomain( 'swt-mri', false, dirname( plugin_basename( __FILE__ ) ).'/lang/' );
}
add_action( 'plugins_loaded', 'swt_mri_load_textdomain' );

/* Enqueue JQuery to remove default form field from user-edit.php
   Ignore multisite network user-edit.php */
function swt_mri_remove_roles_dropdown( $hook )
{
	if ( 'user-edit.php' !== $hook || is_network_admin() ) {
		return;
	};
	wp_enqueue_script( 'swt-mri', plugin_dir_url( __FILE__ ).'js/multi-roles-edit.js', array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', 'swt_mri_remove_roles_dropdown' );

/* Generate checkboxes list of roles for user-edit.php */
function swt_mri_edit_user_profile_roles( $user )
{
	// Return if current user doesn't have user edit capability
	// Return if it's a multisite network user-edit.php page; we don't set roles here
	if ( !current_user_can( 'edit_user', $user->ID ) || is_network_admin() ) {
		return;
	}

	// Get user's roles and available roles for list
	$user_roles = ( array ) $user->roles;
	$roles = get_editable_roles();

	// Use this hook for custom filtering and sorting of roles
	$roles = apply_filters( 'swt_list_multi_roles', $roles );

	// Add nonce, then output available roles as list of checkboxes
	wp_nonce_field( 'swt-mri-update-roles', 'swt_mri_roles_nonce' );
?>
<h2><?php _e( 'User Roles', 'swt-mri' ); ?></h2>
<table class="form-table">
	<tr>
		<th><?php _e( 'Roles', 'swt-mri' ); ?></th>
		<td>
    	<ul>
			<?php foreach( $roles as $role => $attributes ): ?>
      	<li>	
			<input type="checkbox" name="swt_mri_user_roles[]" id="swt-mri-user-roles-<?php echo $role; ?>" value="<?php echo esc_attr( $role ); ?>"
				<?php checked( in_array( $role, $user_roles ) ); ?> />
        	<label for="swt-mri-user-roles-<?php echo $role; ?>"><?php echo translate_user_role( $attributes['name'] ); ?></label>
        </li>
			<?php endforeach; ?>
      </ul>
		</td>
	</tr>
</table>
<?php
}
add_action( 'edit_user_profile', 'swt_mri_edit_user_profile_roles' );

/* Form action: get roles from $_POST on user-edit.php and update roles */
function swt_mri_profile_update_roles( $user_id, $old_user_data )
{
	// Return if current user is trying to change own roles or does not have edit capability
	global $current_user;
	if ( $user_id == $current_user->ID || !current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	
	// Return if if nonce is unset/unverified
	if ( !isset( $_POST['swt_mri_roles_nonce'] ) || !wp_verify_nonce( $_POST['swt_mri_roles_nonce'], 'swt-mri-update-roles' ) ) {
		return;
	}
	
	// Return if multisite network user-edit.php page; we don't set roles here
	if ( is_network_admin() ) {
		return;
	}

	// Get user's new roles from $_POST; don't return if empty
	$user_new_roles = array();
	if ( isset( $_POST['swt_mri_user_roles'] ) ) {
		$user_new_roles = array_map( 'sanitize_key', $_POST['swt_mri_user_roles'] );
	}

	// Check if roles array is empty and default role feature is enabled; if so, assign default role
	// User filter to modify default role assigned
	if ( empty( $user_new_roles ) && defined( 'MULTI_ROLES_EDIT_DEFAULT' ) && MULTI_ROLES_EDIT_DEFAULT ) {
		$user_new_roles = array( get_option( 'default_role' ) );
		$user_new_roles = apply_filters( 'swt_modify_multi_roles_default', $user_new_roles );
	}

	// Get user and remove roles by setting new role as blank
	$user = get_user_by( 'id', $user_id );
	$user->set_role( '' );

	// Add new roles
	foreach( $user_new_roles as $user_new_role ) {
		$user->add_role( $user_new_role );
	}
}
add_action( 'profile_update', 'swt_mri_profile_update_roles', 10, 2 );

/* Unset WP core's Role column on users.php page and add Roles column in its place */
function swt_mri_manage_users_columns( $columns )
{
	// Unset WP core's Role column
	if ( isset( $columns['role'] ) ) {
		unset( $columns['role'] );
	}

	// Add Roles column
	$columns['roles'] = __( 'Roles', 'swt-mri' );

	// Reorder columns; move Posts column to end
	if ( isset( $columns['posts'] ) ) {
		$temp = $columns['posts'];
		unset( $columns['posts'] );
		$columns['posts'] = $temp;
	}

	return $columns;
}
add_filter( 'manage_users_columns', 'swt_mri_manage_users_columns' );

/* Generate content of Roles column on users.php page */
function swt_mri_manage_users_custom_column( $output, $column, $user_id )
{
	// Return unchanged output if not Roles column
	if ( 'roles' !== $column ) {
		return $output;
	}

	// 'None' will appear if user on site has no role
	// WP multisite may hide users with no roles or capabilities
	$output = __( 'None', 'swt-mri' );

	// Get user and user's roles
	$user = get_user_by( 'id', $user_id );
	$user_roles = (array) $user->roles;

	// If user roles found, get site roles
	if ( count( $user_roles ) > 0 ) {
		$roles = wp_roles()->roles;
		$output_roles = array();
		
		// For each user role, find screen name if available and add to $output_roles
		foreach ( $user_roles as $user_role ) {
			if ( isset( $roles[$user_role]['name'] ) ) {
				$output_roles[] = translate_user_role( $roles[$user_role]['name'] );
			} else {
				$output_roles[] = $user_role;
			}
		}
		// Implode for display in Roles column
		$output = implode( ', ', $output_roles );
	}

	// Use this hook for custom filtering of screen output
	$output = apply_filters( 'swt_display_multi_roles', $output );
	return $output;
}
add_filter( 'manage_users_custom_column', 'swt_mri_manage_users_custom_column', 10, 3 );

?>