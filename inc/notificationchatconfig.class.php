<?php

class NotificationChatConfig extends CommonDBTM
{

    function processPostData($rocketUrl, $chat, $type, $value)
    {
        $return = $this->add(array(
            'hookurl' => $rocketUrl,
            'chat' => $chat,
            'type' => $type,
            'value' => $value
        ));
    }

    public function sendRocketNotification($ticketTitle, $ticketId, $entName, $serverName, $hookurl)
    {
        $glpiUrl = $serverName;
        $entName = $entName;
        $ticketId = $ticketId;
        $ticketTitle = $ticketTitle;
        $hookurl = $hookurl;

        $jsonStructure = '{"alias":"test","text":"%s"}';
        $textStructure = "Entite : **%s** \n Le ticket numéro %s à été créé : **%s** \n Accédez au ticket en [cliquant ici](http://%s/front/ticket.form.php?id=%s)";

        $data = array(
            'alias' => 'test',
            'text' => ''
        );
        $data["text"] = sprintf($textStructure, $entName, $ticketId, $ticketTitle, $glpiUrl, $ticketId);

        $payload = json_encode($data);

        $ch = curl_init($hookurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request 
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            )
        );

        // Submit the POST request
        $result = curl_exec($ch);
        if ($result) {
            Toolbox::logInFile(
                "chat",
                sprintf(
                    __('Rocket chat: the chat %s was sent'),
                    $ticketTitle
                ) . "\n"
            );
        } else {
            Toolbox::logInFile(
                "chat-error",
                sprintf(
                    __('Fatal-error: the chat %s was not send to rocket chat'),
                    $ticketTitle
                ) . "\n"
            );
        }

        // Close cURL session handle
        curl_close($ch);

        return $result;
    }

    public function sendRocketNotificationNew($ticketTitle, $ticketId, $entName, $serverName, $hookurl)
    {
        $glpiUrl = $serverName;
        $entName = $entName;
        $ticketId = $ticketId;
        $ticketTitle = $ticketTitle;
        $hookurl = $hookurl;

        $jsonStructure = '{"alias":"test","text":"%s"}';
        $textStructure = $entName;

        $data = array(
            'alias' => 'test',
            'text' => ''
        );

        $data["text"] = $entName;

        $payload = json_encode($data);

        $ch = curl_init($hookurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request 
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            )
        );

        // Submit the POST request
        $result = curl_exec($ch);
        if ($result) {
            Toolbox::logInFile(
                "chat",
                sprintf(
                    __('Rocket chat: the chat %s was sent'),
                    $ticketTitle
                ) . "\n"
            );
        } else {
            Toolbox::logInFile(
                "chat-error",
                sprintf(
                    __('Fatal-error: the chat %s was not send to rocket chat'),
                    $ticketTitle
                ) . "\n"
            );
        }

        // Close cURL session handle
        curl_close($ch);
    }
}
