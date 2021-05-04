<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

use ViaBill\Config\Config;
use ViaBill\Util\DebugLog;
use ViaBill\Builder\Template\TagBodyTemplate;

require_once dirname(__FILE__).'/../../vendor/autoload.php';

/**
 * ViaBill Troubleshoot Controller Class.
 *
 * Class AdminViaBillTroubleshootController
 */
class AdminViaBillTroubleshootController extends ModuleAdminController
{

    /**
     * Module Main Class Variable Declaration.
     *
     * @var ViaBill
     */
    public $module;

    public $template_files = null;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();
    }

    /**
     * Calls Redirect To Login Method.
     */
    public function init()
    {
        $this->redirectToLogin();
        parent::init();
    }

    /**
     * Redirects To Authentication Tab If User Is Not Loggen In.
     *
     * @throws \Exception
     */
    private function redirectToLogin()
    {
        /**
         * @var Config $config
         */
        $config = $this->module->getModuleContainer()->get('config');

        if ($config->isLoggedIn()) {
            return;
        }

        /**
         * @var Tab $tab
         */
        $tab = $this->module->getModuleContainer()->get('tab');

        \Tools::redirectAdmin($this->context->link->getAdminLink(
            $tab->getControllerAuthenticationName()
        ));
    }

    public function initContent()
    {
        $html = $this->getTroubleshootInfo();

        $this->content = $html;

        return parent::initContent();
    }
    
    public function getTroubleshootInfo()
    {
        $theme_dir = _PS_THEME_DIR_;

        $this->scanDirForTemplates($theme_dir);

        $theme_info = $this->getThemeInfo();
        $price_tags_info = $this->getPriceTagsParams();
        $product_price_tags_info = $this->getProductInfo();
        $cart_price_tags_info = $this->getCartInfo();
        $checkout_price_tags_info = $this->getCheckoutInfo();
        
        $html = '
        <div class="container" style="margin-top: 20px;margin-bottom: 20px;">'.
            '<h3>Price Tags Information</h3>'.
            '<blockquote class="blockquote bg-primary">'.
            'Below you will find some helpful information with regard to the ViaBill'.
            ' price tags and how to resolve some common issues.'.
            '</blockquote>';
        $html .= $theme_info;
        $html .= $price_tags_info;
        $html .= $product_price_tags_info;
        $html .= $cart_price_tags_info;
        $html .= $checkout_price_tags_info;
        $html .= '</div>';

        return $html;
    }

    private function getThemeInfo()
    {
        $theme_dir = _PS_THEME_DIR_;
        $theme_info = sprintf(
            'Your active theme is under the folder <strong>%s</strong>',
            realpath($theme_dir)
        );
        $theme_info = '<div class="alert alert-info" role="alert">
            <strong>Theme Info</strong><br/>
            '.$theme_info.'
        </div>';

        return $theme_info;
    }

    private function getProductInfo()
    {
        $theme_dir = _PS_THEME_DIR_;
        $module_path = $this->module->getLocalPath();

        $product_handler_js = realpath($module_path.'/views/js/front/product_update_handler.js');
        $product_page_query_selector = $this->findProductPageQuerySelector($product_handler_js);
        if (empty($product_page_query_selector)) {
            // no query selector found in module javascript file - this should not happen
            $div_class = 'danger';
            $product_price_tags_info = sprintf(
                'Price Tags for the <em>Product</em> page will be '.
                'inserted after the HTML element with an unknown jQuery selector. '.
                'Please examine the Javascript file <strong>%s</strong> to identify '.
                'the jQuery selector right at the $(\'query-selector\').after(priceTagScriptHolder)',
                $product_handler_js
            );
        } else {
            $product_price_var = '{$product.price}';
            $result = $this->findPriceTagsTemplates(
                'product',
                $product_page_query_selector,
                $product_price_var
            );
            if (empty($result)) {
                $product_price_var = '$product.price';
                $result = $this->findPriceTagsTemplates(
                    'product',
                    $product_page_query_selector,
                    $product_price_var
                );
            }
            if (empty($result)) {
                // could not locate the template that contains the product price
                $div_class = 'danger';
                $product_price_tags_info = sprintf('Price Tags for the <em>Product</em> page '.
                     'will be inserted after the HTML element with jQuery selector '.
                     '<strong>%s</strong>. We could not locate the template file responsible for '.
                      'rendering this HTML element.', $product_page_query_selector);
            } else {
                if (isset($result['pricetags_selector'])) {
                    $div_class = 'success';
                    $product_price_tags_info = sprintf(
                        "Price Tags for the <em>Product</em> page ".
                        "will be inserted ".
                        "after the HTML element with jQuery selector <strong>%s</strong> ".
                        "found in template file:<br/><strong>%s</strong>.<br/>".
                        "If you want to update the selector, you should edit the following ".
                        "Javascript file:<br/><strong>%s</strong>",
                        $product_page_query_selector,
                        realpath($result['pricetags_template']),
                        $product_handler_js
                    );
                } elseif (isset($result['pricetags_template'])) {
                    $div_class = 'danger';
                    $product_template_list = (is_array($result['pricetags_template']))?'<ul><li>'.
                        implode('</li><li> ', $result['pricetags_template']).'</li><ul>':
                        realpath($result['pricetags_template']);
                    $product_price_tags_info = sprintf(
                        "Price Tags for the <em>Product</em> page ".
                        "will be inserted ".
                        "after the HTML element with jQuery selector <strong>%s</strong> ".
                        "that we could not found in template file(s):<br/><strong>%s</strong>.".
                        "In order to resolve this you can:<ul>",
                        "<li>Edit the javascript file <strong>%s</strong> and change the jQuery ".
                        'selector around line <em>$(\'your-query-selector\').after(priceTagScriptHolder)'.
                        "</em></li>".
                        "<li>Edit the template file and either insert a new HTML element
                         with the proper selector,
                         or update an existing HTML element with the proper selector</li></ul>",
                        $product_page_query_selector,
                        $product_template_list,
                        $product_handler_js
                    );
                } else {
                    $div_class = 'danger';
                    $product_price_tags_info = sprintf(
                        "Price Tags for the <em>Product</em> page
                         will be inserted ".
                        "after the HTML element with jQuery selector <strong>%s</strong>.
                         We could not locate the template file responsible for rendering
                          this HTML element.",
                        $product_page_query_selector
                    );
                }
            }
        }
        $product_price_tags_info = '
        <div class="alert alert-'.$div_class.'" role="alert">
            <strong>Product Page Price Tags</strong><br/>
            '.$product_price_tags_info.'
        </div>';

        if (!empty($product_page_query_selector)) {
            $query_selector = str_replace(array('.','#',' '), '', $product_page_query_selector);

            if (!empty($product_handler_js)) {
                $filepath = $product_handler_js;
                $search_term = $product_page_query_selector;
                $product_price_tags_info .= $this->getCodeSnippet($filepath, $search_term);
            }

            if (!empty($result) && isset($result['pricetags_template'])) {
                if (!empty($result['pricetags_template'])) {
                    $filepath = realpath($result['pricetags_template']);
                    $search_term = $query_selector;
                    $product_price_tags_info .= $this->getCodeSnippet($filepath, $search_term);
                }
            }
        }
        
        return $product_price_tags_info;
    }

    private function findProductPageQuerySelector($product_handler_js)
    {
        # sanity check
        if (!file_exists($product_handler_js)) {
            return false;
        }

        $query_selector = '.product-prices';  // default value

        $contents = Tools::file_get_contents($product_handler_js);
        if (strpos($contents, $query_selector)!==false) {
            return $query_selector;
        }

        $query_selector = '';

        // pass #1
        $pos = strpos($contents, 'after(priceTagScriptHolder)');
        if ($pos !== false) {
            // locate the important part
            $search_length = 120;
            if ($pos > $search_length) {
                $search_str = Tools::substr($contents, $pos - $search_length, $search_length);
            } else {
                $search_str = $contents;
            }
            $start_pos = null;
            $ppos = strpos($search_str, '(');
            while ($ppos !== false) {
                $start_pos = $ppos;
                $offset = $ppos + 1;
                $ppos = strpos($search_str, '(', $offset);
            }
            $end_pos = null;
            $ppos = strpos($search_str, ')');
            while ($ppos !== false) {
                $end_pos = $ppos;
                $offset = $ppos + 1;
                $ppos = strpos($search_str, ')', $offset);
            }
            if (isset($start_pos)&&isset($end_pos)) {
                $q_selector = Tools::substr($search_str, $start_pos + 1, ($end_pos - $start_pos - 1));
                $query_selector = trim(str_replace(array('"',"'",' '), '', $q_selector));
            }
        }

        // pass #2
        if (empty($query_selector)) {
            $pattern = '#\((.+)\)\.after\s*\(\s*priceTagScriptHolder#i';
            if (preg_match($pattern, $contents, $matches)) {
                $query_selector = trim(str_replace(array('"',"'",' '), '', $matches[1]));
            }
        }

        return $query_selector;
    }

    private function getCartInfo()
    {
        $theme_dir = _PS_THEME_DIR_;
        $module_path = $this->module->getLocalPath();
                
        $cart_handler_js = realpath($module_path.'/views/js/front/cart_update_handler.js');
        $cart_page_query_selector = $this->findCartPageQuerySelector($cart_handler_js);
        if (empty($cart_page_query_selector)) {
            // no query selector found in module javascript file - this should not happen
            $div_class = 'danger';
            $cart_price_tags_info = sprintf(
                "Price Tags for the <em>Cart</em> page will be inserted ".
                "after the HTML element with an unknown jQuery selector. 
                Please examine the Javascript file <strong>%s</strong> to identify ".
                "the jQuery selector right at the $('query-selector').after(priceTagScriptHolder)",
                $cart_handler_js
            );
        } else {
            $cart_price_var = null;
            $result = $this->findPriceTagsTemplates(
                'cart',
                $cart_page_query_selector,
                $cart_price_var
            );
            if (empty($result)) {
                // could not locate the template that contains the cart price
                $div_class = 'danger';
                $cart_price_tags_info = sprintf("Price Tags for the <em>Cart</em> page
                 will be inserted ".
                    "after the HTML element with jQuery selector <strong>%s</strong>.
                     We could not locate the template file responsible for rendering 
                     this HTML element.", $cart_page_query_selector);
            } else {
                if (isset($result['pricetags_selector'])) {
                    $div_class = 'success';
                    $cart_price_tags_info = sprintf(
                        "Price Tags for the <em>Cart</em> page
                         will be inserted ".
                        "after the HTML element with jQuery selector <strong>%s</strong> ".
                        "found in template file:<br/><strong>%s</strong>.<br/>".
                        "If you want to update the selector, you should edit the following
                         Javascript file:
                        <br/><strong>%s</strong>",
                        $cart_page_query_selector,
                        realpath($result['pricetags_template']),
                        $cart_handler_js
                    );
                } elseif (isset($result['pricetags_template'])) {
                    $div_class = 'danger';
                    $cart_template_list = (is_array($result['pricetags_template']))?'<ul><li>'.
                        implode('</li><li> ', $result['pricetags_template']).'</li><ul>':
                        realpath($result['pricetags_template']);
                    $cart_price_tags_info = sprintf(
                        "Price Tags for the <em>Cart</em>
                         page will be inserted ".
                        "after the HTML element with jQuery selector <strong>%s</strong> ".
                        "that we could not found in template file(s):<br/><strong>%s</strong>.".
                        "In order to resolve this you can:<ul>",
                        "<li>Edit the javascript file <strong>%s</strong> and change the jQuery
                         selector around line
                         <em>$('your-query-selector').after(priceTagScriptHolder)</em></li>".
                        "<li>Edit the template file and either insert a new HTML element with
                         the proper selector,
                         or update an existing HTML element with the proper selector</li></ul>",
                        $cart_page_query_selector,
                        $cart_template_list,
                        $cart_handler_js
                    );
                } else {
                    $div_class = 'danger';
                    $cart_price_tags_info = sprintf(
                        "Price Tags for the <em>Cart</em> page
                         will be inserted ".
                        "after the HTML element with jQuery selector <strong>%s</strong>.
                         We could not locate the template file responsible for rendering
                         this HTML element.",
                        $cart_page_query_selector
                    );
                }
            }
        }

        $cart_price_tags_info = '
        <div class="alert alert-'.$div_class.'" role="alert">
            <strong>Cart Page Price Tags</strong><br/>
            '.$cart_price_tags_info.'
        </div>';
        
        if (!empty($cart_page_query_selector)) {
            $query_selector = str_replace(array('.','#',' '), '', $cart_page_query_selector);

            if (!empty($cart_handler_js)) {
                $filepath = $cart_handler_js;
                $search_term = $cart_page_query_selector;
                $cart_price_tags_info .= $this->getCodeSnippet($filepath, $search_term);
            }

            if (!empty($result) && isset($result['pricetags_template'])) {
                if (!empty($result['pricetags_template'])) {
                    $filepath = realpath($result['pricetags_template']);
                    $search_term = $query_selector;
                    $cart_price_tags_info .= $this->getCodeSnippet($filepath, $search_term);
                }
            }
        }
        
        return $cart_price_tags_info;
    }

    private function findCartPageQuerySelector($cart_handler_js)
    {
        # sanity check
        if (!file_exists($cart_handler_js)) {
            return false;
        }

        $query_selector = '.cart-detailed-totals';  // default value

        $contents = Tools::file_get_contents($cart_handler_js);
        if (strpos($contents, $query_selector)!==false) {
            return $query_selector;
        }

        $query_selector = '';

        // pass #1
        $pos = strpos($contents, 'after(priceTagCartBodyHolder)');
        if ($pos !== false) {
            // locate the important part
            $search_length = 120;
            if ($pos > $search_length) {
                $search_str = Tools::substr($contents, $pos - $search_length, $search_length);
            } else {
                $search_str = $contents;
            }
            $start_pos = null;
            $ppos = strpos($search_str, '(');
            while ($ppos !== false) {
                $start_pos = $ppos;
                $offset = $ppos + 1;
                $ppos = strpos($search_str, '(', $offset);
            }
            $end_pos = null;
            $ppos = strpos($search_str, ')');
            while ($ppos !== false) {
                $end_pos = $ppos;
                $offset = $ppos + 1;
                $ppos = strpos($search_str, ')', $offset);
            }
            if (isset($start_pos)&&isset($end_pos)) {
                $q_selector = Tools::substr($search_str, $start_pos + 1, ($end_pos - $start_pos - 1));
                $query_selector = trim(str_replace(array('"',"'",' '), '', $q_selector));
            }
        }

        // pass #2
        if (empty($query_selector)) {
            $pattern = '#\((.+)\)\.after\s*\(\s*priceTagCartBodyHolder#i';
            if (preg_match($pattern, $contents, $matches)) {
                $query_selector = trim(str_replace(array('"',"'",' '), '', $matches[1]));
            }
        }

        return $query_selector;
    }

    private function getCheckoutInfo()
    {
        $module_path = $this->module->getLocalPath();
                
        $checkout_handler_js = realpath($module_path.'/views/js/front/payment_option.js');
        
        $checkout_price_tags_info = sprintf(
            'Price Tags for the <em>Checkout</em>
            page will be inserted '.
            'using the Prestashop API, so you cannot control the exact position.
             The Viabill Payment logo (viabill.png) is styled by Javascript code found in
              <strong>%s</strong>',
            $checkout_handler_js
        );

        $checkout_price_tags_info = '
        <div class="alert alert-success" role="alert">
            <strong>Checkout Page Price Tags</strong><br/>
            '.$checkout_price_tags_info.'
        </div>';
        
        return $checkout_price_tags_info;
    }
    
    private function findPriceTagsTemplates($prefix, $raw_query_selector, $price_var)
    {
        $result = array(
            'pricetags_template' => null,
            'pricetags_selector' => null
        );

        // clean selector from . or #
        $query_selector = trim(str_replace(array('.','#','>'), '', $raw_query_selector));
        $parts = explode(' ', $query_selector);
        if (count($parts)>1) {
            $query_selector = $parts[count($parts)-1];
        }

        if (!empty($this->template_files)) {
            $query_found = 0;
            foreach ($this->template_files as $template_file) {
                if ($template_file[$prefix.'_pattern']) {
                    $contents = Tools::file_get_contents($template_file['fullpath']);
                    if (strpos($contents, $query_selector)!==false) {
                        if (isset($price_var)) {
                            if (strpos($contents, $price_var)!==false) {
                                $result['pricetags_template'] = $template_file['fullpath'];
                                $result['pricetags_selector'] = $raw_query_selector;
                                $query_found = 1;
                                break;
                            }
                        } else {
                            $result['pricetags_template'] = $template_file['fullpath'];
                            $result['pricetags_selector'] = $raw_query_selector;
                            $query_found = 1;
                            break;
                        }
                    }
                }
            }
            if (!$query_found) {
                foreach ($this->template_files as $template_file) {
                    $contents = Tools::file_get_contents($template_file['fullpath']);
                    if (strpos($contents, $query_selector)!==false) {
                        if (isset($price_var)) {
                            if (strpos($contents, $price_var)!==false) {
                                $result['pricetags_template'] = $template_file['fullpath'];
                                $result['pricetags_selector'] = $raw_query_selector;
                                $query_found = 1;
                                break;
                            }
                        } else {
                            $result['pricetags_template'] = $template_file['fullpath'];
                            $result['pricetags_selector'] = $raw_query_selector;
                            $query_found = 1;
                            break;
                        }
                    }
                }
            }
        } else {
            // "No templates were found!";
            return false;
        }
        
        return $result;
    }

    private function scanDirForTemplates($dir)
    {
        $cdir = scandir($dir);

        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".",".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $this->scanDirForTemplates($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $fileName = $dir . DIRECTORY_SEPARATOR . $value;
                    
                    // Look for specific file name patterns
                    $product_pattern = false;
                    $product_match = false;
                    $checkout_pattern = false;
                    $checkout_match = false;
                    $cart_pattern = false;
                    $cart_match = false;
                    
                    if (strpos($value, 'product')!==false) {
                        $product_pattern = true;
                    }
                    if (strpos($value, 'cart')!==false) {
                        $cart_pattern = true;
                    }
                    if (strpos($value, 'checkout')!==false) {
                        $checkout_pattern = true;
                    }
                    
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    if ($ext == 'tpl') {
                        $this->template_files[] = array(
                            'filename' => $value,
                            'fullpath' => $fileName,
                            'product_pattern' => $product_pattern,
                            'product_match' => $product_match,
                            'checkout_pattern' => $checkout_pattern,
                            'checkout_match' => $checkout_match,
                            'cart_pattern' => $cart_pattern,
                            'cart_match' => $cart_match
                        );
                    }
                }
            }
        }
    }

    private function getCodeSnippet($filepath, $search_term)
    {
        $line_pos = null;
        $range = 1;
        $contents = Tools::file_get_contents($filepath);
        $lines = explode("\n", $contents);
        $num_of_lines = count($lines);
        foreach ($lines as $line_id => $line) {
            if (strpos($line, $search_term)!==false) {
                $line_pos = $line_id;
                break;
            }
        }

        // sanity check
        if (empty($line_pos)) {
            return '';
        }

        $line_from = $line_pos - $range;
        $line_to = $line_pos + $range;
        if ($line_from<0) {
            $line_from = 0;
        }
        if ($line_to>=$num_of_lines) {
            $line_to = $num_of_lines - 1;
        }

        $pathinfo = pathinfo($filepath);
        $html = '<div><em>'.$pathinfo['basename'].'</em><br/>';
        $html .= '<dl class="row border">';
        for ($i=$line_from; $i<=$line_to; $i++) {
            $line_val = trim($lines[$i]);
            if (empty($line_val)) {
                $line_val = '&nbsp;';
            } else {
                $line_val = htmlentities($line_val, ENT_QUOTES);
            }
            if ($i == $line_pos) {
                $html .= '<dt class="col-sm-3 bg-success">Line #'.($i+1).'</dt>';
                $html .= '<dd class="col-sm-9 bg-success">'.$line_val.'</dd>';
            } else {
                $html .= '<dt class="col-sm-3">Line #'.($i+1).'</dt>';
                $html .= '<dd class="col-sm-9">'.$line_val.'</dd>';
            }
        }
        $html .= '</dl>';
        $html .= '</div>';

        return $html;
    }

    private function getPriceTagsParams()
    {
        $params = array();

        $module_path = $this->module->getLocalPath();
                
        $params['Template file (HTML)'] =
            realpath($module_path.'/views/templates/front/price-tag.tpl');
        $params['CSS file'] = realpath($module_path.'/views/css/front/price-tag.css');
        
        $params['dataLanguageIso'] = Tools::strtoupper($this->context->language->iso_code);
        $params['dataCurrencyIso'] = Tools::strtoupper($this->context->currency->iso_code);
        $params['dataCountryCodeIso'] = Config::getCountryISOCodeByCurrencyISO(
            $this->context->currency->iso_code
        );

        $params['dynamicPriceTrigger'] = Config::DYNAMIC_PRICE_PRODUCT_TRIGGER;
        $params['dynamicPriceSelector'] = Config::DYNAMIC_PRICE_PRODUCT_SELECTOR;

        $params['Tags Script'] = Configuration::get(Config::API_TAGS_SCRIPT);

        $html = '<figure class="highlight">
        <strong>Price Tags Params</strong>
            <pre><code class="language-html" data-lang="html"><ul>';
        foreach ($params as $key => $value) {
            $html .= '<li>'.$key.':'.htmlentities($value, ENT_QUOTES).'</li>';
        }
        $html .= '</ul></code></pre></figure>';

        return $html;
    }
}
