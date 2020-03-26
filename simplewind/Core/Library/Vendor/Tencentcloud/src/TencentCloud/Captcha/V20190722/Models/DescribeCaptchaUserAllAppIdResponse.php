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
namespace TencentCloud\Captcha\V20190722\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method array getData() 获取用户注册的所有Appid和应用名称
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setData(array $Data) 设置用户注册的所有Appid和应用名称
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getCaptchaCode() 获取成功返回 0  其它失败
 * @method void setCaptchaCode(integer $CaptchaCode) 设置成功返回 0  其它失败
 * @method string getCaptchaMsg() 获取返回操作信息
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setCaptchaMsg(string $CaptchaMsg) 设置返回操作信息
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *DescribeCaptchaUserAllAppId返回参数结构体
 */
class DescribeCaptchaUserAllAppIdResponse extends AbstractModel
{
    /**
     * @var array 用户注册的所有Appid和应用名称
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Data;

    /**
     * @var integer 成功返回 0  其它失败
     */
    public $CaptchaCode;

    /**
     * @var string 返回操作信息
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $CaptchaMsg;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param array $Data 用户注册的所有Appid和应用名称
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $CaptchaCode 成功返回 0  其它失败
     * @param string $CaptchaMsg 返回操作信息
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $RequestId 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    function __construct()
    {

    }
    /**
     * 内部实现，用户禁止调用
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("Data",$param) and $param["Data"] !== null) {
            $this->Data = [];
            foreach ($param["Data"] as $key => $value){
                $obj = new CaptchaUserAllAppId();
                $obj->deserialize($value);
                array_push($this->Data, $obj);
            }
        }

        if (array_key_exists("CaptchaCode",$param) and $param["CaptchaCode"] !== null) {
            $this->CaptchaCode = $param["CaptchaCode"];
        }

        if (array_key_exists("CaptchaMsg",$param) and $param["CaptchaMsg"] !== null) {
            $this->CaptchaMsg = $param["CaptchaMsg"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
