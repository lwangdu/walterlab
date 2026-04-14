<?php
/**
 * Template Name: Publications List
 */

get_header(); ?>

<?php
/**
 * Hooked in to wrap the content with GP’s grid/container,
 * title, etc.  You can remove or move these as you like.
 */
do_action( 'generate_before_main_content' );
do_action( 'generate_sidebar_template' );
?>

<main id="primary" class="site-main">

  <header class="entry-header">
    <h1 class="entry-title"><?php the_title(); ?></h1>
  </header>

  <div class="entry-content">
    <?php the_content(); ?>
    <?php
        $publication_count = 0;
		echo '<div class="search-input" role="search" aria-label="Filter publications">';
        echo '<label class="wl-screen-reader-text" for="search_input">Search publications</label>';
		echo '<input id="search_input" type="search" name="publication_search" placeholder="Search publications" aria-describedby="pub-search-help pub-search-status" autocomplete="off">';
        echo '<p id="pub-search-help" class="wl-screen-reader-text">Type to filter the publication list below.</p>';
        echo '<p id="pub-search-status" class="wl-screen-reader-text" role="status" aria-live="polite" aria-atomic="true"></p>';
        echo '</div>'; ?>

    <section class="publications" aria-labelledby="publications-results-heading">
      <h2 id="publications-results-heading" class="wl-screen-reader-text">Publication results</h2>
      <ul id="search_list">
        <?php
        // build member list once
        $allMemberInfoArr = wl_publication_build_member_list();

        // query all publications ordered by pub_date desc
        $args = [
          'post_type'      => 'wl_publication',
          'posts_per_page' => -1,
          'orderby'        => 'meta_value_num',
          'meta_key'       => 'pub_date',
          'order'          => 'DESC',
        ];
        $pub_query = new WP_Query( $args );

        if ( $pub_query->have_posts() ) :
          while ( $pub_query->have_posts() ) : $pub_query->the_post();
            $publication_count++;
            $postID      = get_the_ID();
            $url         = get_permalink();
            $pubAuthors  = get_field( 'pub_authors' );
            $pubName     = get_field( 'pub_name' );
            $pubPage     = get_field( 'pub_page' );
            $pubDate     = get_field( 'pub_date' );
            $pubShowDate = get_field( 'pub_show_date' );
            $pmid        = get_field( 'pmid' );
            $pmcid       = get_field( 'pmcid' );
            $pdfURL      = get_field( 'pub_pdf' );

            // generate the citation HTML
            $citation = wl_publication_citation(
              $allMemberInfoArr,
              $postID,
              $pubAuthors,
              get_the_title(),
              $pubName,
              $pubPage,
              $pubDate,
              $pubShowDate,
              $url,
              $pmid,
              $pmcid,
              $pdfURL
            );
            $citation = walterlab_accessible_publication_markup( $citation, get_the_title() );
            ?>
            <li><?php echo wp_kses_post( $citation ); ?></li>
          <?php
          endwhile;
          wp_reset_postdata();
        endif;

        $GLOBALS['walterlab_publication_count'] = $publication_count;
        ?>
      </ul>
    </section>
  </div><!-- .entry-content -->

</main><!-- #primary -->

<?php

// 2) Print your inline init in the footer, after jQuery + plugin
add_action( 'wp_footer', 'walterlab_fastlive_inline_init' );
function walterlab_fastlive_inline_init() {
    $initial_count = isset( $GLOBALS['walterlab_publication_count'] ) ? (int) $GLOBALS['walterlab_publication_count'] : 0;
    ?>
    <script type="text/javascript">
    jQuery(function(){
        var totalResults = <?php echo (int) $initial_count; ?>;
        var $status = jQuery('#pub-search-status');

        function updateSearchStatus(count) {
            var message = count === 1 ? '1 publication shown.' : count + ' publications shown.';
            if (count === totalResults) {
                message += ' Showing all results.';
            }
            $status.text(message);
        }

        jQuery('#search_input').fastLiveFilter('#search_list', {
            callback: updateSearchStatus
        });

        updateSearchStatus(totalResults);
    });
    </script>
    <?php
}

do_action( 'generate_after_main_content' );

get_footer();
