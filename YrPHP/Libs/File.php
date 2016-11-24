<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP;
/*
#文件目录操作类
*/

class File
{
    /**
     * 建立文件
     *
     * @param  string $aimUrl
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    static public function createFile($aimUrl, $overWrite = false)
    {
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            self::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        self::createDir($aimDir);
        touch($aimUrl);
        return true;
    }

    /**
     * 递归删除文件夹或文件
     *
     * @param  string $aimDir
     * @return  boolean
     */
    static public function rm($aimDir)
    {
        $aimDir = str_replace('', '/', $aimDir);
        if (is_file($aimDir)) return self::unlinkFile($aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';

        if (!is_dir($aimDir)) return false;

        $dirHandle = opendir($aimDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aimDir . $file)) {
                self::unlinkFile($aimDir . $file);
            } else {
                self::rm($aimDir . $file);
            }
        }
        closedir($dirHandle);
        return rmdir($aimDir);
    }

    /**
     * 删除文件
     *
     * @param  string $aimUrl
     * @return  boolean
     */
    static public function unlinkFile($aimUrl)
    {
        if (file_exists($aimUrl))
            return unlink($aimUrl);

        return false;
    }

    /**
     * 建立文件夹
     *
     * @param  string $aimUrl
     * @return  viod
     */
    static public function mkDir($aimUrl, $mode = 0777)
    {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        foreach ($arr as $str) {
            $aimDir .= $str . '/';
            if (!file_exists($aimDir)) {
                mkdir($aimDir, $mode);
            }
        }
        return true;
    }


    /**
     * 移动文件
     *
     * @param  string $fileUrl
     * @param  string $aimUrl
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    static public function moveFile($fileUrl, $aimUrl, $overWrite = false)
    {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite = false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite = true) {
            self::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        self::createDir($aimDir);
        rename($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 移动文件夹
     *
     * @param  string $oldDir
     * @param  string $aimDir
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    static public function mv($oldDir, $aimDir, $overWrite = false)
    {
        if (is_file($oldDir)) return self::moveFile($oldDir, $aimDir, $overWrite);
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            self::createDir($aimDir);
        }
        @$dirHandle = opendir($oldDir);
        if (!$dirHandle) {
            return false;
        }
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                self::moveFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                self::mv($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        closedir($dirHandle);
        return rmdir($oldDir);
    }


    /**
     * 复制文件
     *
     * @param  string $fileUrl
     * @param  string $aimUrl
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    static public function copyFile($fileUrl, $aimUrl, $overWrite = false)
    {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            self::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        self::mkDir($aimDir);
        copy($fileUrl, $aimUrl);
        return true;
    }


    /**
     * 复制文件或则文件夹
     *
     * @param  string $oldDir
     * @param  string $aimDir
     * @param  boolean $overWrite 该参数控制是否覆盖原文件
     * @return  boolean
     */
    static public function cp($oldDir, $aimDir, $overWrite = false)
    {
        $aimDir = str_replace('', '/', $aimDir);
        if (is_file($oldDir)) return self::copyFile($oldDir, $aimDir, $overWrite);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            self::mkDir($aimDir);
        }
        $dirHandle = opendir($oldDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                self::copyFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                self::cp($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        closedir($dirHandle);
        return true;
    }


    /**
     * 修改文件名
     *$path 需要修改的文件路径
     *$name 修改后的文件路径及文件名
     * @return    bool
     */
    static public function rename($path, $name)
    {
        if (file_exists($path)) {
            if (rename($path, $name)) {
                return true;
            }
        }
        return false;

    }

    /**
     * 将字符串写入文件
     *
     * @param  string $filename 文件名
     * @param  boolean $str 待写入的字符数据
     */
    static public function vi($filename, $str)
    {
        $path = pathinfo($filename, PATHINFO_DIRNAME);

        if (!is_dir($path)) self::mkDir($path);

        if (function_exists('file_put_contents')) {
            file_put_contents($filename, $str);
        } else {
            $fp = fopen($filename, "w+");
            fwrite($fp, $str);
            fclose($fp);
        }
    }

    /**
     * 将整个文件内容读出到一个字符串中
     *
     * @param  string $filename 文件名
     * @return string
     */

    static public function readsFile($filename)
    {
        if (function_exists('file_get_contents')) {
            return file_get_contents($filename);
        } else {
            $fp = fopen($filename, "rb");
            $str = fread($fp, filesize($filename));
            fclose($fp);
            return $str;
        }
    }

    /**
     * 将文件内容读出到一个数组中
     *
     * @param  string $filename 文件名
     * @return array
     */
    static public function readFile2array($filename)
    {
        $file = file($filename);
        $arr = array();
        foreach ($file as $value) {
            $arr [] = trim($value);
        }
        return $arr;
    }

    /**
     * 转换目录下面的所有文件编码格式
     *
     * @param    string $in_charset 原字符集
     * @param    string $out_charset 目标字符集
     * @param    string $dir 目录地址
     * @param    string $fileexts 转换的文件格式
     * @return    string    如果原字符集和目标字符集相同则返回false，否则为true
     */
    static public function dirIconv($in_charset, $out_charset, $dir, $fileexts = 'php|html|htm|shtml|shtm|js|txt|xml')
    {
        if ($in_charset == $out_charset)
            return false;
        $list = self::dirList($dir);
        foreach ($list as $v) {
            if (preg_match("/\.($fileexts)/i", $v) && is_file($v)) {
                file_put_contents($v, iconv($in_charset, $out_charset, file_get_contents($v)));
            }
        }
        return true;
    }

    /**
     * 根据关键词列出目录下所有文件
     *
     * @param    string $path 路径
     * @param    string $key 关键词
     * @param    array $list 增加的文件列表
     * @return    array    所有满足条件的文件
     */
    static public function dirList($path, $key = '', $list = array())
    {
        $path = self::dirPath($path);
        $files = glob($path . '\*');
        //$dir = substr($path,strrpos($path,'\\')+1);
        foreach ($files as $v) {
            if (empty($key)) {

                $list [$path][] = $v;

            } else {

                $lastFileName = substr($v, strlen($path) + 1);

                if (preg_match("/.*$key.*/i", $lastFileName)) {

                    $list [$path][] = $v;
                }
            }

            if (is_dir($v)) {
                $list = self::dirList($v, $key, $list);
            }


        }
        return $list;
    }


    /**
     * 根据关键词列出目录下所有文件
     *
     * @param    string $path 路径
     * @param    string $key 关键词
     * @param    array $list 增加的文件列表
     * @return    array    所有满足条件的文件
     */
    static public function search($path, $key = '', $list = array())
    {
        $path = self::dirPath($path);

        $files = glob($path . '*');

        //$dir = substr($path,strrpos($path,'\\')+1);
        foreach ($files as $v) {
            if (empty($key)) {

                $list [] = $v;

            } else {

                $lastFileName = substr($v, strlen($path) + 1);

                if (preg_match("/.*$key.*/i", $lastFileName)) {

                    $list [] = $v;
                }
            }

            if (is_dir($v)) {
                $list = self::search($v, $key, $list);
            }


        }
        return $list;
    }

    /**
     * 转化 \ 为 /
     *
     * @param    string $path 路径
     * @return    string    路径
     */
    static public function dirPath($path)
    {
        return realpath($path) . '/';
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/')
            $path = $path . '/';
        return $path;
    }

    /**
     * 获取文件名后缀
     *
     * @param    string $filename
     * @return    string
     */
    static public function fileExt($filename)
    {
        if (file_exists($filename))
            return pathinfo($filename, PATHINFO_EXTENSION);

        return false;
    }

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
    static public function getFileInfo($filename)
    {
        if (file_exists($filename))
            return array_merge(pathinfo($filename), stat($filename));

        return false;
    }

    /**
     * 设置目录下面的所有文件的访问和修改时间
     *
     * @param    string $path 路径
     * @param    int $mtime 修改时间
     * @param    int $atime 访问时间
     * @return    array    不是目录时返回false，否则返回 true
     */
    static public function dirTouch($path, $mtime = TIME, $atime = TIME)
    {
        if (!is_dir($path))
            return false;

        $path = self::dirPath($path);
        if (!is_dir($path))
            touch($path, $mtime, $atime);
        $files = glob($path . '*');
        foreach ($files as $v) {
            is_dir($v) ? self::dirTouch($v, $mtime, $atime) : touch($v, $mtime, $atime);
        }
        return true;
    }

    /**
     * 目录列表
     *
     * @param    string $dir 路径
     * @param    int $parentid 父id
     * @param    array $dirs 传入的目录
     * @return    array    返回目录及子目录列表
     */
    static public function dirTree($dir, $parentid = 0, $dirs = array())
    {
        global $id;
        if ($parentid == 0)
            $id = 0;
        $list = glob($dir . '*');
        foreach ($list as $v) {
            if (is_dir($v)) {
                $id++;
                $dirs [$id] = array('id' => $id, 'parentid' => $parentid, 'name' => basename($v), 'dir' => $v . '/');
                $dirs = self::dirTree($v . '/', $id, $dirs);
            }
        }
        return $dirs;
    }

    /**
     * 目录列表
     *
     * @param    string $dir 路径
     * @return    array    返回目录列表
     */
    static public function dirNodeTree($dir)
    {
        $d = dir($dir);
        $dirs = array();
        while (false !== ($entry = $d->read())) {
            if ($entry != '.' and $entry != '..' and is_dir($dir . '/' . $entry)) {
                $dirs[] = $entry;
            }
        }
        return $dirs;
    }

    /**
     * 获取目录大小
     *
     * @param    string $dirname 目录
     * @return    string      比特B
     */
    static public function getDirSize($dirname)
    {
        if (!file_exists($dirname) or !is_dir($dirname))
            return false;
        if (!$handle = opendir($dirname))
            return false;
        $size = 0;
        while (false !== ($file = readdir($handle))) {
            if ($file == "." or $file == "..")
                continue;
            $file = $dirname . "/" . $file;
            if (is_dir($file)) {
                $size += self::getDirSize($file);
            } else {
                $size += filesize($file);
            }

        }
        closedir($handle);
        return $size;
    }

    /**
     * 将字节转换成Kb或者Mb...
     * @param $size为字节大小
     */
    static public function bitSize($size)
    {
        if (!preg_match("/^[0-9]+$/", $size))
            return 0;
        $type = array("B", "KB", "MB", "GB", "TB", "PB");

        $j = 0;
        while ($size >= 1024) {
            if ($j >= 5)
                return $size . $type [$j];
            $size = $size / 1024;
            $j++;
        }
        return $size . $type [$j];
    }

    static public function remote_file_exists($url_file)
    {
        $url_file = trim($url_file);
        if (empty($url_file)) return false;
        $url_arr = parse_url($url_file);
        if (!is_array($url_arr) || empty($url_arr)) return false;
        $host = $url_arr['host'];
        $path = $url_arr['path'] . "?" . $url_arr['query'];
        $port = isset($url_arr['port']) ? $url_arr['port'] : "80";
        $fp = fsockopen($host, $port, $err_no, $err_str, 30);
        if (!$fp) return false;
        $request_str = "GET " . $path . " HTTP/1.1\r\n";
        $request_str .= "Host:" . $host . "\r\n";
        $request_str .= "Connection:Close\r\n\r\n";
        fwrite($fp, $request_str);
        //fread replace fgets
        $first_header = fread($fp, 128);
        fclose($fp);
        if (trim($first_header) == "") return false;
        //check $url_file "Content-Location"
        if (!preg_match("/200/", $first_header) || preg_match("/Location:/", $first_header)) return false;
        return true;
    }


}
