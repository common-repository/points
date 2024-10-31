<?php
/**
 * class-points-widget-leaderboard.php
 * 
 * Copyright (c) 2010, 2011 "eggemplo" Antonio Blanco www.eggemplo.com
 * 
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 * 
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This header and all notices must be kept intact.
 * 
 * @author Antonio Blanco
 * @package points
 * @since points 1.0 
 */

/**
 * Points widget.
 */
class Points_Widget extends WP_Widget {

	/**
	 * Creates a points widget.
	 */
	function __construct() {
		parent::__construct( false, 'Points - leaderboard' );
		add_action( 'wp_print_styles', array( __CLASS__, '_print_styles' ) );
	}

	/**
	 * Enqueues required stylesheets.
	 */
	public static function _print_styles() {
		wp_enqueue_style( 'points', POINTS_PLUGIN_URL . 'css/points.css', array() );
	}

	/**
	 * Widget output
	 * 
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		extract( $args );
		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$widget_id = $args['widget_id'];
		echo $before_widget;
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$limit          = $instance['limit'];
		$order          = $instance['order'];
		$display_avatar = isset($instance['display_avatar']) ? $instance['display_avatar'] : false;
		$order_by = 'points';

		$pointsusers = Points::get_users_total_points( $limit, $order_by, $order, POINTS_STATUS_ACCEPTED );

		if ( sizeof( $pointsusers )>0 ) {
			foreach ( $pointsusers as $pointsuser ) {
				echo '<div class="points-user">';
				if ( $display_avatar ) {
				    echo '<span class="points-user-avatar">';
				    echo get_avatar( $pointsuser->user_id );
				    echo ' </span>';
				} else {
    				echo '<span class="points-user-username">';
    				echo get_user_meta ( $pointsuser->user_id, 'nickname', true );
    				echo ': </span>';
				}
				echo '<span class="points-user-points">';
				echo $pointsuser->total . " " . Points::get_label( $pointsuser->total );
				echo '</span>';
				echo '</div>';
			}
		} else {
			echo '<p>' . __('No users', 'points' ) . '</p>';
		}

		echo $after_widget;
	}

	/**
	 * Save widget options
	 * 
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {
		$settings = $old_instance;

		// title
		if ( !empty( $new_instance['title'] ) ) {
			$settings['title'] = strip_tags( $new_instance['title'] );
		} else {
			unset( $settings['title'] );
		}

		// limit
		if ( !empty( $new_instance['limit'] ) ) {
		    $settings['limit'] = strip_tags( $new_instance['limit'] );
		} else {
		    unset( $settings['limit'] );
		}
		
		// display_avatar
		if ( !empty( $new_instance['display_avatar'] ) ) {
		    $settings['display_avatar'] = true;
		} else {
		    unset( $settings['display_avatar'] );
		}
		
		// order
		if ( !empty( $new_instance['order'] ) ) {
			$settings['order'] = strip_tags( $new_instance['order'] );
		} else {
			unset( $settings['order'] );
		}

		return $settings;
	}

	/**
	 * Output admin widget options form
	 * 
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'points' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php
		$limit = isset( $instance['limit'] ) ? esc_attr( $instance['limit'] ) : '5';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Number of users to display:', 'points' ); ?></label> 
			<input class="" size="3" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" />
		</p>
		
		<?php
		$selected = isset( $instance['display_avatar'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Display avatar?', 'points' ); ?></label> 
			<input type="checkbox" id="<?php echo $this->get_field_id( 'display_avatar' ); ?>" name="<?php echo $this->get_field_name( 'display_avatar' ); ?>" <?php if ( $selected ) : ?>checked<?php endif; ?>>
		</p>
		
		<?php
		$order = isset( $instance['order'] ) ? esc_attr( $instance['order'] ) : 'DESC';
		$selectdesc = ($order == 'DESC')?"selected":"";
		$selectasc = ($order == 'ASC')?"selected":"";
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order:', 'points' ); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" >
				<option value="DESC" <?php echo $selectdesc;?> ><?php _e( 'Desc', 'points' );?></option>
				<option value="ASC" <?php echo $selectasc;?> ><?php _e( 'Asc', 'points' );?></option>
			</select>
		</p>
		<?php

	}
}
?>