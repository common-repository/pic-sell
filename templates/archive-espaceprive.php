<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (isset($_GET["validate_commande"]) && !empty($_GET["validate_commande"])) {
    get_header();

    $content = "";
    $content .= "<div class='container'>";
    $content .=     "<p>Merci pour votre achat ! Vous recevrez dans quelques minutes un mail de confirmation.</p>";
    $content .= "</div>";
    echo wp_kses_post($content);
    
    get_footer();
    exit();
}

if (isset($_GET["validate_ipn"]) && !empty($_GET["validate_ipn"])) {

    $urlparts = parse_url(home_url());
    $domain = $urlparts['host'];

    // Check to see there are posted variables coming into the script
    if ($_SERVER['REQUEST_METHOD'] != "POST")
        die("No Post Variables");
    
    $paypal = get_option("paypal_pic");
    $paypal_sandbox = (isset($paypal["paypal"]["sandbox"]) && $paypal["paypal"]["sandbox"]) ? true : false;
    $paypal_url = $paypal_sandbox ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr";

    $config = get_option('config_pic');
    $admin_address_mail = isset($config["config"]["adresse"]) ? $config["config"]["adresse"] : false;

    $body = ['cmd' =>'_notify-validate'];
    $body += stripslashes_deep($_POST); //Who sanitize ?

    $args = array(
        'body'        => $body, // <-- error if sanitize
        'method' => 'POST',
        'sslverify' => false,
        'timeout' => 60,
        'httpversion' => '1.1',
        'compress' => false,
        'decompress' => false,
        'user-agent' => 'paypal-ipn/'
    );

    $response = wp_safe_remote_post($paypal_url, $args ); //replacement for safe

    do_action('pic_paypal_express_ipn', $body, $response);

    if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {

        $body = array_map(function($input){
                    return sanitize_text_field($input); //sanitize all input POST
                    }, $body);

        $payer_email = sanitize_email($body['payer_email']);//double sanitize text and email
        $custom = $body['custom']; //sanitize text l.52

        // Check 1
        $receiver_email = sanitize_email($body['receiver_email']);//double sanitize text and email
        $paypal_address_mail = isset($paypal["paypal"]["adresse"]) ? $paypal["paypal"]["adresse"] : "fake@mail.com"; //ne pas mettre vide
        if ($receiver_email != $paypal_address_mail) {
            die("Address mail receiver email is invalid");
        }

        // Check 2 
        if ($body['payment_status'] != "Completed") {
            $infoMail = "Le paiement est en défault, merci de recommencer.";
            $infoAdmin = "Un paiement est parvenu en invalide.";
            if ($admin_address_mail) {
                wp_mail($payer_email, "Paiement invalide", $infoMail, "From: noreply@$domain");
            }
            die($infoMail);
        }

        // Check 3
        $txn_id = $body['txn_id']; //sanitize text l.52
        $custom = $body["custom"]; //sanitize text l.52

        $defaultOrders = array("orders" => []);
        $allOrders = get_option('allcommands_pic', serialize(json_encode($defaultOrders)));
        $allOrders = json_decode(unserialize($allOrders), true);

        if (array_key_exists($txn_id, $allOrders["orders"])) {
            $infoAdmin = "Un paiement avec un ID identique à tenté de payer.";
            if ($admin_address_mail) {
                wp_mail($admin_address_mail, "INFO: Paypal paiement identique(txn_id)", $infoAdmin, "From: noreply@$domain");  
            }
            die();
        }

        // Check 4
        if (session_id() == '') {
            session_start();
        }

        require(PIC_SELL_PATH_INC . "app/panier.php");

        $cart = new PIC_Panier();
        $cart->emailOrder($txn_id, $custom);

        exit();


    } else {
        if ($admin_address_mail) {
            wp_mail(sanitize_email($admin_address_mail), "IPN interaction not verified",$req, "From: noreply@$domain");
            die("\n\nData NOT verified from Paypal!");
        }
    }

}

if(isset($_GET["commande"]) && !empty($_GET["commande"])){

    $txn_id = sanitize_text_field($_GET["commande"]);
    get_header();

    require(PIC_SELL_PATH_INC . "app/panier.php");
    $cart = new PIC_Panier();
    $cart->getOrders($txn_id, false);

    get_footer();
    exit();    
}
