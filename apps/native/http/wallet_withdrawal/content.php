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

if (empty($cl["is_logged"])) {
	cl_redirect("guest");
}

else {
	$cl["page_title"] = cl_translate("Withdraw money");
	$cl["page_desc"]  = $cl["config"]["description"];
	$cl["page_kw"]    = $cl["config"]["keywords"];
	$cl["pn"]         = "wallet_withdrawal";
	$cl["sbr"]        = true;
	$cl["sbl"]        = true;

	$cl["withdrawal_gws"] = explode(",", $cl["config"]["withdrawal_payment_methods"]);

	$cl["withdrawal_gws"] = array_map(function($pg_name) {
		return str_replace(['\n', "\n", "\r", '\r'], "", $pg_name);
	}, $cl["withdrawal_gws"]);

	if (is_array($cl["withdrawal_gws"]) != true) {
		$cl["withdrawal_gws"] = array("");
	}

	$cl["withdrawal_awaiting"] = cl_is_withdrawal_awaiting($me["id"]);

	$cl["http_res"] = cl_template("wallet_withdrawal/content");
}