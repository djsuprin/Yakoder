<div>
	<div class="logged_block_name">
		<div style="font-weight: 300; font-size: 1.4em; padding-bottom: 5px;">
			<?=Site::getName();?>
		</div>
		<div style="text-align: right;">
			<a href="/index.php/?exit">Выход</a>
		</div>
	</div>
	<div class="logged_block_avatar">
		<a href="/Site/userInfo/<?=Site::$attrs['id'];?>" title="Редактирование профиля">
			<div class="user_icon_container" style="background-image: url(<?=Site::$attrs['avatar'];?>);" title="Перейти к профилю">
			</div>
		</a>
	</div>
	<div style="clear: both;"></div>
</div>
