<?php

/**
 * 3D标签云特效
 * @package Svg3dTagCloud
 * @author Yuuki
 * @original_author Hoe 
 * @version 1.0.1
 * @link http://www.yuukisoul.com
 */
class Svg3dTagCloud_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     */
    public static function activate()
    {  	
	    Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'header');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     * entries：一个对象数组，用于初始化标签。
     * width：标签云的宽度。
     * height：标签云的高度。
     * radius：标签云的半径。
     * radiusMin：标签云的最小半径。
     * bgDraw：是否使用背景色。
     * bgColor：背景颜色。
     * opacityOver：选中的标签透明度。
     * opacityOut：未选中的标签透明度。
     * opacitySpeed：标签透明度过渡速度。
     * fov：how the content is presented。
     * speed：标签云动画的速度。
     * fontFamily：标签云的字体。
     * fontSize：标签云的字体大小。
     * fontColor：标签云的字体颜色。
     * fontWeight：标签云的字体的fontWeight。
     * fontStyle：标签云的字体样式。
     * fontStretch：标签云的字体的fontStretch。
     * fontToUpperCase：是否转换为大写字体。
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $jquery = new Typecho_Widget_Helper_Form_Element_Radio('jquery',
            ['0' => _t('不加载'), '1' => _t('加载')],
            '0', _t('是否加载外部jQuery库'), _t('插件需要jQuery库文件的支持，如果已加载就不需要加载了 jquery源是新浪Public Resources on SAE：https://lib.sinaapp.com/js/jquery/1.9.1/jquery-1.9.1.min.js'));
        $form->addInput($jquery);

        $divName = new Typecho_Widget_Helper_Form_Element_Text('divName', null, '#tag-cloud', _t('标签云容器名'), 'JQuery选择器,就是承载标签云DOM元素');
        $form->addInput($divName);

        $targetType = [
            '_self' => _t('在本窗口打开'),
            '_blank' => _t('新窗口打开'),
        ];
        $target = new Typecho_Widget_Helper_Form_Element_Radio('target', $targetType, '_self', _t('标签打开方式'));
        $form->addInput($target);

        $limit = new Typecho_Widget_Helper_Form_Element_Text('limit', null, 50, _t('显示条数'), '请填入数字');
        $form->addInput($limit);

        $width = new Typecho_Widget_Helper_Form_Element_Text('width', null, 300, _t('标签云容器宽度'), '默认单位px,也可以填入50%');
        $form->addInput($width);

        $height = new Typecho_Widget_Helper_Form_Element_Text('height', null, 350, _t('标签云容器高度'), '默认单位px,也可以填入100%');
        $form->addInput($height);

        $radius = new Typecho_Widget_Helper_Form_Element_Text('radius', null, '80%', _t('标签云的半径'), '默认单位px,也可以填入100%');
        $form->addInput($radius);

        $radiusMin = new Typecho_Widget_Helper_Form_Element_Text('radiusMin', null, '80%', _t('标签云的最小半径'), '默认单位px,也可以填入100%');
        $form->addInput($radiusMin);

        $bgColor = new Typecho_Widget_Helper_Form_Element_Text('bgColor', null, '#FFFFFF', _t('标签云背景颜色'), '#FFFFFF OR white');
        $form->addInput($bgColor);

        $opacityOver = new Typecho_Widget_Helper_Form_Element_Text('opacityOver', null, 1, _t('选中的标签透明度'), '请填入0.1 - 1之间的透明度,数字越少越透明');
        $form->addInput($opacityOver);

        $opacityOut = new Typecho_Widget_Helper_Form_Element_Text('opacityOut', null, 0.3, _t('未选中的标签透明度'), '请填入0.1 - 1之间的透明度,数字越少越透明');
        $form->addInput($opacityOut);

        $opacitySpeed = new Typecho_Widget_Helper_Form_Element_Text('opacitySpeed', null, 50, _t('标签透明度过渡速度'), '默认单位毫秒');
        $form->addInput($opacitySpeed);

        $speed = new Typecho_Widget_Helper_Form_Element_Text('speed', null, 0.2, _t('标签云动画速度'), '数字越大旋转越快');
        $form->addInput($speed);

        $fontFamily = new Typecho_Widget_Helper_Form_Element_Text('fontFamily', null, 'Oswald, Arial, sans-serif', _t('标签云的字体'), '标签云的字体');
        $form->addInput($fontFamily);

        $fontSize = new Typecho_Widget_Helper_Form_Element_Text('fontSize', null, 12, _t('标签云的字体大小'), '默认单位px');
        $form->addInput($fontSize);

        $fontColor = new Typecho_Widget_Helper_Form_Element_Text('fontColor', null, '#5f5f5f', _t('标签云的字体颜色'), '#FFFFFF OR white');
        $form->addInput($fontColor);

        $fontWeight = new Typecho_Widget_Helper_Form_Element_Text('fontWeight', null, 'normal', _t('标签云的字体的粗细'), '默认单位px');
        $form->addInput($fontWeight);

        $fontStyle = new Typecho_Widget_Helper_Form_Element_Radio('fontStyle', ['normal' => _t('普通'), 'italic' => _t('斜体')], 'normal', _t('标签云的字体样式'), _t('normal or italic, 普通或斜体'));
        $form->addInput($fontStyle);

        $fontToUpperCase = new Typecho_Widget_Helper_Form_Element_Radio('fontToUpperCase', ['false' => _t('否'), 'true' => _t('是')], 'false', _t('是否转换为大写字体'));
        $form->addInput($fontToUpperCase);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {

    }

    /**
     * 为footer添加js文件
     * JS库地址 https://github.com/NiklasKnaack/jquery-svg3dtagcloud-plugin
     * @return void
     * @throws Typecho_Db_Exception
     * @throws Typecho_Plugin_Exception
     */

  public static function header()
    {
        //$divName = Helper::options()->plugin('Svg3dTagCloud')->divName;
        //echo '<div id="'. trim($divName, '#') .'"></div>';
    }

    public static function footer()
    {
        // 获取插件配置数据
        $svg3dTagCloud = Helper::options()->plugin('Svg3dTagCloud');
        $jquery = Helper::options()->plugin('Svg3dTagCloud')->jquery;
        $settings = [];
        foreach ($svg3dTagCloud as $key => $item) {
            if ($key == 'fontToUpperCase') {
                $item = (int)$item; // svg3dtagcloud.fontToUpperCase 需要int类型
            }
            if ($key == 'jquery') continue; // 不属于svg3dtagcloud的配置
            $settings[$key] = $item;
        }
        $settings = json_encode($settings); // svg3dtagcloud库的配置
        // 查询标签
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $table = $prefix . 'metas';
        $limit = $svg3dTagCloud->limit;
        $tags = $db->fetchAll($db->select('slug,name')->from($table)->where('type = ?', 'tag')->order('mid', Typecho_Db::SORT_DESC)->limit($limit));
        // 组成符合svg3dtagcloud.entries的格式
        $entries = [];
        foreach ($tags as $key => $row) {
            $entries[$key]['label'] = $row['name'];
          //  $entries[$key]['url'] = '/tag/' . $row['slug'];
	    $entries[$key]['url'] = Typecho_Common::url(Typecho_Router::url('tag', ['slug' => $row['slug']]),Helper::options()->index);
            $entries[$key]['target'] = $svg3dTagCloud->target;
        }
        $entries = json_encode($entries);
        if($jquery) {
            echo '<script type="text/javascript" src="//lib.sinaapp.com/js/jquery/1.9.1/jquery-1.9.1.min.js"></script>';
        }
        // 加载JS库
        $jsUrl = Helper::options()->pluginUrl . '/Svg3dTagCloud/static/jquery.svg3dtagcloud.min.js';
        printf("<script type='text/javascript' src='%s'></script>\n", $jsUrl);
	$script = <<<SCRIPT

    <script type='text/javascript'>
    jQuery(function($) {
        // 1. 获取容器元素
        var \$container = $('{$svg3dTagCloud->divName}');
        
        // 2. 检查容器是否存在
        if (\$container.length > 0) {
            try {
                console.log('%c 3D标签云 https://github.com/yuukijiang/Svg3dTagCloud %c www.yuukisoul.com 😊 Svg3dTagCloud By Yuuki ', 
                    'font-family:\'Microsoft YaHei\',\'SF Pro Display\',Roboto,Noto,Arial,\'PingFang SC\',sans-serif;color:white;background:#ffa099;padding:5px 0;', 
                    'font-family:\'Microsoft YaHei\',\'SF Pro Display\',Roboto,Noto,Arial,\'PingFang SC\',sans-serif;color:#ffa099;background:#404040;padding:5px 0;');
                
                // 3. 初始化标签云
                var settings = $settings;
                settings.entries = $entries;
                \$container.html("").svg3DTagCloud(settings);
            } catch (e) {
                console.error("3D标签云初始化错误: ", e);
                console.error("请联系www.yuukisoul.com");
            }
        } else {
            // 4. 调试信息（可选）
            console.warn('3D标签云容器未找到: {$svg3dTagCloud->divName}');
        }
    });
</script>
\n
SCRIPT;
        echo $script;
    }
}
