<?php
    /**
    * Plugin Name: Horizontal Widget Most Recent Viewd Posts
    * Plugin URI: http://www.atareao.es
    * Description: A widget to show most recent viewed posts
    * Author: Lorenzo Carbonell
    * Version: 0.1.0
    * Author URI: http://www.atareao.es
    * License: GPL2
    */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function hwmrvp_load_styles() {
    if(!is_admin()){
        wp_enqueue_style( 'hwmrvp_style', plugin_dir_url( __FILE__ ) . 'hwmrvp-style.css', array(), '0.1.0', 'screen' );
    }
}
add_action( 'wp_enqueue_scripts', 'hwmrvp_load_styles' );

function catch_first_image_from_post($post_id) {
    if ( has_post_thumbnail($post_id)) {
            $first_img = get_the_post_thumbnail($post_id);
    }else{
        $first_img = '';
        $post_content = get_post_field('post_content', $post_id);
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches);
        if(count($matches [1]))$first_img = $matches [1] [0];
        if(empty($first_img)) {
              $first_img = plugin_dir_url( __FILE__ ) . "no_image.png";
        }
    }
    return $first_img;
}
function get_first_image_from_post($width,$height,$post) {
  $thumb = catch_first_image_from_post($post);
  if ($thumb) : 
	return '<img class="hwmrvp-image" src="'.$thumb.'"alt="'.get_the_title().'" width="'.$width.'" height="'.$height.'" border="0" />';
  endif;
  return '';
}
function horizontal_recent_posts($posts_per_page=4) {
    $args = array(
        'numberposts' => $posts_per_page,
        'offset' => 0,
        'category' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'post',
        'post_status' => 'publish',
        'suppress_filters' => true );
    $recent_posts = wp_get_recent_posts( $args, ARRAY_A );
    $result = '<ul class="hwmrvp-list">';
    foreach( $recent_posts as $recent ){        
        $result .= '<li class="hwmrvp-item">
                <a  rel="external" href="'.get_permalink($recent["ID"]).'">
                '.get_first_image_from_post(135,100,$recent['ID']).'
                <p class="hwmrvp-text">'.get_the_title($recent["ID"]).'</p>
                </a>
            </li>
            <hr size="8px" color="black" />';
    }
    $result .='</ul';
    echo _navigation_markup( $result, 'post-navigation' );
}
/**********************************************************************/
/***************** Recent Post With Thumbnails Widget *****************/
/**********************************************************************/

class hwmrvp_recent_posts_with_thumbnails extends WP_Widget {
	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'hwmrvp_recent_posts_with_thumbnails', 
			// Widget name will appear in UI
			'Widget Horizontal Recent Post', 
			// Widget description
		array( 'description' => 'Ultimos artículos con miniaturas', ) 
		);
	}
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
                $items =  $instance['items'];
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
                echo '<div class="hwmrvp-widget-horizontal-recent-posts">';
		echo horizontal_recent_posts($items);
                echo '</div>';
		echo $args['after_widget'];
	}
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}else {
			$title = 'Nuevo título';
		}
		if ( isset( $instance[ 'items' ] ) ) {
			$items = $instance[ 'items' ];
		}else {
			$items = 0;
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		<label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e( 'Items:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" type="number" value="<?php echo esc_attr( $items ); ?>" />
		</p>
		<?php 
	}
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
                $instance['items'] = ( ! empty( $new_instance['items'] ) ) ? strip_tags( $new_instance['items'] ) : 0;
		return $instance;
	}
}
// Register and load the widget
function hwmrvp_widget_horizontal_recent_posts_with_thumbnails_load_widget() {
    register_widget( 'hwmrvp_recent_posts_with_thumbnails' );
}
add_action( 'widgets_init', 'hwmrvp_widget_horizontal_recent_posts_with_thumbnails_load_widget' );