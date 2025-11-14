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
            'min' => '9.0.0.0',
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
}
