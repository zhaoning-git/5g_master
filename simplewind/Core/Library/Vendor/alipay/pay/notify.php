<?php
//require_once '../aop/AopClient.php';

//$aop = new AopClient;
//$aop->alipayrsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmO4Xhn6dbUJ6vydwwNUhKd8dzOA/19UPJXr4ysFd2mxDTibjvJGrWaT22nrKqFat+6Ps8gCRINpxA00tQyqFX3vr2Ok6UTFKKVFYQAu6zIMkWvUk4Y+Xfa39hXGLc30iEPFWW6gEq0I4RGF1L7yTzdGsY7dYd8cSzrofhzbbm5TIbYLKenSE3k77wgpImxWUelz1wqGIUMErchgF/5jK3Y8XCq/Bi8WFCPLgh/G80zfgz/bLnm+Iy+hLNJkfsouasPCOSEqL85oH82LI1hwXk2jV/NMC77AsLPJp1Ry8yvJdz+sgEsS/JG0LexMZ3QIdtRaKz9jYrPwWVlca+rkq+wIDAQAB";

//$info =$_POST;

//--------------test data-----------------------
/*
$info['gmt_create']='2019-05-17 17:04:03';
$info['charset']='UTF-8';
$info['seller_email']='13002711907';
$info['subject']='家之源门窗';
$info['sign']='hd9rd31fGTjoHQvEXKuPNqJmTtok8JcQ6jqjZKNPBAk4ggoSJRedrwDKTaBlB0ZTgKP/myrel7q0CliJGcmuqmmnimBfBhl8+/5yTZqHqWYvmhvzeL05rcSs/bTqMG7g1vEHqaLCwMMdJOf0B4tu8GKhMxdmbtJDiwtret705ZIcM8iIRKFNL9N0IQEnxpYUjzm5b2QvPBlqAdyvCpi2YKj8YF1jP2wAtS233DZ28hpk6z1cr2NBo4DvEXJ3a5siWl534wVM6gzHFraBfKdP88R2k9BL6YaXo/Jpt2ajD0Mh+Dh7mwFvDONFDB5XzH607VXQabUqzu5FH+bdkFU3/g==';
$info['body']='商品名称为：家之源门窗';
$info['buyer_id']='2088302137391159';
$info['invoice_amount']='0.01';
$info['notify_id']='2019051700222170404091151027132465';

$info['fund_bill_list']='[{"amount":"0.01","fundChannel":"PCREDIT"}]';

$info['notify_type']='trade_status_sync';
$info['trade_status']='TRADE_SUCCESS';
$info['receipt_amount']='0.01';
$info['app_id']='2019022863423522';
$info['buyer_pay_amount']='0.01';
$info['sign_type']='RSA2';
$info['seller_id']='2088332646982142';
$info['gmt_payment']='2019-05-17 17:04:04';
$info['notify_time']='2019-05-17 17:04:04';
$info['version']='1.0';
$info['out_trade_no']='2019051751535757';
$info['total_amount']='0.01';
$info['trade_no']='2019051722001491151044521223';
$info['auth_app_id']='2019022863423522';
$info['buyer_logon_id']='xif***@163.com';
$info['point_amount']='0.00';
*/

/*
$out_trade_no = $info['out_trade_no'];

$str = '';
foreach($info as $k=>$v) {
	$str .= $k . ':' . $v . ' ';
}

log_zfb($str);

$flag = $aop->rsaCheckV1($info, NULL, "RSA2");
if($flag)
{
	log_zfb("sign ok, ready to deal order...");

	orderServer($out_trade_no);

	header('Access-Control-Allow-Origin: *');
	header('Content-type: text/plain');
	echo 'success';
}
else
{
	log_zfb("sign error, bianhao=$out_trade_no");
}

//订单处理业务逻辑
function orderServer($out_trade_no)
{
	$order = M("order")->where("bianhao=$out_trade_no")->find();
	if($order != null)
	{
		if($order['type'] == '1')
		{
			$ordersave = M("order")->where("bianhao=$out_trade_no")->save(array("type"=>2));
			if($ordersave)
			{
				log_zfb("order type update ok,bianhao=$out_trade_no");
			}
			else
			{
				log_zfb("order type update error,bianhao=$out_trade_no");
			}
		}
		else
		{
			$type = $order['type'];
			log_zfb("order is deal , bianhao=$out_trade_no, type=$type ");
		}
	}
	else
	{
		log_zfb("not find the order, bianhao=$out_trade_no");
	}
}


function log_zfb($str)
{
	$now_time = time();	
	$filename = date('Y-m-d',$now_time);	
	$now_date = date('Y-m-d H:i:s',$now_time);

	$outstr = "[$now_date]---[$str]" . "\n";
	echo ($outstr);
	//file_put_contents("../../log/payresult_zfb_$filename.log", $outstr , FILE_APPEND | LOCK_EX);
}
*/