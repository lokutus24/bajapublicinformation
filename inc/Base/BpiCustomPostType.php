<?php

namespace Inc\Base;

use Inc\Base\BajaPublicInformationBaseController;

/**
 * Registers the Lakossági Információk custom post type, taxonomy and meta fields.
 */
class BpiCustomPostType extends BajaPublicInformationBaseController
{
    /**
     * Hook into WordPress.
     */
    public function registerFunction()
    {
        add_action('init', [$this, 'register']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_bpi_institution', [$this, 'saveMeta']);
        add_shortcode('bpi_search', [$this, 'searchShortcode']);
    }

    /**
     * Register custom post type and taxonomy.
     */
    public function register()
    {
        $labels = [
            'name'               => __('Intézmények', 'bpi'),
            'singular_name'      => __('Intézmény', 'bpi'),
            'add_new_item'       => __('Új intézmény', 'bpi'),
            'edit_item'          => __('Intézmény szerkesztése', 'bpi'),
            'menu_name'          => __('Intézmények', 'bpi'),
        ];

        $args = [
            'labels'      => $labels,
            'public'      => true,
            'has_archive' => true,
            'supports'    => ['title', 'editor', 'thumbnail'],
            'rewrite'     => ['slug' => 'intezmenyek'],
            'show_in_rest'=> true,
        ];

        register_post_type('bpi_institution', $args);

        $taxLabels = [
            'name'          => __('Kategóriák', 'bpi'),
            'singular_name' => __('Kategória', 'bpi'),
        ];

        register_taxonomy('bpi_category', 'bpi_institution', [
            'labels'            => $taxLabels,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'intezmeny-kategoria'],
            'show_in_rest'      => true,
        ]);

        $defaults = [
            'Egészségügy',
            'Szociális ellátás',
            'Oktatás',
            'Kultúra',
            'Sport',
            'Közlekedés',
            'Hatóságok',
            'Környezetvédelem',
            'Vallás',
            'Temetők',
            'Természeti értékeink',
        ];

        if (!get_option('bpi_default_terms')) {
            foreach ($defaults as $term) {
                if (!term_exists($term, 'bpi_category')) {
                    wp_insert_term($term, 'bpi_category');
                }
            }
            update_option('bpi_default_terms', 1);
        }
    }

    /**
     * Register meta boxes for institutional data.
     */
    public function addMetaBoxes()
    {
        add_meta_box('bpi_details', __('Intézményi adatok', 'bpi'), [$this, 'renderMetaBox'], 'bpi_institution', 'normal', 'default');
    }

    /**
     * Render meta box HTML.
     */
    public function renderMetaBox($post)
    {
        $address = get_post_meta($post->ID, 'bpi_address', true);
        $phone   = get_post_meta($post->ID, 'bpi_phone', true);
        $website = get_post_meta($post->ID, 'bpi_website', true);
        $email   = get_post_meta($post->ID, 'bpi_email', true);
        $streets = get_post_meta($post->ID, 'bpi_streets', true);
        $extra   = get_post_meta($post->ID, 'bpi_extra', true);
        if (!is_array($extra)) {
            $extra = [];
        }
        wp_nonce_field('bpi_details_nonce', 'bpi_details_nonce');
        ?>
        <p>
            <label for="bpi_address"><?php _e('Cím', 'bpi'); ?></label>
            <input type="text" id="bpi_address" name="bpi_address" class="widefat" value="<?php echo esc_attr($address); ?>">
        </p>
        <p>
            <label for="bpi_phone"><?php _e('Telefonszám', 'bpi'); ?></label>
            <input type="text" id="bpi_phone" name="bpi_phone" class="widefat" value="<?php echo esc_attr($phone); ?>">
        </p>
        <p>
            <label for="bpi_website"><?php _e('Weboldal', 'bpi'); ?></label>
            <input type="url" id="bpi_website" name="bpi_website" class="widefat" value="<?php echo esc_attr($website); ?>">
        </p>
        <p>
            <label for="bpi_email"><?php _e('E-mail', 'bpi'); ?></label>
            <input type="email" id="bpi_email" name="bpi_email" class="widefat" value="<?php echo esc_attr($email); ?>">
        </p>
        <p>
            <label for="bpi_streets"><?php _e('Körzet utcái (soronként egy)', 'bpi'); ?></label>
            <textarea id="bpi_streets" name="bpi_streets" rows="4" class="widefat"><?php echo esc_textarea($streets); ?></textarea>
        </p>
        <p><?php _e('További mezők', 'bpi'); ?></p>
        <table id="bpi-extra-table" class="widefat">
            <tbody>
            <?php foreach ($extra as $index => $row) : ?>
                <tr>
                    <td><input type="text" name="bpi_extra[<?php echo $index; ?>][label]" placeholder="<?php _e('Megnevezés', 'bpi'); ?>" value="<?php echo esc_attr($row['label']); ?>"/></td>
                    <td><input type="text" name="bpi_extra[<?php echo $index; ?>][value]" placeholder="<?php _e('Érték', 'bpi'); ?>" value="<?php echo esc_attr($row['value']); ?>"/></td>
                    <td><button class="button remove-field">&times;</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" class="button" id="bpi-add-field"><?php _e('Mező hozzáadása', 'bpi'); ?></button></p>
        <script>
        jQuery(function($){
            $('#bpi-add-field').on('click', function(e){
                e.preventDefault();
                var i = $('#bpi-extra-table tbody tr').length;
                $('#bpi-extra-table tbody').append('<tr><td><input type="text" name="bpi_extra['+i+'][label]" placeholder="<?php _e('Megnevezés', 'bpi'); ?>"/></td><td><input type="text" name="bpi_extra['+i+'][value]" placeholder="<?php _e('Érték', 'bpi'); ?>"/></td><td><button class="button remove-field">&times;</button></td></tr>');
            });
            $('#bpi-extra-table').on('click', '.remove-field', function(e){
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }

    /**
     * Save meta box data.
     */
    public function saveMeta($post_id)
    {
        if (!isset($_POST['bpi_details_nonce']) || !wp_verify_nonce($_POST['bpi_details_nonce'], 'bpi_details_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, 'bpi_address', isset($_POST['bpi_address']) ? sanitize_text_field($_POST['bpi_address']) : '');
        update_post_meta($post_id, 'bpi_phone', isset($_POST['bpi_phone']) ? sanitize_text_field($_POST['bpi_phone']) : '');
        update_post_meta($post_id, 'bpi_website', isset($_POST['bpi_website']) ? esc_url_raw($_POST['bpi_website']) : '');
        update_post_meta($post_id, 'bpi_email', isset($_POST['bpi_email']) ? sanitize_email($_POST['bpi_email']) : '');
        update_post_meta($post_id, 'bpi_streets', isset($_POST['bpi_streets']) ? sanitize_textarea_field($_POST['bpi_streets']) : '');

        $extra = [];
        if (!empty($_POST['bpi_extra']) && is_array($_POST['bpi_extra'])) {
            foreach ($_POST['bpi_extra'] as $row) {
                if (empty($row['label']) && empty($row['value'])) {
                    continue;
                }
                $extra[] = [
                    'label' => sanitize_text_field($row['label']),
                    'value' => sanitize_text_field($row['value']),
                ];
            }
        }
        update_post_meta($post_id, 'bpi_extra', $extra);
    }

    /**
     * Render search form and results on the front-end.
     */
    public function searchShortcode()
    {
        $keyword = isset($_GET['bpi_keyword']) ? sanitize_text_field($_GET['bpi_keyword']) : '';
        $cat     = isset($_GET['bpi_cat']) ? intval($_GET['bpi_cat']) : 0;
        $sub     = isset($_GET['bpi_sub']) ? intval($_GET['bpi_sub']) : 0;
        $street  = isset($_GET['bpi_street']) ? sanitize_text_field($_GET['bpi_street']) : '';

        $top_terms = get_terms([
            'taxonomy'   => 'bpi_category',
            'parent'     => 0,
            'hide_empty' => false,
        ]);

        ob_start();
        ?>
        <form method="get" class="bpi-search-form">
            <input type="text" name="bpi_keyword" value="<?php echo esc_attr($keyword); ?>" placeholder="<?php _e('Keresés…', 'bpi'); ?>" />
            <select name="bpi_cat" id="bpi_cat">
                <option value=""><?php _e('Fő kategória', 'bpi'); ?></option>
                <?php foreach ($top_terms as $term) : ?>
                    <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($cat, $term->term_id); ?>><?php echo esc_html($term->name); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="bpi_sub" id="bpi_sub">
                <option value=""><?php _e('Alkategória', 'bpi'); ?></option>
                <?php
                if ($cat) {
                    $subs = get_terms([
                        'taxonomy'   => 'bpi_category',
                        'parent'     => $cat,
                        'hide_empty' => false,
                    ]);
                    foreach ($subs as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '" ' . selected($sub, $term->term_id, false) . '>' . esc_html($term->name) . '</option>';
                    }
                }
                ?>
            </select>
            <input type="text" name="bpi_street" value="<?php echo esc_attr($street); ?>" placeholder="<?php _e('Utcanév', 'bpi'); ?>" />
            <button type="submit"><?php _e('Keresés', 'bpi'); ?></button>
        </form>
        <?php
        if ($keyword || $cat || $sub || $street) {
            $tax_query = [];
            if ($sub) {
                $tax_query[] = [
                    'taxonomy' => 'bpi_category',
                    'field'    => 'term_id',
                    'terms'    => $sub,
                ];
            } elseif ($cat) {
                $tax_query[] = [
                    'taxonomy' => 'bpi_category',
                    'field'    => 'term_id',
                    'terms'    => $cat,
                ];
            }

            $meta_query = [];
            if ($street) {
                $meta_query[] = [
                    'key'     => 'bpi_streets',
                    'value'   => $street,
                    'compare' => 'LIKE',
                ];
            }

            $args = [
                'post_type'      => 'bpi_institution',
                's'              => $keyword,
                'tax_query'      => $tax_query,
                'meta_query'     => $meta_query,
                'posts_per_page' => -1,
            ];
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                echo '<ul class="bpi-results">';
                while ($query->have_posts()) {
                    $query->the_post();
                    $address = get_post_meta(get_the_ID(), 'bpi_address', true);
                    $phone   = get_post_meta(get_the_ID(), 'bpi_phone', true);
                    $website = get_post_meta(get_the_ID(), 'bpi_website', true);
                    $email   = get_post_meta(get_the_ID(), 'bpi_email', true);
                    echo '<li class="bpi-result">';
                    echo '<h3>' . get_the_title() . '</h3>';
                    if ($address) {
                        $map = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);
                        echo '<div><a href="' . esc_url($map) . '" target="_blank">' . esc_html($address) . '</a></div>';
                    }
                    if ($phone) {
                        echo '<div><a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></div>';
                    }
                    if ($website) {
                        echo '<div><a href="' . esc_url($website) . '" target="_blank">' . esc_html($website) . '</a></div>';
                    }
                    if ($email) {
                        echo '<div><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></div>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
                wp_reset_postdata();
            } else {
                echo '<p>' . __('Nincs találat.', 'bpi') . '</p>';
            }
        }
        return ob_get_clean();
    }
}
