<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TencentCloud\Monitor\V20180724;
use TencentCloud\Common\AbstractClient;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Credential;
use TencentCloud\Monitor\V20180724\Models as Models;

/**
* @method Models\BindingPolicyObjectResponse BindingPolicyObject(Models\BindingPolicyObjectRequest $req) 将告警策略绑定到特定对象
* @method Models\CreatePolicyGroupResponse CreatePolicyGroup(Models\CreatePolicyGroupRequest $req) 增加策略组
* @method Models\DescribeAccidentEventListResponse DescribeAccidentEventList(Models\DescribeAccidentEventListRequest $req) 获取平台事件列表
* @method Models\DescribeBaseMetricsResponse DescribeBaseMetrics(Models\DescribeBaseMetricsRequest $req) 获取基础指标详情
* @method Models\DescribeProductEventListResponse DescribeProductEventList(Models\DescribeProductEventListRequest $req) 分页获取产品事件的列表
* @method Models\GetMonitorDataResponse GetMonitorData(Models\GetMonitorDataRequest $req) 获取云产品的监控数据。传入产品的命名空间、对象维度描述和监控指标即可获得相应的监控数据。
接口调用频率限制为：20次/秒，1200次/分钟。
若您需要调用的指标、对象较多，可能存在因限频出现拉取失败的情况，建议尽量将请求按时间维度均摊。
* @method Models\ModifyAlarmReceiversResponse ModifyAlarmReceivers(Models\ModifyAlarmReceiversRequest $req) 修改告警接收人
* @method Models\PutMonitorDataResponse PutMonitorData(Models\PutMonitorDataRequest $req) 默认接口请求频率限制：50次/秒。
默认单租户指标上限：100个。
单次上报最多 30 个指标/值对，请求返回错误时，请求中所有的指标/值均不会被保存。

上报的时间戳为期望保存的时间戳，建议构造整数分钟时刻的时间戳。
时间戳时间范围必须为当前时间到 300 秒前之间。
同一 IP 指标对的数据需按分钟先后顺序上报。
 */

class MonitorClient extends AbstractClient
{
    /**
     * @var string 产品默认域名
     */
    protected $endpoint = "monitor.tencentcloudapi.com";

    /**
     * @var string api版本号
     */
    protected $version = "2018-07-24";

    /**
     * CvmClient constructor.
     * @param Credential $credential 认证类实例
     * @param string $region 地域
     * @param ClientProfile $profile client配置
     */
    function __construct($credential, $region, $profile=null)
    {
        parent::__construct($this->endpoint, $this->version, $credential, $region, $profile);
    }

    public function returnResponse($action, $response)
    {
        $respClass = "TencentCloud"."\\".ucfirst("monitor")."\\"."V20180724\\Models"."\\".ucfirst($action)."Response";
        $obj = new $respClass();
        $obj->deserialize($response);
        return $obj;
    }
}
