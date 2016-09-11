<?php
	
	/**
	 * Google analytics screen
	 */
	Class Statistics {
		
		static public function start(){
		
			global $site;
		
			$out = "<h1 style='padding: 0 0 20px;'>".t("Site İstatistikleri")."</h1>";	
			
			if (!isset($site["analytics"]) || $site["analytics"]=="ga:")
				return "$out<div class='alert alert-error'>".t("Site istatistiklerini görebilmek için Google Analytics bilgileri gereklidir.")."</div>";
			else
				return $out.self::div();
		
		}
		
		static private function div(){
		
			return "
			<script type='text/javascript' src='https://www.google.com/jsapi'></script>
			<script type='text/javascript'>google.load('visualization', '1', {packages:['corechart']});
			</script>
			<div id='analytics_wrp'>
				<div id='analytics'><div class='alert alert-info'>
					<div  style='margin:200px auto; width:300px; text-align:center;'>".t("Site istatistikleri yükleniyor. Lütfen bekleyiniz.")."
						<div class='progress progress-striped progress-success active'><div class='bar' style='width:100%'></div></div>
					</div>
				</div></div>
			</div>";
		
		}
			
	}
