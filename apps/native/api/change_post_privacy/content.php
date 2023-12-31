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

	$post_id  = fetch_or_get($_POST['post_id'], 0);
    $priv_wcr = fetch_or_get($_POST['privacy'], 'everyone');

    if (is_posnum($post_id)) {
        $post_data = cl_raw_post_data($post_id);

        if (not_empty($post_data) && $post_data["user_id"] == $me["id"] && in_array($priv_wcr, array("everyone", "mentioned", "followers"))) {
            cl_update_post_data($post_id, array(
                "priv_wcr" => $priv_wcr
            ));

            $data['code']    = 200;
            $data['message'] = "Post privacy changed successfully";
            $data['data']    = array();
        }
        else{
            $data['code']    = 400;
            $data['message'] = "Post ID is missing or invalid. Please check your details";
            $data['data']    = array();
        }
    }
    else{
        $data['code']    = 400;
        $data['message'] = "Post ID is missing or invalid. Please check your details";
        $data['data']    = array();
    }
}