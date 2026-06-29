<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 *  NotificationMailing class implements the NotificationInterface
**/
class NotificationMailing implements NotificationInterface
{
    /**
     * Check data
     *
     * @param mixed $value   The data to check (may differ for every notification mode)
     * @param array $options Optionnal special options (may be needed)
     *
     * @return boolean
    **/
    public static function check($value, $options = [])
    {
        return self::isUserAddressValid($value, $options);
    }

    /**
     * Determine if email is valid
     *
     * @param string $address email to check
     * @param array  $options options used (by default 'checkdns'=>false)
     *     - checkdns :check dns entry
     *
     * @return boolean
    **/
    public static function isUserAddressValid($address, $options = ['checkdns' => false])
    {
        if (empty($address)) {
            return false;
        }

        //drop sanitize...
        $address = Toolbox::stripslashes_deep($address);
        $isValid = GLPIMailer::ValidateAddress($address);

        $checkdns = (isset($options['checkdns']) ? $options['checkdns'] : false);
        if ($checkdns) {
            $domain    = substr($address, strrpos($address, '@') + 1);
            if (
                $isValid
                  && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))
            ) {
                // domain not found in DNS
                $isValid = false;
            }
        }
        return $isValid;
    }


    public static function testNotification()
    {
        global $CFG_GLPI;

        $mmail = new GLPIMailer();

        $smtp_test = null;
        if ($CFG_GLPI["smtp_mode"] != MAIL_MAIL) {
            $smtp_test = self::testSMTPConnection($mmail);
            if (!$smtp_test['success']) {
                Session::addMessageAfterRedirect($smtp_test['message'], false, ERROR);
                return false;
            }
        }

        $mmail->AddCustomHeader("Auto-Submitted: auto-generated");
        // For exchange
        $mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");
        $mmail->SetFrom($CFG_GLPI["admin_email"], $CFG_GLPI["admin_email_name"], false);

        $text = __('This is a test email.') . "\n-- \n" . $CFG_GLPI["mailing_signature"];
        $recipient = $CFG_GLPI['admin_email'];
        if (defined('GLPI_FORCE_MAIL')) {
            //force recipient to configured email address
            $recipient = GLPI_FORCE_MAIL;
            //add original email addess to message body
            $text .= "\n" . sprintf(__('Original email address was %1$s'), $CFG_GLPI['admin_email']);
        }

        $mmail->AddAddress($recipient, $CFG_GLPI["admin_email_name"]);
        $mmail->Subject = "[ITSM-NG] " . __('Mail test');
        $mmail->Body    = $text;

        if (!$mmail->Send()) {
            $lines = [__('Failed to send test email to administrator')];
            if (!empty($mmail->ErrorInfo)) {
                $lines[] = sprintf(__('PHPMailer error: %1$s'), $mmail->ErrorInfo);
            }

            Session::addMessageAfterRedirect(
                implode('<br/>', array_map(function ($line) {
                    return nl2br(Html::entities_deep($line));
                }, $lines)),
                false,
                ERROR
            );
            return false;
        } else {
            $message = __('Test email sent to administrator');
            if ($smtp_test !== null) {
                $message = implode('<br/>', array_map(function ($line) {
                    return nl2br(Html::entities_deep($line));
                }, [
                    __('SMTP connection successful'),
                    $message,
                ]));
            }
            Session::addMessageAfterRedirect($message);
            return true;
        }
    }


    private static function testSMTPConnection(GLPIMailer $mailer)
    {
        $debug = [];
        $previous_debug = $mailer->SMTPDebug;
        $previous_debug_output = $mailer->Debugoutput;

        $mailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION;
        $mailer->Debugoutput = function ($message, $level) use (&$debug) {
            $message = trim((string) $message);
            if ($message !== '') {
                $debug[] = sprintf('%1$s - %2$s', $level, $message);
            }
        };

        $success = false;
        $exception = null;

        try {
            $success = $mailer->smtpConnect($mailer->SMTPOptions);
        } catch (\Throwable $e) {
            $exception = $e;
        }

        $smtp_error = $mailer->getSMTPInstance()->getError();
        $mailer->smtpClose();

        $mailer->SMTPDebug = $previous_debug;
        $mailer->Debugoutput = $previous_debug_output;

        if ($success) {
            return [
                'success' => true,
                'message' => '',
            ];
        }

        return [
            'success' => false,
            'message' => implode(
                '<br/>',
                array_map(
                    function ($line) {
                        return nl2br(Html::entities_deep($line));
                    },
                    self::getSMTPDiagnosticLines($mailer, $smtp_error, $exception, $debug)
                )
            ),
        ];
    }


    private static function getSMTPDiagnosticLines(GLPIMailer $mailer, array $smtp_error, $exception, array $debug)
    {
        global $CFG_GLPI;

        $security = __('None');
        if ($CFG_GLPI['smtp_mode'] == MAIL_SMTPSSL) {
            $security = 'SSL';
        } elseif ($CFG_GLPI['smtp_mode'] == MAIL_SMTPTLS) {
            $security = 'TLS';
        }

        $lines = [
            __('SMTP connection failed'),
            sprintf(__('SMTP server: %1$s:%2$s'), $CFG_GLPI['smtp_host'], $CFG_GLPI['smtp_port']),
            sprintf(__('SMTP security: %1$s'), $security),
            sprintf(__('Certificate check: %1$s'), $CFG_GLPI['smtp_check_certificate'] ? __('Yes') : __('No')),
            sprintf(
                __('SMTP authentication: %1$s'),
                $CFG_GLPI['smtp_username'] !== '' ? sprintf(__('enabled for %1$s'), $CFG_GLPI['smtp_username']) : __('disabled')
            ),
        ];

        if (!empty($mailer->ErrorInfo)) {
            $lines[] = sprintf(__('PHPMailer error: %1$s'), $mailer->ErrorInfo);
        }

        if ($exception !== null) {
            $lines[] = sprintf(__('Exception: %1$s'), $exception->getMessage());
        }

        if (!empty($smtp_error['error'])) {
            $lines[] = sprintf(__('SMTP error: %1$s'), $smtp_error['error']);
        }
        if (!empty($smtp_error['detail'])) {
            $lines[] = sprintf(__('SMTP detail: %1$s'), $smtp_error['detail']);
        }
        if (!empty($smtp_error['smtp_code'])) {
            $lines[] = sprintf(__('SMTP code: %1$s'), $smtp_error['smtp_code']);
        }
        if (!empty($smtp_error['smtp_code_ex'])) {
            $lines[] = sprintf(__('SMTP extended code: %1$s'), $smtp_error['smtp_code_ex']);
        }

        $debug = array_slice($debug, -20);
        if (count($debug) > 0) {
            $lines[] = __('Last SMTP debug lines:');
            foreach ($debug as $debug_line) {
                $lines[] = $debug_line;
            }
        }

        return $lines;
    }


    public function sendNotification($options = [])
    {

        $data = [];
        $data['itemtype']                             = $options['_itemtype'];
        $data['items_id']                             = $options['_items_id'];
        $data['notificationtemplates_id']             = $options['_notificationtemplates_id'];
        $data['entities_id']                          = $options['_entities_id'];

        $data["headers"]['Auto-Submitted']            = "auto-generated";
        $data["headers"]['X-Auto-Response-Suppress']  = "OOF, DR, NDR, RN, NRN";

        $data['sender']                               = $options['from'];
        $data['sendername']                           = $options['fromname'];

        if (isset($options['replyto']) && $options['replyto']) {
            $data['replyto']       = $options['replyto'];
            if (isset($options['replytoname'])) {
                $data['replytoname']   = $options['replytoname'];
            }
        }

        $data['name']                                 = $options['subject'];

        $data['body_text']                            = $options['content_text'];
        if (!empty($options['content_html'])) {
            $data['body_html'] = $options['content_html'];
        }

        $data['recipient']                            = Toolbox::stripslashes_deep($options['to']);
        $data['recipientname']                        = $options['toname'];

        if (!empty($options['messageid'])) {
            $data['messageid'] = $options['messageid'];
        }

        if (isset($options['documents'])) {
            $data['documents'] = $options['documents'];
        }

        $data['mode'] = Notification_NotificationTemplate::MODE_MAIL;

        $queue = new QueuedNotification();

        if (!$queue->add(Toolbox::addslashes_deep($data))) {
            Session::addMessageAfterRedirect(__('Error inserting email to queue'), true, ERROR);
            return false;
        } else {
            //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
            Toolbox::logInFile(
                "mail",
                sprintf(
                    __('%1$s: %2$s'),
                    sprintf(
                        __('An email to %s was added to queue'),
                        $options['to']
                    ),
                    $options['subject'] . "\n"
                )
            );
        }

        return true;
    }
}
