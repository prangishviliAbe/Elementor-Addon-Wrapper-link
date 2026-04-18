<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class
 */
class Elementor_Wrapper_Link_Plugin {

    public function __construct() {
        add_action( 'elementor/init', [ $this, 'init_controls' ] );

        add_action( 'elementor/frontend/section/before_render', [ $this, 'before_element_render' ] );
        add_action( 'elementor/frontend/column/before_render', [ $this, 'before_element_render' ] );
        add_action( 'elementor/frontend/container/before_render', [ $this, 'before_element_render' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function init_controls() {
        add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'add_link_control' ], 10, 2 );
        add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'add_link_control' ], 10, 2 );
        add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'add_link_control' ], 10, 2 );
    }

    public function add_link_control( $element, $args ) {
        $element->start_controls_section(
            'wrapper_link_section',
            [
                'label' => __( 'Wrapper Link', 'elementor-wrapper-link' ),
                'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $element->add_control(
            'wrapper_link_url',
            [
                'label'       => __( 'Link URL', 'elementor-wrapper-link' ),
                'type'        => \Elementor\Controls_Manager::URL,
                'dynamic'     => [ 'active' => true ],
                'placeholder' => __( 'https://example.com ან აირჩიე დინამიური ველი', 'elementor-wrapper-link' ),
                'show_external' => true,
            ]
        );

        $element->add_control(
            'wrapper_link_is_external',
            [
                'label'        => __( 'Open in new tab', 'elementor-wrapper-link' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default'      => '',
            ]
        );

        $element->add_control(
            'wrapper_link_nofollow',
            [
                'label'        => __( 'Add nofollow', 'elementor-wrapper-link' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default'      => '',
            ]
        );

        $element->end_controls_section();
    }

    public function before_element_render( $element ) {
        $settings = $element->get_settings_for_display();

        $raw = isset( $settings['wrapper_link_url'] ) ? $settings['wrapper_link_url'] : '';

        $url = '';
        $is_external = 'false';
        $nofollow = 'false';

        // Handle URL control formats:
        // - If control is a URL array: ['url'=>..., 'is_external'=>..., 'nofollow'=>...]
        // - If dynamic tag, $raw may be array with 'id' pointing to dynamic tag; dynamic tag get_value may return string or array with ['url']
        if ( is_array( $raw ) ) {
            if ( ! empty( $raw['url'] ) ) {
                $url = $raw['url'];
                if ( isset( $raw['is_external'] ) ) {
                    $is_external = ! empty( $raw['is_external'] ) ? 'true' : 'false';
                }
                if ( isset( $raw['nofollow'] ) ) {
                    $nofollow = ! empty( $raw['nofollow'] ) ? 'true' : 'false';
                }
            } elseif ( isset( $raw['id'] ) ) {
                // dynamic tag
                $tag_data = \Elementor\Plugin::instance()->dynamic_tags->get_tag_data( $raw );
                if ( $tag_data && isset( $tag_data['tag'] ) && method_exists( $tag_data['tag'], 'get_value' ) ) {
                    $value = $tag_data['tag']->get_value( $tag_data['settings'] );

                    // Debugging: when WP_DEBUG is enabled, log the dynamic-tag returned value for troubleshooting
                    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                        // keep logs small but helpful
                        if ( is_object( $value ) ) {
                            error_log( '[ewl] dynamic tag returned object: ' . get_class( $value ) );
                        } elseif ( is_array( $value ) ) {
                            error_log( '[ewl] dynamic tag returned array keys: ' . implode( ',', array_keys( $value ) ) );
                        } else {
                            error_log( '[ewl] dynamic tag returned: ' . wp_trim_words( wp_json_encode( $value ), 20, '...' ) );
                        }
                    }

                    // Common return shapes:
                    // - string (url)
                    // - array with ['url']
                    // - number (post ID) or string numeric
                    // - WP_Post object
                    if ( is_array( $value ) ) {
                        if ( isset( $value['url'] ) ) {
                            $url = $value['url'];
                        } elseif ( isset( $value['value'] ) ) {
                            $url = $value['value'];
                        } elseif ( isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
                            $url = get_permalink( (int) $value['id'] );
                        }
                    } elseif ( is_object( $value ) ) {
                        // WP_Post or other object that might represent a post
                        if ( isset( $value->ID ) ) {
                            $url = get_permalink( (int) $value->ID );
                        } elseif ( method_exists( $value, '__toString' ) ) {
                            $url = (string) $value;
                        }
                    } elseif ( is_numeric( $value ) ) {
                        $url = get_permalink( (int) $value );
                    } elseif ( is_string( $value ) ) {
                        $url = $value;
                    }
                }
            }
        } elseif ( is_string( $raw ) ) {
            $url = $raw;
        }

        // If the separate controls are set, they should take precedence for external/nofollow
        if ( ! empty( $settings['wrapper_link_is_external'] ) ) {
            $is_external = 'true';
        }
        if ( ! empty( $settings['wrapper_link_nofollow'] ) ) {
            $nofollow = 'true';
        }

        if ( empty( $url ) ) {
            return;
        }

        $url = esc_url( $url );

        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-url', $url );
        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-external', $is_external );
        $element->add_render_attribute( '_wrapper', 'data-wrapper-link-nofollow', $nofollow );
        $element->add_render_attribute( '_wrapper', 'class', 'wrapper-link-enabled' );
    }

    public function enqueue_assets() {
        // Don't load in admin, during REST requests, or during AJAX
        if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'WP_DOING_AJAX' ) && WP_DOING_AJAX ) ) {
            return;
        }

        // If Elementor editor or preview is active, avoid loading to prevent breaking the editor UI
        if ( class_exists( '\\Elementor\\Plugin' ) ) {
            try {
                $elementor = \Elementor\Plugin::instance();
                if ( isset( $elementor->editor ) && is_object( $elementor->editor ) && method_exists( $elementor->editor, 'is_edit_mode' ) && $elementor->editor->is_edit_mode() ) {
                    return;
                }
                if ( isset( $elementor->preview ) && is_object( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview' ) && $elementor->preview->is_preview() ) {
                    return;
                }
            } catch ( \Throwable $e ) {
                // In case Elementor internals throw, avoid blocking loading — but continue to enqueue for front-end
            }
        }

        wp_register_script( 'ewl-wrapper-link', EWL_PLUGIN_URL . 'assets/js/wrapper-link.js', [], '1.5', true );
        wp_enqueue_script( 'ewl-wrapper-link' );
    }
}
