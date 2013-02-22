<h1>Полный список кодеров (<?=count($parameters['users']);?>)</h1>
<?php
    foreach ($parameters['users'] as $user)
    {
        $title = $user['email'];
        if (trim($user['name']) != '')
        {
            $title = $user['name'] . ', email: ' . $title;
        }
?>

<a href="/Site/userInfo/<?=$user['id'];?>">
<div class="user_icon_container" style="float: left; background-image: url(<?=$user['avatar'];?>);" title="<?=$title;?>">
</div>
</a>

<?php
    }
?>