<?php
if(!defined('_PS_VERSION_')){
    exit;
}

class SpecialOffers extends Module
{ 

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

        $banners = $this->getBanners(true);

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
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            Configuration::updateValue('SPECIALOFFERS_MODULE_ENABLE', $enabled);

            if(!$bannerGroupId){
                $bannerGroupId = (int) Db::getInstance()->getValue(
                    'SELECT MAX(id_group) FROM '._DB_PREFIX_.'specialoffers_banners') +1;
            }

            foreach ($languages as $lang){
                $id_lang = (int)$lang['id_lang'];
                $text = Tools::getValue('SPECIALOFFERS_BANNER_TEXT_'.$id_lang);

                $existing = Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM '._DB_PREFIX_.'specialoffers_banners
                    WHERE id_group = '.(int)$bannerGroupId.' AND id_lang = '.(int)$id_lang
                );

                if(!empty(trim($text))){
                    if($existing){
                        Db::getInstance()->update('specialoffers_banners', [ //update banner
                            'text' => $text,
                            'enabled' => (int)$bannerEnabled,
                            'date_start' => pSQL($dateStart),
                            'date_end' => pSQL($dateEnd),
                        ], 'id_group = ' . $bannerGroupId.' AND id_lang = '.(int)$id_lang);
                    }else{
                        Db::getInstance()->insert('specialoffers_banners', [ //insert new banner
                            'id_group' => $bannerGroupId,
                            'id_lang' => $id_lang,                            
                            'text' => $text,
                            'enabled' => (int)$bannerEnabled,
                            'date_start' => pSQL($dateStart),
                            'date_end' => pSQL($dateEnd),
                        ]);   
                    }
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

            $bannerEdit = Db::getInstance()->executeS('
            SELECT * FROM '._DB_PREFIX_.'specialoffers_banners 
            WHERE id_group = '.(int)$idGroup);
        }
        
        if(Tools::isSubmit('deletespecialoffers_banners')){
            $idGroup = (int)Tools::getValue('id_group');
            Db::getInstance()->delete('specialoffers_banners', 'id_group = ' . $idGroup);
        }
        
        $active_tab = Tools::isSubmit('submitStyleForm') ? 'style' : 'settings';

        $show_form = Tools::isSubmit('updatespecialoffers_banners') || Tools::isSubmit('showAddForm');

        $list = $this->displayListForm();
        $this->context->smarty->assign([
            'active_tab' => $active_tab,
            'form_settings' => $this->displaySettingsForm($bannerEdit),
            'form_style' => $this->displayStyleForm(),
            'show_form' => $show_form,
            'list_banners' => $list,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');

    }

    public function displayListForm()
    {
        
        $fields_list = [
            'id_banner' =>[
                'title' => $this->l('ID'),
                'type' => 'text',
            ],
            'id_group' => [
                'title' => $this->l('Group ID'),
                'type' => 'text',
            ],
            'id_lang' => [
                'title' => $this->l('Lang ID'),
                'type' => 'text',
            ],
            'text' => [
                'title' => $this->l('Text'),
                'type' => 'text',
            ],
            'date_start' => [
                'title' => $this->l('Start date'),
                'type' => 'datetime',
            ],
            'date_end' => [
                'title' => $this->l('End date'),
                'type' => 'datetime',
            ],
            'enabled' => [
                'title' => $this->l('Enabled'),
                'type' => 'bool',
            ],
        ];

        $banners = $this->getBanners(false);

        $helper = $this->getHelperList();
        $helper->title = $this->l('Banner list');
        $helper->identifier = 'id_group';
        $helper->table = 'specialoffers_banners';
        $helper->actions = ['edit', 'delete'];
        $helper->toolbar_btn['new'] = [
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&showAddForm=1&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add New Banner'),
        ];

        foreach($banners as &$banner){
            $banner['text'] = strip_tags($banner['text']);
        }
        unset($banner);

        return $helper->generateList($banners, $fields_list);
    }

    public function displaySettingsForm($bannerEdit = null)
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');


        $commonData = [
            'enabled' => 1,
            'id_banner' => '',
            'id_group' => '',
            'date_start' => '',
            'date_end' => ''
        ];

        if($bannerEdit) {
            $firstRow = $bannerEdit[0];
            $commonData['enabled'] = $firstRow['enabled'];
            $commonData['id_banner'] = $firstRow['id_banner'];
            $commonData['id_group'] = $firstRow['id_group'];
            $commonData['date_start'] = $firstRow['date_start'];
            $commonData['date_end'] = $firstRow['date_end'];
        }

        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [ // module on/off
                        'type' => 'switch',
                        'label' => $this->l('Enable module'),
                        'name' => 'SPECIALOFFERS_MODULE_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ]
                        ],
                    ],
                    [ // banner on/off
                        'type' => 'switch',
                        'label' => $this->l('Enable banner'),
                        'name' => 'SPECIALOFFERS_BANNER_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'banner_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'banner_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ],
                        ],
                    ],
                    [ // text input
                        'type' => 'textarea',
                        'label' => $this->l('Text to display'),
                        'name' => 'SPECIALOFFERS_BANNER_TEXT',
                        'autoload_rte' => true,
                        'rows' => 10,
                        'cols' => 50,
                        'lang' => true,
                    ],
                    [ // display banner id during edit
                        'type' => 'text',
                        'label' => $this->l('Banner ID'),
                        'name' => 'SPECIALOFFERS_BANNER_ID_DISPLAY',
                        'readonly' => true,
                    ],
                    [ // banner id
                        'type' => 'text',
                        'label' => $this->l('Group ID'),
                        'readonly' => true,
                        'name' => 'SPECIALOFFERS_BANNER_GROUP_ID',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'SPECIALOFFERS_BANNER_GROUP_ID_DISPLAY',
                    ],
                    [ // start date
                        'type' => 'datetime',
                        'label' => $this->l('Start date'),
                        'name' => 'SPECIALOFFERS_BANNER_DATE_START',
                        
                    ],
                    [ // end date
                        'type' => 'datetime',
                        'label' => $this->l('End date'),
                        'name' => 'SPECIALOFFERS_BANNER_DATE_END'
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitSettingsForm',
                ],
                'buttons' => [
                    [
                        'title' => $this->l('Cancel'),
                        'name' => 'cancelSettingsForm',
                        'class' => 'btn btn-default pull-right',
                        'type' => 'submit',
                        'onclick' => 'return true',
                    ],
                ],
            ],
        ];

        $languages = Language::getLanguages();

        $helper = $this->getHelperForm();
        $helper->submit_action = 'submitSettingsForm';
        
        $helper->fields_value['SPECIALOFFERS_MODULE_ENABLE'] = 
        Tools::getValue('SPECIALOFFERS_MODULE_ENABLE', Configuration::get('SPECIALOFFERS_MODULE_ENABLE'));

        $helper->fields_value['SPECIALOFFERS_BANNER_ENABLE'] = $commonData['enabled']; 
        $helper->fields_value['SPECIALOFFERS_BANNER_DATE_START'] = $commonData['date_start']; 
        $helper->fields_value['SPECIALOFFERS_BANNER_DATE_END'] = $commonData['date_end']; 
        $helper->fields_value['SPECIALOFFERS_BANNER_ID_DISPLAY'] = $commonData['id_banner']; 
        $helper->fields_value['SPECIALOFFERS_BANNER_GROUP_ID'] = $commonData['id_group']; 
        $helper->fields_value['SPECIALOFFERS_BANNER_GROUP_ID_DISPLAY'] = $commonData['id_group'];
        
        foreach ($languages as $lang) {
            $id_lang = (int)$lang['id_lang'];
            $text = '';

            if ($bannerEdit) {
                foreach ($bannerEdit as $row) {
                    if ($row['id_lang'] == $id_lang) {
                        $text = $row['text'];
                    }
                }
            }
            $helper->fields_value['SPECIALOFFERS_BANNER_TEXT'][$id_lang] = $text;
        }

        $helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		$helper->languages = $this->context->controller->getLanguages();

        return $helper->generateForm([$form]);

    }

    public function displayStyleForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Colors'),
                ],
                'input' => [
                    [ // text color
                        'type' => 'color',
                        'label' => $this->l('Text color'),
                        'name' => 'SPECIALOFFERS_BANNER_TEXT_COLOR',
                    ],
                    [ // background color
                        'type' => 'color',
                        'label' => $this->l('Background color'),
                        'name' => 'SPECIALOFFERS_BANNER_BG_COLOR',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],

        ];

        $helper = $this->getHelperForm();
        $helper->submit_action = 'submitStyleForm';

        $helper->fields_value['SPECIALOFFERS_BANNER_TEXT_COLOR'] =
        Tools::getValue('SPECIALOFFERS_BANNER_TEXT_COLOR', Configuration::get('SPECIALOFFERS_BANNER_TEXT_COLOR'));

        $helper->fields_value['SPECIALOFFERS_BANNER_BG_COLOR'] =
        Tools::getValue('SPECIALOFFERS_BANNER_BG_COLOR', Configuration::get('SPECIALOFFERS_BANNER_BG_COLOR'));

        return $helper->generateForm([$form]);
    }

    public function getHelperForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        
        return $helper;
    }

    public function getHelperList(){
        $helper = new HelperList();
        $helper->module = $this;
        $helper->show_toolbar = false;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper;
    }

    public function getBanners($onlyEnabled = false)
    {
        $dateNow = date('Y-m-d H:i:s'); //current date
        $id_lang = (int)$this->context->language->id;
        
        
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'specialoffers_banners`';


        if($onlyEnabled){
            $sql .= ' WHERE id_lang='.(int)$id_lang.' AND enabled=1
                    AND (date_start IS NULL OR date_start="0000-00-00 00:00:00" OR date_start <= "'.$dateNow.'")
                    AND (date_end IS NULL OR date_end="0000-00-00 00:00:00" OR date_end >= "'.$dateNow.'")';}

        return Db::getInstance()->executeS($sql);
    }

}
