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

    protected $width = 0;

    protected $height = 0;

    protected $fields = array();

    protected $user;

    protected $jquery_ver = '1.11.1';

    protected $jquery_ui_ver = '1.11.2';

    protected $wrapper = 'p';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		$this->user = wp_get_current_user();

		// load plugin text domain
		add_action( 'init', array( $this, 'agmw_textdomain' ) );

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_slug(),
			$this->get_widget_name(),
			array(
				'classname'  => $this->get_widget_class(),
				'description' => $this->get_widget_description()
			),
			array(
				'width' => $this->width, 
				'height' => $this->height
			)
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		if(is_active_widget(false, false, $this->get_slug())){
			add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );
		}
		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );


		$field = array(
			'type' => 'checkbox',
			'name' => 'checkbox',
			'label' => __('Input Label', $this->get_slug()),
			'description' => __('Input Description', $this->get_slug()),
			'options' => array(
				'1' => 'One',
				'2' => 'Two',
				'3' => 'Three',
				'4' => 'Four'
			)
		);

		$this->set_field($field);
	
	} // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_slug() {
        return $this->widget_slug;
    }

    /**
     * Return the widget name.
     *
     * @since    1.0.0
     *
     * @return    Plugin name variable.
     */
    public function get_widget_name() {
        return __( 'Advanced Google Map', $this->get_slug() );
    }

    /**
     * Return the widget description.
     *
     * @since    1.0.0
     *
     * @return    Plugin description variable.
     */
    public function get_widget_description() {
        return __( 'Show advanced googe map on your wordpress widget.', $this->get_slug() );
    }

    /**
     * Return the widget class.
     *
     * @since    1.0.0
     *
     * @return    Plugin class variable.
     */
    public function get_widget_class() {
        return $this->get_slug().'-class';
    }
	
	/**
     * Delete widget cache
     */
	public function flush_widget_cache() {
    	wp_cache_delete( $this->get_slug(), 'widget' );
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
		$cache = wp_cache_get( $this->get_slug(), 'widget' );

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

		wp_cache_set( $this->get_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	
	public function update( $new_instance, $old_instance ) {
		return $this->get_update_instance( $new_instance, $old_instance );
	} // end update

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$this->build_form($instance);
	} // end form

	protected function set_field($field){
		array_push($this->fields, $this->parse_field( $field ));
	}

	protected function parse_field($field){
		$defaults = array(
			'type' => '',
			'name' => '',
			'label' => '',
			'description' => '',
			'value' => '',
			'attr' => array('class'=>'widefat'),
			'filter_data' => '',
			'filter_view' => '',
			'capability' => array(),
			'role' => array(),
			'dependency' => array()
		);
		switch ($field['type']) {
			case 'checkbox':
				$defaults['options'] = array();
				$defaults['attr'] = array('class'=>'checkbox');
				break;
			case 'radio':
				$defaults['options'] = array();
				break;
			case 'dropdown':
				$defaults['options'] = array();
				break;
			
			default:
				# code...
				break;
		}
		return wp_parse_args( $field, $defaults );
	}

	protected function get_fields(){
		return $this->fields;
	}

	protected function get_field_value($field, $instance){
		$value = (isset($instance[$field['name']])) ? $instance[$field['name']] : $field['value'];
		if($field['name'] == 'title'){
			$value = apply_filters( 'widget_title', $value );
		}
		if(!empty($field['filter_data']) && is_string($field['filter_data'])){
			$value = apply_filters( $field['filter_data'], $value, $field );
		}
		switch ($field['type']) {
			case 'checkbox':
					$value = array_map('esc_attr', (array) $value);
				break;
			case 'url':
					$value = esc_url($value);
				break;
			case 'email':
					$value = sanitize_email($value);
				break;
			case 'textarea':
					$value = esc_textarea($value);
				break;
			
			default:
				if(is_array($value)){
					$value = array_map('esc_attr', $value);
				}else{
					$value = esc_attr($value);
				}
				break;
		}
		return $value;
	}

	protected function get_field_attr($field){
		$attrs = array();
		if($field['attr'] && is_array($field['attr'])){
			foreach ($field['attr'] as $key => $value) {
				if (!in_array($key, array('type','id','name','value'))) {
					$attrs[] = $key.'="'.esc_attr($value).'"';
				}
			}
		}
		return implode(" ", $attrs);
	}

	protected function allow_field($field){
		$allow = true;
		if($field['capability'] && is_array($field['capability'])){
			$allow = false;
			if(is_array($field['capability'])){
				foreach ($field['capability'] as $capability) {
					if (is_string($capability) && current_user_can($capability)) {
						$allow = true;
						break;
					}
				}
			}elseif (is_string($field['capability'])) {
				if(current_user_can($field['capability'])){
					$allow = true;
				}
			}
		}
		if($field['role'] && is_array($field['role'])){
			$allow = false;
			if ( !empty( $this->user->roles ) && is_array( $this->user->roles ) ) {
				foreach ( $user->roles as $role ){
					if(in_array($role, $field['role'])){
						$allow = true;
						break;						
					}
				}
			}
		}
		return $allow;
	}

	protected function build_field($field, $instance){
		$output = '';
		
		if(!empty($field['name'])){
			$output .= '<'.$this->wrapper.'>';
			if($field['label']){
				$output .= '<label for="'.$this->get_field_id($field['name']).'">'.$field['label'].'</label>';
			}
			if($field['description']){
				$output .= '<br /><small>'.$field['description'].'</small><br />';
			}
			switch ($field['type']) {
				case 'text':
				case 'email':
				case 'url':
				case 'password':
				case 'date':
					$output .= '<input type="'.$field['type'].'" id="'.$this->get_field_id($field['name']).'" name="'.$this->get_field_name($field['name']).'" value="'.$this->get_field_value($field, $instance).'" '.$this->get_field_attr($field).'>';
					break;

				case 'textarea':
					$output .= '<textarea id="'.$this->get_field_id($field['name']).'" name="'.$this->get_field_name($field['name']).'" '.$this->get_field_value($field, $instance).'</textarea>';
					break;

				case 'checkbox':
					if(!empty($field['options']) && is_array($field['options'])){
						foreach ($field['options'] as $opt_value => $opt_label) {
							$checked = (in_array($opt_value, $this->get_field_value($field, $instance))) ? ' checked="checked"' : '';
							$output .= '<label><input type="checkbox" name="'. $this->get_field_name( $field['name'] ) .'[]" value="'.$opt_value.'"'.$checked.'>'.$opt_label.'</label><br />';
						}
					}
					break;

				case 'radio':
					if(!empty($field['options']) && is_array($field['options'])){
						foreach ($field['options'] as $opt_value => $opt_label) {
							$checked = ($opt_value == $this->get_field_value($field, $instance)) ? ' checked="checked"' : '';
							$output .= '<label><input type="radio" name="'. $this->get_field_name( $field['name'] ) .'" value="'.$opt_value.'"'.$checked.'>'.$opt_label.'</label><br />';
						}
					}
					break;

				case 'dropdown':
					$output .= '<select id="'.$this->get_field_id($field['name']).'" name="'.$this->get_field_name($field['name']).'">';
					if(!empty($field['options']) && is_array($field['options'])){
						foreach ($field['options'] as $opt_value => $opt_label) {
							$selected = ($this->get_field_value($field, $instance) == $opt_value) ? ' selected="selected"' : '';
							$output .= '<option value="'.$opt_value.'"'.$selected.'>'.$opt_label.'</option>';
						}
					}
					$output .= '</select>';
					break;

				case 'image':
					$output .= '<div class="image-uploader">';
					$output .= '<div class="image-preview">';
					if($this->get_field_value($field, $instance)){
						$output .= wp_get_attachment_image( $this->get_field_value($field, $instance), 'thumbnail', true);
					}
					$output .= '</div>';
					$output .= '<button type="submit" class="button media-select" onclick="return false;">'.__('Select', $this->get_slug()).'</button>';
					if($this->get_field_value($field, $instance)){
						$output .= '<button type="submit" class="button media-delete" onclick="return false;">'.__('Delete', $this->get_slug()).'</button>';
					}else{
						$output .= '<button type="submit" class="button media-delete" onclick="return false;" style="display:none;">'.__('Delete', $this->get_slug()).'</button>';
					}
					$output .= '<input type="hidden" id="'.$this->get_field_id($field['name']).'" name="'.$this->get_field_name($field['name']).'" value="'.$this->get_field_value($field, $instance).'" class="image-id">';
					$output .= '</div>';
					break;

				case 'wpeditor':
					$output .= '<select id="'.$this->get_field_id($field['name']).'" name="'.$this->get_field_name($field['name']).'">';
					if(!empty($field['options']) && is_array($field['options'])){
						foreach ($field['options'] as $opt_value => $opt_label) {
							$selected = ($this->get_field_value($field, $instance) == $opt_value) ? ' selected="selected"' : '';
							$output .= '<option value="'.$opt_value.'"'.$selected.'>'.$opt_label.'</option>';
						}
					}
					$output .= '</select>';
					break;
				
				default:
					# code...
					break;
			}
			$output .= '</'.$this->wrapper.'>';
		}
		return $output;
	}

	protected function build_form($instance, $echo = true){
		$has_dependency = false;
		$output = '';
		foreach ($this->get_fields() as $key => $field) {
			if(!$this->allow_field($field)){
				continue;
			}
			$output_temp = $this->build_field($field, $instance);
			if(!empty($field['filter_view']) && is_string($field['filter_view'])){
				$output_temp = apply_filters( $field['filter_view'], $output_temp, $field );
			}
			$output .= $output_temp;
		}
		$output = apply_filters( $this->get_slug().'_build_form', $output, $this->get_fields() );
		if($echo){
			echo $output;
		}else{
			return $output;
		}
	}

	protected function get_update_instance($new_instance, $old_instance=array()){
		$instance = array();
		foreach ($this->get_fields() as $key => $field) {
			if(!$this->allow_field($field)){
				if(isset($old_instance[$field['name']])){
					$instance[$field['name']] = $this->get_field_value($field, $old_instance);
				}
			}else{
				if(isset($new_instance[$field['name']])){
					$instance[$field['name']] = $this->get_field_value($field, $new_instance);
				}
			}
		}
		return $instance;
	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function agmw_textdomain() {

		load_plugin_textdomain( $this->get_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

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

		wp_enqueue_style( $this->get_slug().'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {
		wp_enqueue_script( $this->get_slug().'-dependsOn', plugins_url( 'assets/js/dependsOn-1.0.1.js', __FILE__ ), array('jquery') );
		wp_enqueue_script( $this->get_slug().'-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array('jquery') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		wp_enqueue_style( $this->get_slug().'-widget-styles', plugins_url( 'assets/css/widget.css', __FILE__ ) );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( $this->get_slug().'-script', plugins_url( 'assets/js/widget.js', __FILE__ ), array('jquery') );

	} // end register_widget_scripts


	/*--------------------------------------------------*/
	/* Private Functions
	/*--------------------------------------------------*/

	private function get_jquery_version() {

		global $wp_scripts;

		if( !$wp_scripts instanceof WP_Scripts )
			$wp_scripts = new WP_Scripts();

		$jquery = $wp_scripts->query( 'jquery' );

		if( !$jquery instanceof _WP_Dependency )
			return $this->jquery_ver;

		if( !isset( $jquery->ver ) )
			return $this->jquery_ver;

		return $jquery->ver;
	}

	private function get_jquery_ui_version() {

		global $wp_scripts;

		if( !$wp_scripts instanceof WP_Scripts )
			$wp_scripts = new WP_Scripts();

		$jquery_ui_core = $wp_scripts->query( 'jquery-ui-core' );

		if( !$jquery_ui_core instanceof _WP_Dependency )
			return $this->jquery_ui_ver;

		if( !isset( $jquery_ui_core->ver ) )
			return $this->jquery_ui_ver;

		return $jquery_ui_core->ver;
	}

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("AGMW");' ) );
