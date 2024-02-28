var code;
var lastkey;
var tablet = false;
var kb;
var slideTID;
var shiftTID;
var evt;
var currKey;

function init(){
	if (/(iPhone|iPad|iPod)/i.test(navigator.userAgent)) {  
		tablet = true;
	}else{
		tablet = false;
		$('.loginUI .shiftkey').addClass('disabled');
	}
	
	kb = document.getElementById("kb");
	
	$('.loginUI div.key')
		.mousemove(function(){$(this).addClass('hover');})
		.mouseout(function(){$(this).removeClass('hover');});

	code = "";
	lastkey = "";
	slideTID = null;
	shiftTID = null;
	if(tablet){
		kb.addEventListener("touchstart", start_record, false);
		kb.addEventListener("touchmove", record, false);
		kb.addEventListener("touchend", stop_record, false);
	}else{
		kb.onmousedown = start_record;
		document.onkeydown = keydown;
		document.onkeyup = keyup;
	}
}

function shiftdown(sTID){
		shiftTID = sTID;
		code += "s";
		$(".loginUI .key").each(function(){
			$(this).html($(this).attr('skey'));
		});

}

function shiftup(){
		shiftTID = null;
		code += "s";
		$(".loginUI .key").each(function(){
			$(this).html($(this).attr('key'));
		});
}

function keydown(e)
{
　　evt=e||event;
　　currKey=e.keyCode||e.which||e.charCode;
	if(currKey == 16 && shiftTID == null){
		shiftdown(1);
	}
}

function keyup(e)
{ 
　　evt=e||event;
　　currKey=e.keyCode||e.which||e.charCode;
	if(currKey == 16 && shiftTID == 1){
		shiftup();
	}
}

function start_record(e){
	e.preventDefault();
	banner("输入中");
	if(tablet){
		var l = e.changedTouches.length, t;
		for(i = 0; i < l; i++){
			t = e.changedTouches[i];
			
			k = document.elementFromPoint(t.pageX, t.pageY).getAttribute("key");
			
			if(k == "s" && shiftTID == null){
				shiftdown(t.identifier);
			}else if(k != null && slideTID == null){
				slideTID = t.identifier;
			}
		}
	}else{ 
		k = e.target.getAttribute("key");

		if(k != null){
			kb.onmousedown = null;
			kb.onmousemove = record;
			kb.onmouseup = stop_record;
		}
	}
}

function record(e){
	e.preventDefault();
	k = null;
	if(tablet){
		var l = e.changedTouches.length, t;
		for(i = 0; i < l; i++){
			t = e.changedTouches[i];
			if(t.identifier == slideTID){
				k = document.elementFromPoint(t.pageX, t.pageY).getAttribute("key");
			}
		}
	}else{
		k = e.target.getAttribute("key");
	}
	if(k != null && k != "s"){
		if(k != lastkey){
			lastkey = k;
			code += k;
		}
	}
}

function stop_record(e){
	if(tablet){
		var l = e.changedTouches.length, t;
		for(i = 0; i < l; i++){
			t = e.changedTouches[i];
			if(t.identifier == slideTID){
				slideTID = null;
				post_password();
			}else if(t.identifier == shiftTID){
				shiftup();
				return;
			}else{
				return;
			}
		}
				
	}else{
		kb.onmousemove = null;
		kb.onmouseup = null;
		post_password();
	}
}

function post_password(){
	//alert(code);
	//Ajax
	$.ajax({
		url: $("#post_form").attr("action"),
		type: $("#post_form").attr("method"),
		data: {mode: "ajax", method: "slide", pass: code, user: $("#post_form #user").attr("value")},
		dataType: "text",
		cache: false,
		success: function(data){
			try{
				data = jQuery.parseJSON(data);
				
				try{
					if(data.status == "success"){
						failure("验证成功", data);
					}else{
						failure("用户名或密码错误", data);
					}
				}catch(err){
					failure("未知错误", data);
				}
				
			}catch(e){
				failure("未知错误", data);
			}
		},
		fail: function(jqXHR, textStatus) {
			failure("网络错误", data);
		}
	});
}

function failure(text, data){
	$("#post_form .log").html(text);
	if(data == undefined){
		//console.debug(data);
	}else{
		$(".area-ABC").html(data.new_keys);
	}
	init();
	$('div.key')
		.mousemove(function(){$(this).addClass('hover');})
		.mouseout(function(){$(this).removeClass('hover');});
}

function banner(text){
	$("#post_form .log").html(text);
}

$(document).ready(init);
