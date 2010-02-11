function toggleBox(id){
	if (document.getElementById(id).style.display == "") {
		show = "none";
	} else {
		show = "";
	}
	document.getElementById(id).style.display = show;
}

function insert_smilie(text)
{
	var text = text;
	document.REPLIER.body.value += " " + text + " ";
}

function smilie_window(gate)
{
	window.open(gate + '?a=misc','','width=250,height=500,resizable=yes,scrollbars=yes'); 
}