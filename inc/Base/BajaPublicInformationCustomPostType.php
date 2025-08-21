<?php

namespace Inc\Base;

use Inc\Base\BajaPublicInformationBaseController;

/**
 * Registers the Lakossági Információk custom post type, taxonomy and meta fields.
 */
class BajaPublicInformationCustomPostType extends BajaPublicInformationBaseController
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
        add_action('wp_ajax_bpi_live_search', [$this, 'ajaxSearch']);
        add_action('wp_ajax_nopriv_bpi_live_search', [$this, 'ajaxSearch']);
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
     * Render search card with live AJAX results.
     */
    public function searchShortcode()
    {
        $top_terms = get_terms([
            'taxonomy'   => 'bpi_category',
            'parent'     => 0,
            'hide_empty' => false,
        ]);

        ob_start();
        ?>
        <div class="bpi-search-card">
            <div class="bpi-input-wrapper">
                <input type="text" id="bpi-live-search" placeholder="<?php _e('Keresés a lakossági információk között', 'bpi'); ?>" />
                <span class="bpi-search-icon"><img class="search-icon" src="<?php echo esc_url($this->pluginUrl . 'assets/img/search-svgrepo-com.svg'); ?>" alt="Search" /></span>
            </div>
            <div class="bpi-category-dropdown">
                <span id="bpi-category-toggle" class="bpi-category-toggle">
                    <span id="bpi-selected-main"><?php _e('Kategória szűrés', 'bpi'); ?></span>
                    <span class="bpi-arrow">&#9660;</span>
                    <span id="bpi-selected-sub" class="bpi-badge" style="display:none;"></span>
                </span>
                <ul class="bpi-category-list">
                    <li class="bpi-item-default">Összes kategória</li>
                    <?php foreach ($top_terms as $term) :
                        $subs = get_terms([
                            'taxonomy'   => 'bpi_category',
                            'parent'     => $term->term_id,
                            'hide_empty' => false,
                        ]);
                    ?>
                        <li class="bpi-cat-item" data-id="<?php echo esc_attr($term->term_id); ?>" data-name="<?php echo esc_attr($term->name); ?>">
                            <?php echo esc_html($term->name); ?>
                            <?php if (!empty($subs)) : ?>
                                <ul class="bpi-sub-list">
                                    <li class="bpi-item-default">Összes alkategória</li>
                                    <?php foreach ($subs as $sub) : ?>
                                        <li class="bpi-sub-item" data-id="<?php echo esc_attr($sub->term_id); ?>" data-name="<?php echo esc_attr($sub->name); ?>"><?php echo esc_html($sub->name); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div id="bpi-category-cards">
            <?php foreach ($top_terms as $term) :
                $subs = get_terms([
                    'taxonomy'   => 'bpi_category',
                    'parent'     => $term->term_id,
                    'hide_empty' => false,
                ]);
                if (!empty($subs)) : ?>
                <div class="bpi-category-block">
                    <h2 class="bpi-main-category"><?php echo esc_html($term->name); ?></h2>
                    <div class="bpi-subcategories-grid">
                        <?php foreach ($subs as $sub) : ?>
                            <div class="bpi-subcard" data-parent="<?php echo esc_attr($term->term_id); ?>" data-id="<?php echo esc_attr($sub->term_id); ?>">
                                <h4><?php echo esc_html($sub->name); ?></h4>
                                <img
                                    class="bpi-subcard-arrow"
                                    src="<?php echo esc_url($this->pluginUrl . 'assets/img/arrow-circle-up-right.svg'); ?>"
                                    data-src-default="<?php echo esc_url($this->pluginUrl . 'assets/img/arrow-circle-up-right.svg'); ?>"
                                    data-src-hover="<?php echo esc_url($this->pluginUrl . 'assets/img/arrow-circle-right.svg'); ?>"
                                    alt=""
                                />
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; endforeach; ?>
        </div>
        <div id="bpi-live-results"></div>
        <div id="bpi-modal" class="bpi-modal"><div class="bpi-modal-content"><span class="bpi-close"><img class="search-icon" src="<?php echo esc_url($this->pluginUrl . 'assets/img/circle-close.svg'); ?>" alt="Search" /></span><div class="bpi-modal-body"></div></div></div>
        <div id="bpi-image-modal" class="bpi-modal"><div class="bpi-modal-content"><span class="bpi-close"><img class="search-icon" src="<?php echo esc_url($this->pluginUrl . 'assets/img/circle-close.svg'); ?>" alt="Close" /></span><div class="bpi-image-wrapper"></div></div></div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler for live search.
     */
    public function ajaxSearch()
    {
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $cat     = isset($_POST['cat']) ? intval($_POST['cat']) : 0;
        $sub     = isset($_POST['sub']) ? intval($_POST['sub']) : 0;

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

        $args = [
            'post_type'      => 'bpi_institution',
            's'              => $keyword,
            'tax_query'      => $tax_query,
            'posts_per_page' => -1,
        ];
        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            $sub_name = $sub ? get_term($sub, 'bpi_category') : '';
            $cat_name = $cat ? get_term($cat, 'bpi_category') : '';
            echo '<div class="bpi-results-info">';
            if ($cat_name && !is_wp_error($cat_name)) {
                echo '<div class="bpi-main-category">' . esc_html($cat_name->name) . '</div>';
            }
            if ($sub_name && !is_wp_error($sub_name)) {
                echo '<div class="bpi-subcategory">' . esc_html($sub_name->name) . '</div>';
            }
            echo '<div class="bpi-results-count">' . sprintf(__('%d találat', 'bpi'), $query->found_posts) . '</div>';
            if ($cat_name && $cat_name->name == 'Egészségügy') {
                echo '<p class="bpi-question">' . __('Melyik a körzeti háziorvosom?', 'bpi') . '</p>';
                echo '<div class="bpi-street-input-wrapper">'; // wrapper
                echo '  <input type="text" id="bpi-street-search" placeholder="' . esc_attr__('Írja be az utca nevét..', 'bpi') . '" />';
                echo '  <img class="bpi-street-search-icon" src="' . esc_url( $this->pluginUrl . 'assets/img/street-search-icon.svg' ) . '" alt="Search" />';
                echo '</div>';
            }

            echo '</div>'; // .bpi-results-info
            echo '<button id="bpi-sort-alpha" class="bpi-sort-button">' . __('Rendezés ABC szerint', 'bpi') . '</button>';
            echo '<div class="bpi-results-grid">';
            while ($query->have_posts()) {
                $query->the_post();
                $address = get_post_meta(get_the_ID(), 'bpi_address', true);
                $phone   = get_post_meta(get_the_ID(), 'bpi_phone', true);
                $website = get_post_meta(get_the_ID(), 'bpi_website', true);
                $email   = get_post_meta(get_the_ID(), 'bpi_email', true);
                $extra   = get_post_meta(get_the_ID(), 'bpi_extra', true);
                $streets = get_post_meta(get_the_ID(), 'bpi_streets', true);
                $terms   = get_the_terms(get_the_ID(), 'bpi_category');
                $category = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : '';
                $masked_phone = $this->maskPhone($phone);
                echo '<div class="bpi-result-card" data-streets="' . esc_attr($streets) . '">';
                if (has_post_thumbnail()) {
                    $thumb_id  = get_post_thumbnail_id();
                    $thumb_url = wp_get_attachment_image_url($thumb_id, 'medium');
                    $full_url  = wp_get_attachment_image_url($thumb_id, 'full');
                    echo '<div class="bpi-card-image">';
                    echo '<img src="' . esc_url($thumb_url) . '" alt="">';
                    echo '<span id="card-featured-image" class="bpi-open-image" data-full="' . esc_url($full_url) . '"><img src="' . esc_url($this->pluginUrl . 'assets/img/scale.svg') . '" alt="' . esc_attr__('Kép nagyítása', 'bpi') . '"></span>';
                    echo '</div>';
                }
                echo '<div class="bpi-card-header">';

                if ($category) {
                    echo '<div class="bpi-card-category">' . esc_html($category) . '</div>';
                }
                echo '<div class="bpi-open-modal"><img src="' . esc_url($this->pluginUrl . 'assets/img/zoom-in.svg') . '" alt="' . esc_attr__('Részletek', 'bpi') . '"></div>';
                echo '</div>';
                echo '<h3>' . get_the_title() . '</h3>';
                if ($streets) {
                    echo '<div class="bpi-field"><img src="' . esc_url($this->pluginUrl . 'assets/img/map-pin.svg') . '" alt=""><span>' . __('Körzet utcái: ', 'bpi') . esc_html($streets) . '</span></div>';
                }
                if ($phone) {
                    echo '<div class="bpi-field"><img src="' . esc_url($this->pluginUrl . 'assets/img/phone.svg') . '" alt="">';
                    echo '<span class="bpi-phone-number" data-full="' . esc_attr($phone) . '" data-mask="' . esc_attr($masked_phone) . '">' . esc_html($masked_phone) . '</span>';
                    echo '<span class="bpi-phone-toggle"><img src="' . esc_url($this->pluginUrl . 'assets/img/eye.svg') . '" alt="' . esc_attr__('Telefonszám megjelenítése', 'bpi') . '"></span>';
                    echo '</div>';
                }
                echo '<div class="bpi-card-details" style="display:none;">';
                if ($category) {
                    echo '<div class="bpi-card-category">' . esc_html($category) . '</div>';
                }
                echo '<h3>' . get_the_title() . '</h3>';
                if (has_post_thumbnail()) {
                    $thumb_id  = get_post_thumbnail_id();
                    $thumb_url = wp_get_attachment_image_url($thumb_id, 'medium');
                    $full_url  = wp_get_attachment_image_url($thumb_id, 'full');
                    echo '<div class="bpi-card-image">';
                    echo '<img src="' . esc_url($thumb_url) . '" alt="">';
                    echo '<span class="bpi-open-image" data-full="' . esc_url($full_url) . '"><img src="' . esc_url($this->pluginUrl . 'assets/img/zoom-in.svg') . '" alt="' . esc_attr__('Kép nagyítása', 'bpi') . '"></span>';
                    echo '</div>';
                }

                if ($phone) {
                    echo '<div class="bpi-field"><img src="' . esc_url($this->pluginUrl . 'assets/img/phone.svg') . '" alt="">';
                    echo '<span class="bpi-phone-number" data-full="' . esc_attr($phone) . '" data-mask="' . esc_attr($masked_phone) . '">' . esc_html($masked_phone) . '</span>';
                    echo '<span class="bpi-phone-toggle"><img src="' . esc_url($this->pluginUrl . 'assets/img/eye.svg') . '" alt="' . esc_attr__('Telefonszám megjelenítése', 'bpi') . '"></span>';
                    echo '</div>';
                }
                if ($website) {
                    echo '<p><a href="' . esc_url($website) . '" target="_blank">' . esc_html($website) . '</a></p>';
                }
                if ($email) {
                    echo '<p><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>';
                }

                if (!empty($extra) && is_array($extra)) {
                    echo '<ul>';
                    foreach ($extra as $row) {
                        if (empty($row['label']) && empty($row['value'])) {
                            continue;
                        }
                        echo '<li>' . esc_html($row['label']) . ': ' . esc_html($row['value']) . '</li>';
                    }
                    echo '</ul>';
                }

                if ($address) {
                    echo '<div class="bpi-field"><img src="' . esc_url($this->pluginUrl . 'assets/img/map-pin.svg') . '" alt=""><span>' . __('Cím', 'bpi') . ': ' . esc_html($address) . '</span></div>';
                    echo '<iframe width="100%" height="200" src="https://www.google.com/maps?q=' . urlencode($address) . '&output=embed"></iframe>';
                }
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . __('Nincs találat.', 'bpi') . '</p>';
        }
        wp_die();
    }

    private function maskPhone($phone)
    {
        $masked = '';
        $digits = 0;
        $length = strlen($phone);
        for ($i = 0; $i < $length; $i++) {
            $char = $phone[$i];
            if (ctype_digit($char)) {
                $digits++;
                $masked .= ($digits > 4) ? 'x' : $char;
            } else {
                $masked .= $char;
            }
        }
        return $masked;
    }
}
