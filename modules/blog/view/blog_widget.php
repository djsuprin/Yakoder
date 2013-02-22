<?foreach($parameters as $tag):?>
<a href="/Blog/showPostsByTag/<?=$tag['tag'];?>" style="font-size: <?=$tag['size'];?>em; font-weight: 300;"><?=$tag['tag'];?></a> 
<?endforeach;?>