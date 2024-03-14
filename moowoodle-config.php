<?php
define('MOOWOODLE_PLUGIN_TOKEN', 'moowoodle');
define('MOOWOODLE_PLUGIN_VERSION', '3.1.11');
define('MOOWOODLE_PLUGIN_SERVER_URL', 'http://plugins.dualcube.com');
define('MOOWOODLE_MOODLE_PLUGIN_URL', '/auth/moowoodle/login.php?data=');
define('EMU2_I18N_DOMAIN', 'moowoodle');
define('MOOWOODLE_SCRIPT_DEBUG', true);
define('MW_LOGS', (trailingslashit( wp_upload_dir(null, false)['basedir'] ) . 'mw-logs'));
define('MOOWOODLE_PRO_SHOP_URL', 'https://dualcube.com/product/moowoodle-pro/');
define('MOOWOODLE_SETUP_URL', 'https://dualcube.com/docs/moowoodle-set-up-guide/');
define('MOOWOODLE_SUPPORT_URL', 'https://wordpress.org/support/plugin/moowoodle/');
define('MOOWOODLE_DUALCUBE_URL', 'http://dualcube.com');
define('MOOWOODLE_FILE', __FILE__);
define('MOOWOODLE_PLUGIN_URL', trailingslashit(plugins_url('', __FILE__)));
define('MOOWOODLE_PLUGIN_PUTH', trailingslashit(dirname(__FILE__)));
define('MOOWOODLE_PRO_ADV', !defined('MOOWOODLE_PRO_PLUGIN_TOKEN'));
define('MOOWOOLE_PRO_STICKER', MOOWOODLE_PRO_ADV ? '<span class="mw-pro-tag">Pro</span>' : '' );
