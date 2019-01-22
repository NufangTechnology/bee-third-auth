<?php
require __DIR__ . '/../vendor/autoload.php';


//$accessToken = new \Bee\ThirdAuth\AccessToken('wx4459a4f9da3f73cc', '052375cb92a22cf7c35def3173e3ed8f');

//$token = $accessToken->get();

//file_put_contents(__DIR__ . '/token.log', $token . PHP_EOL, 8);

$token = json_decode(file_get_contents('./token.log'), true);

$ticket = new \Bee\ThirdAuth\Ticket($token['access_token']);

$result = $ticket->rawBody();

file_put_contents(__DIR__ . '/ticket.log', json_encode($result) . PHP_EOL, 8);