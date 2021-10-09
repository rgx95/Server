<?php

require('Server.php');

$app = new Server();


$messages = ["hi", "how are you", "hello! i'm fine", "and you?", "good, today i have an exam", "wish you good luck!"];
  

$app->get("messages", function ($req, $res) use ($messages) {
  foreach($messages as $msg) {
    echo "- $msg\n";
  }
});

$app->get("message:", function ($req, $res) use ($messages) {
  if (array_key_exists($req->params["message"], $messages)) {
    echo "message:".$req->params["message"]." => ".$messages[($req->params["message"])]."'\n";
    return;
  }
  echo "message:".$req->params["message"]." not found";        
});

$app->get("messages/length", function ($req) use ($messages) {
  echo count($messages);
});


$app->post("message", function ($req) use ($messages) {
  $new_msg = $req->json["text"];
  array_push($messages, $new_msg);
  echo "successfully posted: \n";
  foreach($messages as $msg) {
    echo "- $msg\n";
  }
});



$app->listen();