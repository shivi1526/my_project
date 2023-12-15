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
	$post_id   = fetch_or_get($_POST['post_id'], 0);
	$post_data = cl_raw_post_data($post_id);

	if (not_empty($post_data)) {
		if (cl_has_liked($me['id'], $post_id) != true) {
            cl_db_insert(T_LIKES, array(
                'pub_id'  => $post_id,
                'user_id' => $me['id'],
                'time'    => time()
            ));

            $likes_count        = ($post_data['likes_count'] += 1);
            $data['post_likes'] = $likes_count;
            $data['message']    = "";
            $data['code']       = 200;
            $data['data']       = array("like" => true);

            cl_update_post_data($post_id, array(
                'likes_count' => $likes_count
            ));

            if ($post_data['user_id'] != $me['id']) {
                cl_notify_user(array(
                    'subject'  => 'like',
                    'user_id'  => $post_data['user_id'],
                    'entry_id' => $post_id
                ));
            }
        }
        else {

            $likes_count        = ($post_data['likes_count'] -= 1);
            $data['post_likes'] = $likes_count;
            $data['message']    = "";
            $data['code']       = 200;
            $data['data']       = array("like" => false);

            cl_update_post_data($post_id, array(
                'likes_count' => $likes_count
            ));

            cl_db_delete_item(T_LIKES, array(
            	'pub_id'  => $post_id, 
            	'user_id' => $me['id']
            ));

            cl_db_delete_item(T_NOTIFS, array(
            	'notifier_id'  => $me['id'],
            	'recipient_id' => $post_data['user_id'],
            	'subject'      => 'like',
            	'entry_id'     => $post_id
            ));
        }
	}
	else {
		$data['code']    = 400;
        $data['message'] = "Post id is missing or invalid";
    	$data['data']    = array();
	}
}