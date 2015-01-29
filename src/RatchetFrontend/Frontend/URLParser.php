<?php
namespace RatchetFrontend\Frontend;

class URLParser{
	public static function get($templateid){
		$arr = explode("/",URLParser::getCurrent());
		$itembase = $templateid;
		$replace = $templateid;
		
		if($p = strpos($templateid,":")){
			$itembase = substr($templateid,0,$p);
			$replace = substr($templateid,$p+1);
		}
		
		foreach($arr as $item){
			$itema = explode(":",$item);
			if(count($itema) < 2) continue;
			if($itema[0] == $itembase){
				return $itema[1];
			}
		}
		return $replace;
	}
	public static function getCurrent(){
		return @$_SERVER["REQUEST_URI"];
	}
	public static function parse($url=""){
		if(!$url) $url = URLParser::getCurrent();
		$arr = explode("/",$url);
		$ret = array();
		foreach($arr as $item){
			if(!$item) continue;
			if($p = strpos($item,"=")){
				$ret["var"][substr($item,0,$p)] = substr($item,$p+1);
			}else{
				if($p = strpos($item,":")){
					$ret["tmpl"][substr($item,0,$p)] = substr($item,$p+1);
				}else{
					$ret["tmpl"][$item] = $item;
				}
			}
		}
		if(isset($ret["var"])) foreach($ret["var"] as $k=>$v){
			$_GET[$k] = $v;
			$_REQUEST[$k] = $v;
		}
		
		return $ret;
	}
	public static function merge(Array $arr){
		$ret="";
		if(isset($arr["tmpl"])){
			foreach($arr["tmpl"] as $k=>$v){
				if($k==$v){
					$ret.='/'.urlencode($k);
				}else{
					$ret.='/'.urlencode($k).":".urlencode($v);
				}
			}
		}
		if(isset($arr["var"])){
			foreach($arr["var"] as $k=>$v){
				$ret.='/'.urlencode($k)."=".urlencode($v);
			}
		}
		return $ret;
		
	}
	public static function add($param){
		$arr = URLParser::parse(URLParser::getCurrent());
		$parama = URLParser::parse($param);
		foreach($parama as $vartmpl=>$arr2){
			foreach($arr2 as $k=>$v){
				$arr[$vartmpl][$k] = $v;
			}
		}
		return URLParser::merge($arr);
	}
	public static function addVariables(Array $param){	
		
		$arr = URLParser::parse(URLParser::getCurrent());
		foreach($param as $k=>$v){
			if(!$v){
				if(isset($arr["var"][$k])) unset($arr["var"][$k]);
			}else{
				$arr["var"][$k] = $v;
			}
		}
		return URLParser::merge($arr);
	}
	public static function selectParameters($paramarr) {
		if(!$paramarr) return "";
		$urlarr = URLParser::parse();
		$ret = "";
		foreach($paramarr as $k=>$key){
			if(isset($urlarr["var"][$key])){
				$ret .= "/".$key."=".$urlarr["var"][$key];
			}
		}
		return $ret;
	}
}
