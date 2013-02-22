<script>
	$(document).ready(function() {
		console.log('?');
		$('.enterButton').click(function() {
			console.log('!');
			$('.enterForm').submit();
		});
	});
</script>

<div style="padding: 10px 10px 0 0;">
	<div>
		<form class="enterForm" action="#" method="POST">
			<div align="right">
				<label for="site_login">Логин</label>
				<input type="text" id="site_login" class="enterFormInput" name="site_login" value="" />
			</div>
			<div align="right">
				<label for="site_password">Пароль</label>
				<input type="password" id="site_password" class="enterFormInput" name="site_password" value="" />
			</div>
			<div align="right">
				<a href="#" class="enterButton">Войти</a>
			</div>
		</form>
	</div>
	Авторизуйся, используя свой	<a href="?by_google">Google</a> или 
	<a href="?by_yandex">Яндекс</a> аккаунт
</div>