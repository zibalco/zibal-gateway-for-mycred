<?php

add_action( 'plugins_loaded', 'mycred_zibal_plugins_loaded' );

function mycred_zibal_plugins_loaded() {
    add_filter( 'mycred_setup_gateways', 'Add_Zibal_to_Gateways' );
    function Add_Zibal_to_Gateways( $installed ) {
        $installed['zibal'] = [
            'title'    => get_option( 'zibal_display_name' ) ? get_option( 'zibal_display_name' ) : __( 'Zibal payment gateway', 'zibal-mycred' ),
            'callback' => [ 'myCred_Zibal' ],
        ];
        return $installed;
    }

    add_filter( 'mycred_buycred_refs', 'Add_Zibal_to_Buycred_Refs' );
    function Add_Zibal_to_Buycred_Refs( $addons ) {
        $addons['buy_creds_with_zibal'] = __( 'Zibal Gateway', 'zibal-mycred' );

        return $addons;
    }

    add_filter( 'mycred_buycred_log_refs', 'Add_Zibal_to_Buycred_Log_Refs' );
    function Add_Zibal_to_Buycred_Log_Refs( $refs ) {
        $zibal = [ 'buy_creds_with_zibal' ];

        return $refs = array_merge( $refs, $zibal );
    }

    add_filter( 'wp_body_open', 'zibal_success_message_handler' );
    function zibal_success_message_handler( $template ){
        if( !empty( $_GET['mycred_zibal_nok'] ) )
            echo '<div class="mycred_zibal_message error">'. $_GET['mycred_zibal_nok'] .'</div>';

        if( !empty( $_GET['mycred_zibal_ok'] ) )
            echo '<div class="mycred_zibal_message success">'. $_GET['mycred_zibal_ok'] .'</div>';

        if( !empty( $_GET['mycred_zibal_nok'] ) || !empty( $_GET['mycred_zibal_ok'] ))
            echo '<style>
                .mycred_zibal_message {
                    position: absolute;
                    z-index: 9;
                    top: 40px;
                    right: 15px;
                    color: #fff;
                    padding: 15px;
                }
                .mycred_zibal_message.error {
                    background: #F44336;
                }
                .mycred_zibal_message.success {
                    background: #4CAF50;
                }
            </style>';
    }
}

spl_autoload_register( 'mycred_zibal_plugin' );

function mycred_zibal_plugin() {
    if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) {
        return;
    }

    if ( ! class_exists( 'myCred_Zibal' ) ) {
        class myCred_Zibal extends myCRED_Payment_Gateway {

            function __construct( $gateway_prefs ) {
                $types            = mycred_get_types();
                $default_exchange = [];

                foreach ( $types as $type => $label ) {
                    $default_exchange[ $type ] = 1000;
                }

                parent::__construct( [
                    'id'                => 'zibal',
                    'label'             => get_option( 'zibal_display_name' ) ? get_option( 'zibal_display_name' ) : __( 'Zibal payment gateway', 'zibal-mycred' ),
                    'documentation'     => 'https://zibal.ir',
                    'gateway_logo_url'  => plugins_url( '/assets/zibal.png', __FILE__ ),
                    'defaults'          => [
                        'merchant'            => NULL,
                        'zibal_display_name' => __( 'Zibal payment gateway', 'zibal-mycred' ),
                        'currency'           => 'rial',
                        'exchange'           => $default_exchange,
                        'item_name'          => __( 'Purchase of myCRED %plural%', 'mycred' ),
                    ],
                ], $gateway_prefs );
            }

            public function Zibal_Iranian_currencies( $currencies ) {
                unset( $currencies );

                $currencies['rial']  = __( 'Rial', 'zibal-mycred' );
                $currencies['toman'] = __( 'Toman', 'zibal-mycred' );

                return $currencies;
            }

            function preferences() {
                add_filter( 'mycred_dropdown_currencies', [
                    $this,
                    'Zibal_Iranian_currencies',
                ] );

                $prefs = $this->prefs;
                ?>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'merchant' ); ?>"><?php _e( 'کد درگاه - مرچنت', 'zibal-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'merchant' ); ?>"
                                   name="<?php echo $this->field_name( 'merchant' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['merchant']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <!-- <label class="subheader"
                       for="<?php //echo $this->field_id( 'sandbox' ); ?>"><?php //_e( 'Sandbox', 'zibal-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php //echo $this->field_id( 'sandbox' ); ?>"
                                   name="<?php //echo $this->field_name( 'sandbox' ); ?>"
                                   <?php //echo $prefs['sandbox'] == "on"? 'checked="checked"' : '' ?>
                                   type="checkbox"/>
                        </div>
                    </li>
                </ol> -->

                <label class="subheader"
                       for="<?php echo $this->field_id( 'zibal_display_name' ); ?>"><?php _e( 'Title', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'zibal_display_name' ); ?>"
                                   name="<?php echo $this->field_name( 'zibal_display_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['zibal_display_name'] ? $prefs['zibal_display_name'] : __( 'Zibal payment gateway', 'zibal-mycred' ); ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'currency' ); ?>"><?php _e( 'Currency', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->currencies_dropdown( 'currency', 'mycred-gateway-zibal-currency' ); ?>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'item_name' ); ?>"
                                   name="<?php echo $this->field_name( 'item_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['item_name']; ?>"
                                   class="long"/>
                        </div>
                        <span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
                    </li>
                </ol>

                <label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->exchange_rate_setup(); ?>
                    </li>
                </ol>
                <?php
            }

            public function sanitise_preferences( $data ) {
                $new_data['merchant']            = sanitize_text_field( $data['merchant'] );
                $new_data['zibal_display_name'] = sanitize_text_field( $data['zibal_display_name'] );
                $new_data['currency']           = sanitize_text_field( $data['currency'] );
                $new_data['item_name']          = sanitize_text_field( $data['item_name'] );
                // $new_data['sandbox']            = sanitize_text_field( $data['sandbox'] );

                if ( isset( $data['exchange'] ) ) {
                    foreach ( (array) $data['exchange'] as $type => $rate ) {
                        if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), ['.', ',',] ) ) {
                            $data['exchange'][ $type ] = (float) '0' . $rate;
                        }
                    }
                }

                $new_data['exchange'] = $data['exchange'];
                update_option( 'zibal_display_name', $new_data['zibal_display_name'] );
                return $data;
            }

            public function process() {

                // $pending_post_id = sanitize_text_field( $_REQUEST['payment_id'] );
                $pending_post_id = sanitize_text_field( $_REQUEST['orderId'] );
                $org_pending_payment = $pending_payment = $this->get_pending_payment( $pending_post_id );

                // var_dump($org_pending_payment);
                // die();


                $mycred = mycred( $org_pending_payment->point_type );

                $status    = !empty($_POST['status'])  ? sanitize_text_field($_POST['status'])   : (!empty($_GET['status'])  ? sanitize_text_field($_GET['status'])   : NULL);
                $trackId  = !empty($_POST['trackId'])? sanitize_text_field($_POST['trackId']) : (!empty($_GET['trackId'])? sanitize_text_field($_GET['trackId']) : NULL);
                $success    = !empty($_POST['success'])  ? sanitize_text_field($_POST['success'])   : (!empty($_GET['success'])  ? sanitize_text_field($_GET['success'])   : NULL);
                // $id        = !empty($_POST['id'])      ? sanitize_text_field($_POST['id'])       : (!empty($_GET['id'])      ? sanitize_text_field($_GET['id'])       : NULL);
                $orderId  = !empty($_POST['orderId'])? sanitize_text_field($_POST['orderId']) : (!empty($_GET['orderId'])? sanitize_text_field($_GET['orderId']) : NULL);
                $params    = !empty($_POST['id']) ? $_POST : $_GET;
                
                $amount = $_GET['amount'];
                
                if ( $status == 2 ) {
                    $merchant = $merchant = $this->prefs['merchant'];

                    $data = [
                        "merchant" => $merchant,
                        "trackId" => $trackId,
                    ];

                    $response = $this->post_to_zibal('verify', json_encode($data));
                    
                    if ( is_wp_error( $response ) ) {

                        $log = $response->get_error_message();
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_zibal',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id,
                            $params
                        );

                        $return = add_query_arg( 'mycred_zibal_nok', $log, $this->get_cancelled() );
                        wp_redirect( $return );
                        exit;
                    }

                    $result = (object)$response;

                    if ( $result->result != 100 ) {

                        $log = sprintf( __( 'An error occurred while verifying the transaction. status: %s, code: %s, message: %s', 'zibal-mycred' ), $_GET['status'], $result->result, $result->message );
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_zibal',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id,
                            $params
                        );

                        $return = add_query_arg( 'mycred_zibal_nok', $log, $this->get_cancelled() );
                        wp_redirect( $return );
                        exit;
                    }

                    if ( $result->result == 100 && $result->amount == $amount ) {
                        
                        $message = sprintf( __( 'Payment succeeded. Status: %s, Track id: %s, Order no: %s', 'zibal-mycred' ), $result->status, $trackId, $result->orderId );
                        $log = $message;

                        add_filter( 'mycred_run_this', function( $filter_args ) use ( $log ) {
                            return $this->mycred_zibal_success_log( $filter_args, $log );
                        } );

                        // if ( $this->complete_payment( $org_pending_payment, $id ) ) {
                        if ( $this->complete_payment( $org_pending_payment, $trackId ) ) {

                            $this->log_call( $pending_post_id, $message );
                            $this->trash_pending_payment( $pending_post_id );

                            $return = add_query_arg( 'mycred_zibal_ok', $message, $this->get_thankyou() );
                            wp_redirect( $return );
                            exit;
                        } else {

                            $log = sprintf( __( 'An unexpected error occurred when completing the payment but it is done at the gateway. Track id is: %s', 'zibal-mycred', $result->track_id ) );
                            $this->log_call( $pending_post_id, $log );
                            $mycred->add_to_log(
                                'buy_creds_with_zibal',
                                $pending_payment->buyer_id,
                                $pending_payment->amount,
                                $log,
                                $pending_payment->buyer_id,
                                $result
                            );

                            $return = add_query_arg( 'mycred_zibal_nok', $log, $this->get_cancelled() );
                            wp_redirect( $return );
                            exit;
                        }
                    }

                    $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s', 'zibal-mycred' ), $result->result, $trackId );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_zibal',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $result
                    );

                    $return = add_query_arg( 'mycred_zibal_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    exit;

                } else {
                    $error = $this->getStatus($status);

                    $log = sprintf( __( '%s (Code: %s), Track id: %s', 'zibal-mycred' ), $error, $status, $trackId );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_zibal',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $params
                    );

                    $return = add_query_arg( 'mycred_zibal_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    exit;
                }
            }

            public function returning() {}

            public function mycred_zibal_success_log( $request, $log ){
                if( $request['ref'] == 'buy_creds_with_zibal' )
                    $request['entry'] = $log;

                return $request;
            }
            /**
             * Prep Sale
             *
             * @since   1.8
             * @version 1.0
             */
            public function prep_sale( $new_transaction = FALSE ) {

                // Point type
                $type   = $this->get_point_type();
                $mycred = mycred( $type );

                // Amount of points
                $amount = $mycred->number( $_REQUEST['amount'] );

                // Get cost of that points
                $cost = $this->get_cost( $amount, $type );
                $cost = abs( $cost );

                $to   = $this->get_to();
                $from = $this->current_user_id;

                // Revisiting pending payment
                if ( isset( $_REQUEST['revisit'] ) ) {
                    $this->transaction_id = strtoupper( $_REQUEST['revisit'] );
                } else {
                    $post_id = $this->add_pending_payment( [
                        $to,
                        $from,
                        $amount,
                        $cost,
                        $this->prefs['currency'],
                        $type,
                    ] );
                    $this->transaction_id = get_the_title( $post_id );
                }

                $is_ajax    = ( isset( $_REQUEST['ajax'] ) && $_REQUEST['ajax'] == 1 ) ? true : false;
                $callback = add_query_arg( 'payment_id', $this->transaction_id, $this->callback_url() );
                $callback = add_query_arg( 'amount', (( $this->prefs['currency'] == 'toman' ) ? ( $cost * 10 ) : $cost), $this->callback_url());
                $merchant  = $this->prefs['merchant'];

                $data = array(
                    "merchant" => $merchant,
                    "orderId" => $this->transaction_id,
                    "amount"   => ( $this->prefs['currency'] == 'toman' ) ? ( $cost * 10 ) : $cost,
                    "callbackUrl" => $callback,
                );

                $response = $this->post_to_zibal( 'request', json_encode($data) );

                if ( is_wp_error( $response ) ) {
                    $error = $response->get_error_message();
                    $mycred->add_to_log(
                        'buy_creds_with_zibal',
                        $from,
                        $amount,
                        $error,
                        $from,
                        $data,
                        'point_type_key'
                    );

                    if($is_ajax){
                        $this->errors[] = $error;
                    }
                    else if( empty( $_GET['zibal_error'] ) ){
                        wp_redirect( $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&zibal_error='. $error );
                        exit;
                    }
                }

                $result = (object)$response;

                if ( empty( $result ) || empty( $result->result ) || empty( $result->trackId ) ) {

                    if ( ! empty( $result->result ) && ! empty( $result->message ) ) {
                        $error = $result->message;

                        $mycred->add_to_log(
                            'buy_creds_with_zibal',
                            $from,
                            $amount,
                            $error,
                            $from,
                            $data,
                            'point_type_key'
                        );

                        if($is_ajax){
                            $this->errors[] = $error;
                        }
                        else if( empty( $_GET['zibal_error'] ) ){
                            wp_redirect( $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&zibal_error='. $error );
                            exit;
                        }
                    }
                }

                $item_name = str_replace( '%number%', $this->amount, $this->prefs['item_name'] );
                $item_name = $this->core->template_tags_general( $item_name );

                $redirect_fields = [
                    //'pay_to_email'        => $this->prefs['account'],
                    'transaction_id'      => $this->transaction_id,
                    'return_url'          => $this->get_thankyou(),
                    'cancel_url'          => $this->get_cancelled( $this->transaction_id ),
                    'status_url'          => $this->callback_url(),
                    'return_url_text'     => get_bloginfo( 'name' ),
                    'hide_login'          => 1,
                    'merchant_fields'     => 'sales_data',
                    'sales_data'          => $this->post_id,
                    'amount'              => $this->cost,
                    'currency'            => $this->prefs['currency'],
                    'detail1_description' => __( 'Item Name', 'mycred' ),
                    'detail1_text'        => $item_name,
                ];

                // Customize Checkout Page
                if ( isset( $this->prefs['account_title'] ) && ! empty( $this->prefs['account_title'] ) ) {
                    $redirect_fields['recipient_description'] = $this->core->template_tags_general( $this->prefs['account_title'] );
                }

                if ( isset( $this->prefs['account_logo'] ) && ! empty( $this->prefs['account_logo'] ) ) {
                    $redirect_fields['logo_url'] = $this->prefs['account_logo'];
                }

                if ( isset( $this->prefs['confirmation_note'] ) && ! empty( $this->prefs['confirmation_note'] ) ) {
                    $redirect_fields['confirmation_note'] = $this->core->template_tags_general( $this->prefs['confirmation_note'] );
                }

                // If we want an email receipt for purchases
                if ( isset( $this->prefs['email_receipt'] ) && ! empty( $this->prefs['email_receipt'] ) ) {
                    $redirect_fields['status_url2'] = $this->prefs['account'];
                }

                // Gifting
                if ( $this->gifting ) {
                    $user                                   = get_userdata( $this->recipient_id );
                    $redirect_fields['detail2_description'] = __( 'Recipient', 'mycred' );
                    $redirect_fields['detail2_text']        = $user->display_name;
                }

                $this->redirect_fields = $redirect_fields;
                // $this->redirect_to = empty( $_GET['zibal_error'] )? $result->link : $_SERVER['REQUEST_URI'];
                // $this->redirect_to = ('https://gateway.zibal.ir/start/' + $result->trackId);
                wp_redirect('https://gateway.zibal.ir/start/' . $result->trackId);
                exit;
            }

            /**
             * AJAX Buy Handler
             *
             * @since   1.8
             * @version 1.0
             */
            public function ajax_buy() {
                // Construct the checkout box content
                $content = $this->checkout_header();
                $content .= $this->checkout_logo();
                $content .= $this->checkout_order();
                $content .= $this->checkout_cancel();
                $content .= $this->checkout_footer();

                // Return a JSON response
                $this->send_json( $content );
            }

            /**
             * Checkout Page Body
             * This gateway only uses the checkout body.
             *
             * @since   1.8
             * @version 1.0
             */
            public function checkout_page_body() {
                echo $this->checkout_header();
                echo $this->checkout_logo( FALSE );
                echo $this->checkout_order();
                echo $this->checkout_cancel();
                if( !empty( $_GET['zibal_error'] ) ){
                    echo '<div class="alert alert-error zibal-error">'. $_GET['zibal_error'] .'</div>';
                    echo '<style>
                        .checkout-footer, .zibal-logo, .checkout-body > img {display: none;}
                        .zibal-error {
                            background: #F44336;
                            color: #fff;
                            padding: 15px;
                            margin: 10px 0;
                        }
                    </style>';
                }
                else {
                    echo '<style>.checkout-body > img {display: none;}</style>';
                }
                echo $this->checkout_footer();
                echo sprintf(
                    '<span class="zibal-logo" style="font-size: 12px;padding: 5px 0;"><img src="%1$s" style="display: inline-block;vertical-align: middle;width: 70px;">%2$s</span>',
                    plugins_url( '/assets/zibal.png', __FILE__ ), __( 'Pay with Zibal', 'zibal-mycred' )
                );

            }

            /**
             * @param $action (PaymentRequest, )
             * @param $params string
             *
             * @return mixed
             */
            function post_to_zibal($action, $params)
            {
                try {

                    $number_of_connection_tries = 3;
                    $response = null;
                    while ( $number_of_connection_tries>0 ) {
                        $response = wp_safe_remote_post('https://gateway.zibal.ir/v1/' . $action,array(
                            'body'=> $params,
                            'headers'=>array(
                                'Content-Type'=>'application/json'
                            )
                        ));

                        if ( is_wp_error( $response ) ) {
                            $number_of_connection_tries --;
                            continue;
                        } else {
                            break;
                        }
                    }

                    $body = wp_remote_retrieve_body($response);
                    return json_decode($body, true);
                } catch (Exception $ex) {
                    return false;
                }
            }


            /**
             * Calls the gateway endpoints.
             *
             * Tries to get response from the gateway for 4 times.
             *
             * @param $url
             * @param $args
             *
             * @return array|\WP_Error
             */
            private function call_gateway_endpoint( $url, $args ) {
                $number_of_connection_tries = 4;
                while ( $number_of_connection_tries ) {
                    $response = wp_safe_remote_post( $url, $args );
                    if ( is_wp_error( $response ) ) {
                        $number_of_connection_tries --;
                        continue;
                    } else {
                        break;
                    }
                }
                return $response;
            }

            /**
             * return description for status.
             *
             * @param $status_code
             *
             * @return String
             */
            public function getStatus($status_code){
                switch ($status_code){
                    case -1:
                        return 'در انتظار پردخت';
                        break;
                    case 1:
                        return 'پرداخت شده - تاییدشده';
                        break;
                    case 2:
                        return 'پرداخت شده - تاییدنشده';
                        break;
                    case 3:
                        return 'لغوشده توسط کاربر';
                        break;
                    case 4:
                        return '‌شماره کارت نامعتبر می‌باشد.';
                        break;
                    case 5:
                        return 'موجودی حساب کافی نمی‌باشد.';
                        break;
                    case 6:
                        return 'رمز واردشده اشتباه می‌باشد.';
                        break;
                    case 7:
                        return '‌تعداد درخواست‌ها بیش از حد مجاز می‌باشد.';
                        break;
                    case 8:
                        return '‌تعداد پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد.';
                        break;
                    case 9:
                        return 'مبلغ پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد.';
                        break;
                    case 10:
                        return '‌صادرکننده‌ی کارت نامعتبر می‌باشد.';
                        break;
                    case 11:
                        return '‌خطای سوییچ';
                        break;
                    case 12:
                        return 'کارت قابل دسترسی نمی‌باشد.';
                        break;
                }
            }

            /**
             * return description for result code.
             *
             * @param $result_code
             *
             * @return String
             */
            public function getResult($result_code){
                switch ($result_code){
                    case 100:
                        return 'با موفقیت تایید شد.';
                        break;
                    case 102:
                        return 'merchant یافت نشد.';
                        break;
                    case 103:
                        return 'merchant غیرفعال';
                        break;
                    case 104:
                        return 'merchant نامعتبر';
                        break;
                    case 105:
                        return 'amount بایستی بزرگتر از 1,000 ریال باشد.';
                        break;
                    case 106:
                        return 'callbackUrl نامعتبر می‌باشد. (شروع با http و یا https)';
                        break;
                    case 113:
                        return 'amount مبلغ تراکنش از سقف میزان تراکنش بیشتر است.';
                        break;
                    case 201:
                        return 'قبلا تایید شده.';
                        break;
                    case 202:
                        return 'سفارش پرداخت نشده یا ناموفق بوده است.';
                        break;
                    case 203:
                        return 'trackId نامعتبر می‌باشد.';
                        break;
                }
            }
        }
    }
}
