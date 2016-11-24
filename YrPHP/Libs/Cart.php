<?php

/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/kwinH/YrPHP
 *
 */
namespace YrPHP;
class Cart
{

    protected $singleCartContents = array();
    protected $multiCartContents = array();
    protected $error = '';
    public $saveMode = 'session';
    public $mallMode = false;//商城模式 true多商家 false单商家
    public $key = 'cartContents';

    public function __construct($params = array())
    {
        if (isset($params['mallMode'])) {
            $this->mallMode = $params['mallMode'];
        }

        if (isset($params['saveMode'])) {
            $this->saveMode = $params['saveMode'];
        }

        if (isset($params['key'])) {
            $this->key = $params['key'];
        }

        if ($this->saveMode == 'session' && !session_id()) session_start();

        if (isset($_SESSION[$this->key]) || isset($_COOKIE[$this->key])) {
            $this->contents();
        }

    }


    /**
     * 返回一个包含了购物车中所有信息的数组
     * @param null $mallMode 商城模式 true多商家(二维数组) false单商家（一维数组）默认为配置中的模式,当为单商家时，不管设置什么都返回一维数组
     * @param null $seller 返回指定商家下的所以产品，默认为null，返回所以商家，单商家下无效
     * @return array
     */
    function getContents($mallMode = null, $seller = null)
    {
        $mallMode = is_null($mallMode) ? $this->mallMode : ($this->mallMode ? $mallMode : false);
        if ($mallMode) {
            if ($seller) return array($seller => $this->multiCartContents[$seller]);

            return $this->multiCartContents;

        } else {
            return $this->singleCartContents;
        }
    }


    /**
     * 返回一个包含了购物车中所有信息的数组
     * @return array
     */
    public function contents()
    {

        $data = '';
        if ($this->saveMode == 'session' && isset($_SESSION[$this->key])) {


            $data = $_SESSION[$this->key];
        } else if (isset($_COOKIE[$this->key])) {
            $data = json_decode($_COOKIE[$this->key], true);
        }
        $data = empty($data) ? array() : $data;


        if ($this->mallMode) {
            foreach ($data as $v) {

                foreach ($v as $kk => $vv) {
                    $this->singleCartContents[$kk] = $vv;
                }
            }
            $this->multiCartContents = $data;

        } else {
            $this->singleCartContents = $data;

        }
        return $data;
    }

    /**
     * 添加单条或多条购物车项目
     * @param array $items
     * @param bool $accumulation 是否累加
     * @return bool|string
     */
    public function insert($items = array(), $accumulation = true)
    {

        if (isset($items['id'])) {
            $rowId[] = $this->_insert($items, $accumulation);
        } elseif (is_array(reset($items))) {

            foreach ($items as $v) {
                $rowId[] = $this->_insert($v);
            }

        }

        if (!isset($rowId)) return false;
        if (in_array(false, $rowId)) {
            return false;
        }


        if ($this->mallMode) {
            $this->saveCart($this->multiCartContents);
        } else {
            $this->saveCart($this->singleCartContents);
        }

        if (!isset($rowId[1])) {
            return $rowId[0];
        } else {
            return $rowId;
        }
    }

    /**
     * 添加单条购物车项目
     * @param array $items
     * @param bool $accumulation 是否累加
     * @return bool|string
     */
    protected function _insert($item = array(), $accumulation = false)
    {

        if (!is_array($item) OR count($item) === 0) {
            $this->error = '插入的数据必须是数组格式';
            return false;
        }


        if (!isset($item['id'], $item['qty'], $item['price'], $item['name'])) {
            $this->error = '数组必须包含 id(产品ID),qty(商品数量),price(商品价格),name(商品名称)';
            return false;
        }

        $item['qty'] = (int)$item['qty'];
        $item['price'] = (float)$item['price'];

        if (isset($item['options']) && count($item['options']) > 0) {
            $rowId = md5($item['id'] . serialize($item['options']));
        } else {
            $rowId = md5($item['id']);
        }
        $item['rowId'] = $rowId;

        $item['subtotal'] = $item['qty'] * $item['price'];


        if (isset($this->singleCartContents[$rowId])) {
            $this->singleCartContents[$rowId] = array_merge($this->singleCartContents[$rowId], $item);

            if ($accumulation) {
                $this->singleCartContents[$rowId]['qty'] += $item['qty'];
                $this->singleCartContents[$rowId]['subtotal'] += $item['subtotal'];
            } else {
                $this->singleCartContents[$rowId]['qty'] = $item['qty'];
                $this->singleCartContents[$rowId]['subtotal'] = $item['subtotal'];
            }

        } else {
            $this->singleCartContents[$rowId] = $item;
        }


        if ($this->mallMode) {
            if (isset($item['seller'])) {
                $this->multiCartContents[$item['seller']][$rowId] = $this->singleCartContents[$rowId];
            } else {
                $this->error = 'seller(卖家标识ID) 不能为空';
                return false;
            }
        }

        return $rowId;
    }

    /**
     * 根据配置保存数据
     * @param array $cartContent
     * @return array
     */
    public function saveCart($cartContent = null)
    {

        if ($this->saveMode == 'session') {

            $_SESSION[$this->key] = $cartContent;
        } else {

            setcookie($this->key, json_encode($cartContent), time() + 36000, '/');

        }

    }

    /**
     * 更新购物车中的项目 必须包含 rowId
     * @param $item 修改多个可为二维数组
     * @return bool
     */
    public function update($items = array())
    {

        $status = true;
        if (isset($items['rowId'])) {
            $status = $this->_update($items);
        } elseif (is_array(reset($items))) {

            foreach ($items as $v) {
                if ($this->_update($v) === false) {
                    $status = false;
                }
            }

        }

        if ($status === false) return false;

        if ($this->mallMode) {
            $this->saveCart($this->multiCartContents);
        } else {
            $this->saveCart($this->singleCartContents);
        }

        return true;

    }


    /**
     * 修改单条项目
     * @param $item
     * @return bool
     */
    protected function _update($item)
    {
        if (!isset($this->singleCartContents[$item['rowId']])) {
            $this->error = '数组必须包含 rowId(唯一标识符)';
            return false;
        }


        if (isset($item['qty'])) {
            $item['qty'] = intval($item['qty']);
            if ($item['qty'] <= 0) {
                $this->remove($item['rowId']);
                return true;
            }
        }

        if (isset($item['price'])) {
            $item['price'] = (float)$item['price'];
        }


        $keys = array_intersect(array_keys($this->singleCartContents[$item['rowId']]), array_keys($item));


        foreach (array_diff($keys, array('id', 'name')) as $key) {
            $this->singleCartContents[$item['rowId']][$key] = $item[$key];
        }


        $this->singleCartContents[$item['rowId']]['subtotal'] =
            $this->singleCartContents[$item['rowId']]['qty'] * $this->singleCartContents[$item['rowId']]['price'];


        if ($this->mallMode) {
            $seller = $this->singleCartContents[$item['rowId']]['seller'];
            $this->multiCartContents[$seller][$item['rowId']] = $this->singleCartContents[$item['rowId']];
        }

        return true;
    }

    /**
     * 删除一条购物车中的项目  必须包含 rowId
     * @param null|array $rowId
     * @return bool
     */
    public function remove($rowId = null)
    {

        if (!is_array($rowId)) $rowId = array($rowId);

        foreach ($rowId as $v) {
            if (!isset($this->singleCartContents[$v])) {
                continue;
            }
            if ($this->mallMode) {
                $seller = $this->singleCartContents[$v]['seller'];

                unset($this->multiCartContents[$seller][$v]);

                if (count($this->multiCartContents[$seller]) == 0) {
                    unset($this->multiCartContents[$seller]);
                }

                $this->saveCart($this->multiCartContents);

                unset($this->singleCartContents[$v]);

            } else {

                unset($this->singleCartContents[$v]);
                $this->saveCart($this->singleCartContents);
            }
        }

        return $this->totalItems();
    }

    /**
     * 获得一条购物车的项目
     * @param null $rowId
     * @return bool|array
     */
    public function getItem($rowId = null)
    {
        if (!$rowId) return false;

        if (!isset($this->singleCartContents[$rowId])) {
            return false;
        }

        return $this->singleCartContents[$rowId];
    }

    /**
     * 显示购物车中总共的项目数量
     * @param null $seller 商家标识符 单商家模式下无效
     * @return int
     */
    public function totalItems($seller = null)
    {
        if (empty($seller) || $this->saveMode = false) {
            return count($this->singleCartContents);
        } else {
            return count($this->multiCartContents[$seller]);
        }
    }

    /**
     * 显示购物车中总共的商品数量
     * @param null $seller 商家标识符 单商家模式下无效
     * @return int
     */
    public function totalQty($seller = null)
    {

        $qty = 0;

        if (empty($seller) || $this->saveMode = false) {
            if (empty($this->singleCartContents)) return $qty;
            foreach ($this->singleCartContents as $v) {
                $qty += $v['qty'];
            }
        } else {
            if (empty($this->multiCartContents[$seller])) return $qty;
            foreach ($this->multiCartContents[$seller] as $v) {
                $qty += $v['qty'];
            }
        }

        return $qty;
    }


    /**
     * 显示购物车中的总计金额  商家标识符 单商家模式下无效
     * @return int
     */
    public function total($seller = null)
    {
        $total = 0;
        if ($this->mallMode && !is_null($seller)) {
            if (isset($this->multiCartContents[$seller])) {
                foreach ($this->multiCartContents[$seller] as $v) {
                    $total += $v['subtotal'];
                }
            }
        } else {
            foreach ($this->singleCartContents as $v) {
                $total += $v['subtotal'];
            }
        }
        return $total;
    }

    /**
     * 根据rowId 查找商家
     * @param $key
     * @return bool|int|string 当为单商家模式时直接返回false,当找不到时也返回false，否则返回商家标识符
     */
    public function searchSeller($rowId)
    {
        if (!$this->saveMode) return false;

        foreach ($this->cartContents as $k => $v) {
            if (isset($v[$rowId])) {
                return $k;
            }
        }
        return false;
    }


    /**
     * 销毁购物车
     */
    public function destroy()
    {
        if ($this->saveMode == 'session') {
            unset($_SESSION[$this->key]);
        } else {
            setcookie($this->key, 'die', time() - 3600, '/');
        }


    }

    public function getError()
    {
        return $this->error;
    }
}