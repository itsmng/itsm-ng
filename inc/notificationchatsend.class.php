<?php

class NotificationChatSend extends CommonDBTM
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

    static function triggerOnAdd(Ticket $post)
    {

        $ticketId = $post->fields['id'];
        $ticketTitle = $post->fields['name'];
        $serverName = $_SERVER['SERVER_NAME'] . $_SESSION['glpiroot'];

        $rocketNotifConfiguration = new self();
        $config = $rocketNotifConfiguration->find();
        $rocketHookUrl = $config[key($config)]['rockethookurl'];

        $entity = new Entity();
        $entity->getFromDB($post->fields['entities_id']);
        $entName = $entity->fields['name'];

        exec(sprintf(
            "%s &",
            $rocketNotifConfiguration->sendRocketNotification($ticketId, $entName, $ticketTitle, $serverName, $rocketHookUrl)
        ));
    }

    public function sendRocketNotification($ticketId, $entName, $ticketTitle, $serverName, $rocketHookUrl)
    {
        $glpiUrl = $serverName;
        $ticketId = $ticketId;
        $entName = $entName;
        $ticketTitle = $ticketTitle;
        $rocketHookUrl = $rocketHookUrl;

        $jsonStructure = '{"alias":"test","text":"%s"}';
        $textStructure = "Client : **%s** \n Le ticket numéro %s à été créé : **%s** \n Accédez au ticket en [cliquant ici](http://%s/front/ticket.form.php?id=%s)";

        $data = array(
            'alias' => 'test',
            'text' => ''
        );
        $data["text"] = sprintf($textStructure, $entName, $ticketId, $ticketTitle, $glpiUrl, $ticketId);

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
            Toolbox::logInFile('chat', __('The chat %s was sent'),  $ticketTitle . "\n");
        } else {
            Toolbox::logInFile('chat-error', __('Fatal-error: the chat %s was not send'),  $ticketTitle . "\n");
        }

        // Close cURL session handle
        curl_close($ch);
    }
}
