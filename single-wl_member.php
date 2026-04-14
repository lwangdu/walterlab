<?php
/**
 * Template Name: Lab Member
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

    <?php
    // ────────────────────────────────────────────────────────────────────────────
    // Example: Display member photo, type, and email
    $pID              = get_the_ID();
    $image            = get_field( 'lab_member_photo' );
    $lab_member_type  = get_post_meta( $pID, 'lab_member_type', true );
    $lab_member_email = get_post_meta( $pID, 'email', true );

    if ( is_array( $image ) && ! empty( $image['url'] ) ) : ?>
      <div class="member-photo">
        <img src="<?php echo esc_url( $image['url'] ); ?>"
             alt="<?php echo esc_attr( get_the_title() ); ?>">
      </div>
    <?php endif; ?>

    <?php if ( $lab_member_type ) : ?>
      <p class="member-type">
         <?php echo esc_html( $lab_member_type ); ?>
      </p>
    <?php endif; ?>

    <?php if ( $lab_member_email ) : ?>
      <p class="member-email">
        <a href="mailto:<?php echo esc_attr( $lab_member_email ); ?>">
          <?php echo esc_html( $lab_member_email ); ?>
        </a>
      </p>
    <?php endif; ?>

    <div class="entry-content-lab-member">
      <?php the_content(); ?>
    </div>
    <?php
    // ────────────────────────────────────────────────────────────────────────────

    // ────────────────────────────────────────────────────────────────────────────
    // “Selected Publications” section
    // 1) Build a query ($pub_q) to find any publications whose 'pub_authors' meta
    //    contains this member’s last name. (Adjust the field‐keys as needed.)
    // 2) If $pub_q has results, show the heading “Selected Publications” and loop.

    // Derive last name from member’s title
    $full_name  = get_the_title();
    $parts      = explode( ' ', trim( $full_name ) );
    $last_name  = array_pop( $parts );

    $pub_q = new WP_Query( [
      'post_type'      => 'wl_publication',
      'posts_per_page' => -1,
      'orderby'        => 'meta_value_num',
      'meta_key'       => 'pub_date',
      'order'          => 'DESC',
      'meta_query'     => [
        [
          'key'     => 'pub_authors',
          'value'   => $last_name,
          'compare' => 'LIKE',
        ],
      ],
    ] );

    if ( $pub_q->have_posts() ) : ?>
      <div id="memberPublications">
        <h2 class="member-section-title">Selected Publications</h2>
        <ul class="member-publications-list">
          <?php
          // Pre‐build any helper data (e.g. a “member list” array) if needed
          $allMemberInfoArr = wl_publication_build_member_list();

          // Now loop through each publication that matched $pub_q
          while ( $pub_q->have_posts() ) : $pub_q->the_post();
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

            // Generate the citation HTML (you can adjust this function as needed)
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

          // Reset postdata for safety
          wp_reset_postdata();
          ?>
        </ul> <!-- .member-publications-list -->
      </div>   <!-- #memberPublications -->
    <?php
    // ── Close the "if ( $pub_q->have_posts() ) :" block ─────────────────────────────────────
    endif;
    // ────────────────────────────────────────────────────────────────────────────
    ?>

    <!-- …any other member‐specific sections would go here… -->

  <?php endwhile; endif; ?>
</main>

<?php
get_footer();
?>
