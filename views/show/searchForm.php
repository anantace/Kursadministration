<?php

/*
 *  @autor  Annelene Sudau <asudau@uos.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
use Studip\Button, Studip\LinkButton;

global $perm;
?>

<? if (isset($msg)): ?>
    <?= parse_msg($msg) ?>
<? endif; ?>

<? if ($_SESSION['admin_modules_data']["orig_bin"] != $_SESSION['admin_modules_data']["changed_bin"]): ?>
    <?= MessageBox::info(_("Diese Daten sind noch nicht gespeichert.")) ?>
<? endif; ?>

<h1>Kurssuche</h1>

<form action="<?=$controller->url_for('show/search/');?>" method="post">

<input style="margin-right:5px" value="" name="course_search" size="20"/><button title="Suche starten" name="submit" class="button" type="submit">Suchen</button></p>
</form>


<form name="links_admin_search" action="<?=$controller->url_for('show/search/');?>" method="POST">
                <?= CSRFProtection::tokenTag() ?>
                <table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
                    <tr>
                        <td class="table_row_even" colspan=5>
                               <br>
                               <b><?=_("Sie können die Auswahl der Veranstaltungen eingrenzen:")?></b><br>
                               <br>
                        </td>
                    </tr>
                    <tr>
                        <td class="table_row_even" colspan="5">
                            <label style="display: inline-block;">
                                <?=_("Semester:")?><br>
                                <?=SemesterData::GetSemesterSelector(array('name'=>'srch_sem'), $_SESSION['links_admin_data']['srch_sem'])?>
                            </label>
                        <?
                        if ($perm->have_perm("root")) {
                            $dbquery = "SELECT Institut_id, Name FROM Institute WHERE Institut_id!=fakultaets_id ORDER BY Name";
                            $dbparams = array();
                        } else {
                            $dbquery = "SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
                                WHERE a.user_id=? AND a.inst_perms='admin' ORDER BY is_fak,Name";
                            $dbparams = array($user->id);
                        }
                        ?>
                        <label style="display: inline-block;">
                            <?=_("Einrichtung:")?><br>
                            <select name="srch_inst">
                                <option value="0"><?=_("alle")?></option>
                                <?
                                $dbstatement = DBManager::get()->prepare($dbquery);
                                $dbstatement->execute($dbparams);

                                while ($dbrow = $dbstatement->fetch(PDO::FETCH_ASSOC)) {
                                    $my_inst[]=$dbrow['Institut_id'];
                                    if ($_SESSION['links_admin_data']['srch_inst'] == $dbrow['Institut_id'])
                                        echo"<option selected value=\"".$dbrow['Institut_id']."\">".htmlReady(substr($dbrow['Name'], 0, 30))."</option>";
                                    else
                                        echo"<option value=\"".$dbrow['Institut_id']."\">".htmlReady(substr($dbrow['Name'], 0, 30))."</option>";
                                    if ($dbrow['is_fak']) {
                                        $db2query = "SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$dbrow['Institut_id'] . "' AND institut_id!='" .$dbrow['Institut_id'] . "' ORDER BY Name";
                                        foreach (DBManager::get()->query($db2query) as $dbrow2) {
                                            if ($_SESSION['links_admin_data']['srch_inst'] == $dbrow2['Institut_id'])
                                                echo"<option selected value=\"".$dbrow2['Institut_id']."\">&nbsp;&nbsp;&nbsp;".htmlReady(substr($dbrow2['Name'], 0, 30))."</option>";
                                            else
                                                echo"<option value=\"".$dbrow2['Institut_id']."\">&nbsp;&nbsp;&nbsp;".htmlReady(substr($dbrow2['Name'], 0, 30))."</option>";
                                            $my_inst[]=$dbrow2['Institut_id'];
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </label>
                    <? //if (($perm->have_perm("admin")) && (!$perm->have_perm("root"))): ?>
                        <label style="display: inline-block;">
                            <?=_("DozentIn:")?><br>
                            <select name="srch_doz">
                            <option value="0"><?=_("alle")?></option>
                            <?
                            if (is_array($my_inst) && count($my_inst)) {
                                $db2query="SELECT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] ." AS fullname, Institut_id FROM user_inst  LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms='dozent' AND institut_id IN (?) GROUP BY auth_user_md5.user_id ORDER BY Nachname";
                                $db2statement = DBManager::get()->prepare($db2query);
                                $db2statement->execute(array($my_inst));
                                while ($db2row = $db2statement->fetch(PDO::FETCH_ASSOC)){
                                            if ($_SESSION['links_admin_data']['srch_doz'] == $db2row['user_id'])
                                            echo"<option selected value=\"".$db2row['user_id']."\">".htmlReady(my_substr($db2row['fullname'],0,35))."</option>";
                                        else
                                            echo"<option value=\"".$db2row['user_id']."\">".htmlReady(my_substr($db2row['fullname'],0,35))."</option>";

                                }
                            }
                            ?>
                            </select>
                        </label>
                    <? //endif; ?>

                    <?
                        if ($perm->have_perm("root")) {
                            $dbquery = "SELECT Institut_id,Name FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name";
                            ?>
                        <label style="display: inline-block;">
                            <?=_("Fakultät:")?><br>
                            <select name="srch_fak">
                                <option value="0"><?=_("alle")?></option>
                                <?
                                $dbstatement = DBManager::get()->prepare($dbquery);
                                $dbstatement->execute();

                                while ($dbrow = $dbstatement->fetch(PDO::FETCH_ASSOC)){
                                    if ($_SESSION['links_admin_data']['srch_fak'] == $dbrow['Institut_id'])
                                        echo"<option selected value=\"".$dbrow['Institut_id']."\">".htmlReady(substr($dbrow['Name'], 0, 30))."</option>";
                                    else
                                        echo"<option value=\"".$dbrow['Institut_id']."\">".htmlReady(substr($dbrow['Name'], 0, 30))."</option>";
                                }
                                ?>
                            </select>
                        </label>
                            <?
                        }
                        ?>&nbsp;
                    </td>
                </tr>
                <tr>
                    <td class="table_row_even" colspan="5">
                        <label style="display: inline-block;">
                            <?=_("freie Suche:")?><br>
                            <input type="text" name="srch_exp" maxlength=255 style="width: 250px; vertical-align: middle;" value="<?= htmlReady($_SESSION['links_admin_data']['srch_exp']) ?>">
                            <input type="hidden" name="srch_send" value="TRUE">
                        </label>

                        <?= Button::create(_("Anzeigen"), 'anzeigen'); ?>
                    <? if ($_SESSION['links_admin_data']['srch_on']): ?>
                        <?= Button::create(_("Zurücksetzen"), 'links_admin_reset_search') ?>
                    <? endif; ?>
                        <input type="hidden" name="view" value="<?= htmlReady($_SESSION['links_admin_data']['view'])?>">
                    </td>
                </tr>
                <tr>
                    <td class="table_row_even" colspan="5">
                        <label>
                            <input type="checkbox" name="show_rooms_check" value="on" <? if ($show_rooms_check == 'on') echo 'checked'; ?> >&nbsp; <?=_("Raumdaten einblenden")?>
                        </label>
                    </td>
                </tr>
                    <?
                    //more Options for archiving
                    if ($i_page == "archiv_assi.php") {
                        ?>
                        <tr>
                            <td class="table_row_even" colspan=6>
                                <br>
                                <input type="CHECKBOX" name="select_old" <? if ($_SESSION['links_admin_data']['select_old']) echo ' checked' ?>>&nbsp;<?=_("keine zukünftigen Veranstaltungen anzeigen - Beginn des (letzten) Veranstaltungssemesters ist verstrichen")?><br>
                                <!-- <input type="CHECKBOX" name="select_inactive" <? if ($_SESSION['links_admin_data']['select_inactive']) echo ' checked' ?>>&nbsp;<?=_("nur inaktive Veranstaltungen auswählen (letzte Aktion vor mehr als sechs Monaten)")?> -->
                            </td>
                        </tr>
                        <?
                    } else {
                        ?>
                        <input type="hidden" name="select_old" value="<? if ($_SESSION['links_admin_data']['select_old']) echo "TRUE" ?> ">
                        <input type="hidden" name="select_inactive" value="<? if ($_SESSION['links_admin_data']['select_inactive']) echo "TRUE" ?>">
                        <?
                    }
                    ?>
                    <? if (! empty($message)) : ?>
                    <tr>
                        <td class="blank" colspan=5>
                            <? parse_msg($message); ?>
                        </td>
                    </tr>
                    <? endif; ?>
                </table>
            </form>


<ul>



<? foreach($tabs as $tab){?>
 	<li name="<?=$tab_num?>" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
 	<input type="checkbox" name="visible_<?=$tab_num?>" <?=$tab['visible']?>/> 
 	<input type="hidden" value="<?= $tab['tab']; ?>" name="tab_title_<?=$tab_num?>" />
	<input value="<?= $tab['title']; ?>" name="new_tab_title_<?=$tab_num?>" size="20"/>
	<input type="hidden" value="<?= $tab['position']; ?>" name="tab_position_<?=$tab_num?>" />
 	(<?= $tab['orig_title']; ?>)</p>
	</li>
 	<?$tab_num++;
	
}?>

</ul>





<?php
$sidebar = Sidebar::get();
$sidebar->setImage(Assets::image_path("sidebar/plugin-sidebar.png"));
