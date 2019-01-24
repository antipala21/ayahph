<?php
//AWS SDK
include(ROOT.'/lib/Vendor/aws.phar');
use Aws\Ses\SesClient,
Aws\Ses\Exception\SesException,
Aws\Common\Enum\Region;

App::uses('AppController', 'Controller');
class myMailer{

	public static function preRegistrantActivation($data, $table = 'User') {
		if(!($data['email'] = self::emailValid($data['email'], $table))) {
			return;
		}

		$email = PreRegistrantTable::emailTemplate();
		$sendEmailData = array(
			'email_to' => $data['email'],
			'email_subject' => isset($data['subject']) ? $data['subject'] : null,
			'email_body_txt' => isset($email['body']) ? $email['body'] : null,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => 'NativeCamp運営事務局'
			)
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	/*
	* NC-1665 Email and SMS invitation
	* Email sending for user invitation 
	*/
	public static function spEmailInvitation($data, $table = 'User') {
		if(!($data = self::emailValid($data, $table))) {
			return;
		}

		$View = new View(null);
		$View->hasRendered = false;
		$View->viewPath = $View->layoutPath = 'Emails' . DS . 'text';
		$body = $View->render('/Emails/text/email_invitation');

		$sendEmailData = array(
			'email_to' => $data,
			'email_subject' => 'ご案内状【オンライン英会話NativeCamp.】',
			'email_body_txt' => $body,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => 'NativeCamp運営事務局'
			)
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	public static function changeEmailAddress($user, $hash, $table = 'User'){
		if(!($user['new_email'] = self::emailValid($user['new_email'], $table))) {
			return;
		}

		$View = new View(null);
		$View->hasRendered = false;
		$View->viewPath = $View->layoutPath = 'Emails' . DS . 'text';
		$body = $View->render('/Emails/text/change_email_address');
		$body = str_replace(array('/--nickname--/', '/--email--/', '/--new_email--/', '/--hash--/'), array($user['nickname'], $user['email'], $user['new_email'], $hash), $body);

		$sendEmailData = array(
			'email_to' => $user['new_email'],
			'email_subject' => '★＜ネイティブキャンプ英会話＞メールアドレスの変更完了しました★',
			'email_body_txt' => $body,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => 'NativeCamp運営事務局'
			)
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	public static function sendMailMagazin($from_email,$to_email, $subject, $messages, $userData, $table = 'User'){
		$urlActivate = "";

		// 文字置換
		//NC-3819 - check if recruit or applicant and not user.
		if(!isset($userData['for_type'])) {
			$search  = array(
				'/--id--/',
				'/--nickname--/',
				'/--activate_email_url--/',
				'/--email--/',
				'/--domain--/',
				'/--disable_mail_magazine--/'
			);
			$replace = array(
				$userData["id"],
				$userData["nickname"],
				$urlActivate,
				$userData["email"],
				trim(Configure::read('base_url'),'/'),
				'https://nativecamp.net/user/distribution_cancelled/'.$userData["hash"]
			);
			$senderAdminNameHB = 'NativeCamp運営事務局';
		} else {
			$search  = array(
				'/--id--/',
				'/--name--/'
			);
			$replace = array(
				$userData["id"],
				$userData["name"]
			);
			$senderAdminNameHB = 'NativeCamp';
		}

		$subject = str_replace($search, $replace, $subject);

		if (!($to_email = self::emailValid($to_email, $table))) {
			return;
		}

		if (is_array($messages)) {
			//send both text and html
			$bodyText = str_replace($search, $replace, $messages['text']);
			$bodyHtml = str_replace($search, $replace, $messages['html']);

			// filter file tags
			$bodyText = self::files_tag_filter($bodyText);
			$bodyHtml = self::files_tag_filter($bodyHtml);
		} else {
			//send basic text
			$bodyText = str_replace($search, $replace, $messages);
			// filter file tags
			$bodyText = self::files_tag_filter($bodyText);
		}

		$sendEmailData = array(
			'email_to' => $to_email,
			'email_subject' => $subject,
			'email_body_txt' => $bodyText,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => 'NativeCamp運営事務局'
			),
			'senderAdminNameHB' => $senderAdminNameHB
		);

		if (isset($bodyHtml)) {
			$sendEmailData['email_body_html'] = $bodyHtml;
		} else {
            $sendEmailData['reply_to'] = 'info@nativecamp.net';
        }

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	public static function sendTemplateMail($mail_id, $to_email, $user = array(), $param = array(), $table = 'User'){
		App::uses('MailTemplate', 'Model');
		$hasHTML = FALSE;
		$toTeacher = FALSE;

		// 宛先がない
		if (empty($to_email)) {
			return 'to_email is empty';
		}

		if (empty($mail_id)) {
			return 'mail_id is empty';
		}		

		// テンプレート取得
        //NC-3819 check if homebase teacher
        $param['is_homebased'] = is_array($param) && isset($param['is_homebased']) ? $param['is_homebased'] : false;
        if($param['is_homebased']) {
            $mailbase = ClassRegistry::init('MailTemplatesHomeBaseTeacher')->findByIdAndStatus($mail_id, 1);
            if(isset($mailbase['MailTemplatesHomeBaseTeacher'])) {
                $mailbase['MailTemplate'] = $mailbase['MailTemplatesHomeBaseTeacher'];
                unset($mailbase['MailTemplatesHomeBaseTeacher']);
            } else {
                $mailbase = ClassRegistry::init('MailTemplate')->findByIdAndStatus($mail_id, 1);    
            }
        } else {
            $mailbase = ClassRegistry::init('MailTemplate')->findByIdAndStatus($mail_id, 1);
        }
		if (empty($mailbase['MailTemplate'])) {
			return 'MailTemplate '.$mail_id.' was not found';
		}
		$mail_template = $mailbase['MailTemplate'];

		// FROMがない
		if (empty($mail_template['from_email'])) {
			return 'from_email is empty';
		}

		# set vars
		$disableMailMagazine = 'https://nativecamp.net/user/distribution_cancelled/'.@$user["hash"];
		$search       = array('/--id--/','/--nickname--/', '/--email--/', '/--message--/', '/--disable_mail_magazine--/');
		$replace      = array(@$user['id'], @$user['nickname'], @$user['email'], @$param['message'], @$disableMailMagazine);

		$native_lang_subject = $mail_template['subject'];
		$native_lang_body = $mail_template['body'];
		$native_lang_body_html = $mail_template['body_html'];
		$native_lang = 'ja' ;
		//This will be set mail template base in the user native language 
		if ( isset($user['native_language2']) && $user['native_language2']) {
			switch ($user['native_language2']) {
				case 'en':
					$native_lang_subject = $mail_template['subject_en'];
					$native_lang_body = $mail_template['body_en'];
					$native_lang_body_html = $mail_template['body_html_en'];
					break;
				case 'ko':
					$native_lang_subject = $mail_template['subject_ko'];
					$native_lang_body = $mail_template['body_ko'];
					$native_lang_body_html = $mail_template['body_html_ko'];
					break;
				case 'th':
					$native_lang_subject = $mail_template['subject_th'];
					$native_lang_body = $mail_template['body_th'];
					$native_lang_body_html = $mail_template['body_html_th'];
					break;
				default:
					$native_lang_subject = $native_lang_subject;
					$native_lang_body = $native_lang_body;
					$native_lang_body_html = $native_lang_body_html;
					break;
			}
			$native_lang = ($user['native_language2'] != $native_lang) ? $user['native_language2'] : $native_lang ;
		}

		$mail_subject = (isset($native_lang_subject) && strlen(trim($native_lang_subject)) != 0 ) ? str_replace($search, $replace, trim($native_lang_subject)) : '';
		$mail_text = (isset($native_lang_body) && strlen(trim($native_lang_body)) != 0 ) ? str_replace($search, $replace, trim($native_lang_body)) : '';
		$mail_html = (isset($native_lang_body_html) && strlen(trim($native_lang_body_html)) != 0 ) ? str_replace($search, $replace, trim($native_lang_body_html)) : '';
		$hasHTML = (isset($native_lang_body_html) && !is_null($native_lang_body_html) && strlen(trim($native_lang_body_html)) != 0) ? TRUE : FALSE;

        switch ($mail_id) {
            case Configure::read('site_in_mail.student_changed_email'):
                $mail_text = str_replace(array('/--new_email--/', '/--url--/'), array($user['new_email'], $user['active_url']), $mail_text);
                $mail_html = str_replace(array('/--new_email--/', '/--url--/'), array($user['new_email'], $user['active_url']), $mail_html);
                break;

            case Configure::read('site_in_mail.student_changed_password'):
                $mail_text = str_replace(array('/--url--/'), array($user['active_url']), $mail_text);
                $mail_html = str_replace(array('/--url--/'), array($user['active_url']), $mail_html);
                break;

            case Configure::read('site_in_mail.student_reserved'):
            case Configure::read('site_in_mail.student_cancel_reservation'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--textbookName--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['textbookName']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--textbookName--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['textbookName']), $mail_html);
                break;
            case Configure::read('site_in_mail.instructor_mail_student_cancel_reservation'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_html);
                $toTeacher = TRUE;
                break;
            case Configure::read('site_in_mail.instructor_mail_student_reserved'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--textbookName--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['textbookName']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--textbookName--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['textbookName']), $mail_html);
                $toTeacher = TRUE;
                break;                            

            case Configure::read('site_in_mail.admin_student_reserved'):
            case Configure::read('site_in_mail.admin_student_cancel'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime']), $mail_html);
                break;

            case Configure::read('site_in_mail.auto_cancel_reservation'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/', '/--reservedCoin--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime'], $user['reservePoint']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/', '/--reservedCoin--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime'], $user['reservePoint']), $mail_html);
                break;
                
            case Configure::read('site_in_mail.teacher_cancel_reservation'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime']), $mail_html);
                break;

            case Configure::read('site_in_mail.student_reservedlesson_before_30minutes'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_html);
                break;

            case Configure::read('site_in_mail.instructor_reservedlesson_before_30minutes'):
                $mail_text = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_text);
                $mail_html = str_replace(array('/--teacherName--/','/--appointmentDate--/', '/--startTime--/'), array($user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_html);
                $toTeacher = TRUE;
                break;

            case Configure::read('site_in_mail.admin_reservedlesson_before_30minutes'):
                $mail_text = str_replace(array('/--email--/','/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/'), array($user['email'], $user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime']), $mail_text);
                $mail_html = str_replace(array('/--email--/','/--teacherName--/','/--appointmentDate--/', '/--startTime--/', '/--endTime--/'), array($user['email'], $user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['endTime']), $mail_html);
                break;

            case Configure::read('site_in_mail.student_inquiry'):
                $mail_text = str_replace(array('/--inquiryMessage--/','/--inquiryCategoryName--/','/--emailAddress--/','/--contactNumber--/'), array($user['inquiryMessage'], $user['inquiryCategoryName'], $user['email'], $user['contactNumber']), $mail_text);
                $mail_html = str_replace(array('/--inquiryMessage--/','/--inquiryCategoryName--/','/--emailAddress--/','/--contactNumber--/'), array($user['inquiryMessage'], $user['inquiryCategoryName'], $user['email'], $user['contactNumber']), $mail_html);
                break;
            case Configure::read('site_in_mail.student_inquiry_reply'):
                $mail_text = str_replace(array('/--replymessage--/'), array($user['replymessage']), $mail_text);
                $mail_html = str_replace(array('/--replymessage--/'), array($user['replymessage']), $mail_html);
                $mail_text = str_replace(array('/--translated-reply--/'), array($user['translated-reply']), $mail_text);
                break;

            
            case Configure::read('site_in_mail.student_corporate_inquiry'):
                $mail_text = str_replace(array('/--content--/','/--companyname--/','/--name--/'), array($user['content'], $user['companyname'], $user['name']), $mail_text);
                $mail_html = str_replace(array('/--content--/','/--companyname--/','/--name--/'), array($user['content'], $user['companyname'], $user['name']), $mail_html);
                break;

            case Configure::read('site_in_mail.admin_corporate_inquiry'):
                $mail_text = str_replace(array('/--content--/','/--companyname--/','/--name--/','/--companyurl--/','/--email--/','/--phone_number--/','/--category--/'), array($user['content'], $user['companyname'], $user['name'], $user['companyurl'], $user['email'], $user['phone_number'], $user['category']), $mail_text);
                $mail_html = str_replace(array('/--content--/','/--companyname--/','/--name--/'), array($user['content'], $user['companyname'], $user['name']), $mail_html);
                break; 

            case Configure::read('site_in_mail.admin_media_inquiry'):
                $mail_text = str_replace(array('/--content--/','/--companyname--/','/--name--/','/--companyurl--/','/--email--/','/--phone_number--/'), array($user['content'], $user['companyname'], $user['name'], $user['companyurl'], $user['email'], $user['phone_number']), $mail_text);
                $mail_html = str_replace(array('/--content--/','/--companyname--/','/--name--/'), array($user['content'], $user['companyname'], $user['name']), $mail_html);
                break;

            case Configure::read('site_in_mail.media_inquiry'):
                $mail_text = str_replace(array('/--content--/','/--companyname--/','/--name--/'), array($user['content'], $user['companyname'], $user['name']), $mail_text);
                $mail_html = str_replace(array('/--content--/','/--companyname--/','/--name--/'), array($user['content'], $user['companyname'], $user['name']), $mail_html);
                break;

            case Configure::read('site_in_mail.admin_student_inquiry'):
                $mail_text = str_replace(array('/--inquiry_message--/'), array($user['inquiry_message']), $mail_text);
                $mail_html = str_replace(array('/--inquiry_message--/'), array($user['inquiry_message']), $mail_html);
                break;

            case Configure::read('site_in_mail.student_regist_email_confirm'):
                $mail_text = str_replace(array('/--url--/'), array($user['active_url']), $mail_text);
                $mail_html = str_replace(array('/--url--/'), array($user['active_url']), $mail_html);
                break;

            case Configure::read('site_in_mail.purchase_book_confirm_user'):
                $mail_subject = str_replace('/--books--/', $user['books'], $mail_subject);
                $mail_text = str_replace(array('/--tax--/','/--nickname--/','/--name--/', '/--address--/', '/--contact--/', '/--email--/', '/--products--/', '/--price--/', '/--quantity--/', '/--subtotal--/', '/--total--/', '/--account--/'), array($user['tax'], $user['nickname'], $user['name'], $user['address'], $user['contact'], $user['email'], $user['products'], $user['price'], $user['qty'], $user['subtotal'], $user['total'], $user['account']), $mail_text);
                $mail_html = str_replace(array('/--tax--/','/--nickname--/','/--name--/', '/--address--/', '/--contact--/', '/--email--/', '/--products--/', '/--price--/', '/--quantity--/', '/--subtotal--/', '/--total--/', '/--account--/'), array($user['tax'], $user['nickname'], $user['name'], $user['address'], $user['contact'], $user['email'], $user['products'], $user['price'], $user['qty'], $user['subtotal'], $user['total'], $user['account']), $mail_html);
                break;

            case Configure::read('site_in_mail.purchase_book_confirm_admin'):
                $mail_text = str_replace(array('/--tax--/', '/--name--/', '/--address--/', '/--contact--/', '/--email--/', '/--products--/', '/--price--/', '/--quantity--/', '/--subtotal--/', '/--total--/'), array($user['tax'], $user['name'], $user['address'], $user['contact'], $user['email'], $user['products'], $user['price'], $user['qty'], $user['subtotal'], $user['total']), $mail_text);
                $mail_html = str_replace(array('/--tax--/', '/--name--/', '/--address--/', '/--contact--/', '/--email--/', '/--products--/', '/--price--/', '/--quantity--/', '/--subtotal--/', '/--total--/'), array($user['tax'], $user['name'], $user['address'], $user['contact'], $user['email'], $user['products'], $user['price'], $user['qty'], $user['subtotal'], $user['total']), $mail_html);
                break;

            case Configure::read('site_in_mail.student_registration_complete_notification'):
                break;

            case Configure::read('site_in_mail.student_referral_bonus_notification'):
                $mail_text = str_replace(array('/--recruiter--/', '/--recruit--/', '/--coin--/'), array($user['recruiter'], $user['recruit'], $user['coin']), $mail_text);
                $mail_html = str_replace(array('/--recruiter--/', '/--recruit--/', '/--coin--/'), array($user['recruiter'], $user['recruit'], $user['coin']), $mail_html);
                break;
            case Configure::read('site_in_mail.student_notify_settlement'):
                $mail_text = str_replace('/--trial/premium_end_date--/', date('Y年m月d日', strtotime('-1 day', strtotime($user['next_charge_date']))), $mail_text);
                $mail_text = str_replace('/--nickname--/', $user['nickname'], $mail_text);
                break;
            case Configure::read('site_in_mail.coupon_cancellation'):
                $mail_text = str_replace(array('/--nickname--/', '/--teacherName--/', '/--appointmentDate--/', '/--startTime--/'), array($user['nickname'], $user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_text);
                break;
            case Configure::read('site_in_mail.late_cancellation'):
                $mail_text = str_replace(array('/--nickname--/', '/--teacherName--/', '/--appointmentDate--/', '/--startTime--/'), array($user['nickname'], $user['teacherName'], $user['appointmentDate'], $user['startTime']), $mail_text);
                break;
            case Configure::read('site_in_mail.receipt_cert'):
                $mail_subject = str_replace('/--title--/', $user['subject'], $mail_subject);
                 $mail_text = str_replace(array('/--link--/','/--username--/','/--unique--/'), array($user['link'],$user['username'],$user['unique']), $mail_text);
                $mail_html = str_replace(array('/--link--/','/--username--/','/--unique--/'), array($user['link'],$user['username'],$user['unique']), $mail_html);
                break;    
            case Configure::read('site_in_mail.1hour_cancel_reservation'):
                $mail_text = str_replace(array('/--nickname--/', '/--teacherName--/', '/--appointmentDate--/', '/--startTime--/', '/--reservedCoin--/'), array($user['nickname'], $user['teacherName'], $user['appointmentDate'], $user['startTime'], $user['reservePoint']), $mail_text);
                break;
            case Configure::read('site_in_mail.mail_template_id'):
                $mail_text = str_replace(array('/--nickname--/','/--date--/'), array($user['nickname'], date('Y-m-d')), $mail_text);
                break;
            case Configure::read('site_in_mail.family_plan_disapprove_mail'):
                $mail_text = str_replace(array('/--name--/'), array($user['nickname']), $mail_text);
                break;
            case Configure::read('site_in_mail.family_plan_approve_mail'):
                $mail_text = str_replace(array('/--name--/', '/--parent ID--/', '/--parent mail address--/', '/--child ID--/', '/--child mail address--/'), array($user['nickname'], $user['parentId'], $user['parentEmail'], $user['familyId'], $user['familyEmail']), $mail_text);
                break;
            case Configure::read('site_in_mail.homebase_teacher_inquiry'):
                $mail_text = str_replace(array('/--inquiryMessage--/','/--inquiryCategoryName--/','/--emailAddress--/','/--contactNumber--/','/--nickName--/'), array($user['inquiryMessage'], $user['inquiryCategoryName'], $user['email'], $user['contactNumber'], $user['nickname']), $mail_text);
                $mail_html = str_replace(array('/--inquiryMessage--/','/--inquiryCategoryName--/','/--emailAddress--/','/--contactNumber--/','/--nickName--/'), array($user['inquiryMessage'], $user['inquiryCategoryName'], $user['email'], $user['contactNumber'], $user['nickname']), $mail_html);
                break;
            case Configure::read('site_in_mail.homebase_teacher_admin_inquiry'):
                $mail_text = str_replace(array('/--inquiryMessage--/','/--inquiryCategoryName--/','/--emailAddress--/','/--nickName--/'), array($user['inquiryMessage'], $user['inquiryCategoryName'], $user['email'], $user['nickname']), $mail_text);
                $mail_html = str_replace(array('/--inquiryMessage--/','/--inquiryCategoryName--/','/--emailAddress--/','/--nickName--/'), array($user['inquiryMessage'], $user['inquiryCategoryName'], $user['email'], $user['nickname']), $mail_html);
                break;
            case Configure::read('site_in_mail.homebase_teacher_admin_reply_inquiry'):
                $mail_text = str_replace(array('/--replymessage--/','/--nickName--/', '/--inquiryCategoryName--/'), array($user['replymessage'], $user['name'], $user['inquiryCategoryName']), $mail_text);
                $mail_html = str_replace(array('/--replymessage--/','/--nickName--/', '/--inquiryCategoryName--/'), array($user['replymessage'], $user['name'], $user['inquiryCategoryName']), $mail_html);
                break;
            case Configure::read('site_in_mail.student_change_textbook_reservation'):
                $mail_text = str_replace(array('/--teacherName--/','/--nickName--/', '/--appointmentDate--/', '/--startTime--/', '/--fromCategory--/', '/--fromTextbook--/', '/--toCategory--/', '/--toTextbook--/'), array($user['teacherName'], $user['nickname'], $user['appointmentDate'], $user['startTime'], $user['fromCategory'], $user['fromTextbook'], $user['toCategory'], $user['toTextbook']), $mail_text);
                break;
            case Configure::read('site_in_mail.textbook_recommendation'):
                $mail_html = str_replace(
                    array( '/--userName--/', '/--level--/', '/--level_text--/', '/--purpose--/', '/--type_id--/', '/--category_name--/', '/--type_text--/', '/--callan_method--/', '/--textbook_url--/', '/--textbook_result_cat_message--/', '/--texbook_image_url--/' ),
                    array( $user['nickname'], $user['level'], $user['level_text'], $user['purpose'], $user['type_id'], $user['category_name'], $user['type_text'], $user['callan_method'], $user['textbook_url'], $user['textbook_result_cat_message'], $user['texbook_image_url'] ), 
                    $mail_html
                );
            break;
			case Configure::read('site_in_mail.user_certificate'): // NC-4098 certificate template
				$mail_subject = str_replace('/--title--/', $user['subject'], $mail_subject);
				$mail_text = str_replace(array('/--link--/','/--username--/','/--unique--/'), array($user['link'],$user['username'],$user['unique']), $mail_text);
				$mail_html = str_replace(array('/--link--/','/--username--/','/--unique--/'), array($user['link'],$user['username'],$user['unique']), $mail_html);
				break;
            case Configure::read('site_in_mail.teacher_changed_password'):
                $mail_text = str_replace(array('/--url--/', '/--teacherName--/'), array($user['active_url'], $user['name']), $mail_text);
                break;    
            case Configure::read('site_in_mail.user_certificate'): // NC-4098 certificate template
                $mail_subject = str_replace('/--title--/', $user['subject'], $mail_subject);
                $mail_text = str_replace(array('/--link--/','/--username--/','/--unique--/'), array($user['link'],$user['username'],$user['unique']), $mail_text);
                $mail_html = str_replace(array('/--link--/','/--username--/','/--unique--/'), array($user['link'],$user['username'],$user['unique']), $mail_html);
                break;
        }

		$error = NULL;

		$sendEmailData = array(
			'email_to' => $to_email,
			'email_subject' => $mail_subject,
			'email_body_txt' => $mail_text,
			'is_homebased' => $param['is_homebased'],
			'native_lang' => $native_lang
		);

		# check if template has html email
		if ($hasHTML) {
			$sendEmailData['email_body_html'] = $mail_html;
		} else {
			$sendEmailData['reply_to'] = $sendEmailData['email_from'] = $mail_template['from_email'];
			$sendEmailData['toTeacher'] = $toTeacher;
		}

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	/**
	* send email
	* @param  array  $params
	* @return
	*/
	public static function sendHtmlOrPlainTextEMail($params = array(), $table = "User") {
		# check if any of the critical parameters are missing
		if (
			!isset($params['email_to']) ||
			!isset($params['email_subject']) ||
			(!isset($params['email_body_html']) && !isset($params['email_body_txt']))
		) {
			throw new Exception("Invalid parameters");
		}

		if(
			!($params['email_to'] = self::emailValid($params['email_to'], $table)) ||
			!filter_var($params['email_to'], FILTER_VALIDATE_EMAIL)
		) {
			return;
		}

		$error = null;
		$emailTo = $params['email_to'];
		$subject = $params['email_subject'];
		$bodyHtml = isset($params['email_body_html']) ? $params['email_body_html'] : null;
		$bodyText = isset($params['email_body_txt']) ? $params['email_body_txt'] : null;
		
		if (isset($params['senderAdminNameHB'])) {
			$senderAdminNameHB = $params['senderAdminNameHB'];
		} else {
			$subjectHeader = '運営事務局';
			if (isset($params['native_lang']) && $params['native_lang']) {
				switch ($params['native_lang']) {
					case 'en':
						$subjectHeader = 'Administrative Office';
						break;
					case 'ko':
						$subjectHeader = '운영 사무국';
						break;
					case 'th':
						$subjectHeader = 'สำนักงานบริหาร';
						break;
					default:
						$subjectHeader = $subjectHeader;
						break;
				} 
			}

			$senderAdminNameHB = ((isset($params['toTeacher']) && $params['toTeacher']) || (isset($params['is_homebased']) && $params['is_homebased'])) ? 'NativeCamp' : 'NativeCamp'.$subjectHeader;
		}

		$sendMailData = array(
			'Source' => "=?utf-8?b?".base64_encode($senderAdminNameHB)."?= <info@nativecamp.net>\r\n", # From mail address
			'Destination' => array(
				'ToAddresses' =>  array($emailTo) # To mail address
			),
			# mail subject
			'Message' => array(
				'Subject' => array(
					'Data' => $subject,
					'Charset' => 'utf-8'
				)
			)
		);

		if (isset($params['email_body_txt'])) {
			$sendMailData['Message']['Body']['Text'] = array('Data' => $bodyText, 'Charset' => 'utf-8');
			$body = $bodyText;
		}

		if (isset($params['email_body_html'])) {
			$sendMailData['Message']['Body']['Html'] = array('Data' => $bodyHtml, 'Charset' => 'utf-8');
			$body = $bodyHtml;
		}

		if (isset($params['email_cc'])) {
			$sendMailData['Destination']['CcAddresses'] = array($params['email_cc']);
		}

		if (isset($params['email_bcc'])) {
			$sendMailData['Destination']['BccAddresses'] = array($params['email_bcc']);
		}

		if (isset($params['reply_to'])) {
			$sendMailData['ReplyToAddresses'] = array($params['reply_to']);
		}

		if (isset($params['return_path'])) {
			$sendMailData['ReturnPath'] = "=?utf-8?b?".base64_encode($params['return_path']['name'])."?= <{$params['return_path']['email']}>\r\n"; # FROM mail address
		}

		# SES authorization
		$client = SesClient::factory(array(
			'key' => Configure::read('amazon_mailer.key'),
			'secret' => Configure::read('amazon_mailer.secret'),
			'region' => Region::US_WEST_2,
		));

		$status = 1;
		try {
			# send function
			$mail = $client->sendEmail($sendMailData);
			$messageId = $mail['MessageId'];
		} catch (AwsException $e) {
			$status = 0;
			$error = "AwsException Error \n" . $e->getAwsErrorMessage() . "params error testing : {$emailTo} {$subject} {$bodyText}  {$bodyHtml}";
		} catch (Exception $e) {
			$status = 0;
			$error = "Exception Error \n" . $e->getMessage() . "\n" . $e->__toString() . "params error testing : {$emailTo} {$subject} {$bodyText}  {$bodyHtml}";
		}

		// NC-4995: if has error
		if (!$status) {
			// send slack
			self::sendSlackErrorMail(array(
				'email_to' => $emailTo,
				'email_subject' => $subject,
				'email_body_txt' => $body,
				'error' => $error
			));
		}

		$emailLogData = array(
			'title' => $subject,
			'status' => $status,
			'body' => $body,
			'recipient_mail' => $emailTo,
			'error_contents' => $error
		);

		// NC-4995: check if $messageId exist and add to array $emailLogData
		if (isset($messageId)) {
			$emailLogData['message_id'] = $messageId;
		}

		ClassRegistry::init('EmailLog')->saveLog($emailLogData);

		if (isset($error)) {
			return ;
		} else {
			# return email status
			return $mail->toArray();
		}
	}

    /**
    * try catch function for mail
    * @params object : $email, array : $params - title, recipient_mail 
    */
    public static function catchExceptionError($email, $params = array()) {
        if (
            (!isset($params['title']) || empty($params['title'])) || 
            (!isset($params['recipient_mail']) || empty($params['recipient_mail']))
        ) {
            return NULL;
        }
        $status = 1;
        $send = null;
        $params['error_contents'] = NULL;
        $tmpStr = isset($params['body']) ? trim($params['body']) : '';
        $body = !empty($tmpStr) ? $tmpStr : NULL;
        try {
            if ($body) {
               $send = $email->send($body);
            } else {            
               $send = $email->send();
            }
        } catch (Exception $e) {
            $status = 0;
            $params['error_contents'] = $e->getMessage();
            $params['error_contents'] .= "\n" . $e->__toString();
            self::sendSlackErrorMail(array(
                    'email_to' => $params['recipient_mail'],
                    'email_subject' => $params['title'],
                    'email_body_txt' => $body,
                    'error' => $params['error_contents']
                ));
        }
        $params['status'] = $status;
        $params['body'] = $body;
        ClassRegistry::init('EmailLog')->saveLog($params);
        return $send;
    }

    /**
    * send slack error in mail
    * @param array
    */
    public static function sendSlackErrorMail($params = array()) {
        if (
            (!isset($params['email_to']) || empty($params['email_to'])) || 
            (!isset($params['email_subject']) || empty($params['email_subject']))
        ) {
            return false;
        }
        //check slack class
        if (!class_exists('mySlack')) {
            App::uses('mySlack', 'Lib');
        }
        //instantiate slack
        $mySlack = new mySlack();
        $mySlack->channel = myTools::checkChannel('nc-system', 'nc-system-dev');
        $mySlack->username = 'NC-myMailer:Error';

        //put error message in body
        $params['email_body_txt'] = isset($params['error']) ? $params['error'] : '';
        //trim error message for slack
        if (mb_strlen($params['email_body_txt']) >= 600) {
             $params['email_body_txt'] = mb_strimwidth($params['email_body_txt'], 0, 600, '...');
        }  

        //set params for slack message
        $mySlack->text = "Mail Server Down \nEmail : ".$params['email_to'];
        $mySlack->text .= "\nTITLE : ".$params['email_subject'];
        $mySlack->text .= "\nCONTENT : ".$params['email_body_txt']." \n";
        $mySlack->text .= (!isset($_SERVER['HTTP_HOST']) || !isset($_SERVER['REQUEST_URI'])) ? "TYPE : CRON" : "URL : https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $mySlack->sendSlack();
    }

	/**
	* custom mailer for teacher preregistration
	*/
	public static function sendApplicantMail($params = array(), $table = 'User') {
		if (empty($params['mail_id']) || empty($params['to_email'])) {
			return false;
		}
		$mailId = $params['mail_id'];
		$fromEmailLabel = (isset($params['from_email_label']) && !empty($params['from_email_label'])) ? $params['from_email_label'] : 'NativeCamp運営事務局';
		$toEmail = $params['to_email'];

		if(!($toEmail = self::emailValid($toEmail, $table))) {
			return;
		}

		App::uses('MailTemplate', 'Model');
		$mailBase = ClassRegistry::init('MailTemplate')->useReplica()->find('first', array(
			'conditions' => array(
				'MailTemplate.id' => $mailId,
				'MailTemplate.status = 1'
			)
		));

		if (empty($mailBase['MailTemplate'])) {
			return 'MailTemplate '.$mailId.' was not found';
		}

		$mailBase = $mailBase['MailTemplate'];
		$sendEmailData = array(
			'email_from' => $mailBase['from_email'],
			'email_to' => $toEmail,
			'email_subject' => $mailBase['subject'],
			'email_body_txt' => $mailBase['body'],
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => $fromEmailLabel
			),
			'senderAdminNameHB' => $fromEmailLabel
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	/**
	* custom mailer for recruit confirm email
	*/
	public static function sendApplicantEmailConfirm($params = array(), $table = 'User') {
		if (empty($params['mail_id']) || empty($params['to_email'])) {
			return false;
		}

		$mailId = $params['mail_id'];
		$fromEmailLabel = 'Recruitment Team, NativeCamp.';
		$toEmail = $params['to_email'];

		if(!($toEmail = self::emailValid($toEmail, $table))) {
			return;
		}

		App::uses('MailTemplate', 'Model');
		$mailBase = ClassRegistry::init('MailTemplate')->useReplica()->find('first', array(
			'conditions' => array(
				'MailTemplate.id' => $mailId,
				'MailTemplate.status = 1'
			)
		));

		if (empty($mailBase['MailTemplate'])) {
			return 'MailTemplate '.$mailId.' was not found';
		}

		$mailBase = $mailBase['MailTemplate'];
		$mail_text = str_replace(
			'/--signup link activation--/', 
			myTools::getUrl().'/recruit/signup_cert_complete?hash='.$params['hash'], 
			$mailBase['body']
		);

		$sendEmailData = array(
			'email_from' => $mailBase['from_email'],
			'email_to' => $toEmail,
			'email_subject' => $mailBase['subject'],
			'email_body_txt' => $mail_text,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => $fromEmailLabel
			),
			'senderAdminNameHB' => $fromEmailLabel
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	/**
	* custom mailer for recruit forgot password
	*/
	public static function sendApplicantResetPassword($params = array(), $table = 'User') {
		if (empty($params['mail_id']) || empty($params['to_email'])) {
			return false;
		}

		$mailId = $params['mail_id'];
		$fromEmailLabel = 'Recruitment Team, NativeCamp.';
		$toEmail = $params['to_email'];

		if(!($toEmail = self::emailValid($toEmail, $table))) {
			return;
		}

		App::uses('MailTemplate', 'Model');
		$mailBase = ClassRegistry::init('MailTemplate')->useReplica()->find('first', array(
			'conditions' => array(
				'MailTemplate.id' => $mailId,
				'MailTemplate.status = 1'
			)
		));

		if (empty($mailBase['MailTemplate'])) {
			return 'MailTemplate '.$mailId.' was not found';
		}

		$mailBase = $mailBase['MailTemplate'];

		$mail_text = str_replace(
			'/--forgot password link--/',
			myTools::getUrl().'/recruit/reset?hash='.$params['hash'], 
			$mailBase['body']
		);

		$sendEmailData = array(
			'email_from' => $mailBase['from_email'],
			'email_to' => $toEmail,
			'email_subject' => $mailBase['subject'],
			'email_body_txt' => $mail_text,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => $fromEmailLabel
			),
			'senderAdminNameHB' => $fromEmailLabel
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	/**
	* custom mailer for recruit promotion
	*/
	public static function sendApplicantPromotion($params = array(), $table = 'User') {
		if (empty($params['mail_id']) || empty($params['to_email'])) {
			return false;
		}

		$mailId = $params['mail_id'];
		$fromEmailLabel = 'Recruitment Team, NativeCamp.';
		$toEmail = $params['to_email'];
		if(!($toEmail = self::emailValid($toEmail, $table))) {
			return;
		}

		App::uses('MailTemplate', 'Model');
		$mailBase = ClassRegistry::init('MailTemplate')->useReplica()->find('first', array(
			'conditions' => array(
				'MailTemplate.id' => $mailId,
				'MailTemplate.status = 1'
			)
		));

		if (empty($mailBase['MailTemplate'])) {
			return 'MailTemplate '.$mailId.' was not found';
		}

		$mailBase = $mailBase['MailTemplate'];
		$promotion = $params['promotion'];
		$mail_text = str_replace(
			array(
				'/--login id--/',
				'/--login password--/',
				'/--nick name--/',
				'/--first name--/',
				'/--middle name--/',
				'/--last name--/',
				'/--gender--/',
				'/--birth date--/',
				'/--mobile--/',
				'/--hobby--/',
				'/--occupation--/',
				'/--self introduction--/',
				'/--esl teaching experience--/',
				'/-- login link --/'
			),
			array(
				$promotion['login_id'],
				$promotion['password'],
				$promotion['nick_name'],
				$promotion['first_name'],
				$promotion['middle_name'],
				$promotion['last_name'],
				$promotion['gender'],
				$promotion['birthdate'],
				$promotion['mobile'],
				$promotion['hobby'],
				$promotion['occupation'],
				$promotion['self_introduction'],
				$promotion['working_experience'],
				myTools::getUrl().'/teacher'
			),
			$mailBase['body']
		);

		$sendEmailData = array(
			'email_from' => $mailBase['from_email'],
			'email_to' => $toEmail,
			'email_subject' => $mailBase['subject'],
			'email_body_txt' => $mail_text,
			'return_path' => array(
				'email' => 'return@nativecamp.net',
				'name' => $fromEmailLabel
			),
			'senderAdminNameHB' => $fromEmailLabel
		);

		self::sendHtmlOrPlainTextEMail($sendEmailData, $table);
	}

	/**
	* NC-4114
	* Filter file tags..
	* Replace /--files tag and replace with full url
	*/
	private static function files_tag_filter ($text) {
		$toFind = "/--files:";
		$start = 0;
		while(($pos = strpos($text, $toFind, $start)) !== false) {
			$start = $pos+1;
			$full_tag = substr($text, $pos + 9, 18);
			$fileName = explode('--', $full_tag);
			$fileName = $fileName[0];
			$text = str_replace('/--files:' . $fileName . '--/', ClassRegistry::init('FileUpload')->getUrl($fileName), $text);
		}
		return $text;
	}

	//if $email is array, remove email from array, else filter directly.
	public static function emailValid($email, $table){
		// NC-4665 return email bec corporate has no invalid email_flg
		if ($table === 'Corporate') {
			return $email;
		}
        //if $table is array or empty or valid email; make $table to default
        if(is_array($table) || empty($table) || filter_var($table, FILTER_VALIDATE_EMAIL) || $table == 'PreRegistrant' || $table == 'TeacherPreregister') {
            $table = 'User';
        }
		//check email has invalid_email_flg
        $invalidEmails = ClassRegistry::init($table)->find('list', array(
            'conditions' => array(
                $table.'.email' => $email, //check only passed emails
                $table.'.invalid_email_flg' => 1
            ),
            'fields' => $table.'.email',
            'recursive' => -1
        ));
        if(empty($invalidEmails)) {
            return $email;
        }
        $invalidEmails = array_values($invalidEmails);
		if(is_array($email) || is_object($email)) {
			foreach ($email as $key => $value) {
				//remove email in email(array) if email is in invalidEmails
				if (in_array($value, $invalidEmails)) {
					if(is_array($email)) {
						unset($email[$key]);
					} else {
						unset($email->$key);
					}
				}
			}
			if(is_array($email)) {
				$email = array_values($email);	
			}
			if(empty($email)) {
				return false;
			}
		} else {
			if (in_array($email, $invalidEmails)) {
				return false;
			}
		}
		return $email;
	}
}
