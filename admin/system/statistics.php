<?php
/**
 * Google analytics interface
 */
set_time_limit(0);
require_once('../inc/analytics_api.php');
require_once('../conf/conf.inc.php');
function data2table($data, $iid, $name)
{
?>
<h2><?=$name?></h2>
<table class="table table-striped table-bordered table-condensed">
        <thead>
          <tr>
            <th><?php echo $iid?></th>
            <th><?=t("Ziyaretçi")?></th>
            <th><?=t("Sayfa Gösterimi")?></th>
          </tr>
        </thead>
        <tbody>
<?php							
		foreach($data as $item=>$m)
		{
			echo "<tr><td>$item</td><td>$m[visit]</td><td>$m[pageviews]</td></tr>\n";
		}
	?>
	 </tbody></table>
<?php
}
$ttype=(isset($_GET['type'])) ? $_GET['type'] : 'day';
$ltype=$ttype=="month"?"year":"week";

if (!isset($_GET['between'])) $_GET['between'] = date("d.m.Y",strtotime("last $ltype"));
if (!isset($_GET['and'])) $_GET['and'] = date("d.m.Y");
$b=explode('.',$_GET['between']);
$a=explode('.',$_GET['and']);


$b=strtotime($b[1].'/'.$b[0].'/'.$b[2]);
$a=strtotime($a[1].'/'.$a[0].'/'.$a[2]);


if (($a-$b)/86400>31 && !isset($_GET['type']))
{
	$ttype='month';
}

$api = new analytics_api();
if($api->login("info@example.com", "123456")) {
							$id= $site["analytics"];
							$datax=array();
							$datasehir=array();
							$datakeyword=array();
							$datapage=array();
							while($b<$a)
							{
								$visits=0;
								$pageviews=0;
								$___data = $api->get_summary($id,date('Y-m-d',$b),date('Y-m-d',strtotime('next '.$ttype,$b)));
								foreach($___data as $page=>$__data)
								{
									$pvisit=0;
									$ppv=0;
									$page=($page=='(not set)' || $page=='(not provided)') ? '(Diğer)' :$page;
									if (!isset($datapage[$page]))
									{
										$datapage[$page]=array('visit'=>0,'pageviews'=>0);
									}
										foreach($__data as $keyword=>$_data)
										{
											$kvisit=0;
											$kpv=0;
											$keyword=($keyword=='(not set)' || $keyword=='(not provided)') ? '(Diğer)' :$keyword;
											if (!isset($datakeyword[$keyword]))
											{
												$datakeyword[$keyword]=array('visit'=>0,'pageviews'=>0);
											}
											foreach($_data as $sehir=>$data)
											{
												$sehir=($sehir=='(not set)') ? '(Diğer)' :$sehir;
												if (!isset($datasehir[$sehir]))
												{
													$datasehir[$sehir]=array('visit'=>0, 'pageviews'=>0);
												}
												$kvisit+=$data['ga:visits'];
												$kpv+=$data['ga:pageviews'];
												$pvisit+=$data['ga:visits'];
												$ppv+=$data['ga:pageviews'];
												$datasehir[$sehir]['visit']+=$data['ga:visits'];
												$datasehir[$sehir]['pageviews']+=$data['ga:pageviews'];
												$visits+=$data['ga:visits'];
												$pageviews+=$data['ga:pageviews'];
											}
											$datakeyword[$keyword]['visit']+=$kvisit;
											$datakeyword[$keyword]['pageviews']+=$kpv;
										}
									$datapage[$page]['visit']+=$pvisit;
									$datapage[$page]['pageviews']+=$ppv;
								}
								$datax[]='[new Date('.date('Y,n,j',$b).'), '.$visits.', '.$pageviews.']';
								$b=strtotime('+1 '.$ttype, $b);
							}
							?>
							
<script type="text/javascript">
									function drawChart() {
									var data = new google.visualization.DataTable();
										data.addColumn('date', '<?=t("Tarih")?>');
										data.addColumn('number', '<?=t("Ziyaret")?>');
										data.addColumn('number','<?=t("Sayfa Görüntüleme")?>');
										data.addRows([
										  <?php echo implode(',',$datax)?>
										]);
                                       var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                                       var formatter = new google.visualization.DateFormat({pattern: "MMM yyyy"});
                                       <?php
                                       	if ($ttype=='month') echo 'formatter.format(data, 0);';
                                       ?>
                                       chart.draw(data, {displayAnnotations: true});
                                       var dates = $( "#from, #to" ).datepicker({
											minDate: "-3Y", 
											maxDate: "1D",
											changeMonth: true,
											onSelect: function( selectedDate ) {
												var option = this.id == "from" ? "minDate" : "maxDate",
													instance = $( this ).data( "datepicker" ),
													date = $.datepicker.parseDate(
														instance.settings.dateFormat ||
														$.datepicker._defaults.dateFormat,
														selectedDate, instance.settings );
												dates.not( this ).datepicker( "option", option, date );
											}
										});
									  }
									</script>
									<div class='alert' style='position: relative; z-index: 1; margin: 0 0 -40px; text-align: right;'>
										<b><?=t("Zaman Aralığı:")?> </b> 
										<input id='from' class='input-small' value='<?=$_GET['between']?>' style='margin-bottom:3px'/> - 
										<input id='to' class='input-small' value='<?=$_GET['and']?>' style='margin-bottom:3px'/> 
										<button class='btn btn-warning' style='margin-bottom:3px' onclick='loadAnalytics($("#from").val(),$("#to").val());'><?=t("Yükle")?></button></div> 
									<div id="chart_div" style="width: 100%; height: 400px;"></div>
									<?php
									data2table($datapage,t('Sayfa'),t('Sayfa Gösterimi İstatistikleri'));
									data2table($datasehir,t('Şehir'),t('Ziyaretçi Gönderen Şehirler'));
									data2table($datakeyword,t('Anahtar Kelime'),t('Arama Motorlarında Arama Yaparak Gelenler'));
	}
?>
