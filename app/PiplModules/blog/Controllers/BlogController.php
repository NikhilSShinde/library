<?php

namespace App\PiplModules\blog\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use Datatables;
use App\PiplModules\blog\Models\Post;
use App\PiplModules\blog\Models\PostCategory;
use App\PiplModules\blog\Models\PostComment;
use App\PiplModules\blog\Models\Tag;
use Mail;
use Image;

class BlogController extends Controller {

    private $thumbnail_size = array("width" => 50, "height" => 50);

    public function index() {

        $all_posts = Post::translatedIn(\App::getLocale())->get();

        return view("blog::list", array("posts" => $all_posts));
    }

    public function getBlogData() {

        $all_posts = Post::translatedIn(\App::getLocale())->get();

        return Datatables::of($all_posts)
                        ->addColumn("category", function($post) {
                            if ($post->category) {
                                return $post->category->name;
                            } else {
                                return "-";
                            }
                        })
                        ->addColumn('Language', function($post) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language.='<li class="dropdown-item"> <a href="blog-post/' . $post->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->make(true);
    }

    public function deleteBlogPost($post_id) {
        $post = Post::find($post_id);

        if ($post) {

            // remove photo and attachments
            $photo = $post->post_image;
            $attachments = $post->post_attachments;

            if ($photo) {

                $this->removeBlogFileFromStrorage($photo);
            }

            foreach ($attachments as $attachment) {
                $this->removeBlogFileFromStrorage($attachment['original_name']);
            }

            $post->delete();

            return redirect("admin/blog")->with('status', 'Blog post deleted successfully!');
        } else {
            return redirect('admin/blog');
        }
    }

    public function deleteSelectedBlogPost($post_id) {
        $post = Post::find($post_id);

        if ($post) {
            // remove photo and attachments
            $photo = $post->post_image;
            $attachments = $post->post_attachments;

            if ($photo) {

                $this->removeBlogFileFromStrorage($photo);
            }

            foreach ($attachments as $attachment) {
                $this->removeBlogFileFromStrorage($attachment['original_name']);
            }

            $post->delete();

            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function getAllTags() {
        $arr_tags = Tag::get(["name"]);
        $return_tags = array();
        foreach ($arr_tags as $tag) {
            $return_tags[] = $tag->name;
        }
        return $return_tags;
    }

    public function updateBlogPost(Request $request, $post_id, $locale = "") {
        $post = Post::find($post_id);
        if ($post) {
            $translated_post = $post->translateOrNew($locale);

            if ($request->method() == "GET") {
                $existing_categories = PostCategory::withTranslation()->get();
                $tree = $this->getCategoryTree($existing_categories->toTree(), '&nbsp;&nbsp;');

                $db_post_tags = $post->tags;

                $arr_post_tags = array();
                foreach ($db_post_tags as $tag) {
                    $arr_post_tags [] = $tag->name;
                }

                $all_db_tags = $this->getAllTags();

                if (isset($locale) && $locale != 'en' && $locale != '') {

                    return view('blog::update-language', array('post' => $translated_post, 'ori_post' => $post, 'post_tags' => implode(",", $arr_post_tags))
                    );
                } else {
                    return view('blog::update', array('post' => $translated_post, 'tree' => $tree, 'locale' => $locale, 'category' => $post->post_category_id,
                        'ori_post' => $post,
                        'post_tags' => implode(",", $arr_post_tags),
                        'available_tags' => json_encode($all_db_tags)
                            )
                    );
                }
            } else {

                // do validation and update the faq

                $data = $request->all();

                if (!empty($request->url))
                    $data['url'] = str_slug($request->url);

                if ($locale != 'en' && $locale != '') {
                    $validator_array = array(
                        'title' => 'required',
                        'short_description' => 'required',
                        'description' => 'required',
                        'seo_title' => 'required',
                        'seo_keywords' => 'required',
                        'seo_description' => 'required'
                    );
                } else
                    $validator_array = array(
                        'title' => 'required',
                        'short_description' => 'required',
                        'photo' => 'sometimes|image',
                        'seo_title' => 'required',
                        'seo_keywords' => 'required',
                        'seo_description' => 'required'
                    );

                if ($locale == "") {
                    $validator_array['url'] = 'required|unique:posts,post_url,' . $post->id;
                }

                $validate_response = Validator::make($data, $validator_array);
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {

                    if ($locale != "" && $locale != 'en') {
                        $translated_post->post_id = $post_id;
                        $translated_post->locale = $locale;
                        $translated_post->title = $request->title;
                        $translated_post->short_description = $request->short_description;
                        $translated_post->seo_title = $request->seo_title;
                        $translated_post->seo_keywords = $request->seo_keywords;
                        $translated_post->seo_description = $request->seo_description;
                        $translated_post->save();
                    } else {
                        $post->post_url = str_slug($request->url);

                        if ($request->category) {
                            $post->post_category_id = $request->category;
                        }

                        $post->allow_comments = $request->allow_comments;
                        $post->allow_attachments_in_comments = $request->allow_attachments_in_comments;
                        $post->post_status = $request->post_status;

                        // check if photo available

                        if (!is_dir(storage_path('app/public/blog/thumbnails/'))) {

                            Storage::makeDirectory('public/blog/thumbnails/');
                        }

                        if ($request->hasFile('photo')) {
                            $extension = $request->file('photo')->getClientOriginalExtension();

                            $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                            Storage::put('public/blog/' . $new_file_name, file_get_contents($request->file('photo')->getRealPath()));

                            if ($post->post_image) {
                                // delete previous file
                                $this->removeBlogFileFromStrorage($post->post_image);
                            }


                            // make thumbnail

                            $thumbnail = Image::make(storage_path('app/public/blog/' . $new_file_name));

                            $thumbnail->resize($this->thumbnail_size["width"], $this->thumbnail_size["height"]);

                            $thumbnail->save(storage_path('app/public/blog/thumbnails/' . $new_file_name));


                            $post->post_image = $new_file_name;
                        }

                        // check attachments available
                        $attachments = array();
                        if ($request->hasFile('attachments')) {
                            $uploaded_files = $request->file('attachments');

                            foreach ($uploaded_files as $uploaded_file) {
                                $extension = $uploaded_file->getClientOriginalExtension();

                                $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                                Storage::put('public/blog/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));

                                $attachments[] = array("original_name" => $new_file_name, "display_name" => $uploaded_file->getClientOriginalName());
                            }
                        }

                        if (count($attachments)) {
                            $prev_attachments = $post->post_attachments;

                            $attachments = array_merge($prev_attachments, $attachments);

                            $post->post_attachments = $attachments;
                        }



                        $post->save();
                    }


                    $translated_post->title = $request->title;
                    $translated_post->short_description = $request->short_description;
                    $translated_post->description = $request->description;
                    $translated_post->seo_title = $request->seo_title;
                    $translated_post->seo_keywords = $request->seo_keywords;
                    $translated_post->seo_description = $request->seo_description;
                    $translated_post->save();


                    // check for tags
                    $existing_tags = $post->tags;
                    if (!empty($request->tags)) {

                        $tags = explode(",", $request->tags);


                        $detach_tags = array();
                        $detach_tag_ids = array();

                        foreach ($existing_tags as $tag) {
                            $detach_tags [] = $tag->name;

                            if (!in_array(strtolower($tag->name), $tags)) {
                                $detach_tag_ids [] = $tag->id;
                            }
                        }



                        $arr_tags = array();

                        foreach ($tags as $tag) {
                            if (strlen(trim($tag))) {
                                $created_tag = Tag::firstOrCreate(array('name' => strtolower(trim($tag)), 'slug' => str_slug($tag)));

                                if (!in_array(strtolower($tag), $detach_tags)) {
                                    $arr_tags[] = $created_tag->id;
                                }
                            }
                        }

                        if (count($detach_tag_ids) > 0) {
                            $post->tags()->detach($detach_tag_ids);
                        }

                        if (count($arr_tags) > 0) {
                            $post->tags()->attach($arr_tags);
                        }
                    } else {
                        // check if respective post has previous tags, if found detach all
                        if ($existing_tags) {
                            foreach ($existing_tags as $tag) {
                                $detach_tag_ids [] = $tag->id;
                            }

                            $post->tags()->detach($detach_tag_ids);
                        }
                    }

                    return redirect('admin/blog')->with('status', 'Blog post updated successfully!');
                }
            }
        } else {
            return redirect('admin/blog');
        }
    }

    public function createBlogPost(Request $request) {

        if ($request->method() == "GET") {

            $existing_categories = PostCategory::withTranslation()->get();

            $tree = $this->getCategoryTree($existing_categories->toTree(), '&nbsp;&nbsp;');

            return view("blog::create", array('tree' => $tree));
        } else {
            $data = $request->all();

            if (!empty($request->url))
                $data['url'] = str_slug($request->url);

            $validate_response = Validator::make($data, array(
                        'title' => 'required',
                        'short_description' => 'required',
                        'description' => 'required',
                        'url' => 'required|unique:posts,post_url',
                        'photo' => 'sometimes|image',
                        'allow_comments' => 'required',
                        'seo_title' => 'required',
                        'seo_keywords' => 'required',
                        'seo_description' => 'required'
                            )
            );

            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {

                $post_attributes = array(
                    'created_by' => Auth::user()->id,
                    'post_url' => str_slug($request->url),
                    'allow_comments' => $request->allow_comments,
                    'allow_attachments_in_comments' => $request->allow_attachments_in_comments,
                    'post_status' => intval($request->post_status)
                );

                if ($request->category) {
                    $post_attributes['post_category_id'] = $request->category;
                }

                // check if photo available

                if ($request->hasFile('photo')) {
                    $extension = $request->file('photo')->getClientOriginalExtension();

                    $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                    Storage::put('public/blog/' . $new_file_name, file_get_contents($request->file('photo')->getRealPath()));

                    if (!is_dir(storage_path('app/public/blog/thumbnails/'))) {
                        Storage::makeDirectory('public/blog/thumbnails/');
                    }

                    // make thumbnail

                    $thumbnail = Image::make(storage_path('app/public/blog/' . $new_file_name));

                    $thumbnail->resize($this->thumbnail_size["width"], $this->thumbnail_size["height"]);

                    $thumbnail->save(storage_path('app/public/blog/thumbnails/' . $new_file_name));


                    $post_attributes['post_image'] = $new_file_name;
                }

                // check attachments available
                $attachments = array();
                if ($request->hasFile('attachments')) {
                    $uploaded_files = $request->file('attachments');

                    foreach ($uploaded_files as $uploaded_file) {
                        $extension = $uploaded_file->getClientOriginalExtension();

                        $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                        Storage::put('public/blog/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));

                        $attachments[] = array("original_name" => $new_file_name, "display_name" => $uploaded_file->getClientOriginalName());
                    }
                }

                $post_attributes['post_attachments'] = $attachments;

                $created_post = Post::create($post_attributes);




                $translated_post = $created_post->translateOrNew(\App::getLocale());
                $translated_post->title = $request->title;
                $translated_post->short_description = $request->short_description;
                $translated_post->description = $request->description;
                $translated_post->seo_title = $request->seo_title;
                $translated_post->seo_keywords = $request->seo_keywords;
                $translated_post->seo_description = $request->seo_description;
                $translated_post->locale = \App:: getLocale();
                $translated_post->post_id = $created_post->id;
                $translated_post->save();


                // check for tags
                if (!empty($request->tags)) {

                    $tags = explode(",", $request->tags);
                    $arr_tags = array();

                    foreach ($tags as $tag) {
                        $created_tag = Tag::firstOrCreate(array('name' => strtolower(trim($tag)), 'slug' => str_slug($tag)));

                        $arr_tags[] = $created_tag->id;
                    }

                    if (count($arr_tags) > 0) {
                        $created_post->tags()->attach($arr_tags);
                    }
                }


                return redirect("admin/blog")->with('status', 'Post created successfully!');
            }
        }
    }

    public function removePostPhoto($post_id) {
        $post = Post::find($post_id);

        if ($post) {
            $post_image = $post->post_image;

            if ($post_image) {
                if ($this->removeBlogFileFromStrorage($post_image)) {
                    $post->post_image = "";
                    $post->save();
                }
            }
        }

        echo '<script>window.opener.alert("File deleted successfully!");window.opener.location.reload();window.opener = window.self;window.close();</script>';
    }

    public function removePostAttachment($post_id, $attachment_id) {
        $post = Post::find($post_id);

        if ($post) {
            $post_attachments = $post->post_attachments;

            $attachment_to_remove = $post_attachments[$attachment_id];

            if ($attachment_to_remove) {
                if ($this->removeBlogFileFromStrorage($attachment_to_remove['original_name'])) {
                    array_forget($post_attachments, '' . $attachment_id);

                    $new_array = array();

                    foreach ($post_attachments as $attachment) {
                        $new_array[] = $attachment;
                    }

                    $post->post_attachments = $new_array;

                    $post->save();
                }
            }
        }

        echo '<script>window.opener.alert("File deleted successfully!");window.opener.location.reload();window.opener = window.self;window.close();</script>';
    }

    private function removeBlogFileFromStrorage($file_name) {
        if (Storage::exists('public/blog/' . $file_name)) {
            Storage::delete('public/blog/' . $file_name);

            if (Storage::exists('public/blog/thumbnails/' . $file_name)) {
                Storage::delete('public/blog/thumbnails/' . $file_name);
            }

            return true;
        }

        return false;
    }

    public function listBlogCategories() {

        return view('blog::list-categories');
    }

    public function listBlogCategoriesData() {

        $all_categories = PostCategory::translatedIn(\App::getLocale())->get();

        return Datatables::of($all_categories)
                        ->addColumn("parent", function($category) {
                            if ($category->parentCat) {
                                return $category->parentCat->translate()->name;
                            } else {
                                return "-";
                            }
                        })
                        ->addColumn('Language', function($category) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language.='<li class="dropdown-item"> <a href="blog-category/' . $category->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->make(true);
    }

    public function createBlogCategories(Request $request) {
        if ($request->method() == "GET") {
            $existing_categories = PostCategory::withTranslation()->get();
            $tree = $this->getCategoryTree($existing_categories->toTree(), '&nbsp;&nbsp;');
            return view("blog::create-category", array('categories' => $existing_categories, "tree" => $tree));
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required',
                        'slug' => 'required|unique:post_categories',
                            ), array('name.required' => 'Please enter name',
                        'slug.required' => 'Please enter url',
                        'slug.unique' => 'This url is already exists',
            ));

            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {
                $parent_cat = PostCategory::find($request->parent_id);

                $created_category = PostCategory::create(array('created_by' => Auth::user()->id, 'parent_id' => $request->parent_id, 'slug' => str_slug($request->slug)));

                $translated_category = $created_category->translateOrNew(\App::getLocale());
                $translated_category->name = $request->name;
                $translated_category->locale = \App:: getLocale();
                $translated_category->post_category_id = $created_category->id;
                $translated_category->save();

                return redirect("admin/blog-categories")->with('status', 'Category created successfully!');
            }
        }
    }

    public function updateBlogCategory(Request $request, $category_id, $locale = "") {

        $category = PostCategory::find($category_id);

        if ($category) {
            $translated_category = $category->translateOrNew($locale);

            if ($request->method() == "GET") {
                $existing_categories = PostCategory::withTranslation()->where('id', '<>', $category_id)->get();

                $tree = $this->getCategoryTree($existing_categories->toTree(), '&nbsp;&nbsp;');

                if (isset($locale) && $locale != 'en' && $locale != '') {

                    return view("blog::update-language-category", array('category' => $translated_category,'main_catgeoy_details' => $category));
                    
                } else {
                    return view("blog::update-category", array('category' => $translated_category, 'parent_id' => $category->parent_id, 'locale' => $locale, 'tree' => $tree, 'main_catgeoy_details' => $category));
                }
            } else {

                $data = $request->all();
                if ($locale != 'en' && $locale != '') {
                    $validate_response = Validator::make($data, array(
                        'name' => 'required',
                    ));
                } else{
                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                            'slug' => 'required|unique:post_categories,slug,' . str_slug($category->id),
                                ), array('name.required' => 'Please enter name',
                            'slug.required' => 'Please enter url',
                            'slug.unique' => 'This url is already exists',
                ));
                }
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    $translated_category->name = $request->name;

                    if ($locale != 'en' && $locale != '') {
                        $translated_category->post_category_id = $category->id;
                        $translated_category->locale = $locale;
                        $translated_category->name = $request->name;
                        
                    } else {
                        $parent_cat = PostCategory::find($request->parent_id);
                        $category->parent_id = $request->parent_id;
                        $category->slug = str_slug($request->slug);
                        $category->save();
                    }
                    $translated_category->save();
                    return redirect("admin/blog-categories")->with('status', 'Category updated successfully!');
                }
            }
        } else {
            return redirect('admin/blog-categories');
        }
    }

    public function deleteBlogCategory($category_id) {

        $category = PostCategory::find($category_id);
        if ($category) {
            $category->delete();
            return redirect("admin/blog-categories")->with('status', 'Category deleted successfully!');
        } else {
            return redirect('admin/blog-categories');
        }
    }

    public function deleteSelectedBlogCategory($category_id) {

        $category = PostCategory::find($category_id);
        if ($category) {
            $category->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function viewPost(Request $request, $post_url) {

        $page = Post::where('post_url', $post_url)->first();

        if ($page && $page->post_status) {

            if ($request->method() == "GET") {
                $page_information = $page->translateOrDefault(\App::getLocale());
                return view('blog::view-post', array('page' => $page, 'page_information' => $page_information));
            } else {
                // Post Comments

                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'comment' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    $comment_data = array(
                        'comment' => $request->comment,
                        'commented_by' => Auth::user()->id,
                        'post_id' => $page->id
                    );

                    $attachments = array();

                    if ($page->allow_attachments_in_comments && $request->hasFile('attachments')) {

                        $uploaded_files = $request->file('attachments');

                        foreach ($uploaded_files as $uploaded_file) {
                            $extension = $uploaded_file->getClientOriginalExtension();

                            $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                            Storage::put('public/blog/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));

                            $attachments[] = array("original_name" => $new_file_name, "display_name" => $uploaded_file->getClientOriginalName());
                        }
                    }

                    $comment_data['comment_attachments'] = $attachments;

                    $post_comment = PostComment::create($comment_data);

                    return redirect($request->url());
                }
            }
        } else {
            abort(404);
        }
    }

    public function viewPostsForTag($tag_slug) {
        $obj_tag = Tag::where('slug', $tag_slug)->first();

        if ($obj_tag) {
            $posts = $obj_tag->posts;

            $existing_categories = PostCategory::withTranslation()->get();

            $tree = $this->getCategoryTreeList($existing_categories->toTree(), '<li>', true);


            return view("blog::search-by-tag", array('posts' => $posts, 'tag' => $obj_tag, 'category_tree' => $tree));
        } else {
            abort(404);
        }
    }

    public function viewPostsForCategory($category_slug) {
        $obj_category = PostCategory::where('slug', $category_slug)->first();
        if ($obj_category) {

            $posts = $obj_category->posts;

            $existing_categories = PostCategory::withTranslation()->get();

            $tree = $this->getCategoryTreeList($existing_categories->toTree(), '<li>', true);


            return view("blog::search-by-category", array('posts' => $posts, 'category' => $obj_category, 'category_tree' => $tree));
        } else {
            abort(404);
        }
    }

    private function getCategoryTree($nodes, $prefix = "-") {
        $arr_cats = array();
        $traverse = function ($categories, $prefix) use (&$traverse, &$arr_cats ) {

            foreach ($categories as $category) {


                $arr_cats[] = new categoryTreeHolder($prefix . ' ' . $category->name, $category->id);

                $traverse($category->children, $prefix . $prefix);
            }
        };

        $traverse($nodes, $prefix);

        return $arr_cats;
    }

    public function viewBlogPosts() {
        $posts = Post::translatedIn(\App::getLocale())->where('post_status', '1')->orderBy('updated_at', 'desc')->get();

        $posts_latest = Post::translatedIn(\App::getLocale())->where('post_status', '1')->orderBy('updated_at', 'desc')->first();

        $existing_categories = PostCategory::withTranslation()->get();

        $tree = $this->getCategoryTreeList($existing_categories->toTree(), '<li>', true);

        return view('blog::posts', array('posts' => $posts, 'posts_latest' => $posts_latest, 'category_tree' => $tree));
    }

    public function searchPost($keyword) {
        $matching_posts = Post::search(array('short_description', 'title', 'description'), "%$keyword%")->where('post_status', '1')->get();

        $existing_categories = PostCategory::withTranslation()->get();

        $tree = $this->getCategoryTreeList($existing_categories->toTree(), '<li>', true);

        return view('blog::search-results', array('posts' => $matching_posts, 'category_tree' => $tree, 'keyword' => $keyword));
    }

    private function getCategoryTreeList($nodes, $prefix = "</li><li>", $include_anchor = false) {
        $arr_cats = array();
        $traverse = function ($categories, $prefix) use (&$traverse, &$arr_cats, $include_anchor) {

            foreach ($categories as $category) {

                $disp_name = $prefix . ' ' . $category->name . " (" . count($category->posts) . ")</li>";

                if ($include_anchor) {
                    $disp_name = $prefix . '<a href="' . url('/blog/categories/' . $category->slug) . '" title="Click to view posts">' . $category->name . " (" . count($category->posts) . ")</a></li>";
                }

                $arr_cats[] = new categoryTreeHolder($disp_name, $category->id, $category->slug);

                $traverse($category->children, "<ul class='subtree'><li>");
            }
        };

        $traverse($nodes, $prefix);

        return $arr_cats;
    }

}

class categoryTreeHolder {

    public $display = '';
    public $id = '';
    public $slug = '';

    public function __construct($display, $id, $slug = '') {
        $this->id = $id;
        $this->display = $display;
        $this->slug = $slug;
    }

}
