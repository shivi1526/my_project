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

function cl_admin_get_user_ads($args = array()) {
    global $cl, $me, $db;

    $args           = (is_array($args)) ? $args : array();
    $options        = array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC'
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
    $sql            =  cl_sqltepmlate('apps/cpanel/ads/sql/fetch_ads',array(
        'offset'    => $offset,
        't_ads'     => T_ADS,
        't_users'   => T_USERS,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order
    ));

    $data = array();
    $ads  = $db->rawQuery($sql);

    if (cl_queryset($ads)) {
        foreach ($ads as $ad_data) {
            $ad_data['ad_url']  = cl_link(cl_strf("ads/%d", $ad_data["id"]));
            $ad_data['cover']   = cl_get_media($ad_data['cover']);
            $ad_data['time']    = cl_time2str($ad_data['time']);
            $ad_data['views']   = cl_number($ad_data['views']);
            $ad_data['clicks']  = cl_number($ad_data['clicks']);
            $ad_data['budget']  = cl_money($ad_data['budget']);
            $ad_data['domain']  = cl_get_host($ad_data['target_url']);
            $ad_data['owner']   = array(
                'avatar'        => cl_get_media($ad_data['avatar']),
                'name'          => $ad_data['name'],
                'username'      => $ad_data['username'],
                'verified'      => $ad_data['verified'],
                'url'           => cl_link($ad_data['username'])
            );

            array_push($data, $ad_data);
        }
    }
    
    return $data;
}


