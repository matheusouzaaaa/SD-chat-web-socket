<?php

// precave o server do timing out
set_time_limit(0);

// inclui o script do servidor websocket (lembrando que o server tem que ser inicializado via terminal)
require 'class.PHPWebSocket.php';

// quando um usuário manda uma mensagem pro servidor.
function wsOnMessage($clientID, $message, $messageLength, $binary) {
    global $Server;
    $ip = long2ip($Server->wsClients[$clientID][6]);

    // validação simples para ver se o tamanho da mensagem é 0
    if ($messageLength == 0) {
        $Server->wsClose($clientID);
        return;
    }

    //essa validação serve para verificar se o usuário está sozinho na sala de conversa.
    //caso ele esteja, aparece uma mensagem informando a situação para o usuário.
    if (sizeof($Server->wsClients) == 1)
        $Server->wsSend($clientID, "Você está alone nessa sala de chat :(");
    else
    //aqui serve para enviar uma mensagem para qualquer um.
        foreach ($Server->wsClients as $id => $client)
            if ($id != $clientID)
                $Server->wsSend($id, "Visitante $clientID ($ip) disse \"$message\"");
}

// quando um usuário conecta ao chat
function wsOnOpen($clientID) {
    global $Server;
    $ip = long2ip($Server->wsClients[$clientID][6]);

    $Server->log("$ip ($clientID) conectou-se.");

    //envia uma mensagem para todos de que alguém entrou na sala de chat.
    foreach ($Server->wsClients as $id => $client)
        if ($id != $clientID)
            $Server->wsSend($id, "Visistante $clientID ($ip) entrou na sala o/.");
}

// quando um cliente fecha o chat
function wsOnClose($clientID, $status) {
    global $Server;
    $ip = long2ip($Server->wsClients[$clientID][6]);

    $Server->log("$ip ($clientID) desconectou-se.");

    //envia uma mensagem para todos da sala informando que um usuário deixou a sala.
    foreach ($Server->wsClients as $id => $client)
        $Server->wsSend($id, "Visitante $clientID ($ip) deixou a sala :(");
}

// inicia o phpWebSocket
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
$Server->wsStartServer('127.0.0.1', 9300);
?>