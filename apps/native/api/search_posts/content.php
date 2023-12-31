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

require_once(cl_full_path("core/apps/explore/app_ctrl.php"));

$query  = fetch_or_get($_GET['query'], null);
$offset = fetch_or_get($_GET['offset'], null);
$offset = (is_posnum($offset)) ? $offset : null;
$limit  = fetch_or_get($_GET['page_size'], null);
$limit  = (is_posnum($limit)) ? $limit: null;

if (empty($query) || is_string($query) != true || len_between($query, 1, 32) != true) {
    $data['code']    = 400;
    $data['message'] = "The search term is invalid or missing";
    $data['data']    = array();
}

else {

    $posts_ls = cl_search_posts(cl_text_secure($query), $offset, $limit);

    if (not_empty($posts_ls)) {
        $data['code']    = 200;
        $data['message'] = "Results found";
        $data['data']    = $posts_ls;
    }
    else {
        $data['code']    = 204;
        $data['message'] = "No data found";
        $data['data']    = array();
    }
}
