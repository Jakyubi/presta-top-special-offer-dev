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
            'max' => '8.99.99',
        ];
        $this->bootstrap = true;
        $this->is_configurable = 1;

        parent::__construct();

        $this->displayName = $this->trans('Special offers', [], 'Modules.Specialoffers.Admin');
        $this->description = $this->trans('Description of module', [], 'Modules.Specialoffers.Admin');
        $this->confirmUninstall = $this->trans('Are you sure to uninstall?', [], 'Modules.Specialoffers.Admin');

        if(!Configuration::get('SPECIALOFFERS_NAME')){
            $this->warning = $this->trans('No name provided', [], 'Modules.Specialoffers.Admin');
        }
    }

    public function install()
    {
        return(
            parent::install()
            && $this->installDb()
            && $this->registerHook('displayBanner')
            && Configuration::updateValue('SPECIALOFFERS_NAME', 'Special offers')
        );
    }

    public function installDb()
    {
        $sql = 
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'specialoffers_banners`(
            `id_banner` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `text` TEXT,
            `enabled` TINYINT(1) DEFAULT 1,
            PRIMARY KEY (`id_banner`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

            return Db::getInstance()->execute($sql);
    }


    public function uninstall()
    {
        return(
            parent::uninstall()
            && $this->uninstallDb()
            && Configuration::deleteByName('SPECIALOFFERS_NAME')
            );
    }   

    public function uninstallDb()
    {
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'specialoffers_banners`';
        return Db::getInstance()->execute($sql);
    }


    public function hookDisplayBanner($params)
    {
        $enabled = (bool) Configuration::get('SPECIALOFFERS_ENABLE');
        if(!$enabled){
            return '';
        }

        $banners = $this->getBanners(true);

        $this->context->smarty->assign([
            'specialoffers_text_color' => Configuration::get('SPECIALOFFERS_TEXT_COLOR'),
            'specialoffers_bg_color' => Configuration::get('SPECIALOFFERS_BG_COLOR'),
            'banners' => $banners,
        ]);

        return $this->display(__FILE__, 'views/templates/template.tpl');
    }
    
    
    public function getContent()
    {
        if(Tools::isSubmit('submitSettingsForm')){
            $text = Tools::getValue('SPECIALOFFERS_TEXT');
            $enabled = Tools::getValue('SPECIALOFFERS_ENABLE');
            $bannerId = Tools::getValue('BANNER_ID');
            $bannerEnabled = Tools::getValue('BANNER_ENABLE');
            //Configuration::updateValue('SPECIALOFFERS_TEXT', $text, true);
            Configuration::updateValue('SPECIALOFFERS_ENABLE', $enabled);

            if(!empty(trim($text))){
                if($bannerId){
                    Db::getInstance()->update('specialoffers_banners', [
                        'text' => pSQL($text),
                        'enabled' => (int)$bannerEnabled,
                    ], 'id_banner = ' . $bannerId);
                }else{
                    Db::getInstance()->insert('specialoffers_banners', [
                        'text' => pSQL($text),
                        'enabled' => (int)$bannerEnabled
                    ]);   
                }
            }

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name
            ]));
        }

        if(Tools::isSubmit('submitStyleForm')){
            $textColor = Tools::getValue('SPECIALOFFERS_TEXT_COLOR');
            $bgColor = Tools::getValue('SPECIALOFFERS_BG_COLOR');

            Configuration::updateValue('SPECIALOFFERS_TEXT_COLOR', $textColor);
            Configuration::updateValue('SPECIALOFFERS_BG_COLOR', $bgColor);
        }

        $bannerEdit = null;
        if(Tools::isSubmit('editBanner')){
            $idBanner = (int)Tools::getValue('editBanner');
            $bannerEdit = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'specialoffers_banners WHERE id_banner = '.$idBanner);
        }

        if(Tools::isSubmit('deleteBanner')){
            $idBanner = (int)Tools::getValue('deleteBanner');
            Db::getInstance()->delete('specialoffers_banners', 'id_banner='.(int)$idBanner);
        }

        $active_tab = 'settings';
        if (Tools::isSubmit('submitStyleForm')) {
            $active_tab = 'style';
        }

        $content = // WORK IN PROGRESS
            '<ul class="nav nav-tabs" role="tablist">
                <li class="'.($active_tab == 'settings' ? 'active' : '').'">
                    <a href="#tab-settings" data-toggle="tab">'.$this->l('Settings').'</a>
                </li>
                <li class="'.($active_tab == 'style' ? 'active' : '').'">
                    <a href="#tab-style" data-toggle="tab">'.$this->l('Style').'</a>
                </li>
            </ul>
            <div class="tab-content" >
                <div class="tab-pane '.($active_tab == 'settings' ? 'active' : '').'" id="tab-settings">
                    '.$this->displaySettingsForm($bannerEdit).'
                </div>
                <div class="tab-pane '.($active_tab == 'style' ? 'active' : '').'" id="tab-style">
                    '.$this->displayStyleForm().'
                </div>
            </div>';

        $banners = $this->getBanners(false);

        foreach ($banners as $banner){
            $content .= '
            <div>
            '.htmlspecialchars($banner['id_banner']).'
            '.htmlspecialchars($banner['text']).'

            <a href="'.$this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name,
                'editBanner' => $banner['id_banner']
            ]).'">'.$this->l('Edit').'</a>

            <a href="'.$this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name,
                'deleteBanner' => $banner['id_banner']
            ]).'">'.$this->l('Delete').'</a>
            '.htmlspecialchars($banner['enabled']).'
        </div>';
        }

        return $content;

    }

    public function displaySettingsForm($bannerEdit = null)
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [ // module on/off
                        'type' => 'switch',
                        'label' => $this->l('Enable module'),
                        'name' => 'SPECIALOFFERS_ENABLE',
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
                        'name' => 'BANNER_ENABLE',
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
                        'name' => 'SPECIALOFFERS_TEXT',
                        'autoload_rte' => false,
                        'rows' => 10,
                        'cols' => 50,
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'BANNER_ID'
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = $this->getHelper();
        $helper->submit_action = 'submitSettingsForm';
        
        $helper->fields_value['SPECIALOFFERS_ENABLE'] = 
        Tools::getValue('SPECIALOFFERS_ENABLE', Configuration::get('SPECIALOFFERS_ENABLE'));

        /*
                $helper->fields_value['SPECIALOFFERS_TEXT'] =
                Tools::getValue('SPECIALOFFERS_TEXT', Configuration::get('SPECIALOFFERS_TEXT'));
        */
        $helper->fields_value['BANNER_ENABLE'] = $bannerEdit ? $bannerEdit['enabled'] : 1;
        $helper->fields_value['SPECIALOFFERS_TEXT'] = $bannerEdit ? $bannerEdit['text'] : '';
        $helper->fields_value['BANNER_ID'] = $bannerEdit ? $bannerEdit['id_banner'] : '';

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
                        'name' => 'SPECIALOFFERS_TEXT_COLOR',
                    ],
                    [ // background color
                        'type' => 'color',
                        'label' => $this->l('Background color'),
                        'name' => 'SPECIALOFFERS_BG_COLOR',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],

        ];

        $helper = $this->getHelper();
        $helper->submit_action = 'submitStyleForm';

        $helper->fields_value['SPECIALOFFERS_TEXT_COLOR'] =
        Tools::getValue('SPECIALOFFERS_TEXT_COLOR', Configuration::get('SPECIALOFFERS_TEXT_COLOR'));

        $helper->fields_value['SPECIALOFFERS_BG_COLOR'] =
        Tools::getValue('SPECIALOFFERS_BG_COLOR', Configuration::get('SPECIALOFFERS_BG_COLOR'));

        return $helper->generateForm([$form]);
    }

    public function getHelper()
    {
        $helper = new HelperForm();
        //$helper->module = $this;
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        
        return $helper;
    }

    public function getBanners($onlyEnabled = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'specialoffers_banners`';

        if($onlyEnabled){$sql .= ' WHERE enabled=1';}

        return Db::getInstance()->executeS($sql);
    }

}


