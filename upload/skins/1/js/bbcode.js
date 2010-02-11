function codeBasic(e) {
	var form = document.forms["REPLIER"];
	var tag = e.name;
	var code = "";

	if(!form['mode'][0].checked){
		text = prompt("Enter the text you wish to format:\n[" + tag + "]xxx[/" + tag + "]" , "");
		if ( (text != "") && (text != null) ) {
			code = "[" + tag + "]" + text + "[/" + tag + "]";
		}
	}else{
		if(e.value.indexOf("*") != -1){
			var code = "[/" + tag + "]";
			e.value = e.value.substring(0,(e.value.length-1));
		}else{
			var code = "[" + tag + "]";
			e.value += "*";
		}
	}
	form['body'].value += code;
	form['body'].focus();
}


function codeList()
{
	var value = "init";
	var list = "[list]\n";
	
	while ( (value != "") && (value != null) ) {
		value = prompt("Enter a list item to continue, press 'Cancel' when you are finished:", "");
		if ( (value != "") && (value != null) ) {
			list = list + "  [*]" + value + "\n";
		}
	}

	document.REPLIER.body.value += list + "[/list]";
	document.REPLIER.body.focus();
}

function fontFace(param)
{
	text = prompt("Enter the text whose typeface you wish to change:", "");

	if ( (text != "") && (text != null) ) {
		document.REPLIER.body.value += "[font=" + param + "]" + text + "[/font]";
		document.REPLIER.body.focus();
	} else {
		alert("You must enter text to use this feature!");
	}
}

function fontColor(param)
{
	text = prompt("Enter the text whose color you wish to change:", "");

	if ( (text != "") && (text != null) ) {
		document.REPLIER.body.value += "[color=" + param + "]" + text + "[/color]";
		document.REPLIER.body.focus();
	} else {
		alert("You must enter text to use this feature!");
	}
}

function fontSize(param)
{
	text = prompt("Enter the text whose size you wish to change:", "");

	if ( (text != "") && (text != null) ) {
		document.REPLIER.body.value += "[size=" + param + "]" + text + "[/size]";
		document.REPLIER.body.focus();
	} else {
		alert("You must enter text to use this feature!");
	}
}