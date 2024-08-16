function custom_post_type() {
 
    // Set UI labels for Custom Post Type
    $labels = array(
        'name' => _x( 'Glossary', 'Post Type General Name', 'twentytwentyone' ),
        'singular_name' => _x( 'Glossary Term', 'Post Type Singular Name', 'twentytwentyone' ),
        'menu_name' => __( 'Glossary', 'twentytwentyone' ),
        'parent_item_colon' => __( 'Parent Glossary Term', 'twentytwentyone' ),
        'all_items' => __( 'All Glossary Terms', 'twentytwentyone' ),
        'view_item' => __( 'View Glossary Term', 'twentytwentyone' ),
        'add_new_item' => __( 'Add New Glossary Term', 'twentytwentyone' ),
        'add_new' => __( 'Add New', 'twentytwentyone' ),
        'edit_item' => __( 'Edit Glossary Term', 'twentytwentyone' ),
        'update_item' => __( 'Update Glossary Term', 'twentytwentyone' ),
        'search_items' => __( 'Search Glossary Terms', 'twentytwentyone' ),
        'not_found' => __( 'Not Found', 'twentytwentyone' ),
        'not_found_in_trash' => __( 'Not found in Trash', 'twentytwentyone' ),
    );
 
    // Set other options for Custom Post Type
    $args = array(
        'label' => __( 'glossary', 'twentytwentyone' ),
        'description' => __( 'Glossary terms and definitions', 'twentytwentyone' ),
        'labels' => $labels,
        // Features this CPT supports in Post Editor
        'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        'taxonomies' => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
         * Parent and child items. A non-hierarchical CPT
         * is like Posts.
         */
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
    );
 
    // Registering your Custom Post Type
    register_post_type( 'glossary', $args );
}
 
/* Hook into the 'init' action so that the function
 * Containing our post type registration is not
 * unnecessarily executed.
 */
add_action( 'init', 'custom_post_type', 0 );


function glossary_template_include( $template ) {
    if ( is_singular( 'glossary' ) ) {
        $template = locate_template( array( 'single-glossary.php' ) );
    }
    return $template;
}
add_filter( 'template_include', 'glossary_template_include' );



function glossary_shortcode() {
  ?>
  <div class="glossary-filter">
    <ul>
      <?php
      $alphabet = range('A', 'Z');
      foreach ($alphabet as $letter) {
        echo '<li><a href="#'. $letter. '">'. $letter. '</a></li>';
      }
      ?>
    </ul>
    <input type="search" id="glossary-search" placeholder="Search glossary terms">
  </div>

  <div class="glossary-terms">
    <?php
    $args = array(
      'post_type' => 'glossary',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
      $terms_by_letter = array();
      while ($query->have_posts()) {
        $query->the_post();
        $title = get_the_title();
        $content = apply_filters('the_content', get_the_content()); // Use the_content filter
        $first_letter = strtoupper(substr($title, 0, 1));
        $terms_by_letter[$first_letter][] = array(
          'title' => $title,
          'content' => $content
        );
      }
      wp_reset_postdata();

      foreach ($terms_by_letter as $letter => $terms) {
        ?>
        <h2 id="<?php echo $letter;?>"><?php echo $letter;?></h2>
        <ul class="glossary-term-container">
          <?php foreach ($terms as $term) { ?>
            <li class="glossary-term-itms">
              <a href="#" class="glossary-term-link" data-title="<?php echo esc_attr($term['title']); ?>" data-content="<?php echo esc_attr($term['content']); ?>"><?php echo $term['title'];?></a>
            </li>
          <?php } ?>
        </ul>
        <?php
      }
    } else {
      echo '<p>No glossary terms found.</p>';
    }
    ?>
  </div>

  <!-- The Modal -->
  <div id="glossary-popup" class="popup">
    <div class="popup-content">
      <span class="close">&times;</span>
      <h2 id="popup-title"></h2>
      <div id="popup-content"></div>
    </div>
  </div>

  <?php
}

add_shortcode('glossary', 'glossary_shortcode');


function glossary_js() {
  ?>
  <script>
    jQuery(document).ready(function($) {
      $('.glossary-filter a').on('click', function(e) {
        e.preventDefault();
        var letter = $(this).attr('href').replace('#', '');
        var target = $('h2#' + letter);
        if (target.length) {
          $('html, body').animate({
            scrollTop: target.offset().top - 100 // adjust the offset as needed
          }, 500);
        }
      });

      // Add event listener for search input
      $('#glossary-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.glossary-terms ul li').each(function() {
          var termText = $(this).text().toLowerCase();
          if (termText.indexOf(searchTerm) === -1) {
            $(this).hide();
          } else {
            $(this).show();
          }
        });
      });

      // Popup functionality
      $('.glossary-term-link').on('click', function(e) {
        e.preventDefault();
        var title = $(this).data('title');
        var content = $(this).data('content');
        $('#popup-title').text(title);
        $('#popup-content').html(content); // Use .html() to correctly parse HTML content
        $('body').addClass('no-scroll'); // Disable background scrolling
        $('#glossary-popup').addClass('fullscreen').show();
      });

      $('.close').on('click', function() {
        $('body').removeClass('no-scroll'); // Enable background scrolling
        $('#glossary-popup').hide();
      });

      $(window).on('click', function(event) {
        if ($(event.target).is('#glossary-popup')) {
          $('body').removeClass('no-scroll'); // Enable background scrolling
          $('#glossary-popup').hide();
        }
      });
    });
  </script>
  <?php
}

add_action('wp_footer', 'glossary_js');
