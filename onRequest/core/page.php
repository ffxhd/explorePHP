<?php
namespace onRequest\core;
//本类必需的处理
/* $page = $_GET['p'];
 $page = $page==null||!is_numeric($page)?1:intval($page);
 $totalPage=ceil($totalRows/$pageSize);//总页数
 $page = $page > $total_pages?$total_pages:$page; */
class page
{
    /**根据页码，返回偏移量 ，便于取该页码的数据
     * @param integer $page 页码
     * @param integer $totalPage 总页数
     * @param integer $pageSize  每页显示条数
     * @return integer
     */
    public static function getOffsetByPage($page,$totalPage,$pageSize)
    {
        if( $totalPage === 0)
        {
            return 0;
        }
        if( $page < 1 || $page === null ||  false === is_numeric($page) )
        {
            $page = 1;
        }
        if($page >= $totalPage)
        {
            $page = $totalPage;
        }
        $offset = ( $page - 1 ) * $pageSize;//偏移量;
        //跳过前面$offset条记录,从第$offset+1条记录开始读取,读取$pageSize条记录
        return $offset;
    }

    /**总页数
     * @param int $totalRows
     * @param  int $pageSize
     * @return float
     */
    public static function getTotalPage($totalRows,$pageSize)
    {
        $totalRows = intval($totalRows);
        return  $totalRows === 0 ? 0 : ceil($totalRows/$pageSize);//总页数
    }

    /**返回分页条，需配合page.css,page.js $url本身带参数
     * @param $page
     * @param int $totalRows 数据总条数
     * @param int $pageSize 每页显示条数
     * @param int $showPage 显示几个页码
     * @param null $url
     * @param bool $needArray
     * @return array|string 页码条
     */
    public static function getPageBanner($page,$totalRows,$pageSize,$showPage,$url=null,$needArray= false)
    {
        // 计算页数
        $total_pages = ceil ( $totalRows / $pageSize );

        $page = $page==null||!is_numeric($page)? 1 : intval($page);
        $page = $page > $total_pages? $total_pages : $page;
        // 计算偏移量
        $showPage = $showPage < 3 ? 3 : $showPage;
        $pageoffset = ($showPage - 1) / 2;
        //url
        $url= $url==null?$_SERVER ['PHP_SELF'].'?':$url.'&';

        // 首页
        $page_banner = "<div class='page'>";
        $bannerArr = array();
        if ($page > 1)
        {
            $page_banner .= '<a href="' . $url . 'p=1">首页</a>';
            $page_banner .= '<a href="' . $url . 'p=' . ($page - 1) . '">上一页</a>';
            /* $bannerArr[]  = array('首页'   => $url . 'p=1') ;
             $bannerArr[]  = array('上一页' => $url . 'p=' . ($page - 1));*/
            $bannerArr['首页']   = $url . 'p=1' ;
            $bannerArr['上一页'] = $url . 'p=' . ($page - 1);
        }
        else
        {
            $page_banner .= "<span class='disable'>首页</a></span>";
            $page_banner .= "<span class='disable'>上一页</a></span>";
            /* $bannerArr[]  = array( '首页' => null) ;
             $bannerArr[]  = array('上一页' => null);*/
            $bannerArr['首页']   =  null ;
            $bannerArr['上一页'] = null;
        }

        // 初始化
        $start = 1;
        $end = $total_pages;
        if ($total_pages > $showPage)
        {
            if ($page > $pageoffset + 1)
            {
                $page_banner .= "...";
                //$bannerArr[]  = array( '...' => null );
                $bannerArr['head']  = '...' ;
            }
            if ($page > $pageoffset)
            {
                $start = $page - $pageoffset;
                $end = $total_pages > $page + $pageoffset ? $page + $pageoffset : $total_pages;
            }
            else
            {
                $start = 1;
                $end = $total_pages > $showPage ? $showPage : $total_pages;
            }
            if ($page + $pageoffset > $total_pages)
            {
                $start = $start - ($page + $pageoffset - $end);
            }
        }

        for($i = $start; $i <= $end; $i ++)
        {
            if ($page == $i)
            {
                $page_banner .= "<span class='current'>" . $i . "</span>";
                //$bannerArr[]  = array( $i=>'current' );
                $bannerArr[$i]  = 'current';
            }
            else
            {
                $page_banner .= '<a href="' . $url . 'p=' . $i . '">' . $i . '</a>';
                //$bannerArr[]  =array( $i => $url . 'p=' . $i ) ;
                $bannerArr[$i]  = $url . 'p=' . $i  ;
            }
        }
        // 尾部省略
        if ($total_pages > $showPage && $total_pages > $page + $pageoffset)
        {
            $page_banner .= "...";
            // $bannerArr[]  = array('...'=>null);
            $bannerArr['tail']  = '...';
        }

        if ($page < $total_pages)
        {
            $page_banner .= '<a href="' . $url . 'p=' . ($page + 1) . '">下一页</a>';
            $page_banner .= '<a href="' . $url . 'p=' . $total_pages . '">末页</a>';
            /*$bannerArr[]  = array('下一页' =>  $url . 'p=' . ($page + 1));
            $bannerArr[]  = array('末页'   =>  $url . 'p=' . $total_pages);*/
            $bannerArr['下一页'] =  $url . 'p=' . ($page + 1);
            $bannerArr['末页']   =   $url . 'p=' . $total_pages;
        } else
        {
            $page_banner .= "<span class='disable'>下一页</a></span>";
            $page_banner .= "<span class='disable'>末页</a></span>";
            /* $bannerArr[]  = array('下一页'=> null);
             $bannerArr[]  = array('末页'  => null);*/
            $bannerArr['下一页']  =  null;
            $bannerArr['末页']    =   null;
        }
        $page_banner .= "共" . $totalRows . "条,共" . $total_pages . "页";
        // $bannerArr[]  = '共' . $totalRows . '条,共' . $total_pages . '页';
        //$bannerArr['extra']  = array('totalRows'=>$totalRows,'totalPage'=>$total_pages);
        if($url==null)
        {
            $page_banner .= 'form  method="get">
第<input type="text" size="1" name="p"/>页，
<button type="submit">确定</button>
</form></div>';
            //echo $page_banner;
        }
        return  $needArray?$bannerArr : $page_banner;
        /*有$url form  method="get" onsubmit="sentP('admin.php?controller=admin&method=contentList&');return false;">
        第<input type="text" size="1" name="p" id="page"/>页，
        <button type="submit">确定</button>
        </form> */
        //Smaty注册 $url
        /* <form  method="get" onsubmit="sentP('{$url}');return false;">
        第<input type="text" size="1" name="p" id="page"/>页，
        <button type="submit">确定</button>
        </form> */
    }
}
