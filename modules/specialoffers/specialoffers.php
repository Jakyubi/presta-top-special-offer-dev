<?php
if(!defined('_PS_VERSION_')){
    exit;
}

require_once __DIR__.'/classes/BannerManager.php';
require_once __DIR__.'/classes/FormManager.php';



class SpecialOffers extends Module
{ 


    public $bannerManager;
    public $formManager;

    
    public function __construct()
    {
        $this->name = 'specialoffers';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'abc';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Special offers', [], 'Modules.Specialoffers.Admin');
        $this->description = $this->trans('Description of module', [], 'Modules.Specialoffers.Admin');
        $this->confirmUninstall = $this->trans('Are you sure to uninstall?', [], 'Modules.Specialoffers.Admin');

        if(!Configuration::get('SPECIALOFFERS_MODULE_NAME')){
            $this->warning = $this->trans('No name provided', [], 'Modules.Specialoffers.Admin');
        }

        $this->bannerManager = new BannerManager();
        $this->formManager = new FormManager($this);

    }

    public function install()
    {
        return(
            parent::install()
            && $this->installDb()
            && $this->registerHook('displayBanner')
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('SPECIALOFFERS_MODULE_NAME', 'Special offers')
        );
    }

    public function installDb()
    {
        $sql = 
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'specialoffers_banners`(
            `id_banner` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_group` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            `text` TEXT,
            `enabled` TINYINT(1) DEFAULT 1,
            `date_start` DATETIME DEFAULT NULL,
            `date_end` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id_banner`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

            return Db::getInstance()->execute($sql);
    }

    public function uninstall()
    {
        return(
            parent::uninstall()
            && $this->uninstallDb()
            && Configuration::deleteByName('SPECIALOFFERS_MODULE_NAME')
            );
    }   

    public function uninstallDb()
    {
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'specialoffers_banners`';
        return Db::getInstance()->execute($sql);
    }

    public function hookActionFrontControllerSetMedia($params)
    {

            $this->context->controller->registerStylesheet(
                'splide-css',
                'modules/'.$this->name.'/resources/styles/splide.min.css',
                ['media'=>'all', 'priority' => 150]
            );
            
            $this->context->controller->registerJavascript(
                'splide-js', 
                'modules/'.$this->name.'/resources/scripts/splide.min.js',
                ['media'=>'all', 'priority' => 150]
            );
            
            $this->context->controller->registerJavascript(
                'splide-init', 
                'modules/'.$this->name.'/resources/scripts/splide-init.js',
                ['priority' => 160]
            );
        
    }

    public function hookDisplayBanner($params)
    {
        $enabled = (bool) Configuration::get('SPECIALOFFERS_MODULE_ENABLE');
        if(!$enabled){
            return '';
        }

        $id_lang = (int)$this->context->language->id;
        $banners = $this->bannerManager->getBanners($id_lang, true);

        $this->context->smarty->assign([
            'specialoffers_banner_text_color' => Configuration::get('SPECIALOFFERS_BANNER_TEXT_COLOR'),
            'specialoffers_banner_bg_color' => Configuration::get('SPECIALOFFERS_BANNER_BG_COLOR'),
            'banners' => $banners,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayFrontBanner.tpl');
    }
    
    public function getContent()
    {

        
        if(Tools::isSubmit('submitSettingsForm')){ //take data from settings form
            $enabled = Tools::getValue('SPECIALOFFERS_MODULE_ENABLE');
            $bannerEnabled = Tools::getValue('SPECIALOFFERS_BANNER_ENABLE');
            $dateStart = Tools::getValue('SPECIALOFFERS_BANNER_DATE_START');   //sets start/end date from form
            $dateEnd = Tools::getValue('SPECIALOFFERS_BANNER_DATE_END');       //sets null to 0000-00-00
            $bannerGroupId = (int)Tools::getValue('SPECIALOFFERS_BANNER_GROUP_ID');
            $languages = Language::getLanguages();

            Configuration::updateValue('SPECIALOFFERS_MODULE_ENABLE', $enabled);

            if(!$bannerGroupId){
                $sql = new DbQuery();
                $sql->select('MAX(id_group)');
                $sql->from('specialoffers_banners');
                $bannerGroupId = (int) Db::getInstance()->getValue($sql) +1;
            }

            foreach ($languages as $lang){
                $id_lang = (int)$lang['id_lang'];
                $text = Tools::getValue('SPECIALOFFERS_BANNER_TEXT_'.$id_lang);

                if(!empty(trim($text))){
                    $this->bannerManager->saveBanner([
                        'id_group' => $bannerGroupId,
                        'id_lang' => $id_lang,
                        'text' => $text,
                        'enabled' => $bannerEnabled,
                        'date_start' => $dateStart,
                        'date_end' => $dateEnd,
                    ]);
                }
            }

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name
            ]));
        }

        if(Tools::isSubmit('submitStyleForm')){ //take data from style form
            $textColor = Tools::getValue('SPECIALOFFERS_BANNER_TEXT_COLOR');
            $bgColor = Tools::getValue('SPECIALOFFERS_BANNER_BG_COLOR');

            Configuration::updateValue('SPECIALOFFERS_BANNER_TEXT_COLOR', $textColor);
            Configuration::updateValue('SPECIALOFFERS_BANNER_BG_COLOR', $bgColor);
        }

        if(Tools::isSubmit('cancelSettingsForm')){
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name,
                'tab' => 'settings',
            ]));
        }


        $bannerEdit = null;
        if(Tools::isSubmit('updatespecialoffers_banners')){
            $idGroup = (int)Tools::getValue('id_group');
            $bannerEdit = $this->bannerManager->getBannersByGroup($idGroup);
        }
        
        if(Tools::isSubmit('deletespecialoffers_banners')){
            $idGroup = (int)Tools::getValue('id_group');
            $this->bannerManager->deleteBanner($idGroup);
        }
        
        $active_tab = Tools::isSubmit('submitStyleForm') ? 'style' : 'settings';

        $show_form = Tools::isSubmit('updatespecialoffers_banners') || Tools::isSubmit('showAddForm');

        $list = $this->formManager->displayListForm();
        $this->context->smarty->assign([
            'active_tab' => $active_tab,
            'form_settings' => $this->formManager->displaySettingsForm($bannerEdit),
            'form_style' => $this->formManager->displayStyleForm(),
            'show_form' => $show_form,
            'list_banners' => $list,
        ]);
        
        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
                
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getTable() 
    {
        return $this->table;
    }

    public function getName()
    {
        return $this->name;
    }

}
