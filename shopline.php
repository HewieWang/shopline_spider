<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$url="https://priskur.xyz/products/cleaner-tools?sku=18053000207889292718923322";


$data=system("caiji -u ".$url);

// file_put_contents("aa.json",$data);

$arr=json_decode($data,true);

$title=$arr['spu']['title'];

$Subtitle=$arr['spu']['subTitle'];

$images=$arr['spu']['images'];

$seo_title=$arr['productSeo']['title'];
$seo_desc=$arr['productSeo']['desc'];

//sku
$sku_num=count($arr['sku']['skuAttributeMap']);

echo "共有".$sku_num."条option".PHP_EOL;
echo "分别为:".PHP_EOL;

function getsku($brr,$id){
  foreach ($brr as $m => $n) {
     foreach ($n as $key => $value) {
       if (is_array($value)) {
          foreach ($value as $q => $p) {
             // echo "valueID是".PHP_EOL;
            //  var_dump($p['valueId']);
             if($p['valueId']==$id){
                return $m;
             }
          }
       }

       // $x=array_search($key, array_column($value, 'valueId'));
       // return($x);
     }
  }
}

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
        $data['Product description html'] = compress_html($crawler->filterXPath('//*[@id="shopline-section-product/detail/product-preview"]/div[2]/div[2]/div/div/div[2]/div/div/div')->html());
    } catch (\Exception $e) {
        var_dump($e);exit;
    }

    return $data;

}

$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setCellValueByColumnAndRow(1, 1, 'Title*');
$worksheet->setCellValueByColumnAndRow(2, 1, 'Subtitle');
$worksheet->setCellValueByColumnAndRow(3, 1, 'Product description html');
$worksheet->setCellValueByColumnAndRow(4, 1, 'Master image');
$worksheet->setCellValueByColumnAndRow(5, 1, 'SEO title');
$worksheet->setCellValueByColumnAndRow(6, 1, 'SEO description');
// $worksheet->setCellValueByColumnAndRow(1, 7, 'SEO description');
// $worksheet->setCellValueByColumnAndRow(1, 8, 'SEO description');
// $worksheet->setCellValueByColumnAndRow(1, 9, 'SEO description');
// $worksheet->setCellValueByColumnAndRow(1, 10, 'SEO description');
// $worksheet->setCellValueByColumnAndRow(1, 11, 'SEO description');
// $worksheet->setCellValueByColumnAndRow(1, 12, 'SEO description');


$res=array();
$w=1;
if($sku_num>=1){
  foreach ($arr['sku']['skuAttributeMap'] as $k => $v) {
      echo $v['defaultName'].PHP_EOL;
      foreach ($v['skuAttributeValueMap'] as $key => $value) {
          echo $value['defaultValue'].PHP_EOL;
          echo $value['imgUrl'].PHP_EOL;
          echo $value['attributeValueWeight'].PHP_EOL;

          $m=getsku($arr['sku']['skuList'],$key);
          echo $arr['sku']['skuList'][$m]['price'].PHP_EOL;
          echo $arr['sku']['skuList'][$m]['originPrice'].PHP_EOL;
          echo $arr['sku']['skuList'][$m]['weight'].PHP_EOL;
          echo $arr['sku']['skuList'][$m]['weightUnit'].PHP_EOL;
          echo $arr['sku']['skuList'][$m]['stock'].PHP_EOL;

          // fputcsv($fp,array($title,$Subtitle,$seo_title,$seo_desc));
          // $res["option_name"][$w]
          // var_dump();
          // $x=array_search($key, array_column($arr['sku']['skuList'], 'valueId'));
          // var_dump($x);
      }
  }
}


$writer = new Xlsx($spreadsheet);
$writer->save('hello.xlsx');

 ?>
