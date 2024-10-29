<?php
/*
Plugin Name: Access Watch
Plugin URI: http://wordpress.org/plugins/access-watch/
Description: Understand precisely your website traffic activity and take actions to improve performance and security.
Author: Access Watch
Version: 2.0.0-end-of-life
Author URI: https://access.watch/
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'ACCESS_WATCH__PLUGIN_VERSION', '2.0.0-end-of-life' );
define( 'ACCESS_WATCH__PLUGIN_FILE', __FILE__ );

defined('ACCESS_WATCH__VENDOR_DIR') || define( 'ACCESS_WATCH__VENDOR_DIR', __DIR__ . '/vendor' );
defined('ACCESS_WATCH__BASE_API_URL') || define( 'ACCESS_WATCH__BASE_API_URL', 'https://api.access.watch/1.1' );
defined('ACCESS_WATCH__BASE_LOG_URL') || define( 'ACCESS_WATCH__BASE_LOG_URL', 'https://log.access.watch/1.1' );

require_once ACCESS_WATCH__VENDOR_DIR . '/autoload.php';

require_once __DIR__ . '/includes/clean-transients.php';
require_once __DIR__ . '/includes/http-response-code.php';
require_once __DIR__ . '/includes/bouncer-cache.php';
require_once __DIR__ . '/includes/bouncer-client.php';
require_once __DIR__ . '/includes/http-time.php';

register_activation_hook( __FILE__, 'access_watch_activation' );

register_deactivation_hook( __FILE__, 'access_watch_deactivation' );

register_uninstall_hook( __FILE__, 'access_watch_uninstall' );

function access_watch_activation() {
	$api_key = access_watch_api_key();
	wp_schedule_event(time() + 3600, 'hourly', 'access_watch_clean');
	wp_schedule_single_event(time() + 1, 'access_watch_post_activation');
}

function access_watch_deactivation() {
	wp_clear_scheduled_hook( 'access_watch_clean' );
	access_watch_transient_delete( true );
}

function access_watch_uninstall() {
	delete_option('access_watch_api_key');
	delete_option('access_watch_api_key_registered');
	delete_option('access_watch_site_id');
	delete_option('access_watch_access_token');
}

add_action( 'access_watch_clean', function() { access_watch_transient_delete(); } );

function access_watch_api_key() {
	$api_key = get_option('access_watch_api_key');

	$api_key_registered = get_option('access_watch_api_key_registered');
	if (empty($api_key_registered) || $api_key_registered != ACCESS_WATCH__PLUGIN_VERSION) {
		access_watch_register_api_key($api_key);
		update_option('access_watch_api_key_registered', ACCESS_WATCH__PLUGIN_VERSION);
	}

	return $api_key;
}

function access_watch_register_api_key($api_key, $email = null) {
	$http_client = access_watch_http_client();

	$email = $email ? $email : get_option('admin_email');
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;
	}

	$data = array(
		'key'      => $api_key,
		'email'    => $email,
		'name'     => get_option('blogname'),
		'site'     => get_option('home'),
		'feedback' => plugins_url('feedback.php', __FILE__),
	);

	$http_client->post(ACCESS_WATCH__BASE_API_URL . '/key/register', $data);
}

function access_watch_get_api_key() {
	$http_client = access_watch_http_client();

	$api_key = $http_client->get(ACCESS_WATCH__BASE_API_URL . '/key');
	if ($api_key) {
		update_option('access_watch_api_key', $api_key);
	}
	return $api_key;
}

function access_watch_access_token() {
	$access_token = get_option('access_watch_access_token');

	if (empty($access_token)) {
		$api_key = access_watch_api_key();
		$http_client = access_watch_http_client($api_key);
		$result = $http_client->get(ACCESS_WATCH__BASE_API_URL . '/wordpress/token');
		if (!empty($result['access_token'])) {
			$access_token = $result['access_token'];
			update_option('access_watch_access_token', $access_token);
		}
		if (!empty($result['site_id'])) {
			update_option('access_watch_site_id', $result['site_id']);
		}
		if (!empty($result['user_email'])) {
			update_option('access_watch_user_email', $result['user_email']);
		}
	}

	return $access_token;
}

function access_watch_site_id() {
	$access_token = access_watch_access_token();

	$site_id = get_option('access_watch_site_id');

	if (empty($site_id)) {
		$api_key = access_watch_api_key();
		$http_client = access_watch_http_client($api_key);
		$result = $http_client->get(ACCESS_WATCH__BASE_API_URL . '/wordpress');
		$site_id = $result['site_id'];
		update_option('access_watch_site_id', $site_id);
	}

	return $site_id;
}

function access_watch_user_email() {
	$access_token = access_watch_access_token();

	$user_email = get_option('access_watch_user_email');

	if (empty($user_email)) {
		$api_key = access_watch_api_key();
		$http_client = access_watch_http_client($api_key);
		$result = $http_client->get(ACCESS_WATCH__BASE_API_URL . '/wordpress');
		$user_email = $result['user_email'];
		update_option('access_watch_user_email', $user_email);
	}

	return $user_email;
}

function access_watch_cache() {
	static $cache;
	if (empty($cache)) {
		$cache = new \Bouncer\Cache\WordpressCache();
	}
	return $cache;
}

function access_watch_http_client($api_key = null) {
	static $http_client;
	if (empty($http_client)) {
		$http_client = new \Bouncer\Http\WordpressClient();
	}
	if ($api_key) {
		$http_client->setApiKey($api_key);
	}
	return $http_client;
}

function access_watch_instance() {
	static $instance;
	if (empty($instance)) {
		$access_watch_api_key = access_watch_api_key();
		if ($access_watch_api_key) {
			$instance = new \AccessWatch\AccessWatch(array(
				'apiKey'     => $access_watch_api_key,
				'baseUrl'    => ACCESS_WATCH__BASE_API_URL,
				'baseLogUrl' => ACCESS_WATCH__BASE_LOG_URL,
				'httpClient' => access_watch_http_client(),
				'cache'      => access_watch_cache(),
				'cookiePath' => COOKIEPATH,
			));
		}
	}
	return $instance;
}

add_action( 'admin_menu', 'access_watch_plugin_menu' );

function access_watch_plugin_menu() {

	$page = add_menu_page(
		$page_title = 'Access Watch',
		$menu_title = 'Access Watch',
		$capability = 'manage_options',
		$menu_slug = 'access-watch-dashboard',
		$function = 'access_watch_dashboard',
		$icon_url = 'dashicons-groups'
	);

	add_action( 'load-' . $page, 'access_watch_admin_assets' );

	add_submenu_page (
		$parent_slug = 'access-watch-dashboard',
		$page_title = 'Access Watch',
		$menu_title = 'Access Watch',
		$capability = 'manage_options',
		$menu_slug = 'access-watch-dashboard',
		$function = 'access_watch_dashboard'
	);

	add_submenu_page (
		$parent_slug = 'access-watch-dashboard',
		$page_title = 'About ‹ Access Watch ',
		$menu_title = 'About',
		$capability = 'manage_options',
		$menu_slug = 'access-watch-about',
		$function = 'access_watch_about'
	);

}

function access_watch_about() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if (isset($_POST['reset'])) {
		delete_option('access_watch_api_key');
		delete_option('access_watch_api_key_registered');
		delete_option('access_watch_site_id');
		delete_option('access_watch_access_token');
	}

	echo '<div class="wrap">';

	echo '<h2>Access Watch</h2>';

	if ($api_key = access_watch_api_key()) {
		echo '<p>API Key: <strong>' . $api_key . '</strong></p>';

		if ($site_id = access_watch_site_id()) {
			echo '<p>Site Id: <strong>' . $site_id . '</strong></p>';
		}

		if ($user_email = access_watch_user_email()) {
			echo '<p>User Email: <strong>' . $user_email . '</strong></p>';
		}

		echo '<form method="POST" action="">';
		echo '<input type="submit" name="reset" value="Reset">';
		echo '</form>';
	} else {
		echo '<p>The plugin is currently unregistered. <a href="' . esc_url( menu_page_url( 'access-watch-dashboard', false ) ) . '">Register an API Key now!</a></p>';
	}

	echo '<hr>';

	echo '<p>If you have any question, you can use <a href="https://wordpress.org/support/plugin/access-watch">the WordPress.org forum</a>, or send us an email: <a href="mailto:wordpress@access.watch">wordpress@access.watch</a>.</p>';

	echo '</div>';
}

function access_watch_dashboard() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$api_key = access_watch_get_api_key();
		update_option('access_watch_api_key', $api_key);
		access_watch_register_api_key($api_key, $_POST['email']);
	}

	if (isset($_POST['api_key']) && preg_match('/[a-f0-9]{32}/i', $_POST['api_key'])) {
		$api_key = $_POST['api_key'];
		update_option('access_watch_api_key', $api_key);
		access_watch_register_api_key($api_key);
	}

	$api_key = access_watch_api_key();

	if (true) {
		echo '<div class="wrap">';

		echo '<h2>Access Watch</h2>';

		echo '<h3>End of life</h3>';

		echo '<p>Thank you for using Access Watch.</p>';

		echo '<p>The Access Watch plugin for WordPress is not supported anymore and the plugin is disabled.</p>';

		echo '<p>Feel free to uninstall the plugin now.</p>';

		echo '<p>Sorry!</p>';

		echo '<hr>';

		echo '<p>If you have any question, you can use <a href="https://wordpress.org/support/plugin/access-watch">the WordPress.org forum</a>, or send us an email: <a href="mailto:wordpress@access.watch">wordpress@access.watch</a>.</p>';

		echo '</div>';

	} elseif (!$api_key) {
		echo '<div class="wrap access_watch access_watch_onboarding">';

		echo '<h3>Welcome to Access Watch</h3>';

		$email = get_option('admin_email');
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$current_user = wp_get_current_user();
			$email = $current_user->user_email;
		}

		echo '<div class="access_watch_onboarding_wrapper">';

		echo '<div class="access_watch_onboarding_new">';
		echo '<h4>Create new API Key</h4>';
		echo '<form method="POST" action="">';
		echo '<input class="text" type="email" name="email" value="' . esc_attr( $email ) .'" size="35">';
		echo '<button class="btn btn-big-ci raised hoverable"><div class="anim"></div><span>create</span></button>';
		echo '</form>';
		echo '</div>';

		echo '<div class="access_watch_onboarding_existing">';
		echo '<h4>Use existing API Key</h4>';
		echo '<form method="POST" action="">';
		echo '<input class="text" type="text" name="api_key" value="" size="35" pattern="[a-f0-9]{32}" placeholder="Your API Key">';
		echo '<button class="btn btn-big-ci raised hoverable"><div class="anim"></div><span>use</span></button>';

		echo '</form>';
		echo '</div>';

		echo '</div>';

		echo '</div>';

	} else {

		echo '<div id="react-main">';
		echo '</div>';

		$site_id = access_watch_site_id();
		$access_token = access_watch_access_token();

		$asset_base_url = plugin_dir_url( ACCESS_WATCH__PLUGIN_FILE );
		$script_url = plugins_url( 'main.js?v=' . ACCESS_WATCH__PLUGIN_VERSION, ACCESS_WATCH__PLUGIN_FILE );

		echo '<script data-asset-base-url="'. $asset_base_url . '" data-site-id="' . $site_id . '" data-access-token="' . $access_token . '"  data-context="wp-plugin" type="text/javascript" src="'. $script_url . '"></script>';
	}
}

function access_watch_admin_assets() {
	 add_action( 'admin_enqueue_scripts', 'access_watch_enqueue_assets' );
}

function access_watch_enqueue_assets() {
		wp_register_style( 'access-watch', plugin_dir_url( __FILE__ ) . 'assets/access-watch.css', array(), ACCESS_WATCH__PLUGIN_VERSION );
		wp_enqueue_style( 'access-watch');
}

function access_watch_notices() {
	global $hook_suffix;
	if ( $hook_suffix == 'plugins.php' && !access_watch_api_key() ) {
		?>
		<div class="updated access_watch access_watch_activate">
			<form action="<?php echo esc_url( menu_page_url( 'access-watch-dashboard', false ) ); ?>" method="POST">
				<button class="btn btn-big-ci raised hoverable">
					<div class="anim"></div>
					<span>Configure your Access Watch account</span>
				</button>
				<p>
					<strong>Almost done!</strong> Configure Access Watch to finally understand your website traffic activity.
				</p>
			</form>
		</div>
		<?php
	}
}

add_action( 'admin_notices', 'access_watch_notices' );

function access_watch_enqueue_scripts() {
	global $hook_suffix;
	if ( $hook_suffix == 'plugins.php' && !access_watch_api_key() ) {
		wp_register_style( 'access-watch.css', plugin_dir_url( __FILE__ ) . 'assets/access-watch.css', array(), ACCESS_WATCH__PLUGIN_VERSION );
		wp_enqueue_style( 'access-watch.css');
	}
}

add_action( 'admin_enqueue_scripts', 'access_watch_enqueue_scripts' );

function access_watch_xmlrpc_message() {
	if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
		include_once(ABSPATH . WPINC . '/class-IXR.php');
		$message = new IXR_Message($GLOBALS['HTTP_RAW_POST_DATA']);
		if ($message->parse()) {
			return $message;
		}
	}
}

function access_watch_xmlrpc_extra() {
	$extra = array();
	$xmlrpc_message = access_watch_xmlrpc_message();
	if ($xmlrpc_message) {
		$extra['xmlrpc_method_name'] = $xmlrpc_message->methodName;
		// Multicall methods
		if ($extra['xmlrpc_method_name'] == 'system.multicall') {
			$multicall_methods = array();
			foreach ($xmlrpc_message->params[0] as $call) {
				$multicall_methods[] = $call['methodName'];
			}
			$extra['xmlrpc_multicall_methods'] = array_unique($multicall_methods);
		}
		// Pingback params
		elseif ($extra['xmlrpc_method_name'] == 'pingback.ping') {
			$extra['xmlrpc_pingback_params'] = $xmlrpc_message->params[0];
		}
	}
	return $extra;
}

function access_watch_post_extra($ignore = array()) {
	return array_diff_key($_POST, array_flip($ignore));
}

function access_watch_start() {
	$access_watch = access_watch_instance();
	if ($access_watch) {
		$access_watch->start();
		$access_watch->initSession();
		$identity = $access_watch->getIdentity();
		$session = $identity->getSession();
		$isBlocked = $session && $session->isBlocked();
		// Authenticated User
		$current_user = wp_get_current_user();
		if ($current_user && $current_user->user_login) {
			$access_watch->addContext('user', array(
				'username' => $current_user->user_login,
			));
			// Stop there for Authenticated Users
			return;
		}
		// For now, we're only throttling/blocking POST requests
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// Block Brute Force Login
			if (strpos($_SERVER['REQUEST_URI'], '/wp-login.php?action=register') !== false) {
				if ($isBlocked || $identity->isBad()) {
					$extra = access_watch_post_extra();
					$access_watch->block('registration_blocked', $extra);
				}
			}
			elseif (strpos($_SERVER['REQUEST_URI'], '/wp-login.php') !== false) {
				if ($isBlocked || $identity->isBad()) {
					$extra = access_watch_post_extra(array('pwd'));
					$access_watch->block('login_blocked', $extra);
				}
			}
			// Block Comment Spam
			if (strpos($_SERVER['REQUEST_URI'], '/wp-comments-post.php') !== false) {
				$extra = access_watch_post_extra();
				if ($isBlocked || $identity->isBad()) {
					$access_watch->block('comment_blocked', $extra);
				}
				else {
					$access_watch->registerEvent('comment', $extra);
				}
			}
			// Block Trackback
			if (strpos($_SERVER['REQUEST_URI'], '/trackback') !== false) {
				if ($isBlocked || $identity->isBad()) {
					$access_watch->block('trackback_blocked');
				}
				else {
					$access_watch->registerEvent('trackback');
				}
			}
			// Block XML-RPC
			if (strpos($_SERVER['REQUEST_URI'], '/xmlrpc.php') !== false) {
				$extra = access_watch_xmlrpc_extra();
				if ($isBlocked || $identity->isBad()) {
					$access_watch->block('xmlrpc_blocked', $extra);
				}
				else {
					$access_watch->registerEvent('xmlrpc', $extra);
				}
			}
			// Users Ultra Membership Plugin support
			// https://wordpress.org/plugins/users-ultra/
			if (strpos($_SERVER['REQUEST_URI'], '/login') !== false) {
				if ($isBlocked || $identity->isBad()) {
					$extra = access_watch_post_extra(array('login_user_pass'));
					$access_watch->block('login_blocked', $extra);
				}
			}
			if (strpos($_SERVER['REQUEST_URI'], '/registration') !== false) {
				if ($isBlocked || $identity->isBad()) {
					$extra = access_watch_post_extra(array('user_pass', 'user_pass_confirm'));
					$access_watch->block('registration_blocked', $extra);
				}
			}
			// Budypress support
			// https://wordpress.org/plugins/buddypress/
			if (strpos($_SERVER['REQUEST_URI'], '/register/') !== false) {
				if ($isBlocked || $identity->isBad()) {
					$extra = access_watch_post_extra(array('signup_password', 'signup_password_confirm'));
					$access_watch->block('registration_blocked', $extra);
				}
			}
		}
		// Block Bad Referers
		$access_watch->blockBadReferers();
		// Block Bad Sessions
		$access_watch->blockBadSessions();
		// If it's suspicious/bad and not alreay blocked, throttle
		if ($identity->isSuspicious() || $identity->isBad()) {
			$access_watch->throttle();
		}
	}
}

// add_action( 'init' , 'access_watch_start' );

function access_watch_login_failed( $username ) {
	if (strpos($_SERVER['REQUEST_URI'], '/xmlrpc.php') !== false) {
		return;
	}
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return;
	}
	$access_watch = access_watch_instance();
	if ($access_watch) {
		$access_watch->registerEvent( 'login_failed', array( 'username' => $username ) );
	}
}

add_action( 'wp_login_failed' , 'access_watch_login_failed', 10, 1 );

function access_watch_login( $user_login, $user ) {
	if (strpos($_SERVER['REQUEST_URI'], '/xmlrpc.php') !== false) {
		return;
	}
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return;
	}
	$access_watch = access_watch_instance();
	if ($access_watch) {
		$username = $user && $user->user_login ? $user->user_login : $user_login;
		$access_watch->registerEvent( 'login_succeeded', array( 'username' => $username ) );
	}
}

add_action( 'wp_login', 'access_watch_login', 10, 2 );

function access_watch_wpcf7_submit( $wpcf7_contact_form, $result ) {
	$access_watch = access_watch_instance();
	if ($access_watch) {
		$identity = $access_watch->getIdentity();
		$extra = access_watch_post_extra();
		if ($identity && $identity->isBad()) {
			$access_watch->block('form_blocked', $extra);
		}
		else {
			$access_watch->registerEvent('form', $extra);
		}
	}
}

add_action( 'wpcf7_submit', 'access_watch_wpcf7_submit', 10, 2 );
