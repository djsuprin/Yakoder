<h1>Блог</h1>
<?if (Site::isAllowed('addPost', 'Blog')):?>
<div style="padding: 0px 10px 10px 10px; font-size: 16px; text-align: right;">
	<a href="/Blog/showPostAddForm">Добавить статью</a>
</div>
<?endif;?>

<script src="/js/content_editor.js"></script>

<?php
	foreach ($parameters['posts'] as $post)
	{
		$tags = explode(',', str_replace(' ', '', $post['tags']));
		$author = $post['author_email'];
		if (trim($post['author_name']) != '')
		{
			$author = $post['author_name'];
		}
?>
<div>
	<div class="post_header"><a href="/Blog/showPost/<?=$post['id'];?>"><?=$post['header'];?></a></div>
	<div id="post-preview-<?=$post['id'];?>" class="post_text"><?=$post['preview'];?></div>
	<div style="text-align: right; margin-bottom: 10px;">
		<?if(trim($post['text']) != ''):?>
		<a href="/Blog/showPost/<?=$post['id'];?>">Читать дальше</a>
		<?endif;?>
	</div>
	
	<div class="post_icon_and_prop">
		<?if (Blog::checkPostOwner($post['id']) && Site::isAllowed('deletePost', 'Blog')):?>
		<div class="icon_small post_icon_and_prop">
			<a href="/Blog/deletePost/<?=$post['id'];?>" title="Удалить">&#x2718;</a>
		</div>
		<?endif;?>
		<?if (Blog::checkPostOwner($post['id']) && Site::isAllowed('editPost', 'Blog')):?>
		<div class="icon_small post_icon_and_prop">
			<a href="/Blog/showPostEditForm/<?=$post['id'];?>" title="Редактировать">&#x270e;</a>
		</div>
		<?endif;?>
		<div class="post_icon_and_prop">
			<div class="icon_small" style="display: table-cell;">&#xe079;</div>
			<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
				<nobr><?=date("H:i d.m.Y", $post['edit_date']);?></nobr>
			</div>
		</div>
		<div class="post_icon_and_prop">
			<div class="icon_small" style="display: table-cell;">&#xe02b;</div>
			<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
				<nobr>
				<?for ($i = 0; $i < count($tags) - 1; $i++):?>
				<a href="/Blog/showPostsByTag/<?=$tags[$i];?>"><?=$tags[$i];?></a>,&nbsp;
				<?endfor;?>
				<a href="/Blog/showPostsByTag/<?=$tags[count($tags) - 1];?>"><?=$tags[count($tags) - 1];?></a>
				</nobr>
			</div>
		</div>
		<div class="post_icon_and_prop">
			<div class="icon_small" style="display: table-cell;">&#xe062;</div>
			<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
				<nobr>
					<a href="/Site/userInfo/<?=$post['author_id'];?>"><?=$author;?></a>
				</nobr>
			</div>
		</div>
	</div>
</div>
<div class="post_splitter"></div>
<?if (Blog::checkPostOwner($post['id']) && Site::isAllowed('editPost', 'Blog')):?>
<script>
	$(document).ready(function() {
		new ContentEditor($('#post-preview-<?=$post['id'];?>'), function() {
			$.post('/blog/editPreview/<?=$post['id'];?>', {'preview': $('#post-preview-<?=$post['id'];?>').html()},
			function(data) {
				console.log(data);
			});
		});
	});
</script>
<?endif;?>
<?php
	}
	Blog::pageNavigator(3);
?>