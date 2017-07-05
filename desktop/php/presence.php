<?php
if (!isConnect('admin')) {
    throw new Exception('{{Error 401 Unauthorized}}');
}
sendVarToJS('eqType', 'presence');
$eqLogics = eqLogic::byType('presence');

?>
<div class="row row-overflow">
	
	<div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
	
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend>{{Mes Modules Présence}}
        </legend>
            <div class="eqLogicThumbnailContainer">
				<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
					<center> 
						<i class="fa fa-plus-circle" style="font-size : 7em;color:#4F81BD;"></i>
					</center>
					<span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#4F81BD"><center>{{Ajouter}}</center></span>
				</div>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/presence/doc/images/presence_icon.png" height="105" width="95" />';
                    echo "</center>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
                ?>
            </div>
    </div>
	
	
	
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<div class="row">
			<div class="col-lg-6">
				<form class="form-horizontal">
					<fieldset>
					<legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Nom du module :}}</label>
							<div class="col-lg-8">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label" >{{Objet parent}}</label>
							<div class="col-lg-8">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (object::all() as $object) {
										echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">{{Catégorie}}</label>
							<div class="col-lg-8">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>

							</div>
						</div>
						
						<div class="form-group">
							<label class="col-lg-3 control-label"></label>
							<div class="col-lg-8">
                                                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                                                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisibl" checked/>{{Visible}}</label>
							</div>
						</div>


						<div class="form-group">
							<label class="col-lg-3 control-label">{{Jour de retour}} : </label>
							<div class="col-lg-8">
								
								<!--<input type="text" data-field="datetime" readonly>-->
								<input type="text" data-field="datetime" class="eqLogicAttr" data-l1key="configuration" data-l2key="holiday_comeback" readonly />
								<div id="picker_holiday_comeback"></div>
							</div>
							
						</div>
					</fieldset> 
				</form>
			</div>
			<div class="col-lg-6">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Priorité des modes}} </legend>
							<div>
								<p>{{Glissez déposez les modes du plus au moins prioritaire}} :</p>
								
								<div class="col-md-3" style="border: 1px solid #eee; border-left-width: 5px; border-radius: 3px; border-left-color: #428bca;">
									<a style="color: #428bca;">{{Déjà renseignés}} : </a>
									<ul id="state_order_list" class="connectedSortable" style="list-style-type: none; margin: 0; padding: 0; min-height:10em">
									</ul>
								</div>
								<div class="col-md-3 col-md-offset-1"  style="border: 1px solid #eee; border-left-width: 5px; border-radius: 3px; border-left-color: #d9534f;">
									<a style="color: #428bca;">{{A ajouter}} : </a>
									<ul id="state_order_list_template" class="connectedSortable" style="list-style-type: none; margin: 0; padding: 0; min-height:10em">
									</ul>
								</div>
								<div class="col-md-6"/>
							</div>						
					</fieldset>
				</form>
				<div>
				<!--</br>
				<a class="btn btn-info tooltips"  id="bt_presenceExport" title="{{Export des données}}"><i class="fa fa-download"></i>{{ Export}}</a><br/>-->
				</div>
			</div>
		</div>
		<div class="row" style="padding-left:25px;">
        <ul class="nav nav-tabs" id="tab_modes">
			<li><a class="btn tooltips" id="tab_add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a></li>
            <li><a href="#tab_vacances"><i class="fa fa-plane"></i> {{Vacances}} </a></li>
            <li><a href="#tab_simulation"><i class="loisir-marry"></i> {{Simulation}}</a></li>
            <li><a href="#tab_programmations"><i class="fa fa-pencil"></i> {{Paramètres}}</a></li>
			
        </ul>

        <div class="tab-content">
            <div class="tab-pane" id="tab_vacances">
				<br/><br/>
				<form class="form-horizontal">
					<legend>
                            {{Au départ :}} 
							<a class="btn btn-xs btn-success" id="bt_addActionDepart" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Action}}</a>
                    </legend>
                    <div id="div_action_depart"></div>
                </form>
				<form class="form-horizontal">
					<legend>
                            {{A l'arrivée :}}
							<a class="btn btn-success btn-xs" id="bt_addActionArrivee" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Action}}</a>
                    </legend>
                    <div id="div_action_arrivee"></div>
                </form>	
            </div>
			
			<div class="tab-pane" id="tab_simulation">
				<br/><br/>
				<form class="form-horizontal">
					<legend>
                            {{Heure de simulation :}} 
							<a class="btn btn-xs btn-success" id="bt_addCondSimu" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Heure}}</a>
                    </legend>
					<div id="div_cond_simu"></div>
				</form>
				<form class="form-horizontal">
					<legend>
                            {{Activation du mode :}} 
							<a class="btn btn-xs btn-success" id="bt_addActionSimuON" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Action}}</a>
                    </legend>
                    <div id="div_action_simulation_on"></div>
                </form>
				<form class="form-horizontal">
					<legend>
                            {{Réinitialisation du mode :}}
							<a class="btn btn-success btn-xs" id="bt_addActionSimuOFF" style="margin-left: 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter Action}}</a>
                    </legend>
                    <div id="div_action_simulation_off"></div>
                </form>	
			
            </div>

            <div class="tab-pane" id="tab_programmations">
                <br/>
				<form class="form-horizontal">
                    <div id="div_programmations"></div>
					<form class="form-horizontal">
						<div class="form-group">
							<fieldset class="col-md-6">
								<legend>{{Configuration}} </legend>
								<div>
									{{Garder le cache  lors de l'enregistrement}} : <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="cache_cleanup" checked/>{{Garder}}</label>
								</div>	
								<div style="padding-top:0.3em">
									{{Toujours exécuter les actions de retour de vacances}} : <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="execute_return_holliday" checked/>{{Actions}}</label> 
								</div>		
								<div  style="padding-top:0.3em">
									<p>{{Modes pour lesquels la simulation est active}} :
									<select id="simu_modes" multiple="multiple">
									</select>
									</p>
								</div>								
								
							</fieldset>
							<fieldset class="col-md-6">
								<legend>{{Affichage}} </legend>
								<div>
									<div>
										{{Modes à afficher}} :
										<select id="view_modes" multiple="multiple">
											<option value="Vacances">{{Vacances}}</option>
										</select>
									</div>
									<div style="padding-top:0.3em">
										{{Bouton de verrouilage}} : <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="lock_visible" checked/>{{Visible}}</label>
                                                                                
									</div>		
									<div style="padding-top:0.3em">
										{{Taille des icônes}} : <select id="icones_size"></select>
									</div>				
									<div style="padding-top:0.3em">
									{{Afficher le nom du mode sur le widget}} : <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="display_names" checked/>{{Afficher}}</label>
									</div>	
									<div style="padding-top:0.3em">
										{{Icônes sur les boutons}} : <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="button_display_name" checked/>{{Visible}}</label>
									</div>										
								</div>
							</fieldset>
						</div>
					</form>
                </form>	
            </div>
        </div>

        <br/><br/>
        <hr/>
        <form class="form-horizontal">
            <fieldset>
                <div class="form-actions">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>
            </fieldset>
        </form>
		</div>
    </div>
</div>
 
<?php include_file('desktop', 'presence', 'js', 'presence'); ?>
<?php include_file('desktop', 'DateTimePicker.min', 'js', 'presence'); ?>
<?php include_file('desktop', 'DateTimePicker.min', 'css', 'presence'); ?>
<?php include_file('desktop', 'multi-select.dist', 'css', 'presence'); ?>
<?php include_file('desktop', 'jquery.multi-select', 'js', 'presence'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>