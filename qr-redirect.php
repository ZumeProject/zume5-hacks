<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * The QR Redirect System supports the QR Codes that are used in the Zume Training System.
 * Note: The purpose of using the redirect is to so that when QRs are printed, or used in distributed materials, they can be updated without having to reprint the QR code.
 *
 * @since 1.0.0
 */
class Zume_QR_Redirect
{
    public $page_title = 'Zume Redirect';
    public $root = 'app';
    public $type = 'qr';
    public $root_url = 'https://zume5.training/';
    public $mirror_url = 'https://storage.googleapis.com/zume-file-mirror/';
    public $url_token = 'app/qr';
    public $type_name = 'Zume Redirect';
    public $post_type = 'contacts';
    public $site_url = '';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->root_url = trailingslashit( site_url() );
        $url = dt_get_url_path();

        // fail except for exact url match
        if ( substr( $url, 0, strlen( $this->url_token ) ) !== $this->root . '/' . $this->type ) {
            return;
        }

        $this->redirect();
    }

    public function redirect() {

        $link =  "https://zume5.training$_SERVER[REQUEST_URI]";
        dt_write_log( 'Request: ' . $link );

        header("Location: ".$link, true, 302);

        exit();
    }

}
Zume_QR_Redirect::instance();
