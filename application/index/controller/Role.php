<?php
// +----------------------------------------------------------------------
// | Blog [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://lexiang123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 2275840206@qq.com
// +----------------------------------------------------------------------
// | Time : ${DATA}下午 1:46
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Db;
use think\Request;
use think\Controller;
use app\index\model\Role as RoleModel;

class Role extends Base
{

    //角色信息列表
    public function roleList()
    {
        //获取记录数量
        $this->view->count = RoleModel::count();

        $pre = config('database.prefix');
        $roleList = Db::name('role')->alias('r')->field('r.*,GROUP_CONCAT(p.pri_name) pri_name')
            ->join("{$pre}role_privilege b", 'r.id=b.role_id')
            ->join("{$pre}privilege p", 'b.pri_id=p.id')
            ->group('r.id')->select();

        $this->view->assign('roleList', $roleList);
        return $this->view->fetch('role_list');
    }



    /**
     * 添加
     */
    public function add()
    {
        $pro = model('Privilege');
        $priData = $pro->getTree();

        $this->view->assign('priData',$priData);
        return $this->view->fetch('role_add');
    }



    /**
     * 添加角色表并分配权限
     * @param Request $request
     */
    public function doAdd(Request $request)
    {
        //从提交表单中获取数据
        $post = $request->param();

        $data = $post;

        //新增角色记录
        unset($post['pri_id']);
        $role = RoleModel::create($post);

        //添加权限到权限表
        $pri_id = $data['pri_id'];
        if($pri_id)
        {
            $rpModel = Db::name('RolePrivilege');
            foreach ($pri_id as $k => $v)
            {
                $rpModel->insert(array(
                    'pri_id' => $v,
                    'role_id' => $role->id,
                ));
            }
        }

        //记录日志
        admin_log('添加角色', $data['role_name']);

        $this->success('添加成功！', url('role/roleList'));

    }





    /**
     * 编辑
     */
    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = RoleModel::get($id);
        $this->view->assign('data', $data);

        // 取出所有的权限
        $priModel = model('Privilege');
        $priData = $priModel->getTree();
        $this->view->assign('priData', $priData);

        // 取出当前角色所拥有的权限的ID
        $rpModel = Db::name('RolePrivilege');
        $rpData = $rpModel->field('GROUP_CONCAT(pri_id) pri_id')->where(array('role_id'=>array('eq', $id)))->find();
        $this->view->assign('pri_id', $rpData['pri_id']);

        return $this->view->fetch('role_edit');
    }



    /**
     * 处理编辑
     * @param Request $request
     */
    public function doEdit(Request $request)
    {
        $post = $request->param();

        $data = $post;

        //编辑角色
        unset($post['pri_id']);
        $role = RoleModel::update($post);


        // 先清除原来的权限
        $rpModel = Db::name('RolePrivilege');
        $rpModel->where(array('role_id'=>array('eq', $data['id'])))->delete();

        // 接收表单重新添加一遍
        $pri_id = $data['pri_id'];
        if($pri_id)
        {
            foreach ($pri_id as $k => $v)
            {
                $rpModel->insert(array(
                    'pri_id' => $v,
                    'role_id' => $role->id,
                ));
            }
        }

        //记录日志
        admin_log('编辑角色', $data['role_name']);

        $this->success('编辑成功！', url('role/roleList'));

    }



    /**
     * 删除
     * @param Request $request
     * @throws \think\Exception
     */
    public function delete(Request $request)
    {

        //设置返回数据
        $status = 0;
        $message = '删除失败';

        $id = $request->param('id');

        // 先判断有没有管理员属性这个角色-要读管理员角色表
        $arModel = Db::name('AdminRole');
        $has = $arModel->where(array('role_id'=>array('eq', $id)))->count();
        if($has > 0)
        {
            $this->error('有管理员属于这个角色，无法删除！');
        }
        // 把这个角色所拥有的权限的数据也一起删除
        $rpModel = Db::name('RolePrivilege');
        $result = $rpModel->where(array('role_id'=>array('eq', $id)))->delete();
        if($result === true){
            $status = 1;
            $message = '删除成功';
        }
        $role_name = Db::name('role')->where(array('id'=>array('eq', $id)))->column('role_name');

        //记录日志
        admin_log('删除角色', $role_name);

        return ['status'=>$status, 'message'=>$message];
    }



}