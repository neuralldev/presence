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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function presence_update() {
    //$myPlugin = plugin::byId('presence');
    log::add('presence', 'info', '*****************************************************');
    log::add('presence', 'info', '*********** Mise à jour du plugin Presence **********');
    log::add('presence', 'info', '*****************************************************');
    log::add('presence', 'info', '*			Core version    : 3.000					*');
    log::add('presence', 'info', '*			Desktop version : 3.000					*');
    log::add('presence', 'info', '*			Mobile version  : 1.000					*');
    log::add('presence', 'info', '*****************************************************');

    config::save('presence_core_version', '3.000', 'presence');
    config::save('presence_desktop_version', '3.000', 'presence');
    config::save('presence_mobile_version', '1.000', 'presence');
    // log::add('presence','info','Maj du plugin');
    $cron = cron::byClassAndFunction('presence', 'Update_cron');
    // le plugin utilise une programmation répétitive via CRON
    // par défaut on règle la répétition à toutes les 2 mn
    // ps : il n'y a pas de deamon 
    if (!is_object($cron)) {
        message::add('Présence', 'Création du cron en cours...', null, null);
        $cron = new cron();
        $cron->setClass('presence');
        $cron->setFunction('Update_cron');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('*/2 * * * *');
        $cron->save();
    } else {
        message::add('Présence', 'Mise à jour cron en cours...', null, null);
        $cron->setSchedule('*/2 * * * *');
        $cron->save();
    }
    message::add('Présence', 'Mise à jour en cours...', null, null);
    foreach (eqLogic::byType('presence') as $_eqLogic) {
        log::add('presence', 'info', 'Objet :');
        log::add('presence', 'info', '       ' . $_eqLogic->getHumanName());
        if (is_object($_eqLogic)) {
            log::add('presence', 'info', 'Récupération des paramètres');
            $my_mode = array();
            if (!is_array($_eqLogic->getConfiguration('modes'))) {
                message::add('Présence', 'Lancement de la migration...', null, null);
                log::add('presence', 'info', 'Migration nécessaire');
// traitement du mode présent
                $condition = $_eqLogic->getConfiguration('condition_present');
                $action = $_eqLogic->getConfiguration('action_present');
                $action_exit = $_eqLogic->getConfiguration('action_exit_present');
                array_push($my_mode, array("icon" => "", "name" => "Présent", "condition" => $condition, "action" => $action, "action_exit" => $action_exit));
// traitement du mode Absent
                $condition = $_eqLogic->getConfiguration('condition_absent');
                $action = $_eqLogic->getConfiguration('action_absent');
                $action_exit = $_eqLogic->getConfiguration('action_exit_absent');
                array_push($my_mode, array("icon" => "", "name" => "Absent", "condition" => $condition, "action" => $action, "action_exit" => $action_exit));
// traitement du mode travail
                $condition = $_eqLogic->getConfiguration('condition_travail');
                $action = $_eqLogic->getConfiguration('action_travail');
                $action_exit = $_eqLogic->getConfiguration('action_exit_travail');
                array_push($my_mode, array("icon" => "", "name" => "Travail", "condition" => $condition, "action" => $action, "action_exit" => $action_exit));
// traitement du mode nuit
                $condition = $_eqLogic->getConfiguration('condition_nuit');
                $action = $_eqLogic->getConfiguration('action_nuit');
                $action_exit = $_eqLogic->getConfiguration('action_exit_nuit');
                array_push($my_mode, array("icon" => "", "name" => "Nuit", "condition" => $condition, "action" => $action, "action_exit" => $action_exit));
// écriture en base des nouveaux paramètres
                log::add('presence', 'info', 'Sauvegarde du nouvel objet');
                $_eqLogic->setConfiguration('modes', $my_mode);
                $_eqLogic->save();
            } else
                log::add('presence', 'info', 'Version des paramètres déjà compatible avec Jeedom V3');
            // effacement préventif des vielles variables de configuration utilisées avant la V2.4
            $_eqLogic->setConfiguration('condition_present', '');
            $_eqLogic->setConfiguration('action_present', '');
            $_eqLogic->setConfiguration('action_exit_present', '');
            $_eqLogic->setConfiguration('condition_absent', '');
            $_eqLogic->setConfiguration('action_absent', '');
            $_eqLogic->setConfiguration('action_exit_absent', '');
            $_eqLogic->setConfiguration('condition_travail', '');
            $_eqLogic->setConfiguration('action_travail', '');
            $_eqLogic->setConfiguration('action_exit_travail', '');
            $_eqLogic->setConfiguration('condition_nuit', '');
            $_eqLogic->setConfiguration('action_nuit', '');
            $_eqLogic->setConfiguration('action_exit_nuit', '');
            $_eqLogic->save();
        }
        // efface tous les message du module du centre de présence
        message::removeAll('Présence');
        message::add('Présence', 'Mise à jour terminée', null, null);
    }
}

function presence_remove() {
    $cron = cron::byClassAndFunction('presence', 'Update_cron');
    if (is_object($cron)) {
        $cron->remove();
    }
}

function presence_install() {
    $cron = cron::byClassAndFunction('presence', 'Update_cron');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('presence');
        $cron->setFunction('Update_cron');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('*/2 * * * *');
        $cron->save();
    }
}

?>
