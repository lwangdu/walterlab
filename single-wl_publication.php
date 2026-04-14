<?php
/**
 * Template Name: Publication
 */

get_header();
do_action('generate_before_main_content');
do_action('generate_sidebar_template');
?>

<main id="primary" class="site-main">

  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <header class="entry-header">
      <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>

    <div class="entry-content">

      <?php
      // – Gather fields
      $postID      = get_the_ID();
      $title       = get_the_title();
      $pubAuthors  = get_field('pub_authors');
      $pubName     = get_field('pub_name');
      $pubPage     = get_field('pub_page');
      $pubDate     = get_field('pub_date');
      $pubShowDate = get_field('pub_show_date');
      $pmid        = get_field('pmid');
      $pmcid       = get_field('pmcid');
      $pdfURL      = get_field('pub_pdf');

      // – Build raw citation via helper
      $allMemberInfoArr = wl_publication_build_member_list();
      $citation = wl_publication_citation(
        $allMemberInfoArr,
        $postID,
        $pubAuthors,
        $title,
        $pubName,
        $pubPage,
        $pubDate,
        $pubShowDate,
        '',
        $pmid,
        $pmcid,
        $pdfURL
      );
      $citation = walterlab_accessible_publication_markup( $citation, $title );

      // – Determine the first lab-member to link by last name
      $member_link = '';
      $last_name   = '';
      foreach ( $allMemberInfoArr as $lname => $memberID ) {
        if ( stripos( $pubAuthors, $lname ) !== false ) {
          $last_name   = $lname;
          $member_link = get_permalink( $memberID );
          break;
        }
      }

      // – Link the first last-name occurrence back to the member page
      $linkedCitation = wp_kses_post( $citation );
      if ( $member_link && $last_name ) {
        $escaped_last = preg_quote( $last_name, '/' );
        $linked_last  = '<a href="' . esc_url( $member_link ) . '">' . esc_html( $last_name ) . '</a>';
        $linkedCitation = preg_replace(
          '/\b' . $escaped_last . '\b/u',
          $linked_last,
          $linkedCitation,
          1
        );
      }
      ?>

      <section class="citationBlock" aria-labelledby="publication-citation-heading">
        <h2 id="publication-citation-heading" class="wl-screen-reader-text">Publication citation</h2>
        <?php echo $linkedCitation; ?>
      </section>
      
      <section aria-labelledby="publication-abstract-heading">
        <h2 id="publication-abstract-heading">Abstract</h2>
        <?php the_content(); ?>
      </section>

      <?php
      // – show manually‐linked Lab Member authors (ACF relationship)
      $authors = get_field('publication_authors');
      if ( $authors ) : ?>
        <section aria-labelledby="publication-authors-heading">
        <h2 id="publication-authors-heading">Authors</h2>
        <ul>
          <?php foreach ( $authors as $author ) : ?>
            <li>
              <a href="<?php echo esc_url( get_permalink( $author->ID ) ); ?>">
                <?php echo esc_html( get_the_title( $author->ID ) ); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        </section>
      <?php endif; ?>

    </div><!-- .entry-content -->

  <?php endwhile; endif; ?>

</main><!-- #primary -->

<?php
do_action('generate_after_main_content');
get_footer();
?>
