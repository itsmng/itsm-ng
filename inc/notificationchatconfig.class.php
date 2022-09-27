<?php

class NotificationChatConfig extends CommonDBTM
{

    function processPostData($rocketUrl)
    {

        // First check if already configured
        $results = $this->find();
        if (!empty($results)) {
            foreach ($results as $id => $fields) {
                $this->delete($fields);
            }
        }

        $return = $this->add(array(
            'rockethookurl' => $rocketUrl
        ));
    }

    public function sendRocketNotification($ticketTitle, $ticketId, $entName, $serverName, $rocketHookUrl)
    {
        $glpiUrl = $serverName;
        $entName = $entName;
        $ticketId = $ticketId;
        $ticketTitle = $ticketTitle;
        $rocketHookUrl = $rocketHookUrl;

        $jsonStructure = '{"alias":"test","text":"%s"}';
        $textStructure = $entName;

        $data = array(
            'alias' => 'test',
            'text' => ''
        );
        /* $data["text"] = sprintf($textStructure, $entName, $ticketId, $ticketTitle, $glpiUrl, $ticketId); */
        $data["text"] = $entName;

        $payload = json_encode($data);

        $ch = curl_init($rocketHookUrl);
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
