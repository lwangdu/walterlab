<?php
//* Enqueue GeneratePress and child theme styles.
function walterlab_enqueue_styles() {
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( get_template() )->get( 'Version' )
    );

    wp_enqueue_style(
        'walterlab-style',
        get_stylesheet_uri(),
        array( 'parent-style' ),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'walterlab_enqueue_styles' );
 
//* Enqueue Scripts
add_action( 'wp_enqueue_scripts', 'walterlab_load_scripts' );
function walterlab_load_scripts() {
    wp_enqueue_script(
        'jquery-fast-live-filter',                              // handle (no dots)
        get_stylesheet_directory_uri() . '/js/jquery.fastLiveFilter.js', // script URL
        array( 'jquery' ),                                      // depend on jQuery
        '1.0.0',                                                // version (optional)
        true                                                    // load in footer
    );
}


add_action( 'after_setup_theme', 'walterlab_generatepress_footer' );
function walterlab_generatepress_footer() {
    // 1. Remove GP’s entire footer construction
    remove_action( 'generate_footer', 'generate_construct_footer' );

    // 2. Add your custom footer
    add_action( 'generate_footer', 'walterlab_footer' );
}

function walterlab_footer() {
    // build your dynamic year + links
    $year    = date( 'Y' );
    $company = 'WALTER LAB';

    echo '<div class="site-info">';
    echo "© {$year} {$company} | ";
    echo '<a href="https://www.ucsf.edu" target="_blank" rel="noopener noreferrer">University of California, San Francisco</a> | ';
    echo '<a href="https://www.hhmi.org/" target="_blank" rel="noopener noreferrer">Howard Hughes Medical Institute</a>';
    echo '</div>';
}

function walterlab_accessible_publication_markup( $html, $publication_title = '' ) {
    if ( ! is_string( $html ) || '' === $html ) {
        return '';
    }

    $title_context = $publication_title ? wp_strip_all_tags( $publication_title ) : 'this publication';

    return preg_replace_callback(
        '/<a\b([^>]*)>(.*?)<\/a>/is',
        function( $matches ) use ( $title_context ) {
            $attributes = $matches[1];
            $link_text  = trim( wp_strip_all_tags( $matches[2] ) );

            if ( preg_match( '/\baria-label\s*=/i', $attributes ) ) {
                return $matches[0];
            }

            $label = '';

            if ( preg_match( '/^PDF$/i', $link_text ) ) {
                $label = sprintf( 'PDF for %s', $title_context );
            } elseif ( preg_match( '/^PMID\b/i', $link_text ) ) {
                $label = sprintf( '%s for %s', $link_text, $title_context );
            } elseif ( preg_match( '/^PMCID\b/i', $link_text ) ) {
                $label = sprintf( '%s for %s', $link_text, $title_context );
            }

            if ( '' === $label ) {
                return $matches[0];
            }

            return sprintf(
                '<a%s aria-label="%s">%s</a>',
                $attributes,
                esc_attr( $label ),
                $matches[2]
            );
        },
        $html
    );
}

add_filter( 'generate_back_to_top_output', 'walterlab_accessible_back_to_top_output' );
function walterlab_accessible_back_to_top_output( $output ) {
    if ( ! is_string( $output ) || '' === $output ) {
        return $output;
    }

    return preg_replace(
        '/<a\s+/i',
        '<a tabindex="-1" aria-hidden="true" ',
        $output,
        1
    );
}

add_action( 'wp_enqueue_scripts', 'walterlab_accessible_back_to_top_script', 20 );
function walterlab_accessible_back_to_top_script() {
    $script = <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    var backToTop = document.querySelector('.generate-back-to-top');

    if (!backToTop) {
        return;
    }

    function syncBackToTopAccessibility() {
        var isVisible = backToTop.classList.contains('generate-back-to-top__show');

        backToTop.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
        backToTop.tabIndex = isVisible ? 0 : -1;
    }

    syncBackToTopAccessibility();

    if ('MutationObserver' in window) {
        var observer = new MutationObserver(syncBackToTopAccessibility);
        observer.observe(backToTop, { attributes: true, attributeFilter: ['class'] });
    }

    window.addEventListener('scroll', syncBackToTopAccessibility, { passive: true });
});
JS;

    wp_add_inline_script( 'generate-back-to-top', $script, 'after' );
}

add_action( 'wp_enqueue_scripts', 'walterlab_content_accessibility_script', 25 );
function walterlab_content_accessibility_script() {
    $script = <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wp-lightbox-container .lightbox-trigger').forEach(function(button) {
        if (button.getAttribute('aria-label')) {
            return;
        }

        var figure = button.closest('figure');
        var image = figure ? figure.querySelector('img') : null;
        var caption = figure ? figure.querySelector('figcaption') : null;
        var description = '';

        if (image && image.getAttribute('alt')) {
            description = image.getAttribute('alt').trim();
        } else if (caption) {
            description = caption.textContent.trim();
        }

        button.setAttribute(
            'aria-label',
            description ? 'Enlarge image: ' + description : 'Enlarge image'
        );
    });

    var seenIds = new Map();

    document.querySelectorAll('[id]').forEach(function(element) {
        var id = element.id;

        if (!id) {
            return;
        }

        if (!seenIds.has(id)) {
            seenIds.set(id, 1);
            return;
        }

        var nextCount = seenIds.get(id) + 1;
        var newId = id + '-' + nextCount;

        while (document.getElementById(newId)) {
            nextCount += 1;
            newId = id + '-' + nextCount;
        }

        seenIds.set(id, nextCount);
        element.id = newId;
    });
});
JS;

    wp_add_inline_script( 'generate-menu', $script, 'after' );
}


// ensure block content is always a string
add_filter( 'render_block', function( $block_content ) {
    return $block_content ?? '';
}, 9 );

add_filter( 'render_block', 'walterlab_accessible_image_lightbox_buttons', 15, 2 );
function walterlab_accessible_image_lightbox_buttons( $block_content, $block ) {
    if ( ! is_string( $block_content ) || '' === $block_content ) {
        return $block_content;
    }

    if ( empty( $block['blockName'] ) || 'core/image' !== $block['blockName'] ) {
        return $block_content;
    }

    if ( false === strpos( $block_content, 'lightbox-trigger' ) ) {
        return $block_content;
    }

    if ( preg_match( '/<button\b[^>]*(?:\s|^)aria-label=/i', $block_content ) ) {
        return $block_content;
    }

    $description = '';

    if ( preg_match( '/<img\b[^>]*\balt="([^"]*)"/i', $block_content, $matches ) ) {
        $description = trim( wp_strip_all_tags( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ) ) );
    }

    if ( '' === $description && preg_match( '/<figcaption\b[^>]*>(.*?)<\/figcaption>/is', $block_content, $matches ) ) {
        $description = trim( wp_strip_all_tags( $matches[1] ) );
    }

    $aria_label = $description ? 'Enlarge image: ' . $description : 'Enlarge image';

    return preg_replace(
        '/<button\b([^>]*)data-wp-bind--aria-label="([^"]*)"([^>]*)>/i',
        '<button$1aria-label="' . esc_attr( $aria_label ) . '" data-wp-bind--aria-label="$2"$3>',
        $block_content,
        1
    );
}

add_filter( 'the_content', 'walterlab_accessible_lightbox_buttons_in_content', 25 );
function walterlab_accessible_lightbox_buttons_in_content( $content ) {
    if ( ! is_string( $content ) || false === strpos( $content, 'lightbox-trigger' ) ) {
        return $content;
    }

    return preg_replace_callback(
        '/<figure\b[^>]*class="[^"]*wp-lightbox-container[^"]*"[^>]*>.*?<\/figure>/is',
        function( $matches ) {
            $figure_html = $matches[0];

            if ( preg_match( '/<button\b[^>]*(?:\s|^)aria-label=/i', $figure_html ) ) {
                return $figure_html;
            }

            $description = '';

            if ( preg_match( '/<img\b[^>]*\balt="([^"]*)"/i', $figure_html, $image_matches ) ) {
                $description = trim( wp_strip_all_tags( html_entity_decode( $image_matches[1], ENT_QUOTES, 'UTF-8' ) ) );
            }

            if ( '' === $description && preg_match( '/<figcaption\b[^>]*>(.*?)<\/figcaption>/is', $figure_html, $caption_matches ) ) {
                $description = trim( wp_strip_all_tags( $caption_matches[1] ) );
            }

            $aria_label = $description ? 'Enlarge image: ' . $description : 'Enlarge image';

            return preg_replace(
                '/<button\b([^>]*)data-wp-bind--aria-label="([^"]*)"([^>]*)>/i',
                '<button$1aria-label="' . esc_attr( $aria_label ) . '" data-wp-bind--aria-label="$2"$3>',
                $figure_html,
                1
            );
        },
        $content
    );
}

add_filter( 'the_content', 'walterlab_disable_problematic_lightbox_markup', 30 );
function walterlab_disable_problematic_lightbox_markup( $content ) {
    if ( ! is_string( $content ) || false === strpos( $content, 'wp-lightbox-container' ) ) {
        return $content;
    }

    $content = preg_replace(
        '/<button\b[^>]*class="lightbox-trigger"[^>]*>.*?<\/button>/is',
        '',
        $content
    );

    $content = preg_replace(
        '/\sdata-wp-[a-z0-9:._-]+="[^"]*"/i',
        '',
        $content
    );

    $content = str_replace( 'wp-lightbox-container', '', $content );

    return $content;
}

// disable featured images on all posts
add_filter( 'generate_show_post_featured_image', '__return_false' );

// disable featured images on all pages
add_filter( 'generate_show_page_featured_image', '__return_false' );


add_action( 'after_setup_theme', 'walterlab_remove_gp_comment_form' );
function walterlab_remove_gp_comment_form() {
    // this unhooks the entire comments template (form + comment list)
    remove_action( 'generate_after_do_template_part', 'generate_do_comments_template', 15 );
}

// 1) Run after ACF has saved pub_authors
add_action('acf/save_post', 'wl_auto_link_pub_members', 20);
function wl_auto_link_pub_members( $post_id ) {
  // only for your Publication CPT
  if ( get_post_type( $post_id ) !== 'wl_publication' ) {
    return;
  }

  // grab the comma-separated authors string
  $authors = get_field( 'pub_authors', $post_id );
  if ( ! $authors ) {
    return;
  }

  // split and trim into names
  $names = array_map( 'trim', explode( ',', $authors ) );
  $member_ids = [];

  // for each name, look up the matching wl_member post by title
  foreach( $names as $name ) {
    $m = get_page_by_title( $name, OBJECT, 'wl_member' );
    if ( $m ) {
      $member_ids[] = $m->ID;
    }
  }

  // finally, write into the Relationship field behind the scenes
  if ( $member_ids ) {
    update_field( 'pub_members', $member_ids, $post_id );
  }
}

add_action( 'wp', function() {
    remove_action( 'generate_before_content', 'generate_featured_page_header_inside_single', 10 );
	remove_action( 'generate_after_header', 'generate_featured_page_header',10 );
} );

/*
function lw_excerpt_more($more) {
            global $post;
            return '... <a href="' . get_permalink($post->ID) . '" class="readmore">Read More</a>';
        }
        add_filter('excerpt_more', 'lw_excerpt_more');

*/
add_action( 'admin_footer', function() {
	$screen = get_current_screen();
	
	if ( 'widgets' === $screen->base ) {
		?>
			<script>
				wp.domReady( function() {
    				var unregisterPlugin = wp.plugins.unregisterPlugin;

    				unregisterPlugin( 'generatepress-content-width' );
				} );
			</script>
		<?php
	}
} );
