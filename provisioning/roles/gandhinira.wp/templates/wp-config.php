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
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'bananas' );

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
define( 'AUTH_KEY',         'GCk;eq-5?0^Et40U|p{OLKz1? rxf{:D,Lnlp$&DZ[k^}^0j6Akz4W/C#S3v7f+,' );
define( 'SECURE_AUTH_KEY',  'sX]!XX-V2_HpchQ>xsAfCNUuZX0ER;m7aHcx?nSV`%[hM5^,8AguM&4V2(%d7C_C' );
define( 'LOGGED_IN_KEY',    '$Ug5m9.kg8WV)[*yC&^UX!Li)+@V#881#BTnKPwl^t|EKL<QYDqIlg|xilUgk*;:' );
define( 'NONCE_KEY',        '5F=ylV1vWgYvay:V(a|)IyDBEV*&oD@P?(56S:@gQV=u(Lz`nMDS{OV]4R&{!EYc' );
define( 'AUTH_SALT',        'm_<uACL=1,XPUv@&_$Ql@ Al,{?XX]^1+!TcBM20r)&:$h4VqupOrP,TM6P 0,XO' );
define( 'SECURE_AUTH_SALT', 'o30f12:y<y>/y%Ftv6FeR14H,6Hs)+JSxqHA{5CS[oNd9: j~Ac=!zBn6^t/?OBA' );
define( 'LOGGED_IN_SALT',   'Km&ZCgJ;LJti.kri0eY^vX?r1K7Jr0ux[6xknWuM)yi&GG4z4Dhz5y`8i4TWn@3o' );
define( 'NONCE_SALT',       ')mB:hSp(cF]-jP#v-9LXt7L|ajyETnf?xoNUk-QnkZWq_@AW6$YAwB+SQ;SJc_K]' );

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
