<?php 
	Site::addHeadCode('<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?31"></script>');
?>
<script src="/js/content_editor.js"></script>
<h1><?=$parameters['header'];?></h1>
<div style="margin: 0 10px 0 10px;">
	<div id="post-preview-editable" class="post_text"><?=$parameters['preview'];?></div>
	<div id="post-text-editable" class="post_text"><?=$parameters['text'];?></div>
	<div>
		<div style="margin-right: 10px; font-size: 0px; display:table-cell; vertical-align: middle;">
			<a href="/Site/userInfo/<?=$parameters['author_id'];?>" title="Автор статьи">
				<img src="<?=$parameters['avatar'];?>" />
			</a>
		</div>
		<div style="font-size: 14px; padding-left: 20px; display:table-cell; vertical-align: middle;">
			<div style="margin-bottom: 5px;">
				<div style="display: table-cell;">
					<span class="oi" data-glyph="person" title="Автор" aria-hidden="true"></span>
				</div>
				<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
					<a href="/Site/userInfo/<?=$parameters['author_id'];?>"><?=$parameters['author'];?></a>
				</div>
				<div style="padding-left: 10px; display: table-cell;">
					<span class="oi" data-glyph="clock" title="Дата последнего обновления" aria-hidden="true"></span>
				</div>
				<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
					<?=date("H:i d.m.Y", $parameters['edit_date']);?>
				</div>
			</div>
			<div style="margin-top: 5px;">
				<div style="display: table-cell;">
					<span class="oi" data-glyph="tag" title="Тэги" aria-hidden="true"></span>
				</div>
				<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
					<?for ($i = 0; $i < count($parameters['tags']) - 1; $i++):?>
					<a href="/Blog/showPostsByTag/<?=$parameters['tags'][$i];?>">
						<?=$parameters['tags'][$i];?>
					</a>,&nbsp;
					<?endfor;?>
					<a href="/Blog/showPostsByTag/<?=$parameters['tags'][count($parameters['tags']) - 1];?>">
						<?=$parameters['tags'][count($parameters['tags']) - 1];?>
					</a>
				</div>
				<?if (Blog::checkPostOwner($parameters['id']) && Site::isAllowed('editPost', 'Blog')):?>
				<div style="padding-left: 10px; display: table-cell;">
					<a href="/Blog/showPostEditForm/<?=$parameters['id'];?>"><span class="oi" data-glyph="pencil" title="Редактировать" aria-hidden="true"></span></a>
				</div>
				<?endif;?>
				<?if (Blog::checkPostOwner($parameters['id']) && Site::isAllowed('deletePost', 'Blog')):?>
				<div style="padding-left: 10px; display: table-cell;">
					<a href="/Blog/deletePost/<?=$parameters['id'];?>" title="Удалить"><span class="oi" data-glyph="circle-x" title="Удалить" aria-hidden="true"></span></a>
				</div>
				<?endif;?>
			</div>
		</div>
	</div>
</div>
<div class="post_splitter"></div>

<?if (Blog::checkPostOwner($parameters['id']) && Site::isAllowed('editPost', 'Blog')):?>
<script>
	$(document).ready(function() {
		console.log('!!!');
		new ContentEditor($('#post-preview-editable'), function() {
			$.post('/blog/editPreview/<?=$parameters['id'];?>', {'preview': $('#post-preview-editable').html()},
			function(data) {
				console.log(data);
			});
		});
		new ContentEditor($('#post-text-editable'), function() {
			$.post('/blog/editText/<?=$parameters['id'];?>', {'text': $('#post-text-editable').html()},
			function(data) {
				console.log(data);
			});
		});
	});
</script>
<?endif;?>

<div id="disqus_thread"></div>
<script type="text/javascript">
	/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
	var disqus_shortname = 'yakoderru'; // required: replace example with your forum shortname
	var disqus_identifier = '<?=Site::$argsString;?>';
	var disqus_title = '<?=$parameters['header'];?>';

	/* * * DON'T EDIT BELOW THIS LINE * * */
	(function() {
		var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
		dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	})();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>


<script src="/js/content_editor.js"></script>
<script src="/modules/blog/view/js/blog.js"></script>