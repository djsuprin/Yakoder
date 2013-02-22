<div>
<?foreach($parameters as $koder):?>
	<div class="site_widget_avatar">
		<a href="/Site/userInfo/<?=$koder['id'];?>">
		<div class="user_icon_container" style="background-image: url(<?=$koder['avatar'];?>);" title="<?=$koder['title'];?>">
		</div>
		</a>
	</div>
<?endforeach;?>
</div>
<div class="site_widget_allusers_link"><a href="/Site/showUsers">Все кодеры</a></div>