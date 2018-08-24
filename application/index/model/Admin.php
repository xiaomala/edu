<?php
// +----------------------------------------------------------------------
// | Blog [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://lexiang123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 2275840206@qq.com
// +----------------------------------------------------------------------
// | Time : ${DATA}下午 2:36
// +----------------------------------------------------------------------
namespace app\index\model;

use think\Model;

class Admin extends Model
{

    //只有该字段为NULL,该字段才会显示出来
    protected $deleteTime = 'delete_time';


    // 保存自动完成列表
    protected $auto = [
        'delete_time' => NULL,
        'is_delete' => 1, //1:允许删除 0:禁止删除
    ];
    // 新增自动完成列表
    protected $insert = [
        'login_time'=> NULL, //新增时登录时间应该为NULL,因为刚创建
        'login_count' => 0, //原因同上,刚创建肯定没有登录过
    ];
    // 更新自动完成列表
    protected $update = [];
    // 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
    protected $autoWriteTimestamp = true; //自动写入
    // 创建时间字段
    protected $createTime = 'create_time';
    // 更新时间字段
    protected $updateTime = 'update_time';
    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y年m月d日';


    //状态字段:status返回值处理
    public function getStatusAttr($value)
    {
        $status = [
            0=>'已停用',
            1=> '已启用'
        ];
        return $status[$value];
    }

    //密码修改器
    public function setPasswordAttr($value)
    {
        return encrypt($value);
    }

    //登录时间获取器
    public function getLoginTimeAttr($value)
    {
        return date('Y/m/d H:i', $value);
    }


}