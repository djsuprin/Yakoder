<h1>Редактирование профиля</h1>
<form method="POST" action="/Site/editProfile/<?=$parameters['user']['id'];?>" enctype="multipart/form-data">
<h2>Основная информация</h2>
<div style="display: table;">

    <div style="display: table-row;">
        <div style="display: table-cell; padding-right: 5px; vertical-align: middle;">Имя:</div>
        <div style="display: table-cell; vertical-align: middle;">
        	<input type="text" name="new_name" value="<?=$parameters['user']['name'];?>" />
        </div>
    </div>
    
    <div style="display: table-row;">
        <div style="display: table-cell; padding-right: 5px; vertical-align: middle;">E-mail:</div>
        <div style="display: table-cell; vertical-align: middle;">
        	<!--<input type="text" name="new_email" value="<?=$parameters['user']['email'];?>" />-->
        	<?=$parameters['user']['email'];?>
        </div>
    </div>

    <h2>Смена аватара</h2>
    
    <div style="display: table-row;">
        <div style="display: table-cell; padding-right: 10px; vertical-align: top;"><img src="<?=$parameters['user']['avatar'];?>" border="0" /></div>
        <div style="display: table-cell; vertical-align: middle;">
        	Внимание, любой загружаемый аватар будет:
        	<ol>
        		<li>обрезан до размеров NxN, где N - меньшая сторона аватара;</li>
        		<li>масштабирован до размеров 50х50.</li>
        	</ol>
        </div>
    </div>
    
    <div style="display: table-row;">
        <div style="display: table-cell; padding-right: 5px; vertical-align: middle;">Файл:</div>
        <div style="display: table-cell; vertical-align: middle;"><input type="file" name="new_avatar" /></div>
    </div>

</div>
<div style="padding-top: 10px;"><input type="submit" value="Сохранить" /></div>
</form>