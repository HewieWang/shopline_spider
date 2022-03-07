<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$url="https://priskur.xyz/products/cleaner-tools?sku=18053000207889292718923322";

$data=exec("caiji -u ".$url);
// file_put_contents("aa.json",$data);

$arr=json_decode($data,true);

function compress_html($string){
$string=str_replace("\r\n",'',$string);//清除换行符
$string=str_replace("\n",'',$string);//清除换行符
$string=str_replace("\t",'',$string);//清除制表符
$pattern=array(
"/> *([^ ]*) *</",//去掉注释标记
"/[\s]+/",
"/<!--[^!]*-->/",
"/\" /",
"/ \"/",
"'/\*[^*]*\*/'"
);
$replace=array (
">\\1<",
" ",
"",
"\"",
"\"",
""
);
return preg_replace($pattern, $replace, $string);
}
function get_html($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        array(
            'pragma: no-cache',
            'cache-control: no-cache',
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
            'accept: application/json, text/plain, */*',
            'content-type: application/json',
            'sec-ch-ua-mobile: ?0',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36',
            'sec-ch-ua-platform: "Windows"',
            'sec-fetch-site: same-origin',
            'sec-fetch-mode: cors',
            'sec-fetch-dest: empty',
            'accept-language: zh-CN,zh;q=0.9',
          ));
    // This is what solved the issue (Accepting gzip encoding)
    curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function Spider($url)
{
    $response = get_html($url);
    //进行XPath页面数据抽取
    $data    = []; //结构化数据存本数组
    $crawler = new Crawler();
    $crawler->addHtmlContent($response);
    try {
        return compress_html($crawler->filterXPath('//*[@id="shopline-section-product/detail/product-preview"]/div[2]/div[2]/div/div/div[2]/div/div/div')->html());
    } catch (\Exception $e) {
        var_dump($e);exit;
    }

}
$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setCellValueByColumnAndRow(1, 1, 'Title*');
$worksheet->setCellValueByColumnAndRow(2, 1, 'Subtitle');
$worksheet->setCellValueByColumnAndRow(3, 1, 'Product description html');
$worksheet->setCellValueByColumnAndRow(4, 1, 'Master image');
$worksheet->setCellValueByColumnAndRow(5, 1, 'SEO title');
$worksheet->setCellValueByColumnAndRow(6, 1, 'SEO description');
$worksheet->setCellValueByColumnAndRow(7, 1, 'Option1 name');
$worksheet->setCellValueByColumnAndRow(8, 1, 'Option1 value');
$worksheet->setCellValueByColumnAndRow(9, 1, 'Option2 name');
$worksheet->setCellValueByColumnAndRow(10, 1, 'Option2 value');
$worksheet->setCellValueByColumnAndRow(11, 1, 'Option3 name');
$worksheet->setCellValueByColumnAndRow(12, 1, 'Option3 value');
$worksheet->setCellValueByColumnAndRow(13, 1, 'SKU price');
$worksheet->setCellValueByColumnAndRow(14, 1, 'SKU compare at price');

$images=$arr['spu']['images'];
//sku
$sku_num=count($arr['sku']['skuAttributeMap']);
$r=2;
// 获取有多少种SKU组合
if(count($arr['sku']['skuList'])>0){
  foreach ($arr['sku']['skuList'] as $k => $v) {
    //SKU
    $stock=$v['stock'];
    $option_name1=@$arr['sku']['skuAttributeMap'][$v['skuAttributeIds'][0]['id']]['defaultName'];
    $option_value1=@$arr['sku']['skuAttributeMap'][$v['skuAttributeIds'][0]['id']]['skuAttributeValueMap'][$v['skuAttributeIds'][0]['valueId']]['defaultValue'];

    $option_name2=@$arr['sku']['skuAttributeMap'][$v['skuAttributeIds'][1]['id']]['defaultName'];
    $option_value2=@$arr['sku']['skuAttributeMap'][$v['skuAttributeIds'][1]['id']]['skuAttributeValueMap'][$v['skuAttributeIds'][1]['valueId']]['defaultValue'];

    $option_name3=@$arr['sku']['skuAttributeMap'][$v['skuAttributeIds'][2]['id']]['defaultName'];
    $option_value3=@$arr['sku']['skuAttributeMap'][$v['skuAttributeIds'][2]['id']]['skuAttributeValueMap'][$v['skuAttributeIds'][2]['valueId']]['defaultValue'];

    $worksheet->setCellValueByColumnAndRow(1, $r, $arr['spu']['title']);
    $worksheet->setCellValueByColumnAndRow(2, $r, $arr['spu']['subTitle']);
    $worksheet->setCellValueByColumnAndRow(3, $r, Spider($url));
    $worksheet->setCellValueByColumnAndRow(4, $r, $images[array_rand($images)]);
    $worksheet->setCellValueByColumnAndRow(5, $r, $arr['productSeo']['title']);
    $worksheet->setCellValueByColumnAndRow(6, $r, $arr['productSeo']['desc']);
    $worksheet->setCellValueByColumnAndRow(7, $r, $option_name1);
    $worksheet->setCellValueByColumnAndRow(8, $r, $option_value1);
    $worksheet->setCellValueByColumnAndRow(9, $r, $option_name2);
    $worksheet->setCellValueByColumnAndRow(10, $r, $option_value2);
    $worksheet->setCellValueByColumnAndRow(11, $r, $option_name3);
    $worksheet->setCellValueByColumnAndRow(12, $r, $option_value3);
    $worksheet->setCellValueByColumnAndRow(13, $r, $v['price']);
    $worksheet->setCellValueByColumnAndRow(14, $r, $v['originPrice']);
    $r++;
  }
}

$writer = new Xlsx($spreadsheet);
$writer->save('hello.xlsx');




 ?>
