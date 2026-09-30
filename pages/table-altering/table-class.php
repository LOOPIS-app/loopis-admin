<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class table_menu {

	public $table_name;
	public $key;
	public $data_columns;
	public $menu_name;
	public $menu_icon;

	public function __construct(
		$table_name = 'wp_table',
		$key = array(
			'id' => '%d',
		),
		$data_columns = array(
			'name'  => '%s',
			'value' => '%d',
		),
		$optional_additions = array(
			'menu_name' => 'Table menu',
			'menu_icon' => 'dashicons-list-view',
            'hook_prefix' => 'loopis_table',
            'hook_name' => 'loopis-admin-records',
		)
	) {
		$optional_defaults = array(
			'menu_name' => 'Table menu',
			'menu_icon' => 'dashicons-list-view',
            'hook_prefix' => 'loopis_table',
            'page_name' => 'loopis-admin-records',
		);

		$optional_additions = wp_parse_args(
			$optional_additions,
			$optional_defaults
		);

		$this->table_name   = $table_name;
		$this->key          = $key;
		$this->data_columns = $data_columns;
		$this->menu_name    = $optional_additions['menu_name'];
		$this->menu_icon    = $optional_additions['menu_icon'];
        $this->hook_prefix    = $optional_additions['hook_prefix'];
        $this->page_name    = $optional_additions['page_name'];
        $this->hook_save    = $this->hook_prefix.'save';

		add_action(
			'admin_menu',
			array( $this, 'register_admin_menu' )
		);

		add_action(
			'admin_post_'.$this->hook_save,
			array( $this, 'save_record' )
		);
	}


	private function get_key_name() {
		return (string) key( $this->key );
	}

	private function get_key_format() {
		return current( $this->key );
	}


	private function identifier( $name ) {
		if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $name ) ) {
			wp_die( 'Invalid database identifier.' );
		}

		return '`' . $name . '`';
	}

	private function get_table_name() {
		return $this->identifier( $this->table_name );
	}

	private function get_key_identifier() {
		return $this->identifier( $this->get_key_name() );
	}

	public function register_admin_menu() {
		add_menu_page(
			$this->menu_name,
			$this->menu_name,
			'manage_options',
			$this->page_name,
			array( $this, 'render_table_page' ),
			$this->menu_icon,
			25
		);
	}

	public function render_table_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to view this page.' );
		}

        do_action($this->hook_prefix . '_before_page');

		global $wpdb;

		$table_name = $this->get_table_name();
		$key_name   = $this->get_key_name();
		$key_id     = $this->get_key_identifier();

		$edit_id = isset( $_GET['edit'] )
			? absint( $_GET['edit'] )
			: 0;

		$record = null;

		if ( $edit_id > 0 ) {
			$sql = $wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE {$key_id} = %d",
				$edit_id
			);

			$record = $wpdb->get_row( $sql, ARRAY_A );
		}

		$rows = $wpdb->get_results(
			"SELECT * FROM {$table_name} ORDER BY {$key_id} DESC",
			ARRAY_A
		);

		?>
		<div class="wrap">

			<h1>
				<?php
				echo esc_html(
					$edit_id ? 'Edit Record' : $this->menu_name
				);
				?>
			</h1>

			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>Record saved successfully.</p>
				</div>
			<?php endif; ?>

			<div style="max-width: 700px; margin: 20px 0;">

				<form
					method="post"
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				>
					<input
						type="hidden"
						name="action"
						value="<?php echo esc_attr($this->hook_save);?>"
					>

					<input
						type="hidden"
						name="<?php echo esc_attr( $key_name ); ?>"
						value="<?php echo esc_attr( $record[ $key_name ] ?? 0 ); ?>"
					>

					<?php
					wp_nonce_field(
						$this->hook_save,
						'loopis_admin_nonce'
					);
					?>

					<table class="form-table" role="presentation">
						<?php foreach ( $this->data_columns as $column => $format ) : ?>
							<tr>
								<th scope="row">
									<label
										for="<?php echo esc_attr( 'loopis-admin-' . $column ); ?>"
									>
										<?php echo esc_html( ucfirst( $column ) ); ?>
									</label>
								</th>

								<td>
									<input
										type="text"
										id="<?php echo esc_attr( 'loopis-admin-' . $column ); ?>"
										name="<?php echo esc_attr( $column ); ?>"
										class="regular-text"
										value="<?php echo esc_attr( $record[ $column ] ?? '' ); ?>"
									>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>

					<?php submit_button( $edit_id ? 'Update' : 'Add' ); ?>

					<?php if ( $edit_id ) : ?>
						<a
							href="<?php echo esc_url(
								admin_url(
									'admin.php?page='.$this->page_name
								)
							); ?>"
							class="button"
						>
							Cancel
						</a>
					<?php endif; ?>
				</form>

			</div>

			<hr>

			<h2>All Records</h2>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html( ucfirst( $key_name ) ); ?></th>

						<?php foreach ( $this->data_columns as $column => $format ) : ?>
							<th><?php echo esc_html( ucfirst( $column ) ); ?></th>
						<?php endforeach; ?>

						<th>Actions</th>
					</tr>
				</thead>

				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr>
							<td
								colspan="<?php echo esc_attr( count( $this->data_columns ) + 2 ); ?>"
							>
								No records found.
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td>
									<?php echo esc_html( $row[ $key_name ] ); ?>
								</td>

								<?php foreach ( $this->data_columns as $column => $format ) : ?>
									<td>
										<?php echo esc_html( $row[ $column ] ?? '' ); ?>
									</td>
								<?php endforeach; ?>

								<td>
									<a
										href="<?php echo esc_url(
											add_query_arg(
												array(
													'page' => $this->page_name,
													'edit' => absint(
														$row[ $key_name ]
													),
												),
												admin_url( 'admin.php' )
											)
										); ?>"
									>
										Edit
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

		</div>
		<?php
        do_action($this->hook_prefix . '_after_page');
	}

	public function save_record() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to perform this action.' );
		}

		check_admin_referer(
			$this->hook_save,
			'loopis_admin_nonce'
		);

		global $wpdb;

		$key_name = $this->get_key_name();

		$record_id = isset( $_POST[ $key_name ] )
			? absint( $_POST[ $key_name ] )
			: 0;

		$data    = array();
		$formats = array();

		foreach ( $this->data_columns as $column => $format ) {
			if ( ! isset( $_POST[ $column ] ) ) {
				continue;
			}

			$value = wp_unslash( $_POST[ $column ] );

			if ( '%d' === $format ) {
				$value = absint( $value );
			} elseif ( '%f' === $format ) {
				$value = (float) $value;
			} else {
				$value = sanitize_text_field( $value );
			}

			$data[ $column ] = $value;
			$formats[]       = $format;
		}

		$table_name = $this->table_name;

		if ( $record_id > 0 ) {
			$wpdb->update(
				$table_name,
				$data,
				array(
					$key_name => $record_id,
				),
				$formats,
				array( $this->get_key_format() )
			);
		} else {
			$wpdb->insert(
				$table_name,
				$data,
				$formats
			);
		}

		$redirect_url = add_query_arg(
			array(
				'page'    => $this->page_name,
				'updated' => 1,
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}
}

global $wpdb;
$qrs = new table_menu(
    $wpdb->base_prefix . 'loopis_qr_codes',
    array(
    	'id' => '%d',
    ),
    array(
        'name'   => '%s',
    	'uid'  => '%s',
    	'blog_id' => '%d',
    	'redirect'   => '%s',
    ),
    array(
    	'menu_name' => 'QR codes',
    	'menu_icon' => 'dashicons-grid-view',
        'hook_prefix' => 'loopis_qr_codes',
        'page_name' => 'loopis-qrs',
    )
);
/*
$settings = new table_menu(
    $wpdb->prefix . 'loopis_settings',
    array(
    	'id' => '%d',
    ),
    array(
        'setting_key'   => '%s',
    	'setting_value'  => '%s',
    ),
    array(
    	'menu_name' => 'Loopis Settings',
    	'menu_icon' => 'dashicons-grid-view',
        'hook_prefix' => 'loopis_settings',
        'page_name' => 'loopis-settings',
    )
);
*/

add_action('loopis_qr_codes_before_page', function(){
    ?>
    <?php
});

add_action('loopis_qr_codes_after_page', function(){
    global $wpdb;
    $codes = $wpdb->base_prefix.'loopis_qr_codes';
    $visits = $wpdb->base_prefix.'loopis_qr_visits';
    $rows = $wpdb->get_results(
			"SELECT codes.`name` AS 'name', COUNT(*) AS 'count', codes.`uid` AS 'uid' FROM {$codes} codes INNER JOIN {$visits} visit ON visit.`name`=codes.`name` GROUP BY codes.`name`;",
			ARRAY_A
		);
    

    ?>
    <h1>
		Statistik
	</h1>
    <table class="widefat striped">
		<thead>
			<tr>
				<th>Name</th>
                <th>Number of visits</th>
                <th>QR download</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr>
					<td
						colspan="<?php echo esc_attr( 3 ); ?>"
					>
						No records found.
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td>
							<?php echo esc_html( $row[ 'name' ] ); ?>
						</td>
                        <td>
							<?php echo esc_html( $row[ 'count' ] ); ?>
						</td>
                        <td>
							<button type="button" >
                            </button>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
    <?php 
});