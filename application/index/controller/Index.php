<?php
namespace app\index\controller;

use think\Db;
use think\Session;

class Index extends Base
{

    public function index()
    {
        //显示管理员名称
        $this->view->assign("my_groups", get_group(get_group_id(Session::get('admin_id'))));

        $this->view->assign('title', '教学管理系统');

        //获得权限菜单
        $menu = $this->menu();
        $this->view->assign('menu_list',$menu);

        return $this->view->fetch();
    }




    /**
     * 管理菜单
     * @return \think\response\View
     */
    public function menu()
    {
        //先获取当前管理员的ID
        $admin_id = Session::get('admin_id');
        if($admin_id == 1){
            $pri = Db::name('privilege')->select();
        }else{
            $pre = Config('databases.prefix');
            $pri = Db::name('role_privilege')->field("p.*")->alias('a')
                ->join("{$pre}privilege p, a.pri_id=p.id")
                ->join("{$pre}admin_role c, a.role_id=c.role_id")
                ->where("c.admin_id={$admin_id}")->select();
        }

        $btn = array(); //取前两级的权限放入该数组当中

        //从所有的权限中取出顶级的权限
        foreach($pri as $k => $v )
        {
            //找顶级为0的权限
            if ($v['parent_id'] == 0)
            {
                //再循环把这个顶级的子权限
                foreach ($pri as $k1 => $v1)
                {
                    if ($v1['parent_id'] == $v['id']) {
                        $v['children'][] = $v1;
                    }
                }
                $btn[] = $v;
            }
        }
        return $btn;
    }





    public function main()
    {
        return $this->view->fetch();
    }



    /**
     * ajax 修改指定表数据字段  一般修改状态 比如 是否推荐 是否开启 等 图标切换的
     * table,id_name,id_value,field,value
     */
    public function changeTableVal()
    {
        $table = input('table'); // 表名
        $id_name = input('id_name'); // 表主键id名
        $id_value = input('id_value'); // 表主键id值
        $field  = input('field'); // 修改哪个字段
        $value  = input('value'); // 修改字段值
        DB::table($table)->where("$id_name = $id_value")->update(array($field=>$value)); // 根据条件保存修改的数据
    }


}
