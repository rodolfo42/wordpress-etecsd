<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
 
$isOpenShift = !!getenv('OPENSHIFT_APP_NAME');
 
if($isOpenShift) {
	$dbName = getenv('OPENSHIFT_APP_NAME');
	$dbUser = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
	$dbPass = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
	$dbHost = getenv('OPENSHIFT_MYSQL_DB_HOST') . ':' . getenv('OPENSHIFT_MYSQL_DB_PORT');
} else {
	$dbConfig = (array)json_decode(file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db.json'));
	$dbName = $dbConfig['name'];
	$dbUser = $dbConfig['user'];
	$dbPass = $dbConfig['pass'];
	$dbHost = $dbConfig['host'];
}
 
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', $dbName);

/** MySQL database username */
define('DB_USER', $dbUser);

/** MySQL database password */
define('DB_PASSWORD', $dbPass);

/** MySQL hostname */
define('DB_HOST', $dbHost);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

if($isOpenShift) {
	// This is where we define the OpenShift specific secure variable functions
	require_once(ABSPATH . '../.openshift/openshift.inc');

	// Set the default keys to use
	$_default_keys = array(
		'AUTH_KEY'          => ' w*lE&r=t-;!|rhdx5}vlF+b=+D>a)R:nTY1Kdrw[~1,xDQS]L&PA%uyZ2:w6#ec',
		'SECURE_AUTH_KEY'   => '}Sd%ePgS5R[KwDxdBt56(DM:0m1^4)-k6_p8}|C:[-ei:&qA)j!X`:7d-krLZM*5',
		'LOGGED_IN_KEY'     => '$l^J?o)!zhp6s[-x^ckF}|BjU4d+(g1as)n/Q^s+k|,ZZc@E^h%Rx@VTm|0|?]6R',
		'NONCE_KEY'         => '#f^JM8d^!sVsq]~|4flCZHdaTy.-I.f+1tc[!h?%-+]U}|_8qc K=k;]mXePl-4v',
		'AUTH_SALT'         => 'I_wL2t!|mSw_z_ zyIY:q6{IHw:R1yTPAO^%!5,*bF5^VX`5aO4]D=mtu~6]d}K?',
		'SECURE_AUTH_SALT'  => '&%j?6!d<3IR%L[@iz=^OH!oHRXs4W|D,VCD7w%TC.uUa`NpOH_XXpGtL$A]{+pv9',
		'LOGGED_IN_SALT'    => 'N<mft[~OZp0&Sn#t(IK2px0{KloRcjvIJ1+]:,Ye]>tb*_aM8P&2-bU~_Z>L/n(k',
		'NONCE_SALT'        => 'u E-DQw%[k7l8SX=fsAVT@|_U/~_CUZesq{v(=y2}#X&lTRL{uOVzw6b!]`frTQ|'
	);

	// This function gets called by openshift_secure and passes an array
	function make_secure_key($args) {
		$hash = $args['hash'];
		$key  = $args['variable'];
		$original = $args['original'];

		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$chars .= '!@#$%^&*()';
		$chars .= '-_ []{}<>~`+=,.;:/?|';

		// Convert the hash to an int to seed the RNG
		srand(hexdec(substr($hash,0,8)));
		// Create a random string the same length as the default
		$val = '';
		for($i = 1; $i <= strlen($original); $i++){
			$val .= substr( $chars, rand(0,strlen($chars))-1, 1);
		}
		// Reset the RNG
		srand();
		// Set the value
		return $val;
	}

	// Generate OpenShift secure keys (or return defaults if not on OpenShift)
	$array = openshift_secure($_default_keys,'make_secure_key');

	// Loop through returned values and define them
	foreach ($array as $key => $value) {
		define($key,$value);
	}
} else {
	define('AUTH_KEY',         '-W|kE!dYCxPDuI!TIscXl/k]dAl _)GGH4=ssk&+jk9H5AXGggd]{sG;|FxWT^ !');
	define('SECURE_AUTH_KEY',  '0c8x>rox]C)FQpY%u5*Qj]C^RZG|]YnbwSGw3Ml<$G:-!Je0K4DW.$WQW]/8JHAk');
	define('LOGGED_IN_KEY',    ']..QY}oC^S1WtBKaR_?cgm_b$HH+{)tJN&7`ZFlMn`AtUcOvg?V9nE-]0YxI#.#E');
	define('NONCE_KEY',        'MwqYgh%K_<y91)?$t|o}L`(bks,+7o1HE4?6mWIRKD.&:|GB&F,UR,V)5RnA4<q>');
	define('AUTH_SALT',        'POWP/}Qk#vb4^XC2gg60m+YX^i7;P]`?-I?[|zJi1v1o4]~a)A:.|zl5YeJ/Q1|r');
	define('SECURE_AUTH_SALT', 'O#5N<9wyD>9;``%v@&Qh>u>z v!ON3d*1i1VIx#q=kUyL]DO%5--!D4~zD4y&kkA');
	define('LOGGED_IN_SALT',   'a;W|Qfrf-~v=:&<J>lV#z: hjlH]+(.8tDk6gp<t-UF8&Ls_u}%`!^BW0j.{a$%-');
	define('NONCE_SALT',       ')j3}4{G(xA/$e!{WXt(WXhc=#YuQciVw&tkW_,1jKO]3K{{<QsL-qQtuG}Hf?9V2');
}

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'pt_BR');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/**
 * We prefer to be secure by default. NOT!
 */
define('FORCE_SSL_ADMIN', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
