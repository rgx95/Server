<?php

  class Server {

    private $routes;
    private $req;
    private $res;

    public function __construct() {
      // create $req and $res
      $this->req = new stdClass();
      $this->res = new stdClass();
      $this->routes = array("GET" => array(), "POST" => array(), "PUT" => array(), "DELETE" => array());
      
      // fill in req object
      $this->req->path_info = $_SERVER['PATH_INFO'];
      $this->req->params = $this->extract_params($_SERVER['PATH_INFO']);
      $this->req->method = $_SERVER['REQUEST_METHOD'];
      $this->req->query = $_GET;
      $this->req->query_string = $_SERVER['QUERY_STRING'];
      $this->req->path_novalues = $this->clear_path($_SERVER['PATH_INFO']);
      $this->req->json = null;
      // se $_POST è empty allora ho dati raw (sicuramente json) e Parso il json in array associativo;
      // se $_POST è empty allora ho dati in $_POST come array associativo;
      if (empty($_POST)) {
        $this->req->body = file_get_contents('php://input');
        $this->req->json = json_decode($this->req->body, true);
      } else {
        $this->req->body = $_POST;
      }
            
      // fill in res object
    }

    public function get($path = "", $name_of_callback_func = "") : void {
      $this->register_function("GET", $path, $name_of_callback_func);      
    }
    public function post($path = "", $name_of_callback_func = "") : void {
      $this->register_function("POST", $path, $name_of_callback_func);      
    }
    public function put($path = "", $name_of_callback_func = "") : void {
      $this->register_function("PUT", $path, $name_of_callback_func);      
    }
    public function delete($path = "", $name_of_callback_func = "") : void {
      $this->register_function("DELETE", $path, $name_of_callback_func);      
    }

    private function register_function($method, $path, $name_of_callback_func) {
      if ($name_of_callback_func == "") {
        return;
      }  

      //check if path already exists
      if ($this->path_exists($method, $path)) {
        throw new Exception("Path already registered!");
        return;
      }

      array_push($this->routes[$method], array("path" => $path, "cfunc" => $name_of_callback_func));
    }

    public function listen() : void{
      $index = $this->path_index();
      call_user_func($this->routes[$this->req->method][$index]['cfunc'], $this->req, $this->res);
    }


    // utilities
    private function path_exists($method, $path) : bool {
      foreach($this->routes[$method] as $registered_route) {
        if ($registered_route["path"] == $path) {
          return true;
        }
      } 
      return false;
    }
    private function path_index() : int {
      foreach($this->routes[$this->req->method] as $i => $registered_route) {
        if ($registered_route["path"] == $this->req->path_novalues) {
          return $i;
        }
      } 
      return -1;
    }
    private function clear_path($path) : string {
      $path_array = explode('/', trim(htmlspecialchars($path), "/"));
      foreach($path_array as $i => $v) {
        $tmp = $v;
        if (strpos($v, ":") != false) {
          $tmp = substr($v, 0, strpos($v, ":")+1);
        }        
        $path_array[$i] = $tmp;
      }
      return implode('/', $path_array);
    }
    private function extract_params($path) : array {
      $params = array();
      $exploded_path_info = isset($path) ? explode('/', trim(htmlspecialchars($path), "/")) : array();
      foreach($exploded_path_info as $index => $param) {
        $temp_array = explode(":", $param);
        $params[$temp_array[0]] = isset($temp_array[1]) ? $temp_array[1] : null;
      }
      return $params;
    }

  }



/*
$app = new Server();



$app->get("messages", function ($req, $res) {
  echo "hello world!";
});

$app->get("message:", function ($req, $res) {
  $messages = ["hi", "how are you", "hello! i'm fine", "and you?", "good, today i have an exam", "wish you good luck!"];
  if (array_key_exists($req->params["message"], $messages)) {
    echo "message:".$req->params["message"]." => ".$messages[($req->params["message"])]."'\n";
    return;
  }
  echo "message:".$req->params["message"]." not found";        
});

$app->get("message:/length", function ($req) {
  echo 12;
});


$app->post("message", function ($req) {
  print_r($req->body);
  echo "successfully posted";
});



$app->listen();
*/
  