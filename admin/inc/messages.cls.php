<?php
	
	/**
	 * Messages section
	 */
	Class Messages {
		
		static public function start(){
			
			$id=@$_GET["id"];
			$type=@$_GET["type"];

			$out = "<style>body > .container-fluid {padding-bottom:0;}</style>
			<div id='messages' class='clearfix' data-id='$id' data-type='$type'>
				<ul class='userlist nav-collapse span4'>";
			$out .= "Konuşmalar yükleniyor.";
			$out .= "</ul><div class='message span8'>
			<div class='newtopbar'>
				<div class='newmes'><button class='btn pull-right'><i class='icon-comment'></i> Yeni Mesaj</button> <h3>Kullanıcı</h3> </div>
				<div class='oldmes'>Alıcı: <input id='messageto' type='text' placeholder='İsim'/> </div>
			</div>
			<div class='mwrap'>
			<div class='messagewrapper'>
			</div>
			</div>
			<div class='newmessage'><textarea disabled></textarea> <button class='btn btn' disabled>Gönder</button></div>
			</div></div>";
			return $out;
			
		}
					
	}
