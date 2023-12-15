<?php 
# @*************************************************************************@
# @ Software author: Mansur Terla (Mansur_TL)                               @
# @ UI/UX Designer & Web developer ;)                                       @
# @                                                                         @
# @*************************************************************************@
# @ Instagram: https://www.instagram.com/mansur_tl                          @
# @ VK: https://vk.com/mansur_tl_uiux                                       @
# @ Envato: http://codecanyon.net/user/mansur_tl                            @
# @ Behance: https://www.behance.net/mansur_tl                              @
# @ Telegram: https://t.me/mansurtl_contact                                 @
# @*************************************************************************@
# @ E-mail: mansurtl.contact@gmail.com                                      @
# @ Website: https://www.mansurtl.com                                       @
# @*************************************************************************@
# @ ColibriSM - The Ultimate Social Network PHP Script                      @
# @ Copyright (c)  ColibriSM. All rights reserved                           @
# @*************************************************************************@

if (empty($cl["is_logged"])) {
    $data['status'] = 400;
    $data['error']  = 'Invalid access token';
}

else if($action == 'topup_wallet') {
    $data['status']   = 400;
    $data['err_code'] = 0;
    $topup_amount     = fetch_or_get($_POST['amount'], false);
    $topup_method     = fetch_or_get($_POST['method'], false);
    $topup_min_amount = intval($cl["config"]["wallet_min_amount"]);

    if (empty($topup_amount) || is_numeric($topup_amount) != true) {
        $data['err_code'] = 'invalid_topup_amount';
    }

    else if ($topup_amount < $topup_min_amount || $topup_amount > 15000) {
        $data['err_code'] = 'invalid_topup_amount';
    }

    else if (empty($topup_method) || in_array($topup_method, array("paypal", "stripe", "razorpay", "paystack", "stripe_alipay", "bank", "coinpayments")) != true) {
        $data['err_code'] = 'invalid_topup_method';
    }

    else {
        if ($topup_method == "paypal" && $cl['config']['paypal_method_status'] == 'on') {

            try {
                require_once("core/libs/paypal/paypal.php");
                
                cl_session('tup_amount', $topup_amount);
            }

            catch (Exception $ex) {
                $data['status']  = 500;
                $data['message'] = $ex->getMessage();
            }
        }

        else if ($topup_method == "coinpayments" && $cl['config']['coinpayments_method_status'] == 'on') {

            try {

                $currency2 = "LTCT";

                if ($cl['config']['coinpayments_api_mode'] == 'live') {
                    $currency2 = "BTC";
                }

                $request_result = cl_coinpayments_api_request(array(
                    "key" => $cl['config']['coinpayments_api_key'],
                    "version" => "1",
                    "format" => "json",
                    "cmd" => "create_transaction",
                    "amount" => $topup_amount,
                    "custom" => $topup_amount,
                    "currency1" => strtoupper($cl['config']['site_currency']),
                    "currency2" => $currency2,
                    "buyer_email" => $me["email"],
                    "item_name" => cl_translate('Top up your account balance'),
                    "item_number" => time(),
                    "ipn_url" => cl_link("native_api/wallet/coinpayments_ipn_handler")
                ));

                if (not_empty($request_result["result"])) {
                    $data['url']    = $request_result["result"]["checkout_url"];
                    $data['status'] = 200;
                }

                else{
                    $data['status'] = 500;
                    $data['message'] = $request_result["error"];
                }
            }

            catch (Exception $ex) {
                $data['status'] = 500;
                $data['message'] = $ex->getMessage();
            }
        }

        else if($topup_method == "paystack" && $cl['config']['paystack_method_status'] == 'on') {
            try {
                require_once(cl_full_path("core/libs/PayStack-PHP/vendor/autoload.php"));

                $paystack       = new \Yabacon\Paystack($cl["config"]["paystack_api_pass"]);
                $reference      = sha1(microtime());
                $tranx          = $paystack->transaction->initialize([
                    'amount'    => ($topup_amount * 100),
                    'email'     => $me["email"],
                    'reference' => $reference,
                    'callback'  => cl_link("native_api/wallet/pgw2_wallet_tup_verification"),
                    'currency'  => strtoupper($cl['config']['site_currency'])
                ]);

                cl_session('paystack_reference', $reference);
                cl_session('tup_amount', $topup_amount);

                $data['url']    = $tranx->data->authorization_url;
                $data['status'] = 200;
            }

            catch(Exception $ex){
                $data['status']  = 500;
                $data['message'] = $ex->getMessage();
            }
        }

        else if($topup_method == "razorpay" && $cl['config']['rzp_method_status'] == 'on') {
            try {
                require_once(cl_full_path("core/libs/RazorPay/vendor/autoload.php"));


                $razorpay_api = new Razorpay\Api\Api($cl['config']['rzp_api_key'], $cl['config']['rzp_api_secret']);

                $rzp_order_id = $razorpay_api->order->create(array(
                    'receipt' => sha1(microtime()),
                    'amount' => ($topup_amount * 100),
                    'currency' => strtoupper($cl['config']['site_currency']),
                    'partial_payment' => false,
                    'notes'=> array(
                        'key1'=> 'value3',
                        'key2'=> 'value2'
                    )
                ));


                cl_session('tup_amount', $topup_amount);
                cl_session('razorpay_order_id', $rzp_order_id->id);

                $data['order_id'] = $rzp_order_id->id;
                $data['status']   = 200;
            }

            catch(Exception $ex){
                $data['status']  = 500;
                $data['message'] = $ex->getMessage();
            }
        }

        else if($topup_method == "bank" && $cl['config']['bank_method_status'] == 'on') {
            try {

                $bank_trans_session_id = cl_strf("SID_%s", cl_genkey(32,32));

                cl_session("bank_trans_session", array(
                    "amount" => $topup_amount,
                    "currency" => strtoupper($cl['config']['site_currency']),
                    "sess_id" => $bank_trans_session_id
                ));

                $data['url']    = cl_link(cl_strf("wallet_bank_transfer/%s", $bank_trans_session_id));
                $data['status'] = 200;
            }

            catch(Exception $ex){
                $data['status']  = 500;
                $data['message'] = $ex->getMessage();
            }
        }

        else if(in_array($topup_method, array("stripe", "stripe_alipay")) && $cl['config']['stripe_method_status'] == 'on') {
            try {
                require_once(cl_full_path("core/libs/Stripe/vendor/autoload.php"));

                $stripe_methods = array("stripe" => "card", "stripe_alipay" => "alipay");
                $stripe         = new \Stripe\StripeClient($cl["config"]["stripe_api_pass"]);
                $stripe_session = $stripe->checkout->sessions->create(array(
                    "payment_method_types" => array(
                        $stripe_methods[$topup_method]
                    ),
                    "success_url" => cl_link("native_api/wallet/pgw3_wallet_tup_success"),
                    "cancel_url" => cl_link("wallet"),
                    "mode" => "payment",
                    "line_items" => array(
                        array(
                            "price_data" => array(
                                "currency"  => strtoupper($cl["config"]["site_currency"]),
                                "product_data" => array(
                                    "name" => cl_translate('Top up your account balance')
                                ),
                                "unit_amount" => ($topup_amount * 100)
                            ),
                            "quantity" => 1
                        )
                    )
                ));

                if (not_empty($stripe_session)) {
                    $data["status"]  = 200;
                    $data["sess_id"] = $stripe_session->id;

                    cl_session('stripe_session', $data["sess_id"]);
                    cl_session('tup_amount', $topup_amount);
                }
            }

            catch(Exception $ex){
                $data['status']  = 500;
                $data['message'] = $ex->getMessage();
            }
        }
    }
}

else if($action == 'pgw1_wallet_tup_success') {
    if (not_empty($_GET['token'])) {
        try{

            require_once("core/libs/configs/paypal.php");

            $paym_tok = fetch_or_get($_GET['token'], false);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $paypal_req_url . '/v2/checkout/orders/' . $paym_tok . '/capture');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: Bearer ' . $cl['paypal_access_token'];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                cl_session_unset('tup_amount');
                cl_redirect('wallet');

                exit();
            }

            curl_close($ch);

            $tup_amount = cl_session('tup_amount');

            if (not_empty($result) && $tup_amount) {
                $result = json($result);

                if ($result["status"] == "COMPLETED") {

                    cl_update_user_data($me['id'], array(
                        'wallet' => ($me['wallet'] += $tup_amount)
                    ));

                    cl_db_insert(T_WALLET_HISTORY, array(
                        'user_id'   => $me['id'],
                        'operation' => 'paypal_wallet_tup',
                        'amount'    => $tup_amount,
                        'json_data' => json(array(
                            'paypal_pid' => $result["id"]
                        ), true),
                        'time' => time()
                    ));

                    cl_session_unset('tup_amount');

                    cl_redirect('wallet');
                }
            }
            else {
                throw new Exception('The payment was not approved or an error occurred when trying to complete the transaction');
            }
        }

        catch (Exception $e) {
            cl_session_unset('tup_amount');

            cl_session('err500_message', array(
                'title' => "Transaction failed!",
                'desc' => $e->getMessage()
            ));

            cl_redirect('500');
        }
    }
}

else if($action == 'pgw1_wallet_tup_cancel') {
    cl_session_unset('tup_amount');
    cl_redirect('wallet');
}

else if($action == 'pgw2_wallet_tup_verification') {
    if (not_empty($_GET['reference'])) {

        try{
            $reference1 = fetch_or_get($_GET['reference'], false);
            $tup_amount = cl_session('tup_amount');
            $reference2 = cl_session('paystack_reference');

            if ($tup_amount && ($reference1 == $reference2)) {
                
                require_once(cl_full_path("core/libs/PayStack-PHP/vendor/autoload.php"));

                $paystack = new \Yabacon\Paystack($cl["config"]["paystack_api_pass"]);
                $tranx    = $paystack->transaction->verify(array(
                    'reference' => $reference1
                ));
                
                cl_update_user_data($me['id'], array(
                    'wallet' => ($me['wallet'] += $tup_amount)
                ));

                cl_db_insert(T_WALLET_HISTORY, array(
                    'user_id'   => $me['id'],
                    'operation' => 'paystack_wallet_tup',
                    'amount'    => $tup_amount,
                    'json_data' => json(array(
                        'paystack_ref' => $reference1
                    ), true),
                    'time' => time()
                ));

                cl_session_unset('tup_amount');
                cl_session_unset('paystack_reference');

                cl_redirect('wallet');
            }
            else {
                throw new Exception('An error occurred while processing your request. Please try again later. Please contact our support team');
            }
        }

        catch (Exception $e) {
            cl_session_unset('tup_amount');

            cl_session('err500_message', array(
                'title' => "Transaction failed!",
                'desc' => $e->getMessage()
            ));

            cl_redirect('500');
        }
    }
}

else if($action == 'pgw3_wallet_tup_success') {
    $tup_amount     = cl_session('tup_amount');
    $stripe_session = cl_session('stripe_session');

    if ($tup_amount && $stripe_session) {

        try{

            require_once(cl_full_path("core/libs/Stripe/vendor/autoload.php"));

            $stripe         = new \Stripe\StripeClient($cl["config"]["stripe_api_pass"]);
            $session_object = $stripe->checkout->sessions->retrieve($stripe_session);

            if ($session_object && not_empty($session_object->payment_status) && $session_object->payment_status == "paid") {
                cl_update_user_data($me['id'], array(
                    'wallet' => ($me['wallet'] += $tup_amount)
                ));

                cl_db_insert(T_WALLET_HISTORY, array(
                    'user_id' => $me['id'],
                    'operation' => 'stripe_wallet_tup',
                    'amount' => $tup_amount,
                    'json_data' => json(array(
                        'sess_id' => $session_object->id,
                        'payment_intent' => $session_object->payment_intent
                    ), true),
                    'time' => time()
                ));

                cl_session_unset('tup_amount');
                cl_session_unset('stripe_session');

                cl_redirect('wallet');
            }

        }
        catch (Exception $e) {
            cl_session_unset('tup_amount');
            cl_session_unset('stripe_session');

            cl_session('err500_message', array(
                'title' => "Transaction failed!",
                'desc' => $e->getMessage()
            ));

            cl_redirect('500');
        }
    }
}

else if($action == 'banktrans_receipt_submit') {
    $data['status']   = 400;
    $data['err_code'] = 0;

    $bank_trans_session = cl_session("bank_trans_session");

    if (empty($bank_trans_session) || is_array($bank_trans_session) != true) {
        $data['err_code'] = 'invalid_session_id';
    }

    else if (not_empty($_POST['text_message']) && len($_POST['text_message']) > 1200) {
        $data['err_code'] = "invalid_text_message";
    }

    else if(empty($_FILES['receipt']) || empty($_FILES['receipt']['tmp_name'])) {
        $data['err_code'] = "invalid_receipt_photo";
    }

    else {
        $file_info      = array(
            'file'      => $_FILES['receipt']['tmp_name'],
            'size'      => $_FILES['receipt']['size'],
            'name'      => $_FILES['receipt']['name'],
            'type'      => $_FILES['receipt']['type'],
            'file_type' => 'image',
            'folder'    => 'videos',
            'slug'      => 'image_receipt',
            'allowed'   => 'jpg,png,jpeg,gif'
        );

        $file_upload = cl_upload($file_info);

        if (not_empty($file_upload['filename'])) {
            $text_message = cl_text_secure($_POST['text_message']);
            $trans_id = cl_strf("TID_%s", sha1(microtime()));
            $insert_data  = array(
                'user_id' => $me['id'],
                'message' => $text_message,
                'receipt_img' => $file_upload['filename'],
                'time' => time(),
                'amount' => $bank_trans_session["amount"],
                'currency' => $bank_trans_session["currency"],
                'trans_id' => $trans_id
            );

            $req_id = $db->insert(T_BANKTRANS_REQS, $insert_data);

            $history_rec_id = $db->insert(T_WALLET_HISTORY, array(
                "user_id" => $me["id"],
                "operation" => "banktrans_wallet_tup",
                "amount" => $bank_trans_session["amount"],
                "time" => time(),
                "status" => "pending_approval",  
                "trans_id" => $trans_id
            ));

            if (is_posnum($req_id) && is_posnum($history_rec_id)) {
                $data['err_code'] = 0;
                $data['status']   = 200;

                cl_session_unset("bank_trans_session");
            }
        }
    }
}

else if($action == 'search_recipients') {
    $data['err_code'] = 0;
    $data['status']   = 400;
    $username         = fetch_or_get($_GET["query"], false);
    $username         = cl_text_secure($username);
    $username         = cl_croptxt($username, 32);
    $users_list       = cl_money_recipients_search($username);

    if (not_empty($users_list)) {
        $data["status"] = 200;
        $data["recipients_list"] = $users_list;
    }
}

else if($action == 'send_money') {
    $data['err_code'] = 0;
    $data['status']   = 400;
    $send_amount      = fetch_or_get($_POST["amount"], false);
    $user_id          = fetch_or_get($_POST["user_id"], false);

    if (is_numeric($send_amount) && is_posnum($user_id)) {

        $send_amount = ($send_amount > $me["wallet"]) ? $me["wallet"] : $send_amount;

        $recipient_data = cl_raw_user_data($user_id);

        if (not_empty($recipient_data)) {

            $trans_id = cl_strf("TID_%s", sha1(microtime()));
            $data['status'] = 200;
            
            cl_update_user_data($user_id, array(
                "wallet" => ($recipient_data["wallet"] += $send_amount)
            ));

            cl_update_user_data($me["id"], array(
                "wallet" => ($me["wallet"] -= $send_amount)
            ));

            $db->insert(T_WALLET_HISTORY, array(
                "user_id" => $me["id"],
                "operation" => "wallet_local_transfer",
                "amount" => $send_amount,
                "time" => time(),
                "status" => "success",  
                "trans_id" => $trans_id,
                "json_data" => cl_minify_js(json(array("username" => cl_strf("%s %s", $recipient_data["fname"], $recipient_data["lname"])), true))
            ));

            $db->insert(T_WALLET_HISTORY, array(
                "user_id" => $user_id,
                "operation" => "wallet_local_receipt",
                "amount" => $send_amount,
                "time" => time(),
                "status" => "success",  
                "trans_id" => $trans_id,
                "json_data" => cl_minify_js(json(array("username" => $me["name"]), true))
            ));

            cl_notify_user(array(
                'subject'  => 'wallet_local_receipt',
                'user_id'  => $recipient_data["id"],
                'entry_id' => $me["id"],
                'json' => cl_minify_js(json(array(
                    "trans_amount" => cl_money($send_amount)
                ), true))
            ));
        }
    }
}

else if($action == 'verify_rzp_payment') {
    $data['err_code'] = 0;
    $data['status']   = 400;

    $tup_amount = cl_session('tup_amount');
    $save_razorpay_order_id = cl_session('razorpay_order_id');

    $payment_id = fetch_or_get($_POST["payment_id"], false);
    $order_id = fetch_or_get($_POST["order_id"], false);
    $signature = fetch_or_get($_POST["signature"], false);

    if ($save_razorpay_order_id && $tup_amount && $payment_id && $order_id && $signature) {
        if ($save_razorpay_order_id == $order_id) {
            try {
                require_once(cl_full_path("core/libs/RazorPay/vendor/autoload.php"));


                $razorpay_api = new Razorpay\Api\Api($cl['config']['rzp_api_key'], $cl['config']['rzp_api_secret']);

                $verify_checkout = $razorpay_api->utility->verifyPaymentSignature(array(
                    "razorpay_signature" => $signature,
                    "razorpay_payment_id" => $payment_id,
                    "razorpay_order_id" => $order_id
                ));

                cl_update_user_data($me['id'], array(
                    'wallet' => ($me['wallet'] += $tup_amount)
                ));

                cl_db_insert(T_WALLET_HISTORY, array(
                    'user_id'   => $me['id'],
                    'operation' => 'razorpay_wallet_tup',
                    'amount'    => $tup_amount,
                    'json_data' => json(array(
                        "razorpay_signature" => $signature,
                        "razorpay_payment_id" => $payment_id,
                        "razorpay_order_id" => $order_id
                    ), true),
                    'time' => time()
                ));

                cl_session_unset('tup_amount');
                cl_session_unset('razorpay_order_id');
                
                $data['status'] = 200;
            }

            catch(Exception $ex){
                $data['status']  = 500;
                $data['message'] = $ex->getMessage();
            }
        }
    }
}

else if($action == 'withdrawal_req_submit') {
    $data['status']   = 400;
    $data['err_code'] = 0;

    if (empty($_POST['withdrawal_amount']) || is_posnum($_POST['withdrawal_amount']) != true || $_POST['withdrawal_amount'] < 50 || $_POST['withdrawal_amount'] > 15000) {
        $data['err_code'] = "invalid_withdrawal_amount";
    }
    
    else if (empty($_POST['withdrawal_method']) || len_between($_POST['withdrawal_method'], 3, 42) != true) {
        $data['err_code'] = "invalid_withdrawal_method";
    }

    else if (empty($_POST['withdrawal_requisites']) || len_between($_POST['withdrawal_requisites'], 1, 600) != true) {
        $data['err_code'] = "invalid_withdrawal_equisites";
    }

    else {
        $withdrawal_awaiting = cl_is_withdrawal_awaiting($me["id"]);

        if ($withdrawal_awaiting != true) {

            $withdrawal_amount = cl_text_secure($_POST['withdrawal_amount']);
            $withdrawal_method = cl_text_secure($_POST['withdrawal_method']);
            $withdrawal_requisites = cl_text_secure($_POST['withdrawal_requisites']);

            $payout_req_id = $db->insert(T_WALLET_POUT, array(
                "user_id" => $me["id"],
                "amount" => $withdrawal_amount,
                "method" => $withdrawal_method,  
                "requisites" => $withdrawal_requisites,
                "time" => time(),
            ));

            if ($payout_req_id) {
                $history_rec_id = $db->insert(T_WALLET_HISTORY, array(
                    "user_id" => $me["id"],
                    "operation" => "wallet_withdrawal_req",
                    "amount" => $withdrawal_amount,
                    "json_data" => json(array("withdrawal_method" => $withdrawal_method), true),
                    "time" => time(),
                    "status" => "pending_approval"
                ));

                $data['err_code'] = 0;
                $data['status'] = 200;
            }
        }
    }
}