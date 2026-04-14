<?php
/**
 * Template Name: User Profile
 */

add_action('genesis_entry_content', 'write_lab_members');

function write_lab_members() {
    $type = 'wl_member';
    $pageID = get_the_ID();
    $isAlumniPage = ($pageID == 167);

    // HTML strings
    $photoList = "";
    $noPhotoList = "";

    // Include Peter Walter if not alumni
    if (!$isAlumniPage) {
        $args = array(
            'p' => 76,
            'post_type' => $type
        );
        $my_query = new WP_Query($args);
        if ($my_query->have_posts()) {
            while ($my_query->have_posts()) {
                $my_query->the_post(); 
                $name = the_title("", "", false);        
                $memberPhoto = get_field('lab_member_photo');
                $labMemberType = get_field('lab_member_type');                                    
                $memberURL = get_permalink();
                $photoList .= write_lab_member_block($memberPhoto['url'], $name, $labMemberType, $memberURL, "", "", false);                
            }
        }
        wp_reset_postdata();
    }

    // Query all other members
    $args = array(
        'post_type' => $type,
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => $isAlumniPage ? 'start_year' : 'last_name',
        'order' => 'ASC'
    );

    $my_query = new WP_Query($args);
    if ($my_query->have_posts()) {
        while ($my_query->have_posts()) {
            $my_query->the_post(); 
            $name = the_title("", "", false);
            $isCurrentMember = get_field('current_member');
            $memberURL = get_permalink();
            if ($isCurrentMember == $isAlumniPage) continue;
            if ($name == "Peter Walter") continue;

            $memberPhoto = get_field('lab_member_photo');
            $labMemberType = get_field('lab_member_type');
            $startYear = get_field('start_year');
            $endYear = get_field('end_year');

            if (!empty($memberPhoto['url'])) {
                $photoList .= write_lab_member_block($memberPhoto['url'], $name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumniPage);
            } else {
                $noPhotoList .= write_lab_member_no_photo_block($name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumniPage);
            }
        }
    }
    wp_reset_postdata();

    // OUTPUT the two blocks separated properly
    echo '<div class="labMembers" id="labmemberblock">';
    echo $photoList;
    echo '</div>';

    echo '<div class="members-spacer" aria-hidden="true"></div>';

    echo '<div id="membersNoPhotos">';
    echo $noPhotoList;
    echo '</div>';
}

function write_lab_member_block($memberPhotoURL, $name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumni) {
    $out  = "<div class='labMemberBlock'>\n";
    $out .= "  <a class='labMemberCardLink' href='" . esc_url( $memberURL ) . "'>\n";
    $out .= "    <div>\n";
    $out .= "      <img src='" . esc_url( $memberPhotoURL ) . "' alt='' />\n";
    $out .= "    </div>\n";                  
    $out .= "  <div class='labMemberDetails'>\n";
    $out .= "    <h2 class='labMemberName'>" . esc_html( $name ) . "</h2>\n";
    $out .= "    <p class='labMemberType'>" . esc_html( $labMemberType ) . "</p>\n";
    if ($isAlumni) {
        $out .= "   <p class='labMemberTimespan'>" . esc_html( $startYear ) . " - " . esc_html( $endYear ) . "</p>\n";    
    }
    $out .= "  </div>\n";
    $out .= "  </a>\n";
    $out .= "</div>\n";
    return $out;    
}

function write_lab_member_no_photo_block($name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumni) {
    $out = "<h2 class='labMemberName'><a href='" . esc_url( $memberURL ) . "'>" . esc_html( $name ) . "</a></h2>\n";
    $out .= "<p class='labMemberType'>" . esc_html( $labMemberType );
    if ($isAlumni) {
        $out .= "<br/><span class='labMemberTimespan'>" . esc_html( $startYear ) . " - " . esc_html( $endYear ) . "</span>";
    }
    $out .= "</p>\n";
    return $out;
}

// Now the GeneratePress wrapper instead of Genesis
get_header();
do_action('generate_before_main_content');
do_action('generate_sidebar_template');
?>

<div id="primary" <?php generate_content_class(); ?>>
  <main id="main">
    <?php
    if ( have_posts() ) :
      while ( have_posts() ) : the_post(); ?>
        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
        <?php write_lab_members(); ?>
      <?php endwhile;
    endif;
    ?>
  </main>
</div><!-- #primary -->

<?php
do_action('generate_after_main_content');
get_footer();
