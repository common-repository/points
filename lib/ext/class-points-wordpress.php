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
class Points_Wordpress {

	/**
	 * Add shortcodes.
	 */
	public static function init() {
		
		if ( get_option('points-comments_enable', 1) ) {
			// comments
			add_action('wp_set_comment_status', array( __CLASS__, 'wp_set_comment_status' ), 10, 2);
			add_action('comment_post', array( __CLASS__, 'comment_post' ), 10, 2);
		}
		
		if ( get_option('points-welcome', '0') !== '0' ) {
			add_action( 'user_register', array( __CLASS__,'user_register' ) );
		}
	}

	public static function user_register ( $user_id ) {
		if ( !defined( 'POINTS_TYPE_USER_REGISTRATION' ) ) {
			require_once ( POINTS_CORE_LIB . '/constants.php' );
		}
		Points::set_points( get_option('points-welcome', 0), $user_id, array(
			'description' => sprintf( __( 'Welcome User Registration %d', 'points' ), $user_id ),
			'status' 	  => get_option( 'points-points_status', POINTS_STATUS_ACCEPTED ),
			'type'        => POINTS_TYPE_USER_REGISTRATION
		) );
	}
	
	public static function wp_set_comment_status( $comment_id, $status ) {
		if ( !defined( 'POINTS_TYPE_NEW_COMMENT' ) ) {
			require_once ( POINTS_CORE_LIB . '/constants.php' );
		}
		$user = get_user_by( 'email', get_comment_author_email( $comment_id ) );
		if ( $user ) {
			if ( $status == "approve" ) {
				Points::set_points( get_option('points-comments', 1), 
					$user->ID,
					array(
						'description' => sprintf( __( 'Comment approved %d', 'points' ), $comment_id ),
						'status'      => get_option( 'points-points_status', POINTS_STATUS_ACCEPTED ),
						'type'        => POINTS_TYPE_NEW_COMMENT
					)
				);
			} //else if ( $status == "hold" || $status == "spam" || $status == "delete" || $status == "trash" ) {
				// @todo cambiar el status de los comentarios está mal implementado. Hay que actualizar points, no añadir ni eliminar
			//	Points::set_points( Points::get_user_total_points( $user->ID ) - get_option('points-comments', 1), $user->ID );
			//}
		}
	}

	public static function comment_post( $comment_id, $status ) {
		if ( !defined( 'POINTS_TYPE_NEW_COMMENT' ) ) {
			require_once ( POINTS_CORE_LIB . '/constants.php' );
		}
		$user = get_user_by( 'email', get_comment_author_email( $comment_id ) );
		if ( $user ) {
			if ( $status == "1" ) {
				Points::set_points( get_option('points-comments', 0), 
					$user->ID,
					array(
						'description' => sprintf( __( 'Comment posted %d', 'points' ), $comment_id ),
						'status'      => get_option( 'points-points_status', POINTS_STATUS_ACCEPTED ),
						'type'        => POINTS_TYPE_NEW_COMMENT
					)
				);
			}
		}
	}

}
Points_Wordpress::init();
