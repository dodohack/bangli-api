<?php
/**
 * Backend admin routes for bangli-admin-spa.
 */

$router->group(['namespace'  => '\App\Http\Controllers'], function () use ($router) {
    $router->post('/admin/register', 'AuthController@postRegister');
    $router->get('/admin/login',     'AuthController@login');
});

$router->group(['prefix'     => 'admin',
    'namespace'  => '\App\Http\Controllers\Admin'], function () use ($router) {
    /* Beacon: /admin/ping?key=domain_key */
    $router->get('/ping', 'PingController@handle');
    $router->post('/ping', 'PingController@handle');
});


/****************************************************************************
 * Basic routes for users can use dashboard
 ***************************************************************************/
$router->group([/*'middleware' => 'permission:use_dashboard',*/
    'prefix'     => 'admin',
    'namespace'  => '\App\Http\Controllers\Admin'], function () use ($router) {

    /************************************************************************
     * Preload commonly used data to client, such as authors, editors,
     * categories, tags etc.
     ************************************************************************/
    $router->get('/cms/attributes',     'CmsController@getAttributes');

    /* System level attributes */
    $router->get('/sys/attributes',     'SysController@getAttributes');

    /* Get list of post states */
    $router->get('/posts/states',    'PostController@getStates');


    /************************************************************************
     * Extra authentication is needed for getting/putting user info(if the
     * user is not me).
     ***********************************************************************/
    /* API /user_roles */
    $router->get('/users/roles',         'UserController@getRoles');
    /* API: /users?page=<num>&role=<string>&role_id=<id>&per_page=<num> */
    $router->get('/users',                'UserController@getUsers');
    /* Update a list of users */
    $router->put('/users/batch',          'UserController@putUsers');
    $router->delete('/users/batch',       'UserController@deleteUsers');
    /* API: /users/{uuid} */
    $router->get('/users/{uuid}',         'UserController@getUser');
    /* Create user */
    $router->post('/users',               'UserController@postUser');
    /* Update a user */
    $router->put('/users/{uuid}',         'UserController@putUser');
    /* Delete a user */
    $router->delete('/users/{uuid}',      'UserController@deleteUser');


    /* Typeahead search, API: /post/search?query=<string> */
    $router->get('/search/post',       'PostListController@search');
    /* Typeahead search, API: /search/product?query=<string> */
    $router->get('/search/product',    'ProductController@search');

    /* NOTE: Some operation needs extra authentication */
    /* Get list of attachments */
    $router->get('/attachments',          'AttachmentController@getAttachments');
    /* Modify a list of attachments */
    $router->put('/attachments/batch',    'AttachmentController@putAttachments');
    /* Upload a list of attachments */
    $router->post('/attachments/batch',   'AttachmentController@postAttachments');
    $router->delete('/attachments/batch', 'AttachmentController@dealAttachments');
    /* Get a attachment */
    $router->get('/attachments/{id}',     'AttachmentController@getAttachment');
    /* Create a new attachment */
    $router->post('/attachments',         'AttachmentController@postAttachment');
    /* Update a attachment */
    $router->put('/attachments/{id}',     'AttachmentController@putAttachment');
    /* Delete a attachment */
    $router->delete('/attachments/{id}',  'AttachmentController@deleteAttachment');

    /* API for froala image manager */
    /* Get froala image manager style list of images */
    //$router->get('/froala-images',        'AttachmentController@getFroalaImages');
});

/****************************************************************************
 * Author and above routes, route prefix: /admin/cms
 ****************************************************************************/
$router->group([/*'middleware' => 'permission:edit_own_post',*/
    'prefix'     => 'admin/cms',
    'namespace'  => '\App\Http\Controllers\Admin'], function () use ($router) {

    /* API: /posts?page=<num>?state=<string>&author=<num>&editor=<num>&per_page=<num> */
    /* Retrieve a list of posts */
    $router->get('/posts',             'PostController@getPosts');
    /* Update a list of posts */
    $router->put('/posts/batch',       'PostController@putPosts');
    /* Delete a list of posts */
    $router->delete('/posts/batch',    'PostController@deletePosts');
    /* Retrieve a specific post */
    $router->get('/posts/{id}',   'PostController@getPost');
    /* Create a new post */
    $router->post('/posts',       'PostController@postPost');
    /* Update a specific post */
    $router->put('/posts/{id}',    'PostController@putPost');
    /* Delete a specific post */
    $router->delete('/posts/{id}', 'PostController@deletePost');

    /* Get a list of categories */
    $router->get('/categories',         'CmsCatController@getCategories');
    /* Get a category */
    $router->get('/categories/{id}',    'CmsCatController@getCategory');
    /* Other put/post/delete method about categories is in other group
     * which requires higher permission */

    /* Get a list of post tags */
    $router->get('/tags',               'CmsTagController@getTags');
    /* Get a tag */
    $router->get('/tags/{id}',          'CmsTagController@getTag');
    /* Other put/post/delete method about tags is in other group
     * which requires higher permission */

    /* Get a cut down version of topics for posts */
    $router->get('/topic_cats',  'PostController@getTopicCats');
    /* Topic states */
    $router->get('/topics/states', 'TopicController@getStates');
});


/****************************************************************************
 * Topic/Page/Deal related routes - Role: editor, shop_manager, administrator
 ****************************************************************************/
$router->group([/*'middleware' => ['role:administrator|editor'],*/
    'prefix'     => 'admin/cms',
    'namespace'  => '\App\Http\Controllers\Admin'], function () use ($router) {

    /* Update a list of categories */
    $router->put('/categories/batch',      'CmsCatController@putCategories');
    /* Delete a list of categories */
    $router->delete('/categories/batch',   'CmsCatController@deleteCategories');
    /* Create a new category */
    $router->post('/categories',           'CmsCatController@postCategory');
    /* Update a category */
    $router->put('/categories/{id}',       'CmsCatController@putCategory');
    /* Delete a category */
    $router->delete('/categories/{id}',    'CmsCatController@deleteCategory');

    /* Update a list of tags */
    $router->put('/tags/batch',       'CmsTagController@putTags');
    /* Delete a list of tags */
    $router->delete('/tags/batch',    'CmsTagController@deleteTags');
    /* Create a new tag */
    $router->post('/tags',            'CmsTagController@postTag');
    /* Update a tag */
    $router->put('/tags/{id}',        'CmsTagController@putTag');
    /* Delete a tag */
    $router->delete('/tags/{id}',     'CmsTagController@deleteTag');

    /* API: /topics?page=<num>?status=<string>&editor=<num>&per_page=<num> */
    /* Get list of topics */
    $router->get('/topics',          'TopicController@getTopics');
    /* Update a list of topics */
    $router->put('/topics/batch',    'TopicController@putTopics');
    $router->delete('/topics/batch', 'TopicController@deleteTopics');
    /* Get a topic */
    $router->get('/topics/{id}',     'TopicController@getTopic');
    /* Create a new topic */
    $router->post('/topics',        'TopicController@postTopic');
    /* Update a topic */
    $router->put('/topics/{id}',    'TopicController@putTopic');
    /* Delete a topic */
    $router->delete('/topics/{id}', 'TopicController@deleteTopic');

    /* Get list of pages */
    $router->get('/pages',          'PageController@getPages');
    /* Update a list of pages */
    $router->put('/pages/batch',    'PageController@putPages');
    $router->delete('/pages/batch', 'PageController@deletePages');
    /* Get a page */
    $router->get('/pages/{id}',      'PageController@getPage');
    /* Create a new page */
    $router->post('/pages',         'PageController@postPage');
    /* Update a page */
    $router->put('/pages/{id}',     'PageController@putPage');
    /* Delete a page */
    $router->delete('/pages/{id}',  'PageController@deletePage');

    /* Get list of deals */
    $router->get('/deals',          'DealController@getDeals');
    /* Update a list of deals */
    $router->put('/deals/batch',    'DealController@putDeals');
    $router->delete('/deals/batch', 'DealController@deleteDeals');
    /* Get a deal */
    $router->get('/deals/{id}',      'DealController@getDeal');
    /* Create a new deal*/
    $router->post('/deals',         'DealController@postDeal');
    /* Update a deal */
    $router->put('/deals/{id}',     'DealController@putDeal');
    /* Delete a deal */
    $router->delete('/deals/{id}',  'DealController@deleteDeal');
});



/****************************************************************************
 * Comment/Attachment related routes - Role: editor, shop_manager, administrator
 ****************************************************************************/
$router->group([/*'middleware' => ['role:administrator|editor'],*/
    'prefix'     => 'admin',
    'namespace'  => '\App\Http\Controllers\Admin'], function () use ($router) {

    /* Get list of comments */
    $router->get('/comments',          'CommentController@getComments');
    /* Update a list of comments */
    $router->put('/comments/batch',    'CommentController@putComments');
    $router->delete('/comments/batch', 'CommentController@dealComments');
    /* Get a comment */
    $router->get('/comments/{id}',     'CommentController@getComment');
    /* Create a new comment */
    $router->post('/comments',         'CommentController@postComment');
    /* Update a comment */
    $router->put('/comments/{id}',     'CommentController@putComment');
    /* Delete a comment */
    $router->delete('/comments/{id}',  'CommentController@deleteComment');

});

/****************************************************************************
 * Higher level settings, Role: administrator
 ****************************************************************************/
$router->group([/*'middleware' => ['role:administrator'],*/
    'prefix'     => 'admin',
    'namespace'  => '\App\Http\Controllers\Admin'], function () use ($router) {

    /* Get a list of locations */
    $router->get('/locations',             'LocationController@getLocations');
    /* Get a location */
    $router->get('/locations/{id}',        'LocationController@getLocation');
    /* Update a list of locations */
    $router->put('/locations/batch',       'LocationController@putLocations');
    /* Delete a list of locations */
    $router->delete('/locations/batch',    'LocationController@deleteLocations');
    /* Create a new location */
    $router->post('/locations',            'LocationController@postLocation');
    /* Update a location */
    $router->put('/locations/{id}',        'LocationController@putLocation');
    /* Delete a location */
    $router->delete('/locations/{id}',     'LocationController@deleteLocation');

    /* Get a list of advertises */
    $router->get('/advertises',            'AdvertiseController@getAdvertises');
    /* Get a advertise */
    $router->get('/advertises/{id}',       'AdvertiseController@getAdvertise');
    /* Update a list of advertises */
    $router->put('/advertises/batch',      'AdvertiseController@putAdvertises');
    /* Delete a list of advertises */
    $router->delete('/advertises/batch',   'AdvertiseController@deleteAdvertises');
    /* Create a new advertise */
    $router->post('/advertises',           'AdvertiseController@postAdvertise');
    /* Update a advertise */
    $router->put('/advertises/{id}',       'AdvertiseController@putAdvertise');
    /* Delete a advertise */
    $router->delete('/advertises/{id}',    'AdvertiseController@deleteAdvertise');

    /**************************************************************************
     * Frontend menu settings
     *************************************************************************/

    /* Get a list of all frontend menus */
    $router->get('/fe_menus',              'FeMenuController@getFeMenus');
    /* Get a frontend menu */
    $router->get('/fe_menus/{id}',         'FeMenuController@getFeMenu');
    /* Update a list of frontend menus */
    $router->put('/fe_menus/batch',        'FeMenuController@putFeMenus');
    /* Delete a list of frontend menus */
    $router->delete('/fe_menus/batch',     'FeMenuController@deleteFeMenus');
    /* Create a new frontend menu */
    $router->post('/fe_menus',             'FeMenuController@postFeMenu');
    /* Update a frontend menu */
    $router->put('/fe_menus/{id}',         'FeMenuController@putFeMenu');
    /* Delete a frontend menu */
    $router->delete('/fe_menus/{id}',      'FeMenuController@deleteFeMenu');
});
