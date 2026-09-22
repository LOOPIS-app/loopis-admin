<?php
/**
 * Plugin Name: Move User Admin Tool
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Add the admin menu page.
 */
add_action( 'admin_menu', 'loopis_register_move_user_page' );

function loopis_register_move_user_page() {
	add_menu_page(
		'Move User',
		'Move User',
		'manage_options',
		'move-user',
		'loopis_move_user_page',
		'dashicons-migrate',
		75
	);
}

/*
 * Render the admin page and form.
 */
function loopis_move_user_page() {
    global $wpdb;

    $table = $wpdb->base_prefix . 'loopis_areas';

    $site_ids = $wpdb->get_col(
        "SELECT DISTINCT blog_id
         FROM {$table}"
    );

	?>
	<div class="wrap">
		<h1>Move User</h1>
        

		<?php if ( isset( $_GET['moved'] ) && '1' === $_GET['moved'] ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>User moved successfully.</p>
			</div>
		<?php endif; ?>
		<?php if ( isset( $_GET['error'] ) && 'migration_failed' === $_GET['error'] ) : ?>
			<div class="notice notice-error is-dismissible">
				<p>The user migration failed.</p>
			</div>
		<?php endif; ?>
		<?php if ( isset( $_GET['error'] ) && 'same_site' === $_GET['error'] ) : ?>
			<div class="notice notice-error is-dismissible">
				<p>The source and target sites must be different.</p>
			</div>
		<?php endif; ?>
		<?php if ( isset( $_GET['error'] ) && 'values_wrong' === $_GET['error'] ) : ?>
			<div class="notice notice-error is-dismissible">
				<p>Please provide valid user, source blog, and target blog IDs.</p>
			</div>
		<?php endif; ?>


		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="loopis_move_user">

			<?php 
            wp_nonce_field( 'loopis_move_user_action', 'loopis_move_user_nonce' ); 
            $member_users = get_users();
            ?>
                
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="user_id">User ID</label>
					</th>
					<td>
                        <select id="user_id" name="user_id" style="max-width: 175px; margin-right: 10px;">
                            <option value="">Välj medlem</option>
                            <?php foreach ($member_users as $member_user) : ?>
                                <option value="<?php echo esc_attr($member_user->ID); ?>">
                                    <?php echo esc_html($member_user->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
					</td>
				</tr>


				<tr>
					<th scope="row">
						<label for="source_blog">Från område</label>
					</th>
					<td>
						<select name="source_blog" id="source_blog" required>
							<option value="">Välj område</option>

							<?php foreach ( $site_ids as $site_id ) : ?>
								<option value="<?php echo esc_attr( $site_id ); ?>">
									<?php echo esc_html( get_blog_option( $site_id, 'blogname' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
							
				<tr>
					<th scope="row">
						<label for="target_blog">Till område</label>
					</th>
					<td>
						<select name="target_blog" id="target_blog" required>
							<option value="">Välj område</option>
							
							<?php foreach ( $site_ids as $site_id ) : ?>
								<option value="<?php echo esc_attr( $site_id ); ?>">
									 <?php echo esc_html( get_blog_option( $site_id, 'blogname' )); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

			</table>

			<?php submit_button( 'Move User' ); ?>
		</form>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			const sourceSelect = document.getElementById('source_blog');
			const targetSelect = document.getElementById('target_blog');

			function preventSameSite() {
				const sourceValue = sourceSelect.value;
				const targetValue = targetSelect.value;

				Array.from(targetSelect.options).forEach(function (option) {
					option.disabled = option.value !== '' && option.value === sourceValue;
				});

				Array.from(sourceSelect.options).forEach(function (option) {
					option.disabled = option.value !== '' && option.value === targetValue;
				});

				if (sourceValue && sourceValue === targetValue) {
					targetSelect.value = '';
				}
			}

			sourceSelect.addEventListener('change', preventSameSite);
			targetSelect.addEventListener('change', preventSameSite);

			preventSameSite();
		});
		</script>
	</div>
	<?php
}

/*
 * Handle the submitted form.
 */
add_action( 'admin_post_loopis_move_user', 'loopis_handle_move_user' );
function loopis_handle_move_user() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to perform this action.' );
	}

	check_admin_referer(
		'loopis_move_user_action',
		'loopis_move_user_nonce'
	);

	$user_id     = isset( $_POST['user_id'] )
		? absint( $_POST['user_id'] )
		: 0;

	$source_blog = isset( $_POST['source_blog'] )
		? absint( $_POST['source_blog'] )
		: 0;

	$target_blog = isset( $_POST['target_blog'] )
		? absint( $_POST['target_blog'] )
		: 0;

	$base_url = add_query_arg(
		array(
			'page' => 'move-user',
		),
		admin_url( 'admin.php' )
	);

	if ( ! $user_id || ! $source_blog || ! $target_blog ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'moved' => '0',
					'error' => 'values_wrong',
				),
				$base_url
			)
		);
		exit;
	}

	if ( $source_blog === $target_blog ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'moved' => '0',
					'error' => 'same_site',
				),
				$base_url
			)
		);
		exit;
	}

	$result = move_user(
		$user_id,
		$source_blog,
		$target_blog
	);

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'moved' => '0',
					'error' => 'migration_failed',
				),
				$base_url
			)
		);
		exit;
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'moved' => '1',
			),
			$base_url
		)
	);
	exit;
}