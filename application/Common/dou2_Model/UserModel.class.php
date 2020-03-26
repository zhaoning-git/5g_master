<?php

namespace Common\Model;
use Think\Model\ViewModel;
class UserModel extends ViewModel {
    
    public $viewFields = array(
        'UcenterMember'=>array('id','username','parent_id','region_id','agents_id',
            'salesman_id','is_supplier','supplier_type','salesman','flower','gold',
            'money','no_money','lng','lat','status','_type'=>'RIGHT'
        ),
        'Member'=>array('uid','nickname','mobile','my_price','profession','phone', 
            '_on'=>'UcenterMember.id=Member.uid'
        ),
    );
   
    
   
   
 }