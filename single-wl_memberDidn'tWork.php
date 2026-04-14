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
    // Derive last name for publication filtering
    $full_name  = get_the_title();
    $parts      = explode( ' ', trim( $full_name ) );
    $last_name  = array_pop( $parts );

    // Find this member's page for linking
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
    ?>

    <?php if ( $pub_q->have_posts() ) : ?>
      <div class="publications">
        <?php while ( $pub_q->have_posts() ) : $pub_q->the_post(); ?>
            <?php
            // Build citation fields
            if ( get_field( 'raw_citation' ) ) {
              $citation = wp_kses_post( trim( get_field( 'raw_citation' ) ) );
            } else {
              $authors = get_field( 'pub_authors' );

              // --- NEW: Link any author who is also a lab member ---
              if ( $authors ) {
                $auth_items = array_map( 'trim', explode( ',', $authors ) );
                $linked     = [];
                foreach ( $auth_items as $item ) {
                  $member_page = get_page_by_title( $item, OBJECT, 'wl_member' );
                  if ( $member_page ) {
                    $linked[] = sprintf(
                      '<a href="%1$s">%2$s</a>',
                      esc_url( get_permalink( $member_page->ID ) ),
                      esc_html( $item )
                    );
                  } else {
                    $linked[] = esc_html( $item );
                  }
                }
                $authors = implode( ', ', $linked );
              }
              // --- END NEW CODE ---

              $journal = get_field( 'pub_name' );
              $page    = get_field( 'pub_page' );
              $date    = get_field( 'pub_date' );
              $year    = $date ? date( 'Y', strtotime( $date ) ) : '';

              // Title linked to publication page
              $title_link = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url( get_permalink() ),
                esc_html( get_the_title() )
              );

              // Assemble citation pieces
              $parts    = array_filter( [ $authors, $title_link, $journal, $page, $year ] );
              $citation = trim( implode( '. ', $parts ) ) . '.';

              //  Link the primary lab member instance (LastName I) if needed
              if ( $member_link && $last_name ) {
                $name_parts  = explode( ' ', get_the_title( $member_id ) );
                $first_init  = strtoupper( substr( array_shift( $name_parts ), 0, 1 ) );
                $pattern     = '/\b' . preg_quote( $last_name, '/' ) . '\s+' . preg_quote( $first_init, '/' ) . '\b/u';
                $replacement = '<a href="' . esc_url( $member_link ) . '">' . esc_html( $last_name . ' ' . $first_init ) . '</a>';
                $citation    = preg_replace( $pattern, $replacement, $citation, 1 );
              }
            }
            ?>

            <div class="citation"><?php echo wp_kses_post( $citation ); ?></div>

            <?php
            // PMID / PDF links
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

  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
