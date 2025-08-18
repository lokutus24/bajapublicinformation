<?php

/**
 * @package Active
 */

namespace Inc\Base;

class BajaPublicInformationActivate
{

        public static function BajaPublicInformationactivate(){

                // Ensure custom post types and taxonomies are registered for rewrite rules
                $cpt = new BpiCustomPostType();
                $cpt->register();

                flush_rewrite_rules();

                $default = array();

                if ( ! get_option( 'bpi' ) ) {
                        update_option( 'bpi', $default );
                }
        }

}