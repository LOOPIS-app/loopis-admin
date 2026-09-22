<?php


// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

/**
 * Migrate user function for user/admin.
 *
 */
 
function move_user(int $user_id, int $source_blog_id, int $target_blog_id){

    $user_migration = migrate_user_to_site( $user_id,  $source_blog_id, $target_blog_id );
    if (is_wp_error( $user_migration )){
        return $user_migration;
    }
    $posts_migration = migrate_user_active_posts($user_id,$source_blog_id,$target_blog_id);
    if (is_wp_error( $posts_migration )){
        return $posts_migration;
    }
    return true;
}

function migrate_user_to_site( int $user_id, int $source_blog_id, int $target_blog_id ) {
    $user = get_user_by( 'id', $user_id );

    if ( ! $user ) {
        return new WP_Error( 'invalid_user', 'User does not exist.' );
    }

    if ( ! get_site( $source_blog_id ) || ! get_site( $target_blog_id ) ) {
        return new WP_Error( 'invalid_site', 'Source or target site does not exist.' );
    }

    switch_to_blog( $source_blog_id );

    $source_user = new WP_User( $user_id );
    $roles       = $source_user->roles;
    $source_user->set_role( 'member_archived' );

    restore_current_blog();

    switch_to_blog( $target_blog_id );

    if (!is_user_member_of_blog( $user_id, $target_blog_id )){
        $result = add_user_to_blog(
            $target_blog_id,
            $user_id,
            'member'
        );

        if ( is_wp_error( $result ) ) {
            restore_current_blog();
            return $result;
        }
    }

    $target_user = new WP_User( $user_id );

    foreach($roles as $role){
        if(!in_array( $role, $target_user->roles, true )){
            $target_user->add_role($role);
        }
    }
    $target_user->remove_role('member_archived');

    restore_current_blog();

    return array(
        'user_id'      => $user_id,
        'source_site'  => $source_blog_id,
        'target_site'  => $target_blog_id,
    );
}


/**
 * Migrate user function for user/admin.
 *
 */
function migrate_user_active_posts( int $user_id, int $source_blog_id, int $target_blog_id ) {
    $source_blog_id = absint( $source_blog_id );
    $target_blog_id = absint( $target_blog_id );
    $user_id   = absint( $user_id );

    if ( ! $user_id || ! $source_blog_id || ! $target_blog_id || $source_blog_id === $target_blog_id ) {
        return new WP_Error(
            'invalid_value',
            'Missing, invalid, or identical source and target site.'
        );
    }

    if ( ! get_site( $source_blog_id ) || ! get_site( $target_blog_id ) ) {
        return new WP_Error(
            'invalid_site',
            'The source or target site does not exist.'
        );
    }

    $original_blog_id = get_current_blog_id();

    $moved             = array();
    $errors             = array();

    switch_to_blog( $source_blog_id );

    $post_ids = get_posts(
        array(
            'author'         => $user_id,
            'post_status'    => 'publish',
            'post_type'      => 'any',
            'posts_per_page' => -1,
            'category__in'   =>loopis_cats(['old', 'new']),
            'fields'         => 'ids',
            ),
        );

    foreach ( $post_ids as $post_id ) {
        try {
            $target_post_id = move_post(
                (int) $post_id,
                $source_blog_id,
                $target_blog_id
            );

            $moved[ $post_id ] = $target_post_id;
        } catch ( Throwable $e ) {
            $errors[ $post_id ] = $e->getMessage();
        }
    }


    switch_to_blog( $original_blog_id );

    return array(
        'moved'  => $moved,
        'errors' => $errors,
    );
}


function move_post(
    int $post_id,
    int $source_blog_id,
    int $target_blog_id
): int {
    if ( ! is_multisite() ) {
        throw new Exception( 'This function requires WordPress multisite.' );
    }


    $site_ids = array_map(
        'intval',
        get_sites(
            array(
                'fields' => 'ids',
                'number' => 0,
            )
        )
    );

    if (
        ! in_array( $source_blog_id, $site_ids, true ) ||
        ! in_array( $target_blog_id, $site_ids, true )
    ) {
        throw new Exception( 'Invalid source or target blog ID.' );
    }

    if ( $source_blog_id === $target_blog_id ) {
        throw new Exception( 'Source and target blogs must be different.' );
    }

    $original_blog_id = get_current_blog_id();

    switch_to_blog( $source_blog_id );
    $migration_ids = get_post_meta($post_id, 'migration_ids',true);
    
    if (is_array($migration_ids)){
        if(array_key_exists( $target_blog_id, $migration_ids )){
            $new_post_id = (int) $migration_ids[$target_blog_id];
            remove_post($post_id, 'migrated', $target_blog_id, $new_post_id);
            switch_to_blog( $target_blog_id );
            revive_post($new_post_id, 'migrated');
            switch_to_blog(  $original_blog_id );
            return $new_post_id;
        }
    }
    try {
        $source_post = get_post( $post_id );

        if ( ! $source_post instanceof WP_Post ) {
            throw new Exception( 'Source post not found.' );
        }

        $meta_rows = get_post_meta( $post_id );
        $terms_by_taxonomy = array();

        foreach ( get_object_taxonomies( $source_post->post_type, 'names' ) as $taxonomy ) {
            $terms = get_the_terms( $post_id, $taxonomy );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $terms_by_taxonomy[ $taxonomy ][] = array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }
        }
        
        $post_data = array(
            'post_author'           => (int) $source_post->post_author,
            'post_date'             => $source_post->post_date,
            'post_date_gmt'         => $source_post->post_date_gmt,
            'post_content'          => $source_post->post_content,
            'post_title'            => $source_post->post_title,
            'post_excerpt'          => $source_post->post_excerpt,
            'post_status'           => $source_post->post_status,
            'comment_status'        => $source_post->comment_status,
            'ping_status'           => $source_post->ping_status,
            'post_password'         => $source_post->post_password,
            'post_name'             => $source_post->post_name,
            'to_ping'               => $source_post->to_ping,
            'pinged'                => $source_post->pinged,
            'post_modified'         => current_time( 'mysql' ),
            'post_modified_gmt'     => current_time( 'mysql', true ),
            'post_content_filtered' => $source_post->post_content_filtered,
            'post_parent'           => 0,
            'post_type'             => $source_post->post_type,
            'post_mime_type'        => $source_post->post_mime_type,
            'menu_order'            => (int) $source_post->menu_order,
        );
    } finally {
        restore_current_blog();
    }

    switch_to_blog( $target_blog_id );

    try {
        if ( ! post_type_exists( $post_data['post_type'] ) ) {
            throw new Exception(
                sprintf(
                    'Post type "%s" does not exist on the target blog.',
                    $post_data['post_type']
                )
            );
        }

        $new_post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $new_post_id ) ) {
            throw new Exception( $new_post_id->get_error_message() );
        }

        $new_post_id = (int) $new_post_id;
        if (!is_array($migration_ids)) {
            $migration_ids = [];
        }

        $migration_ids[$target_blog_id] = $new_post_id;
        $migration_ids[$source_blog_id] = $post_id;

        update_post_meta($new_post_id, 'migration_ids', $migration_ids);

        foreach ( $meta_rows as $meta_key => $values ) {
            foreach ( $values as $meta_value ) {
                add_post_meta(
                    $new_post_id,
                    $meta_key,
                    maybe_unserialize( $meta_value )
                );
            }
        }
        update_post_meta($new_post_id,'fetcher', null);
        update_post_meta($new_post_id,'participants', null);

        $url_map = copy_post_attachments(
	        $post_id,
	        $new_post_id,
	        $source_blog_id,
	        $target_blog_id
        );


        $target_post = get_post($new_post_id);
        if ( $target_post instanceof WP_Post && ! empty( $url_map ) ) {
        	$updated_content = str_replace(
        		array_keys( $url_map ),
        		array_values( $url_map ),
        		$target_post->post_content
        	);

        	if ( $updated_content !== $target_post->post_content ) {
        		wp_update_post(
        			array(
        				'ID'           => $new_post_id,
        				'post_content' => $updated_content,
        			)
        		);
        	}
        }

        foreach ( $terms_by_taxonomy as $taxonomy => $terms ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $target_term_ids = array();

            foreach ( $terms as $term_data ) {
                $target_term = get_term_by(
                    'slug',
                    $term_data['slug'],
                    $taxonomy
                );

                if ( $target_term && ! is_wp_error( $target_term ) ) {
                    $target_term_ids[] = (int) $target_term->term_id;
                    continue;
                }

                $inserted_term = wp_insert_term(
                    $term_data['name'],
                    $taxonomy,
                    array(
                        'slug' => $term_data['slug'],
                    )
                );

                if ( is_wp_error( $inserted_term ) ) {
                    if ( 'term_exists' === $inserted_term->get_error_code() ) {
                        $existing_id = $inserted_term->get_error_data();

                        if ( $existing_id ) {
                            $target_term_ids[] = (int) $existing_id;
                        }
                    }

                    continue;
                }

                $target_term_ids[] = (int) $inserted_term['term_id'];
            }

            if ( ! empty( $target_term_ids ) ) {
                $result = wp_set_post_terms(
                    $new_post_id,
                    $target_term_ids,
                    $taxonomy,
                    false
                );

                if ( is_wp_error( $result ) ) {
                    throw new Exception(
                        sprintf(
                            'Could not assign taxonomy "%s": %s',
                            $taxonomy,
                            $result->get_error_message()
                        )
                    );
                }
            }
        }
    } finally {
        restore_current_blog();
    }
    switch_to_blog( $source_blog_id );
    if(!function_exists('add_admin_comment')){
        include_once LOOPIS_THEME_DIR .'/includes/functions/user/admin-post-comment.php';
    }
    update_post_meta($post_id, 'migration_ids', $migration_ids);
    remove_post($post_id, 'migrated',$target_blog_id, $new_post_id);
    loopis_ledger_add_post('submitted', $post_data['post_author'] , $post_id, ['timestamp' => current_time('Y-m-d H:i:s'), 'description' => 'revived', 'clovers'=>0]);
    switch_to_blog( $original_blog_id );

    return $new_post_id;
}

function revive_post(int $post_id, string $description = ''){
    // Set post meta
    $timestamp = current_time('Y-m-d H:i:s');
	wp_set_object_terms( $post_id, null, 'category' ); 
	wp_set_object_terms( $post_id, 'old', 'category' );
	update_post_meta($post_id,'remove_date', null);
    $author_id = get_post_field( 'post_author', $post_id );
	update_post_meta($post_id,'extend_date', current_time('Y-m-d H:i:s'));
    loopis_ledger_add_post('submitted', $author_id , $post_id, ['timestamp' => $timestamp, 'description' => $description, 'clovers'=>0]);
	
	// Leave comment by Nisse
    add_admin_comment ('<p class="unremove">🧟 Annons migrerad tillbaka.</p>', $post_id, 4);
}

function remove_post(int $post_id, string $description = '',  int $blog_id = 1, int $new_post_id = 1) {
    // Set post meta
	$timestamp = current_time('Y-m-d H:i:s');
	wp_set_object_terms( $post_id, null, 'category' ); 
	wp_set_object_terms( $post_id, 'removed', 'category' );
	$author_id = get_post_field( 'post_author', $post_id );
	$fetcher_id = (int) get_post_meta($post_id,'fetcher', true);
	
	update_post_meta($post_id,'fetcher', null);
    update_post_meta($post_id,'participants', null);
	update_post_meta($post_id,'remove_date', $timestamp);

	// Update ledger
	loopis_ledger_add_post('removed', $author_id , $post_id, ['timestamp' => $timestamp, 'description' => $description]);
	
	// Leave comment by author
	add_admin_comment('<p class="migrated">
		❌ Denna annons har flyttats till :  <a href="' . esc_url(add_query_arg('p',(int) $new_post_id, get_home_url( (int) $blog_id, '/' ))) .'">'. get_blog_option( $blog_id, 'blogname' ) .'</a></p>', $post_id, 4);
  
}


/**
 * Copy all attachments from one post to another.
 *
 */
function copy_post_attachments(
	int $source_post_id,
	int $target_post_id,
	int $source_blog_id,
	int $target_blog_id
): array {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachments = array();

	switch_to_blog( $source_blog_id );

    $meta_image_2 = get_post_meta($source_post_id, 'image_2', true) ?? '';
    $meta_image_3 = get_post_meta($source_post_id, 'image_3', true) ?? '';

	$attachment_ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_parent'    => $source_post_id,
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	$thumbnail_id = (int) get_post_thumbnail_id( $source_post_id );

	if ( $thumbnail_id && ! in_array( $thumbnail_id, $attachment_ids, true ) ) {
		$attachment_ids[] = $thumbnail_id;
	}

	$source_upload_dir = wp_upload_dir();

	foreach ( $attachment_ids as $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment instanceof WP_Post ) {
			continue;
		}

		$source_url = wp_get_attachment_url( $attachment_id );

		if ( ! $source_url ) {
			continue;
		}

		$attachments[] = array(
			'old_id'          => $attachment_id,
			'url'             => $source_url,
			'title'           => $attachment->post_title,
            'author'           => $attachment->post_author,
			'caption'         => $attachment->post_excerpt,
			'description'    => $attachment->post_content,
			'mime_type'       => $attachment->post_mime_type,
			'post_date'       => $attachment->post_date,
			'alt'             => get_post_meta(
				$attachment_id,
				'_wp_attachment_image_alt',
				true
			),
			'metadata'        => wp_get_attachment_metadata( $attachment_id ),
			'upload_baseurl'  => $source_upload_dir['baseurl'],
			'is_thumbnail'    => ( $attachment_id === $thumbnail_id ),
		);
	}

	restore_current_blog();

	$url_map = array();
	$new_thumbnail_id = 0;

	switch_to_blog( $target_blog_id );

	$target_upload_dir = wp_upload_dir();

	foreach ( $attachments as $attachment_data ) {
		$url = $attachment_data['url'];

		$tmp_file = download_url( $url, 300 );

		if ( is_wp_error( $tmp_file ) ) {
			error_log(
				sprintf(
					'Could not download attachment %d: %s',
					$attachment_data['old_id'],
					$tmp_file->get_error_message()
				)
			);

			continue;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );
		$name = $path ? wp_basename( $path ) : 'attachment';

		$file_array = array(
			'name'     => sanitize_file_name( $name ),
			'tmp_name' => $tmp_file,
		);

		$new_attachment_id = media_handle_sideload(
			$file_array,
			$target_post_id,
			$attachment_data['title'],
			array(
				'post_title'     => $attachment_data['title'],
                'post_author'     => $attachment_data['author'],
				'post_excerpt'   => $attachment_data['caption'],
				'post_content'   => $attachment_data['description'],
				'post_date'      => $attachment_data['post_date'],
				'post_status'    => 'inherit',
				'post_mime_type' => $attachment_data['mime_type'],
			)
		);

		if ( is_wp_error( $new_attachment_id ) ) {
			@unlink( $tmp_file );

			error_log(
				sprintf(
					'Could not import attachment %d: %s',
					$attachment_data['old_id'],
					$new_attachment_id->get_error_message()
				)
			);

			continue;
		}

		$new_attachment_id = (int) $new_attachment_id;

        if ((int) $attachment_data['old_id'] === (int) $meta_image_2){
            update_post_meta(
				$target_post_id,
				'image_2',
				$new_attachment_id
			);
        }
        if ((int) $attachment_data['old_id'] === (int) $meta_image_3){
            update_post_meta(
				$target_post_id,
				'image_3',
				$new_attachment_id
			);
        }

		if ( $attachment_data['alt'] !== '' ) {
			update_post_meta(
				$new_attachment_id,
				'_wp_attachment_image_alt',
				$attachment_data['alt']
			);
		}

		$new_url = wp_get_attachment_url( $new_attachment_id );

		if ( $new_url ) {
			$url_map[ $attachment_data['url'] ] = $new_url;
		}


		$old_metadata = $attachment_data['metadata'];
		$new_metadata = wp_get_attachment_metadata( $new_attachment_id );

		if (
			is_array( $old_metadata ) &&
			is_array( $new_metadata ) &&
			! empty( $old_metadata['file'] ) &&
			! empty( $new_metadata['file'] )
		) {
			$old_file_url = trailingslashit(
				$attachment_data['upload_baseurl']
			) . ltrim( $old_metadata['file'], '/' );

			$new_file_url = trailingslashit(
				$target_upload_dir['baseurl']
			) . ltrim( $new_metadata['file'], '/' );

			$url_map[ $old_file_url ] = $new_file_url;

			if ( ! empty( $old_metadata['sizes'] ) ) {
				foreach ( $old_metadata['sizes'] as $size_name => $old_size ) {
					if (
						empty( $old_size['file'] ) ||
						empty( $new_metadata['sizes'][ $size_name ]['file'] )
					) {
						continue;
					}

					$old_size_url = trailingslashit(
						dirname( $old_file_url )
					) . $old_size['file'];

					$new_size_url = trailingslashit(
						dirname( $new_file_url )
					) . $new_metadata['sizes'][ $size_name ]['file'];

					$url_map[ $old_size_url ] = $new_size_url;
				}
			}
		}

		if ( $attachment_data['is_thumbnail'] ) {
			$new_thumbnail_id = $new_attachment_id;
		}
	}

	if ( $new_thumbnail_id ) {
		set_post_thumbnail( $target_post_id, $new_thumbnail_id );
	}

	restore_current_blog();

	return $url_map;
}