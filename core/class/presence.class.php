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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class presence extends eqLogic {
    /*     * *************************Attributs****************************** */

    static $_time_tmp = 300;
    static $_last_declencheur = "";

    /*     * ***********************Methode static*************************** */

    public static function pull($_option) {
        $presence = presence::byId($_option['presence_id']);
        log::add('presence', 'debug', 'Objet mis à jour => ' . $_option['event_id'] . ' / ' . $_option['value']);
        if (is_object($presence) && $presence->getIsEnable() == 1) {
            $cache = cache::byKey('presence::' . $presence->getId() . '::' . $_option['event_id'], false, true);
            if ($cache->getValue() != $_option['value']) {
                cache::set('presence::' . $presence->getId() . '::' . $_option['event_id'], $_option['value'], 0);
                log::add('presence', 'debug', 'Changement détecté, Vérification des états');
                $presence->execute($_option['event_id'], $_option['value']);
            } else {
                log::add('presence', 'debug', 'Pas de changement, attente de la prochaine vérification');
            }
        }
    }

    public static function cron() {
        
    }

    public function Update_cron() {
        log::add('presence', 'info', 'Update_cron');
        foreach (eqLogic::byType('presence') as $eqLogic) {
            log::add('presence', 'debug', 'Update_cron :  check state of object');
            $eqLogic->check_state(0, 0);
        }
    }

    /*     * *********************Methode d'instance************************* */

    public function launch($_trigger_id, $_value) {
        return true;
    }

    public function preInsert() {
        
    }

    public function postSave() {
        log::add('presence', 'info', 'Enregistrement des modifications');

        $view_modes = $this->getConfiguration('modes_view');
        if ($view_modes == '') {
            $view_modes = 'Présent;Absent';
        } else {
            //log::add('presence','debug','Simu Manuel : ' . $simu_modes);
        }

        $tableau_view = explode(";", $view_modes);

        /// Cmd Mode
        $cmd = null;

        $_liste_maj_cmd = array();
        foreach ($this->getConfiguration('modes') as $key => $value) {
            array_push($_liste_maj_cmd, $value['name']);
        }
        array_push($_liste_maj_cmd, 'Mode');
        array_push($_liste_maj_cmd, 'Vacances');
        array_push($_liste_maj_cmd, 'Retour');
        array_push($_liste_maj_cmd, 'Retour_action');
        array_push($_liste_maj_cmd, 'last_declencheur');
        array_push($_liste_maj_cmd, 'lock_state');
        array_push($_liste_maj_cmd, 'unlock');
        array_push($_liste_maj_cmd, 'lock');

        log::add('presence', 'info', 'Supression des anciennes commandes');
        foreach ($this->getCmd() as $_cmd) {
            $_cmd_name = $_cmd->getLogicalId();
            log::add('presence', 'debug', '  - ' . $_cmd_name);
            if (!in_array($_cmd_name, $_liste_maj_cmd)) {
                log::add('presence', 'debug', '   => Supprimée');
                $_cmd->remove();
            }
        }

        $cmd = null;
        $cmd = $this->getCmd('info', 'Mode');
        if ($cmd == null) {
            $cmd = new presenceCmd();
            $cmd->setName('Mode');
            $cmd->setEqLogic_id($this->id);
            $cmd->setLogicalId('Mode');
            $cmd->setType('info');
            //$cmd->setTemplate('dashboard', 'plugin_presence');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->setTemplate('dashboard', 'mode_state');
            $cmd->setTemplate('mobile', 'mode_state');
            $cmd->setDisplay('generic_type', 'MODE_STATE');
            $cmd->setorder(1);
            $cmd->setSubType('string');
            $cmd->setEventOnly(1);
            $cmd->setIsVisible(1);
            $cmd->setIsHistorized(1);
            //$cmd->setValue(1);
            $cmd->save();
            //$cmd->event(1);
        } else {
            $cmd->setTemplate('dashboard', 'mode_state');
            $cmd->setTemplate('mobile', 'mode_state');
            if ($cmd->getDisplay('generic_type') == '') {
                $cmd->setDisplay('generic_type', 'MODE_STATE');
            }
            //$cmd->setTemplate('dashboard', 'plugin_presence');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->setSubType('string');
            $cmd->setIsHistorized(1);
            $cmd->setorder(1);
            $cmd->save();
        }

        foreach ($this->getConfiguration('modes') as $key => $value) {

            $cmd = $this->getCmd(null, $value['name']);
            if (!is_object($cmd)) {
                log::add('presence', 'debug', 'Création de ');
                log::add('presence', 'debug', $value['name']);
                $cmd = new presenceCmd();
            }
            $cmd->setName($value['name']);
            $cmd->setEqLogic_id($this->id);
            $cmd->setLogicalId($value['name']);
            $cmd->setType('action');
            $cmd->setDisplay('icon', $value['icon']);
            if ($cmd->getDisplay('generic_type') == '') {
                $cmd->setDisplay('generic_type', 'MODE_SET_STATE');
            }
            if ($this->getConfiguration('button_display_name', 1) == 0) {
                $cmd->setTemplate('dashboard', 'button_presence_string');
                //$cmd->setTemplate('mobile', 'default');
            } else {
                $cmd->setTemplate('dashboard', 'button_presence');
                //$cmd->setTemplate('mobile', 'button_presence');
            }
            $cmd->setorder(5);
            $cmd->setSubType('other');
            if (in_array($value['name'], $tableau_view)) {
                $cmd->setIsVisible(1);
            } else {
                $cmd->setIsVisible(0);
            }
            $cmd->save();
        }


        /// Cmd Vacances
        $cmd = null;

        $cmd = $this->getCmd('action', 'Vacances');
        if ($cmd == null) {
            $cmd = new presenceCmd();
            $cmd->setName('Vacances');
            $cmd->setLogicalId('Vacances');
            $cmd->setEqLogic_id($this->id);
            $cmd->setType('action');
            $cmd->setTemplate('dashboard', 'button_presence');
            //$cmd->setTemplate('mobile', 'button_presence');				
            $cmd->setDisplay('icon', '<i style="font-size:' . $this->getConfiguration('icones_size') . '" class="icon fa fa-plane"></i>');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            $cmd->setDisplay('generic_type', 'MODE_SET_STATE');
            $cmd->setorder(5);
            $cmd->setSubType('other');
            $cmd->setIsVisible(1);
            $cmd->setValue(5);
            $cmd->save();
            $cmd->event(5);
        } else {
            //$cmd->setTemplate('dashboard', 'plugin_presence');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            $cmd->setName($this->getConfiguration('vacances_name'));
            $cmd->setDisplay('icon', '<i style="font-size:' . $this->getConfiguration('icones_size') . '" class="icon fa fa-plane"></i>');
            if ($cmd->getDisplay('generic_type') == '') {
                $cmd->setDisplay('generic_type', 'MODE_SET_STATE');
            }
            if ($this->getConfiguration('button_display_name', 1) == 0) {
                $cmd->setTemplate('dashboard', 'button_presence_string');
                // $cmd->setTemplate('mobile', 'button_presence_string');
            } else {
                $cmd->setTemplate('dashboard', 'button_presence');
                // $cmd->setTemplate('mobile', 'button_presence');
            }
            $cmd->setorder(5);
            if (in_array("Vacances", $tableau_view)) {
                $cmd->setIsVisible(1);
                $cmd->save();
            } else {
                $cmd->setIsVisible(0);
                $cmd->save();
            }
        }
        /// Cmd date de retour
        $cmd = null;
        /* foreach ($this->getCmd() as $cmd_list) {
          if ($cmd_list->getName() == 'Retour') {
          $cmd = $cmd_list;
          break;
          }
          } */
        $cmd = $this->getCmd('info', 'Retour');
        if ($cmd == null) {
            $cmd = new presenceCmd();
            $cmd->setName('Retour');
            $cmd->setEqLogic_id($this->id);
            $cmd->setLogicalId('Retour');
            $cmd->setType('info');
            $cmd->setTemplate('dashboard', 'date_retour');
            $cmd->setTemplate('mobile', 'date_retour');
            $cmd->setTemplate('forceReturnLineBefore', '1');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            $cmd->setDisplay('generic_type', 'GENERIC');
            $cmd->setorder(6);
            $cmd->setSubType('other');
            $cmd->setEventOnly(1);
            $cmd->setIsVisible(0);
            $cmd->setValue("01-01-2000 00:00");
            $cmd->save();
            $cmd->event("01-01-2000 00:00");
        } else {
            $cmd->setTemplate('dashboard', 'date_retour');
            $cmd->setTemplate('mobile', 'date_retour');
            $cmd->setTemplate('forceReturnLineBefore', '1');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            if ($cmd->getDisplay('generic_type') == '') {
                $cmd->setDisplay('generic_type', 'GENERIC');
            }
            //$cmd->setIsVisible(0);
            $comeback = $this->getConfiguration("holiday_comeback", "01-01-2000 00:00");
            log::add('presence', 'debug', 'Retour sauvegardé ==> ' . $comeback . " Id de la commande :" . $cmd->getId());
            $cmd->setValue($comeback);
            $cmd->save();
            $cmd->event($comeback);
        }

        $cmd = $this->getCmd('action', 'Retour_action');
        if ($cmd == null) {
            $cmd = new presenceCmd();
            $cmd->setName('Modifier date retour');
            $cmd->setEqLogic_id($this->id);
            $cmd->setLogicalId('Retour_action');
            $cmd->setType('action');
            $cmd->setDisplay('generic_type', 'DONT');
            //$cmd->setTemplate('dashboard', 'plugin_presence');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            //$cmd->setorder(6);
            $cmd->setSubType('message');
            $cmd->setEventOnly(1);
            $cmd->setIsVisible(0);
            $cmd->setValue("01-01-2000 00:00");
            $cmd->save();
            $cmd->event("01-01-2000 00:00");
        } else {
            //$cmd->setTemplate('dashboard', 'plugin_presence');
            //$cmd->setTemplate('mobile', 'plugin_presence');
            $comeback = $this->getConfiguration("holiday_comeback", "01-01-2000 00:00");
            if ($cmd->getDisplay('generic_type') == '') {
                $cmd->setDisplay('generic_type', 'DONT');
            }
            //log::add('presence', 'debug', 'Comeback ==> ' . $comeback . " Id :" . $cmd->getId());
            $cmd->setValue($comeback);
            $cmd->save();
            $cmd->event($comeback);
        }

        $cmd = null;

        $last_declencheur = $this->getCmd(null, 'last_declencheur');
        if (!is_object($last_declencheur)) {
            $last_declencheur = new presenceCmd();
            //$last_declencheur->setTemplate('dashboard', 'lock');
            //$last_declencheur->setTemplate('mobile', 'lock');
        }
        $last_declencheur->setEqLogic_id($this->getId());
        $last_declencheur->setName(__('Dernier déclencheur', __FILE__));
        $last_declencheur->setType('info');
        $last_declencheur->setSubType('other');
        $last_declencheur->setLogicalId('last_declencheur');
        if ($last_declencheur->getDisplay('generic_type') == '') {
            $last_declencheur->setDisplay('generic_type', 'DONT');
        }
        $last_declencheur->setIsVisible(0);
        $last_declencheur->setEventOnly(1);
        $last_declencheur->setOrder(10);
        $last_declencheur->save();
        
        $lockState = $this->getCmd(null, 'lock_state');
        if (!is_object($lockState)) {
            $lockState = new presenceCmd();
            //$lockState->setTemplate('dashboard', 'plugin_presence_lock');
            //$lockState->setTemplate('mobile', 'plugin_presence_lock');
        }
        $lockState->setTemplate('dashboard', 'lock_state');
        $lockState->setTemplate('mobile', 'lock_state');
        if ($lockState->getDisplay('generic_type') == '') {
            $lockState->setDisplay('generic_type', 'ALARM_STATE');
        }
        $lockState->setEqLogic_id($this->getId());
        $lockState->setName(__('Verrouillage', __FILE__));
        $lockState->setType('info');
        $lockState->setSubType('binary');
        $lockState->setLogicalId('lock_state');
        //$lockState->setDisplay('forceReturnLineAfter', '1');
        $lockState->setIsVisible(0);
        $lockState->setEventOnly(1);
        $lockState->setOrder(6);
        $lockState->save();
        $lockState->setValue(0);
        //$lockState->event(0);

        $lock = $this->getCmd(null, 'lock');
        if (!is_object($lock)) {
            $lock = new presenceCmd();
            //$lock->setTemplate('dashboard', 'plugin_presence_smallLock');
            //$lock->setTemplate('mobile', 'plugin_presence_lock');
        }
        $lock->setTemplate('dashboard', 'locker');
        $lock->setTemplate('mobile', 'locker');
        if ($lock->getDisplay('generic_type') == '') {
            $lock->setDisplay('generic_type', 'ALARM_SET_MODE');
        }
        $lock->setEqLogic_id($this->getId());
        if ($this->getConfiguration('lock_visible') == "1") {
            $lock->setIsVisible(1);
        } else {
            $lock->setIsVisible(0);
        }
        $lock->setDisplay('forceReturnLineBefore', '1');
        $lock->setName('lock');
        $lock->setType('action');
        $lock->setSubType('other');
        $lock->setLogicalId('lock');

        $lock->setValue($lockState->getId());
        $lock->setOrder(6);
        $lock->save();

        $unlock = $this->getCmd(null, 'unlock');
        if (!is_object($unlock)) {
            $unlock = new presenceCmd();
            //$unlock->setTemplate('dashboard', 'plugin_presence_smallLock');
            //$unlock->setTemplate('mobile', 'plugin_presence_lock');
        }
        $unlock->setTemplate('dashboard', 'locker');
        $unlock->setTemplate('mobile', 'locker');
        if ($unlock->getDisplay('generic_type') == '') {
            $unlock->setDisplay('generic_type', 'ALARM_SET_MODE');
        }
        $unlock->setEqLogic_id($this->getId());
        $unlock->setName('unlock');
        $unlock->setType('action');
        $unlock->setSubType('other');
        $unlock->setLogicalId('unlock');
        //$unlock->setDisplay('forceReturnLineBefore', '1');
        if ($this->getConfiguration('lock_visible') == "1") {
            $unlock->setIsVisible(1);
        } else {
            $unlock->setIsVisible(0);
        }
        $unlock->setValue($lockState->getId());
        $unlock->setOrder(6);
        $unlock->save();

        log::add('presence', 'info', 'Activation des déclencheurs : ');
        $listener = listener::byClassAndFunction('presence', 'pull', array('presence_id' => intval($this->getId())));
        if (!is_object($listener)) {
            $listener = new listener();
        }
        $listener->setClass('presence');
        $listener->setFunction('pull');
        $listener->setOption(array('presence_id' => intval($this->getId())));
        $listener->emptyEvent();

        if (is_array($this->getConfiguration('modes'))) {
            foreach ($this->getConfiguration('modes') as $key => $value) {
                $jj = 0;
                $existing_mode[] = $value['name'];
                foreach ($value["condition"] as $_cond) {
                    if ($_cond['cmd'] != "#time#") {
                        $cmd = cmd::byId(str_replace('#', '', $_cond['cmd']));
                        $jj = $jj + 1;
                        if (!is_object($cmd)) {
                            throw new Exception(__('Commande déclencheur inconnue (present) : ' . $_cond['cmd'], __FILE__));
                        }
                        $listener->addEvent($_cond['cmd']);
                    }
                }
                log::add('presence', 'info', '--> ' . $jj . ' déclencheurs état ' . $value['name']);
            }
        }

        $listener->save();
    }

    public function preSave() {
        foreach ($this->getConfiguration('modes') as $key => $value) {
            foreach ($value['condition'] as $_cond) {
                if ($_cond['comp_value'] == '' || $_cond['waitDelay'] == '' || $_cond['cmd'] == '')
                    throw new Exception('Veuillez renseigner les déclencheurs / délais et états des conditions de l\'état ' . $value['name'] . '.');
            }
            foreach ($value['action'] as $_action) {
                if ($_action['cmd'] == '')
                    throw new Exception('Veuillez renseigner toutes les actions');
            }
            foreach ($value['action_exit'] as $_action_exit) {
                if ($_action_exit['cmd'] == '')
                    throw new Exception('Veuillez renseigner toutes les actions de sortie');
            }
        }
        $action_depart = $this->getConfiguration('action_depart');
        foreach ($action_depart as $_action) {
            if ($_action['cmd'] == '')
                throw new Exception('Veuillez renseigner toutes les actions');
        }
        $action_arrivee = $this->getConfiguration('action_arrivee');
        foreach ($action_arrivee as $_action) {
            if ($_action['cmd'] == '')
                throw new Exception('Veuillez renseigner toutes les actions');
        }

        $cond_simu = $this->getConfiguration('cond_simu');
        foreach ($cond_simu as $_cond_simu) {
            if ($_cond_simu['debut'] == '' || $_cond_simu['fin'] == '' || $_cond_simu['differe'] == '')
                throw new Exception('Veuillez renseigner toutes les conditions de simulation');
        }

        if ($this->getConfiguration('cache_cleanup', 0) == 0) {
            log::add('presence', 'info', 'Nettoyage du cache en cours ==> Les états peuvent être perturbés durant les premiers évènements.');
            $cache = cache::search('presence::', '');
            foreach ($cache as $_cache) {
                $_cache->remove();
            }
            cache::set('presence::' . $this->getId() . '::lock', 'false', 0);
        } else {
            log::add('presence', 'info', 'Nettoyage du cache non réalisé');
        }

        if ($this->getConfiguration('vacances_name') == "") {
            $this->setConfiguration('vacances_name', 'Vacances');
        }
    }

    public function preRemove() {
        $listener = listener::byClassAndFunction('presence', 'pull', array('presence_id' => intval($this->getId())));
        if (is_object($listener)) {
            $listener->remove();
        }
        $this->clear_vacances();
    }

    public function postUpdate() {
        
    }

    // évalue la fonction de comparaison et l'opération correspondante
    // $_operande contient la fonction de comparaison > < == != ~
    // $_object_value la valeur de l'objet à comparer
    // $_prog_value la valeur ciblée
    // la fonction effectue donc object_value operande prog_value et revoie TRUE ou FALSE

    public function check_compare($_object_value, $_operande, $_prog_value) {
        log::add('presence', 'debug', '    --> ' . $_object_value . $_operande . $_prog_value);
        switch ($_operande) {
            case ">" :
                if ($_object_value > $_prog_value) {
                    return true;
                }
                break;
            case "<" :
                if ($_object_value < $_prog_value) {
                    return true;
                }
                break;
            case "==" :
                if ($_object_value == $_prog_value) {
                    return true;
                }
                break;
            case "!=" :
                if ($_object_value != $_prog_value) {
                    return true;
                }
                break;
            case "~" :
                if (strstr($_object_value, $_prog_value)) {
                    return true;
                }
                break;
            default :
                break;
        }

        return false;
    }

    // détermine si c'est un scénario ou une action
    // si $_skip est FAUX, alors évalue si l'action est déjà exécutée en cache
    // $token contient la direction exec_depart ou exec_arrivee
    private function defineActionAndRun($_action, $_skip, $_token, $_exec, $_setcache) {
        $i = 0;

        if ($_action['cmd'] == 'scenario') {
            $_tmp_scenario = scenario::byID($_action['options']['scenario_id']);
            log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
            if ($_skip == false) {
                $allready_exec = cache::byKey('presence::' . $this->getId() . '::' . $_action['options']['scenario_id'] . '::' . $_token, false, true);
                $i = intal($allready_exec->getValue(0));
            }
        } else {
            $cmd = cmd::byId(str_replace('#', '', $_action['cmd']));
            log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getName());
            if ($_skip == false) {
                $allready_exec = cache::byKey('presence::' . $this->getId() . '::' . $cmd->getId() . '::' . $_token, false, true);
                $i = intval($allready_exec->getValue(0));
            }
        }
        if ($_exec) {
            try {
                scenarioExpression::createAndExec('action', str_replace('#', '', $_action['cmd']), $_action['options']);
            } catch (Exception $e) {
                log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }
        if ($_setcache) {
            if ($_action['cmd'] == 'scenario') {
                cache::set('presence::' . $this->getId() . '::' . $_action['options']['scenario_id'] . '::' . $_token, 1, 0);
            } else {
                cache::set('presence::' . $this->getId() . '::' . str_replace('#', '', $_action['cmd']) . '::' . $_token, 1, 0);
            }
        }
        return $i;
    }

    public function verification_triggers($_trigger_id, $_value, $_mode_name) {
        $traitement_temporaire_et = array();
        $traitement_temporaire_ou = array();
        $calcul_next_update = array();
        $cond_ok = false;

        //$cond = $this->getConfiguration('modes');

        foreach ($this->getConfiguration('modes') as $key => $value) {
            if ($value['name'] == $_mode_name) {
                log::add('presence', 'debug', 'verification_triggers : Mode=' . $value['name']);
                foreach ($value["condition"] as $condition) {
                    if (str_replace('#', '', $condition['cmd']) == strval($_trigger_id)) {
                        log::add('presence', 'debug', 'declenchement trigger : ' . $_trigger_id);
                        if ($condition['and'] == '1') {
                            log::add('presence', 'debug', 'Traitement des conditions de type  ET');
                            $cache = cache::byKey('presence::' . $this->getId() . '::' . $_trigger_id, false, true);
                            $datetime1 = date_create($cache->getDatetime());
                            $datetime1 = $datetime1->getTimestamp();
                            $datetime2 = time();
                            $interval = $datetime2 - $datetime1;

                            if ($this->check_compare($_value, $condition['operande'], $condition['comp_value'])) {
                                log::add('presence', 'debug', '    condition ET remplie');
                                log::add('presence', 'debug', '    déclenchement condition ET dans (s): ' . intval($interval));
                                if ($interval >= intval($condition['waitDelay'] * 60)) {
                                    log::add('presence', 'debug', '    Interval OK');
                                    $traitement_temporaire_et[str_replace('#', '', $condition['cmd'])] = 1;
                                } else {
                                    log::add('presence', 'debug', '    Interval KO');
                                    $traitement_temporaire_et[str_replace('#', '', $condition['cmd'])] = 0;
                                    $calcul_next_update[str_replace('#', '', $condition['cmd'])] = intval($condition['waitDelay'] * 60) - $interval;
                                }
                            } else {
                                log::add('presence', 'debug', '    Valeur non prise en compte');
                                $traitement_temporaire_et[str_replace('#', '', $condition['cmd'])] = 0;
                            }
                        } else if ($condition['and'] == '0') {
                            log::add('presence', 'debug', 'Traitement des conditions de type  OU');
                            $cache = cache::byKey('presence::' . $this->getId() . '::' . $_trigger_id, false, true);
                            $datetime1 = date_create($cache->getDatetime());
                            $datetime1 = $datetime1->getTimestamp();
                            $datetime2 = time();
                            $interval = $datetime2 - $datetime1;

                            if ($this->check_compare($_value, $condition['operande'], $condition['comp_value'])) {
                                log::add('presence', 'debug', '    condition OU remplie');
                                log::add('presence', 'debug', '    déclenchement condition OU dans (s): ' . intval($interval));
                                if ($interval >= intval($condition['waitDelay'] * 60)) {
                                    log::add('presence', 'debug', '    Interval OK');
                                    presence::$_last_declencheur = $_trigger_id;
                                    $traitement_temporaire_ou[str_replace('#', '', $condition['cmd'])] = 1;
                                } else {
                                    log::add('presence', 'debug', '    Interval KO');
                                    $traitement_temporaire_ou[str_replace('#', '', $condition['cmd'])] = 0;
                                    $calcul_next_update[str_replace('#', '', $condition['cmd'])] = intval($condition['waitDelay'] * 60) - $interval;
                                }
                            } else {
                                $traitement_temporaire_ou[str_replace('#', '', $condition['cmd'])] = 0;
                                log::add('presence', 'debug', '    Valeur KO');
                            }
                        } else {
                            log::add('presence', 'debug', 'trigger sans condition');
                            $cache = cache::byKey('presence::' . $this->getId() . '::' . $_trigger_id, false, true);
                            $datetime1 = date_create($cache->getDatetime());
                            $datetime1 = $datetime1->getTimestamp();
                            $datetime2 = time();
                            $interval = $datetime2 - $datetime1;

                            if ($this->check_compare($_value, $condition['operande'], $condition['comp_value'])) {
                                log::add('presence', 'debug', '    sans condition');
                                log::add('presence', 'debug', '    déclenchement inconditionnel dans (s): ' . intval($interval));
                                if ($interval >= intval($condition['waitDelay'] * 60)) {
                                    log::add('presence', 'debug', '    Interval OK  ==> Fin de vérification, passage au mode suivant');
                                    presence::$_last_declencheur = $_trigger_id;
                                    $cond_ok = true;
                                    return $cond_ok;
                                } else {
                                    log::add('presence', 'debug', '    Interval KO');
                                    $calcul_next_update[str_replace('#', '', $condition['cmd'])] = intval($condition['waitDelay'] * 60) - $interval;
                                }
                            } else {
                                log::add('presence', 'debug', '    Valeur KO');
                            }
                        }
                    } else {
                        log::add('presence', 'debug', 'Verification des autres déclencheurs : ' . str_replace('#', '', $condition['cmd']));
                        if ($condition['and'] == '1') {
                            log::add('presence', 'debug', 'condition de type ET');
                            if ($condition['cmd'] == "#time#") {
                                $_tmp_value = date("Hi");
                            } else {
                                $cache = cache::byKey('presence::' . $this->getId() . '::' . str_replace('#', '', $condition['cmd']), false, true);
                                $datetime1 = date_create($cache->getDatetime());
                                $datetime1 = $datetime1->getTimestamp();
                                $datetime2 = time();
                                $interval = $datetime2 - $datetime1;
                                log::add('presence', 'debug', '    condtion ET à déclencher dans (s): ' . intval($interval));
                                $tmp_cmd = cmd::byId(str_replace('#', '', $condition['cmd']));
                                $_tmp_value = $tmp_cmd->execCmd();
                            }

                            if ($this->check_compare($_tmp_value, $condition['operande'], $condition['comp_value'])) {
                                log::add('presence', 'debug', '    Valeur de déclenchement');
                                if ($interval >= intval($condition['waitDelay'] * 60)) {
                                    log::add('presence', 'debug', '    Interval OK');
                                    $traitement_temporaire_et[str_replace('#', '', $condition['cmd']) . '1'] = 1;
                                } else {
                                    log::add('presence', 'debug', '    Interval KO');
                                    $traitement_temporaire_et[str_replace('#', '', $condition['cmd']) . '0'] = 0;
                                    $calcul_next_update[str_replace('#', '', $condition['cmd'])] = intval($condition['waitDelay'] * 60) - $interval;
                                }
                            } else {
                                log::add('presence', 'debug', '    Valeur non prise en compte');
                                $traitement_temporaire_et[str_replace('#', '', $condition['cmd']) . '0'] = 0;
                            }
                        } else if ($condition['and'] == '0') {
                            log::add('presence', 'debug', 'condition de type OU');
                            if ($condition['cmd'] == "#time#") {
                                $_tmp_value = date("Hi");
                            } else {
                                $cache = cache::byKey('presence::' . $this->getId() . '::' . str_replace('#', '', $condition['cmd']), false, true);
                                $datetime1 = date_create($cache->getDatetime());
                                $datetime1 = $datetime1->getTimestamp();
                                $datetime2 = time();
                                $interval = $datetime2 - $datetime1;

                                $tmp_cmd = cmd::byId(str_replace('#', '', $condition['cmd']));
                                $_tmp_value = $tmp_cmd->execCmd();
                            }
                            if ($this->check_compare($_tmp_value, $condition['operande'], $condition['comp_value'])) {
                                log::add('presence', 'debug', '    Valeur de déclenchement');
                                log::add('presence', 'debug', '    condition OU à déclencher dans (s): ' . intval($interval));
                                if ($interval >= intval($condition['waitDelay'] * 60)) {
                                    log::add('presence', 'debug', '    Interval OK');
                                    presence::$_last_declencheur = str_replace('#', '', $condition['cmd']);
                                    $traitement_temporaire_ou[str_replace('#', '', $condition['cmd'])] = 1;
                                } else {
                                    log::add('presence', 'debug', '    Interval KO');
                                    $traitement_temporaire_ou[str_replace('#', '', $condition['cmd'])] = 0;
                                    $calcul_next_update[str_replace('#', '', $condition['cmd'])] = intval($condition['waitDelay'] * 60) - $interval;
                                }
                            } else {
                                $traitement_temporaire_ou[str_replace('#', '', $condition['cmd'])] = 0;
                                log::add('presence', 'debug', '    Valeur non prise en compte');
                            }
                        } else {
                            log::add('presence', 'debug', 'déclenchement inconditionnel');
                            log::add('presence', 'debug', 'condition = ' . $condition['cmd']);
                            if ($condition['cmd'] == "#time#") {
                                $_tmp_value = date("Hi");
                            } else {
                                $cache = cache::byKey('presence::' . $this->getId() . '::' . str_replace('#', '', $condition['cmd']), false, true);
                                $datetime1 = date_create($cache->getDatetime());
                                $datetime1 = $datetime1->getTimestamp();
                                $datetime2 = time();
                                $interval = $datetime2 - $datetime1;

                                $tmp_cmd = cmd::byId(str_replace('#', '', $condition['cmd']));
                                $_tmp_value = $tmp_cmd->execCmd();
                            }
                            if ($this->check_compare($_tmp_value, $condition['operande'], $condition['comp_value'])) {
                                log::add('presence', 'debug', '    Valeur de déclenchement');
                                log::add('presence', 'debug', '    déclenchement inconditionnel dans (s): ' . intval($interval));
                                if ($interval >= intval($condition['waitDelay'] * 60)) {
                                    log::add('presence', 'debug', '    Interval OK  ==> Fin de vérification, passage au mode suivant');
                                    presence::$_last_declencheur = str_replace('#', '', $condition['cmd']);
                                    $cond_ok = true;
                                    return $cond_ok;
                                } else {
                                    log::add('presence', 'debug', '    Interval KO');
                                    $calcul_next_update[str_replace('#', '', $condition['cmd'])] = intval($condition['waitDelay'] * 60) - $interval;
                                }
                            } else {
                                log::add('presence', 'debug', '    Valeur non prise en compte');
                            }
                        }
                    }
                } // Fin du foreach
            }
        }

        log::add('presence', 'debug', 'Vérification des conditions ET : ');
        if (sizeof($traitement_temporaire_et) >= 1) {
            log::add('presence', 'debug', 'Conditions trouvées, vérification en cours');
            $cond_ok = true;
            foreach ($traitement_temporaire_et as $k => $v) {
                if ($v == 0) {
                    log::add('presence', 'debug', '- Condition NOK : ' . $k);
                    $cond_ok = false;
                }
            }
            if ($cond_ok == true) {
                log::add('presence', 'debug', 'Toutes les conditions sont OK');
            }
        } else {
            log::add('presence', 'debug', 'Pas de conditions ET passage aux conditions OU');
        }

        if (sizeof($traitement_temporaire_ou) >= 1) {
            log::add('presence', 'debug', 'Au moins une condition OU trouvée');
            if ($cond_ok == false && sizeof($traitement_temporaire_et) >= 1) {
                log::add('presence', 'debug', 'Les conditions ET ne sont pas toutes respectées -- Pas de vérification');
                goto endofverification;
            }
            $cond_ok = false;
            foreach ($traitement_temporaire_ou as $k => $v) {
                log::add('presence', 'debug', '- Condition OU Vérif : ' . $k . ' / ' . $v);
                if ($v == 1) {
                    log::add('presence', 'debug', '- Condition OK : ' . $k);
                    $cond_ok = true;
                }
            }
        } else {
            log::add('presence', 'debug', 'Pas de conditions OU');
        }

        endofverification:
        log::add('presence', 'debug', 'Calcul pour prochain déclenchement, par défaut 300 secondes');
        // par défaut 300 secondes
        presence::$_time_tmp = 300;
        // si une des mises à jours nécessite un check avant on réduit le temps
        foreach ($calcul_next_update as $k => $v) {
            log::add('presence', 'debug', 'délai trouvé => ' . $v);
            if ($v <= presence::$_time_tmp) {
                log::add('presence', 'debug', 'réduction du délai à ' . $v);
                presence::$_time_tmp = $v;
            }
        }
        log::add('presence', 'debug', 'Prochain déclenchement dans ' . presence::$_time_tmp . ' secondes');

        return $cond_ok;
    }

    public function verification_vacances() {
        $_id = $this->getId();
        $calcul_next_update = array();
        $action_depart = $this->getConfiguration('action_depart');
        $action_arrivee = $this->getConfiguration('action_arrivee');
        log::add('presence', 'debug', 'Traitement du mode spécifique Vacances');

        $_locker_date_retour = cache::byKey('presence::' . $_id . '::locker_date_retour');
        if ($_locker_date_retour->getValue() > 0) {
            log::add('presence', 'debug', 'Date de retour présente à prendre en compte');
            $cmd_retour = $this->getCmd('info', 'Retour');
            $_datetime1 = DateTime::createFromFormat('d-m-Y H:i', $cmd_retour->getValue());
            $_datetime1 = $_datetime1->format('U');
            $_datetime2 = time();
            $_interval = $_datetime2 - $_datetime1;
            presence::$_time_tmp = 60;
            if (intval($_interval) < 0) {
                log::add('presence', 'debug', 'Déclenchement retour atteint');
                cache::set('presence::' . $_id . '::locker_date_retour', 0, 0);
            } else {
                log::add('presence', 'debug', 'Déclenchement retour non atteint');
                goto calculdeclenchement;
            }
        }
        $cmd_mode = $this->getCmd('info', 'Mode');
        $_cache = cache::byKey('presence::' . $_id . '::vacances_datetime');
        $cmd_retour = $this->getCmd('info', 'Retour');

        foreach ($action_depart as $_action_depart) {
            $v = $this->defineActionAndRun($action_depart, TRUE, 'exec_depart', FALSE, FALSE);
//            if ($_action_depart['cmd'] == 'scenario') {
//                $_tmp_scenario = scenario::byID($_action_depart['options']['scenario_id']);
//                log::add('presence', 'debug', 'Exécution du scénario ' . $_action_depart['options']['scenario_id'].' '.$_tmp_scenario->getName());
////                log::add('presence', 'debug', 'déclenchement scenario : ' . $_action_depart['options']['scenario_id']);
//                $allready_exec = cache::byKey('presence::' . $_id . '::' . $_action_depart['options']['scenario_id'] . '::exec_depart', false, true);
//            } else {
//                $cmd = cmd::byId(str_replace('#', '', $_action_depart['cmd']));
//                log::add('presence', 'debug', 'déclenchement commande : ' . $cmd->getId().' '.$cmd->getName());
//                $allready_exec = cache::byKey('presence::' . $_id . '::' . $cmd->getId() . '::exec_depart', false, true);
//            }
//            log::add('presence', 'debug', 'Allready : ' . $allready_exec->getValue(0));
            log::add('presence', 'debug', 'Allready vacances : ' . $v);
            if ($v != 1) {
                try {
                    $datetime1 = $_cache->getValue();
                    $datetime2 = time();
                    $interval = $datetime2 - $datetime1;
                    log::add('presence', 'debug', 'datetime2 : ' . intval($datetime2) . ' / datetime1 : ' . intval($datetime1));
                    log::add('presence', 'debug', 'Interval (s): ' . intval($interval));
                    if ($interval > intval($_action_depart['waitDelay'] * 60)) {
                        log::add('presence', 'debug', 'On a atteint l\'heure de retour');
                        $v2 = $this->defineActionAndRun($action_depart, FALSE, 'exec_depart', TRUE, TRUE);
//                        if ($_action_depart['cmd'] == 'scenario') {
//                            $_tmp_scenario = scenario::byID($_action_depart['options']['scenario_id']);
//                            log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
////                            log::add('presence', 'info', 'Exécution du scénario ' . $_action_depart['options']['scenario_id']);
//                        } else {
//                            log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getHumanName());
//                        }
//                        try {
//                            scenarioExpression::createAndExec('action', str_replace('#', '', $_action_depart['cmd']), $_action_depart['options']);
//                        } catch (Exception $e) {
//                            log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action_depart['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
//                        }

                        /* log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getHumanName());
                          $options = array();
                          if (isset($_action_depart['options'])) {
                          $options = $_action_depart['options'];
                          }
                          $cmd->execCmd($options);
                         */
//                        if ($_action_depart['cmd'] == 'scenario') {
//                            cache::set('presence::' . $_id . '::' . $_action_depart['options']['scenario_id'] . '::exec_depart', 1, 0);
//                        } else {
//                            cache::set('presence::' . $_id . '::' . str_replace('#', '', $_action_depart['cmd']) . '::exec_depart', 1, 0);
//                        }
                    } else {
                        log::add('presence', 'debug', 'Délai NOK');
                        $calcul_next_update[str_replace('#', '', $_action_depart['cmd'])] = intval($_action_depart['waitDelay'] * 60) - $interval;
                    }
                } catch (Exception $e) {

                    log::add('presence', 'error', 'Erreur lors de l\'exécution : ' . str_replace('#', '', $_action_depart['cmd']) . ' Info : ' . $e->getMessage());
                }
                //}
            }
        }

        log::add('presence', 'debug', '--- Retour ---');
        log::add('presence', 'debug', 'Date programmée : ' . $cmd_retour->getValue());
        foreach ($action_arrivee as $_action_arrivee) {
            if ($_action_arrivee['cmd'] == 'scenario') {
                $_tmp_scenario = scenario::byID($_action_arrivee['options']['scenario_id']);
                log::add('presence', 'info', 'Scénario trouvé : ' . $_tmp_scenario->getName());
//                log::add('presence', 'debug', 'Scenario : ' . $_action_arrivee['options']['scenario_id']);
                $allready_exec = cache::byKey('presence::' . $_id . '::' . $_action_arrivee['options']['scenario_id'] . '::exec_arrivee', false, true);
            } else {
                $cmd = cmd::byId(str_replace('#', '', $_action_arrivee['cmd']));
                log::add('presence', 'debug', 'Cmd : ' . $cmd->hgetId());
                $allready_exec = cache::byKey('presence::' . $_id . '::' . $cmd->getId() . '::exec_arrivee', false, true);
            }

            if (intval($allready_exec->getValue(0)) != 1) {
                //if (is_object($cmd)) {
                try {
                    $datetime1 = DateTime::createFromFormat('d-m-Y H:i', $cmd_retour->getValue());
                    $datetime1 = $datetime1->format('U');
                    $datetime2 = time();
                    $interval = $datetime2 - $datetime1;
                    log::add('presence', 'debug', 'datetime1 (s): ' . $datetime1 . ' datetime2 (s): ' . $datetime2 . ' Interval (s): ' . intval($interval) . ' WaitTime (s): ' . intval('-' . $_action_arrivee['waitDelay'] * 60));
                    if ($interval > intval('-' . $_action_arrivee['waitDelay'] * 60)) {
                        log::add('presence', 'debug', 'Délai de déclenchement OK');
                        $v = $this->defineActionAndRun($action_arrivee, FALSE, 'exec_arrivee', TRUE, TRUE);
//                        if ($_action_arrivee['cmd'] == 'scenario') {
//                            $_tmp_scenario = scenario::byID($_action_arrivee['options']['scenario_id']);
//                            log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
////                            log::add('presence', 'info', 'Exécution du scénario ' . $_action_arrivee['options']['scenario_id']);
//                        } else {
//                            log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getHumanName());
//                        }
//                        try {
//                            scenarioExpression::createAndExec('action', str_replace('#', '', $_action_arrivee['cmd']), $_action_arrivee['options']);
//                        } catch (Exception $e) {
//                            log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action_arrivee['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
//                        }

                        /* $options = array();
                          if (isset($_action_arrivee['options'])) {
                          $options = $_action_arrivee['options'];
                          }
                          $cmd->execCmd($options); */
//                        cache::set('presence::' . $_id . '::' . str_replace('#', '', $_action_arrivee['cmd']) . '::exec_arrivee', 1, 0);
                    } else {
                        log::add('presence', 'debug', 'Délai non atteint, on patiente avant lancement');
                        $calcul_next_update[str_replace('#', '', $_action_arrivee['cmd'])] = intval($_action_arrivee['waitDelay'] * 60) - $interval;
                    }
                } catch (Exception $e) {
                    log::add('presence', 'error', 'Erreur lors de l\'exécution : ' . str_replace('#', '', $_action_arrivee['cmd']) . ' Info : ' . $e->getMessage());
                }
                //}
            }
        }

        presence::$_time_tmp = 300;
        calculdeclenchement:
        log::add('presence', 'debug', 'Calcul pour prochain déclenchement : ');
        foreach ($calcul_next_update as $k => $v) {
            log::add('presence', 'debug', 'délai => ' . $v);
            if ($v <= presence::$_time_tmp) {
                presence::$_time_tmp = $v;
            }
        }

        $cmd_retour = $this->getCmd('info', 'Retour');
        $_datetime1 = DateTime::createFromFormat('d-m-Y H:i', $cmd_retour->getValue());
        $_datetime1 = $_datetime1->format('U');
        $_datetime2 = time();
        $_interval = $_datetime2 - $_datetime1;
        if (intval($interval) > 0) {
            cache::set('presence::' . $_id . '::locker_vacances', 0, 0);
            log::add('presence', 'info', ' ==> Mode vacances terminé');
            $cmd_retour->setIsVisible(0);
            $cmd_retour->save();
        }

        log::add('presence', 'debug', '==> Dans ' . presence::$_time_tmp . ' secondes');
    }

    public function clear_vacances() {

        log::add('presence', 'info', 'RAZ des déclencheurs vacances');

        $_id = $this->getId();
        $action_depart = $this->getConfiguration('action_depart');
        $action_arrivee = $this->getConfiguration('action_arrivee');

        foreach ($action_depart as $_action_depart) {
            if ($_action_depart['cmd'] == 'scenario') {
                cache::set('presence::' . $_id . '::' . $_action_depart['options']['scenario_id'] . '::exec_depart', 0, 0);
            } else {
                $cmd = cmd::byId(str_replace('#', '', $_action_depart['cmd']));
                cache::set('presence::' . $_id . '::' . $cmd->getId() . '::exec_depart', 0, 0);
            }
        }

        foreach ($action_arrivee as $_action_arrivee) {
            if ($_action_arrivee['cmd'] == 'scenario') {
                cache::set('presence::' . $_id . '::' . $_action_arrivee['options']['scenario_id'] . '::exec_arrivee', 0, 0);
            } else {
                $cmd = cmd::byId(str_replace('#', '', $_action_arrivee['cmd']));
                cache::set('presence::' . $_id . '::' . $cmd->getId() . '::exec_arrivee', 0, 0);
            }
        }

        //log::add('presence', 'debug', 'FIN du RAZ');
        $cmd_retour = $this->getCmd('info', 'Retour');
        //log::add('presence', 'debug', $cmd_retour->getValue());
        // date/heure de retour
        $_datetime1 = DateTime::createFromFormat('d-m-Y H:i', $cmd_retour->getValue());
        $_datetime1 = $_datetime1->format('U');
        // date/heure courante
        $_datetime2 = time();
        //log::add('presence','debug','datetime1 (s): ' . $_datetime1);
        //log::add('presence','debug','datetime2 (s): ' . $_datetime2);
        // différence en seconde entre les deux dates
        $_interval = $_datetime2 - $_datetime1;
        log::add('presence', 'debug', 'Interval (s): ' . intval($_interval));
        if (intval($_interval) > 0) {
            message::add('Présence', 'Veuillez renseigner une date de retour ultérieure.', null, null);
            cache::set('presence::' . $_id . '::locker_date_retour', 1, 0);
            cache::set('presence::' . $_id . '::locker_vacances', 1, 0);
        } else {
            cache::set('presence::' . $_id . '::locker_date_retour', 0, 0);
            cache::set('presence::' . $_id . '::locker_vacances', 1, 0);
        }
        //log::add('presence', 'debug', 'FIN du retour');
        cache::set('presence::' . $_id . '::vacances_datetime', time(), 0);
    }

    public function lancement_actions($mode, $old_mode) {
        log::add('presence', 'debug', 'Lancement des actions : entrée en mode (' . $mode . '), sortie du mode (' . $old_mode.')');
        $play_retour_actions = $this->getConfiguration('execute_return_holliday');
        $_id = $this->getId();

        if ($old_mode != "")
            log::add('presence', 'info', 'Déclenchement des actions de sortie du mode ' . $old_mode);
        if ($old_mode == "Vacances" && $play_retour_actions == "1") {
            $action_arrivee = $this->getConfiguration('action_arrivee');
            log::add('presence', 'debug', '--- Retour ---');
            foreach ($action_arrivee as $_action_arrivee) {
                if ($_action_arrivee['cmd'] == 'scenario') {
                    $_tmp_scenario = scenario::byID($_action_arrivee['options']['scenario_id']);
                    log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
                    $allready_exec = cache::byKey('presence::' . $_id . '::' . $_action_arrivee['options']['scenario_id'] . '::exec_arrivee', false, true);
                } else {
                    $cmd = cmd::byId(str_replace('#', '', $_action_arrivee['cmd']));
                    log::add('presence', 'debug', 'Cmd : ' . $cmd->getId());
                    $allready_exec = cache::byKey('presence::' . $_id . '::' . $cmd->getId() . '::exec_arrivee', false, true);
                }

                if (intval($allready_exec->getValue(0)) != 1) {
                    try {
                        log::add('presence', 'debug', 'Délai OK');
                        if ($_action_arrivee['cmd'] == 'scenario') {
                            $_tmp_scenario = scenario::byID($_action_arrivee['options']['scenario_id']);
                            log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
                        } else {
                            $cmd = cmd::byId(str_replace('#', '', $_action_arrivee['cmd']));
                            log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getName());
                        }
                        try {
                            $options = array();
                            if (isset($_action_arrivee['options'])) {
                                $options = $_action_arrivee['options'];
                            }
                            scenarioExpression::createAndExec('action', str_replace('#', '', $_action_arrivee['cmd']), $options);
                        } catch (Exception $e) {
                            log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action_arrivee['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                        }
                        cache::set('presence::' . $_id . '::' . str_replace('#', '', $_action_arrivee['cmd']) . '::exec_arrivee', 1, 0);
                    } catch (Exception $e) {
                        log::add('presence', 'error', 'Erreur');
                    }
                }
            }
        } else {
            $exitactions = $this->getConfiguration('modes');
            if ($exitactions != NULL)
                log::add('presence', 'debug', 'Traitement des actions de sortie du mode '.$old_mode);
            foreach ($this->getConfiguration('modes') as $key => $value) {
                if ($value['name'] == $old_mode) {
                    foreach ($value['action_exit'] as $_action) {
                        try {
                            $options = array();
                            if (isset($_action['options'])) {
                                $options = $_action['options'];
                            }
                            log::add('presence', 'debug', 'Lancement de : ' . str_replace('#', '', $_action['cmd']) . ' ' . $options);
                            scenarioExpression::createAndExec('action', str_replace('#', '', $_action['cmd']), $options);
                        } catch (Exception $e) {
                            log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                        }
                    }
                }
            }
        }
        log::add('presence', 'info', 'Déclenchement des actions d\'entrée en mode ' . $mode);
        if ($mode == "Vacances") {
            $action_depart = $this->getConfiguration('action_depart');
            foreach ($action_depart as $_action) {
                try {
                    if ($_action['cmd'] == 'scenario') {
                        $_tmp_scenario = scenario::byID($_action['options']['scenario_id']);
                        log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
                    } else {
                        $cmd = cmd::byId(str_replace('#', '', $_action['cmd']));
                        log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getName());
                    }
                    try {
                        $options = array();
                        if (isset($_action['options'])) {
                            $options = $_action['options'];
                        }
                        scenarioExpression::createAndExec('action', str_replace('#', '', $_action['cmd']), $options);
                    } catch (Exception $e) {
                        log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                    }

                    //log::add('presence', 'debug', 'Lancement de : ' . $_action['cmd'] . $_action['options']);
                    //scenarioExpression::createAndExec('action', $_action['cmd'], $_action['options']);
                } catch (Exception $e) {
                    log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                }
            }
        } else {
            foreach ($this->getConfiguration('modes') as $key => $value) {
                if ($value['name'] == $mode) {
                    foreach ($value['action'] as $_action) {
                        try {
                            if ($_action['cmd'] == 'scenario') {
                                $_tmp_scenario = scenario::byID($_action['options']['scenario_id']);
                                log::add('presence', 'info', 'Exécution du scénario ' . $_tmp_scenario->getName());
                            } else {
                                $cmd = cmd::byId(str_replace('#', '', $_action['cmd']));
                                log::add('presence', 'info', 'Exécution de la commande ' . $cmd->getName());
                            }
                            try {
                                $options = array();
                                if (isset($_action['options'])) {
                                    $options = $_action['options'];
                                }
                                scenarioExpression::createAndExec('action', str_replace('#', '', $_action['cmd']), $options);
                            } catch (Exception $e) {
                                log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                            }
                        } catch (Exception $e) {
                            log::add('presence', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $_action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    public function check_state($_trigger_id, $_value) {
        try {
            log::add('presence', 'debug', 'CheckState avec paramètres : ');
            log::add('presence', 'debug', 'Trigger : ' . $_trigger_id . ' / Value : ' . $_value);

            $last_declencheur = $this->getCmd(null, 'last_declencheur');

            //cmd = null;
            $cmd = $this->getCmd('info', 'Mode');
            $mode = $cmd->getValue();
            log::add('presence', 'debug', 'Mode actuel : ' . $mode);

            $cond_present_ok = false;
            $cond_absent_ok = false;
            $cond_nuit_ok = false;
            $cond_travail_ok = false;

            $lockState = $this->getCmd(null, 'lock_state');
            if (!is_object($lockState) || $lockState->execCmd() == 1) {
                log::add('presence', 'debug', 'Pas de vérification ==> Mode manuel');
                goto endofcommand;
            }

            $conditions_states = [];

            $_locker_vacances = cache::byKey('presence::' . $this->getId() . '::locker_vacances');

            if ($mode == "Vacances" && intval($_locker_vacances->getValue()) == 1) {
                log::add('presence', 'debug', 'Je suis en vacances');
                $this->verification_vacances();
                goto endofcommand;
            } else {
                if (is_array($this->getConfiguration('modes'))) {
                    log::add('presence', 'debug', 'Modes : ');
                    foreach ($this->getConfiguration('modes') as $key => $value) {
                        $conditions_states[$value['name']] = $this->verification_triggers($_trigger_id, $_value, $value['name']);
                    }
                }
            }
            log::add('presence', 'debug', '-------- Fin des Vérifications --------');
            log::add('presence', 'debug', 'Etat des conditions : ');
            foreach ($conditions_states as $key => $value) {
                log::add('presence', 'debug', '    - ' . $key . ' : ' . $value);
            }

            log::add('presence', 'debug', '-------- Gestion de l\'ordre   --------');
            $state_order = $this->getConfiguration('state_order');
            if ($state_order == '') {
                message::add('Présence', "Veuillez renseigner la priorité des modes", null, null);
                goto endofcommand;
            } else {
                log::add('presence', 'debug', 'Ordre utilisé : ' . $state_order);
            }
            $tableau_ordre = explode(";", $state_order);
            $i = 0;
            for ($i; $i < count($tableau_ordre); $i++) {
                log::add('presence', 'debug', 'Ordre : ' . $tableau_ordre[$i]);
                if ($conditions_states[$tableau_ordre[$i]] == 1) {
                    log::add('presence', 'debug', 'Test condition OK ');
                    if ($mode != $tableau_ordre[$i]) {
                        $cmd->setValue($tableau_ordre[$i]);
                        $cmd->save();
                        $cmd->event($tableau_ordre[$i]);
                        $cmd->setCollectDate(date('Y-m-d H:i:s'));
                        log::add('presence', 'info', 'Changement du mode => ' . $tableau_ordre[$i]);
                        $this->lancement_actions($tableau_ordre[$i], $mode);
                        //goto endofcommand;
                    } else {
                        log::add('presence', 'info', 'Pas besoin de changement mode');
                    }
                    break;
                }
            }

            endofcommand:
            if (presence::$_last_declencheur != NULL) {
                log::add('presence', 'debug', 'Mise à jour du dernier déclencheur ' . presence::$_last_declencheur);
                $last_declencheur->setValue(presence::$_last_declencheur);
                $last_declencheur->event(presence::$_last_declencheur);
                $last_declencheur->save();
            }
            log::add('presence', 'debug', '------- Gestion de la simulation  ------');
            $simu_modes = $this->getConfiguration('simulation_modes');
            if ($simu_modes == '') {
                $simu_modes = '5';
                log::add('presence', 'debug', 'Simulation en mode automatique (en mode vacances)');
            } else {
                log::add('presence', 'debug', 'Simulation activée dans les paramètres sur le mode : ' . $simu_modes);
            }

            $tableau_simu = explode(";", $simu_modes);

            foreach ($tableau_simu as &$value) {
                if ($value == $mode) {
                    $this->simu_presence();
                }
            }
            //log::add('presence','debug','==> _time_tmp : ' . presence::$_time_tmp);
            if (presence::$_time_tmp <= 60)
                presence::$_time_tmp = 60;

            presence::$_time_tmp = intval(presence::$_time_tmp / 60);

            presence::$_time_tmp = date('i') + presence::$_time_tmp;
            if (presence::$_time_tmp >= 60) {
                presence::$_time_tmp = 00;
            }
            $cron = cron::byClassAndFunction('presence', 'Update_cron');
            if (!is_object($cron)) {
                $cron = new cron();
                $cron->setClass('presence');
                $cron->setFunction('Update_cron');
                $cron->setEnable(1);
                $cron->setDeamon(0);
                $cron->setSchedule(presence::$_time_tmp . ' * * * *');
                $cron->save();
            } else {
                $cron->setSchedule(presence::$_time_tmp . ' * * * *');
                $cron->save();
            }
            if (date('i') < presence::$_time_tmp)
                $_time_hour = date('H');
            else
                $_time_hour = date('H') + 1;
            if ($_time_hour > 23)
                $_time_hour -= 23;
            log::add('presence', 'debug', '==> Prochain check auto : ' . $_time_hour . ':' . (presence::$_time_tmp <= 9 ? '0' : '') . presence::$_time_tmp);
            log::add('presence', 'debug', '-----------------------------------------------------------------------');
            $this->refreshWidget();
        } catch (Exception $e) {
            log::add('presence', 'error', 'Problème dans la vérification des états' . $e);
        }
    }

    public function manual_update() {
        
    }

    public function simu_presence() {
        $simulation = $this->getConfiguration('cond_simu');
        foreach ($simulation as $_simulation) {
            log::add('presence', 'debug', 'lecture des infos de simulation');
            $start = explode(":", $_simulation['debut']);
            $stop = explode(":", $_simulation['fin']);

            log::add('presence', 'debug', 'Début : ' . $start[0] . ':' . $start[1] . ' / Fin : ' . $stop[0] . ':' . $stop[1] . ' / Différé max : ' . $_simulation['differe'].'mn');
            $cacheKey = 'presence::' . $this->getId() . $start[0] . $start[1] . $stop[0] . $stop[1] . $_simulation['differe'] . '::simulation';

            // lecture du différé
            $cacheKey1 = 'presence::' . $this->getId() . $start[0] . $start[1] . $stop[0] . $stop[1] . $_simulation['differe'] . '::simulation_differee';
            $cache = cache::byKey($cacheKey1, false, true);
            // s'il n'est pas défini on choisit une valeur aléatoire et on la met en cache
             if ($cache->getValue() == '') {
                // évalue la valeur aléatoire en minutes pour différer par rapport à l'heure de base 
                $differe = rand(0, $_simulation['differe']);
                cache::set($cacheKey1, $differe, 0);
                log::add('presence', 'debug', 'aucune valeur en cache, stockage du différé de '.$differe.' min');
             }
             else {
                $differe = intval($cache->getValue());
                log::add('presence', 'debug', 'lecture en cache de la valeur différée '.$differe.' min');
             }
            // start 0 = heure de départ
            // start 1 = minutes de départ
            // récupète le nombre de minutes de départ et ajoute le différé
            $start[1] = $start[1] + $differe;
            if ($start[1] > 59) {
                $start[1] -= 60;
                $start[0]++;
            }
            if ($start[0] > 23) {
                $start[0] = 0;
            }

            // corrigé, le décalage doit être uniforme sur le début et fin ...
            $stop[1] += $differe;
            if ($stop[1] > 59) {
                $stop[1] -= 60;
                $stop[0] += + 1;
            }
            if ($stop[0] > 23) {
                $stop[0] = 0;
            }
            log::add('presence', 'debug', 'Début effectif : ' . $start[0] . ':' . $start[1] . ' / Fin effective : ' . $stop[0] . ':' . $stop[1] );

            if (date('H') >= $start[0] && date('i') >= $start[1] && date('H') <= $stop[0] && date('i') <= $stop[1]) {
                $cache = cache::byKey($cacheKey, false, true);
//                if ($cache == "")
//                    log::add('presence', 'debug', 'aucune valeur en cache '.$cacheKey);
//                else
//                    log::add('presence', 'debug', 'cache = ' . $cacheKey . '=' . $cache->getValue());
                if ($cache->getValue() != 'inprocess') {
                    log::add('presence', 'debug', 'lancement des actions de début de simulation');
                    cache::set($cacheKey, 'inprocess', 0);
                    $this->lancement_actions('simulation_on', '');
                }
                log::add('presence', 'debug', '==> en cours');
            } else {
                $cache = cache::byKey($cacheKey, false, true);
                log::add('presence', 'debug', 'cache = ' . $cacheKey . '=' . $cache->getValue());
                $cache1 = cache::byKey($cacheKey1, false, true);
                log::add('presence', 'debug', 'cache1 = ' . $cacheKey1 . '=' . $cache1->getValue());
                if ($cache->getValue() == 'inprocess') {
                    log::add('presence', 'debug', 'lancement des actions de clôture de simulation');
                    //cache::set($cacheKey,0, 0);
                    $cache->remove();
                    $cache1 = cache::byKey($cacheKey1, false, true);
                    $cache1->remove();
                    $this->lancement_actions('simulation_off', '');
                }
                log::add('presence', 'debug', '==> arreté');
            }

            log::add('presence', 'debug', 'Calcul du prochain déclenchement du traitement de la simulation');
            // évalue le prochain déclenchement du test 
            // on prend la plus petite des dates entre 
            // heure de déclenchement - heure de fin et 300s (5mn)
            
            $stampStart = mktime($start[0], $start[1], 0, date("m"), date("d"), date("Y"));
            $actualtime = time();
            $delta = $stampStart - $actualtime;
           
//            $datetime1 = date("Y-m-d H:i:s", mktime($start[0], $start[1], 0, date("m"), date("d"), date("Y")));
//            $datetime1 = date_create($datetime1);
//            $datetime1 = $datetime1->getTimestamp();
//
//            $datetime2 = $actualtime;
//            $interval = $datetime1 - $datetime2;
            log::add('presence','debug','delta='.$delta.' time_tmp='.presence::$_time_tmp );

            if ($delta <= presence::$_time_tmp && $delta >= 0) {
                presence::$_time_tmp = $interval;
            }

            $stampEnd = mktime($stop[0], $stop[1], 0, date("m"), date("d"), date("Y"));
            $delta = $stampEnd - $actualtime;

//            $datetime1 = date("Y-m-d H:i:s", mktime($stop[0], $stop[1] + 1, 0, date("m"), date("d"), date("Y")));
//            $datetime1 = date_create($datetime1);
//            $datetime1 = $datetime1->getTimestamp();
//            $datetime2 = time();
//            $interval = $datetime1 - $datetime2;

            if ($delta <= presence::$_time_tmp && $delta >= 0) {
                presence::$_time_tmp = $interval;
            }

            log::add('presence', 'debug', '==> Dans ' . presence::$_time_tmp . ' secondes');
        }
    }

    public function execute($_trigger_id, $_value) {
        $id = $this->getId();
        $cache = cache::byKey('presence::' . $_id . '::lock', false, true);
        $lock_security = 0;
        if ($cache->getValue() != 'true') {
            cache::set('presence::' . $_id . '::lock', 'true', 0);
            log::add('presence', 'debug', 'Lancement de presence : ' . $_trigger_id . ' / value : ' . $_value);
            $this->check_state($_trigger_id, $_value);
            cache::set('presence::' . $_id . '::lock', 'false', 0);
            goto end_execute;
        } else {
            log::add('presence', 'debug', 'Déjà en cours d\'exécution, attente de la fin');
            while ($cache->getValue() != 'false'):
                $cache = cache::byKey('presence::' . $_id . '::lock', false, true);
                log::add('presence', 'debug', 'Boucle');
                if (($lock_security += 1) > 10) {
                    goto end_error_of_execute;
                }
                sleep(1);
            endwhile;
            log::add('presence', 'debug', 'Lancement de presence : ' . $_trigger_id . ' / value : ' . $_value);
            cache::set('presence::' . $_id . '::lock', 'true', 0);
            $this->check_state($_trigger_id, $_value);
            cache::set('presence::' . $_id . '::lock', 'false', 0);
            goto end_execute;
        }
        end_error_of_execute:
        log::add('presence', 'debug', 'Erreur dans la gestion des semaphores');
        end_execute:
    }

    public function doAction($_action) {
        
    }

    public function updateHollidayDate($date) {
        $_cmd = null;
        log::add('presence', 'debug', 'Changement de la date de retour : ' . $date);
        $_cmd = $this->getCmd('info', 'Retour');
        if ($_cmd != null) {
            log::add('presence', 'debug', 'Sauvegarde de la date de retour : ' . $date);
            $_cmd->setValue($date);
            $_cmd->save();
            $_cmd->event($date);
        } else
            log::add('presence', 'warning', 'date de retour non trouvée ! ');
        $this->setConfiguration("holiday_comeback", $date);
        $this->save();
    }

}

class presenceCmd extends cmd {
    /*     * *************************Attributs****************************** */

    public function dontRemoveCmd() {
        return true;
    }

    public function execute($_options = array()) {
        log::add('presence', 'debug', 'Changement de mode vers ' . $this->getLogicalId());
        $eqLogic = $this->getEqLogic();

        $lockState = $eqLogic->getCmd(null, 'lock_state');
        if ($this->getLogicalId() == 'lock') {
            $lockState->setCollectDate(date('Y-m-d H:i:s'));
            $lockState->event(1);
            $eqLogic->refreshWidget();
            return;
        }
        if ($this->getLogicalId() == 'last_declencheur') {
            $_last_declencheur = $eqLogic->getCmd(null, 'last_declencheur');
            log::add('presence', 'debug', 'Demande du dernier déclencheur: ' . $_last_declencheur->getValue());
            return;
        }

        if ($this->getLogicalId() == 'Mode') {
            $_mode = $eqLogic->getCmd(null, 'Mode');
            log::add('presence', 'debug', 'Demande du mode: ' . $_mode->getValue());
            return;
        }

        if ($this->getLogicalId() == 'unlock') {
            $lockState->setCollectDate(date('Y-m-d H:i:s'));
            $lockState->event(0);
            $eqLogic->refreshWidget();
            return;
        }
        if ($this->getLogicalId() == 'lock_state') {
            return;
        }

        if ($this->getLogicalId() == 'Retour_action') {
            log::add('presence', 'info', 'Demande de changement de date de retour ==> ' . $_options[message]);
            $eqLogic->updateHollidayDate($_options[message]);
        }

        if (!is_object($lockState) || $lockState->execCmd() == 0) {
            $cmd = $eqLogic->getCmd();
            foreach ($cmd as $cmd_list) {
                if ($cmd_list->getName() == 'Mode') {
                    $cmd = $cmd_list;
                    break;
                }
            }
            $old_mode = $cmd->getValue();
            log::add('presence', 'info', 'Mode précédent :' . $old_mode . ' / Mode choisi : ' . $this->getLogicalId());
            if ($cmd->getValue() != $this->getLogicalId()) {
                $cmd = $eqLogic->getCmd('info', 'Mode');
                log::add('presence', 'info', 'Changement manuel de mode : ' . $cmd->getId());
                $cmd->setValue($this->getLogicalId());
                $cmd->save();
                $cmd->event($this->getLogicalId());
                $cmd->setCollectDate(date('Y-m-d H:i:s'));
                if ($this->getLogicalId() != "Vacances") {
                    $eqLogic->lancement_actions($this->getLogicalId(), $old_mode);
                }
            }
            $cmd = null;
            foreach ($eqLogic->getCmd() as $cmd_list) {
                if ($cmd_list->getName() == 'Retour') {
                    $cmd = $cmd_list;
                    break;
                }
            }
            if ($this->getLogicalId() == "Vacances") {
                $cmd->setIsVisible(1);
                $eqLogic->clear_vacances();
                $cron = cron::byClassAndFunction('presence', 'Update_cron');
                if (!is_object($cron)) {
                    $cron = new cron();
                    $cron->setClass('presence');
                    $cron->setFunction('Update_cron');
                    $cron->setEnable(1);
                    $cron->setDeamon(0);
                    $cron->setSchedule('*/2 * * * *');
                    $cron->save();
                } else {
                    $cron->setSchedule('*/2 * * * *');
                    $cron->save();
                }
                $cmd->save();
                $eqLogic->refreshWidget();
            } else {
                $cmd->setIsVisible(0);
                $cmd->save();
                $eqLogic->refreshWidget();
            }
//            log::add('presence', 'event', 'Je passe dans le execmd');
        }
        $eqLogic->refreshWidget();
    }

    public function formatValueWidget($_mode) {
        $eqLogic = $this->getEqLogic();
        $str = '';
        if ($eqLogic->getConfiguration('display_names', 0) == 1) {
            $str = '<div style="font-weight: bold;font-size : 12px;">' . str_replace("_", " ", $_mode) . '</div>';
        }
        foreach ($eqLogic->getConfiguration('modes') as $key => $value) {
            if ($value['name'] == $_mode) {
                if (isset($value['icon']) && $value['icon'] != '') {
                    return $str . $value['icon'];
                }
            }
        }
        return $_mode;
    }
}
?>