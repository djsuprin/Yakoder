<form method="POST" action="/Site/changePermissions">
    <p style="font-size: 18px; font-weight: bold; padding-bottom: 10px; text-align: center;">Разрешения</p>
    <table style="width: 100%">
        <tr>
            <td style="font-size: 14px; font-weight: bold;">Модули\Группы</td>
        </tr>
<?php
    foreach ($parameters['modules'] as $module_name => $method_names)
    {
?>
        <tr>
            <td style="font-size: 12px; font-weight: bold; padding: 20px 0 10px 0; vertical-align: middle;"><strong><?=$module_name;?></strong></td>
<?php
    foreach ($parameters['groups'] as $group)
    {
?>
            <td style="font-size: 12px; font-weight: bold; text-align: center; vertical-align: middle;"><strong><?=$group['caption'];?></strong></td>
<?php
    }
?>
        </tr>
<?php
        foreach ($method_names as $method_name => $group_captions)
        {
?>
        <tr>
            <td style="border-bottom: solid 1px black; vertical-align: middle;"><?=$method_name;?></td>
<?php
            foreach ($group_captions as $group_caption => $permission)
            {
?>
            <td style="border-bottom: solid 1px black; text-align: center;">
                <input type="checkbox" name="permissions[<?=$module_name;?>][<?=$method_name;?>][<?=$group_caption;?>]" <?=$permission;?> />
            </td>
<?php
            }
?>
        </tr>
<?php
        }
    }
?>
        <tr>
            <td colspan="3" style="padding-top: 20px;"><input type="submit" value="Сохранить" /></td>
        </tr>
    </table>
</form>