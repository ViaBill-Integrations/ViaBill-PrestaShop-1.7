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
 * It is configured in the "Legal Note" section of the ViaBill settings: it can
 * be switched off, and the merchant can replace the default text with their
 * own text per shop language. The default text is translated in the module's
 * translation files and is shown either in the customer's language ("auto")
 * or in a fixed language.
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
     * Checks If The Legal Note Should Be Shown. It is shown unless the
     * merchant has switched it off.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $enabled = \Configuration::get(Config::LEGAL_NOTE_ENABLED);

        return $enabled === false || (bool) $enabled;
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
     * Returns the legal note to show at the checkout for the given language:
     * the merchant's own text if set, otherwise the default text. Returns
     * empty string if the legal note is switched off.
     *
     * @param \Language $language
     *
     * @return string Safe HTML.
     */
    public function getText(\Language $language)
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $text = trim((string) \Configuration::get(Config::LEGAL_NOTE_TEXT, (int) $language->id));

        if ($text === '') {
            $text = $this->getDefaultText((string) \Configuration::get(Config::LEGAL_NOTE_LANGUAGE));
        }

        return $this->sanitize($text);
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
