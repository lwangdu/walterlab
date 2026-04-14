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
    $imageURL         = is_array($image) && ! empty($image['url'])
                        ? esc_url($image['url'])
                        : '';
    ?>
    <div id="memberPhotoBlock">
      <?php if ( $imageURL ) : ?>
        <img src="<?php echo $imageURL; ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
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
      $parts      = explode(' ', trim($full_name));
      $last_name  = array_pop($parts);

      // Find corresponding member link
      $members     = get_posts([
        'post_type'      => 'wl_member',
        'title'          => $full_name,
        'posts_per_page' => 1,
        'fields'         => 'ids',
      ]);
      $member_id   = ! empty($members) ? $members[0] : 0;
      $member_link = $member_id ? get_permalink($member_id) : '';

      // Query publications by last name
      $pub_q = new WP_Query([
        'post_type'      => 'wl_publication',
        'posts_per_page' => -1,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'pub_date',
        'order'          => 'DESC',
        'meta_query'     => [[
          'key'     => 'pub_authors',
          'value'   => $last_name,
          'compare' => 'LIKE',
        ]],
      ]);

      if ( $pub_q->have_posts() ) : ?>
        <h4>Selected Publications</h4>
        <div class="member-publications-list">
          <?php while ( $pub_q->have_posts() ) : $pub_q->the_post(); ?>
            <?php
            // Fetch your fields
            $authors  = get_field('pub_authors');
            $title    = get_the_title();
            $journal  = get_field('pub_name');
            $date     = get_field('pub_date');
            $pmid     = get_field('pmid');
            $pmcid    = get_field('pmcid');
            $pdfURL   = get_field('pub_pdf');
            ?>
            <div class="publication-item">
              <?php
              $items = [];

              // 1. Authors
              if ( $authors ) {
                $items[] = esc_html( $authors );
              }

              // 2. Title
              if ( $title ) {
                $items[] = '<strong>' . esc_html( $title ) . '</strong>';
              }

              // 3. Journal name
              if ( $journal ) {
                $items[] = esc_html( $journal );
              }

              // 4. Publication date
              if ( $date ) {
                $items[] = esc_html( $date );
              }

              // 5. PMID
              if ( $pmid ) {
                $items[] = sprintf(
                  '<a href="https://pubmed.ncbi.nlm.nih.gov/%1$s" target="_blank" rel="noopener">PMID %1$s</a>',
                  esc_html( $pmid )
                );
              }

              // 6. PMCID
              if ( $pmcid ) {
                $items[] = sprintf(
                  '<a href="https://www.ncbi.nlm.nih.gov/pmc/articles/%1$s" target="_blank" rel="noopener">PMCID %1$s</a>',
                  esc_html( $pmcid )
                );
              }

              // 7. PDF link
              if ( $pdfURL ) {
                $items[] = sprintf(
                  '<a href="%1$s" target="_blank" rel="noopener">PDF</a>',
                  esc_url( $pdfURL )
                );
              }

              // Output all pieces, comma-separated
              echo implode( ', ', $items );
              ?>
            </div>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php endif; ?>
    </div>

  <?php endwhile; endif; ?>
</main>

<?php
do_action('generate_after_main_content');
get_footer();
?>
