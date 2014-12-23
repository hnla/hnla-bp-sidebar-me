<?php
/**
 * Plugin Name: hnla Buddypress sidebar & login widget
 * Plugin URI:  http://github/hnla
 * Description: Add login form, BP sidebar username,/avater, sitewide messages & notifications loop an adaptation & extension of the BP sidebar function from bp-default.
 * Author:      Hugo Ashmore (hnla) 
 * Author URI:  http://buddypress.org/community/hnla/
 * Version:     1.0
 * Text Domain: hnla
 * Domain Path: 
 * License:     GPLv2 or later (license.txt)
 */
/**
*
* This widget provides a customised version of the BP 'sidebar me' & login form found in bp-default sidebar.php
* The login form is modified slightly from the original with just a simple register link.
*  * Site wide messages are included in logged in view.
*  * Essential profile links displayed.
*  * A customised notifications loop is provided to show the users personal notices of @mentions, messages etc.
*  * Avatar dimension can be set.
*/
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( function_exists('bp_loaded') ) {

// Setup translatable
$hnla_bp_sidebar_me_mo = WP_PLUGIN_DIR . "/hnla_bp_sidebar_me/languages/hnla_sidebar_me-" . get_locale() . ".mo";
if ( file_exists( $hnla_bp_sidebar_me_mo ) )
	load_plugin_textdomain( 'hnla', $hnla_bp_sidebar_me_mo );

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
		
		// Which title should we show, loggedin title or form title?
		if( ! is_user_logged_in() ):
			$box_title = esc_attr( $instance['form_title'] );
		else:
			$box_title = esc_attr( $instance['title'] );
		endif;
		
		echo $before_widget;
		// Only show h# tag if text passed through
		if( ''!== $box_title ) {
		echo $before_title .
		     $box_title .
		     $after_title; 
		}

		// Set avatar dimensions if user value passed, otherwise leave empty for BP defaults
		$hnla_avatar_dimensions = (!empty($instance['avatar_width']) && !empty($instance['avatar_height']) )? 'type=thumb&width=' . esc_attr( $instance['avatar_width'] ) . '&height=' . esc_attr( $instance['avatar_height'] ) . '' : '';
	?>

	
<?php ####### Show the thing on the front end  ######## ?>

	<div id="bp-sidebar-me-login">
	
<?php if ( is_user_logged_in() ) : ?>
 
		<?php do_action( 'bp_before_sidebar_me' ) ?>

			<div id="sidebar-me">
			
				<div id="sidebar-me-user">
			
					<a href="<?php echo bp_loggedin_user_domain() ?>">
					<?php bp_loggedin_user_avatar( $hnla_avatar_dimensions ) ?>
					</a>

					<p class="user-link clearfix"><span class="your-name"><?php echo bp_core_get_userlink( bp_loggedin_user_id() ); ?></span></p>
					<p><a class="logout" href="<?php echo wp_logout_url( bp_get_root_domain() ) ?>"><?php _e( 'Log Out', 'hnla' ) ?></a></p>
				
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
					<?php _e( 'Notifications', 'hnla' ); ?>
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
	
	<?php } //end ul private messages loop ?>		
	
	<?php else: ?>
	
		<p class="notification-title logged-in-user"><?php _e('No New Notifications ', 'hnla') ?></p>
		
	<?php endif; // end if has notices ?>
	<?php endif; // end if showing notices list ?>
	
	<?php 
		 // Display sitewide notices if message component active & check BP_legacy to remove footer sitewide
		if ( bp_is_active( 'messages' )  ) : 
			
			// Remove notices added via WP_footer in BP legacy class if user has enabled widget sitewide notice display
			// but isn't using the BP sitewide notice widget which would deal with removing the footer notice for us.
			if( class_exists('BP_Legacy') && 1 == $instance['sitewide_notice']
				&& ! is_active_widget( false, false, 'bp_messages_sitewide_notices_widget', true ) ) :

				function remove_legacy_sitewide_notices() {
				?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('#sitewide-notice').remove();
						});
					</script>
				<?php
				}
				add_action('wp_footer', 'remove_legacy_sitewide_notices', 9);
				
			endif;
			?>
			
			<?php 
			// Show Sitewide messages if user enabled in widget options
			if( 1 == $instance['sitewide_notice'] ) : ?>

			<div class="sitewide-notice">
			<?php	bp_message_get_notices(); ?>
			</div>

			<?php endif; 
		
		endif; ?>	
	
	<?php do_action( 'bp_sidebar_me' ) ?>
	
		</div><!-- / #user-sidebar-notifications-menu -->
	
	</div><!-- / #sidebar-me -->
	
	<?php do_action( 'bp_after_sidebar_me' ) ?>
 
<?php else: ?>
	
	<?php do_action( 'bp_before_sidebar_login_form' ); ?>
	
	<?php if ( bp_get_signup_allowed() ) : ?>
	
	<div id="login_area">

	<?php //wp_login_form(); // possibly replace BP form with WP one. ?>

		<form name="login-form" id="sidebar-login-form" class="standard-form" action="<?php echo site_url( 'wp-login.php' ); ?>" method="post">
			
			<label for="sidebar-user-login"><?php _e( 'Username', 'hnla' ); ?></label>
			<input type="text" name="log" id="sidebar-user-login" class="input" value="<?php if ( isset( $user_login) ) echo esc_attr(stripslashes($user_login)); ?>" />

			<label for="sidebar-user-pass"><?php _e( 'Password', 'hnla' ); ?></label>
			<input type="password" name="pwd" id="sidebar-user-pass" class="input" value="" />

			<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="sidebar-rememberme" value="forever" /> <?php _e( 'Remember Me', 'hnla' ); ?></label></p>

			<p><a href="<?php echo bp_get_signup_page() ?>">Register</a> | <a href="<?php echo wp_lostpassword_url( get_bloginfo('url') ); ?>"><?php _e('Lost your password?', 'hnla') ?></a></p>
			
			<?php do_action( 'bp_sidebar_login_form' ); ?>
			
			<input type="submit" name="wp-submit" id="sidebar-wp-submit" value="<?php _e( 'Log In', 'hnla' ); ?>" />
					
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
		$instance['sitewide_notice'] = absint( strip_tags( $new_instance['sitewide_notice'] ) );
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
		$sitewide_notice = $instance['sitewide_notice'];
		$profile_links = $instance['profile_links'];
		$avatar_height = strip_tags( $instance['avatar_height'] );
		$avatar_width = strip_tags( $instance['avatar_width'] );
		?>
		<p><?php _e('Title and form title will switch depending on login/logout view, leave empty if no display wanted.', 'hnla'); ?></p>
		<p>
			<label style="display:block;" for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Logged in widget title', 'hnla' ); ?></label>
			<input style="width: 80%" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />			
		</p>
		<p>
			<label style="display:block;" for="<?php echo $this->get_field_id( 'form_title' ); ?>"><?php _e( 'Logged out widget Form Title', 'hnla' ); ?> </label>
			<input style="width: 80%" class="widefat" id="<?php echo $this->get_field_id( 'form_title' ); ?>" name="<?php echo $this->get_field_name( 'form_title' ); ?>" type="text" value="<?php echo esc_attr( $form_title ); ?>" />			
		</p>
		<p>
			<label for="enable-notices-loop"><?php _e( 'Enable Notifications list', 'hnla' ); ?> 
				<input style="width: 20%;" class="widefat" id="enable-notices-loop" name="<?php echo $this->get_field_name( 'notify_list' ); ?>" type="checkbox" value="1" <?php checked( esc_attr( $notify_list ) , 1 , true) ?> />
			</label>
		</p>
		<p>
			<label for="enable-sitewide"><?php _e( 'Enable sitewide Notices - (enabling sitewide also removes BP legacy handling of sitewide notices)', 'hnla' ); ?> 
				<input style="width: 20%;" class="widefat" id="enable-sitewide" name="<?php echo $this->get_field_name( 'sitewide_notice' ); ?>" type="checkbox" value="1" <?php checked( esc_attr( $sitewide_notice ) , 1 , true) ?> />
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

}//close if bp_loaded
?>