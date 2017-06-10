<?php

class Utils {
	public static function random($size = 16) {
		return bin2hex(openssl_random_pseudo_bytes($size));
	}

	public static function mail($to, $subject, $message, $buttonURL) {
		//$headers = 'From: upld@joeygallegos.com' . "\r\n" . 'Reply-To: noreply@joeygallegos.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

		if (!isset($buttonURL)) {
			$buttonURL = "http://upld.joeygallegos.com/";
		}

		$content = "<table width=\"100%\" height=\"100%\"><tbody><tr valign=\"center\"><td align=\"center\" style=\"display:block\">";
		
		$content .= "<a href=\"http://upld.joeygallegos.com/\" style=\"display:block;text-decoration:none;margin:50px 0px;width:36px;min-height:36px;line-height:36px;text-align:center;color:#fff;font-size:18px;font-weight:600;background-color:#5890ff;border-radius:18px\" target=\"_blank\">j</a>";
		
		$content .= "<p style=\"display:block;text-align:left;color:#b6b6b6;width:100%;max-width:470px;line-height:30px;font-size:16px;margin-bottom:60px\"><strong style=\"color:#5f5f5f;font-weight:600\">Dear Human,</strong><br><br>" . $message . "<br><br><a href=\"" . $buttonURL . "\" style=\"display:inline-block;padding:20px 0px;margin-bottom:30px;border-radius:3px;background-color:transparent;border:1px solid #5890ff;font-size:16px;text-align:center;font-weight:500;text-decoration:none;color:#5890ff;width:100%;max-width:470px\" target=\"_blank\">View on UPLD</a>";
		
		$content .= "</td></tr></tbody></table>";

		//mail($to, $subject, $content, $headers);

		$result = $mgClient->sendMessage("$domain",
			array(
				'from' => 'Mailgun Sandbox <postmaster@sandbox2dbdc5bf677240bebd16ccd00e2cc2a9.mailgun.org>',
				'to' => 'Joey <joey@joeygallegos.com>',
				'subject' => 'UPLD',
				'text'    => $content
			)
		);
	}
}