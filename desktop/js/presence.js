
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
var liste_modes = {};

$('#tab_modes a').click(function(e) {
    e.preventDefault();
    $(this).tab('show');
});

$('.bt_chooseIcon').on('click', function () {
});
	
//$('#bt_presenceExport').on('click', function() {
//	$('#md_modal').dialog({title: "{{Export}}"});
//    $('#md_modal').load('index.php?v=d&plugin=presence&modal=export').dialog('open');
//});


$(document).ready(function() {
	$('#picker_holiday_comeback').DateTimePicker({
		fullMonthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
		shortMonthNames: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jui", "Juil", "Août", "Sep", "Oct", "Nov", "Dec"],
		shortDayNames: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
		fullDayNames: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"]
	});
});
/**************** Actions Boutons ***********/

//$('#bt_addActionPresent').on('click', function() {
//    addAction({}, 'action_present', '{{Action}}');
//});
//$('#bt_addActionExitPresent').on('click', function() {
//    addAction({}, 'action_exit_present', '{{Action}}');
//});
//$('#bt_addCondPresent').on('click', function() {
//    addTrigger('cond_present',{});
//});
//$('#bt_addActionAbsent').on('click', function() {
//    addAction({}, 'action_absent', '{{Action}}');
//});
//$('#bt_addActionExitAbsent').on('click', function() {
//    addAction({}, 'action_exit_absent', '{{Action}}');
//});
//$('#bt_addCondAbsent').on('click', function() {
//    addTrigger('cond_absent',{});
//});
//$('#bt_addActionNuit').on('click', function() {
//    addAction({}, 'action_nuit', '{{Action}}');
//});
//$('#bt_addActionExitNuit').on('click', function() {
//    addAction({}, 'action_exit_nuit', '{{Action}}');
//});
//$('#bt_addCondNuit').on('click', function() {
//    addTrigger('cond_nuit',{});
//});
//$('#bt_addActionTravail').on('click', function() {
//    addAction({}, 'action_travail', '{{Action}}');
//});
//$('#bt_addActionExitTravail').on('click', function() {
//    addAction({}, 'action_exit_travail', '{{Action}}');
//});
//$('#bt_addCondTravail').on('click', function() {
//    addTrigger('cond_travail',{});
//});
// simulation
$('#bt_addActionDepart').on('click', function() {
    addVacances({}, 'action_depart', '{{Action}}');
});
$('#bt_addActionArrivee').on('click', function() {
    addVacances({}, 'action_arrivee', '{{Action}}');
});
$('#bt_addCondSimu').on('click', function() {
    addSimu('cond_simu', {});
});
$('#bt_addActionSimuON').on('click', function() {
    addAction({}, 'action_simulation_on', '{{Action}}');
});
$('#bt_addActionSimuOFF').on('click', function() {
    addAction({}, 'action_simulation_off', '{{Action}}');
});


$('#tab_add').on('click', function() {
    bootbox.prompt("Nom ?", function (result) {
        if (result !== null && result != '') {
            AddMode({name: result});
        }
    });
});

function AddMode(_mode){
	if (init(_mode.name) == '') {
        return;
    }
	if (init(_mode.icon) == '') {
        // _mode.icon = '<i class="icon fa fa-dot-circle-o"><\/i>';
        _mode.icon = '';
    }
	var mode_without_space = _mode.name.replace(" ","_");
	var mode_with_spaces = 	_mode.name.replace("_"," ");
	console.log(mode_with_spaces);
	/*var n_start = _mode.icon.indexOf('style');
	var n_stop = _mode.icon.indexOf('"',_mode.icon.indexOf('"',n_start));
	n_stop = _mode.icon.indexOf('"',_mode.icon.indexOf('"',n_stop + 1));
	var mode_icon = _mode.icon.slice(0,n_start).concat(_mode.icon.slice(n_stop + 2, _mode.icon.length));*/
	
	$('#tab_modes').append('<li><a class="ModeAttr" href="#tab_' + mode_without_space + '" data-l1key="name" mode_name="' + mode_without_space+ '"><span class="ModeAttr" data-l1key="icon">'+_mode.icon+'</span>'+mode_with_spaces+'</a></li>');

	var NewMode = '<div style="margin-right:20px" class="tab-pane tabAttr" id="tab_' + mode_without_space + '"> ';	
	NewMode += '<br/><div class="btn-group pull-right" role="group"><a class="modeAction btn btn-default btn-sm" data-l1key="chooseName"><i class="fa fa-pencil"></i> {{Modifier le nom}}</a><a class="modeAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> {{Modifier Icône}}</a><a class="modeAction btn btn-default btn-sm" data-l1key="removeIcon"><i class="fa fa-trash"></i> {{Supprimer l\'icône}}</a><a class="modeAction btn btn-danger btn-sm" data-l1key="removeMode"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a></div>';
	NewMode += '<form class="form-horizontal"><legend>{{Pour être dans ce mode :}}<a class="btn btn-xs btn-success" id="bt_addCond' + mode_without_space + '" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Déclencheur}}</a></legend>'
	NewMode += '<div id="div_cond_' + mode_without_space + '"></div></form>'
	NewMode += '<form class="form-horizontal"><legend>{{Une fois dans ce mode je dois :}}<a class="btn btn-success btn-xs" id="bt_addAction' + mode_without_space + '" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Action}}</a>'
	NewMode += '</legend><div id="div_action_' + mode_without_space + '"></div></form>'	
	NewMode += '<form class="form-horizontal"><legend>{{En quittant ce mode je dois :}}<a class="btn btn-success btn-xs" id="bt_addActionExit' + mode_without_space + '" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Action}}</a>'
    NewMode += '</legend><div id="div_action_exit_' + mode_without_space + '"></div></form></div>'	
	$('.tab-content').append(NewMode);
	$('#tab_modes a').on('click', function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
	
	$('#tab_modes').find('a[mode_name="'+mode_without_space+'"] i').attr("style","");
	
	$('#bt_addCond' + mode_without_space).on('click', function() {
		addTrigger('cond_' + mode_without_space,{});
	});
	
	$('#bt_addAction' + mode_without_space).on('click', function() {
		addAction({}, 'action_' + mode_without_space, '{{Action}}');
	});
	
	$('#bt_addActionExit' + mode_without_space).on('click', function() {
		addAction({}, 'action_exit_' + mode_without_space, '{{Action}}');
	});
}

/**************** Commun ***********/

$('body').undelegate('.modeAction[data-l1key=chooseIcon]', 'click').delegate('.modeAction[data-l1key=chooseIcon]', 'click', function () {
    var mode_name = $(this).closest('.tabAttr ').attr("id");
	mode_name = mode_name.substring(4);
	var mode = $("#tab_modes").find("[mode_name="+mode_name+"]");
    chooseIcon(function (_icon) {
        mode.find('.ModeAttr[data-l1key=icon]').empty().append(_icon);
    });
});

$('body').undelegate('.modeAction[data-l1key=chooseName]', 'click').delegate('.modeAction[data-l1key=chooseName]', 'click', function () {
    var mode_name = $(this).closest('.tabAttr ').attr("id");
	mode_name = mode_name.substring(4);
	var mode = $("#tab_modes").find("[mode_name="+mode_name+"]");
    
	bootbox.prompt("{{Nouveau nom ?}}", function (result) {
        if (result !== null) {
			var result_with_space = result;
			result = result.replace(' ','_');
            mode.attr("href","#tab_" + result);
            mode.attr("mode_name", result);
			var _icon = mode.find('.ModeAttr[data-l1key=icon]');
			mode.empty().append(_icon).append(" " + result_with_space);
			$('.tab-content').find('#tab_' + mode_name).attr("id","tab_" + result);
			$('.tab-content').find('#div_cond_' + mode_name).attr("id","div_cond_" + result);
			$('.tab-content').find('#div_action_' + mode_name).attr("id","div_action_" + result);
			$('.tab-content').find('#div_action_exit_' + mode_name).attr("id","div_action_exit_" + result);
			
			$('.tab-content').find('#bt_addCond' + mode_name).attr("id","bt_addCond" + result);
			$('.tab-content').find('#bt_addAction' + mode_name).attr("id","bt_addAction" + result);
			$('.tab-content').find('#bt_addActionExit' + mode_name).attr("id","bt_addActionExit" + result);		
			
			$('.tab-content').find('.cond_' + mode_name).attr("class","cond_" + result);
			$('.tab-content').find('.action_' + mode_name).attr("class","action_" + result);
			$('.tab-content').find('.action_exit_' + mode_name).attr("class","action_exit_" + result);	
        }
    });
});

$('body').undelegate('.modeAction[data-l1key=removeIcon]', 'click').delegate('.modeAction[data-l1key=removeIcon]', 'click', function () {
    var mode_name = $(this).closest('.tabAttr ').attr("id");
	mode_name = mode_name.substring(4);
	var mode = $("#tab_modes").find("[mode_name="+mode_name+"]");
    mode.find('.ModeAttr[data-l1key=icon]').empty();
});

$('body').undelegate('.modeAction[data-l1key=removeMode]', 'click').delegate('.modeAction[data-l1key=removeMode]', 'click', function () {
    var mode_name = $(this).closest('.tabAttr ').attr("id");
	bootbox.confirm("Êtes vous sûr ?", function(result) {
		if(result == true){
			$('.tab-content').find("#" + mode_name).remove();
			mode_name = mode_name.substring(4);
			var mode = $("#tab_modes").find("[mode_name="+mode_name+"]");
			mode.remove();
			$('#state_order_list').find('[mode_name="'+mode_name+'"]').remove();	
		}
	}); 
});

$("body").delegate(".listEquipement", 'click', function() {
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=eqLogic]');
    jeedom.eqLogic.getSelectModal({}, function(result) {
        el.value(result.human);
    });
});

$("body").delegate(".listCmdAction", 'click', function() {
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function(result) {
        el.value(result.human);
        jeedom.cmd.displayActionOption(el.value(), '', function(html) {
            el.closest('.' + type).find('.actionOptions').html(html);
        });
    });
});

//$('#bt_cronGenerator').on('click',function(){
//    jeedom.getCronSelectModal({},function (result) {
//        $('.eqLogicAttr[data-l1key=configuration][data-l2key=repeat_commande_cron]').value(result.value);
//    });
//});

$(".eqLogic").delegate(".listCmdInfo", 'click', function () {
    var el = $(this).closest('.form-group').find('.eqLogicAttr');
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        if (el.attr('data-concat') == 1) {
            el.atCaret('insert', result.human);
        } else {
            el.value(result.human);
        }
    });
});

$('body').delegate('.rename', 'click', function () {
    var el = $(this);
    bootbox.prompt("{{Nouveau nom ?}}", function (result) {
        if (result !== null) {
            el.text(result);
            el.closest('.mode').find('.modeAttr[data-l1key=name]').value(result);
        }
    });
});

$("body").delegate(".listCmdInfo", 'click', function() {
	var type = $(this).attr('data-type');	
	var el = $(this).closest('.' + type).find('.triggerAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function(result) {
        el.value(result.human);
    });
});

$("body").delegate('.bt_removeAction', 'click', function() {
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

$("body").delegate('.bt_removeTrigger', 'click', function() {
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

/*$('body').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function(event) {
    var type = $(this).attr('data-type')
    var expression = $(this).closest('.' + type).getValues('.expressionAttr');
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function(html) {
        $(this).closest('.' + type).find('.actionOptions').html(html);
    })
});*/

$('body').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
    var type = $(this).attr('data-type')
    var expression = $(this).closest('.' + type).getValues('.expressionAttr');
    var el = $(this);
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
        el.closest('.' + type).find('.actionOptions').html(html);
    })
});

function saveEqLogic(_eqLogic) {
	var state_order = '';
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
	_eqLogic.configuration.modes = [];
	$("#tab_modes li a[class*='ModeAttr']").each(function () {
		var my_mode = [];
		//var icones_size = $('#icones_size').val();
		//$(this).find(".ModeAttr i").attr("style","font-size:" + icones_size);
		my_mode = $(this).getValues('.ModeAttr')[0];
		var tmp_icon_name = my_mode.icon;
		if(tmp_icon_name != ""){
			my_mode.icon = my_mode.icon.slice(0,3) + 'style="font-size:' + $('#icones_size').val() + '" ' + tmp_icon_name.slice(3);
		}
		my_mode.name = $(this).attr('mode_name');
		my_mode.action = $('#div_pageContainer #div_action_' + $(this).attr('mode_name') +' .action_' + $(this).attr('mode_name')).getValues('.expressionAttr');
		my_mode.action_exit = $('#div_pageContainer #div_action_exit_' + $(this).attr('mode_name') + ' .action_exit_' + $(this).attr('mode_name')).getValues('.expressionAttr');
		my_mode.condition = $('#div_pageContainer #div_cond_' + $(this).attr('mode_name')).find('.conditionAttr').getValues('.triggerAttr');//$('#div_cond_' + $(this).attr('mode_name') + ' .cond_' + $(this).attr('mode_name')).getValues('.triggerAttr');
		_eqLogic.configuration.modes.push(my_mode);
	});
	
	_eqLogic.configuration.icones_size = $('#icones_size').val();
	_eqLogic.configuration.action_depart = $('#div_action_depart .action_depart').getValues('.expressionAttr');
	_eqLogic.configuration.action_arrivee = $('#div_action_arrivee .action_arrivee').getValues('.expressionAttr');
	
	_eqLogic.configuration.action_simulation_on = $('#div_action_simulation_on .action_simulation_on').getValues('.expressionAttr');
	_eqLogic.configuration.action_simulation_off = $('#div_action_simulation_off .action_simulation_off').getValues('.expressionAttr');
	
	_eqLogic.configuration.cond_simu = $('#div_cond_simu .cond_simu').getValues('.triggerAttr');
	
	var modes_simulation = $('#simu_modes').val();
	var index_modes = 0;
	var modes_string = "";
	
	if(modes_simulation != null){
		for(index_modes = 0; index_modes < modes_simulation.length ; ++index_modes){
		if(modes_string != ""){
			modes_string = modes_string + ";";
		}
		modes_string = modes_string + modes_simulation[index_modes];
		}
	}
	else{
		modes_string = "Vacances";
	}
	_eqLogic.configuration.simulation_modes = modes_string;//$('#simu_modes').val();
	
	
	var modes_view = $('#view_modes').val();
	var index_modes = 0;
	var modes_string = "";
	
	if(modes_view != null){
		for(index_modes = 0; index_modes < modes_view.length ; ++index_modes){
		if(modes_string != ""){
			modes_string = modes_string + ";";
		}
		modes_string = modes_string + modes_view[index_modes];
		}
	}
	else{
		modes_string = "Vacances";
	}
	_eqLogic.configuration.modes_view = modes_string;//$('#simu_modes').val();
	
	$("#state_order_list li").each(function () {
			state_order += $(this).attr('mode_name') + ';';
	});
	state_order = state_order.substr(0,state_order.length -1);
	
	_eqLogic.configuration.state_order = state_order;
		
    return _eqLogic;
}

function printEqLogic(_eqLogic) {

    var view_modes = '' ;
    var priority_modes_list = '' ;
    var priority_modes_list_template = '' ;
    var list_modes = [];
    $("#tab_modes li a[class*='ModeAttr']").remove();
    $(".tab-content div[class*='tabAttr']").remove();

    $('#div_cond_simu').empty();
    $('#div_action_depart').empty();
    $('#div_action_arrivee').empty();
    $('#div_action_simulation_on').empty();
    $('#div_action_simulation_off').empty();
    $('#state_order_list').empty();
    $('#state_order_list_template').empty();
    $('#icones_size').empty();
    $('#view_modes').empty();
    $('#simu_modes').empty();
    $('#state_order_list').empty();
    $('#state_order_list_template').empty();
    
    if (isset(_eqLogic.configuration)) {	
        if (isset(_eqLogic.configuration.modes)) {
            for (var i in _eqLogic.configuration.modes) {
                list_modes.push(_eqLogic.configuration.modes[i].name);
                view_modes += '<option value="'+ _eqLogic.configuration.modes[i].name +'">'+ _eqLogic.configuration.modes[i].name  +'</option>';
                priority_modes_list_template += '<li class="" style="    height: 1.5em;font-size:100%;padding-left:0.8em;;margin-bottom:0.2em;color: #fff;background-color: #d9534f;border-radius: .25em;" mode_name="' + _eqLogic.configuration.modes[i].name + '"><span class="fa fa-arrows-v"><span/> '+ _eqLogic.configuration.modes[i].name +'</li>';
                AddMode(_eqLogic.configuration.modes[i]);
                //console.log(_eqLogic.configuration.modes[i]);
                if (isset(_eqLogic.configuration.modes[i].action)) {
                        for (var j in _eqLogic.configuration.modes[i].action) {
                                addAction(_eqLogic.configuration.modes[i].action[j], 'action_' + _eqLogic.configuration.modes[i].name, '{{Action}}');
                        }
                }
                if (isset(_eqLogic.configuration.modes[i].action_exit)) {
                        for (var j in _eqLogic.configuration.modes[i].action_exit) {
                                addAction(_eqLogic.configuration.modes[i].action_exit[j], 'action_exit_' + _eqLogic.configuration.modes[i].name, '{{Action}}');
                        }
                }
                if (isset(_eqLogic.configuration.modes[i].condition)) {
                        for (var j in _eqLogic.configuration.modes[i].condition) {
                                addTrigger('cond_' + _eqLogic.configuration.modes[i].name, _eqLogic.configuration.modes[i].condition[j]);
                                }
                        }
                }
                view_modes += '<option value="Vacances">Vacances</option>';
        }
		
        if(isset(_eqLogic.configuration.state_order)){
                 var res = _eqLogic.configuration.state_order.split(";");
                 var index_modes = 0;
                 for(index_modes = 0; index_modes < res.length ; ++index_modes){
                        if(res[index_modes] != ""){
                                console.log(list_modes.indexOf(res[index_modes]));
                                if(list_modes.indexOf(res[index_modes]) != -1){
                                priority_modes_list += '<li class="" style="    height: 1.5em;font-size:100%;padding-left:0.8em;margin-bottom:0.2em;background-color: #5bc0de;color: #fff;border-radius: .25em;" mode_name="' + res[index_modes] + '"><span class="fa fa-arrows-v"><span/> '+ res[index_modes] +'</li>';
                                }
                        }
                 }
        }
	
        if (isset(_eqLogic.configuration.action_depart)) {
            for (var i in _eqLogic.configuration.action_depart) {
                addVacances(_eqLogic.configuration.action_depart[i], 'action_depart', '{{Action}}');
            }
        }
        if (isset(_eqLogic.configuration.action_arrivee)) {
            for (var i in _eqLogic.configuration.action_arrivee) {
                addVacances(_eqLogic.configuration.action_arrivee[i], 'action_arrivee', '{{Action}}');
            }
        }
        if (isset(_eqLogic.configuration.action_simulation_on)) {
            for (var i in _eqLogic.configuration.action_simulation_on) {
                addAction(_eqLogic.configuration.action_simulation_on[i], 'action_simulation_on', '{{Action}}');
            }
        }
        if (isset(_eqLogic.configuration.action_simulation_off)) {
            for (var i in _eqLogic.configuration.action_simulation_off) {
                addAction(_eqLogic.configuration.action_simulation_off[i], 'action_simulation_off', '{{Action}}');
            }
        }
        if (isset(_eqLogic.configuration.cond_simu)) {
            for (var i in _eqLogic.configuration.cond_simu) {
                addSimu('cond_simu',_eqLogic.configuration.cond_simu[i]);
            }
        }

        $('#view_modes').append(view_modes);
        $('#simu_modes').append(view_modes);
        $('#icones_size').append('<option value="small">Petit</option><option value="initial">Normal</option><option value="medium">Medium</option><option value="large">Large</option><option value="xx-large">X-Large</option>');		
        $('#state_order_list').append(priority_modes_list);
        $('#state_order_list_template').append(priority_modes_list_template);

        $("#state_order_list li").each(function () {
                $("#state_order_list_template li[mode_name='" + $(this).attr('mode_name') + "']").remove();
        });

        $('#view_modes').multiSelect('destroy').multiSelect();
        $('#simu_modes').multiSelect('destroy').multiSelect();
        $('#icones_size').multiSelect('destroy').multiSelect();
        $('#state_order_list, #state_order_list_template').sortable({connectWith: ".connectedSortable"}).disableSelection();
		
        if (isset(_eqLogic.configuration.simulation_modes)) {
                 var res = _eqLogic.configuration.simulation_modes.split(";");
                 var index_modes = 0;
                 for(index_modes = 0; index_modes < res.length ; ++index_modes){
                         $('#simu_modes').multiSelect('select', res[index_modes]);
                 }
        }
        if (isset(_eqLogic.configuration.modes_view)) {
                var res = _eqLogic.configuration.modes_view.split(";");
                 var index_modes = 0;
                 for(index_modes = 0; index_modes < res.length ; ++index_modes){
                         $('#view_modes').multiSelect('select', res[index_modes]);
                 }
        }
		
        if (isset(_eqLogic.configuration.state_order)) {
                var res = _eqLogic.configuration.state_order.split(";");
                 var index_modes = 0;
                 for(index_modes = 0; index_modes < res.length ; ++index_modes){
                         //$('#view_modes').multiSelect('select', res[index_modes]);
                 }
        }

        if (isset(_eqLogic.configuration.icones_size)) {
            $('#icones_size').multiSelect('select', _eqLogic.configuration.icones_size);
        }

    }
}

function addAction(_action, _type, _name, _el) {
    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }
    var input = '';
    var button = 'btn-warning';
  
    var _mydiv = '<div class="' + _type + '">';
    _mydiv += '<div class="form-group ">';
    _mydiv += '<label class="col-lg-1 control-label">' + _name + '</label>';
    _mydiv += '<div class="col-lg-1">';
    _mydiv += '<a class="btn ' + button + ' btn-sm listCmdAction" data-type="' + _type + '"><i class="fa fa-list-alt"></i></a>';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-3 ' + input + '">';
    _mydiv += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-6 actionOptions">';
    _mydiv += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-1">';
    _mydiv += '<i class="fa fa-minus-circle pull-left cursor bt_removeAction" data-type="' + _type + '"></i>';
    _mydiv += '</div>';
    _mydiv += '</div>';
    if (isset(_el)) {
        _el.find('.div_' + _type).append(_mydiv);
        _el.find('.' + _type + ':last').setValues(_action, '.expressionAttr');
    } else {
        $('#div_' + _type).append(_mydiv);
        $('#div_' + _type + ' .' + _type + ':last').setValues(_action, '.expressionAttr');
    }
}

function addVacances(_action, _type, _name, _el) {
    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }
    var input = '';
    var button = 'btn-default';
    if ((_type == 'action_depart')||(_type == 'action_arrivee')) {
        input = 'has-warning';
        button = 'btn-warning';
    }
    
    var _mydiv = '<div class="' + _type + '">';
    _mydiv += '<div class="form-group ">';
    _mydiv += '<label class="col-lg-1 control-label">' + _name + '</label>';
    _mydiv += '<div class="col-lg-1">';
    _mydiv += '<a class="btn ' + button + ' btn-sm listCmdAction" data-type="' + _type + '"><i class="fa fa-list-alt"></i></a>';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-3 ' + input + '">';
    _mydiv += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-3 actionOptions">';
    _mydiv += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
    _mydiv += '</div>';
    if(_type == "action_arrivee"){
            _mydiv += '<label class="col-lg-1 control-label">{{Avant :}}</label>';
    }
    else{
            _mydiv += '<label class="col-lg-1 control-label">{{Après :}}</label>';
    }
    _mydiv += '<div class="col-lg-1 has-success text-right">';
    _mydiv += '<input class="expressionAttr form-control input-sm" data-l1key="waitDelay" />';
    _mydiv += '<input class="expressionAttr form-control input-sm" style="display:none" data-l1key="already_exec" value="0"/>';
    _mydiv += '</div>';
    _mydiv += '<label class="col-lg-1 control-label">{{minute(s)}}</label>';	
    _mydiv += '<div class="col-lg-1">';
    _mydiv += '<i class="fa fa-minus-circle pull-left cursor bt_removeAction" data-type="' + _type + '"></i>';
    _mydiv += '</div>';
    _mydiv += '</div>';
    if (isset(_el)) {
        _el.find('.div_' + _type).append(_mydiv);
        _el.find('.' + _type + ':last').setValues(_action, '.expressionAttr');
    } else {
        $('#div_' + _type).append(_mydiv);
        $('#div_' + _type + ' .' + _type + ':last').setValues(_action, '.expressionAttr');
    }
}

function addTrigger(_type, _trigger, _el) {
    if (!isset(_trigger)) {
        _trigger = {};
    }
    var _mydiv = '<div class="'+_type+'">';
    _mydiv += '<div class="conditionAttr form-group">';
    _mydiv += '<label class="col-lg-1 control-label">{{Quand }}</label>';
    _mydiv += '<div class="col-lg-1 has-success">';
    _mydiv += '<a class="btn btn-default btn-sm listCmdInfo btn-success" data-type="' + _type + '"><i class="fa fa-list-alt"></i></a>';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-2 has-success">';
    _mydiv += '<input class="triggerAttr form-control input-sm" data-l1key="cmd" data-type="' + _type + '"/>';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-1 has-success">';
    _mydiv += '<select style="" class="triggerAttr form-control input-sm" data-l1key="operande">';
    _mydiv += '<option value="<"><</option>';
    _mydiv += '<option value="==">=</option>';
    _mydiv += '<option value=">">></option>';
    _mydiv += '<option value="!=">!=</option>';
    _mydiv += '<option value="~">Contient</option>';
    _mydiv += '</select>';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-2 has-success">';
    _mydiv += '<input class="triggerAttr form-control input-sm" data-l1key="comp_value" />';
    _mydiv += '</div>';	
    _mydiv += '<label class="col-lg-1 control-label">{{Après :}}</label>';
    _mydiv += '<div class="col-lg-1 has-success text-right">';
    _mydiv += '<input class="triggerAttr form-control input-sm" data-l1key="waitDelay" />';
    _mydiv += '</div>';
    _mydiv += '<label class="col-lg-1 control-label">{{minute(s)}}</label>';
    _mydiv += '<div class="col-lg-1 text-right">';
    _mydiv += '<select style="" class="triggerAttr form-control input-sm" data-l1key="and">';
    _mydiv += '<option value="-1">Seule</option>';
    _mydiv += '<option value="1">Et</option>';
    _mydiv += '<option value="0">Ou</option>';
    _mydiv += '</select>';
    _mydiv += '</div>';
    _mydiv += '<div class="col-lg-1">';
    _mydiv += '<i class="fa fa-minus-circle pull-left cursor bt_removeTrigger" data-type="' + _type + '"></i>';
    _mydiv += '</div>';
    _mydiv += '</div>';
    $('#div_' + _type).append(_mydiv);
    $('#div_' + _type + ' .' + _type + ':last').setValues(_trigger, '.triggerAttr');
}

function addSimu(_type, _trigger, _el) {
    if (!isset(_trigger)) {
        _trigger = {};
    }
    var _mydiv = '<div class="'+_type+'">';
    _mydiv += '<div class="form-group">';
    _mydiv += '<label class="col-lg-1 control-label">{{Début :}}</label>';
 
    _mydiv += '<div class="col-lg-2 has-success">';
    _mydiv += '<input class="triggerAttr form-control input-sm" placeholder="00:00" data-l1key="debut" data-type="' + _type + '"/>';
    _mydiv += '</div>';
    _mydiv += '<label class="col-lg-1 control-label">{{Fin :}}</label>';
    _mydiv += '<div class="col-lg-2 has-success">';
    _mydiv += '<input class="triggerAttr form-control input-sm" placeholder="00:00" data-l1key="fin" data-type="' + _type + '"/>';
    _mydiv += '</div>';
    _mydiv += '<label class="col-lg-2 control-label">{{Différé max (minutes):}}</label>';
    _mydiv += '<div class="col-lg-2 has-success">';
    _mydiv += '<input class="triggerAttr form-control input-sm" placeholder="00" data-l1key="differe" data-type="' + _type + '"/>';
    _mydiv += '</div>';
	
    _mydiv += '<div class="col-lg-2">';
    _mydiv += '<i class="fa fa-minus-circle pull-left cursor bt_removeTrigger" data-type="' + _type + '"></i>';
    _mydiv += '</div>';
    _mydiv += '</div>';
    $('#div_' + _type).append(_mydiv);
    $('#div_' + _type + ' .' + _type + ':last').setValues(_trigger, '.triggerAttr');
}