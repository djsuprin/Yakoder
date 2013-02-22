<? Site::addHeadCode('<script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script>'); ?>
<h1><?=$parameters['form_caption'];?></h1>
<form action="<?=$parameters['link'];?>" method="POST">
	<div style="width: 100%;">
		<div style="padding-bottom: 5px; text-align: left; font-size: 14px;">Заголовок</div>
        <div style="padding-bottom: 10px;">
			<input type="text" name="header" class="post_form_element" value="<?=$parameters['header'];?>" />
		</div>
        <div style="padding-bottom: 5px; text-align: left; font-size: 14px;">Анонс</div>
        <div style="padding-bottom: 10px;">
			<textarea id="preview" name="preview" class="post_form_element" style="height: 300px;">
				<?=$parameters['preview'];?>
			</textarea>
			<script type="text/javascript">CKEDITOR.replace('preview');</script>
		</div>
        <div style="padding-bottom: 5px; text-align: left; font-size: 14px;">Текст статьи</div>
        <div style="padding-bottom: 10px;">
			<textarea id="text" name="text" class="post_form_element" style="height: 400px;">
				<?=$parameters['text'];?>
			</textarea>
			<script type="text/javascript">CKEDITOR.replace('text');</script>
		</div>
        <div style="padding-bottom: 5px; text-align: left; font-size: 14px;">Тэги, (перечисляй через запятую)</div>
        <div style="padding-bottom: 10px;"><input type="text" name="tags" class="post_form_element" value="<?=$parameters['tags'];?>" /></div>
        <div><input type="submit" value="<?=$parameters['button_caption'];?>" /></div>
	</div>
</form>