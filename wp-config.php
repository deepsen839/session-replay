<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'phpmyadmin' );

/** Database password */
define( 'DB_PASSWORD', 'deep' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         ':v5k7Nr|mwYw?Mdnje$Q$OQ3^RIZ/~@S@8j$`3,dqMS=DQ=#l1ckKqd:.a)Hq/9~' );
define( 'SECURE_AUTH_KEY',  'nI[>c:*XE>,W[S_m[/3q|A}_-,cp6DBnCrSw7&GSz96UJ| ![9gm`P[*=OcO>hvC' );
define( 'LOGGED_IN_KEY',    '_Ud9e03mpA`6T5)Sd|Ua|Y.s^x%Non!w@g<!4k$|J_3JZ@]6d<&buoh:?EZYAI1%' );
define( 'NONCE_KEY',        'ap7A{XBKr 65p@^>ey7>2A#m%&o}DQnhA(7$J>{?)oMRUG:qopIx9(*f7cksbBRW' );
define( 'AUTH_SALT',        '},~rllpM9j^KBLP}/Rxx49h,v8#V=~<&,IG1,`)$Z,4. X)bH@2u:<I-mPBjpb{S' );
define( 'SECURE_AUTH_SALT', 'zlL:;XS_dF9:NZM&TGQJ;tE(xuLqW(cNPpJoDf#@oIQGdDlC-JHP9fl:~|>_4p}w' );
define( 'LOGGED_IN_SALT',   'G~cj0qPbbaAqx!Rg^gZ|yIA>5x45-eaCgW}!7PB;F;h,;Sk6UsbPLE7Ov1lStAiy' );
define( 'NONCE_SALT',       ';Yo.9T#.e7n-6)=*Zr{Lt0e:sI_l*#Q*V]Ue)q19XY;g<Q^H#}S$)aYOxr%?=FD+' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
