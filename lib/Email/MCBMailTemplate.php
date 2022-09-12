<?php
namespace OCA\AppDirect\Email;

use OC\Mail\EMailTemplate;

/**
 * This class extends the EMailTemplate class. It is configured as the email template for next cloud in the config.php file.
 * This class simply overrides every variable containing html information to change the look and feel of mails sent by next cloud.
 */
class MCBMailTemplate extends EMailTemplate {
	protected $head = <<<EOF
	<!doctype html>
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

	<head>
		<title></title>
		<!--[if !mso]><!-- -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<!--<![endif]-->
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<style type="text/css">
			#outlook a {
				padding: 0;
			}

			.ReadMsgBody {
				width: 100%;
			}

			.ExternalClass {
				width: 100%;
			}

			.ExternalClass * {
				line-height: 100%;
			}

			body {
				margin: 0;
				padding: 0;
				-webkit-text-size-adjust: 100%;
				-ms-text-size-adjust: 100%;
			}

			table, td {
				border-collapse: collapse;
				mso-table-lspace: 0pt;
				mso-table-rspace: 0pt;
			}

			img {
				border: 0;
				height: auto;
				line-height: 100%;
				outline: none;
				text-decoration: none;
				-ms-interpolation-mode: bicubic;
			}

			p {
				display: block;
				margin: 13px 0;
			}
		</style>
		<!--[if !mso]><!-->
		<style type="text/css">
			@media only screen and (max-width:480px) {
				@-ms-viewport {
					width: 320px;
				}

				@viewport {
					width: 320px;
				}
			}
		</style>
		<!--<![endif]-->
		<!--[if mso]>
		<xml>
			<o:OfficeDocumentSettings>
				<o:AllowPNG/>
				<o:PixelsPerInch>96</o:PixelsPerInch>
			</o:OfficeDocumentSettings>
		</xml>
		<![endif]-->
		<!--[if lte mso 11]>
		<style type="text/css">
			.outlook-group-fix { width:100% !important; }
		</style>
		<![endif]-->
		<style type="text/css">
			@media only screen and (min-width:480px) {
				.mj-column-per-100 {
					width: 100% !important;
					max-width: 100%;
				}

				.mj-column-per-50 {
					width: 50% !important;
					max-width: 50%;
				}

				.mj-column-per-60 {
					width: 60% !important;
					max-width: 60%;
				}

				.mj-column-per-40 {
					width: 40% !important;
					max-width: 40%;
				}

				.mj-column-per-33 {
					width: 33.333333333333336% !important;
					max-width: 33.333333333333336%;
				}

				.mj-column-per-30 {
					width: 30% !important;
					max-width: 30%;
				}

				.mj-column-per-70 {
					width: 70% !important;
					max-width: 70%;
				}
			}
		</style>
		<style type="text/css">
			@media only screen and (max-width:480px) {
				table.full-width-mobile {
					width: 100% !important;
				}

				td.full-width-mobile {
					width: auto !important;
				}
			}
		</style>
		<style type="text/css">
			@font-face {
				font-family: 'TeleNeoWeb';
				font-style: normal;
				font-weight: normal;
				src: local('TeleNeoWeb-Regular'), url("https://nextcloud-dev.t-assets.de/apps/appdirect/assets/fonts/TeleNeoWeb-Regular.woff2") format("woff2");
			}

			@font-face {
				font-family: 'TeleNeoWeb';
				font-style: normal;
				font-weight: bold;
				src: local('TeleNeoWeb-Bold'), url("https://nextcloud-dev.t-assets.de/apps/appdirect/assets/fonts/TeleNeoWeb-Bold.woff2") format("woff2");
			}

			@font-face {
				font-family: 'TeleNeoWeb';
				font-style: normal;
				font-weight: 800;
				src: local('TeleNeoWeb-ExtraBold'), url("https://nextcloud-dev.t-assets.de/apps/appdirect/assets/fonts/TeleNeoWeb-ExtraBold.woff2") format("woff2");
			}

			a, a:hover, a:visited, a:focus {
				color: inherit;
				text-decoration: none;
			}

			ul, ol {
				margin-left: 0;
				padding-left: 15px;
			}

			li {
				margin-bottom: 8px;
				color: #e20074;
			}

			li span {
				color: #262626;
			}

			.textLink {
				color: #262626 !important;
				color: inherit !important;
				text-decoration: none !important;
			}
		</style>
	</head>

	<body style="background-color:#ffffff;">
		<div style="background-color:#ffffff;">
	EOF;

	protected $tail = <<<EOF
		</div>
	</body>

	</html>
	EOF;

	protected $header = <<<EOF
	<!-- START: HEADER basic  -->
	<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="background:#e20074;background-color:#e20074;Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#e20074;background-color:#e20074;width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;padding-bottom:17px;padding-top:17px;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
					  <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td align="left" style="font-size:0px;padding:10px 10px;padding-top:0;padding-bottom:0;word-break:break-word;">
									  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
											<tbody>
											  <tr>
													<td style="width:81px;"><img height="auto" src="https://nextcloud-dev.t-assets.de/apps/appdirect/assets/T_logo_claim_de_rgb_n.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%%;" width="81"></td>
											  </tr>
											</tbody>
									  </table>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: HEADER basic  -->
	<!-- START: MODULE subject  -->
	<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;padding-top:0;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:285px;" ><![endif]-->
					  <div class="mj-column-per-50 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td align="left" style="font-size:0px;padding:10px 10px;word-break:break-word;">
									  <div style="font-family:TeleNeoWeb, Arial, sans-serif;font-size:12px;font-weight:bold;line-height:17px;text-align:left;color:#262626;">
										<strong><a href="#" class="textLink">MagentaCLOUD Business</a></strong></div>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:285px;" ><![endif]-->
					  <div class="mj-column-per-50 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;"></div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE subject  -->
	EOF;

	protected $heading = <<<EOF
	<!-- START: MODULE copy  -->
	<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;padding-bottom:0;padding-top:0;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
					  <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td align="left" style="font-size:0px;padding:10px 10px;padding-top:0;word-break:break-word;">
									  <div style="font-family:TeleNeoWeb, Arial, sans-serif;font-size:18px;font-weight:bold;line-height:22px;text-align:left;color:#262626;">%s</div>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE copy  -->
	EOF;

	protected $bodyBegin = '';

	protected $bodyText = <<<EOF
	<!-- END: MODULE subject  -->
	<!-- START: MODULE copy  -->
	<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;padding-bottom:0;padding-top:0;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
					  <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td align="left" style="font-size:0px;padding:10px 10px;padding-top:0;word-break:break-word;">
									  <div style="font-family:TeleNeoWeb, Arial, sans-serif;font-size:16px;font-weight:400;line-height:22px;text-align:left;color:#262626;">%s</div>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE copy  -->
	EOF;

	protected $listBegin = <<<EOF
	<table class="row description" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
		<tbody>
			<tr style="padding:0;text-align:left;vertical-align:top">
				<th class="small-12 large-12 columns first last" style="Margin:0 auto;color:#0a0a0a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0 auto;padding:0;padding-bottom:30px;padding-left:30px;padding-right:30px;text-align:left;width:550px">
					<table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
	EOF;

	protected $listItem = <<<EOF
	<tr style="padding:0;text-align:left;vertical-align:top">
		<td style="Margin:0;color:#0a0a0a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left;width:15px;">
			<p class="text-left" style="Margin:0;Margin-bottom:10px;color:#777;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;margin-bottom:10px;padding:0;padding-left:10px;text-align:left">%s</p>
		</td>
		<td style="Margin:0;color:#0a0a0a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0;text-align:left">
			<p class="text-left" style="Margin:0;Margin-bottom:10px;color:#555;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;margin-bottom:10px;padding:0;padding-left:10px;text-align:left">%s</p>
		</td>
		<td class="expander" style="Margin:0;color:#0a0a0a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.3;margin:0;padding:0!important;text-align:left;visibility:hidden;width:0"></td>
	</tr>
	EOF;

	protected $listEnd = <<<EOF
					</table>
				</th>
			</tr>
		</tbody>
	</table>
	EOF;

	protected $buttonGroup = <<<EOF
	<!-- START: MODULE subscription -->
	<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="background:#ededed;background-color:#ededed;Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ededed;background-color:#ededed;width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="width:570px;" ><![endif]-->
					  <div class="mj-column-per-100 outlook-group-fix" style="font-size:0;line-height:0;text-align:left;display:inline-block;width:100%%;direction:ltr;">
							<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td style="vertical-align:top;width:285px;" ><![endif]-->
							<div class="mj-column-per-50 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:50%%;">
							  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
									<tr>
									  <td align="right" vertical-align="middle" style="font-size:0px;padding:10px 10px;word-break:break-word;">
											<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%%;">
											  <tr>
													<td align="center" bgcolor="#e20074" role="presentation" style="border:1px solid #e20074;border-radius:8px;cursor:auto;padding:10px 15px 8px;background:#e20074;" valign="middle">
														<a href="%3\$s" style="background:#e20074;color:#ffffff;font-family:TeleNeoWeb, Arial, sans-serif;font-size:12px;font-weight:normal;line-height:14px;Margin:0;text-decoration:none;text-transform:none;" target="_blank">%7\$s</a>
													</td>
											  </tr>
											</table>
									  </td>
									</tr>
							  </table>
							</div>
							<!--[if mso | IE]></td><td style="vertical-align:top;width:285px;" ><![endif]-->
							<div class="mj-column-per-50 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:50%%;">
							  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
									<tr>
									  <td align="left" vertical-align="middle" style="font-size:0px;padding:10px 10px;word-break:break-word;">
											<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%%;">
											  <tr>
													<td align="center" bgcolor="#ededed" role="presentation" style="border:1px solid #262626;border-radius:8px;cursor:auto;padding:10px 15px 8px;background:#ededed;" valign="middle">
														<a href="%8\$s" style="background:#ededed;color:#262626;font-family:TeleNeoWeb, Arial, sans-serif;font-size:12px;font-weight:normal;line-height:14px;Margin:0;text-decoration:none;text-transform:none;" target="_blank">%9\$s</a>
													</td>
											  </tr>
											</table>
									  </td>
									</tr>
							  </table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE subscription -->
	EOF;

	protected $button = <<<EOF
	<!-- START: MODULE subscription -->
	<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="background:#ededed;background-color:#ededed;Margin:0px auto;max-width:600px;">
		<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ededed;background-color:#ededed;width:100%%;">
			<tbody>
				<tr>
					<td style="direction:ltr;font-size:0px;padding:15px;text-align:center;vertical-align:top;">
					<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="width:570px;" ><![endif]-->
						<div class="mj-column-per-100 outlook-group-fix" style="font-size:0;line-height:0;text-align:left;display:inline-block;width:100%%;direction:ltr;">
						<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td style="vertical-align:top;width:570px;" ><![endif]-->
							<div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
									<tr>
										<td align="center" vertical-align="middle" style="font-size:0px;padding:10px 10px;word-break:break-word;">
											<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%%;">
												<tr>
													<td align="center" bgcolor="#e20074" role="presentation" style="border:1px solid #e20074;border-radius:8px;cursor:auto;padding:10px 15px 8px;background:#e20074;" valign="middle">
														<a href="%3\$s" style="background:#e20074;color:#ffffff;font-family:TeleNeoWeb, Arial, sans-serif;font-size:12px;font-weight:normal;line-height:14px;Margin:0;text-decoration:none;text-transform:none;" target="_blank">%7\$s</a>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
						<!--[if mso | IE]></td></tr></table><![endif]-->
						</div>
					<!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE subscription -->
	EOF;

	protected $bodyEnd = '';

	protected $footer = <<<EOF
	<!-- END: MODULE subject  -->
	<!-- START: MODULE copy  -->
	<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
		<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%%;">
			<tbody>
				<tr>
					<td style="direction:ltr;font-size:0px;padding:15px;padding-bottom:0;padding-top:0;text-align:center;vertical-align:top;">
						<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
						<div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
								<tr>
									<td align="left" style="font-size:0px;padding:10px 10px;padding-top:0;word-break:break-word;">
										<div style="font-family:TeleNeoWeb, Arial, sans-serif;font-size:16px;font-weight:400;line-height:22px;text-align:left;color:#262626;"><br>Sollten Sie Fragen zu Ihrem Vertrag, Ihrer Rechnung, zur Einrichtung oder Produktnutzung haben, steht Ihnen ein eigenes Team zertifizierter Cloud-Experten der Telekom zur Seite. Die Kontaktmöglichkeiten finden Sie unter <a href="https://www.telekom.de/cloud-kontakt">www.telekom.de/cloud-kontakt</a>.<br><br><i>Dies ist eine automatisch generierte Nachricht, bitte antworten Sie nicht auf diese E-Mail.<br><br></i></div>
									</td>
								</tr>
							</table>
						</div>
						<!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE copy  -->
	<!-- START: MODULE footer-logo -->
	<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td height="20" style="vertical-align:top;height:20px;"><![endif]-->
	<div style="height:20px;">&nbsp;</div>
	<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;padding-left:25px;padding-right:25px;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:550px;" ><![endif]-->
					  <div class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td style="font-size:0px;padding:20px 0px;word-break:break-word;">
										<p style="border-top:solid 1px #a3a3a3;font-size:1;margin:0px auto;width:100%%;"></p>
										<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 1px #a3a3a3;font-size:1;margin:0px auto;width:550px;" role="presentation" width="550px" ><tr><td style="height:0;line-height:0;"> &nbsp;</td></tr></table><![endif]-->
									</td>
							  </tr>
							  <tr>
									<td align="left" style="font-size:0px;padding:0;word-break:break-word;">
									  <div style="font-family:TeleNeoWeb, Arial, sans-serif;font-size:14px;font-weight:bold;line-height:22px;text-align:left;color:#262626;">
											<a href="https://www.telekom.de/ueber-das-unternehmen/datenschutz#fragen-und-antworten" class="textLink" style="margin-right: 15px;">Datenschutz</a>
											<span style="margin-right: 15px;">|</span>
											<a href="https://www.telekom.de/pflichtangaben" class="textLink" style="margin-right: 15px;">gesetzliche Pflichtangaben</a>
									  </div>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td height="20" style="vertical-align:top;height:20px;"><![endif]-->
	<div style="height:20px;">&nbsp;</div>
	<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
	<div style="Margin:0px auto;max-width:600px;">
	  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%%;">
			<tbody>
			  <tr>
					<td style="direction:ltr;font-size:0px;padding:15px;text-align:center;vertical-align:top;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:285px;" ><![endif]-->
					  <div class="mj-column-per-50 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td align="left" style="font-size:0px;padding:10px 10px;padding-top:0;padding-bottom:0;word-break:break-word;">
									  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
											<tbody>
											  <tr>
													<td style="width:81px;"><img height="auto" src="https://nextcloud-dev.t-assets.de/apps/appdirect/assets/T_logo_claim_de_rgb_k.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%%;" width="81"></td>
											  </tr>
											</tbody>
									  </table>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:285px;" ><![endif]-->
					  <div class="mj-column-per-50 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%%;">
							<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%%">
							  <tr>
									<td align="right" style="font-size:0px;padding:10px 10px;word-break:break-word;">
									  <div style="font-family:TeleNeoWeb, Arial, sans-serif;font-size:8px;font-weight:400;line-height:8px;text-align:right;color:#262626;">Telekom Deutschland GmbH</div>
									</td>
							  </tr>
							</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
			  </tr>
			</tbody>
	  </table>
	</div>
	<!--[if mso | IE]></td></tr></table><![endif]-->
	<!-- END: MODULE footer-logo -->
	EOF;
}
