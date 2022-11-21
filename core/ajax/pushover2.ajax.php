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

try {
  require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
  include_file('core', 'authentification', 'php');

  if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
  }

  // Liste des sons du compte pushover
  if (init('action') == 'getSounds') {
    $cmd = cmd::byId(init('cmdId'));
    if( !is_object($cmd) ) {
      throw new Exception(__('Commande inconnue: ', __FILE__) . init('cmdId'));
    }
    if( $cmd->getEqType_name() != 'pushover2' ) {
      throw new Exception(__('Equipement non Pushover2: ', __FILE__) . init('cmdId'));
    }
    // Requete
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, 'https://api.pushover.net/1/sounds.json?token=' . $cmd->getConfiguration('token'));
    curl_setopt($c, CURLOPT_HEADER, false);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_HTTPHEADER, array(
    	'Cache-Control: no-cache',
    	'content-type:application/json;charset=utf-8'
    ));
    $response = curl_exec($c);
    curl_close($c);
    if (!is_json($response)) {
      throw new Exception(__("Erreur lors de la récupération des sons: ", __FILE__) . $response);
    }
    $result = json_decode($response, true);
    if ($result['status'] === 0) {
      throw new Exception(__("Erreur lors de la récupération des sons: ", __FILE__) . $result['errors'][0]);
    }
    ajax::success($result['sounds']);
  }
  // Methode inconnue
  throw new Exception(__('Aucune méthode correspondante à: ', __FILE__) . init('action'));
} catch (Exception $e) {
  ajax::error(displayExeption($e), $e->getCode());
}
