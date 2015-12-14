<?php

class GCommonAction extends Action{

    function _initialize(){
        if(false == ListMobile() && allowPhone()){
            //Header("Location:http://m.ishoutou.com".$_SERVER['REQUEST_URI']);
            $url = "http://m.ishoutou.com".$_SERVER['REQUEST_URI'];
            echo "<script language='javascript' type='text/javascript'>";
            echo "window.location.href='$url'";
            echo "</script>";
            exit;
        }
        $filename = "/home/www/default/AllowList.txt";
        if(file_exists($filename)){
            $handle = fopen($filename,'rb');
            while(!feof($handle)){
                $contxt123[] = fgetss($handle, 1024);
            }

            foreach($contxt123 as $val){
                $tt = trim($val);
                if(false === empty($tt)){
                    $tmparr[]= $tt;
                }
            }
            $ipadds = get_client_ip();
            if(!in_array($ipadds,$tmparr)){
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
                die('HTTP/1.1 404 Not Found');
            };
        }
        $this->tempswich();
    }

    protected function tempswich(){
        //如果是手机登录修改默认模版文件路径
        if(ListMobile()){
            $TEMPLATE_NAME = explode('/default/',C('TEMPLATE_NAME'));
            $tempfile = array_pop($TEMPLATE_NAME);
            $filename = implode('/default/',$TEMPLATE_NAME).'/touch/'.$tempfile;
            if(file_exists($filename)){
                C('DEFAULT_THEME','touch');
                C('TEMPLATE_NAME',$filename);
            }
        }
    }
}