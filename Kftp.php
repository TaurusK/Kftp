<?php
/**
 * ftp工具类
 * versions:1.2.3
 * author:k
 * date:2019/01/09
 */
class Kftp {

	public $ftp;

	public function __construct($url,$acc,$pwd,$port='21',$timeout='90'){
		try {
			if(empty($acc) && empty($pwd)){
				throw new Exception("Kftp:登录名或密码未传，请登录后在进行相关操作 ");
			}elseif(empty($url)){
				throw new Exception("Kftp:ftp地址未传 ");
			}elseif(($this->ftp = @ftp_connect($url,$port,$timeout)) === false){
				throw new Exception("Kftp:ftp连接失败，请检查ftp地址是否正确 ");
			}elseif($this->login($acc,$pwd) !== true){
				throw new Exception("Kftp:登录失败，请检查账号或密码是否正确 ");
			}
			@ftp_pasv($this->ftp,1); // 打开被动模拟,【注意】一定要打开被动模式，要先登录在打开
		}catch(Exception $e){
			echo $e->getMessage();
		}

	}

	/**
	 * [login ftp登录]
	 * @param  string $acc [必选]ftp账号
	 * @param  string $pwd [必选]ftp密码
	 * @return boolean     
	 */
	private function login($acc,$pwd){

		return @ftp_login($this->ftp,$acc,$pwd);
	}

	/**
	 * [pwd 查看当前目录]
	 * @return 成功返回当前目录，失败抛出异常
	 */
	public function pwd(){
		return ftp_pwd($this->ftp);
	}

	/**
	 * [push 上传文件到指定目录]
	 * @param  string $remote_file [必选]远程路径
	 * @param  string $local_file  [必选]本地文件路径
	 * @param  int    $mode        [可选]上传模式，有FTP_ASCII和FTP_BINARY两种
	 * @return 
	 * 
	 * 这里需要注意的是远程路径，远程路径不以./或/开头，而且如果指定了目录一定是要目录下有文件名的如XXX/test.txt
	 * 没有指定目录时这个远程路径就是你上传到远程后的文件名，如test.txt
	 * 上传成功返回true,否则php会抛出异常,所以判断时要用===true
	 */
	public function push($remote_file,$local_file,$mode=FTP_BINARY){

		try{
			//检查本地目录或文件是否存在
			if(!file_exists($local_file)){
				throw new Exception("Kftp->push():不能打开本地文件（{$local_file}），目录或文件不存在，请检查目录或文件和权限 ");	
			}
			//检查远程路径中的目录是否存在，不存在则创建
			$remote_file_new = $this->createFileDir($remote_file);
			if(@ftp_put($this->ftp,$remote_file_new,$local_file,$mode) !==true){
				throw new Exception("Kftp->push():文件（{$remote_file_new}），创建失败，请检查目录或文件和权限 ");
			}
		}catch(Exception $e){
			echo $e->getMessage();
			return false;
		}

		return true;
	}

	/**
	 * [chdir 改变当前目录]
	 * @param  string $dir [必选]指定目录
	 * @return 成功返回true，失败抛出异常
	 */
	public function chdir($dir){

		return ftp_chdir($this->ftp,$dir);
	}

	/**
	 * [createFileDir 创建目录并当前目录变更到创建的目录中]
	 * @param  string $path [必选]文件路径
	 * @return 
	 *
	 * 成功返回路径的文件名,否则php会抛出异常并返回false,所以判断时要用!==false
	 */
	public function createFileDir($path){

		try{
			//获取各层目录
			$parts = explode('/', $path);
			$remote_file = array_pop($parts);  //弹出文件名
			//halt($remote_file);

			//循环检查目录是否存在并创建
			foreach($parts as $dir){
				if(@$this->chdir($dir)!==true){   //注意这里必须用@抑制错误才会在目录不存在时返回false,不然php会抛出一个异常
												  //然后会被下面的异常捕抓器捕抓转而执行异常处理的操作，而后面的创建目录操作将不会执行
												  //
												  
					$newDir = @ftp_mkdir($this->ftp, $dir);   //这里同样需要使用@阻止当创建目录失败时php抛出的异常,因为php提示的错误信息
															  //不够详细，我需要输出自定义的详细异常信息
															  //
					if($newDir !==false){  //创建新目录成功并变更到这个目录中
						if($this->chdir($newDir)!==true){
							//变更目录失败需要抛出异常
							throw new Exception("Kftp->createFileDir():变更（{$newDir}）目录失败，请检查权限 ");	
						}
					}else{   //创建新目录失败
						throw new Exception("Kftp->createFileDir():创建（{$dir}）目录失败，请检查权限 ");	
					}
				}
			}

		}catch(Exception $e){
			echo $e->getMessage();
			return false;
		}
		return $remote_file;
	}


}