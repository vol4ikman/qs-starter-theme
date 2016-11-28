<?php
// call to function: build_menu_list($post, 'page',true);

// in functions.php
/**
 *  get page parent with it's children pages and return a merged array with post objects
 * @param  Object $post
 * @param  string $post_type post type
 * @return array
 */
function get_page_parent_with_children($post, $post_type = 'page', $display_parent = true){
    $menu_pages = array();
    // Set up the objects needed
    $my_wp_query = new WP_Query();
    $args = array('post_type' => $post_type, 'posts_per_page' => -1);
    $all_wp_pages = $my_wp_query->query($args);

    // get post parent
    if($post->post_parent){
        $parent_id = $post->post_parent;
    }else {$parent_id = $post->ID;}
    $parent_post = get_post($parent_id);

    // get children
    $sub_pages = get_page_children($parent_post->ID, $all_wp_pages);

    if($sub_pages){
        $menu_pages = $sub_pages;

        if($display_parent){
            // insert post parent to the begining of the array
            array_unshift($menu_pages,$parent_post);
        }
    }

    return $menu_pages;
}

/**
 * [build_menu_list description]
 * @param  [type] $post      [description]
 * @param  string $post_type [description]
 * @return [type]            [description]
 */
function build_menu_list($post, $post_type = 'page', $display_parent = true){
    global $post;
    $menu_pages_array = array();
    $menu_pages_array = get_page_parent_with_children($post, $post_type, $display_parent);
    ob_start();
        if($menu_pages_array):  ?>

            <ul class="page_menu">
                <?php foreach ($menu_pages_array as $page): ?>
                    <?php
                        if($post->ID == $page->ID){
                            $activeClass = 'active';
                        } else $activeClass = '';
                        if(!$page->post_parent){
                            $parentClass = 'parent-item';
                        }else{
                            $parentClass = '';
                        }
                    ?>
                    <li class="page-item page-item-<?php echo $page->ID; echo ' '.$activeClass. ' '. $parentClass; ?>">
                        <a href="<?php echo get_permalink($page->ID);?>">
                            <?php echo get_the_title($page->ID); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php endif;
    $output = ob_get_clean();

    return $output;
}// end of function

?>
