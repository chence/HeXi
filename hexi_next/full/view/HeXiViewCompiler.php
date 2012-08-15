<?php

/**
 * 模板编译类
 */
class HeXiViewCompiler {

    /**
     * 编译字符串
     * @static
     * @param string $string
     * @return string
     */
    public static function compile($string) {
        $string = str_replace('<!--foreach(', '<?php foreach(', $string);
        $string = str_replace('<!--for(', '<?php for(', $string);
        $string = str_replace(')-->', '){ ?>', $string);
        $string = str_replace(array('<!--endforeach-->', '<!--endfor-->'), '<?php } ?>', $string);
        $string = str_replace('<!--if(', '<?php if(', $string);
        $string = str_replace('<!--elseif(', '<?php }elseif(', $string);
        $string = str_replace('<!--else-->', '<?php }else{ ?>', $string);
        $string = str_replace('<!--endif-->', '<?php } ?>', $string);
        $string = str_replace('{{', '<?php echo ', $string);
        $string = str_replace('<!--{', '<?php ', $string);
        $string = str_replace(array('}-->', '}}'), ' ?>', $string);
        return $string;
    }

    /**
     * 渲染字符串
     * @static
     * @param string $string
     * @param array $viewData 渲染用的数据
     * @param bool $isCompile 是否开启编译
     * @return string
     */
    public static function render($string, $viewData, $isCompile = true) {
        if ($isCompile) {
            $string = self::compile($string);
        }
        #生成一个临时文件，写入编译后字符串
        $file = __DIR__ . uniqid() . '.php';
        file_put_contents($file, $string);
        #开启缓冲，解析编译后字符串
        ob_start();
        extract($viewData);
        include_once $file;
        $content = ob_get_contents();
        ob_end_clean();
        #删除文件，返回编译后内容
        unlink($file);
        return $content;
    }

    /**
     * 处理布局编译
     * @static
     * @param string $file
     * @param bool $hasChild 是否布局文件中还有子文件，只支持到这一级，即是layout-layout-file，不支持无限layout
     * @return mixed|string
     * @throws HeXiViewException
     */
    public static function compileLayout($file, $hasChild = false) {
        $string = file_get_contents($file);
        $pattern = '/<!--layout:(.*)-->/';
        if (!preg_match_all($pattern, $string, $matches)) {
            return $string;
        }
        $matches[0] = array_unique($matches[0]);
        $matches[1] = array_unique($matches[1]);
        $subTpl = array_combine($matches[0], $matches[1]);
        foreach ($subTpl as $str => $tpl) {
            #通过视图文件判断嵌入视图的位置
            $tpl = dirname($file) . '/' . $tpl;
            if (!is_file($tpl)) {
                throw new HeXiViewException('The embed template "' . $tpl.'" is lost !');
            }
            if ($hasChild) {
                #编译嵌入式图的嵌入视图，只处理第二级
                $string = str_replace($str, self::compileLayout($tpl), $string);
            } else {
                $string = str_replace($str, file_get_contents($tpl), $string);
            }
        }
        return $string;
    }
}
