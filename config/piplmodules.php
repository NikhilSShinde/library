<?php
return [
   'modules'=>array(
        "admin",
        "emailtemplate",
        'contactrequest',
        'faq',
        'blog',
        'category', 
        'service',       
        'reports', 
        'slider',        
        'supporttickets',
        'coupon',
        'wallethistory',
        'orderdetails',
        'ratingreview',
        'vehicle',
        'loan',
        'contentpage', // It must include always in last
    ),
	'front-view-layout-location'=>'layouts.app',
	'back-view-layout-location'=>'layouts.admin',
	'back-view-layout-location-payment'=>'layouts.admin-payment',
        'back-view-layout-login-location'=>'layouts.admin-login',
        'back-left-view-layout-location'=>'layouts.admin-left'
];