<?php




class yott{

    const yott_id = '13';
    const yott_key= 'da471019eb353a4133bc75476b0ccfb5';
    const yott_url= 'http://www.yott.cn/';

    //返回码
    protected $result = array(
        '0'=>'发送成功',
        '1000'=>'插入ProductsLoan表不成功',
        '1001'=>'插入products不成功',
        '1002'=>'Subject标识符重复',
        '1003'=>'缺乏（platform_id, subject_id, name, url）参数',
        '1004'=>'缺乏（borrow_username, account, period, apr, repay_style, addtime）参数',
        '1005'=>'缺乏（subject_id, platform_id）参数',
        '1006'=>'更新product表失败',
        '1007'=>'Key值错误'
    );

    //用户绑定回调
    public function bindUser($user,$phone,$result = 1){
        $str = 'name='.$user.'&phone='.$phone.'&result='.$result;
        $return['key'] = self::yott_key;
        $return['sign'] = $this->_encode($str, $return['key']);
        $result = $this->_userReturn($return);
        return $result;
    }

    //创建一条标记录
    //此接口的作用为：推送一条p2p数据到金融超市，当p2p平台有新标的时候需要调用此接口


    public function createP2p($arr){
        $arr['platform_id'] = self::yott_id;//*平台id(金融超市提供)
        $arr['platform_key'] = self::yott_key;//*密钥（金融超市提供）
        $json = '[' . json_encode($arr) . ']';
        $result = $this->_createP2p($json);
        return $result;
        //echo $result;
    }

    //登录解密
    public function decodeSign($sign){
        if(empty($sign)) return '';
        $result =  $this->_decrypt($sign, self::yott_key);
        if($result){
            return $result;
        }
        return $this->_decrypt(urldecode($sign), self::yott_key);

    }
    //更新标信息
    //投标及复审还款
    public function updateP2p($arr){
        $arr['platform_id'] = self::yott_id;
        $arr['platform_key'] = self::yott_key;
        $json = '['.json_encode($arr).']';
        $result = $this->_updateP2p($json);
        return $result;
    }
    //用户投资调用
    public function userTender($arr){

        $str = join('&',$arr);
        $return['key'] = self::yott_key;
        $return['tender'] = $this->_encode($str, $return['key']);
        $result = $this->_userTender($return);

        return json_decode($result);
    }

    protected function post_url($url, $type, $data, $time = 10){
        try{
            $ch = curl_init();
            if(false === $ch)
                throw new Exception('failed to initialize');
            curl_setopt($ch, CURLOPT_URL, self::yott_url.$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            if($data != ''){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time);

            if($type){
                curl_setopt($ch, CURLOPT_HEADER, $type);
            }else{
                curl_setopt($ch, CURLOPT_HEADER, false);
            }
            $content = curl_exec($ch);
            if (FALSE === $content)
                throw new Exception(curl_error($ch), curl_errno($ch));
            curl_close($ch);

            return $content;
        }catch (Exception $e){
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }

    }

    protected function _createP2p($arr){
        $result = $this->post_url("API/Index/BorrowList", 'json', $arr);
        return $result;
    }

    protected function _updateP2p($arr){
        $result = $this->post_url("API/Index/UpdateInfo", 'json', $arr);
        return $result;
    }

    protected function _userReturn($arr){
        $result = $this->post_url("API/Index/RegStatus", '', $arr);
        return $result;
    }

    protected function _userTender($arr){
        $result = $this->post_url("API/Index/UserTender", '', $arr);
        return $result;
    }

    protected function _encode($text,$key){
        return base64_encode(mcrypt_encrypt(MCRYPT_DES, $key,$this->_pkcs5Pad($text, mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $key));
    }

    protected function _decrypt($str,$key){
        return $this->_pkcs5Unpad(mcrypt_cbc(MCRYPT_DES, $key, base64_decode ($str), MCRYPT_DECRYPT, $key ));
    }

    protected function _pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    protected function _pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text))
            return false;

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;

        return substr($text, 0, - 1 * $pad);
    }
}