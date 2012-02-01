<?php
/**
 * Diggin - Simplicity PHP Library
 * 
 * LICENSE
 *
 * This source file is subject to the new BSD license.
 * It is also available through the world-wide-web at this URL:
 * http://diggin.musicrider.com/LICENSE
 * 
 * @category   Diggin
 * @package    Diggin_Scraper
 * @copyright  2006-2011 sasezaki (http://diggin.musicrider.com)
 * @license    http://diggin.musicrider.com/LICENSE     New BSD License
 */

/**
 * @namespace
 */
namespace Diggin\Scraper\Adapter;

use Diggin\Scraper\Adapter\SimplexmlAdapter;

class Loadhtml implements SimplexmlAdapter
{
    protected $config = array(
        'xml_manifesto' => false,
        'auto_encoding' => true
    );
    
    /**
     * Casts a SimpleXMLElement
     * 
     * @param Zend_Htp_Response 
     * @return SimpleXMLElement
     */
    public function getSimplexml($response)
    {
        if ($this->config['auto_encoding']) {
            $responseBody = \Diggin\Http\Response\Encoding::encodeResponseObject($response);
        } else {
            $responseBody = $response->getBody();
        }

        $responseBody = str_replace('&', '&amp;', $responseBody);
        $dom = @\DOMDocument::loadHTML($responseBody);
        $simplexml = simplexml_import_dom($dom);
        
        /**
         * add xml manifest
         * @see http://goungoun.dip.jp/app/fswiki/wiki.cgi/devnotebook?
         * page=PHP5%A1%A2%CC%A4%C0%B0%B7%C1HTML%A4%F2SimpleXML%A4%D8%CA%D1%B4%B9
         */
        if ($this->config["xml_manifesto"] === true) {
            $str = $simplexml->asXML();
            {
                // XML宣言付与
                if (1 !== preg_match('/^<\\?xml version="1.0"/', $str)) {
                    $str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $str;
                }
                
                // HTML中の改行が数値文字参照になってしまったので、
                // 文字に戻す。
                $str = $this->_numentToChar($str);
            }
            $simplexml = simplexml_load_string($str);
        }
        
        return $simplexml;
    }
    
    /**
     * Set configuration parameters for this
     *
     * @param array $config
     * @return Diggin_Scraper_Adapter_Loadhtml
     * @throws Diggin_Scraper_Adapter_Exception
     */
    public function setConfig($config = array())
    {
        if (! is_array($config))
            throw new Exception('Expected array parameter, given ' . gettype($config));

        foreach ($config as $k => $v)
            $this->config[strtolower($k)] = $v;

        return $this;
    }
    
    /**
     * 数値文字参照を文字に戻す。
     *
     * 以下より
     * http://blog.koshigoe.jp/archives/2007/04/phpdomdocument.html
     * 
     * @param string $string
     * @return string
     */
    protected static function _numentToChar($string)
    {
        $excluded_hex = $string;
        if (preg_match("/&#[xX][0-9a-zA-Z]{2,8};/", $string)) {
            // 16 進数表現は 10 進数に変換
            $excluded_hex = preg_replace("/&#[xX]([0-9a-zA-Z]{2,8});/e",
                                         "'&#'.hexdec('$1').';'", $string);
        }
        return mb_decode_numericentity($excluded_hex,
                                       array(0x0, 0x10000, 0, 0xfffff),
                                       "UTF-8");
    }
    
    public function getConfig($key)
    {
        return $this->config[strtolower($key)];
    }
}

