<?php
// 全局设置
class CapitalAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
        $typearr = array(
            1=>'邮币卡电子现货质押',
            2=>'保证金质押'
        );
        $map = array();
        import("ORG.Util.Page");
        $count = M("applylog")->where($map)->count();
        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        $list = M("applylog")->where($map)->limit($Lsql)->order("id DESC")->select();

        foreach($list as &$val){
            $val['type_name'] = $typearr[$val['optype']];
        }
        $this->assign("list",$list);
        $this->assign("page",$page);
        $this->display();
    }

    public function view(){
        $id = intval($_GET['id']);
        $data = M('applylog')->find($id);
        $this->assign("data",$data);
        $this->display();
    }
}
?>