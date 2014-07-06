<?php
	function M($tabName){
		//判断usermodel是否存在
		if(file_exists(ROOT_PATH.APP."/model/".$tabName."model.class.php")){
			$m = $tabName."model";
			return new $m($tabName);
		}else{
			return new Model($tabName);
		}
	}

	function dump($data){
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}
	
	function p($data){
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}