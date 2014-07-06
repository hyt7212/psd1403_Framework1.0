<?php
	class Create{
		public static function mkFile(){
			mkdir(ROOT_PATH.APP."/model",0755,true);
			mkdir(ROOT_PATH.APP."/controller",0755,true);
			mkdir(ROOT_PATH.APP."/view",0755,true);
			mkdir(ROOT_PATH.APP."/resource",0755,true);
			mkdir(ROOT_PATH.APP."/resource/css",0755,true);
			mkdir(ROOT_PATH.APP."/resource/js",0755,true);
			mkdir(ROOT_PATH.APP."/resource/images",0755,true);
			$str = <<<EOT
<?php
	class IndexAction extends Tpl{
		public function index(){
			echo "创建应用目录成功<br />";
			echo "创建MVC目录成功<br />";
			echo "创建IndexAction成功<br />";
		}
	}
EOT;


			file_put_contents(ROOT_PATH.APP."/controller/indexaction.class.php",$str);

			$info = <<<EOT
<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<title></title>
		<style>
			*{padding:0;margin:0}
			.info{
				width:280px;
				height:280px;
				border:1px solid #ccc;
				margin:0 auto;
				background:#eee;
				text-align:center;
				padding:10px;
			}
			.info .ico{
				font-size:70px;
				font-weight:bold;
			}
			.info .tips{
				height:80px;
				line-height:80px;
				font-size:18px;
				font-family:Microsoft Yahei;
				font-weight:700;
				
			}
			.timer{
				font-size:16px;
				font-weight:bold;
				color:#444;
			}
			.timer span{
				padding:0 10px;
				color:red;
				text-decoration:underline;
			}
			.redi{
				height:80px;
				line-height:80px;
				font-size:14px;
				color:#666;
			}
			.success{
				color:green;
			}
			.error{
				color:red;
			}
		</style>
	</head>
	<body>
		<div class="info <{\$class}>">
			<p class="ico"><{\$ico}></p>
			<p class="tips"><{\$info}></p>
			<p class="timer">
				<span><{\$sec}></span>秒后跳转
			</p>
			<p class="redi"><a href="<{\$app}>/<{\$redirect}>">如果没有跳转，点击这里跳转</a></p>
		</div>
	</body>
</html>

<script>
	var pageH = document.documentElement.clientHeight;
	var div = document.getElementsByTagName("div")[0];
	var span = document.getElementsByTagName("span")[0];
	div.style.marginTop = pageH/2-150+"px";
	
	var t = setInterval(function(){
		var i = parseInt(span.innerHTML);
		i--;
		if(i<=0){
			clearInterval(t);
			window.location.href="<{\$app}>/<{\$redirect}>";
		}
		span.innerHTML = i;
	},1000);
</script>



EOT;
			file_put_contents(APP."/view/info.html",$info);


		}
	}