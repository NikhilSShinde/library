<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier {

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
   protected $except = [
     'api/get-app-faqs','api/get-customer-active-orders','api/get-ticket-history','api/get-all-customer-notifications','api/get-driver-details','api/make-an-order-send-request','api/get-status-msgs-by-category','api/get-chat-users','api/category-data-ios','api/category-data-file-ar-ios','api/category-data-file','api/country-data-to-file', 'api/get-fare-estimate-by-category','api/cron-reject-reassign','api/expired-order-crons','api/recharge-customer-wallet','api/cron-order-notification',  'api/get-quotation-service-estimate', 'api/customer-reject-quotation','api/customer-accept-order-quotation', 'api/deliveryuser-quote-on-order','api/deliveryuser-reject-quotation','api/order-all-quotations', 'api/get-user-services','api/update-user-service', 'api/get-customer-slider-images','api/get-deliveryuser-slider-images','api/complete-order-payment-success', 'api/get-deliveryuser-payment-methods', 'api/add-user-rating','api/get-all-rating-tags',  'api/update-deliveryuser-payment-methods', 'api/get-payment-methods', 'api/deliveryuser-update-order-status', 'api/deliveryuser-reject-order', 'api/delete-order',  'api/deliveryuser-accept-order', 'api/get-order-details',  'api/get-deliveryuser-history-orders', 'api/get-deliveryuser-current-orders', 'api/track-deliveryuser-order-location', 'api/update-deliveryuser-current-location',  'api/check-push-notification',  'api/make-an-order','api/check-msg', 'api/save-card-details','api/list-user-cards','api/remove-card-details', 'api/get-available-deliveryusers', 'api/get-fare-estimate', 'api/set-user-availability', 'api/category-data','api/calculate-price', 'api/list-user-addresses', 'api/delete-user-address','api/update-user-address','api/add-new-address', 'api/list-user-addresses', 'api/list-emergency-contact','api/change-mobile','api/send-otp-for-change-mobile','api/reset-password', 'api/is-social-connect','api/social-connect','api/country-data-ios', 'api/get-content-page','api/contact-us','api/contact-us-categories', 'api/location-data','api/deliveryuser-registration','api/send-otp','api/verify-otp','api/user-login','api/country-data','api/send-otp-forgot','api/customer-registration','api/customer-user-profile','api/update-customer-profile','api/add-customer-emergency-contact','api/update-customer-emergency-contact','api/delete-customer-emergency-contact','api/update-deliveryuser-profile','api/deliveryuser-user-profile','api/customer-wallet-amount','api/deliveryuser-wallet-amount','api/deliveryuser-wallet-transaction-history','api/customer-wallet-transaction-history','api/user-support-ticket-list','api/add-support-ticket','api/change-customer-password','api/send-customer-sos','api/get-customer-current-orders','api/get-customer-history-orders','api/get-customer-posted-orders','api/add-comment-on-ticket','api/change-customer-email','api/get-user-spoken-languages','api/add-user-spoken-languages','api/get-spoken-languages','api/complete-order-payment-type','api/edit-card-details'
    ];

}
