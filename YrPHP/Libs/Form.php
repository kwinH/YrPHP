<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

namespace YrPHP;


class Form
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
        if (!$name) {
            return $value;
        }

        if (!$value && isset($this->data[$name])) {
            $value = $this->data[$name];
        }

        return old($name, $value);

    }

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
    public function input($type, $name, $options = [], $value = null)
    {
        $options['type'] = $type;

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }
        $options['id'] = $this->getIdAttribute($name, $options);

        if (!in_array($type, ['file', 'password', 'checkbox', 'radio'])) {
            $options['value'] = $this->getValueAttribute($name, $value);
        }

        return '<input' . $this->attributesToString($options) . '>';
    }

    /**
     * Create a text input field.
     *
     * @param $name
     * @param array $options
     * @param null $value
     * @return string
     */
    public function text($name, $options = [], $value = null)
    {
        return $this->input('text', $name, $options, $value);
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
        return $this->input('password', $name, $options);
    }

    /**
     * Create a hidden input field.
     *
     * @param  string $name
     * @param null $value
     * @param array $options
     *
     * @return string
     */
    public function hidden($name, $value = null, $options = [])
    {
        return $this->input('hidden', $name, $options, $value);
    }

    /**
     * Create an e-mail input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function email($name, $options = [], $value = null)
    {
        return $this->input('email', $name, $options, $value);
    }

    /**
     * Create a tel input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function tel($name, $options = [], $value = null)
    {
        return $this->input('tel', $name, $options, $value);
    }

    /**
     * Create a number input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function number($name, $options = [], $value = null)
    {
        return $this->input('number', $name, $options, $value);
    }

    /**
     * Create a date input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function date($name, $options = [], $value = null)
    {

        return $this->input('date', $name, $options, $value);
    }

    /**
     * Create a datetime input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function datetime($name, $options = [], $value = null)
    {
        return $this->input('datetime', $name, $options, $value);
    }

    /**
     * Create a datetime-local input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function datetimeLocal($name, $options = [], $value = null)
    {

        return $this->input('datetime-local', $name, $options, $value);
    }

    /**
     * Create a time input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function time($name, $options = [], $value = null)
    {
        return $this->input('time', $name, $options, $value);
    }

    /**
     * Create a url input field.
     *
     * @param  string $name
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function url($name, $options = [], $value = null)
    {
        return $this->input('url', $name, $options, $value);
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
     * @param array $options
     * @param null $value
     *
     * @return string
     */
    public function textarea($name, $options = [], $value = null)
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
     * @param  array $options
     * @param  string $selected
     *
     * @return string
     */
    public function select($name, $list = [], $options = [], $selected = null)
    {
        $selected = $this->getValueAttribute($name, $selected);

        $options['id'] = $this->getIdAttribute($name, $options);

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        $html = [];

        foreach ($list as $value => $display) {
            $optionAttr = '';
            if (is_array($display)) {
                $optionAttr = $display;
                $display = Arr::pop($optionAttr, 'value');
                $optionAttr = $this->attributesToString($optionAttr);
            }
            if ($value == $selected) {
                $html[] = '<option value="' . $value . '"  ' . $optionAttr . ' selected>' . $display . '</option>';
            } else {
                $html[] = '<option value="' . $value . '"  ' . $optionAttr . '>' . $display . '</option>';
            }
        }

        $options = $this->attributesToString($options);

        $list = implode('', $html);

        return "<select{$options}>{$list}</select>";
    }


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
    protected function checkable($type, $name, $value, $checked, $options)
    {
        $oldValue = old($name, isset($this->data[$name]) ? $this->data[$name] : null);
        if (!is_null($oldValue)) {
            if ($oldValue == $value) {
                $options['checked'] = 'checked';
            }
        } else if ($checked) {
            $options['checked'] = 'checked';
        }

        $options['value'] = $value;
        return $this->input($type, $name, $options, $value);
    }

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
    public function checkbox($name, $value = 1, $options = [], $checked = null)
    {
        return $this->checkable('checkbox', $name, $value, $checked, $options);
    }

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
    public function radio($name, $value = null, $options = [], $checked = null)
    {
        return $this->checkable('radio', $name, $value, $checked, $options);
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
     * @param  array $options
     * @param  string $value
     *
     * @return string
     */
    public function color($name, $options = [], $value = null)
    {
        return $this->input('color', $name, $options, $value);
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
        return $this->input('submit', null, $options, $value);
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
        return $this->input('button', null, $options, $value);
    }

}