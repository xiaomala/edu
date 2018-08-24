<?php
// +----------------------------------------------------------------------
// | Blog [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://lexiang123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 2275840206@qq.com
// +----------------------------------------------------------------------
// | Time : ${DATA}下午 2:34
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Db;

class Config extends Base
{

    public function index()
    {
        return $this->view->fetch();
    }



    public function add()
    {

    }

}