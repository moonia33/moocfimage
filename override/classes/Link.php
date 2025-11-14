<?php
/* moocfimage: standalone override */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Link extends LinkCore
{
    public function getImageLink($link_rewrite, $id_image, $type = null, $extension = 'jpg')
    {
        if (!Module::isEnabled('moocfimage') || !Configuration::get('MOOCFIMAGE_ENABLED')) {
            return parent::getImageLink($link_rewrite, $id_image, $type, $extension);
        }

        $url = parent::getImageLink($link_rewrite, $id_image, $type, $extension);
        return $this->applyCloudflareImageTransform($url, $type);
    }

    /* moocfimage:helper start */
    protected function applyCloudflareImageTransform($url, $type)
    {
        if (defined('_PS_ADMIN_DIR_') || PHP_SAPI === 'cli') {
            return $url;
        }
        if (empty($url) || strpos($url, 'http') !== 0) {
            return $url;
        }
        if (strpos($url, '/cdn-cgi/image/') !== false) {
            return $url;
        }

        $width = null;
        if ($type) {
            $imageType = ImageType::getByNameNType($type, 'products');
            if (is_array($imageType) && !empty($imageType['width'])) {
                $width = (int) $imageType['width'];
            }
        }
        if (!$width) {
            $fallbackMap = array(
                'home_default'  => 320,
                'cart_default'  => 80,
                'small_default' => 98,
                'medium_default'=> 452,
                'large_default' => 800,
            );
            if ($type && isset($fallbackMap[$type])) {
                $width = (int) $fallbackMap[$type];
            }
        }
        if (!$width) {
            return $url;
        }

        $quality = (int) Configuration::get('MOOCFIMAGE_QUALITY');
        if ($quality <= 0 || $quality > 100) {
            $quality = 85;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
            return $url;
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        $prefix = '/cdn-cgi/image/fit=scale-down,width=' . $width . ',quality=' . $quality . ',format=auto/';
        $newPath = $prefix . ltrim($parts['path'], '/');

        return $parts['scheme'] . '://' . $parts['host'] . $port . $newPath . $query;
    }
    /* moocfimage:helper end */
}
