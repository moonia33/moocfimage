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
        $this->version = '1.0.1';
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
        $ok = parent::install()
            && Configuration::updateValue('MOOCFIMAGE_ENABLED', 1)
            && Configuration::updateValue('MOOCFIMAGE_QUALITY', 85)
            && $this->registerHook('actionDispatcher');

        if ($ok) {
            $this->installOrMergeLinkOverride();
        }
        return $ok;
    }

    public function uninstall()
    {
        $this->uninstallOrRevertLinkOverride();

        Configuration::deleteByName('MOOCFIMAGE_ENABLED');
        Configuration::deleteByName('MOOCFIMAGE_QUALITY');

        return parent::uninstall();
    }

    public function hookActionDispatcher($params)
    {
        if (!Configuration::get('MOOCFIMAGE_OVERRIDE_READY')) {
            $this->installOrMergeLinkOverride();
        }
    }

    protected function installOrMergeLinkOverride()
    {
        $root = _PS_ROOT_DIR_ . '/override/classes/Link.php';
        $src = __DIR__ . '/override/classes/Link.php';

        if (!file_exists($root)) {
            if (!is_dir(dirname($root))) {
                @mkdir(dirname($root), 0775, true);
            }
            if (!@copy($src, $root)) {
                return false;
            }
            Configuration::updateValue('MOOCFIMAGE_OVERRIDE_READY', 1);
            return true;
        }

        $content = @file_get_contents($root);
        if ($content === false) {
            return false;
        }

        if (strpos($content, 'moocfimage') !== false) {
            Configuration::updateValue('MOOCFIMAGE_OVERRIDE_READY', 1);
            return true; // jau integruota
        }

        $backup = $root . '.bak.moocfimage.' . date('YmdHis');
        @copy($root, $backup);
        Configuration::updateValue('MOOCFIMAGE_OVERRIDE_BACKUP', $backup);

        // 1) Įterpiame helperį (tik metodą) prieš paskutinę klases kabutę
        if (preg_match('/class\s+Link\s+extends\s+LinkCore[^{]*{[\s\S]*}/', $content)) {
            if (strpos($content, 'applyCloudflareImageTransform') === false) {
                $srcContent = file_get_contents($src);
                if (preg_match('/\/\* moocfimage:helper start \*\/(.*?)\/\* moocfimage:helper end \*\//s', $srcContent, $m)) {
                    $helper = "\n" . $m[0] . "\n";
                    $pos = strrpos($content, '}');
                    if ($pos !== false) {
                        $content = substr($content, 0, $pos) . $helper . substr($content, $pos);
                    }
                }
            }
        }

        // 2) Modifikuojame getImageLink, kiekvieną return apvyniodami saugiai
        if (preg_match('/function\s+getImageLink\s*\([^)]*\)\s*\{[\s\S]*?\}/m', $content)) {
            $content = preg_replace_callback(
                '/(function\s+getImageLink\s*\([^)]*\)\s*\{)([\s\S]*?)(\})/m',
                function ($m) {
                    $body = $m[2];
                    if (strpos($body, 'applyCloudflareImageTransform') !== false) {
                        return $m[0];
                    }
                    $body = preg_replace('/return\s+([^;]+);/m', '$_moocf_original = $1;' . "\n" . 'if (Module::isEnabled(\'moocfimage\') && Configuration::get(\'MOOCFIMAGE_ENABLED\')) { $_moocf_original = $this->applyCloudflareImageTransform($_moocf_original, $type); }' . "\n" . 'return $_moocf_original;', $body, 1);
                    return $m[1] . $body . $m[3];
                },
                $content
            );
        }

        if (@file_put_contents($root, $content) === false) {
            return false;
        }

        Configuration::updateValue('MOOCFIMAGE_OVERRIDE_READY', 1);
        return true;
    }

    protected function uninstallOrRevertLinkOverride()
    {
        $root = _PS_ROOT_DIR_ . '/override/classes/Link.php';
        $backup = Configuration::get('MOOCFIMAGE_OVERRIDE_BACKUP');

        if ($backup && file_exists($backup)) {
            @copy($backup, $root);
            @unlink($backup);
        } else {
            // Jei failas yra mūsų standalone override – pašaliname
            if (file_exists($root)) {
                $content = file_get_contents($root);
                if ($content !== false && strpos($content, 'moocfimage: standalone override') !== false) {
                    @unlink($root);
                }
            }
        }

        Configuration::deleteByName('MOOCFIMAGE_OVERRIDE_BACKUP');
        Configuration::deleteByName('MOOCFIMAGE_OVERRIDE_READY');
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
