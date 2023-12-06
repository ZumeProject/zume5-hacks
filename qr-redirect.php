<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_QR_Redirect
{
    public $page_title = 'Zume Redirect';
    public $root = 'zume_app';
    public $type = 'qr';
    public $url_token = 'zume_app/qr';
    public $type_name = 'Zume Redirect';
    public $post_type = 'contacts';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        $url = dt_get_url_path();

        if ( substr( $url, 0, strlen( $this->url_token ) ) !== $this->root . '/' . $this->type ) {
            return;
        }

        $this->redirect();
    }

    public function redirect() {
        global $wpdb;

        $link = trailingslashit( site_url() );

        if ( isset( $_GET['p'] ) ) {
            $post_id = $_GET['p'];

            $language_slug = $wpdb->get_var( $wpdb->prepare( "
                SELECT t.slug
                FROM wp_term_relationships tr
                JOIN wp_term_taxonomy tt_l ON tt_l.term_taxonomy_id=tr.term_taxonomy_id AND tt_l.taxonomy = 'language'
                JOIN wp_terms t ON tt_l.term_id = t.term_id
                WHERE tr.object_id = %d
            ", $post_id ) );
            $post_name = $wpdb->get_var( $wpdb->prepare( "
                SELECT p.post_name
                FROM wp_posts p
                WHERE p.ID = %d
            ", $post_id ) );

            $link = $link . '/' . $language_slug . '/' . $post_name;

            header("Location: ".$link, true, 302);

            exit();

        } else {
            echo 'URL param not regognized.';
            exit();
        }
    }
}
Zume_QR_Redirect::instance();
