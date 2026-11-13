<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

namespace ViaBill\Service\LegalNote;

use ViaBill\Config\Config;

/**
 * Legal note (credit warning) shown next to the ViaBill payment options at the
 * checkout.
 *
 * It is always shown. The text is the default legal note, translated in the
 * module's translation files, in the customer's language ("auto") or in a
 * fixed language. The merchant can only change how it looks, in the "Legal
 * Note" section of the ViaBill settings.
 *
 * Class LegalNoteService
 */
class LegalNoteService
{
    /**
     * Filename Constant, the source of this class' translations.
     */
    const FILENAME = 'LegalNoteService';

    /**
     * Default legal note. It must be identical to the text in getDefaultText(),
     * which is the one the translation tools pick up.
     */
    const DEFAULT_TEXT = 'Warning! Borrow responsibly - it costs money.';

    /**
     * Default font style: the same as the payment option name.
     */
    const DEFAULT_FONT_STYLE = 'normal';

    /**
     * Default space above the legal note. Negative margins depend on the
     * theme (in the Classic theme, -18px leaves a 2px gap below the payment
     * option name), so the default is neutral and safe for every theme.
     */
    const DEFAULT_MARGIN_TOP = '0px';

    /**
     * Default space below the legal note, e.g. above the PriceTag.
     */
    const DEFAULT_MARGIN_BOTTOM = '20px';

    /**
     * Default space to the left of the legal note. In the Classic theme,
     * -20px lines it up with the ViaBill logo; the default is neutral and
     * safe for every theme.
     */
    const DEFAULT_MARGIN_LEFT = '0px';

    /**
     * Translation files of the fixed languages, keyed by language code.
     */
    const TRANSLATION_FILES = [
        'da' => 'da',
        'es' => 'es',
    ];

    /**
     * @var \ViaBill
     */
    private $module;

    /**
     * Translations read from the module's translation files, keyed by file.
     *
     * @var array
     */
    private $translations = [];

    public function __construct(\ViaBill $module)
    {
        $this->module = $module;
    }

    /**
     * Returns the options of the language setting.
     *
     * @return array
     */
    public function getLanguages()
    {
        return [
            ['id' => 'auto', 'name' => $this->module->l('Auto', self::FILENAME)],
            ['id' => 'en', 'name' => $this->module->l('English', self::FILENAME)],
            ['id' => 'da', 'name' => $this->module->l('Danish', self::FILENAME)],
            ['id' => 'es', 'name' => $this->module->l('Spanish', self::FILENAME)],
        ];
    }

    /**
     * Returns the options of the font style setting.
     *
     * @return array
     */
    public function getFontStyles()
    {
        return [
            ['id' => 'normal', 'name' => $this->module->l('Normal', self::FILENAME)],
            ['id' => 'bold', 'name' => $this->module->l('Bold', self::FILENAME)],
            ['id' => 'italic', 'name' => $this->module->l('Italic', self::FILENAME)],
            ['id' => 'bold-italic', 'name' => $this->module->l('Bold italic', self::FILENAME)],
        ];
    }

    /**
     * Returns the options of the text alignment setting.
     *
     * @return array
     */
    public function getAlignments()
    {
        return [
            ['id' => '', 'name' => $this->module->l('Theme default', self::FILENAME)],
            ['id' => 'left', 'name' => $this->module->l('Left', self::FILENAME)],
            ['id' => 'center', 'name' => $this->module->l('Center', self::FILENAME)],
            ['id' => 'right', 'name' => $this->module->l('Right', self::FILENAME)],
        ];
    }

    /**
     * Returns a valid CSS font size, or empty string. A plain number is taken
     * as pixels.
     *
     * @param mixed $value Value entered by the merchant
     *
     * @return string
     */
    public function sanitizeFontSize($value)
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';
        $value = str_replace(',', '.', $value);

        if (!preg_match('/^(\d+(?:\.\d+)?|\.\d+)\s*(px|em|rem|pt|%)?$/', $value, $matches)) {
            return '';
        }
        if ((float) $matches[1] <= 0) {
            return '';
        }

        return $matches[1] . (empty($matches[2]) ? 'px' : $matches[2]);
    }

    /**
     * Returns a valid CSS color (hex or a color name), or empty string.
     *
     * @param mixed $value Value entered by the merchant
     *
     * @return string
     */
    public function sanitizeColor($value)
    {
        $value = is_string($value) ? trim($value) : '';

        return preg_match('/^(#[0-9a-fA-F]{3}|#[0-9a-fA-F]{6}|[a-zA-Z]{3,20})$/', $value) ? $value : '';
    }

    /**
     * Returns the extra CSS declarations (e.g. "padding: 8px; border: 1px solid
     * #ccc") in a form that is safe to print inside a style attribute. Only
     * declarations are supported, so anything that could open a new rule or
     * a tag is removed.
     *
     * @param mixed $css Value entered by the merchant
     *
     * @return string
     */
    public function sanitizeExtraCss($css)
    {
        if (!is_string($css) || trim($css) === '') {
            return '';
        }

        $css = strip_tags($css);
        $css = preg_replace('#/\*.*?\*/#s', '', $css);
        $css = str_replace(['{', '}', '<', '>', '\\'], '', $css);
        $css = preg_replace('/expression\s*\(|javascript\s*:|@import/i', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);

        return trim($css, " ;\t\n\r");
    }

    /**
     * Returns a valid CSS margin, or empty string. A plain number is taken as
     * pixels, and negative values are allowed, e.g. to move the legal note
     * closer to the payment option name.
     *
     * @param mixed $value Value entered by the merchant
     *
     * @return string
     */
    public function sanitizeMargin($value)
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';
        $value = str_replace(',', '.', $value);

        if (!preg_match('/^(-?)(\d+(?:\.\d+)?|\.\d+)\s*(px|em|rem|pt|%)?$/', $value, $matches)) {
            return '';
        }
        if ((float) $matches[2] == 0) {
            return '0px';
        }

        return $matches[1] . $matches[2] . (empty($matches[3]) ? 'px' : $matches[3]);
    }

    /**
     * Returns the margin stored in one of the margin settings, or its default.
     *
     * @param string $setting Config::LEGAL_NOTE_MARGIN_TOP, _BOTTOM or _LEFT
     *
     * @return string
     */
    public function getMargin($setting)
    {
        $margin = $this->sanitizeMargin(\Configuration::get($setting));

        return $margin !== '' ? $margin : $this->getDefaultMargin($setting);
    }

    /**
     * Returns the default of one of the margin settings.
     *
     * @param string $setting Config::LEGAL_NOTE_MARGIN_TOP, _BOTTOM or _LEFT
     *
     * @return string
     */
    public function getDefaultMargin($setting)
    {
        $defaults = [
            Config::LEGAL_NOTE_MARGIN_TOP => self::DEFAULT_MARGIN_TOP,
            Config::LEGAL_NOTE_MARGIN_BOTTOM => self::DEFAULT_MARGIN_BOTTOM,
            Config::LEGAL_NOTE_MARGIN_LEFT => self::DEFAULT_MARGIN_LEFT,
        ];

        return isset($defaults[$setting]) ? $defaults[$setting] : '0px';
    }

    /**
     * Returns the default legal note in each language of the language
     * setting, keyed like its options.
     *
     * @return array
     */
    public function getDefaultTexts()
    {
        $texts = [];
        foreach ($this->getLanguages() as $language) {
            $texts[$language['id']] = strip_tags($this->getDefaultText($language['id']));
        }

        return $texts;
    }

    /**
     * Returns the inline style of the legal note, built from the styling
     * settings, e.g. "font-weight:600;color:#cc0000". It is not escaped.
     *
     * @return string
     */
    public function getStyle()
    {
        $declarations = [];

        $fontSize = $this->sanitizeFontSize(\Configuration::get(Config::LEGAL_NOTE_FONT_SIZE));
        if ($fontSize !== '') {
            $declarations[] = 'font-size:' . $fontSize;
        }

        // Shops that installed the module before the styling settings existed
        // have no value yet, so they get the default font style.
        $fontStyle = (string) \Configuration::get(Config::LEGAL_NOTE_FONT_STYLE);
        if (!in_array($fontStyle, ['normal', 'bold', 'italic', 'bold-italic'], true)) {
            $fontStyle = self::DEFAULT_FONT_STYLE;
        }
        $declarations[] = 'font-weight:' . (strpos($fontStyle, 'bold') !== false ? '600' : 'normal');
        $declarations[] = 'font-style:' . (strpos($fontStyle, 'italic') !== false ? 'italic' : 'normal');

        $color = $this->sanitizeColor(\Configuration::get(Config::LEGAL_NOTE_COLOR));
        if ($color !== '') {
            $declarations[] = 'color:' . $color;
        }

        $align = (string) \Configuration::get(Config::LEGAL_NOTE_ALIGN);
        if (in_array($align, ['left', 'center', 'right'], true)) {
            $declarations[] = 'text-align:' . $align;
        }

        $declarations[] = 'margin-top:' . $this->getMargin(Config::LEGAL_NOTE_MARGIN_TOP);
        $declarations[] = 'margin-bottom:' . $this->getMargin(Config::LEGAL_NOTE_MARGIN_BOTTOM);
        $declarations[] = 'margin-left:' . $this->getMargin(Config::LEGAL_NOTE_MARGIN_LEFT);

        // The extra CSS comes last, so it can override the controls above.
        $extraCss = $this->sanitizeExtraCss(\Configuration::get(Config::LEGAL_NOTE_EXTRA_CSS));
        if ($extraCss !== '') {
            $declarations[] = $extraCss;
        }

        return implode(';', $declarations);
    }

    /**
     * Returns the default legal note in the given language.
     *
     * @param string $language 'auto' (the current language), 'en', 'da' or 'es'
     *
     * @return string
     */
    public function getDefaultText($language = 'auto')
    {
        if ($language === 'en') {
            return self::DEFAULT_TEXT;
        }

        if (isset(self::TRANSLATION_FILES[$language])) {
            return $this->translateFromFile(self::TRANSLATION_FILES[$language], self::DEFAULT_TEXT);
        }

        return $this->module->l('Warning! Borrow responsibly - it costs money.', self::FILENAME);
    }

    /**
     * Returns the legal note to show at the checkout: the default text in the
     * language of the language setting ("auto" uses the current language).
     *
     * @param \Language $language Unused, the current language is taken from the context
     *
     * @return string Safe HTML.
     */
    public function getText(\Language $language)
    {
        return $this->sanitize($this->getDefaultText((string) \Configuration::get(Config::LEGAL_NOTE_LANGUAGE)));
    }

    /**
     * Returns the translation of a text from one of the module's translation
     * files, or the text itself if it is not translated there. The file is
     * read directly, so the language does not have to be installed in the shop.
     *
     * @param string $file Translation file name without extension, e.g. 'da'
     * @param string $text
     *
     * @return string
     */
    private function translateFromFile($file, $text)
    {
        if (!isset($this->translations[$file])) {
            $this->translations[$file] = [];
            $path = $this->module->getLocalPath() . 'translations/' . $file . '.php';

            if (is_file($path)) {
                // The file fills the global $_MODULE array, which PrestaShop
                // also uses for its own translations, so restore it afterwards.
                global $_MODULE;
                $backup = $_MODULE;
                include $path;
                $this->translations[$file] = is_array($_MODULE) ? $_MODULE : [];
                $_MODULE = $backup;
            }
        }

        // Same key as PrestaShop uses for $this->module->l($text, self::FILENAME).
        $key = '<{' . $this->module->name . '}prestashop>' . strtolower(self::FILENAME) . '_' . md5($text);

        return isset($this->translations[$file][$key]) ? (string) $this->translations[$file][$key] : $text;
    }

    /**
     * Returns the text if it is clean HTML, otherwise the escaped text without
     * tags.
     *
     * @param string $text
     *
     * @return string
     */
    private function sanitize($text)
    {
        if (\Validate::isCleanHtml($text)) {
            return $text;
        }

        return htmlspecialchars(strip_tags($text), ENT_QUOTES, 'UTF-8', false);
    }
}
