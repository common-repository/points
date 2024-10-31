<?php
/**
 * class-points-admin.php
 *
 * Copyright (c) Antonio Blanco http://www.eggemplo.com
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
 * @since points 1.0.0
 */

/**
 * Points Admin class
 */
class Points_Admin {

	public static function init () {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 40 );
	}

	public static function admin_notices() {
		if ( !empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		add_menu_page(
				__( 'Points', 'points' ),
				__( 'Points', 'points' ),
				'manage_options',
				'points',
				array( __CLASS__, 'points_menu'),
				POINTS_PLUGIN_URL . '/img/logo.png'
		);

		add_submenu_page(
				'points',
				__( 'Options', 'points' ),
				__( 'Options', 'points' ),
				'manage_options',
				'points-admin-options',
				array( __CLASS__, 'points_admin_options')
		);
	}

	public static function points_menu() {
		
		$alert = "";
		if ( isset( $_POST['save'] ) && isset( $_POST['action'] ) ) {
			if ( $_POST['action'] == "edit" ) {
				$point_id = isset($_POST['point_id'])?intval( $_POST['point_id'] ) : null;
				$points = Points::get_point( $point_id );
				$data = array();
				if ( isset( $_POST['user_id'] ) ) {
					$data['user_id'] = $_POST['user_id'];
				}
				if ( isset( $_POST['datetime'] ) ) {
					$data['datetime'] = $_POST['datetime'];
				}
				if ( isset( $_POST['description'] ) ) {
					$data['description'] = $_POST['description'];
				}
				if ( isset( $_POST['status'] ) ) {
					$data['status'] = $_POST['status'];
				}
				if ( isset( $_POST['points'] ) ) {
					$data['points'] = $_POST['points'];
				}

				if ( $points ) {  // edit points
					Points::update_points($point_id, $data);
				} else {  // add new points
					Points::set_points($_POST['points'], $_POST['user_id'], $data);
				}
			}
			$alert= __( "Points Updated", 'points' );
		}

		if ( isset( $_GET["action"] ) ) {
			$action = $_GET["action"];
			if ( $action !== null ) {
				switch ( $action ) {
					case 'edit' :
						if ( isset( $_GET['point_id'] ) && ( $_GET['point_id'] !== null ) ) {
							return self::points_admin_points_edit( intval( $_GET['point_id'] ) );
						} else {
							return self::points_admin_points_edit();
						}
						break;
					case 'delete' :
						if ( $_GET['point_id'] !== null ) {
							if ( current_user_can( 'administrator' ) ) {
								Points::remove_points( $_GET['point_id'] );
								$alert= __( "Points Removed", 'points' );
							}
						}
						break;
				}
			}
		}

		if ($alert != "") {
			echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}
		
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$cancel_url  = remove_query_arg( 'point_id', remove_query_arg( 'action', $current_url ) );
		$current_url = remove_query_arg( 'point_id', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );
		
		$exampleListTable = new Points_List_Table();
		$exampleListTable->prepare_items();
		?>
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h2>Points</h2>
			<div class="manage add">
				<a class="add button" href="<?php echo esc_url( add_query_arg( 'action', 'edit', $current_url ) ); ?>" title="<?php echo __( 'Click to add a Points manually', 'points' );?>"><?php echo __( 'Add Points', 'points' );?></a>
			</div>

			<?php $exampleListTable->display(); ?>
		</div>
		<?php
	}

	/**
	 * Show Points options page.
	 */
	public static function points_admin_options() {
		$alert = "";
		if ( isset( $_POST['submit'] ) ) {
			update_option( 'points-comments_enable', $_POST['points_comments_enable'] );

			update_option( 'points-comments', $_POST['points_comments'] );

			update_option( 'points-welcome', $_POST['points_welcome'] );

			$label = ( isset( $_POST['points_label'] ) && $_POST['points_label'] !== "" )?$_POST['points_label']:"";
			update_option( 'points-points_label', $label );
			$singular_label = ( isset( $_POST['points_singular_label'] ) && $_POST['points_singular_label'] !== "" )?$_POST['points_singular_label']:"";
			update_option( 'points-points_singular_label', $singular_label );

			update_option( 'points-points_status', $_POST['points_status'] );

			$alert= __( "Saved", 'points' );
		}

		if ($alert != "") {
			echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
		}
		?>
			<h2><?php echo __( 'Points Options', 'points' ); ?></h2>
			<hr>

			<form method="post" action="">
			
				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3><?php echo __( 'General', 'points' ); ?></h3>
					<div class="points-admin-line">
						<div class="points-admin-label">
							<?php echo __( 'Points plural label', 'points' ); ?>
						</div>
						<div class="points-admin-value">
							<?php 
							$label = get_option('points-points_label', 'points');
							?>
							<input type="text" name="points_label" value="<?php echo $label; ?>" class="regular-text" />
						</div>

						<div class="points-admin-label">
							<?php echo __( 'Points singular label', 'points' ); ?>
						</div>
						<div class="points-admin-value">
							<?php 
							$label = get_option('points-points_singular_label', 'point');
							?>
							<input type="text" name="points_singular_label" value="<?php echo $label; ?>" class="regular-text" />
						</div>
					</div>

					<div class="points-admin-line">
						<div class="points-admin-label">
							<?php echo __( 'Default points status', 'points' ); ?>
						</div>
						<div class="points-admin-value">
							<select name="points_status">
							<?php 
							$output = "";
							$status = get_option( 'points-points_status', POINTS_STATUS_ACCEPTED );
							$status_descriptions = array(
									POINTS_STATUS_ACCEPTED => __( 'Accepted', 'points' ),
									POINTS_STATUS_PENDING   => __( 'Pending', 'points' ),
									POINTS_STATUS_REJECTED => __( 'Rejected', 'points' ),
							);
							foreach ( $status_descriptions as $key => $label ) {
								$selected = $key == $status ? ' selected="selected" ' : '';
								$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
							}
							echo $output;
							?>
							</select>
						</div>
					</div>
				</div>

				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3><?php echo __( 'Comments', 'points' ); ?></h3>
					<div class="points-admin-line">
						<div class="points-admin-label">
							Enable comments points
						</div>
						<div class="points-admin-label">
							<?php 
							$enable_comments = get_option('points-comments_enable', 1);
							?>
							<input type="checkbox" name="points_comments_enable" value="1" <?php echo $enable_comments=="1"?" checked ":""?>>
						</div>
					</div>
					<div class="points-admin-line">
						<div class="points-admin-label">
							Comments points
						</div>
						<div class="points-admin-label">
							<?php 
							$enable_comments = get_option('points-comments_enable', 1);
							?>
							<input type="text" name="points_comments" value="<?php echo get_option('points-comments', 1); ?>" size="4">
						</div>
					</div>
				</div>

				<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
					<h3><?php echo __( 'Others', 'points' ); ?></h3>
					<div class="points-admin-line">
						<div class="points-admin-label">
							<?php echo __( 'Welcome points', 'points' ); ?>
						</div>
						<div class="points-admin-label">
							<input type="text" name="points_welcome" value="<?php echo get_option('points-welcome', "0"); ?>" size="4">
						</div>
					</div>
				</div>

				<div class="points-admin-line">
					<?php submit_button("Save"); ?>
				</div>

				<?php settings_fields( 'points-settings' ); ?>

			</form>
		<?php 
	}

	public static function points_admin_points_edit( $point_id = null ) {

		$output = '';

		if ( !current_user_can( 'administrator' ) ) {
			wp_die( __( 'Access denied.', 'points' ) );
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$cancel_url  = remove_query_arg( 'point_id', remove_query_arg( 'action', $current_url ) );
		$current_url = remove_query_arg( 'point_id', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );

		$saved = false;  // temporal

		if ( $point_id !== null ) {
			$points = Points::get_point( $point_id );

			if ( $points !== null ) {
				$user_id = $points->user_id;
				$num_points = $points->points;
				$description = $points->description;
				$datetime = $points->datetime;
				$status = $points->status;
			} 
		} else {
			$user_id = "";
			$num_points = 0;
			$description = "";
			$datetime = "";
			$status = POINTS_STATUS_ACCEPTED;
		}

		$output .= '<div class="wrap">';
		$output .= '<h2>';
		if ( empty( $point_id ) ) {
			$output .= __( 'New Points', 'points' );
		} else {
			$output .= __( 'Edit Points', 'points' );
		}
		$output .= '</h2>';

		$output .= '<form id="points" action="' . $current_url . '" method="post">';
		$output .= '<table class="form-table">';

		if ( $point_id ) {
			$output .= sprintf( '<input type="hidden" name="point_id" value="%d" />', intval( $point_id ) );
		}

		$output .= '<input type="hidden" name="action" value="edit" />';

		$output .= '<tr>';
		$output .= '<th scope="row">';
		$output .= '<span class="title">' . __( 'User ID', 'points' ) . '</span>';
		$output .= '</th><td>';
		$output .= wp_dropdown_users(array('name' => 'user_id', 'echo' => false, 'selected' => $user_id));
		//$output .= sprintf( '<input type="text" name="user_id" value="%s" />',  $user_id );
		$output .= '</td>';
		$output .= '</tr>';

		$output .= '<tr>';
		$output .= '<th scope="row">';
		$output .= '<span class="title">' . __( 'Date & Time', 'points' ) . '</span>';
		$output .= '</th><td>';
		$output .= sprintf( '<input type="text" name="datetime" value="%s" id="datetimepicker" />', esc_attr( $datetime ) );
		$output .= ' ';
		$output .= '<span class="description">' . __( 'Format : YYYY-MM-DD HH:MM:SS', 'points' ) . '</span>';
		$output .= '</td>';
		$output .= '</tr>';

		$output .= '<tr>';
		$output .= '<th scope="row">';
		$output .= '<span class="title">' . __( 'Description', 'points' ) . '</span>';
		$output .= '</th><td>';
		$output .= '<textarea name="description">';
		$output .= stripslashes( $description );
		$output .= '</textarea>';
		$output .= '</td>';
		$output .= '</tr>';

		$output .= '<tr>';
		$output .= '<th scope="row">';
		$output .= '<span class="title">' . __( 'Points', 'points' ) . '</span>';
		$output .= '</th><td>';
		$output .= sprintf( '<input type="text" name="points" value="%s" />', esc_attr( $num_points ) );
		$output .= '</td>';
		$output .= '</tr>';

		$status_descriptions = array(
				POINTS_STATUS_ACCEPTED => __( 'Accepted', 'points' ),
				POINTS_STATUS_PENDING  => __( 'Pending', 'points' ),
				POINTS_STATUS_REJECTED => __( 'Rejected', 'points' ),
		);
		$output .= '<tr>';
		$output .= '<th scope="row">';
		$output .= '<span class="title">' . __( 'Status', 'points' ) . '</span>';
		$output .= '</th><td>';
		$output .= '<select name="status">';
		foreach ( $status_descriptions as $key => $label ) {
			$selected = $key == $status ? ' selected="selected" ' : '';
			$output .= '<option ' . $selected . ' value="' . esc_attr( $key ) . '">' . $label . '</option>';
		}
		$output .= '</select>';
		$output .= '</td>';
		$output .= '</tr>';

		$output .= wp_nonce_field( 'save', 'points-nonce', true, false );

		$output .= '<tr>';
		$output .= '<th scope="row">';
		$output .= sprintf( '<input class="button-primary" type="submit" name="save" value="%s"/>', __( 'Save', 'points' ) );
		$output .= ' ';
		$output .= sprintf( '<a class="cancel button-secondary" href="%s">%s</a>', $cancel_url, $saved ? __( 'Back', 'points' ) : __( 'Cancel', 'points' ) );
		$output .= '</th><td>';
		$output .= '</td><tr>';
		
		$output .= '</table>';
		$output .= '</form>';

		$output .= '</div>';

		echo $output;
	}

}