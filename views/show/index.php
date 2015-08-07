<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
use Studip\Button, Studip\LinkButton;
?>

<pre>
<? //var_dump($members); ?>
</pre>


<? if (isset($msg)): ?>
    <?= parse_msg($msg) ?>
<? endif; ?>

<? if ($_SESSION['admin_modules_data']["orig_bin"] != $_SESSION['admin_modules_data']["changed_bin"]): ?>
    <?= MessageBox::info(_("Diese Daten sind noch nicht gespeichert.")) ?>
<? endif; ?>

<? if (count($tutors) > 0): ?>

<form action="<?= $controller->url_for('show/delete/') ?>" method="post">
<?= CSRFProtection::tokenTag() ?>

<table class="default">
    <caption>
        <?= sprintf(_("Suchergebnis: es wurden %s TutorInnen gefunden"), count($tutors)) ?>
    </caption>
    <thead>
    <tr class="sortable">
        <th colspan="2" <?= ($sortby == 'username') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=username&order='.$order.'&toggle='.($sortby == 'username'))?>"><?=_("Benutzername")?></a>
        </th>
        <th>
        &nbsp;
        </th>
        <th <?= ($sortby == 'perms') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=perms&order='.$order.'&toggle='.($sortby == 'perms'))?>"><?=_("Status")?></a>
        </th>
        <th <?= ($sortby == 'Vorname') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=Vorname&order='.$order.'&toggle='.($sortby == 'Vorname'))?>"><?=_("Vorname")?></a>
        </th>
        <th <?= ($sortby == 'Nachname') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=Nachname&order='.$order.'&toggle='.($sortby == 'Nachname'))?>"><?=_("Nachname")?></a>
        </th>
        <th <?= ($sortby == 'Email') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=Email&order='.$order.'&toggle='.($sortby == 'Email'))?>"><?=_("E-Mail")?></a>
        </th>
        <th <?= ($sortby == 'changed') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=changed&order='.$order.'&toggle='.($sortby == 'changed'))?>"><?=_("inaktiv")?></a>
        </th>
        <th <?= ($sortby == 'mkdate') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=mkdate&order='.$order.'&toggle='.($sortby == 'mkdate'))?>"><?=_("registriert seit")?></a>
        </th>
        <th colspan="2" <?= ($sortby == 'auth_plugin') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=auth_plugin&order='.$order.'&toggle='.($sortby == 'auth_plugin'))?>"><?=_("Kurse")?></a>
        </th>
    </tr>
    </thead>

    <tbody>

    <? foreach ($tutors as $id => $tutor) : ?>

    <tr>
        <td style="white-space:nowrap;">
            <input class="check_all" type="checkbox" name="user_ids[]" value="<?= $tutor['user_id'] ?>">
            <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $tutor['username'])) ?>" title="<?= _('Profil des Benutzers anzeigen')?>">
                 <?= Avatar::getAvatar($tutor['user_id'], $tutor['username'])->getImageTag(Avatar::SMALL, array('title' => htmlReady($tutor['Vorname'] . ' ' . $tutor['Nachname']))) ?>
            </a>
        </td>
        <td>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $tutor['username'])) ?>" title="<?= _('Profil des Benutzers anzeigen')?>">
                <?= $tutor['username'] ?>
            </a>
        </td>
        <td>
            <?
            $tooltxt = _("Sichtbarkeit:") . ' ' . $tutor['visible'];
            $tooltxt .= "\n" . _("Domänen:") . ' ' . $tutor['userdomains'];
            if ($tutor['locked'] == '1') {
                $tooltxt .= "\n" .  _("Nutzer ist gesperrt!");
            }
            ?>
           <?= tooltipicon($tooltxt) ?>
        </td>
        <td>
	     <? $studipUser = new AuthUserMd5($id); ?>
	     <? $userperm = $studipUser->getValue('perms'); ?>
            <?= $userperm ?>
        </td>
        <td>
            <?= htmlReady($tutor['Vorname']) ?>
        </td>
        <td>
            <?= htmlReady($tutor['Nachname']) ?>
        </td>
        <td>
            <?= htmlReady($tutor['Email']) ?>
        </td>
        <td>
	    <? $db = DbManager::get();
        	$st = $db->prepare("SELECT user_online.last_lifesign as lifesign                            
				FROM user_online
                            WHERE user_id = ?");
        	$st->execute(array($id));
        	while ($row = $st->fetch(PDO::FETCH_ASSOC)) { 
            		$tutor["changed_timestamp"] = $row['lifesign'];
		}
	    ?>

        <? if ($tutor["changed_timestamp"] != "") :
            $inactive = time() - $tutor['changed_timestamp'];
            if ($inactive < 3600 * 24) {
                $inactive = gmdate('H:i:s', $inactive);
            } else {
                $inactive = floor($inactive / (3600 * 24)).' '._('Tage');
            }
        else :
            $inactive = _("nie benutzt");
        endif ?>
        <?= $inactive ?>
        </td>
        <td>
            <?= ($tutor["mkdate"]) ? date("d.m.Y", $tutor["mkdate"]) : _('unbekannt') ?>
        </td>
        <td>
	 <? 
		
		$db = DbManager::get();
        	$st = $db->prepare("SELECT seminare.Name as kurs_name, seminar_user.Seminar_id as sem_id
                            FROM seminar_user
                            LEFT JOIN seminare USING (Seminar_id)
                            WHERE user_id = ? ORDER BY kurs_name");
        	$st->execute(array($id));
        	while ($row = $st->fetch(PDO::FETCH_ASSOC)) { ?>
            		<a href="<?=URLHelper::getURL('dispatch.php/course/overview', array('cid' => $row['sem_id']))?>"><?=$row['kurs_name']?></a> 
			<br/> 	
        	<? } 


 	?>
	</td>
	
        <td class="actions" nowrap>
            <a href="<?= URLHelper::getURL('dispatch.php/admin/user/edit/'.$tutor['user_id']) ?>" title="<?= _('Detailansicht des Benutzers anzeigen')?>">
                <?= Assets::img('icons/16/blue/edit.png', array('title' => _('Diesen Benutzer bearbeiten'))) ?>
            </a>
        </td>
    </tr>
    <? endforeach ?>

    </tbody>

    <tfoot>

    <tr>
        <td colspan="11" align="right" >
            <input class="middle" type="checkbox" name="documents" value="1" >Dokumente der Nutzer ebenfalls löschen
        </td>
    </tr>
    <tr>
        <td colspan="11" align="right" >
             <input class="middle" type="checkbox" name="mail" value="1" >Infomail an gelöschte Nutzer versenden
	 </td>
    </tr>
    <tr>
        <td colspan="11" align="right" >
	     <input onclick="check_all('user_ids[]', this)" type="checkbox"> Alle auswählen <br>

	     <button title="Alle ausgewählten Tutoren löschen" name="delete" onClick="return confirm('Wollen Sie die ausgewählten Nutzer wirklich löschen?')" class="button" type="submit">Löschen</button></p>
        </td>
    </tr>



    </tfoot>

</table>
</form>
<? endif; ?>


<? if (count($users) > 0): ?>
<form action="<?= $controller->url_for('show/delete/') ?>" method="post">
<?= CSRFProtection::tokenTag() ?>

<table class="default">
    <caption>
        <?= sprintf(_("Suchergebnis: es wurden %s TeilnehmerInnen gefunden"), count($users)) ?>
    </caption>
    <thead>
    <tr class="sortable">
        <th colspan="2" <?= ($sortby == 'username') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=username&order='.$order.'&toggle='.($sortby == 'username'))?>"><?=_("Benutzername")?></a>
        </th>
        <th>
        &nbsp;
        </th>
        <th <?= ($sortby == 'perms') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=perms&order='.$order.'&toggle='.($sortby == 'perms'))?>"><?=_("Status")?></a>
        </th>
        <th <?= ($sortby == 'Vorname') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=Vorname&order='.$order.'&toggle='.($sortby == 'Vorname'))?>"><?=_("Vorname")?></a>
        </th>
        <th <?= ($sortby == 'Nachname') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=Nachname&order='.$order.'&toggle='.($sortby == 'Nachname'))?>"><?=_("Nachname")?></a>
        </th>
        <th <?= ($sortby == 'Email') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=Email&order='.$order.'&toggle='.($sortby == 'Email'))?>"><?=_("E-Mail")?></a>
        </th>
        <th <?= ($sortby == 'changed') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=changed&order='.$order.'&toggle='.($sortby == 'changed'))?>"><?=_("inaktiv")?></a>
        </th>
        <th <?= ($sortby == 'mkdate') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=mkdate&order='.$order.'&toggle='.($sortby == 'mkdate'))?>"><?=_("registriert seit")?></a>
        </th>
        <th colspan="2" <?= ($sortby == 'auth_plugin') ? 'class="sort' . $order . '"' : ''?>>
            <a href="<?=URLHelper::getLink('?sortby=auth_plugin&order='.$order.'&toggle='.($sortby == 'auth_plugin'))?>"><?=_("Kurse")?></a>
        </th>
    </tr>
    </thead>

    <tbody>

    <? foreach ($users as $id => $user) : ?>

    <tr>
        <td style="white-space:nowrap;">
            <input class="check_all" type="checkbox" name="user_ids[]" value="<?= $user['user_id'] ?>">
            <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $user['username'])) ?>" title="<?= _('Profil des Benutzers anzeigen')?>">
                 <?= Avatar::getAvatar($user['user_id'], $user['username'])->getImageTag(Avatar::SMALL, array('title' => htmlReady($user['Vorname'] . ' ' . $user['Nachname']))) ?>
            </a>
        </td>
        <td>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $user['username'])) ?>" title="<?= _('Profil des Benutzers anzeigen')?>">
                <?= $user['username'] ?>
            </a>
        </td>
        <td>
            <?
            $tooltxt = _("Sichtbarkeit:") . ' ' . $user['visible'];
            $tooltxt .= "\n" . _("Domänen:") . ' ' . $user['userdomains'];
            if ($user['locked'] == '1') {
                $tooltxt .= "\n" .  _("Nutzer ist gesperrt!");
            }
            ?>
           <?= tooltipicon($tooltxt) ?>
        </td>
        <td> 
	     <? $studipUser = new AuthUserMd5($id); ?>
	     <? $userperm = $studipUser->getValue('perms'); ?>
            <?= $userperm ?>
        </td>
        <td>
            <?= htmlReady($user['Vorname']) ?>
        </td>
        <td>
            <?= htmlReady($user['Nachname']) ?>
        </td>
        <td>
            <?= htmlReady($user['Email']) ?>
        </td>
        <td>
	      <? $db = DbManager::get();
        	$st = $db->prepare("SELECT user_online.last_lifesign as lifesign                            
				FROM user_online
                            WHERE user_id = ?");
        	$st->execute(array($id));
        	while ($row = $st->fetch(PDO::FETCH_ASSOC)) { 
            		$user["changed_timestamp"] = $row['lifesign'];
		}
	       ?>


        <? if ($user["changed_timestamp"] != "") :
            $inactive = time() - $user['changed_timestamp'];
            if ($inactive < 3600 * 24) {
                $inactive = gmdate('H:i:s', $inactive);
            } else {
                $inactive = floor($inactive / (3600 * 24)).' '._('Tage');
            }
        else :
            $inactive = _("nie benutzt");
        endif ?>
        <?= $inactive ?>
        </td>
        <td>
            <?= ($user["mkdate"]) ? date("d.m.Y", $user["mkdate"]) : _('unbekannt') ?>
        </td>
        <td>
	 <? 
		
		$db = DbManager::get();
        	$st = $db->prepare("SELECT seminare.Name as kurs_name, seminar_user.Seminar_id as sem_id
                            FROM seminar_user
                            LEFT JOIN seminare USING (Seminar_id)
                            WHERE user_id = ? ORDER BY kurs_name");
        	$st->execute(array($id));
        	$ret = array();
        	while ($row = $st->fetch(PDO::FETCH_ASSOC)) { ?>
            		<a href="<?=URLHelper::getURL('dispatch.php/course/overview', array('cid' => $row['sem_id']))?>"><?=$row['kurs_name']?></a> 
			<br/> 	
        	<? } 


 	?>
	</td>
	
        <td class="actions" nowrap>
            <a href="<?= URLHelper::getURL('dispatch.php/admin/user/edit/'.$user['user_id']) ?>" title="<?= _('Detailansicht des Benutzers anzeigen')?>">
                <?= Assets::img('icons/16/blue/edit.png', array('title' => _('Diesen Benutzer bearbeiten'))) ?>
            </a>
        </td>
    </tr>
    <? endforeach ?>

    </tbody>

    <tfoot>

    <tr>
        <td colspan="11" align="right" >
            <input class="middle" type="checkbox" name="documents" value="1" >Dokumente der Nutzer ebenfalls löschen
        </td>
    </tr>
    <tr>
        <td colspan="11" align="right" >
             <input class="middle" type="checkbox" name="mail" value="1" >Infomail an gelöschte Nutzer versenden
	 </td>
    </tr>
    <tr>
        <td colspan="11" align="right" >
	     	<input onclick="check_all('user_ids[]', this)" type="checkbox"> Alle auswählen <br>
		<button title="Alle ausgewählten TeilnehmerInnen löschen" name="delete" onClick="return confirm('Wollen Sie die ausgewählten Nutzer wirklich löschen?')" class="button" type="submit">Löschen</button></p>
        </td>
    </tr>



    </tfoot>

</table>
</form>
<? endif; ?>



<? //echo $search_form; ?>
<script>
function check_all(name, el){

     if(!el || !el.form) return alert('falscher Parameter');

     var box = el.form.elements[name];

     if(!box) return alert(name + ' existiert nicht!');

     if(!box.length) box.checked = el.checked; else 

     for(var i = 0; i < box.length; i++)  box[i].checked = el.checked;

}
</script>


<?php
$sidebar = Sidebar::get();
$sidebar->setImage(Assets::image_path("sidebar/plugin-sidebar.png"));
