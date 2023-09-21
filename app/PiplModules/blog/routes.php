<?php

Route::group(array('module' => 'Blog', 'namespace' => 'App\PiplModules\blog\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    Route::get("/admin/blog", "BlogController@index")->middleware('permission:view.blog');
    Route::get("/admin/blog-data", "BlogController@getBlogData")->middleware('permission:view.blog');

    Route::get("/admin/blog-post/create", "BlogController@createBlogPost")->middleware('permission:create.blogPost');
    Route::post("/admin/blog-post/create", "BlogController@createBlogPost")->middleware('permission:create.blogPost');

    Route::delete("/admin/blog-post/delete/{post_id}", "BlogController@deleteBlogPost")->middleware('permission:delete.blogPost');
    Route::delete("/admin/blog-post/delete-selected/{post_id}", "BlogController@deleteSelectedBlogPost")->middleware('permission:delete.blogPost');

    Route::get("admin/blog-post/remove-attachment/{post_id}/{attachment_id}", "BlogController@removePostAttachment")->middleware('permission:update.blogPost');

    Route::get("admin/blog-post/remove-photo/{post_id}", "BlogController@removePostPhoto")->middleware('permission:update.blogPost');

    Route::get("/admin/blog-post/{post_id}/{locale?}", "BlogController@updateBlogPost")->middleware('permission:update.blogPost');
    Route::post("/admin/blog-post/{post_id}/{locale?}", "BlogController@updateBlogPost")->middleware('permission:update.blogPost');



    Route::get("/admin/blog-categories", "BlogController@listBlogCategories")->middleware('permission:view.blog-categories');
    Route::get("/admin/blog-categories-data", "BlogController@listBlogCategoriesData")->middleware('permission:view.blog-categories');
    Route::get("/admin/blog-categories/create", "BlogController@createBlogCategories")->middleware('permission:create.blog-category');
    Route::post("/admin/blog-categories/create", "BlogController@createBlogCategories")->middleware('permission:create.blog-category');

    Route::get("/admin/blog-category/{category_id}/{locale?}", "BlogController@updateBlogCategory")->middleware('permission:update.blog-category');
    Route::post("/admin/blog-category/{category_id}/{locale?}", "BlogController@updateBlogCategory")->middleware('permission:update.blog-category');

    Route::delete("/admin/delete-blog-category/{category_id}", "BlogController@deleteBlogCategory")->middleware('permission:delete.blog-category');
    Route::delete("/admin/delete-blog-selected-category/{category_id}", "BlogController@deleteSelectedBlogCategory")->middleware('permission:delete.blog-category');


    Route::get("/blog", "BlogController@viewBlogPosts");
    Route::get("/blog/api/tags", "BlogController@getAllTags");

    Route::post("/blog/search", function(\Illuminate\Http\Request $request) {

        if (empty($request->searchText))
            return redirect()->back()->with("search-error", "Please enter search value");

        return redirect('/blog/search/' . $request->searchText);
    });

    Route::get("/blog/search/{keyword}", "BlogController@searchPost");

    Route::get("/blog/tags/{tag_slug}", "BlogController@viewPostsForTag");
    Route::get("/blog/categories/{category_slug}", "BlogController@viewPostsForCategory");

    Route::get("/blog/{post_url}", "BlogController@viewPost");
    Route::post("/blog/{post_url}", "BlogController@viewPost")->middleware('auth');
});
