<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Zume_Tools_Post_Type
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

    public function __construct( $post_type = 'zume_tools', $singular = 'Tool', $plural = 'Tools', $args = array(), $taxonomies = array() ) {
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
                    'name' => 'Zume Tools',
                    'singular_name' => 'Zume Tool',
                    'all_items' => 'All Zume Tools',
                    'add_new' => 'Add New',
                    'add_new_item' => 'Add New Zume Tool',
                    'edit' => 'Edit',
                    'edit_item' => 'Edit Zume Tool',
                    'new_item' => 'New Zume Tool',
                    'view_item' => 'View Zume Tool',
                    'search_items' => 'Search Zume Tools',
                    'not_found' => 'Nothing found in the Database.',
                    'not_found_in_trash' => 'Nothing found in Trash',
                    'parent_item_colon' => ''
                ),
                'description' => 'Zume tools catalog for language videos',
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
                'has_archive' => 'zume_tools',
                'capability_type' => 'post',
                'hierarchical' => false,
                'supports' => array( 'title', 'custom-fields' )
            )
        );
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
            2  => 'Zume Tool updated.',
            3  => 'Zume Tool deleted.',
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

    public function get_custom_fields_settings() {
        $fields = array();

        $url = '#';

        // Project Update Information Section
        $fields['1'] = array(
            'name'        => '(1) God Uses Ordinary People',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['2'] = array(
            'name'        => '(2) Teach Them to Obey',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['3'] = array(
            'name'        => '(3) Spiritual Breathing',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['4'] = array(
            'name'        => '(4) S.O.A.P.S.',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['5'] = array(
            'name'        => '(5) Accountability Groups',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['6'] = array(
            'name'        => '(6) Producer not Consumer',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['7'] = array(
            'name'        => '(7) Prayer Cycle',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['8'] = array(
            'name'        => '(8) List of 100',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['9'] = array(
            'name'        => '(9) Spiritual Economy',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['10'] = array(
            'name'        => '(10) The Gospel',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['11'] = array(
            'name'        => '(11) Baptism',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['12'] = array(
            'name'        => '(12) 3 Minute Testimony',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['13'] = array(
            'name'        => '(13) Greatest Blessing',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['14'] = array(
            'name'        => '(14) Duckling Discipleship',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['15'] = array(
            'name'        => '(15) Seeing Where Gods Kingdom Isnt',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['16'] = array(
            'name'        => "(16) Lord's Supper",
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['17'] = array(
            'name'        => '(17) Prayer Walking',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['18'] = array(
            'name'        => '(18) Person of Peace',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['19'] = array(
            'name'        => '(19) Bless Prayer',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['20'] = array(
            'name'        => '(20) Faithfulness',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['21'] = array(
            'name'        => '(21) 3|3 Groups',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['22'] = array(
            'name'        => '(22) Training Cycle',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['23'] = array(
            'name'        => '(23) Leadership Cells',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['24'] = array(
            'name'        => '(24) Non-Sequential Growth',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['25'] = array(
            'name'        => '(25) Pace',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['26'] = array(
            'name'        => '(26) Part of Two Churches',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['27'] = array(
            'name'        => '(27) 3-Month Plan',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['28'] = array(
            'name'        => '(28) Coaching Checklist',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['29'] = array(
            'name'        => '(29) Leadership Networks',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['30'] = array(
            'name'        => '(30) Peer Mentoring',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['31'] = array(
            'name'        => '(31) Four Fields',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );
        $fields['32'] = array(
            'name'        => '(32) Generation Mapping',
            'description' => '',
            'type'        => 'link',
            'default'     => $url,
            'section'     => 'tools',
        );



        return $fields;
    }
    public function meta_box_setup() {
        add_meta_box( $this->post_type . '_links', 'Links', array( $this, 'load_links_meta_box' ), $this->post_type, 'normal', 'high' );
    }
    public function load_links_meta_box() {
        $this->meta_box_content( 'tools' ); // prints
    }

    public function meta_box_content( $section = 'tools' ) {
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

                        case 'text':
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th>
                                <td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
                            echo '<p class="description">' . esc_html( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'link':
                            $redirect = 'https://zume.training/zume_app/qr/?l='.esc_attr( get_the_title( $post_id ) ).'&t='.$k;
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_html( $v['name'] ) . '</label></th>
                                <td>'.trailingslashit( site_url() ).'<input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" /><br><br>';
                            if ( ! empty( $redirect )) {
                                dt_write_log( $redirect );
                                echo '<a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data='.urlencode( $redirect ).'"><img src="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data='.urlencode( $redirect ).'" style="width:250px;"/></a><br><br>';
                            }
                            echo 'Publishable QR link: <a href="'. $redirect .'" target="_blank">'. $redirect .'</a><br>';
                            echo 'Redirects to target: <a href="' . $data . '">'.trailingslashit( site_url() ).$data .'</a><br>';
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
    public function register_custom_columns( $column_name ) {
        switch ( $column_name ) {

            default:
                break;
        }
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
}
Zume_Tools_Post_Type::instance();
