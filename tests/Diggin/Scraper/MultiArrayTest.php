<?php

namespace DigginTest\Scraper;
use Diggin\Scraper\Scraper,
    Zend\Http\Client,
    Zend\Http\Client\Adapter\Test as ClientAdapterTest;

class MultiArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var    Diggin_Scraper
     * @access protected
     */
    protected $object;

    private $_responseHeader200 = "HTTP/1.1 200 OK\r\nContent-type: text/html";
    
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = new Scraper;
    }

    public function testKouzoutai()
    {
        $html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja" xml:lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>サッカーニュース</title>
<body>
<ul class="news">
  <li>
    <a href="http://sports.livedoor.com/article/vender-15.html">C・ロナウドが休日返上宣言！</a>
  </li>
  <li>
    <a href="/soccer/italia.cgi?k=v&key=var&amp;">イタリア代表のドナドーニ監督「アイルランドを甘く見てはいない」</a>
  </li>
  <li>
    <a>バルセロナが前回王者セビージャを下す＝スペイン国王杯</a>
  </li>
  <li>
    <a href="http://sportsnavi.yahoo.co.jp/soccer/index.html">ユベントス奮闘、５&#8722;３でエンポリを下す＝イタリア杯</a>
  </li>
</ul>
</body>
</html>
HTML;

$adapter = new ClientAdapterTest();
$adapter->setResponse(
    "HTTP/1.1 200 OK"        . "\r\n" .
    "Content-type: text/html" . "\r\n" .
                               "\r\n" .
    $html);
$test = new Client($url = 'http://www.yahoo.jp', array('adapter' => $adapter));


$scraper = new Scraper();
$scraper->setHttpClient($test);

$items = new Scraper();
//$items->process("a", "title => 'TEXT'", "link => '@href'");
$items->process("a", 'title','TEXT')
      ->process('a', 'link', '@href');

$results = $scraper->process("ul.news>li", array('result[]' => $items))
                   ->scrape("http://localhost/~tobe/news_sample.html");
        $this->assertTrue(is_array($results['result']));
        $this->assertTrue(isset($results['result']['0']['title']));
    }
}
