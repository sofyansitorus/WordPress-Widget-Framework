<?php
/**
 * Plugin Name:       Advanced Google Map Widget
 * Plugin URI:        https://github.com/sofyansitorus/Advanced-Google-Map-Widget
 * Description:       Show advanced googe map on your wordpress widget.
 * Version:           1.0.0
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * Text Domain:       agmw
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /lang
 * GitHub Plugin URI: https://github.com/sofyansitorus/Advanced-Google-Map-Widget
 */
 
 // Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}


class AGMW extends WP_Widget {

    /**
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'agmw';

    protected $width = 400;

    protected $height = 350;

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		add_action( 'init', array( $this, 'agmw_textdomain' ) );

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Advanced Google Map', $this->get_widget_slug() ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Show advanced googe map on your wordpress widget.', $this->get_widget_slug() )
			),
			array('width' => $this->width, 'height' => $this->height)
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

	} // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];
		
		// go on with your widget logic, put everything into a string and …


		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		// Process the widget ouput here
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget
	
	
	public function flush_widget_cache() {
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	
	public function update( $new_instance, $old_instance ) {
		return $this->saveFields( $new_instance, $old_instance );
	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$this->buildFields($instance);
	} // end form

	private function getFields(){
		$fields = array();
		$fields[] = array(
			'id' => 'title',
			'type' => 'textarea',
			'label' => __('Title', $this->get_widget_slug()),
			'description' => __('Title', $this->get_widget_slug()),
		);
		return $fields;
	}

	private function parseField($field){
		$defaults = array(
			'id' => '',
			'type' => '',
			'label' => '',
			'description' => '',
			'value' => '',
			'class' => 'widefat'
		);
		return wp_parse_args( $field, $defaults );
	}

	private function getValue($field, $instance){
		return (isset($instance[$field['id']])) ? $instance[$field['id']] : $field['value'];
	}

	private function buildField($field, $instance){
		$output = '';

		$field = $this->parseField( $field );

		if(!empty($field['id'])){
			switch ($field['type']) {
				case 'textbox':
				case 'email':
				case 'url':
				case 'password':
					$output .= '<p>';
					if($field['label']){
						$output .= '<label for="'.$this->get_field_id($field['id']).'">'.$field['label'].'</label>';
					}
					$output .= '<input type="'.$field['type'].'" id="'.$this->get_field_id($field['id']).'" name="'.$this->get_field_name($field['id']).'" value="'.$this->getValue($field, $instance).'" class="'.$field['class'].'">';
					if($field['description']){
						$output .= '<br /><small>'.$field['description'].'</small>';
					}
					$output .= '</p>';
					break;
				case 'textarea':
					$output .= '<p>';
					if($field['label']){
						$output .= '<label for="'.$this->get_field_id($field['id']).'">'.$field['label'].'</label>';
					}
					$output .= '<textarea id="'.$this->get_field_id($field['id']).'" name="'.$this->get_field_name($field['id']).'" class="'.$field['class'].'">'.$this->getValue($field, $instance).'</textarea>';
					if($field['description']){
						$output .= '<br /><small>'.$field['description'].'</small>';
					}
					$output .= '</p>';
					break;
				
				default:
					# code...
					break;
			}
		}
		return $output;
	}

	private function buildFields($instance, $echo = true){
		$output = '';
		foreach ($this->getFields() as $key => $field) {
			$output .= $this->buildField($field, $instance);
		}
		if($echo){
			echo $output;
		}else{
			return $output;
		}
	}

	private function saveFields($new_instance, $old_instance){
		$instance = $new_instance;
		return $new_instance;
	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function agmw_textdomain() {

		load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

	} // end agmw_textdomain

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// TODO define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here
	} // end deactivate

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {
		wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array('jquery','jquery-ui-core','jquery-ui-tabs') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'assets/css/widget.css', __FILE__ ) );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		wp_enqueue_script( $this->get_widget_slug().'-script', plugins_url( 'assets/js/widget.js', __FILE__ ), array('jquery') );

	} // end register_widget_scripts

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("AGMW");' ) );
