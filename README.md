# Kftp
php ftp上传类

文档完善中......

使用demo

```php
 public function ftp_test(){
        $url = 'qxu1146470112.my3w.com';
        $acc = 'qxu1146470112';
        $pwd = '';
        $ftp = new Kftp($url);

        //登录ftp
        if($ftp->login($acc,$pwd)){
            echo '登录成功';
        }else{
            echo '登录失败';
        }

         //dump(@$ftp->chdir('myfolder'));die;
        
        //halt(getcwd());
        //dump($ftp->mkdir('myfolder1'));
        //查看当前目录
        echo $ftp->pwd();
        //测试上传文件
        if($ftp->push('myfolder/b/test_1.txt','test_ftp.txt')){
            echo '上传成功';
        }else{
            echo '上传失败';
        }

    }
```

