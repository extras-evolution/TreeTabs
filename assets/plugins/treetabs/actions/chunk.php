<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");

if (isset($_REQUEST['id']))
        $id = (int)$_REQUEST['id'];
else    $id = 0;

if ($manager_theme)
        $manager_theme .= '/';
else    $manager_theme  = '';

// Get table names (alphabetical)
$tbl_active_users      = $modx->getFullTableName('active_users');
$tbl_site_htmlsnippets = $modx->getFullTableName('site_htmlsnippets');

$content = array();
if (isset($_REQUEST['id']) && $_REQUEST['id']!='' && is_numeric($_REQUEST['id'])) {
    $sql = 'SELECT * FROM '.$tbl_site_htmlsnippets.' WHERE id=\''.$id.'\'';
    $rs = $modx->db->query($sql);
    $limit = $modx->db->getRecordCount($rs);
    if ($limit > 1) {
        echo '<p>Error: Multiple Chunk sharing same unique ID.</p>';
        exit;
    }
    if ($limit < 1) {
        echo '<p>Chunk doesn\'t exist.</p>';
        exit;
    }
    $content = $modx->db->getRow($rs);
    $_SESSION['itemname'] = $content['name'];
    if ($content['locked'] == 1 && $_SESSION['mgrRole'] != 1) {
        $e->setError(3);
        $e->dumpError();
    }
} else {
    $_SESSION['itemname'] = 'New Chunk';
}

if (isset($_POST['which_editor']))
        $which_editor = $_POST['which_editor'];
else    $which_editor = 'none';

$content = array_merge($content, $_POST);

// Print RTE Javascript function
?>
<script language="javascript" type="text/javascript">
// Added for RTE selection
function changeRTE(){
    var whichEditor = document.getElementById('which_editor');
    if (whichEditor) for (var i=0; i<whichEditor.length; i++){
        if (whichEditor[i].selected){
            newEditor = whichEditor[i].value;
            break;
        }
    }

    documentDirty=false;
    document.mutate.a.value = <?php echo $action?>;
    document.mutate.which_editor.value = newEditor;
    document.mutate.submit();
}

function duplicaterecord(){
    if (confirm("<?php echo $_lang['confirm_duplicate_record']?>")==true) {
        documentDirty=false;
        document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=97";
    }
}

function deletedocument() {
    if (confirm("<?php echo $_lang['confirm_delete_htmlsnippet']?>")==true) {
        documentDirty=false;
        document.location.href="index.php?id=" + document.mutate.id.value + "&a=80";
    }
}
</script>

<form class="htmlsnippet" id="mutate" name="mutate" method="post" action="index.php">
<?php

// invoke OnChunkFormPrerender event
$evtOut = $modx->invokeEvent('OnChunkFormPrerender', array(
    'id' => $id,
));
if (is_array($evtOut))
    echo implode('', $evtOut);

?>
<input type="hidden" name="a" value="79" />
<input type="hidden" name="id" value="<?php echo $_REQUEST['id']?>" />
<input type="hidden" name="mode" value="<?php echo (int) $_REQUEST['a']?>" />

    <div id="actions">
          <ul class="actionButtons">
              <li id="Button1">
                <a href="#" onclick="documentDirty=false; document.mutate.save.click();">
                  <img src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']?>
                </a>
                  
				  <input type="hidden" id="stay" name="stay" value="">
                
              </li>
            </ul>
    </div>

<div class="sectionBody">
    <p><?php echo $_lang['htmlsnippet_msg']?></p>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="display:none">
        <tr><td align="left"><?php echo $_lang['htmlsnippet_name']?>:</td>
            <td align="left"><span style="font-family:'Courier New', Courier, mono">{{</span><input name="name" type="text" maxlength="100" value="<?php echo htmlspecialchars($content['name'])?>" class="inputBox" style="width:140px;" onChange='documentDirty=true;'><span style="font-family:'Courier New', Courier, mono">}}</span><span class="warning" id="savingMessage">&nbsp;</span></td></tr>
        <tr><td align="left"><?php echo $_lang['htmlsnippet_desc']?>:&nbsp;&nbsp;</td>
            <td align="left"><span style="font-family:'Courier New', Courier, mono">&nbsp;&nbsp;</span><input name="description" type="text" maxlength="255" value="<?php echo htmlspecialchars($content['description'])?>" class="inputBox" style="width:300px;" onChange='documentDirty=true;'></td></tr>
        <tr><td align="left"><?php echo $_lang['existing_category']?>:&nbsp;&nbsp;</td>
            <td align="left"><span style="font-family:'Courier New', Courier, mono">&nbsp;&nbsp;</span>
            <select name="categoryid" style="width:300px;" onChange='documentDirty=true;'>
                <option>&nbsp;</option>
<?php
include_once(MODX_MANAGER_PATH.'includes/categories.inc.php');
$ds = getCategories();
if ($ds) {
    foreach ($ds as $n => $v) {
        echo "\t\t\t\t".'<option value="'.$v['id'].'"'.($content['category'] == $v['id'] || (empty($content['category']) && $_POST['categoryid'] == $v['id']) ? ' selected="selected"' : '').'>'.htmlspecialchars($v['category'])."</option>\n";
    }
}
?>
            </select></td></tr>
        <tr><td align="left" valign="top" style="padding-top:5px;"><?php echo $_lang['new_category']?>:</td>
            <td align="left" valign="top" style="padding-top:5px;"><span style="font-family:'Courier New', Courier, mono">&nbsp;&nbsp;</span><input name="newcategory" type="text" maxlength="45" value="<?php echo isset($content['newcategory']) ? $content['newcategory'] : ''?>" class="inputBox" style="width:300px;" onChange="documentDirty=true;"></td></tr>
        <tr><td align="left" colspan="2"><input name="locked" type="checkbox"<?php echo $content['locked'] == 1 || $content['locked'] == 'on' ? ' checked="checked"' : ''?> class="inputBox" value="on" /> <?php echo $_lang['lock_htmlsnippet']?>
            <span class="comment"><?php echo $_lang['lock_htmlsnippet_msg']?></span></td></tr>
    </table>

    <div style="width:100%; position:relative;">
        <div style="padding:1px; width:100%; height:16px; background-color:#eeeeee; border:1px solid #e0e0e0; margin-top:5px;">
            <span style="color:brown; font-weight:bold; padding:3px;">&nbsp;<?php echo $_lang['chunk_code']?></span>
        </div>
        <textarea dir="ltr" class="phptextarea" name="post" style="width:100%; height:370px;" onChange="documentDirty=true;"><?php echo isset($content['post']) ? htmlspecialchars($content['post']) : htmlspecialchars($content['snippet'])?></textarea>
        </div>

    <span class="warning"><?php echo $_lang['which_editor_title']?></span>
            <select id="which_editor" name="which_editor" onchange="changeRTE();">
                <option value="none"<?php echo $which_editor == 'none' ? ' selected="selected"' : ''?>><?php echo $_lang['none']?></option>
<?php
// invoke OnRichTextEditorRegister event
$evtOut = $modx->invokeEvent('OnRichTextEditorRegister');
if (is_array($evtOut)) {
    foreach ($evtOut as $i => $editor) {
        echo "\t".'<option value="'.$editor.'"'.($which_editor == $editor ? ' selected="selected"' : '').'>'.$editor."</option>\n";
    }
}
?>
            </select>
</div><!-- end .sectionBody -->
<?php

// invoke OnChunkFormRender event
$evtOut = $modx->invokeEvent('OnChunkFormRender', array(
    'id' => $id,
));
if (is_array($evtOut))
    echo implode('', $evtOut);
?>

<input type="submit" name="save" style="display:none;" />
</form>
<?php
// invoke OnRichTextEditorInit event
if ($use_editor == 1) {
    $evtOut = $modx->invokeEvent('OnRichTextEditorInit', array(
        'editor' => $which_editor,
        'elements' => array(
            'post',
        ),
    ));
    if (is_array($evtOut))
        echo implode('', $evtOut);
}
?>