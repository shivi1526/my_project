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
	$swift_data = $me['swift'];

	if (not_empty($me["last_swift"]) && isset($swift_data[$me["last_swift"]])) {
        $swift_data     = cl_delete_swift($me["last_swift"]);
       
        cl_update_user_data($me["id"], array(
            "swift"      => cl_minify_js(json($swift_data, true)),
            "last_swift" => ""
        ));
    }

    
    $data['code']    = 200;
    $data['message'] = "Media deleted successfully";
    $data['data']    = array();
}