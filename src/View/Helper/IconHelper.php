<?php
namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

class IconHelper extends Helper
{
    /**
     * Outputs a category icon
     *
     * @param string $categoryName Category name
     * @param string|null $mode Current output mode; set to 'email' if in an email
     * @return string
     */
    public static function category($categoryName, $mode = null)
    {
        if ($mode == 'email') {
            $base = Configure::read('App.fullBaseUrl');
            if (substr($base, -1, 1) != '/') {
                $base = $base . '/';
            }
            $dir = $base . 'img/icons/categories/';
            $filename = 'meicon_' . strtolower(str_replace(' ', '_', $categoryName)) . '_32x32.png';

            return sprintf(
                '<img src="%s%s" title="%s" class="category" alt="%s" />',
                $dir,
                $filename,
                $categoryName,
                $categoryName
            );
        }

        $class = 'icon icon-' . strtolower(str_replace(' ', '-', $categoryName));

        return sprintf('<i class="%s" title="%s"></i>', $class, $categoryName);
    }
}
