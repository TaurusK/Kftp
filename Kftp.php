<?php
/**
 * ftp工具类
 * versions:1.0
 * author:k
 * date:2019/01/08
 */
class Kftp {

	public $ftp;

	public function __construct($url,$port='21',$timeout='90'){
		$this->ftp = ftp_connect($url,$port,$timeout);

	}

	/**
	 * ftp登录
	 */
	public function login($acc,$pwd){
		return ftp_login($this->ftp,$acc,$pwd);
	}

	/**
	 * 查看当前目录
	 */
	public function pwd(){
		return ftp_pwd($this->ftp);
	}

	/**
	 * 上传文件到指定目录
	 * 这里需要注意的是远程路径，远程路径不以./或/开头，而且如果指定了目录一定是要目录下有文件名的如XXX/test.txt
	 * 没有指定目录时这个远程路径就是你上传到远程后的文件名，如test.txt
	 * 上传成功返回true,否则php会抛出异常,所以判断时要用===true
	 */
	public function push($remote_file,$local_file,$mode=FTP_BINARY){

		$remote_file = $this->mkdir($remote_file);

		return ftp_put($this->ftp,$remote_file,$local_file,$mode);
	}

	/**
	 * 改变当前目录
	 */
	public function chdir($dir){
		return ftp_chdir($this->ftp,$dir);
	}

	/**
	 * 创建目录并当前目录变更到创建的目录中
	 * 成功返回路径的文件名,否则php会抛出异常并返回false,所以判断时要用!==false
	 */
	public function mkdir($path){

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
							throw new Exception("Kftp:变更（{$newDir}）目录失败，请检查权限");	
						}
					}else{   //创建新目录失败
						throw new Exception("Kftp:创建（{$dir}）目录失败，请检查权限");	
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