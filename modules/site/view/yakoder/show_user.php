<h1>Информация о пользователе <?=$parameters['user']['display_name'];?></h1>
<div style="margin-bottom: 20px;">
	<div class="user_icon_container" style="background-image: url(<?=$parameters['user']['avatar'];?>);" title="Аватар пользователя">
	</div>
</div>
<div style="padding-bottom: 10px;"><a href="/Blog/showPostsByAuthor/<?=$parameters['user']['id'];?>">Все статьи автора</a></div>
<?if ($parameters['user']['id'] == Site::$attrs['id']):?>
<div><a href="/Site/profileForm/<?=$parameters['user']['id'];?>">Редактировать профиль</a></div>
<?endif;?>
