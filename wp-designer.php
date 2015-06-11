<?php

/*
 *
 *	Plugin Name: WP Designer
 *	Plugin URI: http://www.binaryturf.com/wordpress-designer
 *	Description: This plugin helps you to customize any WordPress site regardless of the theme you use.
 *	Version: 2.0
 *	Author: Shivanand Sharma
 *	Author URI: http://www.binaryturf.com/about
 *	License: GPL-2.0+
 *	License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 */


//* Defines plugin's dir constants
define( 'WPD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPD_BASE_DIR', WP_CONTENT_DIR . '/uploads/' . basename( dirname( __FILE__ ) ) );
define( 'WPD_IMG_DIR', WPD_BASE_DIR . '/images' );
define( 'WPD_JS_DIR', WPD_BASE_DIR . '/scripts' );

//* Defines plugin's url constants
define( 'WPD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPD_BASE_URL', WP_CONTENT_URL . '/uploads/' . basename( dirname( __FILE__ ) ) );
define( 'WPD_IMG_DIR_URL', WPD_BASE_URL . '/images' );
define( 'WPD_JS_DIR_URL', WPD_BASE_URL . '/scripts' );

define( 'WPD_SETTINGS', 'wpd-settings' );
define( 'WPD_LANG', 'wpdesigner' );


/** 
 * Main plugin class 
 * This class includes all the init functions and core plugin functions.
 */

class WPDInit {
	
	public $settings_field = WPD_SETTINGS;
	
	public function __construct() {
		
		register_activation_hook( __FILE__, array( $this, 'wpd_activate' ) );
		
		add_action( 'init', array( $this, 'wpd_activate' ) );
		
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wpd_links' ) );
		
		add_action( 'admin_menu', array( $this, 'wpd_settings_menu' ) );
        add_action( 'admin_init', array( $this, 'wpd_register_settings' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'wpd_plugin_styles' ) );
		
		add_action( 'get_header', array( $this, 'wpd_throttle_sass' ) );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'wpd_stylesheet' ), 9990 );
		add_action( 'init', array( $this, 'wpd_functions' ), 20 );
		
	}
	
	
	/**
	 *	Triggers once the plugin is active. Create plugin's base folder, sub-folders and files on plugin activation
	 *
	 *	@define plugin base folder
	 *	@define images folder inside plugin's base folder in uploads directory
	 *	@define scripts folder inside plugin's base folder in uploads directory
	 *	@define function.php and style.css in the plugin's base folder in uploads directory
	 */
	public function wpd_activate() {
		
		/** Check if the plugin's base folder is created in the uploads folder. If not, create one. **/
		if ( !is_dir( WPD_BASE_DIR ) ) {
			wp_mkdir_p( WPD_BASE_DIR );
		}
		
		/** Create subdirectories, once the plugin base folder is created in the uploads folder. **/
		if( is_dir( WPD_BASE_DIR ) ) {
			
			if ( !is_dir( WPD_IMG_DIR ) ) {
				wp_mkdir_p( WPD_IMG_DIR );
			}
			
			if ( !is_dir( WPD_JS_DIR ) ) {
				wp_mkdir_p( WPD_JS_DIR );
			}
			
			/** Create functions.php and style.css files in the plugin's base folder located in the uploads directory **/
			
			$wpd_files = array(
				array(
					'base' 		=> WPD_BASE_DIR,
					'file' 		=> 'functions.php',
					'content' 	=> "<?php\n/******** Place all your wp/php tweaks here ********/\n/******** This is your master functions.php file ******/"
				),
				array(
					'base' 		=> WPD_BASE_DIR,
					'file' 		=> 'style.scss',
					'content' 	=> "/** Place all your custom styles in this file. The CSS for this file will be automatically generated and enqueued at the front-end. For best-practices please refer to https://make.wordpress.org/core/handbook/coding-standards/css/ **/"
				)
			);
			
			foreach ( $wpd_files as $wpd_file ) {
				if ( !file_exists( trailingslashit( $wpd_file['base'] ) . $wpd_file['file'] ) ) {
					if ( $file_handle = @fopen( trailingslashit( $wpd_file['base'] ) . $wpd_file['file'], 'w' ) ) {
						fwrite( $file_handle, $wpd_file['content'] );
						fclose( $file_handle );
					}
				}
			}
		
		}
	
	}

	
	/** Adding the Support and Author links to the plugin in the admin area on the plugins page **/
	public function wpd_links( $links ) {

		$link = array(
			'<a href="'. admin_url( 'options-general.php?page=wp-designer' ) .'">Settings</a>',
			'<a href="https://www.binaryturf.com/forum/wordpress/wp-designer">Support</a>'
		);

		return array_merge( $links, $link );

	}
	
	
	/** Adds admin submenu-item to WordPress Settings menu **/
	public function wpd_settings_menu() {
		
		add_options_page(
			'WP Designer', 
			'WP Designer', 
			'manage_options', 
			'wp-designer', 
			array( $this, 'wpd_settings_page' )
        );
		
	}
	
	
	/** Registers the settings for plugin's options / settings page **/
	public function wpd_register_settings() {        
        
		register_setting( $this->settings_field, $this->settings_field, array( $this, 'wpd_validate' ) );
		
		/** Sets options defaults on plugin activation (saves the options in the database) **/
		$default_settings = $this->wpd_defaults();
		add_option( $this->settings_field, $default_settings );

		add_settings_section(
            'wpd-section-debug',
            'Debug Tools',
            array( $this, 'wpd_section_debug' ),
            'wp-designer'
        );

		add_settings_field(
            'wpd-include-func',
            'Disable functions?',
            array( $this, 'wpd_func_field' ),
            'wp-designer',
            'wpd-section-debug',
			array( 'label_for' => WPD_SETTINGS . '[include_func]' )
        );
		
        add_settings_field(
			'wpd-include-style', 
			'Disable styles?', 
			array( $this, 'wpd_style_field' ), 
			'wp-designer',
			'wpd-section-debug',
			array( 'label_for' => WPD_SETTINGS . '[include_styles]' )
        );
		
    }
	
	public function wpd_plugin_styles() {
		
		$screen = get_current_screen();
		if( $screen->id == 'settings_page_' . basename( dirname( __FILE__ ) ) ) {
			wp_enqueue_style( 'wpd-stylesheet', WPD_PLUGIN_URL . '/css/wpd-style.css' );
		}
		
	}
	
	
	/** Render the plugin settings page **/
	public function wpd_settings_page() {
		
		if( !current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

		else {
			
			?>
			<div class="wrap">
				<h2>WP Designer</h2>
				
				<form method="post" action="options.php">
				<?php
					$this->option = get_option( WPD_SETTINGS );
					settings_fields( $this->settings_field );
					?>
					<div class="wpd-intro">
						<h3>Say Hello to WP Designer</h3>
						<p>On activation, WP Designer automatically creates a <code>wp-designer</code> folder in the uploads directory. The <code>wp-designer</code> folder includes all the necessary files and folders required for designing the site. Here's a quick list of files and folders (along with their path):</p>
						<ul class="wpd-ul">
							<li><strong>Images Folder:</strong> Upload all your graphics here: <code><?php echo WPD_IMG_DIR; ?></code></li>
							<li><strong>Scripts Folder:</strong> Upload the required scripts here: <code><?php echo WPD_JS_DIR; ?></code></li>
							<li><strong>functions.php:</strong> Add all the code snippets and php tweaks to functions.php here: <code><?php echo WPD_BASE_DIR . '/functions.php'; ?></code></li>
							<li><strong>style.scss:</strong> The right way to style or design your site is through a theme or a child-theme. The style.scss however is provided just in case there are custom elements or markup that needs some miscellaneous tweaks. Place all your SASS customizations here and WP Designer will automatically handle the CSS generation for the front-end. You can place such CSS customizations here: <code><?php echo WPD_BASE_DIR . '/style.scss'; ?></code></li>
						</ul>
						<p>You can use the options given below to enable or disable the plugin functions and styles for debugging purposes.</p>
						<p><em><strong>Note:</strong> If you have made any customizations using Wordpress in-built Customizer, they may not be overriden owing to CSS priority or specificity.</em></p>
					</div>
					<?php
					do_settings_sections( 'wp-designer' );
					submit_button();
				?>
				</form>
				
			</div>
			<?php
			
		}
		
	}
	
	
	/** Defines validation for plugin settings. The plugin options are stored in a single array. Checks for the new values, saves defaults if empty **/
	public function wpd_validate( $input ) {
	
		$defaults = $this->wpd_defaults();
		
		if ( !is_array( $input ) ) //Check if valid input
			return $defaults;

		$output = array();

		foreach ( $defaults as $key=>$value ) {
			
			if ( empty ( $input[$key] ) ) {
				$output[$key] = $value;
			}		
			else {
				$output[$key] = $input[$key];
			}
			
		}
		
		return $output;
		
	}
	
	
	/** Defines the default options for plugin **/
	public function wpd_defaults() {
		
		$defaults = array(
			
			'include_func' 		=> 0,
			'include_styles' 	=> 0,
		
		);
		
		return $defaults;
		
	}

	
	/** Render the Debug Tools section **/
	public function wpd_section_debug() {
		
		?>		
			<p class="wpd-description">These options allow you to enable or disable the functions.php and the style.css created by the plugin. These options come in handy for debugging purposes.</p>
		<?php
		
	}
	
	
	/** Renders checkbox for disabling functions.php **/
	public function wpd_func_field() {
		
		?>
			<input name="<?php $this->wpd_get_option( 'include_func' ); ?>" id="<?php $this->wpd_get_option( 'include_func' ); ?>" type="checkbox" value="1" <?php checked( 1, $this->wpd_get_option_val( 'include_func' ), true ) ?> />
		<?php
		
	}
	
	
	/** Renders checkbox for disabling style.css **/
	public function wpd_style_field() {
		
		?>
			<input name="<?php $this->wpd_get_option( 'include_styles' ); ?>" id="<?php $this->wpd_get_option( 'include_styles' ); ?>" type="checkbox" value="1" <?php checked( 1, $this->wpd_get_option_val( 'include_styles' ), true ) ?> />
		<?php

	}
	
	
	/**
	 *	Define a function to output settings field for the name and ID attributes of input fields	
	 */
	public function wpd_get_option( $field ) {

		printf( '%s[%s]', WPD_SETTINGS, $field );
		
	}
	
	/**
	 *	Define a function to retrieve the values of the settings field from the database
	 */
	public function wpd_get_option_val( $field ) {
		
		$options = get_option( WPD_SETTINGS );
		$opval = '';
		
		if( $options ) {
			$opval = $options[$field];
		}
		else {
			return $opval;
		}
		
		return $opval;		

	}
	
	
	/**
	 *	Compile the SCSS to CSS for the front-end display
	 *	Compiled CSS to be written to autogenerated.css and enqueued to the front-end
	 */	
	public function wpd_throttle_sass() {
		
		if( !class_exists( 'scssc' ) )
			require_once( WPD_PLUGIN_DIR . '/lib/scssphp/scss.inc.php' );
		
		$wpd_scss = WPD_BASE_DIR . '/style.scss';
		
		if( file_exists( $wpd_scss ) ) {
			
			if( !current_user_can( 'update_themes' ) ) {
				return;
			}
			
			$scss = new scssc();
			$scss->setFormatter( 'scss_formatter' );	
			
			if( !file_exists( WPD_BASE_DIR . '/autogenerated.css' ) || filemtime( WPD_BASE_DIR . '/style.scss' ) > filemtime( WPD_BASE_DIR . '/autogenerated.css' ) ) {

				$css = "@charset \"UTF-8\"; \n\n/*********************************************************************************\n******************** Make all your changes to style.scss **************************\n**** This file will be overwritten by style.scss and your changes will be lost ****\n**********************************************************************************/\n\n";
				$css .= $scss->compile( '@import "' . WPD_BASE_DIR . '/style.scss' . '"' );
				
				file_put_contents( WPD_BASE_DIR . '/autogenerated.css', $css );
				
				if( !is_writable( WPD_BASE_DIR . '/autogenerated.css' ) ) {
					add_action( 'admin_notices', 'wpd_sass_write_failure' );
				}
				
			}
			
		}
		
	}
	
	/**
	 *	Trigger an error notice if autogenerated.css is not writeable
	 */
	public function wpd_sass_write_failure() {
		
		 ?>
			<div class="error">
			<p>File Write Error: The <code>autogenerated.css</code> (or the plugin folder) is not writeable. In order for the SASS compiler to work, please make sure that the plugin folder in uploads directory is writeable so that autogenerated.css can be written to.</p>
			</div>
		<?php
		
	}
	
	/**
	 *	Enqueue plugin's CSS file — style.css (if exists) & autogenerated.css
	 *  Checks if the file is created in the uploads folder
	 *  Also checks if style.scss / style.css is not disabled on the plugin's options / settings page
	 */
	public function wpd_stylesheet() {
	
		$wpd_css = WPD_BASE_DIR . '/style.css';
		$wpd_autocss = WPD_BASE_DIR . '/autogenerated.css';
		
		$wpd_style_disabled = $this->wpd_get_option_val( 'include_styles' );
			
		// Bail if disabled on plugin settings page
		if( $wpd_style_disabled )
			return;
		
		// Enqueue style.css; for all the previous versions
		if( file_exists( $wpd_css ) ) {
			wp_register_style( 'wpd-styles', WPD_BASE_URL . '/style.css' );
			wp_enqueue_style( 'wpd-styles' );
		}
		
		// Enqueue autogenerated.css if exists
		if( file_exists( $wpd_autocss ) ) {
			wp_register_style( 'wpd-sass', WPD_BASE_URL . '/autogenerated.css' );
			wp_enqueue_style( 'wpd-sass' );
		}
	
	}
	
	
	/**
	 *	Enqueue plugin's functions.php file 
	 *  Checks if the file is created in the uploads folder
	 *  Also checks if functions.php is not disabled on the plugin's options / settings page
	 */
	public function wpd_functions() {
		
		$wpd_func_disabled = $this->wpd_get_option_val( 'include_func' );
		$wpd_php_path = WPD_BASE_DIR . '/functions.php';
		
		if( !$wpd_func_disabled && file_exists( $wpd_php_path ) ) {
			include_once( WPD_BASE_DIR . '/functions.php' );
		}
		
	}
	
}

$wpdinit = new WPDInit();