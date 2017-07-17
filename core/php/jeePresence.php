<?php

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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
log::add('presence', 'info','Debut traitement JPresence');
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

log::add('presence', 'debug', 'Trame ' . json_encode($_GET, true));

if ($_GET['cmd_id']==NULL || $_GET['date']==NULL || $_GET['jeedom_token']==NULL) {
    log::add('presence', 'error', __('argument obligatoire non trouvé dans retour json'));
    die(__('arguments obligatoires non trouvés', __FILE__));
} 

$eqLogic = null;
$cmd = cmd::byId($_GET['cmd_id']);
$eqLogic_id = $cmd->getEqLogic_id();
$eqLogic = eqLogic::byId($eqLogic_id);

if (!is_object($eqLogic)) {
    // si l'ID n'existe pas
    log::add('presence', 'error', __('ID invalide ', __FILE__) . init('eqLogic_id'));
    die(__('ID invalide ', __FILE__) . init('eqLogic_id'));
}
if ($eqLogic->getIsEnable() != 1) {
    // si l'objet de présence n'est pas actif
    die();
}

// limite l'exécution à 60 secondes
set_time_limit(60);

if ($cmd != null) {
	$cmd->setValue($_GET['date']);
	$cmd->event($_GET['date']);
	$cmd->save();
}
$eqLogic->setConfiguration("holiday_comeback",$_GET['date']);
$eqLogic->save();
$eqLogic->refreshWidget();
log::add('presence', 'info','fin traitement JPresence');
?>
