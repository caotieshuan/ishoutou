<?php
// 全局设置
class RongziAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
   public function index()
    {
		$field= true;
		$map = array();
		$this->_list(D('Rongzi'),$field,$map,'id','DESC');
        $this->display();
    }

    public function index2()
    {
		$field= true;
		$map['type'] = 2;
		$this->_list(D('Comment'),$field,$map,'id','DESC');
        $this->display('index');
    }

	public function _EditFilter($m){
		$result = M('rongzi')->where("id={$m}")->setField("is_read", 1);
		
	}

}
?>