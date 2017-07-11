<?php
exit("This file should not be included, only analyzed by your IDE");
class arr{
/**
     * 不区分大小写的in_array实现
     * @param string $value
     * @param array $array
     * @return bool
     */
static function inIArray($value='',$array=array()){return YrPHP\Arr::inIArray($value,$array);}
/**
     * 在数组中搜索给定的值（不区分大小写），如果成功则返回相应的键名
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @return mixed
     */
static function arrayISearch($needle,$haystack,$strict=false){return YrPHP\Arr::arrayISearch($needle,$haystack,$strict);}
/**
     * 不区分key值大小写获取数组中的值
     * @param array $arr
     * @param string $key
     * @return mixed
     */
static function arrayIGet($arr=array(),$key=''){return YrPHP\Arr::arrayIGet($arr,$key);}
/**
     * 多维数组转一维数组
     * @param array $multi
     * @return array
     */
static function arrToOne($multi=array()){return YrPHP\Arr::arrToOne($multi);}
/**
     *  判断是不是索引数组
     * @param array $array
     * @return bool true ? 索引数组 : 不是索引数组
     */
static function isAssoc($array=array()){return YrPHP\Arr::isAssoc($array);}
/**
     * 使用“点”符号从数组中获取一个项。
     * @param array $arr
     * @param string $key
     * @param null $default
     * @return mixed
     */
static function get($arr=array(),$key='',$default=NULL){return YrPHP\Arr::get($arr,$key,$default);}
/**
     * 弹出指定key 并把值返回
     * @param array $arr
     * @param string $key
     * @return bool|mixed
     */
static function pop($arr=array(),$key=NULL,$default=NULL){return YrPHP\Arr::pop($arr,$key,$default);}
/**
     * 返回数组中指定的数组项
     * @param array $arr 指定数组
     * @param array $onlyKey 可以为多个参数或则单个数组格式
     * @return array
     */
static function only($arr=array(),$onlyKey=array()){return YrPHP\Arr::only($arr,$onlyKey);}
/**
     * 过滤数组中指定的数组项,并返回
     * @param array $arr 指定数组
     * @param array $exceptKey 可以为多个参数或则单个数组格式
     * @return array
     */
static function except($arr=array(),$exceptKey=array()){return YrPHP\Arr::except($arr,$exceptKey);}
}
class cart{
/**
     * 返回一个包含了购物车中所有信息的数组
     * @param null $mallMode 商城模式 true多商家(二维数组) false单商家（一维数组）默认为配置中的模式,当为单商家时，不管设置什么都返回一维数组
     * @param null $seller 返回指定商家下的所以产品，默认为null，返回所以商家，单商家下无效
     * @return array
     */
static function getContents($mallMode=NULL,$seller=NULL){return YrPHP\Cart::getContents($mallMode,$seller);}
/**
     * 返回一个包含了购物车中所有信息的数组
     * @return array
     */
static function contents(){return YrPHP\Cart::contents();}
/**
     * 添加单条或多条购物车项目
     * @param array $items
     * @param bool $accumulation 是否累加
     * @return bool|string
     */
static function insert($items=array(),$accumulation=true){return YrPHP\Cart::insert($items,$accumulation);}
/**
     * 添加单条购物车项目
     * @param array $items
     * @param bool $accumulation 是否累加
     * @return bool|string
     */
static function _insert($item=array(),$accumulation=false){return YrPHP\Cart::_insert($item,$accumulation);}
/**
     * 根据配置保存数据
     * @param array $cartContent
     * @return array
     */
static function saveCart($cartContent=NULL){return YrPHP\Cart::saveCart($cartContent);}
/**
     * 更新购物车中的项目 必须包含 rowId
     * @param $item 修改多个可为二维数组
     * @return bool
     */
static function update($items=array()){return YrPHP\Cart::update($items);}
/**
     * 修改单条项目
     * @param $item
     * @return bool
     */
static function _update($item){return YrPHP\Cart::_update($item);}
/**
     * 删除一条购物车中的项目  必须包含 rowId
     * @param null|array $rowId
     * @return bool
     */
static function remove($rowId=NULL){return YrPHP\Cart::remove($rowId);}
/**
     * 获得一条购物车的项目
     * @param null $rowId
     * @return bool|array
     */
static function getItem($rowId=NULL){return YrPHP\Cart::getItem($rowId);}
/**
     * 显示购物车中总共的项目数量
     * @param null $seller 商家标识符 单商家模式下无效
     * @return int
     */
static function totalItems($seller=NULL){return YrPHP\Cart::totalItems($seller);}
/**
     * 显示购物车中总共的商品数量
     * @param null $seller 商家标识符 单商家模式下无效
     * @return int
     */
static function totalQty($seller=NULL){return YrPHP\Cart::totalQty($seller);}
/**
     * 显示购物车中的总计金额  商家标识符 单商家模式下无效
     * @return int
     */
static function total($seller=NULL){return YrPHP\Cart::total($seller);}
/**
     * 根据rowId 查找商家
     * @param $key
     * @return bool|int|string 当为单商家模式时直接返回false,当找不到时也返回false，否则返回商家标识符
     */
static function searchSeller($rowId){return YrPHP\Cart::searchSeller($rowId);}
/**
     * 销毁购物车
     */
static function destroy(){return YrPHP\Cart::destroy();}

static function getError(){return YrPHP\Cart::getError();}
}
class config{
/**
     * @param string|array $key
     * @param string $value
     */
static function set($key='',$value=''){return YrPHP\Config::set($key,$value);}
/**
     * @param string|array $key
     */
static function delete($key){return YrPHP\Config::delete($key);}
/**
     * @param string $key
     * @param null $default
     * @return null
     */
static function get($key='',$default=NULL){return YrPHP\Config::get($key,$default);}

static function all(){return YrPHP\Config::all();}

static function load($fileName='',$key=NULL){return YrPHP\Config::load($fileName,$key);}
}
class crypt{

static function encrypt($input){return YrPHP\Crypt::encrypt($input);}

static function decrypt($encrypted){return YrPHP\Crypt::decrypt($encrypted);}
}
class curl{
/**
     * 设置需要获取的URL地址
     * [setUrl description]
     * @param string $url [description]
     */
static function setUrl($url=''){return YrPHP\Curl::setUrl($url);}
/**
     * @param string $option
     * @param string $value
     * @return $this
     */
static function setOptions($option='',$value=''){return YrPHP\Curl::setOptions($option,$value);}
/**
     * @param bool $verify 是否验证证书 默认false不验证
     * @param string $path 验证证书时，证书路径
     * @return $this
     */
static function sslVerify($verify=false,$path=''){return YrPHP\Curl::sslVerify($verify,$path);}
/**
     * 传递一个连接中需要的用户名和密码
     * @param array|string $userPassword 格式为：array('userName','password') 或则, "username:password"
     */
static function setUserPassword($userPassword=''){return YrPHP\Curl::setUserPassword($userPassword);}
/**
     * @param array $header //请求头
     */
static function setHeader($header=array()){return YrPHP\Curl::setHeader($header);}
/**
     * 启用时会发送一个常规的POST请求，默认类型为：App/x-www-form-urlencoded，就像表单提交的一样
     * @param array|string $data
     * @param string $enctype App|multipart  默认为application，文件上传请用multipart
     */
static function post($data=array(),$enctype='App'){return YrPHP\Curl::post($data,$enctype);}
/**
     * 启用时会发送一个常规的GET请求
     * @param array|string $data array('user'=>'admin','pass'=>'admin') | admin&admin
     * @return $this
     */
static function get($data=array()){return YrPHP\Curl::get($data);}
/**
     * 启用时会发送一个常规的DELETE请求
     * @param array $data
     * @return array
     */
static function delete($data=array()){return YrPHP\Curl::delete($data);}
/**
     * 启用时会发送一个常规的PUT请求
     * @param array $data
     * @return array
     */
static function put($data){return YrPHP\Curl::put($data);}
/**
     * 获得cookies
     * @param string $path 定义Cookie存储路径 必须使用绝对路径
     */
static function getCookie($path=''){return YrPHP\Curl::getCookie($path);}
/**
     * 设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
     * @param string|array $cookies 定义Cookie的值
     */
static function setCookie($cookies=array()){return YrPHP\Curl::setCookie($cookies);}
/**
     * 取出cookie，一起提交给服务器
     * @param string $path 定义Cookie存储路径 必须使用绝对路径
     */
static function setCookieFile($path=''){return YrPHP\Curl::setCookieFile($path);}
/**
     * 执行一个cURL会话 返回执行的结果
     * @param bool $debug 是否开启调试模式 如果为true将打印调试信息
     * @return mixed
     */
static function exec($debug=false){return YrPHP\Curl::exec($debug);}

static function getInfo(){return YrPHP\Curl::getInfo();}
}
class debug{
/**
     * 在脚本开始处调用获取脚本开始时间的微秒值
     */
static function start(){return YrPHP\Debug::start();}
/**
     *在脚本结束处调用获取脚本结束时间的微秒值
     */
static function stop(){return YrPHP\Debug::stop();}
/**
     * 添加调试消息
     * @param    string $msg 调试消息字符串
     * @param    int $type 消息的类型
     */
static function addMsg($msg,$type=0){return YrPHP\Debug::addMsg($msg,$type);}
/**
     * 已经实例化的自定义类集合
     * @return array
     */
static function newClasses(){return YrPHP\Debug::newClasses();}
/**
     *  返回被 include 和 require 文件名的 array
     * @return array
     */
static function getIncludedFiles(){return YrPHP\Debug::getIncludedFiles();}
/**
     * 调试时代码高亮显示
     * @param $str
     * @return mixed
     */
static function highlightCode($str){return YrPHP\Debug::highlightCode($str);}
/**
     * 输出调试消息
     */
static function message(){return YrPHP\Debug::message();}
/**
     *返回同一脚本中两次获取时间的差值
     */
static function spent($startTime=NULL,$stopTime=NULL){return YrPHP\Debug::spent($startTime,$stopTime);}
/**
     * 记录日志 保存到项目runtime下
     * @param $fileName 文件名
     * @param $content  内容
     */
static function log($content,$fileName=NULL){return YrPHP\Debug::log($content,$fileName);}
}
class file{
/**
     * 建立文件
     *
     * @param  string $aimUrl
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
static function createFile($aimUrl,$overWrite=false){return YrPHP\File::createFile($aimUrl,$overWrite);}
/**
     * 递归删除文件夹或文件
     *
     * @param  string $aimDir
     * @return  boolean
     */
static function rm($aimDir){return YrPHP\File::rm($aimDir);}
/**
     * 删除文件
     *
     * @param  string $aimUrl
     * @return  boolean
     */
static function unlinkFile($aimUrl){return YrPHP\File::unlinkFile($aimUrl);}
/**
     * 建立文件夹
     *
     * @param  string $aimUrl
     * @return  viod
     */
static function mkDir($aimUrl,$mode=511){return YrPHP\File::mkDir($aimUrl,$mode);}
/**
     * 移动文件
     *
     * @param  string $fileUrl
     * @param  string $aimUrl
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
static function moveFile($fileUrl,$aimUrl,$overWrite=false){return YrPHP\File::moveFile($fileUrl,$aimUrl,$overWrite);}
/**
     * 移动文件夹
     *
     * @param  string $oldDir
     * @param  string $aimDir
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
static function mv($oldDir,$aimDir,$overWrite=false){return YrPHP\File::mv($oldDir,$aimDir,$overWrite);}
/**
     * 复制文件
     *
     * @param  string $fileUrl
     * @param  string $aimUrl
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
static function copyFile($fileUrl,$aimUrl,$overWrite=false){return YrPHP\File::copyFile($fileUrl,$aimUrl,$overWrite);}
/**
     * 复制文件或则文件夹
     *
     * @param  string $oldDir
     * @param  string $aimDir
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
static function cp($oldDir,$aimDir,$overWrite=false){return YrPHP\File::cp($oldDir,$aimDir,$overWrite);}
/**
     * 修改文件名
     *$path 需要修改的文件路径
     *$name 修改后的文件路径及文件名
     * @return    bool
     */
static function rename($path,$name){return YrPHP\File::rename($path,$name);}
/**
     * 将字符串写入文件
     *
     * @param  string $filename 文件名
     * @param  boolean $str 待写入的字符数据
     */
static function vi($filename,$str){return YrPHP\File::vi($filename,$str);}
/**
     * 将整个文件内容读出到一个字符串中
     *
     * @param  string $filename 文件名
     * @return string
     */
static function readsFile($filename){return YrPHP\File::readsFile($filename);}
/**
     * 将文件内容读出到一个数组中
     *
     * @param  string $filename 文件名
     * @return array
     */
static function readFile2array($filename){return YrPHP\File::readFile2array($filename);}
/**
     * 转换目录下面的所有文件编码格式
     *
     * @param    string $in_charset 原字符集
     * @param    string $out_charset 目标字符集
     * @param    string $dir 目录地址
     * @param    string $fileexts 转换的文件格式
     * @return    string    如果原字符集和目标字符集相同则返回false，否则为true
     */
static function dirIconv($in_charset,$out_charset,$dir,$fileexts='php|html|htm|shtml|shtm|js|txt|xml'){return YrPHP\File::dirIconv($in_charset,$out_charset,$dir,$fileexts);}
/**
     * 根据关键词列出目录下所有文件
     *
     * @param    string $path 路径
     * @param    string $key 关键词
     * @param    array $list 增加的文件列表
     * @return    array    所有满足条件的文件
     */
static function dirList($path,$key='',$list=array()){return YrPHP\File::dirList($path,$key,$list);}
/**
     * 根据关键词列出目录下所有文件
     *
     * @param    string $path 路径
     * @param    string $key 关键词
     * @param    array $list 增加的文件列表
     * @return    array    所有满足条件的文件
     */
static function search($path,$key='',$list=array()){return YrPHP\File::search($path,$key,$list);}
/**
     * 转化 \ 为 /
     *
     * @param    string $path 路径
     * @return    string    路径
     */
static function dirPath($path){return YrPHP\File::dirPath($path);}
/**
     * 获取文件名后缀
     *
     * @param    string $filename
     * @return    string
     */
static function fileExt($filename){return YrPHP\File::fileExt($filename);}
/**
     * 获得文件相关信息
     * @param $filename 文件路径
     * @return array|bool
     * 将会返回包括以下单元的数组 array ：dirname(文件实在目录)、basename(文件名带后缀)、extension（文件后缀
     * 如果有）、filename(文件名不带后缀)、dev(设备名)、ino(inode 号码)、mode(inode 保护模式)、nlink(被连接数
     * 目)、uid(所有者的用户 id)、gid(所有者的组 id)、rdev(设备类型，如果是 inode 设备的话)、size(文件大小的
     * 字节数)、atime(上次访问时间（Unix 时间戳）)、ctime(上次改变时间（Unix 时间戳）)、blksize(文件系统 IO
     * 的块大小)、blocks(所占据块的数目)。
     *
     */
static function getFileInfo($filename){return YrPHP\File::getFileInfo($filename);}
/**
     * 设置目录下面的所有文件的访问和修改时间
     *
     * @param    string $path 路径
     * @param    int $mtime 修改时间
     * @param    int $atime 访问时间
     * @return    array    不是目录时返回false，否则返回 true
     */
static function dirTouch($path,$mtime='TIME',$atime='TIME'){return YrPHP\File::dirTouch($path,$mtime,$atime);}
/**
     * 目录列表
     *
     * @param    string $dir 路径
     * @param    int $parentid 父id
     * @param    array $dirs 传入的目录
     * @return    array    返回目录及子目录列表
     */
static function dirTree($dir,$parentid=0,$dirs=array()){return YrPHP\File::dirTree($dir,$parentid,$dirs);}
/**
     * 目录列表
     *
     * @param    string $dir 路径
     * @return    array    返回目录列表
     */
static function dirNodeTree($dir){return YrPHP\File::dirNodeTree($dir);}
/**
     * 获取目录大小
     *
     * @param    string $dirname 目录
     * @return    string      比特B
     */
static function getDirSize($dirname){return YrPHP\File::getDirSize($dirname);}
/**
     * 将字节转换成Kb或者Mb...
     * @param $size为字节大小
     */
static function bitSize($size){return YrPHP\File::bitSize($size);}

static function remoteFileExists($url_file){return YrPHP\File::remoteFileExists($url_file);}
}
class form{

static function date($name,$options=array(),$value=NULL){return App\Libs\Form::date($name,$options,$value);}

static function editor($name,$options=array(),$value=NULL){return App\Libs\Form::editor($name,$options,$value);}

static function _uploadImg($onclick='',$html=''){return App\Libs\Form::_uploadImg($onclick,$html);}

static function image($name,$options=array(),$value){return App\Libs\Form::image($name,$options,$value);}

static function images($name,$options=array(),$value){return App\Libs\Form::images($name,$options,$value);}

static function aloneShowTitle(){return App\Libs\Form::aloneShowTitle();}
/**
     * @param array $options
     * @param array $data
     * @return string
     */
static function open($options=array(),$data=array()){return App\Libs\Form::open($options,$data);}
/**
     * @return string
     */
static function close(){return App\Libs\Form::close();}
/**
     * Create a form label element.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
static function label($name,$value=NULL,$options=array()){return App\Libs\Form::label($name,$value,$options);}
/**
     * Generate a hidden field with the current CSRF token.
     *
     * @return string
     */
static function token(){return App\Libs\Form::token();}
/**
     *
     * @param $attributes
     * @return bool|string
     */
static function attributesToString($attributes){return App\Libs\Form::attributesToString($attributes);}
/**
     * Get the ID attribute for a field name.
     *
     * @param  string $name
     * @param  array $attributes
     *
     * @return string
     */
static function getIdAttribute($name,$attributes){return App\Libs\Form::getIdAttribute($name,$attributes);}

static function getValueAttribute($name,$value=NULL){return App\Libs\Form::getValueAttribute($name,$value);}
/**
     * Create a form input field.
     *
     * @param  string $type
     * @param  string $name
     * @param  array $options
     * @param  string $value
     *
     * @return string
     */
static function input($type,$name,$options=array(),$value=NULL){return App\Libs\Form::input($type,$name,$options,$value);}
/**
     * Create a text input field.
     *
     * @param $name
     * @param array $options
     * @param null $value
     * @return string
     */
static function text($name,$options=array(),$value=NULL){return App\Libs\Form::text($name,$options,$value);}
/**
     * Create a password input field.
     *
     * @param  string $name
     * @param  array $options
     *
     * @return string
     */
static function password($name,$options=array()){return App\Libs\Form::password($name,$options);}
/**
     * Create a hidden input field.
     *
     * @param  string $name
     * @param null $value
     * @param array $options
     *
     * @return string
     */
static function hidden($name,$value=NULL,$options=array()){return App\Libs\Form::hidden($name,$value,$options);}
/**
     * Create an e-mail input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function email($name,$options=array(),$value=NULL){return App\Libs\Form::email($name,$options,$value);}
/**
     * Create a tel input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function tel($name,$options=array(),$value=NULL){return App\Libs\Form::tel($name,$options,$value);}
/**
     * Create a number input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function number($name,$options=array(),$value=NULL){return App\Libs\Form::number($name,$options,$value);}
/**
     * Create a datetime input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function datetime($name,$options=array(),$value=NULL){return App\Libs\Form::datetime($name,$options,$value);}
/**
     * Create a datetime-local input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function datetimeLocal($name,$options=array(),$value=NULL){return App\Libs\Form::datetimeLocal($name,$options,$value);}
/**
     * Create a time input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function time($name,$options=array(),$value=NULL){return App\Libs\Form::time($name,$options,$value);}
/**
     * Create a url input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function url($name,$options=array(),$value=NULL){return App\Libs\Form::url($name,$options,$value);}
/**
     * Create a file input field.
     *
     * @param  string $name
     * @param  array $options
     *
     * @return string
     */
static function file($name,$options=array()){return App\Libs\Form::file($name,$options);}
/**
     * Create a textarea input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
static function textarea($name,$options=array(),$value=NULL){return App\Libs\Form::textarea($name,$options,$value);}
/**
     * Create a select box field.
     *
     * @param  string $name
     * @param  array $list
     * @param  array $options
     * @param  string $selected
     *
     * @return string
     */
static function select($name,$list=array(),$options=array(),$selected=NULL){return App\Libs\Form::select($name,$list,$options,$selected);}
/**
     * Create a checkable input field.
     *
     * @param  string $type
     * @param  string $name
     * @param  mixed $value
     * @param  bool $checked
     * @param  array $options
     *
     * @return string
     */
static function checkable($type,$name,$value,$checked,$options){return App\Libs\Form::checkable($type,$name,$value,$checked,$options);}
/**
     * Create a checkbox input field.
     *
     * @param  string $name
     * @param  mixed $value
     * @param  array $options
     * @param  bool $checked
     *
     * @return string
     */
static function checkbox($name,$value=1,$options=array(),$checked=NULL){return App\Libs\Form::checkbox($name,$value,$options,$checked);}
/**
     * Create a radio button input field.
     *
     * @param  string $name
     * @param  mixed $value
     * @param  array $options
     * @param  bool $checked
     *
     * @return string
     */
static function radio($name,$value=NULL,$options=array(),$checked=NULL){return App\Libs\Form::radio($name,$value,$options,$checked);}
/**
     * Create a HTML reset input element.
     *
     * @param  string $value
     * @param  array $attributes
     *
     * @return string
     */
static function reset($value,$attributes=array()){return App\Libs\Form::reset($value,$attributes);}
/**
     * Create a color input field.
     *
     * @param  string $name
     * @param  array $options
     * @param  string $value
     *
     * @return string
     */
static function color($name,$options=array(),$value=NULL){return App\Libs\Form::color($name,$options,$value);}
/**
     * Create a submit button element.
     *
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
static function submit($value=NULL,$options=array()){return App\Libs\Form::submit($value,$options);}
/**
     * Create a button element.
     *
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
static function button($value=NULL,$options=array()){return App\Libs\Form::button($value,$options);}
}
class image{

static function open($imgPath){return YrPHP\Image::open($imgPath);}

static function getImg($imgPath,$imgType){return YrPHP\Image::getImg($imgPath,$imgType);}
/**
     * 缩略图
     * @param $config
     * array('width'=>100,'height'=>100,'pre'=>0.5);
     * 如果设置了$Config['per']则按照$Config['per']比例缩放 否则按给定宽高 (X除于原图宽高的最小比例缩放)
     */
static function thumb($config){return YrPHP\Image::thumb($config);}
/**
     * 裁剪图像
     * @param  integer $w 裁剪区域宽度
     * @param  integer $h 裁剪区域高度
     * @param  integer|array $position 裁剪起始位置 有10种状态，0为随机位置；
     *                                 1为顶端居左，2为顶端居中，3为顶端居右；
     *                                 4为中部居左，5为中部居中，6为中部居右；
     *                                 7为底端居左，8为底端居中，9为底端居右；
     *                                 指定位置 array(100,100) | array('x'=>100,'y'=>100)
     * @param  integer $width 图像保存宽度 默认为裁剪区域宽度
     * @param  integer $height 图像保存高度 默认为裁剪区域高度
     */
static function cut($w,$h,$position=1,$width=NULL,$height=NULL){return YrPHP\Image::cut($w,$h,$position,$width,$height);}
/**
     * 为图片添加文字水印
     * @param    string $water array('str'=>'ok','font'=>'msyh.ttf','color'=>'#ffffff','size'=>20,'angle'=>0,)
     * str水印文字为必填 font字体 color默认黑色 size文字大小默认20，angle文字倾斜度默认0 暂只支持GIF,JPG,PNG格式
     * @param    int $position 水印位置，有10种状态，0为随机位置；
     *                                1为顶端居左，2为顶端居中，3为顶端居右；
     *                                4为中部居左，5为中部居中，6为中部居右；
     *                                7为底端居左，8为底端居中，9为底端居右；
     *                                指定位置 array(100,100) | array('x'=>100,'y'=>100)
     * @return    mixed
     */
static function text($water=array(),$position=0){return YrPHP\Image::text($water,$position);}
/**
     * 添加水印图片
     * @param  string $water 水印图片路径
     * @param  integer|array $position 水印位置
     * @param    int $position 水印位置，有10种状态，0为随机位置；
     *                                1为顶端居左，2为顶端居中，3为顶端居右；
     *                                4为中部居左，5为中部居中，6为中部居右；
     *                                7为底端居左，8为底端居中，9为底端居右；
     *                                指定位置 array(100,100) | array('x'=>100,'y'=>100)
     * @param  integer $alpha 水印透明度
     * @param  integer $waterConf array('width'=>100,'height'=>100) 调整水印大小 默认调用原图
     */
static function watermark($water,$position=0,$alpha=100,$waterConf=array()){return YrPHP\Image::watermark($water,$position,$alpha,$waterConf);}
/**
     * 保存图像
     * @param  string $imgname 图像保存名称
     * @param  string $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
     * @param  integer $quality 图像质量
     * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
     */
static function save($imgPath,$type=NULL,$quality=80,$interlace=true){return YrPHP\Image::save($imgPath,$type,$quality,$interlace);}
/**
     * 客服端下载
     * @param null $downFileName 文件名 默认为原文件名
     * @param null $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
     */
static function down($downFileName=NULL,$type=NULL){return YrPHP\Image::down($downFileName,$type);}
/**
     * 直接在浏览器显示图片
     * @param null $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
     * @return bool
     */
static function show($type=NULL){return YrPHP\Image::show($type);}
/**
     * 获得图片的基本信息
     * @return array(dirname,basename,extension,filename,width,height,type,mime)
     */
static function getInfo(){return YrPHP\Image::getInfo();}
}
class model{

static function connection($name){return YrPHP\Model::connection($name);}

static function getConnection(){return YrPHP\Model::getConnection();}

static function getConnectionInstance(){return YrPHP\Model::getConnectionInstance();}
/**
     * 返回当前终级类对象的实例
     * @param $db_config 数据库配置
     * @return object
     */
static function getInstance($dbConfig){return YrPHP\Model::getInstance($dbConfig);}

static function escapeId($field=''){return YrPHP\Model::escapeId($field);}

static function escape($value=''){return YrPHP\Model::escape($value);}
/**
     * 设置缓存
     * @param array $config
     * @return $this
     */
static function setCache($status=true){return YrPHP\Model::setCache($status);}

static function setOptions($attributes=array()){return YrPHP\Model::setOptions($attributes);}
/**
     * @param string $where
     * @param string $logical
     * @return $this
     */
static function where($where='',$logical='and'){return YrPHP\Model::where($where,$logical);}
/**
     * @param string $where
     * @param string $logical
     * @return $this
     */
static function having($where='',$logical='and'){return YrPHP\Model::having($where,$logical);}
/**
     * @param string $where id=1 || ['id'=>1,'or id'=>2,'age >'=>15,'or id in'=>[1,2,3,4,5]]
     * @param string $logical and | or
     * @param string $type where | having
     * @return $this
     */
static function condition($where='',$logical='and',$type='where'){return YrPHP\Model::condition($where,$logical,$type);}
/**
     * @param string $sql
     * @param string $method
     * @return $this
     */
static function orderOrGroup($sql='',$method='order'){return YrPHP\Model::orderOrGroup($sql,$method);}
/**
     * @param string $sql “id desc,createTime desc”=>“order by id desc,createTime desc”
     * @return Model
     */
static function order($sql=''){return YrPHP\Model::order($sql);}
/**
     * @param string $sql “name,price”=>“group by name,price”
     * @return Model
     */
static function group($sql=''){return YrPHP\Model::group($sql);}
/**
     * @param array $field
     * @return $this
     */
static function select($field=array()){return YrPHP\Model::select($field);}
/**
     * @param array $field
     * @param string $tableName
     * @param bool $auto
     * @return $this
     */
static function except($field=array()){return YrPHP\Model::except($field);}
/**
     * @param string $tableName
     * @param int $auto 1 自动添加前缀
     * @return $this
     */
static function table($tableName='',$auto=true){return YrPHP\Model::table($tableName,$auto);}

static function setTempTableName($tableName='',$auto=true){return YrPHP\Model::setTempTableName($tableName,$auto);}

static function getTempTableName(){return YrPHP\Model::getTempTableName();}
/**
     * 获得表名
     * @return null|string
     */
static function getTable(){return YrPHP\Model::getTable();}

static function buildSql(){return YrPHP\Model::buildSql();}
/**
     * @return $this
     */
static function get($tableName='',$auto=true){return YrPHP\Model::get($tableName,$auto);}
/**
     * 以主键为条件 查询
     * @param int $id 查询的条件主键值
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
static function find($id=0,$assoc=false){return YrPHP\Model::find($id,$assoc);}
/**
     * @param bool $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
static function all($assoc=false){return YrPHP\Model::all($assoc);}
/**
     * 清除上次组合的SQL记录，避免重复组合
     */
static function cleanLastSql(){return YrPHP\Model::cleanLastSql();}
/**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return $this
     */
static function limit($offset,$length=NULL){return YrPHP\Model::limit($offset,$length);}
/**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return $this
     */
static function page($page,$listRows=NULL){return YrPHP\Model::page($page,$listRows);}
/**
     * @param $table 表名称
     * @param $cond  连接条件
     * @param string $type 连接类型
     * @param bool $auto 是否自动添加表前缀
     * @return $this
     */
static function join($table='',$cond=array(),$type='',$auto=true){return YrPHP\Model::join($table,$cond,$type,$auto);}
/**
     * 临时关闭预处理功能
     * @return $this
     */
static function closePreProcess(){return YrPHP\Model::closePreProcess();}
/**
     * @param $data
     * @param $assoc
     * @return mixed
     */
static function getDataPreProcessFill($data,$assoc){return YrPHP\Model::getDataPreProcessFill($data,$assoc);}
/**
     * @param $filed
     * @param $data
     * @return mixed
     */
static function setDataPreProcessFill($filed,$data){return YrPHP\Model::setDataPreProcessFill($filed,$data);}
/**
     * @param string $type
     * @return array
     */
static function getDataPreProcessAttr($type='get'){return YrPHP\Model::getDataPreProcessAttr($type);}

static function getMutatedAttributes(){return YrPHP\Model::getMutatedAttributes();}

static function cacheMutatedAttributes($class){return YrPHP\Model::cacheMutatedAttributes($class);}
/**
     * @param string $dbCacheFile 缓存文件
     * @param string $type 返回的数据类型 object|array
     * $openCache  bool|true 是否开启缓存
     * @return mixed 返回数据
     */
static function cache($assoc=false,$row='result'){return YrPHP\Model::cache($assoc,$row);}
/**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
static function row($assoc=false){return YrPHP\Model::row($assoc);}
/**
     * 返回数据集合
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
static function result($assoc=false){return YrPHP\Model::result($assoc);}
/**
     * @param string $tableName //数据库表名
     * @param string|array $where 条件
     * @return int 返还受影响行数
     */
static function delete($where=''){return YrPHP\Model::delete($where);}
/**
     * 添加数据 如果主键冲突 则修改
     * @param $data
     * @return bool|int
     */
static function duplicateKey($data){return YrPHP\Model::duplicateKey($data);}
/**
     * 添加单条数据
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @param string $act
     * @return int 受影响行数
     */
static function insert($data=array(),$act='INSERT'){return YrPHP\Model::insert($data,$act);}
/**
     * 添加单条数据 如已存在则替换
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
static function replace($data=array()){return YrPHP\Model::replace($data);}
/**
     * 预处理，添加多条数据
     * @param array $data 添加的数据 单条：[filed=>val]| 多条：[[filed=>val],[filed=>val]]
     * @param string $act
     * @return int 受影响行数
     */
static function inserts($data=array(),$act='INSERT'){return YrPHP\Model::inserts($data,$act);}
/**
     * 预处理添加多条数据 如已存在则替换
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @return int 受影响行数
     */
static function replaces($data=array()){return YrPHP\Model::replaces($data);}
/**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @param bool $auto
     * @return array|bool
     */
static function check($array){return YrPHP\Model::check($array);}
/**
     * 自动获取表结构
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return array|bool
     */
static function tableField(){return YrPHP\Model::tableField();}
/**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
static function rowCount(){return YrPHP\Model::rowCount();}
/**
     * @param string $sql
     * @param array $parameters array|''
     * @return $this|\PDOStatement
     */
static function query($sql='',$parameters=array()){return YrPHP\Model::query($sql,$parameters);}
/**
     * @param array $data 更改的数据
     * @param string $tableName 数据库表名
     * @param string|array $where 更改条件
     * @param bool $auto 是否自动添加表前缀
     * @return int 返回受影响行数
     */
static function update($data=array(),$where=''){return YrPHP\Model::update($data,$where);}
/**
     * 所有sql语句
     * @return array
     */
static function history(){return YrPHP\Model::history();}
/**
     * 最后一条sql语句
     * @return mixed
     */
static function lastQuery(){return YrPHP\Model::lastQuery();}
/**
     * 最后一条sql语句
     * @return mixed
     */
static function lastSql(){return YrPHP\Model::lastSql();}
/**
     * SQL历史记录
     * @return array
     */
static function historySql(){return YrPHP\Model::historySql();}
/**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
static function startTrans(){return YrPHP\Model::startTrans();}
/**
     * 事务回滚
     * @return bool 成功返回true，失败返回false
     */
static function rollback(){return YrPHP\Model::rollback();}
/**
     * 提交事务
     * @return bool 成功返回true，失败返回false
     */
static function commit(){return YrPHP\Model::commit();}
/**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
static function transaction($callback){return YrPHP\Model::transaction($callback);}
/**
     * 获取最后一次插入的自增值
     * @return bool|int 成功返回最后一次插入的id，失败返回false
     */
static function getLastId(){return YrPHP\Model::getLastId();}
/**
     * 创建数据库，并且主键是id
     * @param string $tableName 表名
     * @param string $key 主键
     * @param string $engine 引擎 默认InnoDB
     * @param bool $auto 是否自动添加表前缀
     */
static function createTable($tableName='',$key='id',$engine='InnoDB',$auto=true){return YrPHP\Model::createTable($tableName,$key,$engine,$auto);}
/**
     * 删除表
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
static function dropTable($tableName='',$auto=true){return YrPHP\Model::dropTable($tableName,$auto);}
/**
     * 检测表是否存在，也可以获取表中所有字段的信息(表里所有字段的信息)
     * @param string $tableName 要查询的表名
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
static function checkTable($tableName='',$auto=true){return YrPHP\Model::checkTable($tableName,$auto);}
/**
     * 检测字段是否存在，也可以获取字段信息(只能是一个字段)
     * @param string $field 字段名
     * @param string $tableName 表名
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
static function checkField($field='',$tableName='',$auto=true){return YrPHP\Model::checkField($field,$tableName,$auto);}
/**
     * @param array $info 字段信息数组
     * @param string $tableName 表名
     * @param bool $auto 是否自动添加表前缀
     * @return array 字段信息
     */
static function addField($info=array(),$tableName='',$auto=true){return YrPHP\Model::addField($info,$tableName,$auto);}
/**
     * 修改字段
     * 不能修改字段名称，只能修改字段类型、默认值、注释
     * @param array $info
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
static function editField($info=array(),$tableName='',$auto=true){return YrPHP\Model::editField($info,$tableName,$auto);}

static function filterFieldInfo($info=array()){return YrPHP\Model::filterFieldInfo($info);}
/**
     * 删除字段
     * 如果返回了字段信息则说明删除失败，返回false，则为删除成功
     * @param string $field
     * @param string $tableName
     * @param bool $auto
     * @return mixed
     */
static function dropField($field='',$tableName='',$auto=true){return YrPHP\Model::dropField($field,$tableName,$auto);}
/**
     * 获取指定表中指定字段的信息(多字段)
     * @param array $field
     * @param string $tableName
     * @param bool $auto
     * @return array
     */
static function getFieldInfo($field=array(),$tableName='',$auto=true){return YrPHP\Model::getFieldInfo($field,$tableName,$auto);}
}
class page{

static function init($config=array()){return YrPHP\Page::init($config);}
/**
     * 显示
     */
static function show(){return YrPHP\Page::show();}
/**
     * 第一页
     */
static function first(){return YrPHP\Page::first();}
/**
     * 上一页
     */
static function prev(){return YrPHP\Page::prev();}
/**
     * 其他页面
     * @return string
     */
static function pageList(){return YrPHP\Page::pageList();}
/**
     * 下一页
     */
static function next(){return YrPHP\Page::next();}
/**
     * 最后一页
     * @return string
     */
static function last(){return YrPHP\Page::last();}

static function gotoPage(){return YrPHP\Page::gotoPage();}
}
class pipeline{
/**
     * 设置需要处理的对象
     * @param $request
     * @return $this
     */
static function send($request){return YrPHP\Pipeline::send($request);}
/**
     * 需要经过哪些中间件处理
     * @param $pipes
     * @return $this
     */
static function through($pipes){return YrPHP\Pipeline::through($pipes);}
/**
     * 开始流水线处理
     * @param \Closure
     * @return \Closure
     */
static function then($first){return YrPHP\Pipeline::then($first);}
/**
     * 包装迭代对象到闭包
     * @return \Closure
     */
static function getSlice(){return YrPHP\Pipeline::getSlice();}
}
class request{
/**
     * 支持连贯查询
     * @param $keys
     * @return $this
     */
static function except($keys){return YrPHP\Request::except($keys);}

static function only($keys){return YrPHP\Request::only($keys);}

static function header($key=NULL,$default=NULL){return YrPHP\Request::header($key,$default);}

static function get($key=NULL,$default=NULL){return YrPHP\Request::get($key,$default);}

static function post($key=NULL,$default=NULL){return YrPHP\Request::post($key,$default);}

static function all($data=NULL){return YrPHP\Request::all($data);}

static function replace($data,$method='get'){return YrPHP\Request::replace($data,$method);}

static function merge($data,$method='get'){return YrPHP\Request::merge($data,$method);}

static function pop($keys,$method='get'){return YrPHP\Request::pop($keys,$method);}

static function filter($data=array()){return YrPHP\Request::filter($data);}

static function part($n=NULL,$no_result=NULL){return YrPHP\Request::part($n,$no_result);}
/**
     * 返回路由替换过后的uri 数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
static function rpart($n=NULL,$no_result=NULL){return YrPHP\Request::rpart($n,$no_result);}
/**
     *返回没有经过路由替换过的uri字符串
     * @return string
     */
static function getPath(){return YrPHP\Request::getPath();}

static function is($rule){return YrPHP\Request::is($rule);}
/**
     * 返回经过路由替换过的uri字符串
     * @return string
     */
static function getRPath(){return YrPHP\Request::getRPath();}
/**
     * 在问号 ? 之后的所有字符串
     * @return mixed
     */
static function getQuery(){return YrPHP\Request::getQuery();}
/**
     * 判断是不是 AJAX 请求
     * 测试请求是否包含HTTP_X_REQUESTED_WITH请求头。
     * @return    bool
     */
static function isAjax(){return YrPHP\Request::isAjax();}

static function method(){return YrPHP\Request::method();}
/**
     * 判断是不是 POST 请求
     * @return    bool
     */
static function isPost(){return YrPHP\Request::isPost();}
/**
     * 判断是否SSL协议
     * @return boolean
     */
static function isHttps(){return YrPHP\Request::isHttps();}

static function port(){return YrPHP\Request::port();}

static function host(){return YrPHP\Request::host();}

static function currentUrl(){return YrPHP\Request::currentUrl();}

static function referer(){return YrPHP\Request::referer();}
}
class response{

static function redirect($url){return YrPHP\Response::redirect($url);}

static function status($code){return YrPHP\Response::status($code);}

static function sendHeader($headers){return YrPHP\Response::sendHeader($headers);}

static function json($data=array(),$code=200){return YrPHP\Response::json($data,$code);}

static function jsonp($data=array(),$code=200){return YrPHP\Response::jsonp($data,$code);}

static function errorBackTo($errors,$url=NULL){return YrPHP\Response::errorBackTo($errors,$url);}

static function successBackTo($message,$url=NULL){return YrPHP\Response::successBackTo($message,$url);}
}
class session{

static function init(){return YrPHP\Session::init();}
/**
     * @param string|array $key
     * @param string $value
     */
static function set($key='',$value=''){return YrPHP\Session::set($key,$value);}
/**
     * Flash a key / value pair to the session.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
static function flash($key,$value){return YrPHP\Session::flash($key,$value);}
/**
     * @param string $key
     * @param null $default
     * @return null
     */
static function get($key='',$default=NULL){return YrPHP\Session::get($key,$default);}

static function all(){return YrPHP\Session::all();}
/**
     * @param string|array $key
     */
static function delete($key){return YrPHP\Session::delete($key);}

static function clear(){return YrPHP\Session::clear();}
/**
     * 销毁session
     * @return void
     */
static function destroy(){return YrPHP\Session::destroy();}
}
class upload{

static function init($config=array()){return YrPHP\Upload::init($config);}
/**
     * 上传文件
     * @param 文件信息数组 $field ，上传文件的表单名称  默认是 $_FILES数组
     */
static function uploadFile($field=''){return YrPHP\Upload::uploadFile($field);}

static function uploadMulti($files=array()){return YrPHP\Upload::uploadMulti($files);}

static function uploadOne($file=array()){return YrPHP\Upload::uploadOne($file);}
/**
     *检查文件大小是否合法
     * @param integer $fileSize 数据
     */
static function checkSize($fileSize=0){return YrPHP\Upload::checkSize($fileSize);}
/**
     * 返回文件拓展后缀
     * @param $path
     * @return string
     */
static function getExtension($path){return YrPHP\Upload::getExtension($path);}
/** 检查上传的文件MIME类型是否合法
     * @param array $file
     * @return bool
     */
static function checkFileType($file=array()){return YrPHP\Upload::checkFileType($file);}

static function getFileInfo($inputName=NULL){return YrPHP\Upload::getFileInfo($inputName);}
/**
     * 根据错误代码获得上传出错信息
     * @param null $errorNum
     * @return string
     */
static function getError($errorCode=NULL){return YrPHP\Upload::getError($errorCode);}
/**
     * | MIME TYPES
     * | -------------------------------------------------------------------
     * | This file contains an array of mime types.  It is used by the
     * | Upload class to help identify allowed file types.
     *
     * @param string $ext
     * @return bool
     */
static function checkMimes($ext=''){return YrPHP\Upload::checkMimes($ext);}
}
class uri{
/**
     * 解析URL
     * @return array|string
     */
static function parseUrl(){return YrPHP\Uri::parseUrl();}
/**
     * 返回路由替换过后的uri 数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
static function rsegment($n=NULL,$no_result=NULL){return YrPHP\Uri::rsegment($n,$no_result);}

static function rpart($n=NULL,$no_result=NULL){return YrPHP\Uri::rpart($n,$no_result);}
/**
     * 路由验证
     * @return mixed|string
     */
static function parseRoutes(){return YrPHP\Uri::parseRoutes();}

static function segment($n=NULL,$no_result=NULL){return YrPHP\Uri::segment($n,$no_result);}

static function part($n=NULL,$no_result=NULL){return YrPHP\Uri::part($n,$no_result);}
/**
     *返回没有经过路由替换过的uri字符串
     * @return string
     */
static function getPath(){return YrPHP\Uri::getPath();}
/**
     * 返回经过路由替换过的uri字符串
     * @return string
     */
static function getRPath(){return YrPHP\Uri::getRPath();}
/**
     * 在问号 ? 之后的所有字符串
     * @return mixed
     */
static function getQuery(){return YrPHP\Uri::getQuery();}
}
class validate{
/**
     * 判断是否为空值，当数据不为空时 return true
     * @param null $value
     * @return bool
     */
static function required($value=NULL){return YrPHP\Validate::required($value);}
/**
     * 当两个值相等时 return true
     * @param string $value
     * @param string $val
     * @return bool
     */
static function equal($value=NULL,$val=NULL){return YrPHP\Validate::equal($value,$val);}
/**
     * 当两个不值相等时 return true
     * @param string $value
     * @param string $val
     * @return bool
     */
static function notEqual($value=NULL,$val=NULL){return YrPHP\Validate::notEqual($value,$val);}
/**
     * 当存在指定范围时return true
     * @param string $value
     * @param array|string $range
     * @return bool
     */
static function in($value='',$range=''){return YrPHP\Validate::in($value,$range);}
/**
     * 当不存在指定范围时return true
     * @param null $value
     * @param array|string $range
     * @return bool
     */
static function notIn($value='',$range=''){return YrPHP\Validate::notIn($value,$range);}
/**
     * 当存在指定范围时return true
     * @param null $value
     * @param array|string $range
     * @return bool
     */
static function between($value='',$range=''){return YrPHP\Validate::between($value,$range);}
/**
     * 当不存在指定范围时return true
     * @param null $value
     * @param array|string $range
     * @return bool
     */
static function notBetween($value='',$range=''){return YrPHP\Validate::notBetween($value,$range);}
/**
     * 当数据库中值存在时 return false
     * @param $val 值
     * @param $tableName 表名
     * @param $field 字段名
     * @return bool
     */
static function unique($value,$tableName,$field){return YrPHP\Validate::unique($value,$tableName,$field);}
/**
     * 当字符长度存在指定范围时return true
     * @param null $value 字符串
     * @param array|string $range 范围
     * @return bool
     * length('abc',$rage = 3); strlen('abc') ==3
     * length('abc',$rage = array(5,3))==length('abc',$rage = array(3,5)) => strlen('abc') >=3 && strlen('abc') <=5
     */
static function length($value='',$range=''){return YrPHP\Validate::length($value,$range);}
/**
     * Email格式验证
     * @param    string $value 需要验证的值
     */
static function email($value){return YrPHP\Validate::email($value);}
/**
     * URL格式验证
     * @param    string $value 需要验证的值
     */
static function url($value){return YrPHP\Validate::url($value);}
/**
     * 数字格式验证
     * @param    string $value 需要验证的值
     */
static function number($value){return YrPHP\Validate::number($value);}
/**
     * 使用自定义的正则表达式进行验证
     * @param    string $value 需要验证的值
     * @param    string $rules 正则表达式
     */
static function regex($value,$rules){return YrPHP\Validate::regex($value,$rules);}
/**
     * 判断是否为手机号码
     * @param    string $value 手机号码
     */
static function phone($value=''){return YrPHP\Validate::phone($value);}
/**
     * 判断验证码的确与否
     * @param string $value 值
     * @param string $code session中的key
     * @return bool
     */
static function verifyCode($value='',$code='verify'){return YrPHP\Validate::verifyCode($value,$code);}
/**
     * @param $name
     * @param Closure $paramenters
     *
     * @example
    Validate::extend('test', function ($key, $val) {
     * if ($key > $val) return true;
     * return false;
     * });
     * var_dump(Validate::test(3, 2)); true
     */
static function extend($ruleName,$callback){return YrPHP\Validate::extend($ruleName,$callback);}
}
class verifyCode{

static function init($config=array()){return YrPHP\VerifyCode::init($config);}
/**
     * @param string $code 验证码key,用于session获取，默认verify
     * @param bool $line 是否显示干扰线
     * @param bool $pixel 是否显示干扰点
     */
static function show($code='verify',$line=true,$pixel=true){return YrPHP\VerifyCode::show($code,$line,$pixel);}

static function setText(){return YrPHP\VerifyCode::setText();}
/**
     * 获得随机字
     */
static function getStr(){return YrPHP\VerifyCode::getStr();}
/**
     * 设置背景颜色
     * @return $this
     */
static function setBackColor(){return YrPHP\VerifyCode::setBackColor();}
/**
     * 获得随机色
     * @return int
     */
static function getRandColor($alpha=false){return YrPHP\VerifyCode::getRandColor($alpha);}
/**
     * 添加干扰点
     * @return $this
     */
static function interferingPixel(){return YrPHP\VerifyCode::interferingPixel();}
/**
     * 添加干扰线
     * @return $this
     */
static function interferingLine(){return YrPHP\VerifyCode::interferingLine();}
}
class view{

static function setConfig($config=array()){return YrPHP\View::setConfig($config);}
/**
     * 将PHP中分配的值会保存到成员属性$tplVars中，用于将板中对应的变量进行替换
     * @param    string $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    mixed $value 需要一个标量类型的值，用来分配给模板中变量的值
     */
static function assign($tplVar,$value=NULL){return YrPHP\View::assign($tplVar,$value);}
/**
     * 加载指定目录下的模板文件，并将替换后的内容生成组合文件存放到另一个指定目录下
     * @param    string $fileName 提供模板文件的文件名
     * @param    array $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    string 当$cacheId为false时，不会生成缓存文件，其他情况做为缓存ID,当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
static function display($fileName,$tplVars='',$cacheId=''){return YrPHP\View::display($fileName,$tplVars,$cacheId);}

static function buildTplFile($fileName,$tplVars=''){return YrPHP\View::buildTplFile($fileName,$tplVars);}

static function getComFileName($fileName){return YrPHP\View::getComFileName($fileName);}

static function tplReplace($content){return YrPHP\View::tplReplace($content);}

static function setBlock($data){return YrPHP\View::setBlock($data);}

static function blockExtends(){return YrPHP\View::blockExtends();}
/**
     * 静态化
     * @param    string $cacheId 缓存ID 当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
static function init($cacheId=''){return YrPHP\View::init($cacheId);}
/**
     *  生成静态文件
     */
static function setCache(){return YrPHP\View::setCache();}
/**
     * 清空缓存 默认清空所以缓存
     * @param    string $template 当$file为目录时 清除指定模版（类名_方法）
     * @param    string $cacheId 清除指定模版ID
     */
static function clearCache($template='',$cacheId=''){return YrPHP\View::clearCache($template,$cacheId);}
/**
     * 清空文件夹 默认清空所有文件
     * @param    string $file 目录或则目录地址 当是目录时 清空目录内所有文件
     * @param    string $template 当$file为目录时 清除指定模版（类名_方法）
     */
static function delDir($file,$template=''){return YrPHP\View::delDir($file,$template);}
}
