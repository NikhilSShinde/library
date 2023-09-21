<?php

Route::group(array('module'=>'Admin', 'namespace' => 'App\PiplModules\admin\Controllers','middleware'=>'web','as'=>'admin::'), function() {
    //Your routes belong to this module.
	// Admin Login Profile and logout
    
	Route::get("/admin/login","AdminController@showLogin");
	Route::get("/admin/login-chat","AdminController@showLoginChat");
	Route::get("/admin/support-chat","AdminController@showSupportChat");
	
	Route::get("/admin","AdminController@showLogin");
        Route::get('download-pdf-file-admin/{file_name}', ['uses' => 'AdminController@downloadPdfFilePayment']);
	Route::get("/admin/dashboard","AdminController@showDashboard");
	Route::get("/company/dashboard","AdminController@showCompanyDashboard")->middleware('permission:view.company-dashboard');
	Route::get("/agent/dashboard","AdminController@showAgentDashboard")->middleware('permission:view.agent-dashboard');
	Route::get("/agent-manager/dashboard","AdminController@showAgentManagerDashboard")->middleware('permission:view.agent-manager-dashboard');
	Route::get("/free-toner/dashboard","AdminController@showFreeTonerDashboard")->middleware('permission:view.free-toner-users-dashboard');
        Route::get("/admin/profile","AdminController@adminProfile")->middleware('level:1');
        Route::get("/agent/profile","AdminController@agentProfile")->middleware('level:4');
        Route::post("/admin/update-profile-post","AdminController@updateProfile")->middleware('permission:update.admin-users');
        Route::post("/admin/update-agent-profile-post","AdminController@updateAgentProfile");
        Route::post("/admin/update-admin-email","AdminController@updateEmailInfo")->middleware('permission:update.admin-users');
        Route::get('admin/verify-user-email/{id}', ['uses' => 'AdminController@verifyUserEmail'])->middleware('level:1');
        Route::post('admin/change-password-post', ['uses' => 'AdminController@updatePasswordInfo'])->middleware('permission:update.admin-users');
        Route::get('admin/logout', ['uses' => 'AdminController@logout']);
        
	Route::get("/admin/update-user/{user_id}","AdminController@editUser")->middleware('permission:update.registered-users');
	Route::post("/admin/update-user/{user_id}","AdminController@editUser")->middleware('permission:update.registered-users');
	
	Route::post("/admin/update-user-password/{user_id}","AdminController@editUserPassword")->middleware('permission:update.registered-users');
	Route::post("/admin/update-user-status/{user_id}","AdminController@editUserStatus")->middleware('permission:update.registered-users');
	//-------------- Admin Login Profile and logout end
        
        //Manage roles
	Route::get("/admin/manage-roles","AdminController@listRoles")->middleware('permission:view.roles');
	Route::get("/admin/manage-roles-data","AdminController@listRolesData")->middleware('permission:view.roles');
	Route::get("/admin/update-role/{role_id}","AdminController@updateRole")->middleware('permission:update.roles');
	Route::post("/admin/update-role/{role_id}","AdminController@updateRole")->middleware('permission:update.roles');
	Route::get("/admin/roles/create","AdminController@createRole")->middleware('permission:create.roles');
	Route::post("/admin/roles/create","AdminController@createRole")->middleware('permission:create.roles');
	Route::get("/admin/roles/permissions/{role_id}","AdminController@updateRolePermissions")->middleware('permission:update.roles');
	Route::post("/admin/roles/permissions/{role_id}","AdminController@updateRolePermissions")->middleware('permission:update.roles');
	
	Route::delete("/admin/delete-role/{role_id}","AdminController@deleteRole")->middleware('permission:delete.roles');
	Route::delete("/admin/delete-role-select-all/{role_id}","AdminController@deleteRoleFromSelectAll")->middleware('permission:delete.roles');
	
        //Manage roles ends here
        
        
        //Manage Global Settings
	Route::get("/admin/global-settings","AdminController@listGlobalSettings")->middleware('permission:view.global-settings');
	Route::get("/admin/global-settings-data","AdminController@listGlobalSettingsData")->middleware('permission:view.global-settings');
	
	Route::get("/admin/update-global-setting/{setting_id}","AdminController@updateGlobalSetting")->middleware('permission:update.global-settings');
	Route::post("/admin/update-global-setting/{setting_id}","AdminController@updateGlobalSetting")->middleware('permission:update.global-settings');
	//Manage Global Settings end
        
        //Manage admin and registered users users
        Route::get("/admin/manage-users","AdminController@listRegisteredUsers")->middleware('permission:view.registered-users');
        Route::get("/admin/list-registered-users-data","AdminController@listRegisteredUsersData")->middleware('permission:view.registered-users');
	Route::delete("/admin/delete-user/{user_id}","AdminController@deleteRegisteredUser")->middleware('permission:delete.registered-users');
	Route::delete("/admin/delete-selected-user/{user_id}","AdminController@deleteSelectedRegisteredUser")->middleware('permission:delete.registered-users');
	Route::get("/admin/update-registered-user/{user_id}","AdminController@updateRegisteredUser")->middleware('permission:update.registered-users');
	Route::post("/admin/update-registered-user/{user_id}","AdminController@updateRegisteredUser")->middleware('permission:update.registered-users');
	Route::post("/admin/update-registered-user-email/{user_id}","AdminController@updateRegisteredUserEmailInfo")->middleware('permission:update.registered-users');
	Route::post("/admin/update-registered-user-password/{user_id}","AdminController@updateRegisteredUserPasswordInfo")->middleware('permission:update.registered-users');
	Route::get("/admin/create-registered-user","AdminController@createRegisteredUser")->middleware('permission:update.registered-users');
	Route::post("/admin/create-registered-user","AdminController@createRegisteredUser")->middleware('permission:update.registered-users');
	
        
      
	Route::get("/admin/admin-users","AdminController@listAdminUsers")->middleware('permission:view.admin-users');
	Route::get("/admin/admin-users-data","AdminController@listAdminUsersData")->middleware('permission:view.admin-users');
	
        Route::get("/admin/company-users","AdminController@listCompanyUsers")->middleware('permission:view.company-users');
	Route::get("/admin/agent-users","AdminController@listAgentUsers")->middleware('permission:view.agent-users');
	Route::get("/admin/free-toner-users","AdminController@listfreeTonnerUsers")->middleware('permission:view.free-toner-users');
	Route::get("/admin/free-toner-users-data","AdminController@listfreeTonnerUsersData")->middleware('permission:view.free-toner-users');
	 Route::get("/admin/create-free-toner-user","AdminController@createfreeTonnerUser")->middleware('permission:create.free-toner-user');
	Route::post("/admin/create-free-toner-user","AdminController@createfreeTonnerUser")->middleware('permission:create.free-toner-user');
        Route::get("/admin/update-free-toner-user/{user_id}","AdminController@updateFreeTonerUser")->middleware('permission:update.free-toner-user');
        Route::post("/admin/update-free-toner-user/{user_id}","AdminController@updateFreeTonerUser")->middleware('permission:update.free-toner-user');
	Route::post("/admin/update-free-toner-user-email/{user_id}","AdminController@updateFreeTonerUserEmailInfo")->middleware('permission:update.free-toner-user');
	Route::post("/admin/update-free-toner-user-password/{user_id}","AdminController@updateFreeTonerUserPasswordInfo")->middleware('permission:update.free-toner-user');
	
        Route::get("/admin/star-users/{agent_id?}","AdminController@listStarUsers")->middleware('permission:view.star-users');
        Route::get("/admin/stars-list-to-pay/{agent_id?}","AdminController@listStarUsersToPay");
        Route::get("/admin/view-agent-stars/{agent_id?}","AdminController@listStarUsersByAgent");
        Route::get("/admin/view-company-stars/{agent_id?}","AdminController@listStarUsersByCompany");
        Route::get("/admin/view-agent-stars-data/{agent_id?}","AdminController@listStarUsersDataByAgent");
	
        Route::get("/admin/company-users-data","AdminController@listCompanyUsersData")->middleware('permission:view.company-users');
	Route::get("/admin/agent-users-data","AdminController@listAgentUsersData")->middleware('permission:view.agent-users');
	
        Route::get("/admin/star-users-data","AdminController@listStarUsersData")->middleware('permission:view.star-users');
        Route::get("/admin/star-users-data-to-pay","AdminController@listStarUsersDataToPay");
        Route::get("/admin/update-admin-user/{user_id}","AdminController@updateAdminUser")->middleware('permission:update.admin-users');
	
        Route::post("/admin/update-admin-user/{user_id}","AdminController@updateAdminUser")->middleware('permission:update.admin-users');
	
       
        Route::post("/admin/update-admin-user-email/{user_id}","AdminController@updateAdminUserEmailInfo")->middleware('permission:update.admin-users');
	
        Route::post("/admin/update-company-user-email/{user_id}","AdminController@updateCompanyUserEmailInfo")->middleware('permission:update.company-user');
	Route::get("/admin/update-company-user/{user_id}","AdminController@updateCompanyUser")->middleware('permission:update.company-user');
	Route::post("/admin/update-agent-user-email/{user_id}","AdminController@updateAgentUserEmailInfo")->middleware('permission:update.agent-user');
	Route::get("/admin/update-agent-user/{user_id}","AdminController@updateAgentUser")->middleware('permission:update.agent-user');

	
        Route::post("/admin/update-star-user-email/{user_id}","AdminController@updateStarUserEmailInfo")->middleware('permission:update.star-user');
	Route::get("/admin/update-star-user/{user_id}","AdminController@updateStarUser")->middleware('permission:update.star-user');
	
        
        Route::post("/admin/update-company-user/{user_id}","AdminController@updateCompanyUser")->middleware('permission:update.company-user');

	Route::post("/admin/update-admin-user-email/{user_id}","AdminController@updateAdminUserEmailInfo")->middleware('permission:update.agent-user');
	Route::post("/admin/update-agent-user/{user_id}","AdminController@updateAgentUser")->middleware('permission:update.agent-user');
	Route::post("/admin/update-agent-user-email/{user_id}","AdminController@updateAgentUserEmailInfo")->middleware('permission:update.agent-user');
	
        Route::post("/admin/update-star-user/{user_id}","AdminController@updateStarUser")->middleware('permission:update.star-user');
        Route::post("/admin/update-star-vehicle/{user_id}","AdminController@updateStarUserVehicle")->middleware('permission:update.star-user');
	Route::post("/admin/update-star-user-email/{user_id}","AdminController@updateStarUserEmailInfo")->middleware('permission:update.star-user');

        Route::post("/admin/update-company-user-email/{user_id}","AdminController@updateCompanyUserEmailInfo")->middleware('permission:update.admin-users');
	Route::post("/admin/update-admin-user-password/{user_id}","AdminController@updateAdminUserPasswordInfo")->middleware('permission:update.admin-users');
	Route::post("/admin/update-company-user-password/{user_id}","AdminController@updateCompanyUserPasswordInfo")->middleware('permission:update.company-user');
	Route::post("/admin/update-agent-user-password/{user_id}","AdminController@updateAgentUserPasswordInfo")->middleware('permission:update.company-user');
	
        Route::post("/admin/update-star-user-password/{user_id}","AdminController@updateStarUserPasswordInfo")->middleware('permission:update.star-user');
        Route::get("/admin/create-user/{is_admin?}","AdminController@createUser")->middleware('permission:create.admin-users');
	Route::post("/admin/create-user/{is_admin?}","AdminController@createUser")->middleware('permission:create.admin-users');
	Route::get("/admin/create-company-user","AdminController@createCompanyUser")->middleware('permission:create.company-user');
	Route::post("/admin/create-company-user","AdminController@createCompanyUser")->middleware('permission:create.company-user');
	Route::get("/admin/create-agent-user","AdminController@createAgentUser")->middleware('permission:create.agent-user');
	Route::post("/admin/create-agent-user","AdminController@createAgentUser")->middleware('permission:create.agent-user');
	
       
        Route::get("/admin/create-star-user","AdminController@createStarUser")->middleware('permission:create.star-user');
	Route::post("/admin/create-star-user","AdminController@createStarUser")->middleware('permission:create.star-user');
	
        Route::delete("/admin/delete-admin-user/{user_id}","AdminController@deletAdminUser")->middleware('permission:delete.admin-users');


	Route::delete("/admin/delete-admin-selected-user/{user_id}","AdminController@deletSelectedAdminUser")->middleware('permission:delete.admin-users');
	
	Route::delete("/admin/delete-company-user/{user_id}","AdminController@deletCompanyUser")->middleware('permission:delete.company-user');
	Route::delete("/admin/delete-company-selected-user/{user_id}","AdminController@deletSelectedCompanyUser")->middleware('permission:delete.company-user');
	
	Route::delete("/admin/delete-agent-user/{user_id}","AdminController@deletAgentUser")->middleware('permission:delete.agent-user');
	Route::delete("/admin/delete-agent-selected-user/{user_id}","AdminController@deletSelectedAgentUser")->middleware('permission:delete.agent-user');
	Route::delete("/admin/delete-tone-user/{user_id}","AdminController@deleteFreeToneUser")->middleware('permission:delete.agent-user');
	Route::delete("/admin/delete-toner-selected-user/{user_id}","AdminController@deleteSelectedFreeToneUser")->middleware('permission:delete.agent-user');

	
        
        Route::delete("/admin/delete-star-user/{user_id}","AdminController@deletStarUser")->middleware('permission:delete.star-user');
       
	Route::delete("/admin/delete-star-selected-user/{user_id}","AdminController@deletSelectedStarUser")->middleware('permission:delete.star-user');


	//Manage admin users end
        
	
        //Manage counries
	Route::get("/admin/countries/list","AdminController@listCountries")->middleware('permission:view.manage-countries');
        Route::get("/admin/countries-data/list","AdminController@listCountriesData")->middleware('permission:view.manage-countries');
	
	Route::get("/admin/city-geo-settings-data/list","AdminController@listGeoSettingsData")->middleware('permission:view.manage-cities');
	Route::get("/admin/city-geo-settings/list","AdminController@listGeoCitiesSettings")->middleware('permission:view.manage-cities');
	
        Route::get("/admin/countries/update-language/{country_id}/{locale}","AdminController@updateCountryLanguage")->middleware('permission:update.countries');
	Route::post("/admin/countries/update-language/{country_id}/{locale}","AdminController@updateCountryLanguage")->middleware('permission:update.countries');
	
	Route::get("/admin/country_info/{country_id}","AdminController@getCountryInfo")->middleware('permission:update.countries');
	Route::get("/admin/countries/update/{country_id}","AdminController@updateCountry")->middleware('permission:update.countries');
	Route::post("/admin/countries/update/{country_id}","AdminController@updateCountry")->middleware('permission:update.countries');
	
	Route::get("/admin/create-geo-city-setting/create","AdminController@createGeoCityetting")->middleware('permission:update.cities');
	Route::post("/admin/create-geo-city-setting/create","AdminController@createGeoCityetting")->middleware('permission:update.cities');
	
        Route::get("/admin/geo-city-setting/update/{goe_id}","AdminController@updateGeoCitySetting")->middleware('permission:update.cities');
	Route::post("/admin/geo-city-setting/update/{goe_id}","AdminController@updateGeoCitySetting")->middleware('permission:update.cities');
	 Route::delete("/admin/geo-city-setting/delete/{limit_id}","AdminController@deletGeoCitySetting")->middleware('permission:update.cities');
        
        
       
        Route::get("/admin/countries/create","AdminController@createCountry")->middleware('permission:create.countries');
	Route::post("/admin/countries/create","AdminController@createCountry")->middleware('permission:create.countries');
	Route::delete("/admin/countries/delete/{country_id}","AdminController@deleteCountry")->middleware('permission:delete.countries');
	Route::delete("/admin/countries/delete-selected/{country_id}","AdminController@deleteCountrySelected")->middleware('permission:delete.countries');
	//Manage counries end
        
	//Manage states
	Route::get("/admin/states/list","AdminController@listStates")->middleware('permission:view.manage-states');
	Route::get("/admin/states-data/list","AdminController@listStatesData")->middleware('permission:view.manage-states');
	Route::get("/admin/states/update-language/{state_id}/{locale}","AdminController@updateStateLanguage")->middleware('permission:update.states');
	Route::post("/admin/states/update-language/{state_id}/{locale}","AdminController@updateStateLanguage")->middleware('permission:update.states');
	Route::get("/admin/states/getAllStates/{country_id}","AdminController@getAllStatesByCountry");
	Route::get("/admin/states/getAllStatesRegistration/{country_id}","AdminController@getAllStatesByCountryRegistration");
	
	Route::get("/admin/states/update/{state_id}","AdminController@updateState")->middleware('permission:update.states');
	Route::post("/admin/states/update/{state_id}","AdminController@updateState")->middleware('permission:update.states');
	
	Route::get("/admin/states/create","AdminController@createState")->middleware('permission:create.states');
	Route::post("/admin/states/create","AdminController@createState")->middleware('permission:create.states');
	
	Route::delete("/admin/states/delete/{state_id}","AdminController@deleteState")->middleware('permission:delete.states');
	Route::delete("/admin/states/delete-selected/{state_id}","AdminController@deleteStateSelected")->middleware('permission:delete.states');
	//Manage states end
        
	//Manage cities
	Route::get("/admin/cities","AdminController@listCities")->middleware('permission:view.manage-cities');
	Route::get("/admin/cities/list","AdminController@listCities")->middleware('permission:view.manage-cities');
	Route::get("/admin/cities-data/list","AdminController@listCitiesData")->middleware('permission:view.manage-cities');
	Route::get("/admin/cities/update-language/{city_id}/{locale}","AdminController@updateCityLanguage")->middleware('permission:update.cities');
	Route::post("/admin/cities/update-language/{city_id}/{locale}","AdminController@updateCityLanguage")->middleware('permission:update.cities');
	Route::get("/admin/cities/getAllCities/{country_id}/{state_id}","AdminController@getAllCitiesByCountryState");
	Route::get("/admin/cities/getAllCitiesStar/{country_id}/{state_id}","AdminController@getAllCitiesByCountryStateStar");
	Route::get("/admin/cities/getAllCitiesRegistration/{country_id}/{state_id}","AdminController@getAllCitiesByCountryStateRegistration");
	
	Route::get("/admin/cities/update/{city_id}","AdminController@updateCity")->middleware('permission:update.cities');
	Route::post("/admin/cities/update/{city_id}","AdminController@updateCity")->middleware('permission:update.cities');
	
	Route::get("/admin/cities/create","AdminController@createCity")->middleware('permission:create.cities');
	Route::post("/admin/cities/create","AdminController@createCity")->middleware('permission:create.cities');
	
	Route::delete("/admin/cities/delete/{city_id}","AdminController@deleteCity")->middleware('permission:delete.cities');
	Route::delete("/admin/cities/delete-selected/{city_id}","AdminController@deleteCitySelected")->middleware('permission:delete.cities');
	

	Route::delete("/admin/delete-agent-manager-user/{agent_id}","AdminController@deleteAgentManager")->middleware('permission:delete.cities');
	Route::delete("/admin/delete-agent-manager-user-selected/{city_id}","AdminController@deleteAgentManagerSelected")->middleware('permission:delete.cities');
	//Manage cities end here	
        
         Route::post("/admin/update-star-user-documents/{user_id}","AdminController@updateStarUserDocumentInfo")->middleware('permission:update.star-user');
         Route::any("/admin/star-user-documents/{file_id}","AdminController@StarUserDocument")->middleware('permission:update.star-user');
         
         Route::post("/admin/change_status", "AdminController@changeStarUserStatus");
         Route::get("/admin/agent-managers-users","AdminController@listAgentManagerUsers")->middleware('permission:view.agent-manager-users');
         Route::get("/admin/agent-manager-users-data","AdminController@listAgentManagerUsersData")->middleware('permission:view.agent-manager-users');
            Route::get("/admin/create-agent-manager-user","AdminController@createAgentManagerUsers")->middleware('permission:create.agent-manager-user');
         Route::post("/admin/create-agent-manager-user","AdminController@createAgentManagerUsers")->middleware('permission:create.agent-manager-user');
         Route::get("/admin/update-agent-manager-user/{user_id}","AdminController@updateAgentManagerUsers")->middleware('permission:update.agent-manager-user');
         Route::post("/admin/update-agent-manager-user/{user_id}","AdminController@updateAgentManagerUsers")->middleware('permission:update.agent-manager-user');

         Route::post("/admin/update-agent-manager-user-email/{user_id}","AdminController@updateAgentManagerUserEmail")->middleware('permission:pdate.agent-manager-user');
         Route::post("/admin/update-agent-manager-user-password/{user_id}","AdminController@updateAgentManagerUserPassword")->middleware('permission:pdate.agent-manager-user');
     
        
         Route::post("/admin/update-star-user-services/{user_id}","AdminController@updateStarUserServicesInfo")->middleware('permission:update.star-user');
         Route::post("/admin/update-star-user-spoken-language/{user_id}","AdminController@updateStarUserSpokenlanguage")->middleware('permission:update.star-user');
         
         
         //Manage Spoken languages
	Route::get("/admin/preferred-language/list","AdminController@listSpokenlanguage")->middleware('permission:view.manage-spokenlanguage');
	Route::get("/admin/preferred-language-data/list","AdminController@listSpokenlanguageData")->middleware('permission:view.manage-spokenlanguage');
	Route::get("/admin/preferred-language/update-language/{country_id}/{locale}","AdminController@updateSpokenLang")->middleware('permission:update.spokenlanguage');
	Route::post("/admin/preferred-language/update-language/{country_id}/{locale}","AdminController@updateSpokenLang")->middleware('permission:update.spokenlanguage');
	
	Route::get("/admin/preferred-language/update/{country_id}","AdminController@updateSpokenlanguage")->middleware('permission:update.spokenlanguage');
	Route::post("/admin/preferred-language/update/{country_id}","AdminController@updateSpokenlanguage")->middleware('permission:update.spokenlanguage');
	
	Route::get("/admin/preferred-language/create","AdminController@createSpokenlanguage")->middleware('permission:create.spokenlanguage');
	Route::post("/admin/preferred-language/create","AdminController@createSpokenlanguage")->middleware('permission:create.spokenlanguage');
	Route::delete("/admin/preferred-language/delete/{country_id}","AdminController@deleteSpokenlanguage")->middleware('permission:delete.spokenlanguage');
	Route::delete("/admin/preferred-language/delete-selected/{country_id}","AdminController@deleteSpokenlanguageSelected")->middleware('permission:delete.spokenlanguage');
	Route::post("/admin/update-star-user-image/{user_id}","AdminController@uploadUserImage")->middleware('permission:update.star-user');
	Route::get("/admin/approve-star-user-image/{user_id}","AdminController@approveUserImage")->middleware('permission:update.star-user');
	Route::post("/admin/update-star-payment-methods/{user_id}","AdminController@updateStarPaymentMethods")->middleware('permission:update.star-user');
	//Manage payments routes
        Route::get("/admin/create-user-payment","AdminController@createUserPaymentRecived")->middleware('permission:create.users-payments');
        Route::post("/admin/create-user-payment","AdminController@createUserPaymentRecived")->middleware('permission:create.users-payments');
        Route::get("/admin/users-payments/list","AdminController@userPaymentRecived")->middleware('permission:view.manage-users-payments');
	Route::get("/admin/users-payments/list-data","AdminController@userPaymentRecivedData")->middleware('permission:view.manage-users-payments');
	Route::get("/admin/getAllStarAgents","AdminController@getAllStarAgents")->middleware('permission:view.manage-users-payments');
	
	Route::delete("/admin/delete-user-payment/{payment_id}","AdminController@deleteUserPayment")->middleware('permission:delete.users-payments');
        
        Route::get("/admin/getAllStarMateUsers","AdminController@getAllStarMateUsers")->middleware('permission:view.send-notification');
        Route::get("/admin/send-notification-to-user","AdminController@sendNotificationtoUser")->middleware('permission:view.send-notification');
        Route::post("/admin/send-notification-to-user","AdminController@sendNotificationtoUser")->middleware('permission:view.send-notification');
       
});      
