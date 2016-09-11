if ($("[name='0imap']").length)
	for (var i=0; i<16; i++)
		$(document).on("contentChanged","[name='"+i+"imap']",function(){
			var a = $(this).val();
			var b = a.split("||");
			var i = $(this).attr("name").replace("imap","");
			$("[name='"+i+"ilatitude']").val(b[0]);
			$("[name='"+i+"ilongitude']").val(b[1]);
		});


	function updateTimes() {
		$(".saatler").each(function(){
			var datas = [[],[],[],[],[],[],[]];
			$(this).find("input").each(function(){
				var day = $(this).data("day");
				var hour = $(this).data("hour");
				datas[day].push($(this).val());
			});
			$(this).parent().find("textarea").text(JSON.stringify(datas));
		});
	}

	var saatler = $("<div class='saatler' class='well clearfix'></div>");
	saatler.load("ext/map2heal/ajax.php?saatler",function(){
		for (var i=0; i<16;i++) {
			var ta = $("textarea[name="+i+"iopen_hours]").parent();
			ta.append(saatler.clone());
			var t = $("textarea[name="+i+"iopen_hours]").text();
			var datas = (t==="") ? [] : JSON.parse(t);
			ta.find("input").each(function(){
				var day = $(this).data("day");
				var hour = $(this).data("hour");
				if (typeof datas[day] !== "undefined")
					$(this).val(datas[day][hour]);
			});
		}
		$(".saatler .input-append").timepicker({ pickDate: false, language: 'tr', pickSeconds: false }).on('changeDate', function(){
			updateTimes();
		});
		$(".saatler .btn.add-on").click(function(){
			updateTimes();
		});
	});

 	var	keyw;
	var $exp = $(".section-cpoi [name='0iexpertises']");
	if ($exp.length) {
		$keyw = $(".section-cpoi .in-page-0ikeywords select");
		$exp.on("change",function(){
			var kval = $("[name='0ikeywords']").val();
				keyw.clearOptions();
				keyw.clear();
			if ($exp.val() != "") {
				var key_val = kval.split(",");
				keyw.load(function(callback){
					$.post("ext/map2heal/ajax.php",{exp:$exp.val()},function(result){
						callback(JSON.parse(result));
						$.each(key_val,function(k,v){
							keyw.addItem(parseInt(v));
						});
					});
				});
			}
		});
		$(document).ready(function(){
			setTimeout(function(){
				keyw = $(".section-cpoi .in-page-0ikeywords select")[0];
				keyw.selectize.destroy();
				keyw = $(".section-cpoi .in-page-0ikeywords select");
				keyw.html(" ");
				keyw.selectize({
				    valueField: 'cid',
				    labelField: 'iname',
				    searchField: 'iname',
				    create: false,
				    onChange: function(value) {
				    	var val = value!=null ? value.join(",") : "";
				    	var self = $("[name='0ikeywords']");
				    	self.val(val);
				    	self.trigger("change");
				    },
				});
				keyw = keyw[0].selectize;
			},5000);
		});
	}





