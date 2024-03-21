<?php
namespace MooWoodle;

/**
 * MooWoodle Main Class
 *
 * @version		3.1.11
 * @package		MooWoodle
 * @author 		DualCube
 */
defined('ABSPATH') || exit;

class MooWoodle {
	private static $instance = null;
    private $container       = [];
	public function __construct($file) {
        register_activation_hook( $file, [ $this, 'activate' ] );
        register_deactivation_hook( $file, [ $this, 'deactivate' ] );
        add_action( 'admin_menu', [ $this, 'before_moowoodle_load' ] );
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
    
    public function before_moowoodle_load() {
        do_action('before_moodle_load');
        if(is_admin()){
            \add_menu_page(
                "MooWoodle",
                "MooWoodle",
                'manage_options',
                'moowoodle',
                [Settings::class, 'create_settings_page'],
                esc_url(MOOWOODLE_PLUGIN_URL) . 'src/assets/images/moowoodle.png',
                50
		    );
        }
    }

    /**
     * Add High Performance Order Storage Support
     * @return void
     */
    public function declare_compatibility() {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility ( 'custom_order_tables', WP_CONTENT_DIR.'/plugins/moowoodle.moowoodle.php', true );
        
    }

    public function init_plugin() {
        // add link on pugin 'active' button
        if (is_admin() && !defined('DOING_AJAX')) {
            add_filter('plugin_action_links_' . plugin_basename(MOOWOODLE_FILE), [ Helper::class , 'moowoodle_plugin_links']);
        }
        $this->init_hooks();
		// Init Text Domain
		$this->load_plugin_textdomain();
		// Create Log File.
		Helper::MW_log('');
        do_action( 'moowoodle_loaded' );
    }

    private function init_hooks() {
        add_action('init', [$this, 'init_classes']);
        add_action('admin_init', [$this, 'plugin_admin_init']);
    }
    /**
     * Init all MooWoodle classess.
     * Access this classes using magic method.
     * @return void
     */
    public function init_classes() {
		$this->container['Helper'] = new Helper();
		$this->container['Course'] = new Course();
		$this->container['RestAPI'] = new RestAPI();
		$this->container['Template'] = new Template();
		$this->container['Enrollment'] = new Enrollment();
		$this->container['MyAccountEndPoint'] = new MyAccountEndPoint();
		$this->container['Emails'] = new Emails();
		$this->container['Synchronize'] = new Synchronize();
		if(is_admin()){
			$this->container['Settings'] = new Settings();
		    $this->container['TestConnection'] = new TestConnection();
		}

        // echo '<pre>';
        // print_r(var_export(
		// 	$output_array
        //     ,true
        // ));die;

    }
	public function plugin_admin_init() {




		/* Migrate MooWoodle data */
        MooWoodle()->Helper->moowoodle_migration();
	}
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access private
	 * @return void
	 */
	private function load_plugin_textdomain() {
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
