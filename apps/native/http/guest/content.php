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

if (not_empty($cl["is_logged"])) {
	cl_redirect("home");
}

require_once(cl_full_path("core/apps/guest/app_ctrl.php"));

$cl["invite_code"] = fetch_or_get($_GET["invite_code"], false);
$cl["auth_type"]   = fetch_or_get($_GET["auth"], "login");
$cl["auth_type"]   = (in_array($cl["auth_type"], array("login", "signup", "forgot_pass", "reset_pass"))) ? $cl["auth_type"] : "login";
$cl["page_title"]  = $cl["config"]["title"];
$cl["page_desc"]   = $cl["config"]["description"];
$cl["page_kw"]     = $cl["config"]["keywords"];
$cl["pn"]          = "guest";
$cl['em_code']     = ((not_empty($_GET['em_code']) && cl_verify_emcode($_GET['em_code'])) ? cl_text_secure($_GET['em_code']) : null);
$cl["sbr"]         = false;
$cl["sbl"]         = false;
$cl["slider_imgs"] = cl_get_slider_images();
$cl["invite_code_status"] = (not_empty($cl["invite_code"])) ? cl_verify_invite_code($cl["invite_code"]) : false;
$cl["http_res"] = cl_template("guest/content");
