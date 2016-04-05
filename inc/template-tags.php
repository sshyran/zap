<?php
/**
 * Custom template tags for Zap 1.0
 *
 * @package WordPress
 * @subpackage Zap
 * @since Zap 1.0
 */

if ( ! function_exists( 'zap_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @since Zap 1.0
 *
 * @return void
 */
function zap_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$query_args   = array();
	$url_parts    = explode( '?', $pagenum_link );

	if ( isset( $url_parts[1] ) ) {
		wp_parse_str( $url_parts[1], $query_args );
	}

	$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
	$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

	$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

	// Set up paginated links.
	$links = paginate_links( array(
		'base'     => $pagenum_link,
		'format'   => $format,
		'total'    => $GLOBALS['wp_query']->max_num_pages,
		'current'  => $paged,
		'mid_size' => 1,
		'add_args' => array_map( 'urlencode', $query_args ),
		'prev_text' => '',
		'next_text' => '',
	) );

	if ( $links ) :

	?>
	<div class="clearfix"></div>
	<nav class="navigation paging-navigation" role="navigation">
		<div class="pagination loop-pagination">
			<?php echo $links; ?>
		</div><!-- .pagination -->
	</nav><!-- .navigation -->
	<?php
	endif;
}
endif;

if ( ! function_exists( 'zap_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @since Zap 1.0
 *
 * @return void
 */
function zap_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}

	?>
	<nav class="navigation post-navigation" role="navigation">
		<div class="nav-links">
			<?php
			if ( is_attachment() ) :
				previous_post_link( '%link', __( '<span class="meta-nav">Published In</span>%title', 'zap-lite' ) );
			else :
				previous_post_link( '%link', __( '<span class="glyphicon glyphicon-chevron-left"></span><span class="post-left">%title</span>', 'zap-lite' ) );
				next_post_link( '%link', __( '<span class="glyphicon glyphicon-chevron-right"></span><span class="post-right">%title</span>', 'zap-lite' ) );
			endif;
			?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'zap_posted_on' ) ) :
/**
 * Print HTML with meta information for the current post-date/time and author.
 *
 * @since Zap 1.0
 *
 * @return void
 */
function zap_posted_on( $post_id = '' ) {
	global $post;

	// Check if post id given
	if ( $post_id != '' ) {
		$post = get_post( $post_id );
	}

	// Set up and print post meta information.
	printf( '<span class="byline"><span class="author vcard"><a class="url fn n icon-user" href="%4$s" rel="author">%5$s</a></span></span>',
		esc_url( get_permalink() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( $post->post_author ) ),
		get_the_author_meta( 'display_name' , $post->post_author)
	);
}
endif;

/**
 * Find out if blog has more than one category.
 *
 * @since Zap 1.0
 *
 * @return boolean true if blog has more than 1 category
 */
function zap_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'zap_category_count' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'zap_category_count', $all_the_cool_cats );
	}

	if ( 1 !== (int) $all_the_cool_cats ) {
		// This blog has more than 1 category so zap_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so zap_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in zap_categorized_blog.
 *
 * @since Zap 1.0
 *
 * @return void
 */
function zap_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'zap_category_count' );
}
add_action( 'edit_category', 'zap_category_transient_flusher' );
add_action( 'save_post',     'zap_category_transient_flusher' );

/**
 * Display an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index
 * views, or a div element when on single views.
 *
 * @since Zap 1.0
 *
 * @return void
*/
function zap_post_thumbnail() {
	if ( post_password_required() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_single() ) :
	?>
	<?php
		the_post_thumbnail( 'zap-huge-width' );
	?>

	<?php else : ?>

	<a class="post-thumbnail animated bounceIn" href="<?php the_permalink(); ?>">
	<?php
		the_post_thumbnail( 'zap-full-width' );
	?>
	</a>

	<?php endif; // End is_singular()
}
