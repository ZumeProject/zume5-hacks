<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Zume_PDF_Download_Post_Type
{

    public $post_type;
    public $singular;
    public $plural;
    public $args;
    public $taxonomies;
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct( $post_type = 'zume_download', $singular = 'Download', $plural = 'Downloads', $args = array(), $taxonomies = array() ) {
        $this->post_type = $post_type;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->args = $args;
        $this->taxonomies = $taxonomies;

        add_action( 'init', array( $this, 'register_post_type' ) );

        if ( is_admin() ) {
            global $pagenow;

            add_action( 'admin_menu', array( $this, 'meta_box_setup' ), 20 );
            add_action( 'save_post', array( $this, 'meta_box_save' ) );
            add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
            add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

            if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) ) {
                $pt = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
                if ( $pt === $this->post_type ) {
                    add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'register_custom_column_headings' ), 10, 1 );
                    add_action( 'manage_posts_custom_column', array( $this, 'register_custom_columns' ), 10, 2 );
                }
            }
        }
    }

    public function register_post_type() {
        register_post_type( $this->post_type,
            array(
                'labels' => array(
                    'name' => 'Zume Download',
                    'singular_name' => 'Zume Download',
                    'all_items' => 'All Zume Downloads',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Zume Download',
                    'edit' => 'Edit',
                    'edit_item' => 'Edit Zume Download',
                    'new_item' => 'New Zume Download',
                    'view_item' => 'View Zume Download',
                    'search_items' => 'Search Zume Downloads',
                    'not_found' => 'Nothing found in the Database.',
                    'not_found_in_trash' => 'Nothing found in Trash',
                    'parent_item_colon' => ''
                ), /* end of arrays */
                'description' => 'Zume video catalog for language videos',
                'public' => false,
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_ui' => true,
                'query_var' => true,
                'menu_position' => 8,
                'menu_icon' => 'dashicons-book',
                'rewrite' => array(
            'slug' => 'zume_video',
            'with_front' => false
            ),
                'has_archive' => 'zume_video',
                'capability_type' => 'post',
                'hierarchical' => false,

                'supports' => array( 'title' )
            )
        );
    }



    public function register_custom_column_headings( $defaults ) {

        $new_columns = array();

        $last_item = array();

        if ( count( $defaults ) > 2 ) {
            $last_item = array_slice( $defaults, -1 );

            array_pop( $defaults );
        }
        $defaults = array_merge( $defaults, $new_columns );

        if ( is_array( $last_item ) && 0 < count( $last_item ) ) {
            foreach ( $last_item as $k => $v ) {
                $defaults[ $k ] = $v;
                break;
            }
        }

        return $defaults;
    }
    public function updated_messages( $messages ) {
        global $post;

        $messages[ $this->post_type ] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf(
                '%3$s updated. %1$sView %4$s%2$s',
                '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">',
                '</a>',
                $this->singular,
                strtolower( $this->singular )
            ),
            2  => 'Zume Download updated.',
            3  => 'Zume Download deleted.',
            4  => sprintf( '%s updated.', $this->singular ),
            /* translators: %s: date and time of the revision */
            5  => isset( $_GET['revision'] ) ? sprintf( '%1$s restored to revision from %2$s', $this->singular, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( '%1$s published. %3$sView %2$s%4$s', $this->singular, strtolower( $this->singular ), '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">', '</a>' ),
            7  => sprintf( '%s saved.', $this->singular ),
            8  => sprintf( '%1$s submitted. %2$sPreview %3$s%4$s', $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '">', '</a>' ),
            9  => sprintf(
                '%1$s scheduled for: %2$s. %3$sPreview %4$s',
                strtolower( $this->singular ),
                // translators: Publish box date format, see http://php.net/date
                '<strong>' . date_i18n( 'M j, Y @ G:i',
                strtotime( $post->post_date ) ) . '</strong>',
                '<a target="_blank" href="' . esc_url( get_permalink( $post->ID ) ) . '">',
                '</a>'
            ),
            10 => sprintf( '%1$s draft updated. %2$sPreview %3$s%4$s', $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '">', '</a>' ),
        );

        return $messages;
    }

    public function meta_box_setup() {
        add_meta_box( $this->post_type . '_scribes', 'Downloads', array( $this, 'load_downloads_meta_box' ), $this->post_type, 'normal', 'high' );
        add_meta_box( $this->post_type . '_links', 'Links', array( $this, 'load_links_meta_box' ), $this->post_type, 'normal', 'high' );
    }

    public function load_downloads_meta_box() {
        echo 'Add only the filename with the .pdf extension. The file is added to the Theme in `zume-training/files`.<br><br>The page title above needs to be the two character language code.<br><hr>';
        $this->meta_box_content( 'downloads' ); // prints
    }
    public function load_pages_downloads_meta_box() {
        $this->meta_box_content( 'pages' ); // prints
    }

    public function load_links_meta_box() {
        $this->meta_box_content( 'links' ); // prints
    }

    public function meta_box_content( $section = 'scribe' ) {
        global $post_id;
        $fields = get_post_custom( $post_id );
        $field_data = $this->get_custom_fields_settings();

        echo '<input type="hidden" name="' . esc_attr( $this->post_type ) . '_noonce" id="' . esc_attr( $this->post_type ) . '_noonce" value="' . esc_attr( wp_create_nonce( 'video_noonce_action' ) ) . '" />';

        if ( 0 < count( $field_data ) ) {
            echo '<table class="form-table">' . "\n";
            echo '<tbody>' . "\n";

            foreach ( $field_data as $k => $v ) {

                if ( $v['section'] == $section ) {

                    $data = $v['default'];
                    if ( isset( $fields[ $k ] ) && isset( $fields[ $k ][0] ) ) {
                        $data = $fields[ $k ][0];
                    }

                    $type = $v['type'];

                    switch ( $type ) {

                        case 'url':
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th><td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
                            echo '<p class="description">' . esc_html( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'text':
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th>
                                <td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
                            echo '<p class="description">' . esc_html( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'link':
                            $link = zume_mirror_url() . esc_attr( get_the_title( $post_id ) ) . '/' . esc_attr( $data );
                            $redirect = 'https://zume.training/zume_app/qr?l='.esc_attr( get_the_title( $post_id ) ).'&d='.$k;
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th>
                                <td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" /><br><br>';
                            echo 'Redirect: <a href="'. $redirect .'" target="_blank">'. $redirect .'</a><br>';
                            if ( ! empty( $redirect )) {
                                echo '<a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data='.$redirect.'"><img src="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data='.$redirect.'" style="width:250px;"/></a><br><br>';
                            }
                            echo 'Destination: <a href="'. $link .'" target="_blank">'. $link .'</a><br>';
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'rawlink':
                            $link = esc_url( $data );
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th>
                                <td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" /><br>';
                            echo '<a href="'. $link .'" target="_blank">'. $link .'</a><br>';
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'select':
                            echo '<tr valign="top"><th scope="row">
                                <label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th>
                                <td>
                                <select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="regular-text">';
                            // Iterate the options
                            foreach ( $v['default'] as $vv ) {
                                echo '<option value="' . esc_attr( $vv ) . '" ';
                                if ( $vv == $data ) {
                                    echo 'selected';
                                }
                                echo '>' . esc_html( $vv ) . '</option>';
                            }
                            echo '</select>' . "\n";
                            echo '<p class="description">' . esc_html( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;

                        default:
                            break;
                    }
                }
            }
            echo '</tbody>' . "\n";
            echo '</table>' . "\n";
        }
    }

    public function get_custom_fields_settings() {
        $fields = array();

        // Project Update Information Section
        $fields['33'] = array(
            'name'        => '(33) Zúme Guidebook ',
            'description' => '',
            'type'        => 'link',
            'default'     => '33_en_zume_guidebook.pdf',
            'section'     => 'downloads',
        );
        $fields['34'] = array(
            'name'        => '(34) Welcome to Zúme Script',
            'description' => '',
            'type'        => 'link',
            'default'     => '34_en_welcome_to_zume.pdf',
            'section'     => 'downloads',
        );
        $fields['35'] = array(
            'name'        => '(35) Teach them to Obey Script',
            'description' => '',
            'type'        => 'link',
            'default'     => '35_en_teach_them_to_obey.pdf',
            'section'     => 'downloads',
        );
        $fields['36'] = array(
            'name'        => '(36) Spiritual Breathing Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '36_en_spiritual_breathing.pdf',
            'section'     => 'downloads',
        );
        $fields['37'] = array(
            'name'        => '(37) S.O.A.P.S. Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '37_en_soaps.pdf',
            'section'     => 'downloads',
        );
        $fields['38'] = array(
            'name'        => '(38) Accountability Groups Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '38_en_accountability_groups.pdf',
            'section'     => 'downloads',
        );
        $fields['39'] = array(
            'name'        => '(39) Producers vs Consumers Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '39_en_producers_vs_consumers.pdf',
            'section'     => 'downloads',
        );
        $fields['40'] = array(
            'name'        => '(40) Prayer Cycle Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '40_en_prayer_cycle.pdf',
            'section'     => 'downloads',
        );
        $fields['41'] = array(
            'name'        => '(41) List of 100 Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '41_en_list_of_100.pdf',
            'section'     => 'downloads',
        );
        $fields['42'] = array(
            'name'        => '(42) Spiritual Economy Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '42_en_spiritual_economy.pdf',
            'section'     => 'downloads',
        );
        $fields['43'] = array(
            'name'        => '(43) The Gospel Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '43_en_the_gospel.pdf',
            'section'     => 'downloads',
        );
        $fields['44'] = array(
            'name'        => '(44) Baptism Script',
            'description' => '',
            'type'        => 'link',
            'default'     => '44_en_baptism.pdf',
            'section'     => 'downloads',
        );
        $fields['45'] = array(
            'name'        => '(45) 3 Minute Testimony Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '45_en_3_minute_testimony.pdf',
            'section'     => 'downloads',
        );
        $fields['46'] = array(
            'name'        => '(46) Greatest Blessing Script',
            'description' => '',
            'type'        => 'link',
            'default'     => '46_en_greatest_blessing.pdf',
            'section'     => 'downloads',
        );
        $fields['47'] = array(
            'name'        => '(47) Duckling Discipleship Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '47_en_duckling_discipleship.pdf',
            'section'     => 'downloads',
        );
        $fields['48'] = array(
            'name'        => '(48) Eyes to See Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '48_en_eyes_to_see.pdf',
            'section'     => 'downloads',
        );
        $fields['49'] = array(
            'name'        => '(49) Lord\'s Supper Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '49_en_lords_supper.pdf',
            'section'     => 'downloads',
        );
        $fields['50'] = array(
            'name'        => '(50) Prayer Walking Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '50_en_prayer_walking.pdf',
            'section'     => 'downloads',
        );
        $fields['51'] = array(
            'name'        => '(51) Person of Peace Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '51_en_person_of_peace.pdf',
            'section'     => 'downloads',
        );
        $fields['52'] = array(
            'name'        => '(52) Faithfulness Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '52_en_faithfulness.pdf',
            'section'     => 'downloads',
        );
        $fields['53'] = array(
            'name'        => '(53) 3/3 Groups Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '53_en_3_3_groups.pdf',
            'section'     => 'downloads',
        );
        $fields['54'] = array(
            'name'        => '(54) Training Cycle Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '54_en_training_cycle.pdf',
            'section'     => 'downloads',
        );
        $fields['55'] = array(
            'name'        => '(55) Leadership Cells Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '55_en_leadership_cells.pdf',
            'section'     => 'downloads',
        );
        $fields['56'] = array(
            'name'        => '(56) Non-Sequential Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '56_en_non_sequential.pdf',
            'section'     => 'downloads',
        );
        $fields['57'] = array(
            'name'        => '(57) Pace Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '57_en_pace.pdf',
            'section'     => 'downloads',
        );
        $fields['58'] = array(
            'name'        => '(58) Part of Two Churches Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '58_en_part_of_two_churches.pdf',
            'section'     => 'downloads',
        );
        $fields['59'] = array(
            'name'        => '(59) Completion of Training Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '59_en_completion_of_training.pdf',
            'section'     => 'downloads',
        );
        $fields['60'] = array(
            'name'        => '(60) Coaching Checklist Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '60_en_coaching_checklist.pdf',
            'section'     => 'downloads',
        );
        $fields['61'] = array(
            'name'        => '(61) Leadership in Networks Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '61_en_leadership_in_networks.pdf',
            'section'     => 'downloads',
        );
        $fields['62'] = array(
            'name'        => '(62) Peer Mentoring Script ',
            'description' => '',
            'type'        => 'link',
            'default'     => '62_en_peer_mentoring.pdf',
            'section'     => 'downloads',
        );

        /* Full links to files */
        $fields['video_overview'] = array(
            'name'        => 'Video Overview Download',
            'description' => 'This is the complete download link to download the video from Vimeo',
            'type'        => 'rawlink',
            'default'     => 'https://player.vimeo.com/external/248149800.hd.mp4?s=3a6907fc2a263b3a7fa8050864dab149e498226b&profile_id=174&download=1',
            'section'     => 'links',
        );


        return apply_filters( 'zume_pdf_download_fields_settings', $fields );
    }
    public function meta_box_save( $post_id ) {

        // Verify
        if ( get_post_type() != $this->post_type ) {
            return $post_id;
        }

        $key = $this->post_type . '_noonce';
        if ( isset( $_POST[ $key ] ) && !wp_verify_nonce( sanitize_key( $_POST[ $key ] ), 'video_noonce_action' ) ) {
            return $post_id;
        }

        if ( isset( $_POST['post_type'] ) && 'page' == sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        if ( isset( $_GET['action'] ) ) {
            if ( $_GET['action'] == 'trash' || $_GET['action'] == 'untrash' || $_GET['action'] == 'delete' ) {
                return $post_id;
            }
        }

        $field_data = $this->get_custom_fields_settings();
        $fields = array_keys( $field_data );

        foreach ( $fields as $f ) {
            if ( !isset( $_POST[ $f ] ) ) {
                continue;
            }

            ${$f} = strip_tags( trim( sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) ) );

            // Escape the URLs.
            if ( 'url' == $field_data[ $f ]['type'] ) {
                ${$f} = esc_url( ${$f} );
            }

            if ( get_post_meta( $post_id, $f ) == '' ) {
                add_post_meta( $post_id, $f, ${$f}, true );
            } elseif ( ${$f} != get_post_meta( $post_id, $f, true ) ) {
                update_post_meta( $post_id, $f, ${$f} );
            } elseif ( ${$f} == '' ) {
                delete_post_meta( $post_id, $f, get_post_meta( $post_id, $f, true ) );
            }
        }
        return $post_id;
    }
    public function enter_title_here( $title ) {
        if ( get_post_type() == $this->post_type ) {
            $title = 'Enter the title here';
        }

        return $title;
    }
    public function activation() {
        $this->flush_rewrite_rules();
    }
    private function flush_rewrite_rules() {
        $this->register_post_type();
        flush_rewrite_rules();
    }
    public function register_custom_columns( $column_name ) {

        switch ( $column_name ) {

            default:
                break;
        }
    }

}
Zume_PDF_Download_Post_Type::instance();
