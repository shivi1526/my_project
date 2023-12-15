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

if (empty($cl['is_logged'])) {
	$data         = array(
		'code'    => 401,
		'data'    => array(),
		'message' => 'Unauthorized Access'
	);
}
else {
	require_once(cl_full_path("core/apps/bookmarks/app_ctrl.php"));

    $offset    = fetch_or_get($_GET['offset'], null);
    $offset    = (is_posnum($offset)) ? $offset : null;
    $limit     = fetch_or_get($_GET['page_size'], 15);
    $limit     = (is_posnum($limit)) ? $limit : null;
    $bookmarks = cl_get_bookmarks($me['id'], $limit, $offset);

    if (not_empty($bookmarks)) {
        $data['code']    = 200;
        $data['message'] = "Bookmarks fetched successfully";
        $data['data']    = $bookmarks;
    }
    else {
    	$data['code']    = 204;
        $data['message'] = "No data found";
        $data['data']    = array();
    }
}