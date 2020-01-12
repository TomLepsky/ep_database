<div class="text-centered node">
	<p class="font-height"><?php echo $node->fileName; ?></p>
	<p class="font-height">Добавление cуществующего узла</p>		
</div>
<div class="form-wrapper">
	<form action="/copynode/<?php echo $node->id; ?>/" id="copy-node-form">
		<input type="hidden" name="copy_node" value="true">

		<?php foreach($nodes as $elem): ?>
		<div class="form-row-10">
			<div class="form-col-100">
				<input type="radio" name="node_to_add" value="<?php echo $elem->id;?>" id="<?php echo $elem->id;?>">
				<label  for="<?php echo $elem->id;?>">
					<?php echo $elem->fileName; ?>
				</label>		
			</div>
		</div>
		<?php endforeach; ?>

		<div class="form-row-10">
			<div class="form-col-100">
				<input type="submit" name="submitbtn" id="btn-copy" value="Добавить">
			</div>
		</div>
	</form>
</div>

<script>
$("#btn-copy").on('click', function(event) {
	$.ajax({
		url: $("#copy-node-form").attr("action"),
		type: "POST",
		data: $("#copy-node-form").serialize(),
		success: function(response) { 
    		$('#node-info').html(response);
    		$('#tree').jstree(true).refresh();
    	}
	});

	return false;
});
</script>