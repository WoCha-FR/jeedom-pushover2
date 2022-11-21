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

 // Réponse pour Pushover
http_response_code(200);
header('Content-type: application/json');
// Vérification API
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
if (!jeedom::apiAccess(init('apikey'), 'pushover2')) {
  echo __("Clef API non valide, vous n'êtes pas autorisé à effectuer cette action (pushover2)", __FILE__);
  die();
}
log::add('pushover2', 'debug', 'Appel API');
// Récupération du résultat
$content = file_get_contents('php://input');
log::add('pushover2', 'debug', $content);
// Récupération des paramètres de la requête
$id = init('id');
$eqLogic = pushover2::byId($id);
if (!is_object($eqLogic)) {
  echo json_encode(array('text' => __('Id equipement inconnu: ', __FILE__) . init('id')));
  log::add('pushover2', 'debug', 'id inconnu '. $id);
  die();
}
// On traite le message
parse_str($content, $query_params);
// On affecte
$eqLogic->checkAndUpdateCmd('ackstatut', $query_params['acknowledged']);
$eqLogic->checkAndUpdateCmd('ackreceiptid', $query_params['receipt']);
$eqLogic->checkAndUpdateCmd('ackbyuser', $query_params['acknowledged_by']);
$eqLogic->checkAndUpdateCmd('ackbydevice', $query_params['acknowledged_by_device']);
$eqLogic->checkAndUpdateCmd('acktime', date('d-m-Y H:i:s', $query_params['acknowledged_at']));
