<?php
/**
 * Registerable Abstract Class
 *
 * Registerable is shared methods for the Taxonomy and Post Type Classes.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Registerable
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2020 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\WP;

use WPS;
use function add_action;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Registerable' ) ) {
	/**
	 * Class Registerable
	 *
	 * @package WPS\Core\Registerable
	 */
	abstract class Registerable {

		/**
		 * Singular registered name
		 *
		 * @var string
		 */
		protected $singular = '';

		/**
		 * Plural registered name
		 *
		 * @var string
		 */
		protected $plural = '';

		/**
		 * Labels.
		 *
		 * @var array
		 */
		protected $labels = array();

		/**
		 * Rewite.
		 *
		 * @var array
		 */
		protected $rewrite = array();

		/**
		 * Maybe do activate.
		 *
		 * @param string $file File path to main plugin file.
		 */
		public function register_activation_hook( $file ) {
			register_activation_hook( $file, array( $this, 'activate' ) );
			$activation_hook = 'activate_' . plugin_basename( $file );
			if ( did_action( $activation_hook ) || doing_action( $activation_hook ) ) {
				$this->activate();
			}
		}

		/**
		 * Maybe do activate.
		 *
		 * @param string $file File path to main plugin file.
		 */
		public function register_deactivation_hook( $file ) {
			register_deactivation_hook( $file, array( $this, 'activate' ) );
			$deactivation_hook = 'deactivate_' . plugin_basename( $file );
			if ( did_action( $deactivation_hook ) || doing_action( $deactivation_hook ) ) {
				$this->deactivate();
			}
		}

		/**
		 * Activation method.
		 *
		 * Flushes rewrite rules.
		 */
		public function activate() {
			flush_rewrite_rules();
		}

		/**
		 * Deactivation method.
		 *
		 * Flushes rewrite rules.
		 */
		public function deactivate() {
			flush_rewrite_rules();
		}

		/**
		 * A helper function for generating the labels (taxonomy)
		 *
		 * @return array Labels array
		 */
		abstract public function get_labels();

		/**
		 * Getter method for retrieving post type registration defaults.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		abstract public function get_defaults();

		/**
		 * Gets the post type as words
		 *
		 * @param string $str String to capitalize.
		 *
		 * @return string Capitalized string.
		 */
		protected function get_word( $str ) {
			return str_replace( '-', ' ', str_replace( '_', ' ', $str ) );
		}

		/**
		 * Bail out if running an autosave, ajax or a cron
		 *
		 * @return bool
		 */
		protected function should_bail() {
			return (
				( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
				( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
				( defined( 'DOING_CRON' ) && DOING_CRON )
			);
		}

		/**
		 * Hooks a function on to a specific action.
		 *
		 * Actions are the hooks that the WordPress core launches at specific points
		 * during execution, or when specific events occur. Plugins can specify that
		 * one or more of its PHP functions are executed at these points, using the
		 * Action API.
		 *
		 * @param string $tag The name of the action to which the $function_to_add is hooked.
		 * @param callable $function_to_add The name of the function you wish to be called.
		 * @param int $priority Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
		 * @param array $args Args to pass to the function.
		 */
		public function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1, $args = array() ) {
			if ( did_action( $tag ) || doing_action( $tag ) ) {
				call_user_func_array( $function_to_add, (array) $args );
			} else {
				add_action( $tag, $function_to_add, $priority, $accepted_args );
			}
		}

		/**
		 * Alias for add_action.
		 *
		 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
		 * @param callable $function_to_add The name of the function you wish to be called.
		 * @param int      $priority        Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
		 */
		public function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1, $args = array() ) {
			$this->add_action( $tag, $function_to_add, $priority, $accepted_args, $args );
		}

		/**
		 * Hooks a function on to a specific action.
		 *
		 * Actions are the hooks that the WordPress core launches at specific points
		 * during execution, or when specific events occur. Plugins can specify that
		 * one or more of its PHP functions are executed at these points, using the
		 * Action API.
		 *
		 * @since 1.2.0
		 *
		 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
		 * @param callable $function_to_add The name of the function you wish to be called.
		 * @param int      $priority        Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
		 *
		 * @return bool Whether it added the function.
		 */
		public function add_action_once( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {

			if ( has_action( $tag, $function_to_add ) ) {
				return false;
			}

			$this->add_action( $tag, $function_to_add, $priority, $accepted_args );

			return true;
		}

		/**
		 * Hooks a function on to a specific action.
		 *
		 * Actions are the hooks that the WordPress core launches at specific points
		 * during execution, or when specific events occur. Plugins can specify that
		 * one or more of its PHP functions are executed at these points, using the
		 * Action API.
		 *
		 * @since 1.2.0
		 *
		 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
		 * @param callable $function_to_add The name of the function you wish to be called.
		 * @param int      $priority        Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
		 *
		 * @return bool Whether it added the function.
		 */
		public function add_filter_once( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {

			if ( has_filter( $tag, $function_to_add ) ) {
				return false;
			}

			$this->add_filter( $tag, $function_to_add, $priority, $accepted_args );

			return true;
		}

		/**
		 * Set the object properties.
		 *
		 * @param string $property Property in object.  Must be set in object.
		 * @param mixed  $value    Value of property.
		 *
		 * @return mixed  Returns self object, allows for chaining.
		 */
		public function set( $property, $value ) {

			if ( ! property_exists( $this, $property ) ) {
				return $this;
			}

			$this->$property = $value;

			return $this;
		}

		/**
		 * Magic getter for our object.
		 *
		 * @param  string $property Property in object to retrieve.
		 *
		 * @throws \Exception Throws an exception if the field is invalid.
		 *
		 * @return mixed     Property requested.
		 */
		public function __get( $property ) {

			if ( property_exists( $this, $property ) ) {
				return $this->{$property};
			}

			throw new \Exception( 'Invalid ' . __CLASS__ . ' property: ' . $property );
		}

	}
}
