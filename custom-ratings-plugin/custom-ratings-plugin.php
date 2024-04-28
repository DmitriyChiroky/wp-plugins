<?php
/*
Plugin Name: Custom Ratings Plugin
*/





/* 
* Добавляем скрипт и стили для медиа-загрузчика только на странице настроек плагина
 */
function posts_rating_admin_scripts($hook) {
    if ('toplevel_page_posts-rating' != $hook) {
        return;
    }
    wp_enqueue_script('posts_rating-admin-script', plugin_dir_url(__FILE__) . 'js/posts_rating-admin-script.js', array('jquery'), null, true);
    wp_enqueue_style('posts_rating-admin-style', plugin_dir_url(__FILE__) . 'css/posts_rating-admin-style.css');
}
add_action('admin_enqueue_scripts', 'posts_rating_admin_scripts');




/* 
Enqueue custom stylesheet for frontend
 */
function custom_posts_enqueue_frontend_styles() {
    wp_enqueue_style('posts_rating-admin-style', plugin_dir_url(__FILE__) . 'css/custom-posts-frontend-styles.css');
}
add_action('wp_enqueue_scripts', 'custom_posts_enqueue_frontend_styles');





/* 
* Добавление страницы в админ-меню
 */
function custom_posts_rating_page_menu() {
    add_menu_page(
        'Posts Rating',
        'Posts Rating',
        'manage_options',
        'posts-rating',
        'custom_posts_rating_page_content',
        'dashicons-star-filled', // Иконка
        8 // Позиция в меню
    );
}
add_action('admin_menu', 'custom_posts_rating_page_menu');






/* 
custom_register_rating_taxonomy
 */
function custom_register_rating_taxonomy() {
    $labels = array(
        'name'              => 'Rating Categories',
        'singular_name'     => 'Rating Category',
        'search_items'      => 'Search Rating Categories',
        'all_items'         => 'All Rating Categories',
        'parent_item'       => 'Parent Rating Category',
        'parent_item_colon' => 'Parent Rating Category:',
        'edit_item'         => 'Edit Rating Category',
        'update_item'       => 'Update Rating Category',
        'add_new_item'      => 'Add New Rating Category',
        'new_item_name'     => 'New Rating Category Name',
        'menu_name'         => 'Rating Categories',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'rating-category'),
    );

    register_taxonomy('rating_category', 'post', $args);

    // Добавление терминов
    $terms = array(
        array(
            'name' => 'Low',
            'slug' => 'low-rating',
            'description' => 'Low Rating',
        ),
        array(
            'name' => 'Medium',
            'slug' => 'medium-rating',
            'description' => 'Medium Rating',
        ),
        array(
            'name' => 'High',
            'slug' => 'high-rating',
            'description' => 'High Rating',
        ),
    );

    foreach ($terms as $term) {
        if (!term_exists($term['name'], 'rating_category')) {
            wp_insert_term($term['name'], 'rating_category', $term);
        }
    }
}
add_action('init', 'custom_register_rating_taxonomy');










/* 
* Создание страницы
 */
function custom_posts_rating_page_content() {
    // Определите текущую страницу
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    // Get sorting parameters from GET request
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'rating';
    $order = isset($_GET['order']) ? $_GET['order'] : 'desc';

    // Get selected category ID from GET parameter
    $category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

    // Add category filter if a category is selected
    if ($category_id > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'rating_category',
                'field' => 'id',
                'terms' => $category_id,
            ),
        );
    }

    // Определите параметры запроса для WP_Query
    $args = array(
        'post_type' => 'post', // Тип постов
        'posts_per_page' => 5, // Количество постов на странице
        'paged' => $paged,
        'orderby' => $orderby,
        'order' => $order,
        'meta_key' => 'rating', // Sort by the 'rating' meta key
        'meta_type' => 'NUMERIC' // Treat the meta value as numeric for sorting
    );

    // Add category filter if a category is selected
    if ($category_id > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'rating_category',
                'field' => 'id',
                'terms' => $category_id,
            ),
        );
    }

    // Создание нового запроса
    $query = new WP_Query($args);

?>

    <?php
    echo '<h1>Posts Rating</h1>';
    ?>
    <div class="wcl-block-1">
        <div class="data-row">
            <div class="data-col">
                <?php
                // Display category select dropdown
                echo '<h2>Filter by Ratings Category</h2>';
                echo '<form method="get" action="" class="wcl-categories-filter">';
                echo '<input type="hidden" name="page" value="posts-rating">';
                echo '<select name="category">';
                echo '<option value="0">All Categories</option>';

                $categories = get_terms(array(
                    'taxonomy' => 'rating_category',
                    'hide_empty' => false,
                ));

                foreach ($categories as $category) {
                    echo '<option value="' . $category->term_id . '"';
                    if ($category_id == $category->term_id) {
                        echo ' selected';
                    }
                    echo '>' . $category->name . '</option>';
                }

                echo '</select>';
                echo '<input type="submit" value="Filter">';
                echo '</form>';
                ?>
            </div>

            <div class="data-col">
                <div class="shortcode-field">
                    <label for="shortcode">Shortcode:</label>
                    <input type="text" id="shortcode" name="shortcode" value="[best_rated_posts]" readonly>
                </div>
            </div>
        </div>
    </div>



    <?php
    // Начало таблицы
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Title</th>';
    echo '<th>Category Rating</th>';
    echo '<th><a href="?page=posts-rating&orderby=rating&order=asc">Rating &#8593;</a> | <a href="?page=posts-rating&orderby=rating&order=desc">Rating &#8595;</a></th>';
    echo '<th>Change Rating</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="test1">';

    // Начало цикла
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            // Получение рейтинга поста
            $rating = get_post_meta(get_the_ID(), 'rating', true);
            $rating = !empty($rating) ? $rating : '—';

            // Определение категории рейтинга
            $rating_category = '';
            if ($rating >= 0 && $rating <= 30) {
                $rating_category = 'Low';
            } elseif ($rating > 30 && $rating <= 70) {
                $rating_category = 'Medium';
            } elseif ($rating > 70) {
                $rating_category = 'High';
            }

            // Получаем категории поста
            $categories = get_the_terms(get_the_ID(), 'rating_category');
            $category_names = array();
            if ($categories) {
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                }
            }

            $category_names = !empty($category_names) ? implode(', ', $category_names) : '—';

            // Вывод поста в виде строки таблицы
            echo '<tr>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . $rating_category . '</td>'; // Выводим категории
            echo '<td>' . $rating . '</td>'; // Выводим рейтинг
            echo '<td><button class="change-rating-btn" data-post-id="' . get_the_ID() . '">Change</button></td>'; // Кнопка для изменения рейтинга
            echo '</tr>';
        endwhile;

        // Закрытие таблицы
        echo '</tbody>';
        echo '</table>';

        // Пагинация
        // Pagination
        echo '<div class="pagination">';
        echo generate_pagination_links($query->max_num_pages, $paged, $category_id);
        echo '</div>';

        echo '</div>';

        // Сброс запроса
        wp_reset_postdata();
    else :
        // Если нет постов
        echo '<tr><td colspan="2">No posts found</td></tr>';
        echo '</tbody>';
        echo '</table>';
    endif;
}






/* 
generate_pagination_links
Function to generate pagination links
 */
function generate_pagination_links($total_pages, $current_page, $category_id) {
    $output = '';
    if ($total_pages > 1) {
        $output .= '<span class="page-numbers">Page ' . $current_page . ' of ' . $total_pages . '</span>';
        $output .= '<a href="?page=posts-rating&paged=1';
        if ($category_id > 0) {
            $output .= '&category=' . $category_id;
        }
        $output .= '">&laquo; First</a>';

        if ($current_page > 1) {
            $output .= '<a href="?page=posts-rating&paged=' . ($current_page - 1);
            if ($category_id > 0) {
                $output .= '&category=' . $category_id;
            }
            $output .= '">&lsaquo; Previous</a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $output .= '<span class="page-numbers current">' . $i . '</span>';
            } else {
                $output .= '<a href="?page=posts-rating&paged=' . $i;
                if ($category_id > 0) {
                    $output .= '&category=' . $category_id;
                }
                $output .= '" class="page-numbers">' . $i . '</a>';
            }
        }

        if ($current_page < $total_pages) {
            $output .= '<a href="?page=posts-rating&paged=' . ($current_page + 1);
            if ($category_id > 0) {
                $output .= '&category=' . $category_id;
            }
            $output .= '">Next &rsaquo;</a>';
        }
        $output .= '<a href="?page=posts-rating&paged=' . $total_pages;
        if ($category_id > 0) {
            $output .= '&category=' . $category_id;
        }
        $output .= '">Last &raquo;</a>';
    }
    return $output;
}







/**
 * add_rating_field
 */
function add_rating_field() {
    add_meta_box(
        'rating_field',
        'Rating',
        'display_rating_field',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_rating_field');






/* 
* display_rating_field
*/
function display_rating_field($post) {
    $rating = get_post_meta($post->ID, 'rating', true);
    ?>
    <label for="rating">Rating:</label>
    <input type="text" id="rating" name="rating" value="<?php echo $rating; ?>">
<?php
}






/* 
save_rating_field
 */
function save_rating_field($post_id) {
    if (array_key_exists('rating', $_POST)) {
        update_post_meta(
            $post_id,
            'rating',
            sanitize_text_field($_POST['rating'])
        );
    }
}
add_action('save_post', 'save_rating_field');





/* 
update_rating_category_term_init
 */
function update_rating_category_term_init($post_id, $rating = '') {
    $rating = intval($rating);

    // Определяем категорию рейтинга
    $rating_category = '';
    if ($rating >= 0 && $rating <= 30) {
        $rating_category = 'low-rating';
    } elseif ($rating > 30 && $rating <= 70) {
        $rating_category = 'medium-rating';
    } elseif ($rating > 70) {
        $rating_category = 'high-rating';
    }

    // Получаем ID термина категории рейтинга
    $term = get_term_by('slug', $rating_category, 'rating_category');
    $term_id = $term->term_id;

    // Присваиваем посту категорию рейтинга
    wp_set_post_terms($post_id, $term_id, 'rating_category');
}




/* 
update_rating_category_term
 */
function update_rating_category_term($post_id) {
    // Проверяем, был ли изменен рейтинг поста
    if (isset($_POST['rating']) && $_POST['rating'] != '') {
        update_rating_category_term_init($post_id, 'rating');
    }
}
add_action('save_post', 'update_rating_category_term');




/* 
change_post_rating_callback
 */
function change_post_rating_callback() {
    // Проверяем AJAX запрос и наличие данных
    if (isset($_POST['post_id']) && isset($_POST['new_rating'])) {
        $post_id = intval($_POST['post_id']);
        $new_rating = intval($_POST['new_rating']);

        // Обновляем метаполе рейтинга поста
        update_post_meta($post_id, 'rating', $new_rating);

        // Вызываем функцию для обновления категории рейтинга
        update_rating_category_term_init($post_id, $new_rating);

        echo 'success';
    } else {
        // Возвращаем сообщение об ошибке, если данные не переданы
        echo 'error';
    }

    // Обязательно завершаем выполнение скрипта
    wp_die();
}
add_action('wp_ajax_change_post_rating', 'change_post_rating_callback');






/* 
custom_posts_best_rated_shortcode
 */
function custom_posts_best_rated_shortcode() {
    ob_start();
?>
    <!-- custom_posts_best_rated_shortcode -->
    <div class="custom-posts-container">
        <div class="custom-posts-grid">
            <!-- Loop through posts -->
            <?php
            // Query parameters
            $args = array(
                'post_type'      => 'post',
                'posts_per_page' => 5,
                'meta_key'       => 'rating',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC'
            );

            // Get posts
            $query = new WP_Query($args);

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
            ?>
                    <div class="custom-post">
                        <a href="<?php the_permalink(); ?>">
                            <div class="custom-post-img">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('thumbnail');
                                }
                                ?>
                            </div>
                        </a>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo custom_get_excerpt(55); ?></p> <!-- Adjust the number to change excerpt length -->
                    </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No posts found</p>';
            endif;
            ?>
            <!-- End loop -->
        </div>
    </div>
<?php

return ob_get_clean();
}
add_shortcode('best_rated_posts', 'custom_posts_best_rated_shortcode');




/* 
 Function to get custom excerpt length
 */
function custom_get_excerpt($length) {
    $excerpt = get_the_excerpt();
    $excerpt_length = strlen($excerpt);
    if ($excerpt_length > $length) {
        $excerpt = substr($excerpt, 0, $length) . '...';
    }
    return $excerpt;
}
