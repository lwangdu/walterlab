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
    // Member photo, type, and email
    $pID              = get_the_ID();
    $lab_member_type  = get_post_meta( $pID, 'lab_member_type', true );
    $lab_member_email = get_post_meta( $pID, 'email', true );
    $image            = get_field('lab_member_photo');
    $imageURL         = ( is_array( $image ) && ! empty( $image['url'] ) )
                        ? esc_url( $image['url'] )
                        : '';
    ?>
    <div id="memberPhotoBlock">
      <?php if ( $imageURL ) : ?>
        <img src="<?php echo $imageURL; ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" />
      <?php endif; ?>
      <?php if ( $lab_member_type ) : ?>
        <h4><?php echo esc_html( $lab_member_type ); ?></h4>
      <?php endif; ?>
      <?php if ( $lab_member_email ) : ?>
        <p class="labMemberEmail">
          <a href="mailto:<?php echo esc_attr( $lab_member_email ); ?>">
            <?php echo esc_html( $lab_member_email ); ?>
          </a>
        </p>
      <?php endif; ?>
    </div>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>

    <div id="memberPublications">
      <?php
      // Derive last name
      $full_name  = get_the_title();
      $parts      = explode( ' ', trim( $full_name ) );
      $last_name  = array_pop( $parts );

      // Find corresponding member link
      $members     = get_posts( [
        'post_type'      => 'wl_member',
        'title'          => $full_name,
        'posts_per_page' => 1,
        'fields'         => 'ids',
      ] );
      $member_id   = ! empty( $members ) ? $members[0] : 0;
      $member_link = $member_id ? get_permalink( $member_id ) : '';

      // Query publications by last name
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
        <h4>Selected Publications</h4>
        <div class="member-publications-list">
          <?php while ( $pub_q->have_posts() ) : $pub_q->the_post(); ?>
            <?php
            // 1) Build citation
            $raw_citation = get_field( 'publication_citation' );
            if ( $raw_citation ) {
              // Allow basic HTML in manual citations
              $citation = wp_kses_post( trim( $raw_citation ) );
            } else {
              $authors    = get_field( 'pub_authors' );
              $journal    = get_field( 'pub_name' );
              $page       = get_field( 'pub_page' );
              $date       = get_field( 'pub_date' );
              $year       = $date ? date( 'Y', strtotime( $date ) ) : '';

              // Title link
              $title_link = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url( get_permalink() ),
                esc_html( get_the_title() )
              );

              $parts = array_filter( [ $authors, $title_link, $journal, $page, $year ] );
              $citation = trim( implode( '. ', $parts ) ) . '.';
            }

            // 2) Link LastName I to member page (do not escape citation here)
            if ( $member_link && $last_name ) {
              $name_parts   = explode( ' ', get_the_title( $member_id ) );
              $first_init   = strtoupper( substr( array_shift( $name_parts ), 0, 1 ) );
              $pattern      = '/\b' . preg_quote( $last_name, '/' ) . '\s+' . preg_quote( $first_init, '/' ) . '\b/u';
              $replacement  = '<a href="' . esc_url( $member_link ) . '">' . esc_html( $last_name . ' ' . $first_init ) . '</a>';
              $citation     = preg_replace( $pattern, $replacement, $citation, 1 );
            }
            ?>
            <div class="citation"><?php echo wp_kses_post( $citation ); ?></div>
            <?php
            // 3) PMID/PDF links
            $links = [];
            if ( $pmid = get_field( 'pmid' ) ) {
              $links[] = sprintf(
                '(<a href="https://pubmed.ncbi.nlm.nih.gov/%1$s" target="_blank" rel="noopener">PMID %1$s</a>)',
                esc_html( $pmid )
              );
            }
            if ( $pdfURL = get_field( 'pub_pdf' ) ) {
              $links[] = sprintf(
                '(<a href="%1$s" target="_blank" rel="noopener">PDF</a>)',
                esc_url( $pdfURL )
              );
            }
            ?>
            <?php if ( $links ) : ?>
              <p><?php echo implode( '  ', $links ); ?></p>
            <?php endif; ?>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php endif; ?>
    </div>

  <?php endwhile; endif; ?>
</main>

<?php
get_footer();
?>
