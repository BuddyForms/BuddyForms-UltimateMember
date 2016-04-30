<?php

//
// Redirect after submit to the correct Ultimate Member Profile Tab
//
function bf_um_after_save_post_redirect($permalink){
  global $buddyforms, $ultimatemember, $post;

  $um_options = get_option('um_options');

  //echo $permalink;

  // echo '<pre>';
  // print_r($_POST);
  // echo '</pre>';


  // Check if the form isi submited form within Ultimate Member and do the redirect to the profile is yes.
  if(isset($um_options['core_user']) && $um_options['core_user'] == $post->ID)  {
    $permalink = get_the_permalink($post->ID) . '?profiletab=product';
  }

  return $permalink;

}
add_filter('buddyforms_after_save_post_redirect', 'bf_um_after_save_post_redirect', 99 ,1);


/**
 * Redirect the user to their respective profile page
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
function bf_ultimate_member_redirect_to_profile() {
	global $post;

	if( ! isset( $post->ID ) || ! is_user_logged_in() )
		return false;

	$link = bf_ultimate_member_get_redirect_link( $post->ID );

	if( ! empty( $link ) ) :
		wp_safe_redirect( $link );
		exit;
	endif;
}
add_action( 'template_redirect', 'bf_ultimate_member_redirect_to_profile', 999 );

/**
 * Get the redirect link
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
function bf_ultimate_member_get_redirect_link( $id = false ) {
	global $buddyforms, $wp_query, $current_user;

	if( ! $id )
		return false;

  // echo '<pre>';
  // print_r($wp_query->query_vars);
  // echo '</pre>';

	if(!isset( $wp_query->query_vars['bf_form_slug']))
		return false;

	$form_slug = $wp_query->query_vars['bf_form_slug'];

	if(!isset($buddyforms[$form_slug]))
		return false;

	$parent_tab = bf_ultimate_member_parent_tab($buddyforms[$form_slug]);

	$link = '';
	if(isset($buddyforms) && is_array($buddyforms) && isset($parent_tab)){

		if(isset($buddyforms[$form_slug]['attached_page']))
			$attached_page_id = $buddyforms[$form_slug]['attached_page'];

		if(isset($buddyforms[$form_slug]['ultimate_members_profiles_integration']) && isset($attached_page_id) && $attached_page_id == $id){

      $um_options = get_option('um_options');

      $current_user = wp_get_current_user();
      $userdata     = get_userdata($current_user->ID);

			$link = get_the_permalink($um_options['core_user']) . $userdata->user_nicename . '?profiletab=' . $parent_tab;

			if(isset($wp_query->query_vars['bf_action'])){
				if($wp_query->query_vars['bf_action'] == 'create')
					$link = get_the_permalink($um_options['core_user']) . $userdata->user_nicename . '?profiletab=' . $parent_tab . '&subnav=form-' . $form_slug;
				if($wp_query->query_vars['bf_action'] == 'edit')
          $link = get_the_permalink($um_options['core_user']) . $userdata->user_nicename . '?profiletab=' . $parent_tab . '&subnav=form-' . $form_slug . '&bf_post_id=' . $wp_query->query_vars['bf_post_id'];
				if($wp_query->query_vars['bf_action'] == 'revision')
          $link = get_the_permalink($um_options['core_user']) . $userdata->user_nicename . '?profiletab=' . $parent_tab . '&subnav=form-' . $form_slug . '&bf_post_id=' . $wp_query->query_vars['bf_post_id'] . '&bf_rev_id=' . $wp_query->query_vars['bf_rev_id'];
					//$link = bp_loggedin_user_domain() . $parent_tab .'/' . $form_slug . '-revision/'.$bp->unfiltered_uri[3].'/'.$bp->unfiltered_uri[4];
				if($wp_query->query_vars['bf_action'] == 'view')
					$link = get_the_permalink($um_options['core_user']) . $userdata->user_nicename . '?profiletab=' . $parent_tab . '&subnav=posts-' . $form_slug;

			}

		}

	}
	return apply_filters( 'bf_ultimate_member_get_redirect_link', $link );
}

/**
 * Link router function
 *
 * @package BuddyForms
 * @since 0.3 beta
 * @uses	bp_get_option()
 * @uses	is_page()
 * @uses	bp_loggedin_user_domain()
 */
function bf_ultimate_member_page_link_router( $link, $id )	{
	if( ! is_user_logged_in() || is_admin() )
		return $link;

	$new_link = bf_ultimate_member_get_redirect_link( $id );

	if( ! empty( $new_link ) )
		$link = $new_link;

	return apply_filters( 'bf_ultimate_member_page_link_router', $link );
}
add_filter( 'page_link', 'bf_ultimate_member_page_link_router', 10, 2 );

function bf_ultimate_member_page_link_router_edit($link, $id){
	global $buddyforms;

	$form_slug = get_post_meta($id, '_bf_form_slug', true);

	if(!$form_slug)
		return $link;

	if(!$buddyforms[$form_slug]['ultimate_members_profiles_integration'])
		return $link;

	$parent_tab = bf_ultimate_member_parent_tab($buddyforms[$form_slug]);

	return '<a title="Edit" id="' . $id . '" class="bf_edit_post" href="' . bp_loggedin_user_domain()  . $parent_tab. '/'. $form_slug .'-edit/' . $id . '">' . __( 'Edit', 'buddyforms' ) .'</a>';
}
add_filter( 'bf_loop_edit_post_link', 'bf_members_page_link_router_edit', 10, 2 );