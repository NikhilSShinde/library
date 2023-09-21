<?php

Route::group(array('module' => 'RatingReviewController', 'namespace' => 'App\PiplModules\ratingreview\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    Route::get("/admin/rating-review/tags-list", "RatingReviewController@ratingTagsList")->middleware('permission:view.rating-review');
    Route::get("/admin/rating-tags-data", "RatingReviewController@ratingTagsData");
    
    Route::get("/admin/rating-review/edit-tags/{rating_que_tag_id}/{locale?}", "RatingReviewController@editRatingTags");
    Route::post("/admin/rating-review/edit-tags/{rating_que_tag_id}/{locale?}", "RatingReviewController@editRatingTags");
    
    Route::get("/admin/rating-review/create-tags", "RatingReviewController@createRatingTags");
    Route::post("/admin/rating-review/create-tags", "RatingReviewController@createRatingTags");
    
    
    
    Route::get("/admin/rating-review/list", "RatingReviewController@index")->middleware('permission:view.rating-review');
    Route::get("/admin/rating-review/list/{user_id?}", "RatingReviewController@index")->middleware('permission:view.rating-review');
    Route::get("/admin/rating-review-data", "RatingReviewController@getRatingData");
    Route::get("/admin/rating-review-data/{user_id?}", "RatingReviewController@getRatingData");
    Route::get("/admin/rating-review/view/{rating_id}/{user_id}", "RatingReviewController@getRatingAndReviewDetails");
    
    Route::get("/admin/rating-review/edit/{rating_id}", "RatingReviewController@editReviewRating");
    Route::post("/admin/rating-review/edit/{rating_id}", "RatingReviewController@editReviewRating");
    
    Route::get("/admin/rating-review/edit/{rating_id}/{user_id?}", "RatingReviewController@editReviewRating");
    Route::post("/admin/rating-review/edit/{rating_id}/{user_id?}", "RatingReviewController@editReviewRating");
    
    Route::post("/admin/rating/get-tags", "RatingReviewController@getRatingTags");
    
    Route::delete("/admin/rating-review-tag/delete-selected/{tag_id}", "RatingReviewController@deleteSelectedTags");
    Route::delete("/admin/rating-review/delete-selected/{rating_id}", "RatingReviewController@deleteSelectedReview");
});
