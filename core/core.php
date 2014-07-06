<?php
	header("content-type:text/html;charset=utf-8");
	date_default_timezone_set("PRC");
	//关闭错误
	ini_set("display_errors","on");
	
	//2.设置路径
	define("ROOT_PATH",str_replace("\\","/",dirname(dirname(__FILE__)))."/");
	
	//加载配置文件
	include ROOT_PATH."config.inc.php";
	include ROOT_PATH."core/base/functions.php";
	
	//3.设置加载路径
	$include_path = get_include_path();
	$include_path .= PATH_SEPARATOR.ROOT_PATH.APP."/model";
	$include_path .= PATH_SEPARATOR.ROOT_PATH.APP."/controller";
	$include_path .= PATH_SEPARATOR.ROOT_PATH."org";
	$include_path .= PATH_SEPARATOR.ROOT_PATH."core/libs";
	$include_path .= PATH_SEPARATOR.ROOT_PATH."core/libs/sysplugins";
	$include_path .= PATH_SEPARATOR.ROOT_PATH."core/base";
	set_include_path($include_path);
	
	//4.设置自动加载
	function __autoload($className){
		//判断加载的类是不是smarty的核心类
		if(strtolower(substr($className,0,6))=="smarty"&&strtolower($className)!="smarty"){
			include strtolower($className).".php";
		}else{
			include strtolower($className).".class.php";
		}
	}
	//获取程序开始运行的时间
	Debug::start();
	
	//吸收错误
	set_error_handler(array('Debug','setError'));
	
	//设置URL
	Router::setUrl();
	
	//1.接收参数
	$m = empty($_GET['m'])?'index':strtolower($_GET['m']);
	$a = empty($_GET['a'])?'index':strtolower($_GET['a']);
	
	//设置自动创建
	if(!file_exists(ROOT_PATH.APP)){
		Create::mkFile();
	}

	//5.实例化对象
	$m = ucfirst($m)."Action";
	$mod = new $m;
	
	//初始化smarty
	$mod->setTemplateDir(ROOT_PATH.APP."/view");
	$mod->setCompileDir(ROOT_PATH."runtime/compile");
	$mod->setCacheDir(ROOT_PATH."runtime/cache");
	$mod->addPluginsDir(ROOT_PATH."core/plugins");
	$mod->caching = CACHE;
	$mod->cache_lifetime = CACHE_LIFETIME;
	$mod->left_delimiter = LDELIM;
	$mod->right_delimiter = RDELIM;
	
	//6.调用方法
	$mod->$a();
	
	//记录程序运行结束的时间
	Debug::end();
	
	if(defined("DEBUG")&&DEBUG==1){
		//显示错误至一个容器当中
		Debug::showError();
	}
	
	
	
	
	
	
	
	
	
	
	
	