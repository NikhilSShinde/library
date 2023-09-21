<?php
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');
Route::get('/become-a-user', 'Auth\AuthController@becomeAStar');
Route::post('/become-a-user', 'Auth\AuthController@becomeAStar');
Route::get('/print-pdf/{id}', 'Auth\AuthController@printPdf');
Route::get('/become-a-user-personal-info', 'Auth\AuthController@becomeAStarSetp1');
Route::post('/become-a-user-personal-info', 'Auth\AuthController@becomeAStarSetp1');
Route::get('/become-a-user-success', 'Auth\AuthController@becomeAStarSuccess');
Route::get('/permission/denied', 'HomeController@permissionDenied');
Route::get('/redirect-dashboard', 'HomeController@toDashboard');
Route::get('/user-profile', 'HomeController@toDashboard');
Route::get('/change-locale/{locale}', 'HomeController@changeLocale');
Route::auth();
// For User Profile and account settings...
Route::get('verify-user-email/{id}', ['uses' => 'ProfileController@verifyUserEmail']);
Route::get('download-pdf-file/{file_name}', ['uses' => 'Auth\AuthController@downloadPdfFile']);
Route::get('download-pdf-file-admin/{file_name}', ['uses' => 'Auth\AuthController@downloadPdfFileAdmin']);
Route::get('chk-email-duplicate', ['uses' => 'ProfileController@chkEmailDuplicate']);
Route::get('chk-current-password', ['uses' => 'ProfileController@chkCurrentPassword']);
Route::get('profile', ['middleware' => 'auth', 'uses' => 'ProfileController@show']);
Route::get('update-profile', ['middleware' => 'auth', 'uses' => 'ProfileController@updateProfile']);
Route::post('update-profile-post', ['middleware' => 'auth', 'uses' => 'ProfileController@updateProfileInfo']);
Route::get('change-email', ['middleware' => 'auth', 'uses' => 'ProfileController@updateEmail']);
Route::post('change-email-post', ['middleware' => 'auth', 'uses' => 'ProfileController@updateEmailInfo']);
Route::get('change-password', ['middleware' => 'auth', 'uses' => 'ProfileController@updatePassword']);
Route::post('change-password-post', ['middleware' => 'auth', 'uses' => 'ProfileController@updatePasswordInfo']);

//routes for webservices
Route::get('api/order-payment-paypal/{order_number}/{locale}', ['uses' => 'DeliveryPaymentController@paymentForOrder']);
Route::get('make-payment-tap/{user_id}/{amount}/{locale}', ['uses' => 'DeliveryPaymentController@paymentForOrderTap']);
Route::get('make-payment-tap-order/{order_number}/{locale}', ['uses' => 'DeliveryPaymentController@paymentForOrderCompleteTap']);
Route::get('tap-payment-success', ['uses' => 'DeliveryPaymentController@orderPaymentSuccessTap']);
Route::get('tap-payment-error', ['uses' => 'DeliveryPaymentController@orderPaymentErrorTap']);
Route::get('payment/success/', ['uses' => 'DeliveryPaymentController@paymentSuccess']);
Route::post('api/deliveryuser-registration', ['uses' => 'DeliveryController@deliveryuserRegistration']);
Route::post('api/customer-registration', ['uses' => 'DeliveryController@customerRegistration']);
Route::post('api/send-otp', ['uses' => 'DeliveryController@sendOtpForRegstration']);
Route::post('api/send-otp-forgot', ['uses' => 'DeliveryController@sendOtpForForgotPassword']);
Route::post('api/verify-otp', ['uses' => 'DeliveryController@verifyOtp']);
Route::post('api/user-login', ['uses' => 'DeliveryController@userLogin']);
Route::post('user-login-site', ['uses' => 'HomeController@userLoginSite']);
Route::post('api/country-data', ['uses' => 'DeliveryController@countryData']);
Route::get('api/country-data-to-file-en', ['uses' => 'DeliveryController@countryDataToFileEn']);
Route::get('api/country-data-to-file-mr', ['uses' => 'DeliveryController@countryDataToFileMr']);
Route::get('api/country-data-to-file-hi', ['uses' => 'DeliveryController@countryDataToFileHi']);
Route::post('api/country-data-ios', ['uses' => 'DeliveryController@countryDataIOS']);
Route::post('api/location-data', ['uses' => 'DeliveryController@locationData']);
Route::post('api/reset-password', ['uses' => 'DeliveryController@resetPassword']);
Route::post('api/get-content-page', ['uses' => 'DeliveryController@getContentPages']);
Route::post('api/contact-us-categories', ['uses' => 'DeliveryController@getContactUsCategories']);
Route::post('api/contact-us', ['uses' => 'DeliveryController@contactUs']);
Route::post('api/social-connect', ['uses' => 'DeliveryController@socialConnect']);
Route::post('api/is-social-connect', ['uses' => 'DeliveryController@isSocialConnect']);

/* web-services added on 28-11-2016 */
Route::post('api/get-all-customer-notifications', ['uses' => 'DeliveryUserController@getAllCustomerNotifications']);
Route::post('api/customer-user-profile', ['uses' => 'DeliveryUserController@customerUserProfile']);
Route::post('api/deliveryuser-user-profile', ['uses' => 'DeliveryUserController@deliveryuserUserProfile']);
Route::post('api/update-customer-profile', ['uses' => 'DeliveryUserController@updateCustomerUser']);
Route::any('api/update-deliveryuser-profile', ['uses' => 'DeliveryUserController@updateDeliveryuserUser']);
Route::post('api/change-customer-password', ['uses' => 'DeliveryUserController@changeCustomerPassword']);
Route::post('api/change-customer-email', ['uses' => 'DeliveryUserController@changeCustomerEmail']);
Route::post('api/send-otp-for-change-mobile', ['uses' => 'DeliveryUserController@sendOtpForChangeMobile']);
Route::post('api/change-mobile', ['uses' => 'DeliveryUserController@updateCustomerMobile']);

Route::post('api/add-customer-emergency-contact', ['uses' => 'DeliveryUserController@addCustomerEmergencyContact']);
Route::post('api/list-emergency-contact', ['uses' => 'DeliveryUserController@listCustomerEmergencyContact']);
Route::post('api/update-customer-emergency-contact', ['uses' => 'DeliveryUserController@updateCustomerEmergencyContact']);
Route::post('api/delete-customer-emergency-contact', ['uses' => 'DeliveryUserController@deleteCustomerEmergencyContact']);
Route::post('api/send-customer-sos', ['uses' => 'DeliveryUserController@sendSosSMS']);
Route::post('api/customer-wallet-amount', ['uses' => 'DeliveryUserController@getCustomerWalletBalance']);
Route::any('api/deliveryuser-wallet-amount', ['uses' => 'DeliveryUserController@getdeliveryuserWalletBalance']);
Route::post('api/deliveryuser-wallet-transaction-history', ['uses' => 'DeliveryUserController@getdeliveryuserTransactionHistory']);
Route::post('api/customer-wallet-transaction-history', ['uses' => 'DeliveryUserController@getCustomerTransactionHistory']);


Route::post('api/user-support-ticket-list', ['uses' => 'DeliveryUserController@getUserSupportTicketList']);
Route::post('api/add-support-ticket', ['uses' => 'DeliveryUserController@addUserSupportTicket']);
Route::post('api/add-comment-on-ticket', ['uses' => 'DeliveryUserController@postCommentOnTicket']); // pending orders
Route::post('api/get-ticket-history', ['uses' => 'DeliveryUserController@getTicketHistory']); // pending orders

Route::post('api/get-customer-current-orders', ['uses' => 'DeliveryUserController@getCustomerUserCurrentOrder']);
Route::post('api/get-customer-active-orders', ['uses' => 'DeliveryUserController@getCustomerUserActiveOrder']);
Route::post('api/get-deliveryuser-current-orders', ['uses' => 'DeliveryUserController@getdeliveryuserUserCurrentOrder']);
Route::post('api/get-deliveryuser-history-orders', ['uses' => 'DeliveryUserController@getdeliveryuserUserOrderHistory']);
Route::post('api/get-order-details', ['uses' => 'DeliveryUserController@getOrderDetails']);
Route::post('api/get-customer-history-orders', ['uses' => 'DeliveryUserController@getCustomerUserOrderHistory']);

Route::post('api/get-user-spoken-languages', ['uses' => 'DeliveryUserController@getUserSpokenLanguages']); // pending orders
Route::post('api/add-user-spoken-languages', ['uses' => 'DeliveryUserController@addUserSpokenLanguages']); // pending orders
Route::post('api/get-spoken-languages', ['uses' => 'DeliveryUserController@getSpokenLanguages']); // pending orders


/** for address ***/

Route::post('api/add-new-address', ['uses' => 'DeliveryUserController@addUserAddress']); //
Route::post('api/update-user-address', ['uses' => 'DeliveryUserController@updateUserAddress']); // 
Route::post('api/delete-user-address', ['uses' => 'DeliveryUserController@deleteUserAddress']); // 
Route::post('api/list-user-addresses', ['uses' => 'DeliveryUserController@listUserAddresses']); // 

Route::post('api/set-user-availability', ['uses' => 'DeliveryUserController@setUserAvailability']); // 
Route::post('api/get-fare-estimate', ['uses' => 'DeliveryController@getfareEstimate']); // 
Route::post('api/get-fare-estimate-by-category', ['uses' => 'DeliveryController@getfareEstimateByCategory']); // 

Route::post('api/calculate-price', ['uses' => 'DeliveryUserController@calculatePrice']); // 
Route::post('api/category-data', ['uses' => 'DeliveryController@getCategoryData']); // 
Route::post('api/category-data-ios', ['uses' => 'DeliveryController@getCategoryDataIOS']); // 
Route::get('api/category-data-file', ['uses' => 'DeliveryController@getCategoryDataToFileEn']); // 
Route::get('api/category-data-file-ar', ['uses' => 'DeliveryController@getCategoryDataToFileAr']); // 
Route::get('api/category-data-file-ar-ios', ['uses' => 'DeliveryController@getCategoryDataToFileArIOS']); // 
Route::post('api/get-available-deliveryusers', ['uses' => 'DeliveryController@getdeliveryUsers']); // 
Route::post('api/save-card-details', ['uses' => 'DeliveryController@saveUserCard']);
Route::post('api/edit-card-details', ['uses' => 'DeliveryController@EditUserCard']);
Route::post('api/remove-card-details', ['uses' => 'DeliveryController@removeUserCard']);
Route::post('api/list-user-cards', ['uses' => 'DeliveryController@listUserCards']);
Route::get('api/check-msg', ['uses' => 'DeliveryController@checkMsg']);

Route::post('api/make-an-order', ['uses' => 'DeliveryController@makeAnOrder']);
Route::post('api/make-an-order-send-request', ['uses' => 'DeliveryController@makeAnOrderSendRequest']);
Route::post('api/get-driver-details', ['uses' => 'DeliveryController@getDriverDetails']);

Route::get('api/check-push-notification', ['uses' => 'DeliveryController@checkPushNotification']);
Route::post('api/update-deliveryuser-current-location', ['uses' => 'DeliveryController@updateDeliveryuserCurrentLocation']);
Route::post('api/track-deliveryuser-order-location', ['uses' => 'DeliveryController@trackDeliveryuserOrderLocation']);
Route::post('api/deliveryuser-accept-order', ['uses' => 'DeliveryUserController@deliveryuserAcceptOrder']);
Route::post('api/delete-order', ['uses' => 'DeliveryUserController@deleteOrder']);
Route::post('api/deliveryuser-reject-order', ['uses' => 'DeliveryUserController@deliveryuserRejectOrder']);
Route::post('api/deliveryuser-update-order-status', ['uses' => 'DeliveryUserController@updateDeliveryuserStatus']);
//Route::post('api/reject-accept-order', ['uses' => 'DeliveryUserController@deliveryuserAcceptOrder']);
Route::post('api/get-payment-methods', ['uses' => 'DeliveryUserController@getAllPaymentMethod']);
Route::post('api/update-deliveryuser-payment-methods', ['uses' => 'DeliveryUserController@updateDeliveryuserPaymentMethods']);
Route::post('api/get-deliveryuser-payment-methods', ['uses' => 'DeliveryUserController@getAllUserPaymentMethod']);
Route::get('api/complete-order-payment-success', ['uses' => 'DeliveryPaymentController@orderCompletePaymentSuccess']);
Route::get('api/complete-order-payment-error', ['uses' => 'DeliveryPaymentController@orderCompletePaymentError']);
Route::post('api/get-all-rating-tags', ['uses' => 'DeliveryController@getAllRatingQuestions']);
Route::post('api/add-user-rating', ['uses' => 'DeliveryController@giveRating']);


/* web-services added on 28-11-2016 */
Route::post('api/get-customer-slider-images', ['uses' => 'DeliveryController@getAllCustomerSliderImages']);
Route::post('api/get-deliveryuser-slider-images', ['uses' => 'DeliveryController@getAllDeliveryuserSliderImages']);
Route::post('api/get-user-services', ['uses' => 'DeliveryController@getServiceName']);
Route::post('api/update-user-service', ['uses' => 'DeliveryController@updateServiceName']);
Route::get('api/make-schedule-order-cron', ['uses' => 'DeliveryController@makeAnScheduleOrder']);
Route::get('api/make-schedule-order-cron-marine', ['uses' => 'DeliveryController@makeAnScheduleOrderMarine']);
Route::post('api/deliveryuser-quote-on-order', ['uses' => 'DeliveryUserController@deliveryuserQuoteOnOrder']);
Route::post('api/deliveryuser-reject-quotation', ['uses' => 'DeliveryUserController@deliveryuserRejectQuotation']);
Route::post('api/customer-reject-quotation', ['uses' => 'DeliveryUserController@mateRejectQuotation']);
Route::post('api/order-all-quotations', ['uses' => 'DeliveryUserController@orderAllQuotation']);
Route::post('api/customer-accept-order-quotation', ['uses' => 'DeliveryUserController@mateAcceptQuotation']);
Route::post('api/get-quotation-service-estimate', ['uses' => 'DeliveryController@getFareEstimateForQuotation']);
Route::get('api/cron-order-notification', ['uses' => 'DeliveryController@removeNotificationsForActiveOrders']);
Route::get('api/expired-order-crons', ['uses' => 'DeliveryController@expiredOrdersCron']);
Route::get('api/cron-reject-reassign', ['uses' => 'DeliveryUserController@rejectDeliveryuserAndReassign']);
Route::get('api/cron-reject-reassign-scheduled', ['uses' => 'DeliveryUserController@rejectDeliveryuserAndReassignScheduled']);
Route::post('api/recharge-customer-wallet', ['uses' => 'DeliveryUserController@addCustomerWalletBalance']);
Route::post('api/complete-order-payment-type', ['uses' => 'DeliveryPaymentController@orderPaymentSuccess']);
Route::post('api/get-chat-users', ['uses' => 'DeliveryUserController@getChatUsers']);
Route::post('api/get-status-msgs-by-category', ['uses' => 'DeliveryController@getStatusMessagesByCategory']);
Route::post('api/get-app-faqs', ['uses' => 'DeliveryController@getAppFAQS']);
