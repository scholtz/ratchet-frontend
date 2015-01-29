<?php
use RatchetFrontend\Frontend\Block;
namespace RatchetFrontend\Frontend;

Class BlockManagement{
	protected static $blocks = array();
	protected static $personalized = array();
	protected static $instances = array();
	public static function getTid($blockname){
		$block = BlockManagement::get($blockname, "");
		return $tid = URLParser::selectParameters($block->getUsesParams());
	}
	public static function get($name,$tid){
			//var_dump("BlockManagement::get:$name;$tid");
		try{
			//require_once("modules/File.php");
			if(file_exists($f= WEB_PATH."/php/".$name.".php")){
				//echo "getting $name\n";
				require_once($f);
			}else{
				//echo "file $f neexistuje\n";
			}
		
			if(isset(BlockManagement::$instances[$name][$tid])){
				return BlockManagement::$instances[$name][$tid];
			}else if(class_exists($name)){
				if(BlockManagement::$instances[$name][$tid] = new $name($name,$tid)){
					return BlockManagement::$instances[$name][$tid];
				}
			}else{
				if(BlockManagement::$instances[$name][$tid] = new Block($name,$tid)){
					return BlockManagement::$instances[$name][$tid];
				}
			}
			
		}catch(\Exception $exc){
			throw $exc; 
		}
		/*
		
		if(\File::exists($f = "templates/".$name.".html")){
			$uses = array();
			if($template == "profile"){
				$uses[] = "c";
			}
			return array("msg"=>"result","id"=>$id,"result"=>array("template"=>file_get_contents($f,true),"vars"=>$uses));
		}/**/
	}
	public static function subscribe(Block $block,Bool $personalized){
		BlockManagement::$blocks[$block->name()] = $block;
		BlockManagement::$personalized[$block->name()] = $personalized;
	}
	public static function setTemplate(String $BlockClass,String $template){
		BlockManagement::$blocks[$BlockClass]->setTemplate($template);
		foreach(BlockManagement::$instances as $usrsdata){
			foreach($usrsdata as $usr=>$class){
				$class->setTemplate($template);
			}
		}
	}
	public static function setData(String $BlockClass,String $UserId, Array $data){
		if(!$UserId){
			BlockManagement::$blocks[$BlockClass] = new $BlockClass;
		}else{
			BlockManagement::$instances[$BlockClass][$UserId]= new $BlockClass;
		}
	}
	
}
