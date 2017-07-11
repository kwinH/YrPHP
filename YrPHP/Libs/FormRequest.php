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
    /**
     * @var Request
     */
    public $request;

    /**
     * FormRequest constructor.
     * @param Request $request
     * @param array $validates
     */
    public function __construct(Request $request, $validates = [])
    {
        $this->request = $request;
        $this->validate($validates);
    }

    /**
     * 设置验证规则
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @param bool $auto
     * @return array|bool
     *
     * array('字段名' => array(array('验证规则', ['错误提示'],[ '值域','值域',...])));
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
    public function validate($validates = [])
    {
        $res = false;
        $array = $this->request->all();

        if (!$validates) {
            $validates = $this->rules();
        }

        foreach ($validates as $inputKey => $v) {
            $err = "错误:{$inputKey}验证不通过";
            $inputValue = isset($array[$inputKey]) ? $array[$inputKey] : '';

            foreach ($v as $validate) {
                $method = array_shift($validate);

                if (isset($validate[0])) {
                    $err = array_shift($validate);
                }
                array_unshift($validate, $inputValue);

                if ($method instanceof Closure) {
                    $res = call_user_func_array($method, $validate);
                } else if (method_exists('\YrPHP\Validate', $method)) {
                    $res = call_user_func_array([Validate::class, $method], $validate);
                }

                if (!$res) {
                    $error[$inputKey][] = $err;
                }
            }
        }

        if (!empty($error)) {
            Session::flash('_old_input', $array);
            Session::flash('errors', $error);
            $this->response($error);
            return false;
        }

        return $array;
    }


    public function response(array $errors)
    {
        \response::errorBackTo($errors);

    }

    public function __call($method, $args)
    {
        if (method_exists($this->request, $method)) {
            return call_user_func_array([$this->request, $method], $args);
        }
        return false;
    }


}