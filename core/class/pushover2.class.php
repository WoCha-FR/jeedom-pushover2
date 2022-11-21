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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

/* * ****************************CLASSE********************************** */
class pushover2 extends eqLogic {
  // Enregistrer Equipement
  public function postSave() {
    // Général : Statut dernier envoie
    $statut = $this->getCmd(null, 'statut');
    if (!is_object($statut)) {
      $statut = new pushover2Cmd();
      $statut->setLogicalId('statut');
      $statut->setIsVisible(0);
      $statut->setName(__('Dernier Statut', __FILE__));
    }
    $statut->setType('info');
    $statut->setSubType('binary');
    $statut->setEqLogic_id($this->getId());
    $statut->save();
    // Général : Numéro requête venant de Pushover
    $apirequest = $this->getCmd(null, 'apirequest');
    if (!is_object($apirequest)) {
      $apirequest = new pushover2Cmd();
      $apirequest->setLogicalId('apirequest');
      $apirequest->setIsVisible(0);
      $apirequest->setName(__('ID dernière requête', __FILE__));
    }
    $apirequest->setType('info');
    $apirequest->setSubType('string');
    $apirequest->setEqLogic_id($this->getId());
    $apirequest->save();
    // Callback : Numéro Urgence à confirmer venant de Pushover
    $waitreceiptid = $this->getCmd(null, 'waitreceiptid');
    if (!is_object($waitreceiptid)) {
      $waitreceiptid = new pushover2Cmd();
      $waitreceiptid->setLogicalId('waitreceiptid');
      $waitreceiptid->setIsVisible(0);
      $waitreceiptid->setName(__('ID Dernière Urgence', __FILE__));
    }
    $waitreceiptid->setType('info');
    $waitreceiptid->setSubType('string');
    $waitreceiptid->setEqLogic_id($this->getId());
    $waitreceiptid->save();
    // Callback informations - acknowledged
    $ackstatut = $this->getCmd(null, 'ackstatut');
    if (!is_object($ackstatut)) {
      $ackstatut = new pushover2Cmd();
      $ackstatut->setLogicalId('ackstatut');
      $ackstatut->setIsVisible(0);
      $ackstatut->setName(__('Confirmation Urgent', __FILE__));
    }
    $ackstatut->setType('info');
    $ackstatut->setSubType('binary');
    $ackstatut->setEqLogic_id($this->getId());
    $ackstatut->save();
    // Callback informations - acknowledged_by
    $ackbyuser = $this->getCmd(null, 'ackbyuser');
    if (!is_object($ackbyuser)) {
      $ackbyuser = new pushover2Cmd();
      $ackbyuser->setLogicalId('ackbyuser');
      $ackbyuser->setIsVisible(0);
      $ackbyuser->setName(__('Confirmation par', __FILE__));
    }
    $ackbyuser->setType('info');
    $ackbyuser->setSubType('string');
    $ackbyuser->setEqLogic_id($this->getId());
    $ackbyuser->save();
    // Callback informations - acknowledged_by_device
    $ackbydevice = $this->getCmd(null, 'ackbydevice');
    if (!is_object($ackbydevice)) {
      $ackbydevice = new pushover2Cmd();
      $ackbydevice->setLogicalId('ackbydevice');
      $ackbydevice->setIsVisible(0);
      $ackbydevice->setName(__('Confirmation depuis', __FILE__));
    }
    $ackbydevice->setType('info');
    $ackbydevice->setSubType('string');
    $ackbydevice->setEqLogic_id($this->getId());
    $ackbydevice->save();
    // Callback informations - acknowledged_at
    $acktime = $this->getCmd(null, 'acktime');
    if (!is_object($acktime)) {
      $acktime = new pushover2Cmd();
      $acktime->setLogicalId('acktime');
      $acktime->setIsVisible(0);
      $acktime->setName(__('Confirmation à', __FILE__));
    }
    $acktime->setType('info');
    $acktime->setSubType('string');
    $acktime->setEqLogic_id($this->getId());
    $acktime->save();
    // Callback informations - receipt
    $ackreceiptid = $this->getCmd(null, 'ackreceiptid');
    if (!is_object($ackreceiptid)) {
      $ackreceiptid = new pushover2Cmd();
      $ackreceiptid->setLogicalId('ackreceiptid');
      $ackreceiptid->setIsVisible(0);
      $ackreceiptid->setName(__('ID message confirmé', __FILE__));
    }
    $ackreceiptid->setType('info');
    $ackreceiptid->setSubType('string');
    $ackreceiptid->setEqLogic_id($this->getId());
    $ackreceiptid->save();
  }
}

class pushover2Cmd extends cmd {

  public function preSave() {
    if ($this->getSubtype() == 'message') {
      $this->setDisplay('title_disable', 0);
      $this->setDisplay('message_disable', 0);
    } else {
      $this->setOrder(99);
    }
  }

  public function execute($_options = null) {
    if ($this->getType() == 'info') {
      return;
    }
    // Verifications
    if ($_options === null) {
      throw new Exception(__('Les options de la fonction ne peuvent etre null', __FILE__));
    }
    if ($_options['message'] == '' ) {
      throw new Exception(__('Le message ne peut etre vide', __FILE__));
    }
    // Initialisation des paramètres
    $eqLogic = $this->getEqLogic();
    $postData = array(
      "user" =>  $eqLogic->getConfiguration('user'),
      "token" => $this->getConfiguration('token'),
      "message" => $_options['message'],
      "title" => $_options['title'],
      "priority" =>  $this->getConfiguration('priority'), 
      "sound" =>  $this->getConfiguration('sound'), 
      "device" =>  $this->getConfiguration('device'), 
      "html" =>  1,
      "timestamp" => time(),
      "url" => network::getNetworkAccess('external'),
      "url_title" => config::byKey('name','core')
    );
    // Urgence
    if ($this->getConfiguration('priority') == '2') {
      $postData["retry"] = $this->getConfiguration('retry');
      $postData["expire"] =  $this->getConfiguration('expire');
      $postData["callback"] = network::getNetworkAccess('external') . '/plugins/pushover2/core/php/jeePushover2.php?apikey=' . jeedom::getApiKey('pushover2') . '&id='. $this->getEqLogic_id();
    }
    // Ajout d'image depuis CAMERA
    if (array_key_exists('files', $_options) && is_array($_options['files']) && is_file($_options['files'][0])) {
      foreach ($_options['files'] as $file) {
        log::add('pushover2', 'debug', __('Fichier detecte: ', __FILE__) . $file);
        $size = number_format(filesize($file) / 1048576, 2);
        log::add('pushover2', 'debug', __('Taille du fichier en Mo: ', __FILE__) . $size . ' (2,5 Mo Max)');
        $postData["attachment"] = new CURLFile($file);
      }
    }
    // Requete CURL
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, 'https://api.pushover.net/1/messages.json');
    curl_setopt($c, CURLOPT_HEADER, false);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($c);
    log::add('pushover2', 'debug', 'Result : ' . $response);
    curl_close($c);
    if (!is_json($response)) {
      throw new Exception(__("Erreur lors de l'envoi Pushover: ", __FILE__) . $response);
    }
    /// On affecte
    $result = json_decode($response, true);
    $eqLogic->checkAndUpdateCmd('statut', $result['status']);
    $eqLogic->checkAndUpdateCmd('apirequest', $result['request']);
    // Urgence
    if ($this->getConfiguration('priority') == '2') {
      $eqLogic->checkAndUpdateCmd('waitreceiptid', $result['receipt']);
      $eqLogic->checkAndUpdateCmd('ackstatut', '0');
      $eqLogic->checkAndUpdateCmd('ackreceiptid', '');
      $eqLogic->checkAndUpdateCmd('ackbyuser', '');
      $eqLogic->checkAndUpdateCmd('ackbydevice', '');
      $eqLogic->checkAndUpdateCmd('acktime', '');
    }
    // Erreur
    if ($result['status'] === 0) {
      throw new Exception(__("Erreur lors de l'envoi Pushover: ", __FILE__) . $result['errors'][0]);
    }
  }
}
