<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Moocfimage extends Module
{
    public function __construct()
    {
        $this->name = 'moocfimage';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'moonia';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cloudflare Image Optimization');
        $this->description = $this->l('Prepina PrestaShop paveikslėlių URL per Cloudflare /cdn-cgi/image transformacijas.');

        $this->confirmUninstall = $this->l('Ar tikrai norite išjungti Cloudflare image optimizavimą?');
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue('MOOCFIMAGE_ENABLED', 1)
            && Configuration::updateValue('MOOCFIMAGE_QUALITY', 85);
    }

    public function uninstall()
    {
        Configuration::deleteByName('MOOCFIMAGE_ENABLED');
        Configuration::deleteByName('MOOCFIMAGE_QUALITY');

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitMoocfimage')) {
            $enabled = (int) Tools::getValue('MOOCFIMAGE_ENABLED', 1);
            $quality = (int) Tools::getValue('MOOCFIMAGE_QUALITY', 85);

            if ($quality < 1 || $quality > 100) {
                $output .= $this->displayError($this->l('Quality turi būti tarp 1 ir 100.'));
            } else {
                Configuration::updateValue('MOOCFIMAGE_ENABLED', $enabled);
                Configuration::updateValue('MOOCFIMAGE_QUALITY', $quality);
                $output .= $this->displayConfirmation($this->l('Nustatymai atnaujinti.'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Cloudflare Images nustatymai'),
                    'icon' => 'icon-picture',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Įjungta'),
                        'name' => 'MOOCFIMAGE_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Taip'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Ne'),
                            ],
                        ],
                        'desc' => $this->l('Išjungus, grįžtama prie originalių paveikslėlių URL.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Quality (1–100)'),
                        'name' => 'MOOCFIMAGE_QUALITY',
                        'class' => 'fixed-width-sm',
                        'suffix' => '%',
                        'desc' => $this->l('Cloudflare quality parametras. Rekomenduojama 75–90. Numatyta 85.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Išsaugoti'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitMoocfimage';
        $helper->fields_value = $this->getConfigFormValues();

        return $helper->generateForm([$fieldsForm]);
    }

    protected function getConfigFormValues()
    {
        return [
            'MOOCFIMAGE_ENABLED' => (int) Configuration::get('MOOCFIMAGE_ENABLED', 1),
            'MOOCFIMAGE_QUALITY' => (int) Configuration::get('MOOCFIMAGE_QUALITY', 85),
        ];
    }
}
