<?php

// Exit if loaded from outside of WP
if ( !defined( 'ABSPATH' ) ) exit;

// JAVASCRIPT ENQUEUING FUNCTION BEGINS HERE /////////////////////////////////////////////
// Enqueue JQuery to remove default form field from user-new.php and user-edit.php
function swt_mri_remove_roles_dropdown( $hook )
{
	// We don't assign roles on the network users pages
	if ( is_network_admin() ) {
		return;
	};
	
	$plugin_url = plugins_url( '/', dirname(__FILE__) );
	
	// Enqueued separately to facilitate modifications for special use cases
	if ( 'users.php' == $hook) {
		wp_enqueue_script( 'swt-mri-users', $plugin_url.'js/users.js', array( 'jquery' ) );
	}
	
	if ( 'user-new.php' == $hook ) {
		wp_enqueue_script( 'swt-mri-new', $plugin_url.'js/user-new.js', array( 'jquery' ) );
	}
	
	if ( 'user-edit.php' == $hook ) {
		wp_enqueue_script( 'swt-mri-edit', $plugin_url.'js/user-edit.js', array( 'jquery' ) );
	}
}
add_action( 'admin_enqueue_scripts', 'swt_mri_remove_roles_dropdown', 2 );

// FUNCTIONALITY SETUP FUNCTIONS BEGIN HERE //////////////////////////////////////////////
// Add custom form field generation actions; ignore network admin on a multisite.
function swt_mri_add_actions()
{
	if ( is_network_admin() ) {
		return;
	}
	add_action( 'edit_user_profile', 'swt_mri_edit_user_roles_field' );
	add_action( 'profile_update', 'swt_mri_profile_update_roles', 10, 2 );
	add_action( 'user_new_form', 'swt_mri_new_user_roles_field' );
	
	if ( is_multisite() ) { // Different new user hook for single site vs. multisite
		add_action( 'add_user_to_blog', 'swt_mri_new_user_roles' );
	} else {
		add_action( 'user_register', 'swt_mri_new_user_roles' );
	}
}
add_action( 'admin_init', 'swt_mri_add_actions' );

// CUSTOM FORM FIELD GENERATION FUNCTIONS BEGIN HERE /////////////////////////////////////
// Create multiple checkboxes field to select multiple roles
function swt_mri_generate_form_field( $user, $form )
{
	global $wp_roles;

	// Add nonce
	wp_nonce_field( 'swt-mri-'.$form.'-roles', 'swt_mri_'.$form.'_roles_nonce' );
	
	// Get user's roles and available roles for list
	$user_roles = array();
	if ( null != $user ) {
		$user_roles = (array) $user->roles;
	}

	// Use filter hook for custom filtering or sorting of roles
	$roles = get_editable_roles();
	$roles = apply_filters( 'swt_mri_list_multi_roles', $roles );
?>
<h2><?php esc_html_e( 'User Roles', 'swt-mri' ); ?></h2>
<table class="form-table">
	<tr>
		<th><?php esc_html_e( 'Roles', 'swt-mri' ); ?></th>
		<td>
			<?php if ( defined( 'SWT_MRI_ADD_FAILSAFE_ROLE' ) && SWT_MRI_ADD_FAILSAFE_ROLE ) { ?>
			<p><?php esc_html_e( 'If you do not assign a role, one will be added automatically.', 'swt-mri' ); ?></p>
			<?php } // End check for defined constant ?>
    		<ul>
			<?php foreach ( $roles as $role => $attributes ) { ?>
      			<li>	
				<input type="checkbox" name="swt_mri_<?php echo $form; ?>_user_roles[]" 
				id="swt-mri-<?php echo $form; ?>-user-roles-<?php echo $role; ?>" 
				value="<?php echo esc_attr( $role ); ?>" <?php checked( in_array( $role, $user_roles ) ); ?> />
				<label for="swt-mri-<?php echo $form; ?>-user-roles-<?php echo $role; ?>">
				<?php echo translate_user_role( $attributes['name'] ); ?></label>
        		</li>
			<?php } // Next $role ?>
      		</ul>
		</td>
	</tr>
</table>
<?php
}

// Add custom form field to user-edit.php.
function swt_mri_edit_user_roles_field( $user )
{
	if ( !current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}
	swt_mri_generate_form_field( $user, 'edit' );
}

// Add custom form field to user-new.php.
function swt_mri_new_user_roles_field( $context = 'add-new-user' )
{
	// This context is a multisite feature only
	if ( 'add-existing-user' == $context ) {
		
		if ( !is_multisite() || !current_user_can( 'promote_users' ) ) {
			return;
		}
		swt_mri_generate_form_field( null, 'promote' );
		
	} else {
	
		if ( !current_user_can( 'create_users' ) ) {
			return;
		}
		swt_mri_generate_form_field( null, 'new' );
	}
}

// FORM FIELD PROCESSING FUNCTIONS BEGIN HERE ////////////////////////////////////////////
// Perform security checks
function swt_mri_run_security_check( $form, $user_id )
{
	global $current_user;
	
	// No user roles assigned on network admin
	if ( is_network_admin() ) {
		return false;
	}
	
	// Nonce unset or unverified
	if ( !isset( $_POST['swt_mri_'.$form.'_roles_nonce'] ) || !wp_verify_nonce( $_POST['swt_mri_'.$form.'_roles_nonce'], 'swt-mri-'.$form.'-roles' ) ) {
		return false;
	}
	
	// Edit form checks
	if ( 'edit' == $form ) {
	
		if ( !current_user_can( 'edit_user', $user_id ) || $current_user->ID == $user_id ) {
				return false;
		}
	} // End edit user form
	
	// New user form checks
	else if ( 'new' == $form ) {
	
		if ( !current_user_can( 'create_users' ) ) {
				return false;
		}
	} // End new user form
	
	// Promote user form checks
	else if ( 'promote' == $form ) {
	
		if ( !current_user_can( 'promote_users' ) || !is_multisite() ) {
				return false;
		}
	} // End promote user form
	
	else {
		return false;
	}

	return true;
}

// Set the roles
function swt_mri_set_roles( $user_id, $form )
{
	// Run nonce and user permissions checks first
	if ( !swt_mri_run_security_check( $form, $user_id ) ) {
		return;
	}
	
	// Get user's new roles from $_POST. An unset value is allowed.
	$user_new_roles = array();
	if ( isset( $_POST['swt_mri_'.$form.'_user_roles'] ) ) {
		$user_new_roles = array_map( 'sanitize_key', $_POST['swt_mri_'.$form.'_user_roles'] );
	}
	
	// Check if roles array is empty and default role feature is enabled; if so, assign default role
	// Use filter hook to modify default role assigned
	if ( empty( $user_new_roles ) && defined( 'SWT_MRI_ADD_FAILSAFE_ROLE' ) && SWT_MRI_ADD_FAILSAFE_ROLE ) {
		$user_new_roles = array( get_option( 'default_role', 'subscriber' ) );
		$user_new_roles = apply_filters( 'swt_mri_modify_multi_roles_default', $user_new_roles, $user_id, $form );
	}

	// Get user and remove roles by setting new role as blank
	$user = get_user_by( 'id', $user_id );
	$user->set_role( '' );

	// Add new roles
	foreach( $user_new_roles as $user_new_role ) {
		$user->add_role( $user_new_role );
	}
}

// Form action: update user roles on user-edit.php
function swt_mri_profile_update_roles( $user_id, $old_user_data )
{	
	swt_mri_set_roles( $user_id, 'edit' );
}

// Form action: add user roles on user-new.php
function swt_mri_new_user_roles( $user_id )
{
	if ( !isset( $_POST['action'] ) ) {
		return;
	}
	
	if ( 'createuser' == $_POST['action'] ) {
		swt_mri_set_roles( $user_id, 'new' );
	}
	
	else if ( 'adduser' == $_POST['action'] ) {
		swt_mri_set_roles( $user_id, 'promote' );
	}
}

// USERS OVERVIEW PAGE FUNCTIONS BEGIN HERE //////////////////////////////////////////////
// Unset WP core's Role column on users.php page and add Roles column in its place.
function swt_mri_manage_users_columns( $columns )
{
	// Unset WP core's Role column
	if ( isset( $columns['role'] ) ) {
		unset( $columns['role'] );
	}

	// Add Roles column
	$columns['roles'] = esc_html__( 'Roles', 'swt-mri' );

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
	$output = esc_html__( 'None', 'swt-mri' );

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

	return $output;
}
add_filter( 'manage_users_custom_column', 'swt_mri_manage_users_custom_column', 10, 3 );