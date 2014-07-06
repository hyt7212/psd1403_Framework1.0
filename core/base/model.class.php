<?php
	/**
	 *	描述：数据库操作类
	 *	作用：操作数据库，执行增、删、改、查
	 *	时间：2014-06-05
	 *
	 */
	class Model{

		private $dbHost;		//数据库连接地址
		private $dbUser;		//数据库连接用户名
		private $dbPass;		//数据库连接密码
		private $error;			//数据库执行错误信息
		private $dbChar;		//数据库字符集
		private $dbName;		//数据库名称
		private $dbPrefix;		//数据表前缀
		private $link;			//数据库连接资源
		private $res;			//结果集资源
		private $tabName;		//数据表名称
		private $sql;			//SQL语句
		private $dbFields;		//数据表的字段列表
		private $fields;		//显示的字段
		private $where;			//保存条件
		private $order;			//保存排序
		private $limit;			//保存limit
		
		/**
		 *	描述：构造方法
		 *	作用：初始化成员属性
		 *	@param string $dbHost 数据库地址
		 *	@param string $dbUser 数据库用户名
		 *	@param string $dbPass 数据库密码
		 *	@param string $dbChar 数据库字符集
		 *	@param string $dbName 数据库名称
		 *	@param string $dbPrefix 数据表前缀
		 *
		 */
		public function __construct($tabName,$dbHost=DB_HOST,$dbUser=DB_USER,$dbPass=DB_PASS,$dbChar=DB_CHARSET,$dbName=DB_NAME,$dbPrefix=DB_PREFIX){
			$this->tabName = $tabName;
			$this->dbHost = $dbHost;
			$this->dbUser = $dbUser;
			$this->dbPass = $dbPass;
			$this->dbChar = $dbChar;
			$this->dbName = $dbName;
			$this->dbPrefix = $dbPrefix;
			$this->link = $this->connect();
			$this->getColumn();
		}
		
		/**
		 *	描述：数据库连接初始化方法
		 *	作用：连接数据库，设置初始值
		 *	@return mixed $link/false成功返回连接资源，失败返回假
		 *
		 */
		private function connect(){
			//1.连接数据库
			$link = mysql_connect($this->dbHost,$this->dbUser,$this->dbPass);
			
			//2.判断错误
			if(!$link){
				$this->error = mysql_errno().mysql_error();
				return false;
			}
			//3.设置字符集
			mysql_set_charset($this->dbChar);
			
			//4.选择数据库
			mysql_select_db($this->dbName);
			
			//返回连接资源
			return $link;
		}
		
		private function getColumn(){
			//判断缓存文件是否存在，如果缓存文件存在，直接获取缓存文件的内容
			if(file_exists(ROOT_PATH."runtime/cache/".$this->dbPrefix.$this->tabName.".php")){
				$this->dbFields = include(ROOT_PATH."runtime/cache/".$this->dbPrefix.$this->tabName.".php");
			}else{
			
				//否则查询
				$data = $this->query("desc ".$this->dbPrefix.$this->tabName);
				foreach($data as $val){
					if($val['Key']=='PRI'){
						$fields['_pk'] = $val['Field'];
					}
					if($val['Extra']=='auto_increment'){
						$fields['_auto'] = $val['Field'];
					}
					$fields[] = $val['Field'];
				}
				
				//写入缓存
				$str = "<?php\n\treturn ".var_export($fields,true).";";
				file_put_contents(ROOT_PATH."runtime/cache/".$this->dbPrefix.$this->tabName.".php",$str);
				
				$this->dbFields = $fields;
			}
			
		}
		
		/**
		 *	描述：query方法
		 *	作用：执行查询的SQL语句
		 *	@param string $sql 要执行的SQL语句
		 *	@return mixed 成功返回数组，失败返回假
		 *
		 */
		public function query($sql){
			Debug::setSql($sql);
			$res = mysql_query($sql);
			if(is_resource($res)){
				//解析结果集资源
				$this->res = $res;
				//判断结果集资源当中的记录行数是否大于1或者等于1，或者等于0
				if(mysql_num_rows($res)==0){
					//如果查询的结果没有记录，返回空数组
					return array();
				}elseif(mysql_num_rows($res)==1){
					//解析一条记录，将一维数组返回
					return mysql_fetch_assoc($res);
				}else{
					while($row=mysql_fetch_assoc($res)){
						$data[] = $row;
					}
					//返回二维数组
					return $data;
				}
			}else{
				//将错误信息存储
				$this->error = mysql_errno().mysql_error();
				//返回false
				return false;
			}
		}
		
		/**
		 *	描述：execute方法
		 *	作用：执行没有结果集的sql语句，返回受影响行数
		 *	@param string $sql 要执行的SQL语句
		 *	@return mixed 成功返回受影响行数，失败返回假
		 *
		 */
		public function execute($sql){
			Debug::setSql($sql);
			$res = mysql_query($sql);
			if($res){
				//执行sql语句成功，返回受影响行数
				return mysql_affected_rows();
				
			}else{
				//执行sql语句失败，保存错误信息，返回false
				$this->error = mysql_errno().mysql_error();
				return false;
			}
		}
		
		//insert方法
		//array('username'=>'zhangsan','password'=>'123')
		//insert into user(username,password) values('zhangsan','123')
		public function insert($data=null){
			
			//判断是否有参数传入
			if(is_null($data)){
				$data = $_POST;
			}
			//判断参数是否为数组
			if(!is_array($data)){
				return false;
			}
			//过滤数组（对比缓存字段）
			//$this->dbFields
			//array("_pk"=>'id','_auto'=>'id',0=>id,1=>username,2=>password)
			
			//$data
			//array('username'=>'zhangsan','password'=>'123','aaa'=>'bbb')
			foreach($data as $key=>$val){
				if(!in_array($key,$this->dbFields)){
					unset($data[$key]);
				}
			}
			
			//组装字段部分
			$key = join(",",array_keys($data));
			//组装值部分
			$val = "'".join("','",$data)."'";
			//组装sql语句
			$this->sql = "insert into ".$this->dbPrefix.$this->tabName."($key) values($val)";
			//执行sql语句，返回受影响行数
			return $this->execute($this->sql);
		}
		//1.0--2.0
		//delete from user where id=1
		//delete from user where id>1 order by id desc limit 3
		//$m->delete('id>1');
		//$m->delete(array('id'=>1));
		//$m->delete(array('id'=>1,'username'=>'zhangsan'));
		//$m->delete(1);
	
		public function delete($where='',$order='',$limit=''){
		
			//组装$w
			if(empty($this->where)){
				$this->where($where);
				if(empty($this->where)){
					$this->error = "必须传递条件";
					return false;
				}
			}
			
			//拼装$o
			if(empty($this->order)){
				$this->order($order);
			}
			
			//拼装$l
			if(empty($this->limit)){
				$this->limit($limit);
			}
			
			$this->sql = "delete from ".$this->dbPrefix.$this->tabName.$this->where.$this->order.$this->limit;
			
			//执行sql语句
			return $this->execute($this->sql);
			
		}
		
		//update user set username='',password='' where id=1
		//update user set username='',password='' where id>1 order by id desc limit 3
		//$m->update();
		//$m->update(array('username'=>'zhangsan','password'=>'123','id'=>1,'aaa'=>'bbb'));
		public function update($data=null,$where=null,$order='',$limit=''){
			//判断$data是否为null
			if(is_null($data)){
				$data = $_POST;
			}
			if(!is_array($data)){
				$this->error = "您传入的数据不合法！";
				return false;
			}
			foreach($data as $key=>$val){
				//过滤字段
				if(!in_array($key,$this->dbFields)){
					continue;
				}
				//判断是否包含主键
				if($key===$this->dbFields['_pk']){
					$this->where = ' where '.$key."='".$val."'";
					continue;
				}
				//组装set
				//set username='zhangsan',password='123'
				$set .= $key."='".$val."',";
			}
			$set = " set ".rtrim($set,",");
			
			//组装$w
			if(empty($this->where)){
				$this->where($where);
			}
			
			//拼装$o
			if(empty($this->order)){
				$this->order($order);
			}
			
			//拼装$l
			if(empty($this->limit)){
				$this->limit($limit);
			}
			
			//组装sql语句
			$this->sql = "update ".$this->dbPrefix.$this->tabName.$set.$this->where.$this->order.$this->limit;

			//执行sql语句
			return $this->execute($this->sql);
			
		}
		
		//select * from user
		//select id,username from user where id=1 order by id desc limit 0,5
		//$m->select('id,name,AAA')
		//$m->select(array('id','name','aaa'))
		//$this->dbFields array('_pk'=>,'_auto'=>)
		public function select($field='',$where='',$order='',$limit=''){
			//处理$f
			if(empty($this->fields)){
				$this->field($field);
			}
			
			//组装$w
			if(empty($this->where)){
				$this->where($where);
			}
			
			//拼装$o
			if(empty($this->order)){
				$this->order($order);
			}
			
			//拼装$l
			if(empty($this->limit)){
				$this->limit($limit);
			}
			
			$this->sql = "select ".$this->fields." from ".$this->dbPrefix.$this->tabName.$this->where.$this->order.$this->limit;
			
			//判断获取到数组是否为一维数组
			$data = $this->query($this->sql);
			if(!is_array($data[0])){
				$data = array($data);
			}
			
			return $data;
		}
		
		//计算记录总数
		//select count(*) from user
		//select count(*) from user where id=1
		public function total($where=''){
			//组装$w
			if(empty($this->where)){
				$this->where($where);
			}
		
			$this->sql = "select count(*) as c from ".$this->tabName.$this->where;
			
			//执行sql
			$count = $this->query($this->sql);
			return (int)$count['c'];
		}
		
		public function findOne($field='',$where='',$order='',$limit=''){
			
			$data = $this->select($field='',$where='',$order='',$limit='');

			if(is_array($data[0])){
				return $data[0];
			}else{
				return $data;
			}
		}
		
		public function field($field){
			//处理$f
			if(empty($field)){
				$f = "*";
			}else{
				if(is_array($field)){
					foreach($field as $key=>$val){
						if(!in_array($val,$this->dbFields)){
							unset($field[$key]);
						}
					}
					if(empty($field)){
						$f = "*";
					}else{
						$f = join(",",$field);
					}
				}
				if(is_string($field)){
					$fields = explode(",",$field);
					foreach($fields as $key=>$val){
						if(!in_array($val,$this->dbFields)){
							unset($fields[$key]);
						}
					}
					if(empty($fields)){
						$f = "*";
					}else{
						$f = join(",",$fields);
					}
				}
			}
			$this->fields = $f;
			return $this;
		}
		
		public function where($where){
			//组装$w
			//拼装$where
			//判断$where是否为空
			if(empty($where)){
				$w = "";
			}else{
			
				//判断$where是一个字符串
				if(is_string($where)){
					$w = " where ".$where;
				}
				
				//判断$where是一个数组
				//array('id'=>1,'username'=>'zhangsan') id=1 and username=zhangsan
				//array(array('id'=>1,'age'=>20),array('username'=>'zhangsan'))   id=1 or username='zhangsan'
				if(is_array($where)){
					foreach($where as $key=>$val){
						if(!is_array($val)){
							$w .= $key."='".$val."' and ";
						}else{
							foreach($val as $k=>$v){
								$this->w .= $k."='".$v."' and ";
							}
							$w = substr($w,0,-4)." or ";
						}
					}
					$w = " where ".substr($w,0,-4);
				}
				
				//判断$where是一个整型
				if(is_int($where)){
					$w = " where ".$this->dbFields['_pk']."=".$where;
				}
			}
			$this->where = $w;
			return $this;
		}
		
		public function order($order){
			//拼装$o
			//组装$order
			//组装order  order by age asc,id desc
			//$m->delete(1,'age desc')
			//$m->delete(1,array('age'=>'asc','id'=>'desc'))
			
			//如果$order不为空
			if(!empty($order)){
				//字符串
				if(is_string($order)){
					$o = " order by ".$order;
				}
				//数组
				if(is_array($order)){
					foreach($order as $key=>$val){
						$o .= $key." ".$val.",";
					}
					$o = rtrim($o,",");
					$o = " order by ".$o;
				}
			}
			$this->order = $o;
			return $this;
		}
		
		public function limit($limit){
			//拼装$l
			//组装limit
			//组装limit  limit 3   limit 0,5
			//$m->delete(1,'id desc',3)
			//$m->delete(1,'id desc','3,5')
			//$m->delete(1,'id desc',array('a'))
			//$m->delete(1,'id desc',array(0,5))
			
			if(!empty($limit)){
				//整型
				if(is_int($limit)){
					$l = " limit ".$limit;
				}
				//字符串
				if(is_string($limit)){
					$l = " limit ".$limit;
				}
				//数组
				if(is_array($limit)){
					if(count($limit)==1){
						$l = " limit ".intval($limit[0]);
					}else{
						$l = " limit ".intval($limit[0]).",".intval($limit[1]);
					}
				}
			}
			$this->limit = $l;
			return $this;
		}
		
		/**
		 *	描述：getError方法
		 *	作用：获取错误信息
		 *	@return string $error 返回错误信息
		 *
		 */
		public function getError(){
			return $this->error;
		}
		
		/**
		 *	描述：析构方法
		 *	作用：关闭数据库连接，释放结果集资源
		 *
		 */
		public function __destruct(){
			if(is_resource($this->res)){
				mysql_free_result($this->res);
			}
			//8.关闭数据库
			mysql_close($this->link);
		}
		
	}
	
	
	

	
	