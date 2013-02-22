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
				<div class="icon_small" style="display: table-cell;">&#xe062;</div>
				<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
					<a href="/Site/userInfo/<?=$parameters['author_id'];?>"><?=$parameters['author'];?></a>
				</div>
				<div class="icon_small" style="padding-left: 10px; display: table-cell;">&#xe079;</div>
				<div style="padding-left: 5px; display:table-cell; vertical-align: middle;">
					<?=date("H:i d.m.Y", $parameters['edit_date']);?>
				</div>
			</div>
			<div style="margin-top: 5px;">
				<div class="icon_small" style="display: table-cell;">&#xe02b;</div>
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
				<div class="icon_small" style="padding-left: 10px; display: table-cell;">
					<a href="/Blog/showPostEditForm/<?=$parameters['id'];?>" title="Редактировать">&#x270e;</a>
				</div>
				<?endif;?>
				<?if (Blog::checkPostOwner($parameters['id']) && Site::isAllowed('deletePost', 'Blog')):?>
				<div class="icon_small" style="padding-left: 10px; display: table-cell;">
					<a href="/Blog/deletePost/<?=$parameters['id'];?>" title="Удалить">&#x2718;</a>
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

<script type="text/javascript">
  VK.init({apiId: 2350245, onlyWidgets: true});
</script>

<!-- Put this div tag to the place, where the Comments block will be -->
<div id="vk_comments"></div>
<script type="text/javascript">
VK.Widgets.Comments("vk_comments", {limit: 20, width: "300", attach: "*", autoPublish: 0});
</script> 

<script src="/js/content_editor.js"></script>
<script src="/modules/blog/view/js/blog.js"></script>