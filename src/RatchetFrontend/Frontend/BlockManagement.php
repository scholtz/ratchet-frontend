<?php

namespace RatchetFrontend\Frontend;

Class BlockManagement{
	protected static $blocks = array();
	protected static $personalized = array();
	protected static $instances = array();
	public static function subscribe(Block $block,Bool $personalized){
		BlockManagement::$blocks[get_class($block)] = $block;
		BlockManagement::$personalized[get_class($block)] = $personalized;
	}
	public static function setTemplate(String $BlockClass,String $template){
		BlockManagement::$blocks[$BlockClass]->setTemplate($template);
		foreach(BlockManagement::$instances as $cl as $usrsdata){
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
