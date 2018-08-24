<?php
namespace app\index\controller;

use think\Db;
use think\Controller;
use think\Request;
use app\index\model\Privilege as PrivilegeModel;

class Privilege extends Base
{


    public function index()
    {
        $model = model('Privilege');
        $data = $model->getTree();
        $this->view->assign('data', $data);
        return $this->view->fetch();
    }



    public function add()
    {
        $parentModel = model('Privilege');
        $parentData = $parentModel->getTree();
        $this->view->assign('parentData', $parentData);
        return $this->view->fetch('pri_add');
    }



    public function doAdd(Request $request)
    {

        //从提交表单中获取数据
        $data = $request->param();

        //更新当前记录
        $result = PrivilegeModel::create($data);

        //设置返回数据的初始值
        $status = 0;
        $message = '添加失败,请检查';

        //检测更新结果,将结果返回给grade_edit模板中的ajax提交回调处理
        if (true == $result) {
            $status = 1;
            $message = '恭喜, 添加成功~~';
        }

        //记录日志
        admin_log('添加权限', $data['pri_name']);

        //自动转为json格式返回
        return ['status'=>$status, 'message'=>$message];
    }



    public function edit(Request $request)
    {

        $id = $request->param('id');

        $model = model('Privilege');
        $data = $model->find($id);
        $this->view->assign('data', $data);

        $parentModel = model('Privilege');
        $parentData = $parentModel->getTree();
        $children = $parentModel->getChildren($id);


        $this->view->assign('parentData', $parentData);
        $this->view->assign('children', $children);
        return $this->view->fetch('pri_edit');
    }




    public function doEdit(Request $request)
    {
        //从提交表单中排除关联字段teacher字段
        $data = $request->param();
//        $data = $request -> param();  如果全部获取,提交会提示缺少字段teacher

        //设置更新条件
        $condition = ['id'=>$data['id']];

        //更新当前记录
        $result = PrivilegeModel::update($data,$condition);

        //设置返回数据
        $status = 0;
        $message = '更新失败,请检查';

        //检测更新结果,将结果返回给grade_edit模板中的ajax提交回调处理
        if (true == $result) {
            $status = 1;
            $message = '恭喜, 更新成功~~';
        }

        //记录日志
        admin_log('编辑权限', $data['pri_name']);

        return ['status'=>$status, 'message'=>$message];


    }



    //恢复删除操作
    public function delete(Request $request)
    {
        $id = $request->param();

        // 先找出所有的子分类
        $children = model('Privilege')->getChildren($id);
        // 如果有子分类都删除掉
        if($children)
        {
            $children = implode(',', $children);
            $this->execute("DELETE FROM t_Privilege WHERE id IN($children)");
        }

        Db::name('Privilege')->delete($id);
    }


}
