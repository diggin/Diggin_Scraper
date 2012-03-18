Diggin_Scraper
===============
web-sraping component, inspired by Perl’s Web::Scraper. It provides a DSL-ish interface for traversing HTML documents and returning a neatly arranged PHP's multidimensional array.

# Feature
- into multidimensional array
- Handle CSS Selector or XPath expression
- Automatically convert to UTF-8
 - based on Diggin_Http_Charset
- Beautify ugly HTML into XHTML automatically
 - based on Diggin_Scraper_Adapter_Htmlscraping & tidy
- convert relative path into absolute URL automatically ("a href" & "img src") 
- Enable change Strategy  (xpath or regex) & Enable change pretreat converting HTML

## Requirements
- PHP 5.3.3 or over
- Zend Framework 2
- Diggin components
 - Diggin_Http_Charset
 - Diggin_Scraper_Adapter_Htmlscraping
