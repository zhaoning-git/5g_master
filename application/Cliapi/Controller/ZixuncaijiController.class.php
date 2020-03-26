<?php

namespace Cliapi\Controller;

use Think\Controller;
use Think\Cache\Driver\Redis;
use Think\Upload;
use QL\QueryList;
use GuzzleHttp\Client;

class ZixuncaijiController extends Controller {
    public function _initialize() {
        require 'vendor/autoload.php';
        header("Content-type:text/html;charset=utf-8");
        
    }
    
/********************************** 懂球帝足球 **************************************************/    
    //懂球帝足球
    public function donqiudi(){
        $data = [];
        $list = $this->donqiudi_list();
        if(!empty($list)){
            foreach ($list as $value){
                if(!M('Show')->where(['title'=>$value['title']])->count()){
                    $_data = $this->donqiudi_content($value['share'], $value['sort_timestamp'], $value['thumb']);
                    if(!empty($_data)){
                        $result = $this->addZixun($_data);
                        if($result){
                            $data[] = $result;
                        }
                    }
                    $data[] = $_data;
                }
            }
        }
        return count($data);
    }

    //懂球帝足球 采集列表
    public function donqiudi_list(){
        $url = 'https://www.dongqiudi.com/api/app/tabs/web/1.json?after='.time().'&page=1';
        $data = file_get_contents($url);
        $array = json_decode($data, true);
        return $array['articles'];
    }
    
    //懂球帝足球 采集内容
    public function donqiudi_content($url, $addtime='', $thumb=''){
        $ql = QueryList::get($url);

        $rt = [];
        
        // 采集文章标题
        $rt['title'] = $ql->find('.news-title')->text();
        
        // 采集文章作者
        $rt['author'] = $ql->find('.tips>span')->text();
        
        if(!empty($rt['author'])){
            $rt['author'] = $this->StringToText($rt['author'], '懂球号作者: ');
        }
        
        // 采集文章内容
        $rt['content'] = $ql->find('.con')->html();
        if(!empty($rt['content'])){
            $rt['content'] = str_replace(['style="display:none;"','<img data-src='], ['','<img src='], $rt['content']);
        }
        
        
        //采集文章发布时间
        $rt['addtime'] = $addtime;
        //列表缩略图
        $rt['thumb'] = $thumb;
        //首页类型 1:足球  2:篮球
        $rt['ctype'] = 1;
        return $rt;
    }
    
/********************************** 雷速足球 **************************************************/    
    //雷速足球
    public function leisu(){
        $data = [];
        $list = $this->leisu_list();
        if(!empty($list)){
            foreach ($list as $value){
                if(!M('Show')->where(['title'=>$value['title']])->count()){
                    $_data = $this->leisu_content($value['share'], $value['thumb'], $value['Cookie']);
                    if(!empty($_data)){
                        $result = $this->addZixun($_data);
                        if($result){
                            $data[] = $result;
                        }
                    }
                }
            }
        }
        return count($data);
    }

    //雷速足球 采集列表
    public function leisu_list(){
        $url = 'https://news.leisu.com/';
        
        $client = new Client();
        $response = $client->get($url);
        // 获取响应头部信息
        $headers = $response->getHeaders();
        //$Cookie = implode(';', $headers['Set-Cookie']);
        $Cookie = $headers['Set-Cookie'][0];
        
        // 元数据采集规则
        $rules = [
            //文章链接
            'share' => ['p.title a.text','href'],
            
            //缩略图
            'thumb' => ['a.img-view','style'],
            
            //发布日期
            //'published_at' => date('Y-m-d H:i'),
            
            // 采集文章标题
            'title' => ['p.title a.text','text'],
        ];        
        
        // 切片选择器
        $range = '.page-item';
        $rt = QueryList::get($url)
                ->rules($rules)
                ->range($range)
                ->query()
                ->getData(function ($item){
                    $item['share'] = 'https://news.leisu.com'.$item['share'];
                    $item['published_at'] = date('Y-m-d H:i');
                    
                    preg_match("/url\('(.*?)'\)/", $item['thumb'], $matches);
                    
                    $item['thumb'] = $matches[1];
                    return $item;
                });

        return $rt->all();
    }

    //雷速足球 采集内容
    public function leisu_content($url, $thumb='', $Cookie=''){
        
//        $ql = QueryList::get($url,null,[
//            'headers' => [
//                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
//                'Referer' => $url,
//                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
//                //'Cookie' => $Cookie
//            ]
//        ]);
        
        $ql = QueryList::get($url);
        
        $rt = [];
        
        // 采集文章标题
        $rt['title'] = $ql->find('p.title')->text();
        
        // 采集文章作者
        $rt['author'] = $ql->find('div.clearfix-row.bd-bottom span:eq(1)')->text();
        if(!empty($rt['author'])){
            $rt['author'] = $this->StringToText($rt['author'], '作者：');
        }
        
        // 采集文章内容
        $content = $ql->find('.js-conet');
        $content->find('style,p img')->remove();
        $rt['content'] = $content->html();
        if(!empty($rt['content'])){
            $rt['content'] = str_replace([' ',PHP_EOL, '<p></p>'], ['','',''], $rt['content']);
        }
        
        //采集文章发布时间
        $addtime = $ql->find('div.clearfix-row.bd-bottom span:eq(0)')->text();
        
        $rt['addtime'] = strtotime($this->StringToText($addtime, '发布时间：'));
        
        //列表缩略图
        $rt['thumb'] = $thumb;
        
        //首页类型 1:足球  2:篮球
        $rt['ctype'] = 1;
        return $rt;
        
    }
    
/********************************** 腾讯足球 **************************************************/
    //腾讯足球
    public function qqfootball(){
        $data = [];
        $list = $this->qqfootball_list();
        
        if(!empty($list)){
            foreach ($list as $value){
                if(!M('Show')->where(['title'=>$value['title']])->count()){
                    $_data = $this->qqfootball_content($value['share'], $value['thumb'], $value['addtime']);
                    if(!empty($_data)){
                        $result = $this->addZixun($_data);
                        if($result){
                            $data[] = $result;
                        }
                    }
                }
            }
        }
        return count($data);
    }

    //腾讯足球 采集列表
    public function qqfootball_list($url=''){
        if(empty($url)){
            $num = 10;//采集数量
            $url = 'https://pacaio.match.qq.com/tags/tag2articles?name=%e8%8b%b1%e8%b6%85%2c%e8%8b%b1%e8%b6%85%2c%e6%ac%a7%e5%86%a0%2c%e5%be%b7%e7%94%b2%2c%e8%a5%bf%e7%94%b2%2c%e6%9b%bc%e8%81%94%2c%e6%9b%bc%e5%9f%8e%2c%e5%88%a9%e7%89%a9%e6%b5%a6%2c%e5%88%87%e5%b0%94%e8%a5%bf%2c%e9%98%bf%e6%a3%ae%e7%ba%b3%2c%e7%83%ad%e5%88%ba%2c%e8%8e%b1%e6%96%af%e7%89%b9%e5%9f%8e%2c%e7%8b%bc%e9%98%9f%2c%e8%b0%a2%e8%8f%b2%e8%81%94%2c%e8%a5%bf%e6%b1%89%e5%a7%86%e8%81%94%2c%e4%bc%af%e6%81%a9%e5%88%a9%2c%e6%b0%b4%e6%99%b6%e5%ae%ab%2c%e9%98%bf%e6%96%af%e9%a1%bf%e7%bb%b4%e6%8b%89%2c%e6%b2%83%e7%89%b9%e7%a6%8f%e5%be%b7%2c%e5%8d%97%e5%ae%89%e6%99%ae%e6%95%a6%2c%e5%9f%83%e5%bc%97%e9%a1%bf%2c%e8%af%ba%e7%bb%b4%e5%a5%87%2c%e7%ba%bd%e5%8d%a1%e6%96%af%e5%b0%94%e8%81%94%2c%e4%bc%af%e6%81%a9%e8%8c%85%e6%96%af%2c%e5%b8%83%e8%8e%b1%e9%a1%bf&num='.$num.'&_='.time().rand(000,999);
        }
        
        $rt = QueryList::get($url)->getHtml();
        
        $rt =  json_decode($rt, true);
        $list = $rt['data'];
        
        $data = [];
        if(!empty($list)){
            foreach ($list as $value){
                $_data['title'] = $value['title'];
                $_data['share'] = $value['vurl'];
                $_data['thumb'] = $value['img'];
                $_data['published_at'] = $value['publish_time'];
                $_data['addtime'] = $value['ts'];
                $data[] = $_data;
            }
        }
        return $data;
    }

    //腾讯足球 采集内容
    public function qqfootball_content($url, $thumb='', $addtime='', $ctype=1){
        $ql = QueryList::get($url)->encoding('UTF-8','GBK');
        //return $ql->getHtml();
        $rt = [];
        
        // 采集文章标题
        $rt['title'] = $ql->find('h1')->text();
        
        // 采集文章作者
        $rt['author'] = '佚名';
        
        // 采集文章内容
        $content = $ql->find('.content .content-article');
        $content->find('#Status')->remove();
        $rt['content'] = $content->html();
        if(!empty($rt['content'])){
            $rt['content'] = str_replace(['<!--导语-->', '<img src="//'], ['','<img src="https://'], $rt['content']);
        }
        
        
        //采集文章发布时间
        $rt['addtime'] = $addtime;
        
        //列表缩略图
        $rt['thumb'] = $thumb;
        
        //首页类型 1:足球  2:篮球
        $rt['ctype'] = $ctype?:1;
        return $rt;
        
    }
    
/********************************** 腾讯篮球 **************************************************/
    //腾讯篮球
    public function qqbasketball(){
        $data = [];
        $list = $this->qqbasketball_list();
        
        if(!empty($list)){
            foreach ($list as $value){
                if(!M('Show')->where(['title'=>$value['title']])->count()){
                    $_data = $this->qqbasketball_content($value['share'], $value['thumb'], $value['addtime']);
                    if(!empty($_data)){
                        $result = $this->addZixun($_data);
                        if($result){
                            $data[] = $result;
                        }
                    }
                }
            }
        }
        return count($data);
    }
    
    //腾讯篮球 采集列表
    public function qqbasketball_list(){
        $num = 20;//采集数量
        $time = time()-60;
        $url = 'https://pacaio.match.qq.com/tags/tag2articles?name=NBA%2C%E5%B9%BF%E4%B8%9C%E7%94%B7%E7%AF%AE%2C%E6%98%93%E5%BB%BA%E8%81%94%2C%E5%91%A8%E9%B9%8F%2C%E8%B5%B5%E7%9D%BF%2C%E6%9D%9C%E9%94%8B%2C%E5%B8%83%E9%B2%81%E5%85%8B%E6%96%AF%2C%E5%A8%81%E5%A7%86%E6%96%AF%2CCBA%E4%B9%9D%E5%86%A0%E7%8E%8B&num='.$num.'&_='.$time.rand(000,999);
        return $this->qqfootball_list($url);
    }
    
    //腾讯篮球 采集内容
    public function qqbasketball_content($url, $thumb='', $addtime=''){
        return $this->qqfootball_content($url, $thumb, $addtime, 2);
    }
    
/********************************** 新浪足球 **************************************************/
    //新浪足球
    public function sinafootball(){
        $data = [];
        $list = $this->sinafootball_list();
        
        if(!empty($list)){
            foreach ($list as $value){
                if(!M('Show')->where(['title'=>$value['title']])->count()){
                    $_data = $this->sinafootball_content($value['share'], $value['thumb'], $value['addtime']);
                    if(!empty($_data)){
                        $result = $this->addZixun($_data);
                        if($result){
                            $data[] = $result;
                        }
                    }
                }
            }
        }
        return count($data);
        
    }

    //新浪足球 采集列表
    public function sinafootball_list(){
        
        //国内足球
        $list_1 = $this->sinafootball_list_data(87,552);
        
        //欧冠
        $list_2 = $this->sinafootball_list_data(43,307);
        
        return array_merge($list_1,$list_2);
    }
    
    private function sinafootball_list_data($pageid, $lid){
        
        $num = 20;//采集数量
        $time = time()-20;
        
        //国内足球
        $url = 'http://feed.mix.sina.com.cn/api/roll/get?pageid='.$pageid.'&lid='.$lid.'&num='.$num.'&versionNumber=1.2.4&page=1&encode=utf-8&callback=feedCardJsonpCallback&_='.$time.rand(000,999);
        $rt = file_get_contents($url);
        preg_match("/ack\((.*?)\);/is", $rt, $matches);
        $res = json_decode($matches[1], true);
        $list = $res['result']['data'];
        
        $data = [];
        if(!empty($list)){
            foreach ($list as $value){
                $_data['title'] = $value['title'];
                $_data['share'] = $value['url'];
                $_data['thumb'] = $value['img']['u'];
                $_data['published_at'] = date('Y-m-d H:i', $value['ctime']);
                $_data['addtime'] = $value['ctime'];
                $data[] = $_data;
            }
        }
        return $data;
    }

    //新浪足球 采集内容
    public function sinafootball_content($url, $thumb='', $addtime='', $ctype=1){
        $ql = QueryList::get($url);
        
        $rt = [];
        
        // 采集文章标题
        $rt['title'] = $ql->find('.main-title')->text();
        
        // 采集文章作者
        $rt['author'] = '佚名';
        
        // 采集文章内容
        $content = $ql->find('#artibody');
        
        $content->find('div[id],div[style]')->remove();
        
        $rt['content'] = $content->html();
        if(!empty($rt['content'])){
            $rt['content'] = preg_replace("/<\!--.*?--\>/is", '', $rt['content']);
            $rt['content'] = str_replace([PHP_EOL,'src="//'], ['','src="https://'], $rt['content']);
            $rt['content'] = preg_replace("/>(\s*?)</", '>${2}<', $rt['content']);
        }
        
        
        //采集文章发布时间
        $rt['addtime'] = $addtime;
        
        //列表缩略图
        $rt['thumb'] = $thumb;
        
        //首页类型 1:足球  2:篮球
        $rt['ctype'] = $ctype?:1;
        return $rt;
        
    }

/********************************** 新浪蓝球 **************************************************/
    //新浪蓝球
    public function sinabasketball(){
        $data = [];
        $list = $this->sinabasketball_list();
        
        if(!empty($list)){
            foreach ($list as $value){
                if(!M('Show')->where(['title'=>$value['title']])->count()){
                    $_data = $this->sinabasketball_content($value['share'], $value['thumb'], $value['addtime']);
                    if(!empty($_data)){
                        $result = $this->addZixun($_data);
                        if($result){
                            $data[] = $result;
                        }
                    }
                }
            }
        }
        return count($data);
    }
    
    
    //新浪蓝球 采集列表
    public function sinabasketball_list(){
        //return $this->sinabasketball_list_cba_con();
        $list1 =  $this->sinabasketball_list_nba();
        $list2 =  $this->sinabasketball_list_cba_con();
        return array_merge($list1,$list2);
    }
    
    //NBA自媒体专家团
    private function sinabasketball_list_nba(){
        $num = 10;
        $url = 'http://interface.sina.cn/pc_zt_api/zt_latest_news.d.json?cids=255259&size='.$num.'&page=1';
        $rt = QueryList::get($url);
        $data = json_decode($rt->getHtml(), true);
        $list = $data['result']['data'];
        $data = [];
        if(!empty($list)){
            foreach ($list as $value){
                $_data['title'] = $value['title'];
                $_data['share'] = 'https:'.$value['url'];
                $_data['thumb'] = 'https:'.$value['pic'];
                $_data['published_at'] = $value['date'].' '.$value['time'];
                $_data['addtime'] = strtotime(str_replace(['年','月','日',], ['-','-',''], $_data['published_at']));
                $data[] = $_data;
            }
        }
        return $data;
    }
    
    //中国篮球 焦点关注
    private function sinabasketball_list_cba(){
        $url = 'http://sports.sina.com.cn/cba/';
        //$rt = QueryList::get($url);
        //return $rt->getHtml();
        
        // 切片选择器
        $range = '.news-list-d__main_list div.layout-mb-b';
        
        
        // 元数据采集规则
        $rules = [
            // 采集文章标题
            'title' => ['a.mews-list-d__title','text'],
            
            //文章链接
            'share' => ['a.mews-list-d__title','href'],
            
            //缩略图
            'thumb' => ['.img img','src'],
            
            //发布日期
            'published_at' => ['.news-list-d__intro p:eq(1)', 'text'],
        ];        
        
        $rt = QueryList::get($url)
                ->rules($rules)
                ->range($range)
                ->query()
                ->getData(function($item){
                    $item['thumb'] = 'https:'.$item['thumb'];
                    $item['addtime'] = strtotime($item['published_at']);
                    return $item;
                });

        return $rt->all();
        
        
    }
    
    //中国篮球 去内容页获取缩略图
    private function sinabasketball_list_cba_con(){
        $url = 'http://sports.sina.com.cn/cba/';
        //$rt = QueryList::get($url);
        //return $rt->getHtml();
        // 切片选择器
        $range = '.news-list-b .item p,.news-list-a .list .item';
        //$range = '.news-list-a .list .item';
        
        // 元数据采集规则
        $rules = [
            // 采集文章标题
            'title' => ['a','text'],
            
            //文章链接
            'share' => ['a','href'],
            
            //缩略图
            //'thumb' => ['.img img','src'],
            
            //发布日期
            //'published_at' => ['.news-list-d__intro p:eq(1)', 'text'],
        ];        
        
        $rt = QueryList::get($url)
                ->rules($rules)
                ->range($range)
                ->query()
                ->getData(function($item){
                    $ql = QueryList::get($item['share']);
                    $thumb = $ql->find('#artibody .img_wrapper img')->src;
                    $item['thumb'] = $thumb?('https:'.$thumb):'';
                    $item['published_at'] = $ql->find('.date-source span.date')->text();
                    $item['addtime'] = strtotime(str_replace(['年','月','日',], ['-','-',''], $item['published_at']));
                    return $item;
                });
        $list = $rt->all();
        return $list;
        
        
        
    }


    //新浪蓝球 采集内容
    public function sinabasketball_content($url, $thumb='', $addtime=''){
        return $this->sinafootball_content($url, $thumb, $addtime, 2);
    }
    
    
    //入库
    public function addZixun($data){
        if(empty($data['title'])){
            return false;
        }
        
        if(empty($data['author'])){
            //return false;
        }
        
        if(empty($data['content'])){
            return false;
        }
        
        $dass['title'] = strip_tags(trim($data['title']));
        $dass['uname'] = $data['author'];
        $dass['content'] = $data['content'];
        
        if(!empty($data['thumb'])){
            $dass['data'] = $data['thumb'];
        }        
        
        
        $dass['addtime'] = empty($data['addtime'])?time():$data['addtime'];
        
        $dass['identifshi'] = 1;
        $dass['type'] = 2;
        $dass['index_mol'] = $data['ctype']; //首页类型 1:足球  2:篮球
        $typeInfo = M('club_type')->where(array('status' => 0, 'type' => $data['ctype']))->select();
        
        unset($typeInfo[4]);
        $type_one = array_rand($typeInfo, 1);
        $dass['sel_type'] = $typeInfo[$type_one]['id'];
        
        return M('Show')->add($dass);
        
    }
   
    
    /**
     * 提取富文本字符串的纯文本,并进行截取;
     * @param $string 需要进行截取的富文本字符串
     * @param $int 需要截取多少位
     */
    public static function StringToText($string, $other=''){
        if($string){
            //把一些预定义的 HTML 实体转换为字符
            $html_string = htmlspecialchars_decode($string);
            
            if(!empty($other)){
                $html_string = str_replace($other, '', $html_string);
            }
            
            
            //将空格替换成空
            //$content = str_replace(" ", '', $html_string);
            $content = str_replace(PHP_EOL, '', $html_string);
            
            //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
            $contents = strip_tags($content);
            
            return $contents;
            //返回字符串中的前$num字符串长度的字符
            //return mb_strlen($contents,'utf-8') > $num ? mb_substr($contents, 0, $num, "utf-8").'....' : mb_substr($contents, 0, $num, "utf-8");
        }else{
            return $string;
        }
    }    
    
}

