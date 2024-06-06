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

/* Modification affichage des options suivant le mode de transport */
$('#table_cmd tbody').delegate('.cmdAttr[data-l1key=configuration][data-l2key=priority]', 'change', function () {
  var tr = $(this).closest('tr');
  if( $(this).value() == 2 ) {
    tr.find('.cmdAttr[data-l1key=configuration][data-l2key=retry]').show()
    tr.find('.cmdAttr[data-l1key=configuration][data-l2key=expire]').show()
  } else {
    tr.find('.cmdAttr[data-l1key=configuration][data-l2key=retry]').hide()
    tr.find('.cmdAttr[data-l1key=configuration][data-l2key=expire]').hide()
  } 
})

/* Variable contenant les sons par défaut */
var optionSound = '<option value="pushover">Pushover</option>'
optionSound += '<option value="bike">Bike</option>'
optionSound += '<option value="bugle">Bugle</option>'
optionSound += '<option value="cashregister">Cash Register</option>'
optionSound += '<option value="classical">Classical</option>'
optionSound += '<option value="cosmic">Cosmic</option>'
optionSound += '<option value="falling">Falling</option>'
optionSound += '<option value="gamelan">Gamelan</option>'
optionSound += '<option value="incoming">Incoming</option>'
optionSound += '<option value="intermission">Intermission</option>'
optionSound += '<option value="magic">Magic</option>'
optionSound += '<option value="mechanical">Mechanical</option>'
optionSound += '<option value="pianobar">Piano Bar</option>'
optionSound += '<option value="siren">Siren</option>'
optionSound += '<option value="spacealarm">Space Alarm</option>'
optionSound += '<option value="tugboat">Tug Boat</option>'
optionSound += '<option value="alien">Alien Alarm (long)</option>'
optionSound += '<option value="climb">Climb (long)</option>'
optionSound += '<option value="persistent">Persistent (long)</option>'
optionSound += '<option value="echo">Pushover Echo (long)</option>'
optionSound += '<option value="updown">Up Down (long)</option>'
optionSound += '<option value="vibrate">Vibrate Only</option>'
optionSound += '<option value="none">None (silent)</option>'

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

/* Fonction pour recuperer les sons persos de l'application */
function getSounds(cmdid) {
  var select = ''
  $.ajax({
      type: "POST",
      url: "plugins/pushover2/core/ajax/pushover2.ajax.php",
      data: {
        action: "getSounds",
        cmdId: cmdid
      },
      dataType: "json",
      async: false,
      error: function (request, status, error) {
        handleAjaxError(request, status, error)
      },
      success: function (data) {
        if (data.state != 'ok') {
          $.fn.showAlert({message: data.result, level: 'danger'})
          return optionSound
        }
        for (const [key, value] of Object.entries(data.result)) {
          select += `<option value="${key}">${value}</option>`
        }
      }
  })
  return select
}

function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  if (isset(_cmd) && _cmd.type == 'action' && isset(_cmd.configuration.token) && _cmd.configuration.token != '') {
    var pushoverSound = getSounds(init(_cmd.id))
  } else {
    var pushoverSound = optionSound
  }

  var pushoverpriority = '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="priority">'
  pushoverpriority  += '<option value="-2">{{Très basse}}</option>'
  pushoverpriority  += '<option value="-1">{{Basse}}</option>'
  pushoverpriority  += '<option value="0">{{Normale}}</option>'
  pushoverpriority  += '<option value="1">{{Haute}}</option>'
  pushoverpriority  += '<option value="2">{{Urgent}}</option>'
  pushoverpriority  += '</select>'

  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '<input class="cmdAttr" data-l1key="type" style="display:none;" />'
  tr += '<input class="cmdAttr" data-l1key="subType" style="display:none;" />'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '</td>'
  if (!isset(_cmd.type) || _cmd.type == 'action') {
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="token"></td>'
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="device"></td>'
    tr += '<td>' + pushoverpriority + '</td>';
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="retry" placeholder="60" style="display:none;"></td>'
    tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="expire" placeholder="1800" style="display:none;"></td>'
    tr += '<td><select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="sound">'
    tr += pushoverSound
    tr += '</select></td>'
  } else {
    tr += '<td colspan="6"><span class="cmdAttr" data-l1key="htmlstate"></span>'
    tr += '</td>'
  }
  tr += '<td>'
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Visible}}</label></span>'
  tr += '</td><td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a>'
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id:  $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {type: 'info'},
    error: function (error) {
      $.fn.showAlert({message: error.message, level: 'danger'})
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
    }
  })
  if (isset(_cmd.type)) {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type))
  } else {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value('action')
  }
  if (isset(_cmd.subType)) {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=subType]').value(init(_cmd.subType))
  } else {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=subType]').value('message')
  }
}