<?php

class FormManager
{
    protected $module;
    public function __construct($module)
    {
        $this->module = $module;
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
                    'title' => $this->module->l('Settings'),
                ],
                'input' => [
                    [ // module on/off
                        'type' => 'switch',
                        'label' => $this->module->l('Enable module'),
                        'name' => 'SPECIALOFFERS_MODULE_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled')
                            ]
                        ],
                    ],
                    [ // banner on/off
                        'type' => 'switch',
                        'label' => $this->module->l('Enable banner'),
                        'name' => 'SPECIALOFFERS_BANNER_ENABLE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'banner_on',
                                'value' => 1,
                                'label' => $this->module->l('Enabled')
                            ],
                            [
                                'id' => 'banner_off',
                                'value' => 0,
                                'label' => $this->module->l('Disabled')
                            ],
                        ],
                    ],
                    [ // text input
                        'type' => 'textarea',
                        'label' => $this->module->l('Text to display'),
                        'name' => 'SPECIALOFFERS_BANNER_TEXT',
                        'autoload_rte' => true,
                        'rows' => 10,
                        'cols' => 50,
                        'lang' => true,
                    ],
                    [ // display banner id during edit
                        'type' => 'text',
                        'label' => $this->module->l('Banner ID'),
                        'name' => 'SPECIALOFFERS_BANNER_ID_DISPLAY',
                        'readonly' => true,
                    ],
                    [ // banner id
                        'type' => 'text',
                        'label' => $this->module->l('Group ID'),
                        'readonly' => true,
                        'name' => 'SPECIALOFFERS_BANNER_GROUP_ID',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'SPECIALOFFERS_BANNER_GROUP_ID_DISPLAY',
                    ],
                    [ // start date
                        'type' => 'datetime',
                        'label' => $this->module->l('Start date'),
                        'name' => 'SPECIALOFFERS_BANNER_DATE_START',
                        
                    ],
                    [ // end date
                        'type' => 'datetime',
                        'label' => $this->module->l('End date'),
                        'name' => 'SPECIALOFFERS_BANNER_DATE_END'
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitSettingsForm',
                ],
                'buttons' => [
                    [
                        'title' => $this->module->l('Cancel'),
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
		$helper->languages = $this->module->getContext()->controller->getLanguages();

        return $helper->generateForm([$form]);
    }


    public function displayStyleForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Colors'),
                ],
                'input' => [
                    [ // text color
                        'type' => 'color',
                        'label' => $this->module->l('Text color'),
                        'name' => 'SPECIALOFFERS_BANNER_TEXT_COLOR',
                    ],
                    [ // background color
                        'type' => 'color',
                        'label' => $this->module->l('Background color'),
                        'name' => 'SPECIALOFFERS_BANNER_BG_COLOR',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save'),
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


    public function displayListForm()
    {
        
        $fields_list = [
            'id_banner' =>[
                'title' => $this->module->l('ID'),
                'type' => 'text',
            ],
            'id_group' => [
                'title' => $this->module->l('Group ID'),
                'type' => 'text',
            ],
            'id_lang' => [
                'title' => $this->module->l('Lang ID'),
                'type' => 'text',
            ],
            'text' => [
                'title' => $this->module->l('Text'),
                'type' => 'text',
            ],
            'date_start' => [
                'title' => $this->module->l('Start date'),
                'type' => 'datetime',
            ],
            'date_end' => [
                'title' => $this->module->l('End date'),
                'type' => 'datetime',
            ],
            'enabled' => [
                'title' => $this->module->l('Enabled'),
                'type' => 'bool',
            ],
        ];

        $sort = Tools::getValue('specialoffers_bannersOrderby', 'id_group');
        $order = Tools::getValue('specialoffers_bannersOrderway', 'ASC');


        $id_lang = $this->module->getContext()->language->id;
        $banners = $this->module->bannerManager->getBanners($id_lang, false, $sort, $order);

        $helper = $this->getHelperList();
        $helper->title = $this->module->l('Banner list');
        $helper->identifier = 'id_group';
        $helper->table = 'specialoffers_banners';
        $helper->actions = ['edit', 'delete'];
        $helper->toolbar_btn['new'] = [
            'href' => AdminController::$currentIndex.'&configure='.$this->module->getName().'&showAddForm=1&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->module->l('Add New Banner'),
        ];

        foreach($banners as &$banner){
            $banner['text'] = strip_tags($banner['text']);
        }
        unset($banner);

        return $helper->generateList($banners, $fields_list);
    }


    
    public function getHelperForm()
    {
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->table = $this->module->getTable();
        $helper->name_controller = $this->module->getName();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->module->getName()]);
        $helper->submit_action = 'submit' . $this->module->getName();
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        
        return $helper;
    }

    public function getHelperList()
    {
        $helper = new HelperList();
        $helper->module = $this->module;
        $helper->show_toolbar = false;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->getName();

        return $helper;
    }
}