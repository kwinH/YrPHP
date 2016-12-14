<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


use App;

class Page
{
    public $url = '';//跳转链接URL,不配置 默认为当前页
    public $urlParam = array();// 分页跳转时要带的参数
    private $totalPages; // 分页总页面数

    public $totalRows; // 总行数
    public $listRows = 12;// 列表每页显示行数

    public $rollPage = 8;// 分页栏每页显示的页数
    public $p = 'p'; //分页参数名

    public $gotoPage = false;//是否显示select下拉跳转

    //在select下拉周围围绕一些标签
    public $gotoTagOpen = ""; //在select下拉跳转外围包裹标签
    public $gotoTagClose = ""; //在select下拉跳转外围包裹标签闭合

    //自定义“当前页”链接
    public $nowTagOpen = "<strong>";//在当前页外围包裹开始标签 默认<strong>
    public $nowPage = 1; //当前页 默认为'1'第一页
    public $nowTagClose = "</strong>";//在当前页外围包裹结束标签

    //添加封装标签 整个分页周围围绕一些标签
    public $fullTagOpen = "";//整个分页周围围绕一些标签开始标签
    public $fullTagClose = "";//整个分页周围围绕一些标签结束标签

    //自定义起始链接
    public $firstTagOpen = "";//在首页外围包裹开始标签
    public $firstLink = '首页';//你希望在分页中显示“首页”链接的名字 如果不想显示该标签 则设置为FALSE即可
    public $firstTagClose = "";//在首页外围包裹标签结束标签

    //自定义结束链接
    public $lastTagOpen = "";//在尾页外围包裹开始标签
    public $lastLink = '尾页';//你希望在分页中显示“尾页”链接的名字  如果不想显示该标签 则设置为FALSE即可
    public $lastTagClose = "";//在尾页外围包裹标签结束标签

    //自定义“上一页”链接
    public $prevTagOpen = "";//在上一页外围包裹开始标签
    public $prevLink = '上一页';//你希望在分页中显示“上一页”链接的名字 如果不想显示该标签 则设置为FALSE即可
    public $prevTagClose = "";//在上一页外围包裹开始标签

    //自定义“下一页”链接
    public $nextTagOpen = "";//在下一页外围包裹开始标签
    public $nextLink = '下一页';//你希望在分页中显示“下一页”链接的名字 如果不想显示该标签 则设置为FALSE即可
    public $nextTagClose = "";//在下一页外围包裹开始标签

    ////自定义其他页“数字”链接  如果不想显示该标签 将rollPage设置为0即可
    public $otherTagOpen = '';//在其他“数字”链接外围包裹开始标签
    public $otherTagClose = '';//在其他“数字”链接外围包裹结束标签


    public function __construct($config = array())
    {
        $this->init($config);
    }

    public function init($config = array())
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数

        $this->nowPage = empty($_GET[$this->p]) ? $this->nowPage : (int)$_GET[$this->p];//现在行

        $this->urlParam = empty($this->urlParam) ? $_GET : $this->urlParam;//参数

        if (isset($this->urlParam[$this->p])) unset($this->urlParam[$this->p]);

        $this->url = empty($this->url) ? getUrl(App::uri()->getPath()) : $this->url;
        $this->url .= '?' . (empty($this->urlParam) ? '' : http_build_query($this->urlParam) . '&');

        return $this;
    }

    /**
     * 显示
     */
    public function show()
    {
        $html = $this->fullTagOpen;
        $html .= $this->firstLink === false ? '' : $this->first();
        $html .= $this->prevLink === false ? '' : $this->prev();
        $html .= $this->pageList();
        $html .= $this->nextLink === false ? '' : $this->next();
        $html .= $this->lastLink === false ? '' : $this->last();
        $html .= $this->gotoPage ? $this->gotoPage() : '';
        $html .= $this->fullTagClose;
        echo $html;
    }

    /**
     * 第一页
     */
    private function first()
    {
        if ($this->nowPage > 1)
            return $this->firstTagOpen . '<a href="' . $this->url . $this->p . '=1">' . $this->firstLink . '</a>' . $this->firstTagClose;
    }

    /**
     * 上一页
     */
    private function prev()
    {

        if ($this->nowPage > 1) {
            $prevPage = $this->nowPage - 1;
            return $this->prevTagOpen . '<a href="' . $this->url . $this->p . '=' . $prevPage . '">' . $this->prevLink . '</a>' . $this->prevTagClose;
        }

    }

    /**
     * 其他页面
     * @return string
     */
    private function pageList()
    {
        $html = '';

        $leftPage = $this->nowPage - floor($this->rollPage / 2);

        if ($leftPage <= 0) {
            $leftPage = 1;
            $rightPage = $this->rollPage + $this->nowPage;
        } else {
            $rightPage = ceil($this->rollPage / 2) + $this->nowPage;
        }
        $rightPage = $rightPage > $this->totalPages ? $this->totalPages : $rightPage;
        for ($leftPage; $leftPage < $rightPage; $leftPage++) {
            if ($this->nowPage != $leftPage) {
                $html .= $this->otherTagOpen . '<a href="' . $this->url . $this->p . '=' . $leftPage . '">' . $leftPage . '</a>' . $this->otherTagClose;
            } else {
                $html .= $this->nowTagOpen . '<a href="' . $this->url . $this->p . '=' . $leftPage . '">' . $leftPage . '</a>' . $this->nowTagClose;
            }

        }
        return $html;
    }

    /**
     * 下一页
     */
    private function next()
    {

        if ($this->nowPage < $this->totalPages) {
            $nextPage = $this->nowPage + 1;
            return $this->nextTagOpen . '<a href="' . $this->url . $this->p . '=' . $nextPage . '">' . $this->nextLink . '</a>' . $this->nextTagClose;
        }


    }

    /**
     * 最后一页
     */
    private function last()
    {
        if ($this->nowPage < $this->totalPages)
            return $this->lastTagOpen . '<a href="' . $this->url . $this->p . '=' . $this->totalPages . '">' . $this->lastLink . '</a>' . $this->lastTagClose;
    }

    private function gotoPage()
    {

        $html = $this->gotoPageOpen . "<select onchange='javascript:location=\"{$this->url}{$this->p}=\"+this.value'>";

        for ($i = 0; $i <= $this->totalPages; $i++) {
            if ($i == $this->nowPage) $status = 'selected';
            $html .= '<option value="' . $i . '" ' . $status . '>' . $i . '</option>';
            $status = '';
        }

        $html .= '</select>' . $this->gotoPageClose;

        return $html;
    }
}