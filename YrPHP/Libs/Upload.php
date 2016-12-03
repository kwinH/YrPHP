<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


class Upload
{
    static $mimes = null;
    public $savePath = '/';//上传目录
    public $fileName = '';//自定义上传文件后的名称，不含文件后缀 优先级大于isRandName
    public $allowedTypes = array(); //允许上传文件的后缀列表
    public $maxSize = 0; //最大的上传文件 KB
    public $isRandName = true;//设置是否随机重命名文件， false不随机
    public $overwrite = false; //是否覆盖。true则覆盖，false则重命名；


    public $fileExt = null; //文件后缀
    public $isImage = false;//是不是图片
    public $imgWidth = 0;//图片宽度
    public $imgHeight = 0;//图片高度
    public $fileInfo = array();
    public $result = array();
    public $error = array();
    public $fileSize = 0;

    public function __construct($config = array())
    {
        if (!empty($config)) $this->init($config);
    }

    public function init($config = array())
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }


    /**
     * 上传文件
     * @param 文件信息数组 $field ，上传文件的表单名称  默认是 $_FILES数组
     */
    public function upload($field = '')
    {
        if ('' === $field) {
            $files = $_FILES;
        } else {
            if (!isset($_FILES[$field])) {
                $this->fileInfo[$field]['errorCode'] = -7;
                return false;
            } else {
                $files[$field] = $_FILES[$field];
            }
        }
        if (empty($files)) {
            $this->fileInfo[$field]['errorCode'] = 4;
            return false;
        }


        foreach ($files as $k => $v) {
            $v['inputName'] = $k;
            if (is_array($v['name'])) {
                $this->uploadMulti($v);
            } else {
                $this->uploadOne($v);
            }
        }

        return !in_array(false, $this->result);

    }

    public function  uploadMulti($files = array())
    {
        $inputName = $files['inputName'];
        unset($files['inputName']);
        foreach ($files['name'] as $k => $v) {
            $uploadFileInfo = array(
                'inputName' => $inputName . $k,
                'name'      => $v,
                'type'      => $files['type'][$k],
                'tmp_name'  => $files['tmp_name'][$k],
                'error'     => $files['error'][$k],
                'size'      => $files['size'][$k],
            );

            $this->uploadOne($uploadFileInfo);
        }

    }

    public function uploadOne($file = array())
    {

        if ($file['error'] > 0) {
            $this->fileInfo[$file['inputName']]['errorCode'] = $file['error'];
            $this->result[] = false;
            return false;
        }

        $this->fileSize = sprintf('%.2f', $file['size'] / 1024);
        if (!$this->checkSize($this->fileSize)) {
            $this->fileInfo[$file['inputName']]['errorCode'] = -2;
            $this->result[] = false;
            return false;
        }
        $this->fileExt = $this->getExtension($file['name']);
        if (!$this->checkFileType($file)) {
            $this->fileInfo[$file['inputName']]['errorCode'] = -1;
            $this->result[] = false;
            return false;
        }

        if (empty($this->fileName)) {
            if ($this->isRandName) {
                $fileName = date('Ymdhis') . mt_rand(100, 999);
            } else {
                $file['name'] = str_replace('.' . $this->fileExt, '', $file['name']);
                $fileName = str_replace('.', '_', $file['name']);
            }
        }
        //目标不存在 则创建
        if (!is_dir($this->savePath)) {
            if (!mkdir($this->savePath, 0777, true)) {
                $this->fileInfo[$file['inputName']]['errorCode'] = -4;
                $this->result[] = false;
                return false;
            }
        }


        if (file_exists($this->savePath . $fileName . '.' . $this->fileExt) && !$this->overwrite) {

            $fileName .= mt_rand(100, 999);
        }


        $fileName .= '.' . $this->fileExt;


        $path = rtrim($this->savePath, '/') . '/';
        $path .= $fileName;


        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->fileInfo[$file['inputName']]['errorCode'] = -6;
            $this->result[] = false;
            return false;
        }

        if (move_uploaded_file($file['tmp_name'], $path)) {
            $this->fileInfo[$file['inputName']] = array('fileName'  => $fileName,
                                                        'fileType'  => $file['type'],
                                                        'filePath'  => $path,
                                                        'origName'  => $file['name'],
                                                        'fileExt'   => $this->fileExt,
                                                        'fileSize'  => $this->fileSize,
                                                        'isImage'   => $this->isImage,
                                                        'imgWidth'  => $this->imgWidth,
                                                        'imgHeight' => $this->imgHeight,

            );

            return true;
        } else {
            $this->fileInfo[$file['inputName']]['errorCode'] = -3;
            $this->result[] = false;
            return false;
        }


    }

    /**
     *检查文件大小是否合法
     * @param integer $fileSize 数据
     */
    private function checkSize($fileSize=0)
    {
        return ($fileSize < $this->maxSize) || (0 === $this->maxSize);
    }

    /**
     * 返回文件拓展后缀
     * @param $filename
     * @return string
     */
    public function getExtension($filename)
    {
        $x = explode('.', $filename);

        if (count($x) === 1) {
            return '';
        }

        return strtolower(end($x));

    }

    /** 检查上传的文件MIME类型是否合法
     * @param array $file
     * @return bool
     */
    private function checkFileType($file = array())
    {

        if (($imgSize = getimagesize($file['tmp_name'])) !== false) {
            $this->isImage = true;
            $this->imgWidth = $imgSize[0];
            $this->imgHeight = $imgSize[1];
        }

        if (empty($this->allowedTypes)) return true;

        if (!in_array($this->fileExt, $this->allowedTypes, true)) {
            return false;
        }


        $imgExt = array('gif', 'jpg', 'mpng', 'swf', 'swc', 'psd', 'tiff', 'bmp', 'iff', 'jp2', 'jpx', 'jb2', 'jpc', 'xbm', 'wbmp');
        if (array_intersect($this->allowedTypes, $imgExt) && !$this->isImage) {
            return false;
        }


        if ($mimes = $this->checkMimes($this->fileExt)) {
            return is_array($mimes)
                ? in_array($file['type'], $mimes, true)
                : ($mimes === $file['type']);
        }

        return true;
    }

    public function getFileInfo($inputName = null)
    {
        if(is_null($inputName)){
        return $this->fileInfo;
        }else{
        if(isset($this->fileInfo[$inputName])){
         return    $this->fileInfo[$inputName];
        }else{
               return false;
            }
        }

    }


    /**
     * 根据错误代码获得上传出错信息
     * @param null $errorNum
     * @return string
     */
    public function getError($errorCode = null)
    {
        switch ($errorCode) {
            case 4:
                $str = "没有文件被上传";
                break;
            case 3:
                $str = "文件只有部分被上传";
                break;
            case 2:
                $str = "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                break;
            case 1:
                $str = "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                break;
            case -1:
                $str = "不允许该类型上传";
                break;
            case -2:
                $str = "文件过大,上传的文件不能超过{$this->maxSize}KB";
                break;
            case -3:
                $str = "上传失败";
                break;
            case -4:
                $str = "建立存放上传文件目录失败，请重新指定上传目录";
                break;
            case -5:
                $str = "必须指定上传文件的路径";
                break;
            case -6:
                $str = "非法上传文件！";
                break;
            case -7:
                $str = "文件表单不存在";
                break;
            default:
                $str = "未知错误";
        }
        return $str;
    }


    /**
     * | MIME TYPES
    | -------------------------------------------------------------------
    | This file contains an array of mime types.  It is used by the
    | Upload class to help identify allowed file types.
     *
     * @param string $ext
     * @return bool
     */
    private function checkMimes($ext = '')
    {

        if (empty($ext)) return false;
        $mimes = array(
            'hqx'   => array('App/mac-binhex40', 'App/mac-binhex', 'App/x-binhex40', 'App/x-mac-binhex40'),
            'cpt'   => 'App/mac-compactpro',
            'csv'   => array('text/x-comma-separated-values', 'text/comma-separated-values', 'App/octet-stream', 'App/vnd.ms-excel', 'App/x-csv', 'text/x-csv', 'text/csv', 'App/csv', 'App/excel', 'App/vnd.msexcel', 'text/plain'),
            'bin'   => array('App/macbinary', 'App/mac-binary', 'App/octet-stream', 'App/x-binary', 'App/x-macbinary'),
            'dms'   => 'App/octet-stream',
            'lha'   => 'App/octet-stream',
            'lzh'   => 'App/octet-stream',
            'exe'   => array('App/octet-stream', 'App/x-msdownload'),
            'class' => 'App/octet-stream',
            'psd'   => array('App/x-photoshop', 'image/vnd.adobe.photoshop'),
            'so'    => 'App/octet-stream',
            'sea'   => 'App/octet-stream',
            'dll'   => 'App/octet-stream',
            'oda'   => 'App/oda',
            'pdf'   => array('App/pdf', 'App/force-download', 'App/x-download', 'binary/octet-stream'),
            'ai'    => array('App/pdf', 'App/postscript'),
            'eps'   => 'App/postscript',
            'ps'    => 'App/postscript',
            'smi'   => 'App/smil',
            'smil'  => 'App/smil',
            'mif'   => 'App/vnd.mif',
            'xls'   => array('App/vnd.ms-excel', 'App/msexcel', 'App/x-msexcel', 'App/x-ms-excel', 'App/x-excel', 'App/x-dos_ms_excel', 'App/xls', 'App/x-xls', 'App/excel', 'App/download', 'App/vnd.ms-office', 'App/msword'),
            'ppt'   => array('App/powerpoint', 'App/vnd.ms-powerpoint', 'App/vnd.ms-office', 'App/msword'),
            'pptx'  => array('App/vnd.openxmlformats-officedocument.presentationml.presentation', 'App/x-zip', 'App/zip'),
            'wbxml' => 'App/wbxml',
            'wmlc'  => 'App/wmlc',
            'dcr'   => 'App/x-director',
            'dir'   => 'App/x-director',
            'dxr'   => 'App/x-director',
            'dvi'   => 'App/x-dvi',
            'gtar'  => 'App/x-gtar',
            'gz'    => 'App/x-gzip',
            'gzip'  => 'App/x-gzip',
            'php'   => array('App/x-httpd-php', 'App/php', 'App/x-php', 'text/php', 'text/x-php', 'App/x-httpd-php-source'),
            'php4'  => 'App/x-httpd-php',
            'php3'  => 'App/x-httpd-php',
            'phtml' => 'App/x-httpd-php',
            'phps'  => 'App/x-httpd-php-source',
            'js'    => array('App/x-javascript', 'text/plain'),
            'swf'   => 'App/x-shockwave-flash',
            'sit'   => 'App/x-stuffit',
            'tar'   => 'App/x-tar',
            'tgz'   => array('App/x-tar', 'App/x-gzip-compressed'),
            'z'     => 'App/x-compress',
            'xhtml' => 'App/xhtml+xml',
            'xht'   => 'App/xhtml+xml',
            'zip'   => array('App/x-zip', 'App/zip', 'App/x-zip-compressed', 'App/s-compressed', 'multipart/x-zip'),
            'rar'   => array('App/x-rar', 'App/rar', 'App/x-rar-compressed'),
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mpga'  => 'audio/mpeg',
            'mp2'   => 'audio/mpeg',
            'mp3'   => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif'   => array('audio/x-aiff', 'audio/aiff'),
            'aiff'  => array('audio/x-aiff', 'audio/aiff'),
            'aifc'  => 'audio/x-aiff',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'ra'    => 'audio/x-realaudio',
            'rv'    => 'video/vnd.rn-realvideo',
            'wav'   => array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp'   => array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'App/bmp', 'App/x-bmp', 'App/x-win-bitmap'),
            'gif'   => 'image/gif',
            'jpeg'  => array('image/jpeg', 'image/pjpeg'),
            'jpg'   => array('image/jpeg', 'image/pjpeg'),
            'jpe'   => array('image/jpeg', 'image/pjpeg'),
            'png'   => array('image/png', 'image/x-png'),
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'css'   => array('text/css', 'text/plain'),
            'html'  => array('text/html', 'text/plain'),
            'htm'   => array('text/html', 'text/plain'),
            'shtml' => array('text/html', 'text/plain'),
            'txt'   => 'text/plain',
            'text'  => 'text/plain',
            'log'   => array('text/plain', 'text/x-log'),
            'rtx'   => 'text/richtext',
            'rtf'   => 'text/rtf',
            'xml'   => array('App/xml', 'text/xml', 'text/plain'),
            'xsl'   => array('App/xml', 'text/xsl', 'text/xml'),
            'mpeg'  => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mpe'   => 'video/mpeg',
            'qt'    => 'video/quicktime',
            'mov'   => 'video/quicktime',
            'avi'   => array('video/x-msvideo', 'video/msvideo', 'video/avi', 'App/x-troff-msvideo'),
            'movie' => 'video/x-sgi-movie',
            'doc'   => array('App/msword', 'App/vnd.ms-office'),
            'docx'  => array('App/vnd.openxmlformats-officedocument.wordprocessingml.document', 'App/zip', 'App/msword', 'App/x-zip'),
            'dot'   => array('App/msword', 'App/vnd.ms-office'),
            'dotx'  => array('App/vnd.openxmlformats-officedocument.wordprocessingml.document', 'App/zip', 'App/msword'),
            'xlsx'  => array('App/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'App/zip', 'App/vnd.ms-excel', 'App/msword', 'App/x-zip'),
            'word'  => array('App/msword', 'App/octet-stream'),
            'xl'    => 'App/excel',
            'eml'   => 'message/rfc822',
            'json'  => array('App/json', 'text/json'),
            'pem'   => array('App/x-x509-user-cert', 'App/x-pem-file', 'App/octet-stream'),
            'p10'   => array('App/x-pkcs10', 'App/pkcs10'),
            'p12'   => 'App/x-pkcs12',
            'p7a'   => 'App/x-pkcs7-signature',
            'p7c'   => array('App/pkcs7-mime', 'App/x-pkcs7-mime'),
            'p7m'   => array('App/pkcs7-mime', 'App/x-pkcs7-mime'),
            'p7r'   => 'App/x-pkcs7-certreqresp',
            'p7s'   => 'App/pkcs7-signature',
            'crt'   => array('App/x-x509-ca-cert', 'App/x-x509-user-cert', 'App/pkix-cert'),
            'crl'   => array('App/pkix-crl', 'App/pkcs-crl'),
            'der'   => 'App/x-x509-ca-cert',
            'kdb'   => 'App/octet-stream',
            'pgp'   => 'App/pgp',
            'gpg'   => 'App/gpg-keys',
            'sst'   => 'App/octet-stream',
            'csr'   => 'App/octet-stream',
            'rsa'   => 'App/x-pkcs7',
            'cer'   => array('App/pkix-cert', 'App/x-x509-ca-cert'),
            '3g2'   => 'video/3gpp2',
            '3gp'   => 'video/3gp',
            'mp4'   => 'video/mp4',
            'm4a'   => 'audio/x-m4a',
            'f4v'   => 'video/mp4',
            'webm'  => 'video/webm',
            'aac'   => 'audio/x-acc',
            'm4u'   => 'App/vnd.mpegurl',
            'm3u'   => 'text/plain',
            'xspf'  => 'App/xspf+xml',
            'vlc'   => 'App/videolan',
            'wmv'   => array('video/x-ms-wmv', 'video/x-ms-asf'),
            'au'    => 'audio/x-au',
            'ac3'   => 'audio/ac3',
            'flac'  => 'audio/x-flac',
            'ogg'   => 'audio/ogg',
            'kmz'   => array('App/vnd.google-earth.kmz', 'App/zip', 'App/x-zip'),
            'kml'   => array('App/vnd.google-earth.kml+xml', 'App/xml', 'text/xml'),
            'ics'   => 'text/calendar',
            'ical'  => 'text/calendar',
            'zsh'   => 'text/x-scriptzsh',
            '7zip'  => array('App/x-compressed', 'App/x-zip-compressed', 'App/zip', 'multipart/x-zip'),
            'cdr'   => array('App/cdr', 'App/coreldraw', 'App/x-cdr', 'App/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-App/zz-winassoc-cdr'),
            'wma'   => array('audio/x-ms-wma', 'video/x-ms-asf'),
            'jar'   => array('App/java-archive', 'App/x-java-App', 'App/x-jar', 'App/x-compressed'),
            'svg'   => array('image/svg+xml', 'App/xml', 'text/xml'),
            'vcf'   => 'text/x-vcard'
        );

        if(isset($mimes[$ext])) return $mimes[$ext];

        return false;
    }

}