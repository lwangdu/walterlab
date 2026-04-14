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
          <?php while ( $pub_q->have_posts() ) : $pub_q->the_post();
            // Build or fallback citation
            $citation = get_field('publication_citation');
            if ( ! $citation ) {
              $authors = get_field('pub_authors');
              $journal = get_field('pub_name');
              $page    = get_field('pub_page');
              $pub_date    = get_field('pub_date');
              $citation = trim("{$authors}. {$journal}; {$page}; {$pub_date}.");
            }

            // Link "LastName I"
            if ( $member_link && $last_name ) {
              $name_parts    = explode(' ', trim(get_the_title($member_id)));
              $first_initial = strtoupper(substr(array_shift($name_parts), 0, 1));
              $escaped_last    = preg_quote($last_name, '/');
              $escaped_initial = preg_quote($first_initial, '/');
              $pattern         = "/\b{$escaped_last}\s+{$escaped_initial}\b/u";
              $link_text       = esc_html("{$last_name} {$first_initial}");
              $linked          = '<a href="' . esc_url($member_link) . '">' . $link_text . '</a>';
              $citation        = preg_replace($pattern, $linked, esc_html($citation), 1);
            } else {
              $citation = esc_html($citation);
            }
          ?>
            <div class="citation"><?php echo wp_kses_post($citation); ?></div>
            <p>
              <?php
              $links = [];
              if ( $pmid = get_field('pmid') ) {
                $links[] = sprintf(
                  '<a href="https://pubmed.ncbi.nlm.nih.gov/%1$s" target="_blank" rel="noopener">(PMID %1$s)</a>',
                  esc_html($pmid)
                );
              }
              if ( $pdfURL = get_field('pub_pdf') ) {
                $links[] = sprintf(
                  '<a href="%1$s" target="_blank" rel="noopener">PDF</a>',
                  esc_url($pdfURL)
                );
              }
              $links[] = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url(get_permalink()),
                esc_html(get_the_title())
              );
              echo implode(' | ', $links);
              ?>
            </p>
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
