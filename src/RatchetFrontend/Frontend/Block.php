<?php
namespace RatchetFrontend\Frontend;

class Block{
	protected static $i = 1;
	protected static $MustacheEngine = null;
	protected $template = "";
	protected $data = array();
	protected $tid = "";
	protected $clients = array();
	protected $usesparams = array();
	public function getUsesParams(){
		return $this->usesparams;
	}
	public function __construct($name = "", $tid = "", $template=""){
		$this->template = $template;
		if(!$name) $name = get_class($this);
		$this->name = $name;
		$this->data = array(""=>array());
		if(!$template){ 
		require_once("modules/File.php");
		if(!\File::exists($f = "templates/".$name.".html")){
			echo "Template ".$name." not found!\n";
			throw new \Exception("Template ".$name." not found!");
		}
		
		$this->template = file_get_contents("templates/".$name.".html",true);}
		$this->tid = $tid;
		
		$this->init();
	}
	protected function init(){
	
	}
	protected $name = "";
	public function name(){
		return $this->name;
	}
	public function setTemplate(String $template){
		$this->template = $template;
		$this->notify();
	}
	public function getTemplate(){
		return $this->template;	
	}
	public function setData(Array $data, $namespace=""){
		$this->data[$namespace] = $data;
		$this->notify($namespace);
	}
	public function getData($namespace=""){
		return $this->data[$namespace];	
	}
	public function getInnerBlocks($namespace=""){
		$ret=array();
		$pos = 0;
		while(($pos = strpos($this->template,"{{{",$pos)) !== false){
			$pos+=3;
			$pos2 = strpos($this->template,"}}}",$pos);
			$item = trim(substr($this->template,$pos,$pos2-$pos));
			if(substr($item,0,4)=="url:"){
				
			}else if(!isset($this->data[$namespace][$item])){ // only if we do not use the variable with the same name, try to load template
				$templateid = URLParser::get($item);
				try{
					$tid = BlockManagement::getTid($templateid);
					if($itemcl = BlockManagement::get($templateid,$tid)){
						$ret[] = $itemcl;
					}
				}catch(Exception $exc){
				}
			}
		}
		return $ret;
	}
	public function get($namespace=""){
		if(Block::$MustacheEngine == null) 
			Block::$MustacheEngine = new \Mustache_Engine(array(    'escape' => function($value) {return $value;},));
		
		$data = @$this->data[$namespace];
		if(!$data) $data = array();
		
		$pos = 0;
		while(($pos = strpos($this->template,"{{{",$pos)) !== false){
			$pos+=3;
			$pos2 = strpos($this->template,"}}}",$pos);
			$item = trim(substr($this->template,$pos,$pos2-$pos));
			if(substr($item,0,4)=="url:"){
				$data[$item] = URLParser::add(substr($item,4));
			}else if(!isset($this->data[$namespace][$item])){ // only if we do not use the variable with the same name, try to load template
				
				
				$templateid = URLParser::get($item);
				try{
					$tid = BlockManagement::getTid($templateid);
					if($itemcl = BlockManagement::get($templateid,$tid)){
						$itemid = $item;
						if($p = strpos($item,":")){
							$itemid = substr($item,0,$p);
						}
						$data[$item] = '<span id="T_'.$itemid.'">'.$itemcl->get().'</span>';
					}
				}catch(Exception $exc){
				}
			}
		}
		//var_dump($data);
		return Block::$MustacheEngine->render($this->template,$data);
	}
	public function notify($namespace=""){
		//$this->init();
		$R = array("msg"=>"changed","id"=>Block::$i++,"changed"=>array("template"=>$this->name(),"tid"=>$this->tid,"data"=>$this->getData($namespace)));
		$data = json_encode($R);
		
		if(isset($this->clients[$namespace]))
		foreach($this->clients[$namespace] as $client){
			echo "notify: $data\n";
			if($client){
				$r = $client->send($data);
				//echo "result from sending:";
				//var_dump($r);
				//exit;
			}
		}
	}
	public function subscribe($client,$namespace){
		$this->clients[$namespace][] = $client;
		return true;
	}
	public function unsubscribe($client,$namespace){
		foreach($this->clients[$namespace] as $k=>$cl){
			if($cl->resourceId == $client->resourceId){
				unset($this->clients[$namespace][$k]);
				return true;
			}
		}
		return false;
	}
}
