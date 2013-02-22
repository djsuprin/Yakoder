<div class="message_box">
<ul>
<?php
	foreach ($parameters as $message)
	{
?>
<li class="<?=$message['type'];?>"><?=$message['message'];?></li>
<?php
	}
?>
</ul>
</div>