var presentCond = $('#div_cond_present .cond_present').getValues('.triggerAttr');
var absentCond = $('#div_cond_absent .cond_absent').getValues('.triggerAttr');
var nuitCond = $('#div_cond_nuit .cond_nuit').getValues('.triggerAttr');
var travailCond = $('#div_cond_travail .cond_travail').getValues('.triggerAttr');

var tli_total = '';
var tli = '';
var tli2 = '';
var tli3 = '';


tli_total = '<span class="badge">--</span> ';
for(var i in presentCond){
	if(presentCond[i].and == '-1'){
		tli_total += presentCond[i].cmd + presentCond[i].operande + presentCond[i].comp_value + ' - ';
	}
}
$('#present .well1').empty().append(tli_total);
tli_total = '<span class="badge">Et</span> ';
for(var i in presentCond){
	if(presentCond[i].and == '1'){
		tli_total += presentCond[i].cmd + presentCond[i].operande + presentCond[i].comp_value + ' && ';
	}
}
$('#present .well2').empty().append(tli_total);
tli_total = '<span class="badge">Ou</span> ';
for(var i in presentCond){
	if(presentCond[i].and == '0'){
		tli_total += presentCond[i].cmd + presentCond[i].operande + presentCond[i].comp_value + ' || ';
	}
}
$('#present .well3').empty().append(tli_total);

tli_total = '<span class="badge">--</span> ';
for(var i in absentCond){
	if(absentCond[i].and == '-1'){
		tli_total += absentCond[i].cmd + absentCond[i].operande + absentCond[i].comp_value + ' - ';
	}
}
$('#absent .well1').empty().append(tli_total);
tli_total = '<span class="badge">Et</span> ';
for(var i in absentCond){
	if(absentCond[i].and == '1'){
		tli_total += absentCond[i].cmd + absentCond[i].operande + absentCond[i].comp_value  + ' && ';
	}
}
$('#absent .well2').empty().append(tli_total);
tli_total = '<span class="badge">Ou</span> ';
for(var i in absentCond){
	if(absentCond[i].and == '0'){
		tli_total += absentCond[i].cmd + absentCond[i].operande + absentCond[i].comp_value  + ' || ';
	}
}
$('#absent .well3').empty().append(tli_total);


tli_total = '<span class="badge">--</span> ';
for(var i in nuitCond){
	if(nuitCond[i].and == '-1'){
		tli_total += nuitCond[i].cmd + nuitCond[i].operande + nuitCond[i].comp_value + ' - ';
	}
}
$('#nuit .well1').empty().append(tli_total);
tli_total = '<span class="badge">Et</span> ';
for(var i in nuitCond){
	if(nuitCond[i].and == '1'){
		tli_total += nuitCond[i].cmd + nuitCond[i].operande + nuitCond[i].comp_value  + ' && ';
	}
}
$('#nuit .well2').empty().append(tli_total);
tli_total = '<span class="badge">Ou</span> ';
for(var i in nuitCond){
	if(nuitCond[i].and == '0'){
		tli_total += nuitCond[i].cmd + nuitCond[i].operande + nuitCond[i].comp_value  + ' || ';
	}
}
$('#nuit .well3').empty().append(tli_total);


tli_total = '<span class="badge">--</span> ';
for(var i in travailCond){
	if(travailCond[i].and == '-1'){
		tli_total += travailCond[i].cmd + travailCond[i].operande + travailCond[i].comp_value + ' - ';
	}
}
$('#travail .well1').empty().append(tli_total);
tli_total = '<span class="badge">Et</span> ';
for(var i in travailCond){
	if(travailCond[i].and == '1'){
		tli_total += travailCond[i].cmd + travailCond[i].operande + travailCond[i].comp_value  + ' && ';
	}
}
$('#travail .well2').empty().append(tli_total);
tli_total = '<span class="badge">Ou</span> ';
for(var i in travailCond){
	if(travailCond[i].and == '0'){
		tli_total += travailCond[i].cmd + travailCond[i].operande + travailCond[i].comp_value  + ' || ';
	}
}
$('#travail .well3').empty().append(tli_total);

/*
for(var i in presentCond){
	if(presentCond[i].and == '-1'){
		tli += '<li class="list-group-item"><span class="badge">--</span>';
		tli += presentCond[i].cmd;
		tli += '</li>';
	}
	else if(presentCond[i].and == '0'){
		tli2 += '<li class="list-group-item"><span class="badge">Ou</span>';
		tli2 += presentCond[i].cmd;
		tli2 += '</li>';
	}
	else if(presentCond[i].and == '1'){
		tli3 += '<li class="list-group-item"><span class="badge">Et</span>';
		tli3 += presentCond[i].cmd;
		tli3 += '</li>';
	}
}*/
/*
for(var i in presentCond){
	if(presentCond[i].and == '0'){
		tli2 += '<li class="list-group-item"><span class="badge">Ou</span>';
		tli2 += presentCond[i].cmd;
		tli2 += '</li>';
	}
}
for(var i in presentCond){
	if(presentCond[i].and == '1'){
		tli3 += '<li class="list-group-item"><span class="badge">Et</span>';
		tli3 += presentCond[i].cmd;
		tli3 += '</li>';
	}
}*/