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

/*if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
    header("Status: 404 Not Found");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    exit();
}*/

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
log::add('presence', 'info','----- JPresence -----');
if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

$array_recu = "";
foreach ($_GET as $key => $value){
    $array_recu = $array_recu . $key . $value . ' / ';
}

log::add('presence', 'debug', 'Trame recu ' . $array_recu);



log::add('presence', 'info','JPresence : Récupération argument ok');
$eqLogic = null;
$cmd = cmd::byId($_GET['cmd_id']);
$eqLogic_id = $cmd->getEqLogic_id();
$eqLogic = eqLogic::byId($eqLogic_id);

if (!is_object($eqLogic)) {
    log::add('presence', 'info', __('Presence non trouvé verifier id : ', __FILE__) . init('eqLogic_id'));
    die(__('Presence non trouvé vérifier id : ', __FILE__) . init('eqLogic_id'));
}
if ($eqLogic->getIsEnable() != 1) {
    die();
}

set_time_limit(120);

//$cmd = null;
/*foreach ($eqLogic->getCmd() as $cmd_list) {
	if ($cmd_list->getName() == 'Retour') {
		$cmd = $cmd_list;
		break;
	}
}*/
if ($cmd != null) {
	$cmd->setValue($_GET['date']);
	$cmd->event($_GET['date']);
	$cmd->save();
}
$eqLogic->setConfiguration("holiday_comeback",$_GET['date']);
$eqLogic->save();
$eqLogic->refreshWidget();
log::add('presence', 'info','JPresence : changement date retour');

?>
