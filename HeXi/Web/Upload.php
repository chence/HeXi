<?php

/**
 * 上传处理类
 * @author FuXiaoHei
 */
class Upload {

    /**
     * 上传文件信息
     * @var array
     */
    public $fileData;

    /**
     * 保存地址
     * @var string
     */
    public $path;

    /**
     * 命名原则
     * @var string
     */
    public $namedType;

    /**
     * 允许的类型
     * @var array
     */
    public $allowType;

    /**
     * 是否检查MIME
     * @var bool
     */
    public $checkMime;

    /**
     * 大小限制
     * @var int
     */
    public $maxSize;

    /**
     * 错误信息
     * @var array
     */
    private $errMessage;

    /**
     * 结果信息
     * @var array
     */
    private $resMessage;

    /**
     * 初始化
     * @param string $name 表单名称
     */
    public function __construct($name) {
        $files = $_FILES[$name];
        #多文件上传还是单文件上传
        if (is_array($files['name'])) {
            foreach ($files as $key => $file) {
                foreach ($file as $k => $v) {
                    if (!$v) {
                        continue;
                    }
                    $this->fileData[$k]->{$key} = $v;
                }
            }
        } else {
            $this->fileData[] = (object)$files;
        }
        #预设配置信息
        $this->path = UPLOAD_PATH;
        $this->allowType = explode(',', UPLOAD_FILE);
        $this->checkMime = UPLOAD_MIME;
        $this->namedType = UPLOAD_SAVE_TYPE;
        $this->maxSize = UPLOAD_SIZE;
        $this->errMessage = array();
        $this->resMessage = array();
    }

    /**
     * 检查错误信息
     */
    private function checkError() {
        foreach ($this->fileData as $key => $file) {
            switch ($file->error) {
                case '1':
                case '2':
                    $this->errMessage[$key] = '上传文件超过最大值';
                    unset($this->fileData[$key]);
                    break;
                case '3':
                    $this->errMessage[$key] = '上传文件不完整';
                    unset($this->fileData[$key]);
                    break;
                case '4':
                    $this->errMessage[$key] = '没有上传文件';
                    unset($this->fileData[$key]);
                    break;
            }
        }
    }

    /**
     *
     */
    private function checkType() {
        foreach ($this->fileData as $key => $file) {
            $type = pathinfo($file->name, PATHINFO_EXTENSION);
            $this->fileData[$key]->suffix = $type;
            if (!in_array($type, $this->allowType)) {
                $this->errMessage[$key] = '文件格式不允许上传';
                unset($this->fileData[$key]);
            }
        }
    }

    /**
     *
     */
    private function checkMime() {

    }

    /**
     *
     */
    private function namedFile() {
        foreach ($this->fileData as $key => $file) {
            if ($this->namedType == 'day') {
                $path = $this->path . date('ymd') . DS;
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                }
                $this->fileData[$key]->save_file = $path . uniqid() . '.' . $file->suffix;
            }
        }
    }

    /**
     * @return Upload
     */
    public function save() {
        if(!$this->fileData[0]){
            $this->errMessage[0] = '没有上传文件';
            return $this;
        }
        $this->checkError();
        $this->checkType();
        $this->checkMime();
        $this->namedFile();
        $this->saveFiles();
        return $this;
    }

    /**
     * @return bool
     */
    public function isOk(){
        if($this->errMessage){
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function error(){
        return $this->errMessage;
    }

    /**
     *
     */
    private function saveFiles() {
        foreach ($this->fileData as $key => $file) {
            if(!move_uploaded_file($file->tmp_name,$file->save_file)){
                if(!copy($file->tmp_name,$file->save_file)){
                    $this->errMessage[$key] = '上传文件失败';
                    unset($this->fileData[$key]);
                    continue;
                }
                $this->errMessage[$key] = '上传文件失败';
                unset($this->fileData[$key]);
                continue;
            }
            $this->resMessage[$key] = $file->save_file;
        }
    }

}
