<?php

Yii::import('zii.widgets.CMenu');

class MenuRenderer extends CMenu {

    public $id = 1;
    public $firstItemCssClass = 'first';
    public $lastItemCssClass = 'last';
    public $dirCssClass = 'dir';
    public $append = array();

    public function init() {
        $menu = Menu::model()->findByPk($this->id);
        if (!$menu)
            return false;
        //throw new CHttpException(404, 'The specified menu (id=' . $this->id . ') cannot be found.');

        $class = array('dropdown');
        if ($menu->vertical) {
            $class[] = 'dropdown-vertical';
            if ($menu->rtl) {
                $class[] = 'dropdown-vertical-rtl';
                $cssFile = 'dropdown.vertical.rtl.css';
            } else {
                $cssFile = 'dropdown.vertical.css';
            }
        } else if ($menu->upward) {
            $class[] = 'dropdown-upward';
            $cssFile = 'dropdown.upward.css';
        } else {
            $class[] = 'dropdown-horizontal';
            $cssFile = 'dropdown.css';
        }

        $this->htmlOptions['class'] = implode(' ', $class);

        $this->items = array_merge($menu->items, $this->append);

        $basedir = dirname(__FILE__) . '/../assets/frontend';
        $baseUrl = Yii::app()->getAssetManager()->publish($basedir);

        Yii::app()->getClientScript()->registerCSSFile($baseUrl . '/css/' . $cssFile)
                ->registerCSSFile($baseUrl . '/themes/' . $menu->theme . '/default.css');

        //ToDo: these should added just for IE7, i don't know how to do this
//            Yii::app()->getClientScript()->registerCoreScript('jquery')
//                                            ->registerScriptFile($baseUrl.'/js/jquery.dropdown.js');
        parent::init();
    }

    protected function renderMenuRecursive($items) {


        $count = 0;
        $n = count($items);
        foreach ($items as $item) {

            if ($item == array())
                continue;

            //handle links here
            if (isset($item['url'])) {
                if (Awecms::isUrl($item['url'])) {
                    //NOP
                } else if (substr($item['url'], 0, 2) == '//') {
                    //convert //foo to /foo
                    $item['url'] = substr($item['url'], 1);
                } else {
                    $item['url'] = array($item['url']);
                }
            }

            $count++;
            $options = isset($item['itemOptions']) ? $item['itemOptions'] : array();
            $class = array();
//            if ($item['active'] && $this->activeCssClass != '')
//                $class[] = $this->activeCssClass;
            if ($count === 1 && $this->firstItemCssClass != '')
                $class[] = $this->firstItemCssClass;
            if ($count === $n && $this->lastItemCssClass != '')
                $class[] = $this->lastItemCssClass;
            if ($class !== array()) {
                if (empty($options['class']))
                    $options['class'] = implode(' ', $class);
                else
                    $options['class'].=' ' . implode(' ', $class);
            }

            echo CHtml::openTag('li', $options);
            if (isset($item['items']) && count($item['items'])) {
                if (empty($options['class']))
                    $options['class'] = ' ' . $this->dirCssClass;
                else
                    $options['class'].=' ' . $this->dirCssClass;
            }

            $item['linkOptions'] = $options;
            $menu = $this->renderMenuItem($item);
            if (isset($this->itemTemplate) || isset($item['template'])) {
                $template = isset($item['template']) ? $item['template'] : $this->itemTemplate;
                echo strtr($template, array('{menu}' => $menu));
            }
            else
                echo $menu;

            if (isset($item['items']) && count($item['items'])) {
                echo "\n" . CHtml::openTag('ul', isset($item['submenuOptions']) ? $item['submenuOptions'] : $this->submenuHtmlOptions) . "\n";
                $this->renderMenuRecursive($item['items']);
                echo CHtml::closeTag('ul') . "\n";
            }

            echo CHtml::closeTag('li') . "\n";
        }
    }

}

?>
