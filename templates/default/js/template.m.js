function hasClass(obj,cls){
	return (' '+obj.className+' ').indexOf(' '+cls+' ')>-1;
	}
function removeClass(obj,cls){
	var classes = obj.className.split(' ');
	for(i=0; i<classes.length; i++) {
		if(classes[i]==cls){
			classes.splice(i, 1);
			i--;
			}
		}
	obj.className = classes.join(' ');
	}
function addClass(obj,cls){
	var classes=obj.className.split(' ');
	if(hasClass(obj,cls))
		return true;
	obj.className+=' '+cls;
	}

var mmenu;
var gbz;
var gbm;

function addevents(){
	mmenu=document.getElementById('mainmenu');
	mmenui=document.getElementById('mainmenuicon');
	gbz=document.getElementById('gbz');
	//mmenu.addEventListener("click", myScript);
	mmenu.onclick=function(){
		window.console&&console.log('menu: menu button clicked!');
		if(hasClass(gbz,'opened')){
			removeClass(gbz,'opened');
			addClass(mmenui,'pg-burgermenu');
			removeClass(mmenui,'pg-delete');
			}else{
			addClass(gbz,'opened');
			removeClass(mmenui,'pg-burgermenu');
			addClass(mmenui,'pg-delete');
			}
		};
	}
addevents(); //we using lazy load, so we don't need addEventListener

$(document).ready(function(){
	$('.select_switch select').each(function(){
	    $(this).siblings('p').text( $(this).children('option:selected').text() );
	});
	$('.select_switch select').change(function(){
	    $(this).siblings('p').text( $(this).children('option:selected').text() );
	});
});
