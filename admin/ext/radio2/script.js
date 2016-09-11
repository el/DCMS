var $playlist = $("[name=0isarkilar]"),songs;

function playlist(dis) {
	var self = this;
	try { this.structure = JSON.parse(dis.val()); }
	catch(e) { this.structure = []; }
	this.input = dis;
	this.parent = dis.parent();
	this.name = "Dosyalar";
	this.path = "Dosyalar";
	this.prev = false;
	this.playing = {};
	this.onair = false;
	this.ajax = "ext/radio/ajax.php";
	this.selected = $([]);
	this.offset = this.parent.offset();
	this.currentpath = "";
	this.filesearch = [];

	this.init = function () {
	    $(document).keydown(function(objEvent) {        
	        if (objEvent.ctrlKey) {          
	            if (objEvent.keyCode == 65 || objEvent.keyCode == 97) {                         
	            	$(".playlist .song.ui-selectee").addClass("ui-selected");
	            	$(".remove-selected").removeClass("hidden");
	                return false;
	            }            
	        }        
	    });
		var prev = self.prev = self.parent.hasClass("preview");
		self.parent.append("<div class='listwrap no-time'><div class='filemanager span6'></div>"+
			"<div class='listmanager span6'><span class='hidden remove-selected btn btn-mini btn-danger'>"+
			"Seçilenleri Çıkar</span><div class='playlist-wrapper'><ol class='playlist'></ol><p>Buraya Taşıyınız</p></div><div class='total-time'></div></div></div>");
		self.playlist = $(".playlist");
		self.filemanager = $(".filemanager");
		self.filemanager.append("<div class='folderinfo'><i class='search-mp3 icon-search'></i><i class='folderup icon-level-up hidden'></i>"+
			"<div class='search-bar hidden'><input type='text'/><i class='closes icon-remove'></i></div>"+
			"<span class='pull-right hidden add-selected btn btn-mini btn-primary'>Seçilenleri Ekle</span>"+
			"<h2>Dosyalar</h2></div><div class='folder-wrapper'><div class='folderlist'><p>Dosyalar yükleniyor lütfen bekleyiniz!</p></div></div>"+
			"<div class='total-count'></div>");
		self.filemanager.list = self.filemanager.find(".folderlist");
		$.each(this.structure,function(k,v){
			self.addSong(v);
		});
		self.filemanager.find(".add-selected").click(function(){
			self.filemanager.list.find(".ui-selected").each(function(){
				$(this).find(".add-song,.add-folder").click();
			});
		});
		$(".remove-selected").click(function(){
			self.playlist.find(".ui-selected").remove();
			self.updateList();
			$(this).addClass("hidden");
		});
		self.playlist.on("click",".close",function(){
			$(this).parent().remove();
			self.updateList();
		});
		self.filemanager.list.on("click",".add-song",function(){
			self.addSong($(this).parent().data());
			setTimeout(function(){self.updateList();},500);
		});
		self.filemanager.list.on("click",".add-folder",function(){
			self.addFolder($(this).siblings("b").attr("data-path"));
			setTimeout(function(){self.updateList();},500);
		});
		self.filemanager.find(".search-bar input").change(function(){
			var v = $(this).val();
			if (v=="")
				return self.loadFiles(self.currentpath);
			var files = self.currentpath ? self.getFiles(self.currentpath).files : self.files;
			self.loadSearch(files,v);
		});
		self.filemanager.find(".search-mp3").click(function(){
			self.filemanager.find(".search-bar").removeClass("hidden").find("input").focus();
		});
		self.filemanager.find(".search-bar .closes").click(function(){
			self.filemanager.find(".search-bar").addClass("hidden");
			self.filemanager.find(".search-bar input").val("");
			self.loadFiles(self.currentpath);
		});
		self.filemanager.on("click",".folder b, .folderup",function(e){
			self.loadFiles($(this).attr("data-path"));
		});
		if ($(".radio-ui").length) {
			self.initPlayer();
		}
		if (!prev) {
			self.parent.prepend('<div class="add-content"><a class="btn" onclick="songs.resize()" data-toggle="buttons-radio"><i class="icon-resize-full"></i></a></div>')
			$.get("ext/radio/ajax.php?filelist",function(r){
				try { 
					var files = JSON.parse(r);
					self.files = files.files;
					$(".total-count").html("<b>Toplam:</b> "+files.total+" Parça");
					files
				} catch(e) { self.files = []; }
				self.loadFiles();
			});
			self.filemanager.list
			//.bind("mousedown", function(e){e.metaKey = true;})
			.selectable({cancel:".folder b,span",stop:function(){
				if ($(this).find(".ui-selected").length)
					$(".add-selected").removeClass("hidden");
				else
					$(".add-selected").addClass("hidden");
			}});
			self.filemanager.list.on("dblclick",".file",function(){
				if ($(this).hasClass("folder"))
					$(this).find("b").click();
				else
					self.flash($(this).data().path);
			});
			$(".playlist-wrapper").droppable({
				accept: ".folderlist div",
				activeClass: "ui-state-highlight",
				hoverClass: "ui-state-hover",
				drop: function(event,ui) {
					self.filemanager.list.find(".ui-selected").each(function(){
						$(this).find(".add-song,.add-folder").click();
					});
				},
			});
			self.playlist.sortable({stop: self.updateList,
				handle: "i",
				axis : 'y',
				start: function(e, ui) {
					var topleft = 0;
					// if the current sorting LI is not selected, select
					$(ui.item).addClass('ui-selected');
					var w = $(ui.item).parent().width();

					self.playlist.find('li.ui-selected').each(function() {
						var originalParent = $(this).parent()[0];
						$(this).data('origin', originalParent);
						$(this).css({
							'position': 'absolute',
							'top': topleft,
							'left': topleft ? topleft-10 : 30,
							'width': w,
						});
						topleft += 10;
					}).appendTo(ui.item); // glue them all inside current sorting LI
				},
				stop: function(e, ui) {
					$(ui.item).children("li").each(function() {
						
						// restore all the DIVs in the sorting LI to their original parents
						var originalParent = $(this).data('origin');
						$(this).appendTo(originalParent);

						// remove the cascade positioning
						$(this).css({
							'position': '',
							'top': '',
							'left': '',
							'width': '',
						});
					});
					
					// put the selected LIs after the just-dropped sorting LI
					self.playlist.find('li.ui-selected').insertAfter(ui.item);
				},
			})			//.bind("mousedown", function(e){e.metaKey = true;})
			.selectable({
				cancel:".folder b,span",
				stop:function(){
					if ($(this).find(".ui-selected").length)
						$(".remove-selected").removeClass("hidden");
					else
						$(".remove-selected").addClass("hidden");
				},
			});
			setTimeout(self.updateList,500);
		} else {
			var time = 0,count=0;
			for (var i = 0; i < self.structure.length; i++) {
				time += self.structure[i].length;
				count++;
			};
			$(".listmanager .total-time").html("<b>Toplam:</b> "+count+" Parça - "+self.convertTime(time));
		}
	};

	this.getFiles = function(path,list) {
		list = list ? list : self.files;
		if (list && list.length)
		for (var i=0; i<list.length;i++){
			var v = list[i];
			if (v.path==path) {
				return v;
			}
			else if ('files' in v) {
				var found = self.getFiles(path,v.files);
				if (found) return found;
			}
		}
	}

	this.findFiles = function(files,v) {
		$.each(files,function(k,f){
			if ("files" in f)
				self.findFiles(f.files,v);
			if (f.name.toLowerCase().indexOf(v)!= -1)
				self.filesearch.push(f);
		});
	}

	this.loadSearch = function(files,v) {
		self.filesearch = [];
		self.findFiles(files,v);
		self.addToFileManager(self.filesearch);
	}

	this.resize = function() {
		var b = ["span4","span8","span6"];
		var a = $("body").hasClass("fullscr");
		var h = a ? 470 : $(window).height()-250;
		$('.playlist-wrapper').height(h);
		$('.listmanager').addClass(!a?b[0]:b[2]).removeClass(!a?b[2]:b[0]);
		$('.folderlist').height(h-50);
		$('.filemanager').addClass(!a?b[1]:b[2]).removeClass(!a?b[2]:b[1]);;
		$("body").toggleClass("fullscr");
	};

	this.getOnlyFiles = function(list) {
		var arr = [];
		for (var i = 0; i < list.files.length; i++) {
			if ('files' in list.files[i])
				arr = arr.concat(self.getOnlyFiles(list.files[i]));
			else
				arr.push(list.files[i]);
		};
		return arr;
	};

	this.addFolder = function(path) {
		var files = self.getOnlyFiles(self.getFiles(path));
		for (var i = 0; i < files.length; i++) {
			self.addSong(files[i],true);
		};
	};

	this.loadFiles = function(path) {
		self.currentpath = path;
		path = path=="/" ? false : path;
		var list = path ? self.getFiles(path) : self;
		self.filemanager.find("h2").text(list.name);
		var fu = self.filemanager.find(".folderup");
		if (path) {
			var fup = path.split("/").slice(0,-1).join("/");
			fu.removeClass("hidden").attr("data-path",fup);
		} else fu.addClass("hidden");
		$(".add-selected").addClass("hidden");
		self.addToFileManager(list.files);
	};

	this.addToFileManager = function (files) {
		self.filemanager.list.html("");
		$.each(files, function(k,v){
			var folder = ('files' in v);
			if (folder) {
				self.filemanager.list.append("<div class='file folder'>"+
					"<span class='btn btn-mini btn-primary add-folder pull-right'><i class='icon-plus'></i></span>"+
					"<i class='icon-folder-open icon-fixed-width pull-left'></i><b data-path='"+v.path+
					"'>"+v.name+"</b><p></p></div>");
			} else {
				$("<div class='file song'><span class='btn btn-mini btn-primary add-song pull-right'><i class='icon-plus'></i></span>"+
					"<i class='icon-music icon-fixed-width pull-left'></i><i class='info time pull-right'>"+
					(self.getTime(v.length))+"</i><b>"+v.name+"</b><p></p></div>").data(v).appendTo(self.filemanager.list);
			}
		});

		self.filemanager.list.find("div").draggable({
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: "document",
			helper: "clone",
			cursor: "move",
			handle: "p",
			cursorAt: { left: -5 , top: -5},
			start: function(ev,ui) {
				var $selected = self.filemanager.list.find(".ui-selected.file");
				ui.helper.html(" ").width($selected.first().outerWidth());
				$selected.not(ui.helper).each(function() {
					var $t = $(this).find("b").clone();
					$t.prepend($(this).find(".icon-fixed-width").clone());
					$t.appendTo(ui.helper);
				});
			},
		});

	}

	this.getTime = function(s) {
		return Math.floor(s/60)+":"+(("0" + (s%60)).slice(-2));
	}

	this.convertTime = function(s) {
		s = Math.floor(s);
		var t = "";
		if (s>60*60*24) {
			t += Math.floor(s/(60*60*24))+" gün ";
			s = s%(60*60*24);
		}
		if (s>60*60) {
			t += Math.floor(s/(60*60))+" saat ";
			s = s%(60*60);
		}
		if (s>60) {
			t += Math.floor(s/60)+" dakika ";
			s = s%60;
		}
		if (s>0 || t=="") {
			t += s+" saniye";
		}
		return t;
	};

	this.flash = function(a) {
		a = a ? "&url="+encodeURIComponent(a) : "";
		window.open(self.ajax+"?player"+a,"_blank",'location=0, menubar=0, status=0, toolbar=0, scrollbars=0, width=350, height=30');
	}

	this.initPlayer = function() {
		$(".plist").on("mouseenter",".song",function(){
			$(this).find(".pss").removeClass("icon-music").addClass("icon-play-sign");
		}).on("mouseleave",".song",function(){
			$(this).find(".pss").removeClass("icon-play-sign").addClass("icon-music");
		}).on("click",".pss",function(){
			$.get(self.ajax,{mpd: "play", param: $(this).parent().data().id});
			self.updateNowPlaying();
		});
		$("#now-playing").on("click","[data-btn]",function(){
			$.get(self.ajax,{mpd: $(this).data().btn, param: $(this).hasClass("active")?0:1});
		}).on("click",".ply.play i",function(){
			if ($(this).hasClass("icon-play"))
				$(this).removeClass("icon-play").addClass("icon-pause");
			else
				$(this).removeClass("icon-pause").addClass("icon-play");
			$.get(self.ajax,{mpd: "pause", param: $(this).hasClass("icon-play")?1:0});
		}).on("click","[data-method]",function(){
			$.get(self.ajax,{mpd: $(this).data().method});
		}).on("click",".seeker",function(e){
			var left = e.pageX - $(this).offset().left;
			console.log(left);
        	var total = $(this).width();
			console.log(total);
        	var time = parseInt(left*parseInt(self.playing.song.Time)/total);
        	$.get(self.ajax,{mpd: "seek", song:self.playing.song.Pos,param:time})
		});
		$(".onair").click(function(){
			self.onair = $(this).hasClass("active");
			$.get(self.ajax,{mpd: "live", param:self.onair?0:1});
		});

		self.player = $("#now-playing");
		self.player = {
			h2 : self.player.find("h2"),
			h3 : self.player.find("h3"),
			h4 : self.player.find("h4"),
			h5 : self.player.find("h5"),
			pl : self.player.find(".ply.play i"),
			bar: self.player.find(".seeker .bar"),
			rnd: self.player.find("[data-btn=random]"),
			rep: self.player.find("[data-btn=repeat]"),
		};

		self.updateNowPlaying();
	};
	this.updateNowPlaying = function() {
		$.get(self.ajax,{mpd: "now"},function(e){
			var e = self.playing = JSON.parse(e);
			self.onair = e.song.basename=="live";
			if (self.onair){
				$(".onair").addClass("active");
				e.song = {
					Title: e.song.Name,
					Artist: "Canlı Yayın",
					Pos: e.song.Pos,
					};
			} else 
				$(".onair").removeClass("active");
			self.player.h2.text(e.song.Title);
			self.player.h3.text(e.song.Artist);
			self.player.h5.text(e.song.name);
			if (e.status.state!="play")
				self.player.pl.removeClass("icon-pause").addClass("icon-play");
			else
				self.player.pl.removeClass("icon-play").addClass("icon-pause");

			$(".playlist .ui-selected").removeClass("ui-selected");
			$(".playlist .song").eq(parseInt(e.song.Pos)).addClass("ui-selected");

			var time = e.status.time.split(":");
			self.player.bar.css("width",(time[1]?(time[0]/time[1])*100:0)+"%");
			self.player.h4.text(self.getTime((time[0]*1)));
			if (e.status.random=="1") self.player.rnd.addClass("active");
			else self.player.rnd.removeClass("active");
			if (e.status.repeat=="1") self.player.rep.addClass("active");
			else self.player.rep.removeClass("active");

			setTimeout(self.updateNowPlaying,1000);
		});
	};

	this.updateList = function() {
		self.structure = [];
		var time = 0,count=0;
		self.playlist.children(".song").each(function(){
			var data = $(this).data();
			self.structure.push({name:data.name,path:data.path,length:data.length});
			time += parseInt(data.length);
			count++;
		});
		self.input.val(JSON.stringify(self.structure));
		$(".listmanager .total-time").html("<b>Toplam:</b> "+count+" Parça - "+self.convertTime(time));
	};

	this.addSong = function (obj) {
		var song = "<li class='song' title='"+obj.path+"'><i class='pss "+(self.prev?"icon-music":"icon-reorder")+"'></i> <span class='close'>&times;</span>"+
		"<i class='info time'>"+self.getTime(obj.length)+"</i><span>"+obj.name+"</span></li>";
		if (!self.playlist.find('[title="'+obj.path+'"]').length)
			$(song).data(obj).appendTo(self.playlist);
	};

	this.init();
	return this;
}


$(document).on('DOMNodeInserted', function(e) {
    if ($(e.target).is('.ajax-show') && $(e.target).find('.extension').length) {
       songs = new playlist($(e.target).find('.extension'));
    }
});

$(".extension[name=0isarkilar]").on("load",function(){
	songs = new playlist($(this));
});

if ($playlist.length)
	songs = new playlist($playlist);