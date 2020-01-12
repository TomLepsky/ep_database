<div class="text-centered node">
	<ul class="topmenu">
		<li class="more"><?php echo $node->fileName; ?>
			<?php if (User::isAdmin()) : ?>
			<ul class="submenu">
				<?php if ($node instanceof Assembly) : ?>
	    		<li><a class="dropdown-item assynch-link" href="/addnewnode/<?php echo $node->id;?>/<?php echo Node::DETAIL; ?>/">Добавить новую деталь</a></li>
	    		<li><a class="dropdown-item assynch-link" href="/addnewnode/<?php echo $node->id; ?>/<?php echo Node::ASSEMBLY; ?>/">Добавить новую сборочную еденицу</a></li>
	    		<li><a class="dropdown-item assynch-link" href="/addnewnode/<?php echo $node->id; ?>/<?php echo Node::PARTITION; ?>/">Добавить новую сборочную еденицу</a></li>
				<li><a class="dropdown-item assynch-link" href="/copynode/<?php echo $node->id; ?>/">Добавить существующий узел</a></li>
				<li><a class="dropdown-item assynch-link node-link" href="/specification/<?php echo $node->id; ?>/<?php echo $node->parentId;?>">Создать спецификацию</a></li>
		    <?php endif; ?>
		    	<li><a class="dropdown-item assynch-link" href="/updateproduct/<?php echo $node->id;?>/<?php echo $node->parentId; ?>/">Редактировать</a></li>
	 			<li><a class="dropdown-item update-price" href="#/">Обновить цену</a></li>
	 			<li><a class="dropdown-item node-link" href="/removenode/<?php echo $node->id;?>/<?php echo $node->parentId; ?>/">Убрать из сборки</a></li>
	 			<li><a class="dropdown-item node-link" href="/deletenode/<?php echo $node->id; ?>/">Удалить</a></li> 
			</ul>
			<?php endif; ?>
		</li>
	</ul>
</div>
		
<script>

$(".assynch-link").on('click', function(event) {
	event.preventDefault(); 
	$('#node-info').load($(this).attr('href'));
});

$(".node-link").on('click', function(event) {
	event.preventDefault();

	$.ajax({
		url: $(this).attr('href'),
		type: "POST",
		success: function(response) { 
    		$('#node-info').html(response);
    		$('#tree').jstree(true).refresh();
    	}
	});

	return false;
});

$(".update-price").on('click', function() {
	event.preventDefault();
	$.ajax({
		url: $(this).attr('href'),
		type: "POST",
		success: function(response) {
			
		}
	});
});

</script>