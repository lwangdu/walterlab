<?php
/**
 * Template Name: User Profile
 */

// ——— Your original member-listing code, unchanged ———

add_action('genesis_entry_content', 'write_lab_members');

function write_lab_members(){
    
    $type = 'wl_member';
    $pageID = get_the_ID();
    $isAlumniPage = ($pageID == 167);
    
    echo '<div class="labMembers" >';
    
    //holds html for members without photos
    $noPhotoList = "";
            
    //holds html for members with photos
    $photoList = "";    
    
    if ($isAlumniPage == false) {
        //Write out Peter first, then everybody else by alphabetical order;
        $args=array(
          'p' => 76,
          'post_type' => $type
          );
        $my_query = new WP_Query($args);
        if( $my_query->have_posts() ) {
            while ($my_query->have_posts()) {
                $my_query->the_post(); 
                $name = the_title("","",false);        
                $memberPhoto = get_field('lab_member_photo'); // image field, return type = "Image Object"
                $labMemberType =  get_field('lab_member_type');                                    
                $memberURL = get_permalink();
                $photoList .= write_lab_member_block($memberPhoto['url'], $name, $labMemberType, $memberURL, "", "", false);                
            }
        }        
    }
    
    wp_reset_query(); 
    
    if ($isAlumniPage)
    {
        $args=array(
            'post_type'         => $type,
            'posts_per_page'    => -1,
            'orderby'           => 'meta_value',
            'meta_key'          => 'start_year',
            'order'             => 'ASC'
         );
    }
    else
    {
         $args=array(
            'post_type'         => $type,
            'posts_per_page'    => -1,
            'orderby'           => 'meta_value',
            'meta_key'          => 'last_name',
            'order'             => 'ASC'
        );
    }
    
    
    $my_query = new WP_Query($args);
            
            
            
    if( $my_query->have_posts() ) {
        while ($my_query->have_posts()) {
            $my_query->the_post(); 
            $name = the_title("","",false);
            $isCurrentMember = get_field('current_member');
            $memberURL = get_permalink();
            $yearJoined = get_field('');
            
            if($isCurrentMember==$isAlumniPage) continue;
        
            $memberPhoto = get_field('lab_member_photo'); // image field, return type = "Image Object"
            $labMemberType =  get_field('lab_member_type');
            $lastName = get_field('last_name');
            $startYear = get_field('start_year');
            $endYear = get_field('end_year');
            
            //skip peter, we already wrote his 
            if ($name == "Peter Walter") continue;
            
            
            if (isset($memberPhoto['url'])) {
                    $photoList .= write_lab_member_block($memberPhoto['url'], $name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumniPage);
            } else {
                $noPhotoList .= write_lab_member_no_photo_block($name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumniPage);    
            }
            
        }
    }
                    
    wp_reset_query(); 
    
    echo $photoList;
    
    echo '<div id="membersNoPhotos">';
    echo $noPhotoList;
    echo '</div>';
    
    echo '</div>';


}

function write_lab_member_block($memberPhotoURL, $name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumni){

    $out  = "<div class='labMemberBlock'>    \n";
    $out .= "  <div>\n";
    $out .= "    <a href='$memberURL'>\n";
    $out .= "      <img src='$memberPhotoURL' alt='$name' />\n";
    $out .= "    </a>\n";
    $out .= "  </div>\n";                  
    $out .= "  <div class='labMemberDetails'>\n";

    $out .= "    <h2 class='labMemberName'>\n";
       $out .= "      <a href='$memberURL'>$name</a>\n";
    $out .= "    </h2>\n";
    $out .= "    <p class'labMemberType'>$labMemberType</p>\n";
    if ($isAlumni){           
        $out .= "   <p class='labMemberTimespan'>$startYear - $endYear</p>";    
    }
    $out .= "  </div>\n";
    $out .= "</div>\n";
    
    return $out;    
}

function write_lab_member_no_photo_block($name, $labMemberType, $memberURL, $startYear, $endYear, $isAlumni){
    
    $out = "";
    $out .= "<h2 class='labMemberName'>";
       $out .= "  <a href='$memberURL'>$name</a>";
       $out .= "</h2>";
    $out .= "<p class='labMemberType'>$labMemberType";    
    if ($isAlumni){           
        $out .= "   <br/><span class='labMemberTimespan'>$startYear - $endYear</span>";    
    }
    $out .= "</p>";
    return $out;
}

// ——— end original code ———



// now the GeneratePress wrapper instead of genesis()

get_header();
do_action( 'generate_before_main_content' );
do_action( 'generate_sidebar_template' );
?>

<div id="primary" <?php generate_content_class(); ?>>
  <main id="main">

    <?php
    // standard WP loop
    if ( have_posts() ) :
      while ( have_posts() ) : the_post(); ?>
      
        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
        
        <?php
        // fire off your member grid
        write_lab_members();
        ?>

      <?php
      endwhile;
    endif;
    ?>

  </main>
</div><!-- #primary -->

<?php
do_action( 'generate_after_main_content' );
get_footer();
