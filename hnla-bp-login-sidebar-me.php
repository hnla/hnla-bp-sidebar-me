<?php
/**
 * Plugin Name: hnla Buddypress sidebar & login widget
 * Plugin URI:  http://github/hnla
 * Description: Add login form, BP sidebar username,/avater, sitewide messages & notifications loop an adaptation & extension of the BP sidebar function from bp-default.
 * Author:      Hugo Ashmore hnla 
 * Author URI:  http://buddypress.org/community/hnla/
 * Version:     1.0
 * Text Domain: buddypress
 * Domain Path: 
 * License:     GPLv2 or later (license.txt)
 */
/**
*
* This widget provides a customised version of the BP 'sidebar me' & login form found in bp-default sidebar.php
* The login form is modified slightly from the original with just a simple register link.
* Site wide messages are included in logged in view
* A customised notifications loop is provided to show the users personal notices of @mentions, messages etc.
*
*/
 

function hnla_bp_sidebar_me_register_widget() {
	add_action('widgets_init', create_function('', 'return register_widget("HNLA_bp_sidebar_me_Widget");') );
}
add_action( 'plugins_loaded', 'hnla_bp_sidebar_me_register_widget' );

class HNLA_bp_sidebar_me_Widget extends WP_Widget {

	function hnla_bp_sidebar_me_widget() {
		parent::WP_Widget( false, $name = __( 'BP sidebar-me, notifications list, site wide messages & login', 'buddypress' ) );
	}

	function widget( $args, $instance ) {
		$bp = buddypress();

		extract( $args );
		
		// What title should we show, loggedin title or form title?
		if( ! is_user_logged_in() ):
			$box_title = esc_attr( $instance['form_title'] );
		else:
			$box_title = esc_attr( $instance['title'] );
		endif;
		
		echo $before_widget;
		if( ''!== $box_title ) {
		echo $before_title .
		     $box_title .
		     $after_title; 
		}?>

	
<?php ####### Show the thing on the front end  ######## ?>

	<div id="bp-sidebar-me-login">
	
<?php if ( is_user_logged_in() ) : ?>
 
		<?php do_action( 'bp_before_sidebar_me' ) ?>

		<div id="sidebar-me">
			
			<div id="sidebar-me-user">
			
				<a href="<?php echo bp_loggedin_user_domain() ?>">
				<?php bp_loggedin_user_avatar( 'type=thumb&width=' . esc_attr( $instance['avatar_width'] ) . '&height=' . esc_attr( $instance['avatar_height'] ) . '' ) ?>
				</a>

				<p class="user-link clearfix"><span class="your-name"><?php echo bp_core_get_userlink( bp_loggedin_user_id() ); ?></span></p>
				<p><a class="logout" href="<?php echo wp_logout_url( bp_get_root_domain() ) ?>"><?php _e( 'Log Out', 'buddypress' ) ?></a></p>
				
			</div>
			
			<?php if( 1 == $instance['profile_links'] ) { ?>		
			<div id="user-profile-links">
				<ul class="user-links-list">
					<li class="profile-edit-link"><a href="<?php echo bp_core_get_user_domain( bp_loggedin_user_id() ) ?>profile/edit/"><?php _e('Edit your profile', 'hnla'); ?></a></li>
					<li class="settings-edit-link"><a href="<?php echo bp_core_get_user_domain( bp_loggedin_user_id() ) ?>settings/"><?php _e('Change your email or password', 'hnla'); ?></a></li>
					<li class="settings-edit-link"><a href="<?php echo bp_core_get_user_domain( bp_loggedin_user_id() ) ?>profile/change-avatar/"><?php _e('change your Avatar', 'hnla'); ?></a></li>
				</ul>		
			</div>
		 	<?php } ?>
			
			<div id="user-sidebar-notifications">

	<?php if( 1 == $instance['notify_list'] ) : // showing or hidding the list? ?>
	<?php if( $notifications = bp_core_get_notifications_for_user( bp_loggedin_user_id() ) ) : ?>
				
			<h3 class="notification-title logged-in-user">
				<a href="<?php echo bp_loggedin_user_domain(); ?>" title="Go to your account or click the specific notice links below">
					<?php _e( 'Notifications', 'buddypress' ); ?>
 				<span><?php echo count( $notifications ) ?></span>
				</a>
			</h3>
 			
			<?php if ( $notifications ) { ?>
		
			<ul>
			
			<?php			
			$counter = 0;
				foreach($notifications as $notification ){
				$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : ''; ?>
			
				<li<?php echo $alt ?>><?php echo $notification ?></li>
			
				<?php $counter++;			
				}	?>
			
			</ul>
	
	<?php } //end ul notices loop ?>		
	
	<?php else: ?>
	
		<p class="notification-title logged-in-user"><?php _e('No new notifications ', 'hnla') ?></p>
		
	<?php endif; // end if has notices ?>
	<?php endif; // end if showing notices list ?>
	
		<?php if ( bp_is_active( 'messages' ) ) : ?>
		<?php
			// get rid of the horror that is notices splashed across the site via wp_footer
			function remove_legacies_sitewide_notices() {
			?>
				<script type="text/javascript" >
					jQuery(document).ready(function() {
						jQuery('#sitewide-notice').remove();
					});
				</script>
			<?php
			}
			add_action('wp_footer', 'remove_legacies_sitewide_notices');
			?>
			<?php bp_message_get_notices(); /* Site wide notices to all users */ ?>
		<?php endif; ?>	
	
	<?php do_action( 'bp_sidebar_me' ) ?>
	
		</div><!-- / #user-sidebar-notifications-menu -->
	
	</div><!-- / #sidebar-me -->
	
	<?php do_action( 'bp_after_sidebar_me' ) ?>
 
<?php else: ?>
	
	<?php do_action( 'bp_before_sidebar_login_form' ); ?>
	
	<?php if ( bp_get_signup_allowed() ) : ?>
	
	<div id="login_area">
		<form name="login-form" id="sidebar-login-form" class="standard-form" action="<?php echo site_url( 'wp-login.php', 'login_post' ); ?>" method="post">
			
			<label for="sidebar-user-login"><?php _e( 'Username', 'buddypress' ); ?></label>
			<input type="text" name="log" id="sidebar-user-login" class="input" value="<?php if ( isset( $user_login) ) echo esc_attr(stripslashes($user_login)); ?>" />

			<label for="sidebar-user-pass"><?php _e( 'Password', 'buddypress' ); ?></label>
			<input type="password" name="pwd" id="sidebar-user-pass" class="input" value="" />

			<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="sidebar-rememberme" value="forever" /> <?php _e( 'Remember Me', 'buddypress' ); ?></label></p>

			<p><a href="<?php echo bp_get_signup_page() ?>">Register</a> | <a href="<?php echo wp_lostpassword_url( get_bloginfo('url') ); ?>">Lost your password?</a></p>
			
			<?php do_action( 'bp_sidebar_login_form' ); ?>
			
			<input type="submit" name="wp-submit" id="sidebar-wp-submit" value="<?php _e( 'Log In', 'buddypress' ); ?>" />
			<input type="hidden" name="testcookie" value="1" />
		
		</form>
	</div>
	
	<?php do_action( 'bp_after_sidebar_login_form' ); ?>
	
	<?php endif; // end if signup allowed ?>

<?php endif; 

####### We're done showing the thing on the frontend #########
	?>

	<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['form_title'] = sanitize_text_field( $new_instance['form_title'] );
		$instance['notify_list'] = absint( strip_tags( $new_instance['notify_list'] ) );
		$instance['profile_links'] = absint( strip_tags( $new_instance['profile_links'] ) );
		$instance['avatar_height'] = sanitize_text_field( $new_instance['avatar_height'] );
		$instance['avatar_width'] = sanitize_text_field( $new_instance['avatar_width'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'form_title' => '', 'notify_list' => 0, 'profile_links' => 0, 'avatar_height' => '50', 'avatar_width' => '50') );
		$title = strip_tags( $instance['title'] );
		$form_title = strip_tags( $instance['form_title'] );
		$notify_list = $instance['notify_list'];
		$profile_links = $instance['profile_links'];
		$avatar_height = strip_tags( $instance['avatar_height'] );
		$avatar_width = strip_tags( $instance['avatar_width'] );
		?>
		<p><?php _e('Title and form title will switch depending on login/logout view, leave empty if no display wanted.', 'hnla'); ?></p>
		<p>
			<label style="display:block;" for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'hnla' ); ?></label>
			<input style="width: 80%" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />			
		</p>
		<p>
			<label style="display:block;" for="<?php echo $this->get_field_id( 'form_title' ); ?>"><?php _e( 'Login Form Title', 'hnla' ); ?> </label>
			<input style="width: 80%" class="widefat" id="<?php echo $this->get_field_id( 'form_title' ); ?>" name="<?php echo $this->get_field_name( 'form_title' ); ?>" type="text" value="<?php echo esc_attr( $form_title ); ?>" />			
		</p>
		<p>
			<label for="enable-notices-loop"><?php _e( 'Enable Notifications List', 'hnla' ); ?> 
				<input style="width: 20%;" class="widefat" id="enable-notices-loop" name="<?php echo $this->get_field_name( 'notify_list' ); ?>" type="checkbox" value="1" <?php checked( esc_attr( $notify_list ) , 1 , true) ?> />
			</label>
		</p>
		<p>
			<label for="enable-profile-links"><?php _e( 'Enable profile links', 'hnla' ); ?> 
				<input style="width: 20%;" class="widefat" id="enable-profile-links" name="<?php echo $this->get_field_name( 'profile_links' ); ?>" type="checkbox" value="1" <?php checked( esc_attr( $profile_links ) , 1 , true) ?> />
			</label>
		</p>
		<p><?php _e('Manage member avatar width & height - numeric values only i.e 50', 'hnla') ?></p>
		<p>
			<label for="hnla-avatar-height"><?php _e( 'Avatar height', 'hnla' ); ?> 
				<input style="width: 20%;" class="widefat" id="hnla-avatar-height" maxlength="4" name="<?php echo $this->get_field_name( 'avatar_height' ); ?>" type="text" value="<?php echo esc_attr( $avatar_height ); ?>" />
			</label>
		</p>
		<p>
			<label for="hnla-avatar-width"><?php _e( 'Avatar width', 'hnla' ); ?> 
				<input style="width: 20%;" class="widefat" id="hnla-avatar-width" maxlength="4" name="<?php echo $this->get_field_name( 'avatar_width' ); ?>" type="text" value="<?php echo esc_attr( $avatar_width ); ?>" />
			</label>
		</p>			
	<?php
	}
	
}

?>