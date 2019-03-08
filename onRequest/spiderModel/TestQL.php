<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/6
 * Time: 10:59
 */

namespace onRequest\spiderModel;
use QL\QueryList;
use QL\Ext\Chrome;//尝试模拟浏览器失败--node
use QL\Ext\PhantomJs;
class TestQL
{
    //不需要的控制器方法
    public function downLoadHeroJson()
    {
        $obj = new  Pvp();
        $result = $obj->downloadHeroesJson();
        $describe = $result > 0  ? '成功':'失败';
        say('下载结果：',$describe,'res',$result);
    }

    public function downloadHeroesJson()
    {
        $str = '{"ename":105,"cname":"廉颇","title":"正义爆轰","new_type":0,"hero_type":3,"skin_name":"正义爆轰|地狱岩魂"}';
        $list = json_decode($str,true);
        say('$list',$list);
        $error = jsonDecodeError();
        say('转数组失败的原因',$error);
        say('json_last_error_msg ',json_last_error_msg ());

        $url = $this->urlBase.'/js/herolist.json';
        $ql = QueryList::get($url);
        //$queryRes = $ql->query();
        //say('$queryRes',$queryRes);
        //$res = $queryRes->getData();
        //say('getData-Res',$res);
        $data = $ql->getHtml();
        //
        say('获取英雄json',$data);
        //$data2 = "'{$data}'";
        /*$data2 = <<<EOF
<script>let s = JSON.parse($data);s = JSON.stringify(s);console.log("s=",s)</script>
EOF;
        say('js处理后的$data2',$data2);
        */

        $list = json_decode($data,true);
        say('$list',$list);
        $error = jsonDecodeError();
        say('转数组失败的原因',$error);
        say('json_last_error_msg ',json_last_error_msg ());
        //
        /*$file = ROOT.'/onRequest/public/spider/download_heroList.json';
        if(false === file_exists($file))
        {
            touch($file);
        }
        return file_put_contents($file,$data);*/
    }

    public function getAllHeroes()
    {
        error_reporting(E_ALL ^E_DEPRECATED );
        $this->init();
        $baseUrl = $this->urlBase;
        $detailField = 'detail_url';
        $rules = array(
            'hero_name'   => array('ul.herolist  a','text'),
            $detailField  => array('ul.herolist  a','href'/*,'',function($href) use($baseUrl)
            {
                return  $baseUrl.'/'.$href;
            }*/),
            'hero_avatar' => array('ul.herolist img','src','',function($src)
            {
                return 'https:'.$src;
            }),
        );
        $url = $this->heroListUrl;
        $ql = QueryList::getInstance();
// 安装时需要设置PhantomJS二进制文件路径
        $ql->use(PhantomJs::class,'/home/ffxhd/phantomjs-2.1.1-linux-x86_64/bin/phantomjs');
        $data = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use($url){
            $r->setMethod('GET');
            $r->setUrl($url);
            //$r->setTimeout(0); // 10 seconds
            $r->setDelay(0.2); // 3 seconds
            return $r;
        })->rules($rules)->query()->getData();
        $idealData = $data->all();
        foreach ($idealData as $key => $info )
        {
            $href = $info[$detailField];
            $arr = explode('/',$href);
            $str = $arr[1];
            $arr = explode('.',$str);
            $str = $arr[0];
            $idealData[$key]['hero_id'] = $str;
            $idealData[$key][$detailField] = $baseUrl.'/'.$href;
        }
        return $idealData;
    }

    public function getDemoHtml()
    {
        return  <<<EOF
<ul class="herolist-nav">
    <li class="current"><a href="herolist.shtml" target="_blank">英雄</a></li>
    <li><a href="item.shtml" target="_blank">局内道具</a></li>
    <li><a href="summoner.shtml" target="_blank">召唤师技能</a></li>
</ul>
EOF;
    }

    public function getListHtml()
    {
        return file_get_contents(ROOT.'/onRequest/public/Heroes.html');
    }

    public function  testListHtml()
    {
        $rules = array(
            'heroName'   => array('ul.herolist  a','text'),
            'heroAvatar' => array('ul.herolist img','src','',function($src)
            {
                return 'https:'.$src;
            }),
        );
        $html = $this->getListHtml();//动态填充后的html
        $ql = QueryList::html($html);
        $data = $ql->rules($rules)->queryData();
        foreach($data as $key => $info)
        {
            $data[$key]['heroName'] = trim($info['heroName']);
        }
        say(__FUNCTION__.'--$data',$data);
    }


    public function testHtml_3()
    {
        //采集规则
        $rules = array(
            'rule1' => array('ul.herolist-nav  a','text'),
        );
        $html = $this->getDemoHtml();
        $ql = QueryList::html($html);
        $data = $ql->rules($rules)->queryData();
        say(__FUNCTION__.'--$data',$data);
        /*Array
        (
            [0] => Array
                (
                    [rule1] => 英雄
                )

            [1] => Array
                (
                    [rule1] => 局内道具
                )

            [2] => Array
                (
                    [rule1] => 召唤师技能
                )

        )*/
    }

    public function testHtml_2()
    {
        //采集规则
        $rules = array(
            'rule1' => array('a','text'),
        );
        $html = $this->getDemoHtml();
        $ql = QueryList::html($html);
        $data = $ql->rules($rules)->queryData();
        say(__FUNCTION__.'--$data',$data);
        /*Array
        (
            [0] => Array
                (
                    [rule1] => 英雄
                )

            [1] => Array
                (
                    [rule1] => 局内道具
                )

            [2] => Array
                (
                    [rule1] => 召唤师技能
                )

        )*/

    }

    public function testHtml_1()
    {
        $html = $this->getDemoHtml();
        $ql = QueryList::html($html);
        $data = $ql ->find('a')->texts();
        $data = $data->all();
        say(__FUNCTION__.'--$data',$data);
        /*Array
        (
            [0] => 英雄
            [1] => 局内道具
            [2] => 召唤师技能
        )*/
    }

    //-----------------失败的测试------------------------------

    public function getAllHeroes_node_fail()
    {
        $rules = array(
            'heroName'   => array('ul.herolist  a','text'),
            'heroAvatar' => array('ul.herolist img','src','',function($src)
            {
                return 'https:'.$src;
            }),
        );
        //
        $ql = QueryList::getInstance();
        // 注册插件，默认注册的方法名为: chrome
        $ql->use(Chrome::class);
        //
        $data =  $ql->chrome($this->heroListUrl)->rules($rules)->queryData();
        foreach($data as $key => $info)
        {
            $data[$key]['heroName'] = trim($info['heroName']);
        }
        say('尝试抓取所有英雄',$data);
        return $data;
    }

    public function triggerChrome_node_fail()
    {
        $ql = QueryList::getInstance();
        // 注册插件，默认注册的方法名为: chrome
        $ql->use(Chrome::class);
        $text = $ql->chrome(function ($page,$browser) {
            $page->goto($this->heroListUrl);
            // 页面截图
            $page->screenshot([
                'path' => 'page.png',
                'fullPage' => true
            ]);
            $html = $page->content();
            $browser->close();
            return $html;
        })->find('h1')->text();
    }
}
