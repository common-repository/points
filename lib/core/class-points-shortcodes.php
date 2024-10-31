<?php
/**
* class-points-shortcodes.php
*
* Copyright (c) 2010-2012 "eggemplo" Antonio Blanco Oliva www.eggemplo.com
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
* @author Antonio Blanco Oliva
* @package points
* @since points 1.0
*/
class Points_Shortcodes {

	/**
	 * Add shortcodes.
	 */
	public static function init() {

		add_shortcode( 'points_users_list', array( __CLASS__, 'points_users_list' ) );
		add_shortcode( 'points_user_points', array( __CLASS__, 'points_user_points' ) );
		add_shortcode( 'points_user_points_details', array( __CLASS__, 'points_user_points_details' ) );

	}

	public static function points_users_list ( $atts, $content = null ) {
		$options = shortcode_atts(
				array(
						'limit'  => 10,
						'order_by' => 'points',
						'order' => 'DESC'
				),
				$atts
		);
		extract( $options );
		$output = "";

		$pointsusers = Points::get_users();

		if ( sizeof( $pointsusers )>0 ) {
			foreach ( $pointsusers as $pointsuser ) {
				$total = Points::get_user_total_points( $pointsuser );
				$output .='<div class="points-user">';
				$output .= '<span class="points-user-username">';
				$output .= get_user_meta ( $pointsuser, 'nickname', true );
				$output .= ':</span>';
				$output .= '<span class="points-user-points">';
				$output .= " ". $total . " " . Points::get_label( $total );
				$output .= '</span>';
				$output .= '</div>';
			}
		} else {
			$output .= '<p>No users</p>';
		}

		return $output;
	}

	public static function points_user_points ( $atts, $content = null ) {
		$output = "";

		$options = shortcode_atts(
				array(
						'id'  => ""
				),
				$atts
		);
		extract( $options );

		if ( $id == "" ) {
			$id = get_current_user_id();
		}

		if ( $id !== 0 ) {
			$points = Points::get_user_total_points( $id, POINTS_STATUS_ACCEPTED );
			$output .= $points;
		}

		return $output;
	}

	/**
	 * Shortcode. Display the user points details.
	 * @param array $atts
	 * 		id: User id. If not set, then the current user is used.
	 * 		status: The points status.
	 * @param string $content
	 */
	public static function points_user_points_details ( $atts, $content = null ) {
		$options = shortcode_atts(
				array(
						'user_id'         => '',
						'items_per_page'  => 10,
						'order_by'        => 'point_id',
						'order'           => 'DESC',
						'description'     => true
				),
				$atts
		);
		extract( $options );
	
		if ( is_string( $description ) && ( ( $description == '0' ) || ( strtolower( $description ) == 'false' ) ) ) {
			$description = false;
		}
	
		$desc_th = '';
		if ( $description ) {
			$desc_th = 	'<th>' . __( 'Description', 'points' ) . '</th>';
		}

		$user_id = get_current_user_id();
		$points = Points::get_points_by_user( $user_id, null, $order_by, $order, OBJECT );

		// Pagination
		$total           = sizeof( $points );
		$page            = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
		$offset          = ( $page * $items_per_page ) - $items_per_page;
		$totalPage       = ceil($total / $items_per_page);

		$points = Points::get_points_by_user( $user_id, $items_per_page, $order_by, $order, OBJECT, $offset );

		$output = '<table class="points_user_points_table">' .
				'<tr>' .
				'<th>' . __( 'Datetime', 'points' ) . '</th>' .
				'<th>' . ucfirst( Points::get_label( 100 ) ) . '</th>' .
				'<th>' . __( 'Type', 'points' ) . '</th>' .
				'<th>' . __( 'Status', 'points' ) . '</th>' .
				$desc_th .
				'</tr>';
	
		if ( $user_id !== 0 ) {
			if ( sizeof( $points ) > 0 ) {
				foreach ( $points as $point ) {
					$desc_td = '';
					if ( $description ) {
						$desc_td = 	'<td>' . $point->description . '</td>';
					}
					$output .= '<tr>' .
							'<td>' . $point->datetime . '</td>' .
							'<td>' . $point->points . '</td>' .
							'<td>' . $point->type . '</td>' .
							'<td>' . $point->status . '</td>' .
							$desc_td .
							'</tr>';
				}
			}
		}
	
		$output .= '</table>';
	
		// Pagination
		if($totalPage > 1){
			$customPagHTML = '<div><span>' . __( 'Page', 'points' ) . ' '. $page .' '. __( 'of', 'points' ) . ' ' . $totalPage . '</span><br>' . paginate_links( array(
					'base' => add_query_arg( 'cpage', '%#%' ),
					'format' => '',
					'prev_text' => __('&laquo;'),
					'next_text' => __('&raquo;'),
					'total' => $totalPage,
					'current' => $page
			)).'</div>';
			$output .= $customPagHTML;
		}
	
		return $output;
	}
}
Points_Shortcodes::init();
