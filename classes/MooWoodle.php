<?php
namespace MooWoodle;
use Automattic\WooCommerce\Utilities\OrderUtil as WCOrderUtil;

/**
 * MooWoodle Main Class
 *
 * @version		3.1.7
 * @package		MooWoodle
 * @author 		DualCube
 */
defined('ABSPATH') || exit;

class MooWoodle {
	private static $instance = null;
    private $file            = '';
    private $plugin_url      = '';
    private $plugin_path     = '';
    private $container       = [];
	public function __construct($file) {
        register_activation_hook( $file, [ $this, 'activate' ] );
        register_deactivation_hook( $file, [ $this, 'deactivate' ] );
        add_action( 'before_woocommerce_init', [ $this, 'declare_compatibility' ] );
        add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );
        add_action( 'plugins_loaded', [ Helper::class , 'is_woocommerce_loaded_notice'] );
	}

    /**
     * Activation function.
     * @return void
     */
    public function activate() {
        $this->container['install'] = new Installer();

        // flush_rewrite_rules();
    }
	
    /**
     * Deactivation function.
     * @return void
     */
    public function deactivate() {
		// Nothing to write now.
    }

    /**
     * Add High Performance Order Storage Support
     * @return void
     */
    public function declare_compatibility() {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility ( 'custom_order_tables', WP_CONTENT_DIR.'/plugins/moowoodle.moowoodle.php', true );
        
    }

    public function init_plugin() {
        if (is_admin() && !defined('DOING_AJAX')) {
            add_filter('plugin_action_links_' . plugin_basename(MOOWOODLE_FILE), [ Helper::class , 'moowoodle_plugin_links']);
        }
        $this->includes_files();
        $this->init_hooks();
        
        do_action( 'moowoodle_loaded' );
    }

	private function includes_files() {
        
    }

    private function init_hooks() {
        add_action('init', [$this, 'init_classes']);
        add_action('init', [$this, 'plugin_init']);
        add_action('admin_init', [$this, 'plugin_admin_init']);
    }
    /**
     * Init all MooWoodle classess.
     * Access this classes using magic method.
     * @return void
     */
    public function init_classes() {
		$this->container['Course'] = new Course();
		$this->container['Synchronize'] = new Synchronize();
		$this->container['TestConnection'] = new TestConnection();
		$this->container['RestAPI'] = new RestAPI();
		$this->container['Enrollment'] = new Enrollment();
		$this->container['Emails'] = new Emails();
		$this->container['Template'] = new Template();
		if(is_admin()){
			$this->container['admin'] = new Admin();
		}


    }
	public function plugin_admin_init() {
		/* Migrate MooWoodle data */
        if (get_option('dc_moowoodle_plugin_db_version')) {
			if (version_compare(get_option('dc_moowoodle_plugin_db_version'), '3.1.3' ,'<=')) {
				$old_settings = get_option('moowoodle_synchronize_settings');
				if ($old_settings) {
					update_option('moowoodle_synchronize_now', $old_settings);
					delete_option('moowoodle_synchronize_settings');
				}
			}
		}
        update_option('dc_moowoodle_plugin_db_version', MOOWOODLE_PLUGIN_VERSION);
	}

	public function plugin_init() {
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		// Create Log File.
		Helper::MW_log('');
    }
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters('plugin_locale', get_locale(), $this->token);
		load_textdomain('moowoodle', WP_LANG_DIR . "/moowoodle/moowoodle-$locale.mo");
		load_textdomain('moowoodle', MOOWOODLE_PLUGIN_PUTH . "/languages/moowoodle-$locale.mo");
		$locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
		$locale = apply_filters('moowoodle_plugin_locale', $locale, 'moowoodle');
		load_plugin_textdomain('moowoodle', false, plugin_basename(dirname(dirname(__FILE__))) . '/languages');
	}
	/**
     * Magic getter function to get the reference of class.
     * Accept class name, If valid return reference, else Wp_Error. 
     * @param   mixed $class
     * @return  object | \WP_Error
     */
    public function __get( $class ) {
		// file_put_contents( plugin_dir_path(__FILE__) . "/error.log", date("d/m/Y H:i:s", time()) . ":class:  : " . var_export($class, true) . "\n", FILE_APPEND);
        if ( array_key_exists( $class, $this->container ) ) {
            return $this->container[ $class ];
        }
        return new \WP_Error(sprintf('Call to unknown class %s.', $class));
    }
	/**
     * Initializes the MooWoodle class.
     * Checks for an existing instance
     * And if it doesn't find one, create it.
     * @param mixed $file
     * @return object | null
     */
	public static function init($file) {
        if ( self::$instance === null ) {
            self::$instance = new self($file);
        }
        return self::$instance;
    }
}
