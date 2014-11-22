<?php
namespace RatchetFrontend\Frontend;

class Block{
	protected static $MustacheEngine = null;
	protected $template = "";
	protected $data = array();
	protected $clients = array();
	public function __construct($template="", Array $data = array()){
		$this->template = $template;
		if(!$template){ $template = file_get_contents("templates/".get_class($this).".html");}
		$this->data = $data;
	}
	public function setTemplate(String $template){
		$this->template = $template;
		$this->notify();
	}
	public function setData(Array $data, $namespace=""){
		$this->data[$namespace] = $data;
		$this->notify($namespace);
	}
	public function get($namespace=""){
		if(Block::$MustacheEngine == null) 
			Block::$MustacheEngine = new Mustache_Engine;
		return Block::$MustacheEngine->render($this->template,$this->data); 
	}
	public function notify($namespace=""){
		$R = array();
		$R["subscription"] = array("name"=>get_class($this),"value"=>$this->get($namespace));
		$data = json_encode($R);
		
		foreach($this->clients as $client){
			$client->send($data);
		}
	}
	public function subscribe($client,$namespace){
		$this->clients[$namespace][] = $client;
		return true;
	}
	public function unsubscribe($client,$namespace){
		foreach($this->clients as $nm=>$clients){
			if($namespace && $nm != $namespace) continue;
			foreach($clients as $k=>$cl){
				if($cl === $client) unset($this->clients[$nm][$k]);
				return true;
			}
		}
		return false;
	}
}
