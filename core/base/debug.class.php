<?php
	class Debug{
		//储存错误信息的成员属性
		private static $error = array();
		private static $errNo = array(
			E_ERROR	 =>'运行时致命',
			E_WARNING	=>'运行时警告',
			E_PARSE	 =>'语法解析错误',
			E_NOTICE	=>'运行时提示',
			E_CORE_ERROR=>'PHP启动致命错误',
			E_CORE_WARNING =>'PHP启动时警告错误',
			E_COMPILE_ERROR => '编译时致命',
			E_COMPILE_WARNING => '编译时警告',
			E_USER_ERROR => '用户自定义的致命错误',
			E_USER_WARNING => '用户自定义的警告',
			E_USER_NOTICE => '用户自定义的提示',
			E_STRICT => '编码标准化警告'
			);
		//记录所有的sql语句
		private static $sql = array();
			
		//记录开始时间
		public static $start;
		//记录结束时间
		public static $end;
		
		//吸收错误
		public static function setError($errno,$errstr,$errfile,$errline){
			self::$error[] = "[<font color='red'>".self::$errNo[$errno]."</font>] : 在文件".$errfile."的第 ".$errline." 行，错误信息为：".$errstr;
		}
		
		static public function tpl(){
			$str = '<div style="width:90%%;border:1px solid #ccc;background:#eee;padding:10px;margin:20px auto;font-size:14px;font-family:Microsoft Yahei">%s<p />%s<p />%s<p />%s</div>';
			return $str;
		}
		
		//显示错误
		static public function showError(){
			$timer = "[<font color='green'>运行时间</font>] : ".self::spend()." 秒";
			
			$files = "[<font color='green'>加载文件</font>] : ".join("<br />",get_included_files());
			
			$sqls = "[<font color='green'>SQL语句</font>] : ".join("<br />",self::$sql);
			
			printf(self::tpl(),$timer,join("<br />",self::$error),$files,$sqls);
		}
		
		//开始时间记录
		static public function start(){
			self::$start = microtime(true);
		}
		
		//结束时间的记录
		static public function end(){
			self::$end = microtime(true);
		}
		
		//计算运行时间
		static public function spend(){
			return self::$end-self::$start;
		}
		
		//向self::$sql当中记录sql语句
		static public function setSql($sql){
			self::$sql[] = $sql;
		}
		
		//自定义错误信息
		static public function setUserError($str){
			self::$error[] = $str;
		}
		
	}
	
	
	
	