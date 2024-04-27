<?php
/*
Plugin Name: Zappli CRM Elementor Integration
Plugin URI: http://www.zappli.com.au/
Description: Integrates Elementor forms with Zappli CRM.
Version: 1.0.0
Author: Andrej Kudriavcev
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: zappli-crm-elementor-integration
Domain Path: /languages
*/

// Helper function to a CMP parameter form the URL and store it in a session variable
function zappli_cmp() {
    if (!session_id()) session_start();
    $cmp = trim($_GET['cmp'] ?? "");
    if( $cmp) {
        $_SESSION['cmp'] = $cmp;
    }
}

// Helper function to get the visitor's IP address
function zappli_get_visitor_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip); // just to be safe
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return '0.0.0.0'; // Fallback IP
}

function zappli_api_request($crm_url, $api_token, $request_url, $data = array(), $post_type = 'GET') {
    $api_url = $crm_url . '/api/v1/' . $request_url;
    // Set default headers
    $headers = array(
        'Authorization' => 'Bearer ' . $api_token
    );
    // Default args
    $args = array(
        'headers' => $headers
    );

    // If the request is GET, add data to the query string
    if ($post_type === 'GET') {
        $api_url = add_query_arg($data, $api_url);
    }
    // If the request is POST or other type, add data to the body and set the content type to JSON
    else {
        $args['body'] = $data;
        $headers['Content-Type'] = 'application/json';
    }

    // Perform the request
    if ($post_type === 'GET') {
        $response = wp_remote_get($api_url, $args);
    } else if ($post_type === 'POST') {
        $response = wp_remote_post($api_url, $args);
    } else {
        $args['method'] = $post_type;
        $response = wp_remote_request($api_url, $args);
    }

    // Check if the request was successful
    if (is_wp_error($response)) {
        // Handle error
        return false;
    }

    // Decode the JSON response
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    // Return the response body
    return $response_body;
}

function format_crm_url( $crm_url ) {
    // Remove any leading/trailing whitespaces
    $crm_url = strtolower( trim( $crm_url ) );

    // Parse the URL to extract the domain
    $parsed_url = parse_url( $crm_url );
    $domain = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

    // Reconstruct the URL with 'https://' and the extracted domain
    $crm_url = 'https://' . $domain;

    // Add a trailing slash
    $crm_url = rtrim( $crm_url, '/' ) . '/';

    return $crm_url;
}

add_action('elementor_pro/init', 'zappli_crm_register_action');

function zappli_crm_register_action() {
    class Zappli_CRM_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {

        public function get_name() {
            return 'zappli_crm_action';
        }

        public function get_label() {
            return __( 'Zappli CRM Submit', 'zappli-crm-elementor-integration' );
        }

        public function register_settings_section( $widget ) {
            $widget->start_controls_section(
                'zappli_crm_section',
                [
                    'label' => __( 'Zappli CRM', 'zappli-crm-elementor-integration' ),
                    'condition' => [
                        'submit_actions' => $this->get_name(),
                    ],
                ]
            );

            $widget->add_control(
                'zappli_crm_url',
                [
                    'label' => __( 'CRM URL', 'zappli-crm-elementor-integration' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => __( 'Enter your CRM URL here', 'zappli-crm-elementor-integration' ),
                ]
            );

            $widget->add_control(
                'zappli_crm_api_token',
                [
                    'label' => __( 'API Token', 'zappli-crm-elementor-integration' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => __( 'Enter your API token here', 'zappli-crm-elementor-integration' ),
                ]
            );

            $widget->add_control(
                'zappli_crm_redirect_option',
                [
                    'label' => __( 'Redirect Option', 'zappli-crm-elementor-integration' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'none' => [
                            'title' => __( 'None', 'zappli-crm-elementor-integration' ),
                            'icon' => 'fa fa-times',
                        ],
                        'url' => [
                            'title' => __( 'URL', 'zappli-crm-elementor-integration' ),
                            'icon' => 'fa fa-link',
                        ],
                        'crm' => [
                            'title' => __( 'CRM', 'zappli-crm-elementor-integration' ),
                            'icon' => 'fa fa-database',
                        ],
                    ],
                    'default' => 'none',
                    'toggle' => true,
                ]
            );

            $widget->add_control(
                'zappli_crm_redirect_url',
                [
                    'label' => __( 'Redirect URL', 'zappli-crm-elementor-integration' ),
                    'type' => \Elementor\Controls_Manager::URL,
                    'placeholder' => __( 'Enter the redirect URL here', 'zappli-crm-elementor-integration' ),
                    'condition' => [
                        'zappli_crm_redirect_option' => 'url',
                    ],
                ]
            );

            $widget->add_control(
                'zappli_crm_extra_fields',
                [
                    'label' => __( 'Extra Fields', 'zappli-crm-elementor-integration' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'placeholder' => __( 'Enter the extra fields in the format: var1=val1&var2=val2', 'zappli-crm-elementor-integration' ),
                    'description' => __( 'These fields will be included in the CRM submission along with the captured data.', 'zappli-crm-elementor-integration' ),
                ]
            );

            $widget->end_controls_section();
        }

        public function on_export( $element ) {
            // Implement this method if you need to handle the action when exporting an Elementor template
        }

        public function run( $record, $ajax_handler ) {
            $settings = $record->get('form_settings');
            $crm_url = format_crm_url($settings['zappli_crm_url']);
            $api_token = $settings['zappli_crm_api_token'];
            $raw_fields = $record->get('fields');
            $form_data = [];
            foreach ( $raw_fields as $id => $field ) {
                $form_data[$id] = $field['value'];
            }
            if (!session_id()) session_start();
            $form_data['campaign'] = $_SESSION['cmp'] ?? "";
            $form_data['domain'] = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            $form_data['path'] = parse_url($_SERVER['HTTP_REFERER'],  PHP_URL_PATH);
            $form_data['ip_address'] = zappli_get_visitor_ip();
            $extra_fields = $settings['zappli_crm_extra_fields'];
            if ( ! empty( $extra_fields ) ) {
                $extra_fields_pairs = explode( '&', $extra_fields );
                foreach ( $extra_fields_pairs as $field_pair ) {
                    list( $field_name, $field_value ) = explode( '=', $field_pair );
                    $form_data[ $field_name ] = $field_value;
                }
            }
            $response = zappli_api_request($crm_url, $api_token, 'leads/', $form_data, 'POST' );
            if (!isset($response['result'])) {
                $error_message = "Unable to submit the from. Please try agian later!";
                $ajax_handler->add_error_message( $error_message );
            } else if (!$response['result']) {
                if (isset( $response['error'])) {
                    $error_message = $response['result'];
                } else {
                    $error_message = "Unable to submit the from. Please try agian later!";
                }
                $ajax_handler->add_error_message( $error_message );
            } else {
                $redirect_option = $settings['zappli_crm_redirect_option'];
                $redirect_url = '';
                if ( 'url' === $redirect_option ) {
                    $redirect_url = $settings['zappli_crm_redirect_url']['url'];
                } elseif ( 'crm' === $redirect_option ) {
                    if (isset($response['id'])) {
                        $redirect_url = $crm_url + "/clientportal/" + $response['id'];
                    }
                }
                if ( ! empty( $redirect_url ) && filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {
                    $ajax_handler->add_response_data( 'redirect_url', $redirect_url );
                }
            }
        }
    }

    $zappli_crm_action = new Zappli_CRM_Action();
    \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $zappli_crm_action->get_name(), $zappli_crm_action );
}

add_action('wp', 'zappli_cmp');