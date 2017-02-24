<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

namespace YrPHP;


class From
{
    protected $data;

    /**
     * @param array $options
     * @param array $data
     * @return string
     */
    public function open($options = [], $data = [])
    {
        $this->data = (array)$data;
        $attributes['method'] = isset($options['method']) ? (strtoupper($options['method']) != 'GET' ? 'POST' : $options['method']) : 'POST';

        $attributes['action'] = isset($options['url']) ? $options['url'] : '';

        $attributes['accept-charset'] = 'UTF-8';

        unset($options['method'], $options['url'], $attributes['accept-charset']);

        $attributes = array_merge($attributes, $options);

        $attributes = $this->attributesToString($attributes);

        return '<form ' . $attributes . '>' . $this->token();
    }

    /**
     * @return string
     */
    public function close()
    {
        $this->data = [];
        return '</form>';
    }

    /**
     * Create a form label element.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function label($name, $value = null, $options = [])
    {
        $options = $this->attributesToString($options);

        return '<label for="' . $name . '"' . $options . '>' . $value . '</label>';
    }

    /**
     * Generate a hidden field with the current CSRF token.
     *
     * @return string
     */
    public function token()
    {
        return $this->hidden('_token', csrfToken());
    }

    /**
     *
     * @param $attributes
     * @return bool|string
     */
    protected function attributesToString($attributes)
    {
        if (empty($attributes)) {
            return '';
        }

        if (is_object($attributes)) {
            $attributes = (array)$attributes;
        }

        if (is_array($attributes)) {
            $atts = '';

            foreach ($attributes as $key => $val) {
                $atts .= ' ' . $key . '="' . $val . '"';
            }

            return $atts;
        }

        if (is_string($attributes)) {
            return ' ' . $attributes;
        }

        return FALSE;
    }


    /**
     * Get the ID attribute for a field name.
     *
     * @param  string $name
     * @param  array $attributes
     *
     * @return string
     */
    public function getIdAttribute($name, $attributes)
    {
        return isset($attributes['id']) ? $attributes['id'] : $name;
    }

    public function getValueAttribute($name, $value = null)
    {
        if (!$name) return $value;

        if (!$value && isset($this->data[$name]))
            $value = $this->data[$name];

        return old($name, $value);

    }

    /**
     * Create a form input field.
     *
     * @param  string $type
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function input($type, $name, $value = null, $options = [])
    {
        $options['type'] = $type;

        if (!isset($options['name']))
            $options['name'] = $name;

        $options['id'] = $this->getIdAttribute($name, $options);

        if (!in_array($type, ['file', 'password', 'checkbox', 'radio']))
            $options['value'] = $this->getValueAttribute($name, $value);


        return '<input' . $this->attributesToString($options) . '>';
    }

    /**
     * Create a text input field.
     *
     * @param $name
     * @param null $value
     * @param array $options
     * @return string
     */
    public function text($name, $value = null, $options = [])
    {
        return self::class;
        // return $this->input('text', $name, $value, $options);
    }


    /**
     * Create a password input field.
     *
     * @param  string $name
     * @param  array $options
     *
     * @return string
     */
    public function password($name, $options = [])
    {
        return $this->input('password', $name, '', $options);
    }

    /**
     * Create a hidden input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function hidden($name, $value = null, $options = [])
    {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * Create an e-mail input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function email($name, $value = null, $options = [])
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * Create a tel input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function tel($name, $value = null, $options = [])
    {
        return $this->input('tel', $name, $value, $options);
    }

    /**
     * Create a number input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function number($name, $value = null, $options = [])
    {
        return $this->input('number', $name, $value, $options);
    }

    /**
     * Create a date input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function date($name, $value = null, $options = [])
    {

        return $this->input('date', $name, $value, $options);
    }

    /**
     * Create a datetime input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function datetime($name, $value = null, $options = [])
    {
        return $this->input('datetime', $name, $value, $options);
    }

    /**
     * Create a datetime-local input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function datetimeLocal($name, $value = null, $options = [])
    {

        return $this->input('datetime-local', $name, $value, $options);
    }

    /**
     * Create a time input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function time($name, $value = null, $options = [])
    {
        return $this->input('time', $name, $value, $options);
    }

    /**
     * Create a url input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function url($name, $value = null, $options = [])
    {
        return $this->input('url', $name, $value, $options);
    }

    /**
     * Create a file input field.
     *
     * @param  string $name
     * @param  array $options
     *
     * @return string
     */
    public function file($name, $options = [])
    {
        return $this->input('file', $name, null, $options);
    }


    /**
     * Create a textarea input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function textarea($name, $value = null, $options = [])
    {
        if (!isset($options['name'])) {
            $options['name'] = $name;
        }


        if (isset($options['size'])) {
            $segments = explode(',', $options['size']);
            $options['cols'] = $segments[0];
            $options['rows'] = isset($segments[1]) ? $segments[1] : $segments[0];
            unset($options['size']);
        } else {
            $options['cols'] = isset($options['cols']) ? $options['cols'] : 50;
            $options['rows'] = isset($options['rows']) ? $options['cols'] : 10;
        }


        $options['id'] = $this->getIdAttribute($name, $options);

        $value = (string)$this->getValueAttribute($name, $value);


        $options = $this->attributesToString($options);

        return '<textarea' . $options . '>' . $value . '</textarea>';
    }


    /**
     * Create a select box field.
     *
     * @param  string $name
     * @param  array $list
     * @param  string $selected
     * @param  array $options
     *
     * @return string
     */
    public function select($name, $list = [], $selected = null, $options = [])
    {
        $selected = $this->getValueAttribute($name, $selected);

        $options['id'] = $this->getIdAttribute($name, $options);

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        $html = [];

        foreach ($list as $value => $display) {
            if ($value == $selected) {
                $html[] = '<option value=' . $value . ' selected>' . $display . '</option>';
            } else {
                $html[] = '<option value=' . $value . '>' . $display . '</option>';
            }
        }

        $options = $this->attributesToString($options);

        $list = implode('', $html);

        return "<select{$options}>{$list}</select>";
    }


    /**
     * Create a checkbox input field.
     *
     * @param  string $name
     * @param  mixed $value
     * @param  bool $checked
     * @param  array $options
     *
     * @return string
     */
    public function checkbox($name, $value = 1, $checked = null, $options = [])
    {
        if (old($name, isset($this->data[$name]) ? $this->data[$name] : '') == $value || $checked) {
            $options['checked'] = 'checked';
        }

        return $this->input('checkbox', $name, $value, $options);

    }

    /**
     * Create a radio button input field.
     *
     * @param  string $name
     * @param  mixed $value
     * @param  bool $checked
     * @param  array $options
     *
     * @return string
     */
    public function radio($name, $value = null, $checked = null, $options = [])
    {
        if (old($name, isset($this->data[$name]) ? $this->data[$name] : '') == $value || $checked) {
            $options['checked'] = 'checked';
        }

        return $this->input('radio', $name, $value, $options);

    }

    /**
     * Create a HTML reset input element.
     *
     * @param  string $value
     * @param  array $attributes
     *
     * @return string
     */
    public function reset($value, $attributes = [])
    {
        return $this->input('reset', null, $value, $attributes);
    }

    /**
     * Create a HTML image input element.
     *
     * @param  string $url
     * @param  string $name
     * @param  array $attributes
     *
     * @return string
     */
    public function image($url, $name = null, $attributes = [])
    {
        $attributes['src'] = $url;

        return $this->input('image', $name, null, $attributes);
    }

    /**
     * Create a color input field.
     *
     * @param  string $name
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function color($name, $value = null, $options = [])
    {
        return $this->input('color', $name, $value, $options);
    }

    /**
     * Create a submit button element.
     *
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function submit($value = null, $options = [])
    {
        return $this->input('submit', null, $value, $options);
    }

    /**
     * Create a button element.
     *
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function button($value = null, $options = [])
    {
        return $this->input('button', null, $value, $options);
    }

}