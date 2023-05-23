<?php

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL cookie settings

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the installation.

 * You don't have to use the web site, you can copy this file to "wp-config.php"

 * and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * Database settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://wordpress.org/support/article/editing-wp-config-php/

 *

 * @package WordPress

 */


// ** Database settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', 'bitnami_wordpress' );


/** Database username */

define( 'DB_USER', 'bn_wordpress' );


/** Database password */

define( 'DB_PASSWORD', '8d7dc1492527a3e6e96b728cc83a4f71ef58f1d5fa49c0aa9df30774e6c3ba29' );


/** Database hostname */

define( 'DB_HOST', '127.0.0.1:3306' );


/** Database charset to use in creating database tables. */

define( 'DB_CHARSET', 'utf8' );


/** The database collate type. Don't change this if in doubt. */

define( 'DB_COLLATE', '' );


/**#@+

 * Authentication unique keys and salts.

 *

 * Change these to different unique phrases! You can generate these using

 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.

 *

 * You can change these at any point in time to invalidate all existing cookies.

 * This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define( 'AUTH_KEY',         'Hyf,q8I,=8h;A.?a},{IF~>#of~tVbBg3U2z(Ye?uKC(asbX38*(Coa&m1]-1Mm^' );

define( 'SECURE_AUTH_KEY',  'B<|OtpN{3U[)*k,$UVx:9Y-[XC^R6G2XiQ0*`6Agi8n&4da5mb!~a|)`c:dK_l|r' );

define( 'LOGGED_IN_KEY',    'Hl8s]E;WUQ5y;rfm68xTH.#)vpgP#VPEK[gj;TV8:^#jk,ID LY JC<cnYt8sD%m' );

define( 'NONCE_KEY',        '{~,S[}$JihxQU&Z#RDZnwnU0*(0-s:o-C1AIEUBk,37aXjKeC>.8IVJ1JM=)SYaZ' );

define( 'AUTH_SALT',        'W9O~3/*5ko>p(|u;FW,;$8:dvUoVNf_>Uut(bX-C,Xs9V TbnT,>NjTw)c*>5ykg' );

define( 'SECURE_AUTH_SALT', '.C(~KYZ@7v:<0`eJ/ 2~nn#b]N.!Et)sK-yBymLjtpwHzi5L4K{gA>*;wf`@nurH' );

define( 'LOGGED_IN_SALT',   'wco+I;USd[[o[_Ic4O+,;H>#Qb}#fKjjF>R+)`V{e(EI7ngmwjwzJ*E?-DH17QT|' );

define( 'NONCE_SALT',       '81?og}0/(KS  1p4x&acyzB24+`gwAku(1KcLW2|%Xjdy`^-Pis/{TKxq`g6JC?@' );


/**#@-*/


/**

 * WordPress database table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix = 'wp_';


/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 *

 * For information on other constants that can be used for debugging,

 * visit the documentation.

 *

 * @link https://wordpress.org/support/article/debugging-in-wordpress/

 */

define( 'WP_DEBUG', false );

 
/* Add any custom values between this line and the "stop editing" line. */




define( 'FS_METHOD', 'direct' );
/**
 * The WP_SITEURL and WP_HOME options are configured to access from any hostname or IP address.
 * If you want to access only from an specific domain, you can modify them. For example:
 *  define('WP_HOME','https://example.com');
 *  define('WP_SITEURL','https://example.com');
 *
 */
if ( defined( 'WP_CLI' ) ) {
	$_SERVER['HTTP_HOST'] = '127.0.0.1';
}

define('WP_HOME','https://' . $_SERVER['HTTP_HOST'] . '/' );
define('WP_SITEURL','https://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', __DIR__ . '/' );

}


/** Sets up WordPress vars and included files. */

require_once ABSPATH . 'wp-settings.php';

/**
 * Disable pingback.ping xmlrpc method to prevent WordPress from participating in DDoS attacks
 * More info at: https://docs.bitnami.com/general/apps/wordpress/troubleshooting/xmlrpc-and-pingback/
 */
if ( !defined( 'WP_CLI' ) ) {
	// remove x-pingback HTTP header
	add_filter("wp_headers", function($headers) {
		unset($headers["X-Pingback"]);
		return $headers;
	});
	// disable pingbacks
	add_filter( "xmlrpc_methods", function( $methods ) {
		unset( $methods["pingback.ping"] );
		return $methods;
	});
}
