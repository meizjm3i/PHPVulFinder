<?php
/**
 * Created by PhpStorm.
 * User: meizj
 * Date: 2019/10/23
 * Time: 11:22 AM
 */

require  'global.php';



function load_file($name,$ver){
    if($ver = '5'){
        $parser = new PhpParser\Parser\Php5(new PhpParser\Lexer\Emulative()) ;
    }elseif ($ver = '7'){
        $parser = new PhpParser\Parser\Php7(new PhpParser\Lexer\Emulative()) ;
    }

    $code = file_get_contents($name);
    $parseResult = $parser->parse($code);
    $NodeInitVisitor = new NodeInitVisitor;
    $traverser = new PhpParser\NodeTraverser();
    $traverser->addVisitor($NodeInitVisitor);
    $traverser->traverse($parseResult);
    $nodes  = $NodeInitVisitor->getNodes();
    $ast2ir = new AST2IR();
    $resultSet = $ast2ir->parse($nodes);

    return parseResult($resultSet);

}

function parseResult($resultSet){
    $count = array();
    $info = array();
    $html ="
  Highcharts.chart('container', {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: 'pie'
  },
  title: {
    text: '漏洞信息统计'
  },
  tooltip: {
    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
  },
  accessibility: {
    point: {
      valueSuffix: '%'
    }
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: true,
        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
      }
    }
  },
  series: [{
    name: 'Brands',
    colorByPoint: true,
    data: [
";
    $num = 0;
    $code = file_get_contents($_POST['path']);
    $code = explode("\n",$code);
//    var_dump($code);
    foreach ($resultSet as $result){
        if(!array_key_exists($result->type,$count)){
            $count[$result->type] = 1;
        }else{
            $count[$result->type] += 1;
        }
        $info[$num]["type"] = $result->type;
        $info[$num]["name"] = $result->name;
        $info[$num]["path"] = $_POST['path'];
        $info[$num]['code'] = $code[$result->linenum - 2]."\n".$code[$result->linenum - 1]."\n".$code[$result->linenum];
        $info[$num]["linenum"] = $result->linenum;
        $num += 1;
    }
//    var_dump($info);
    foreach ($count as $k=>$v){
        $html = $html."{name:'".$k."',";
        $html = $html."y:".$v/$num."},";

    }
    $html = $html."]}]});------9ff9d6c39f612864ea4f5739be015be8------";
    $html = $html.'<table class="table">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Type</th>
      <th scope="col">Function Name</th>
      <th scope="col">File Path</th>
      <th scope="col">Line Number</th>
      <th scope="col">Detail</th>
    </tr>
  </thead>
  <tbody>';
    foreach ($info as $k=>$v){
        $k += 1;
        $html = $html."<tr>\n";
        $html = $html."<th scope=\"row\">$k</th>\n";
        $html = $html."<td>".$v["type"]."</td>\n";
        $html = $html."<td>".$v["name"]."</td>\n";
        $html = $html."<td>".$v["path"]."</td>\n";
        $html = $html."<td>".$v["linenum"]."</td>\n";

        $v["name"] = str_ireplace("\$","",$v["name"]);
        $html = $html."<td>
        <button type=\"button\" class=\"btn btn-primary\" data-toggle=\"modal\" data-target=\"#".$k."_".$v["name"]."\">
            Details
        </button></td>\n";

        $html = $html."<div class=\"modal fade\" id=\"".$k."_".$v["name"]."\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLongTitle\" aria-hidden=\"true\">
  <div class=\"modal-dialog\" role=\"document\">
    <div class=\"modal-content\">
      <div class=\"modal-header\">
        <h5 class=\"modal-title\" id=\"exampleModalLongTitle\">Code Detail</h5>
        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
          <span aria-hidden=\"true\">&times;</span>
        </button>
      </div>
      <div class=\"modal-body\"><pre><code>".$v["code"]."</code></pre></div>
    </div>
  </div>
</div>";
        $html = $html."</tr>\n";
    }
    $html = $html."</tbody></table>";
    return $html;
}
$Name = $_POST['path'];

if(is_dir($Name)){
    exit("Not yet.");
}elseif (is_file($Name)){
    echo load_file($Name,5);
}