<?php
	class Router{
		static public function setUrl(){
			//设置URL路径
			$path = array_filter(explode("/",ltrim($_SERVER['PATH_INFO'],"/")));
			
			//判断$path的元素个数
			if(!empty($path)){
				if(count($path)==1){
					$_GET['m'] = $path[0];
				}
				if(count($path)==2){
					$_GET['m'] = $path[0];
					$_GET['a'] = $path[1];
				}
				if(count($path)>2){
					$_GET['m'] = array_shift($path);
					$_GET['a'] = array_shift($path);
					for($i=0;$i<count($path);$i=$i+2){
						$_GET[$path[$i]] = $path[$i+1];
					}
				}
			}
		}
	}