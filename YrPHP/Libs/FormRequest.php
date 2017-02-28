<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */
namespace YrPHP;

use Closure;


class FormRequest
{
    public $validate = [];

    function __construct(Request $request)
    {
        $this->request = $request;
        $this->rules();
        $this->validate();
    }


    function rules()
    {
        $this->validate = [];
    }

    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @param bool $auto
     * @return array
     *
     * array('字段名' => array(array('验证规则', ['错误提示'],[ '值域'])));
     * 验证规则:
     * required: 字段不能为空
     * equal:值域:string|null 当值与之相等时，通过验证
     * notequal:值域:string|null 当值与之不相等时 通过验证
     * in:值域:array(1,2,3)|1,2,3 当值存在指定范围时 通过验证
     * notin: 值域:array(1,2,3)|1,2,3  当不存在指定范围时 通过验证
     * between: 值域:array(1,30)|1,30 当存在指定范围时 通过验证
     * notbetween:值域:array(1,30)|1,30 当不存在指定范围时 通过验证
     * length:值域:array(10,30)|10,30 当字符长度大于等于10，小于等于30时 通过验证 || array(30)|30 当字符等于30时 通过验证
     * unique:值域:string 当该字段在数据库中不存在该值时 通过验证
     * email：  当值为email格式时 通过验证
     * url：  当值为url格式时 通过验证
     * number:  当值为数字格式时 通过验证
     * regex:值域:正则表达式 //当符合正则表达式时 通过验证
     * phone：判断是否为手机号码
     * verifyCode：值域:session验证码key值（默认verify）  判断验证码的确与否
     * extend：值域：匿名函数 function(表单值,[ '值域'])
     *
     */
    function validate()
    {
        $res = false;
        $array = $this->request->post();

        foreach ($this->validate as $inputKey => $v) {

            $inputValue = isset($array[$inputKey]) ? $array[$inputKey] : '';

            foreach ($v as $validate) {
                if (empty($validate[1]))
                    $validate[1] = "错误:{$inputKey}验证不通过";

                if (!isset($validate[2]))
                    $validate[2] = null;

                $method = $validate[0];

                if ($method instanceof Closure) {
                    $res = call_user_func($method, $inputValue, $validate[2]);
                } else if (method_exists('\YrPHP\Validate', $method)) {
                    $res = Validate::$method($inputValue, $validate[2]);;
                }

                if (!$res) $error[$inputKey] = $validate[1];
            }
        }

        if (!empty($error)) {
            Session::set('errors', $error);
            $this->response($error);
            return false;
        }

        return $array;
    }


    public function response(array $errors)
    {
        if ($this->isAjax()) {
            exit($this->request->toJson(['error' => $errors]));
        }

        if ($this->isPost()) {
            gotoUrl($this->request->referer());
        }
    }

    public function __call($method, $args)
    {
        if (method_exists($this->request, $method)) {
            return call_user_func_array([$this->request, $method], $args);
        }
        return false;
    }


}