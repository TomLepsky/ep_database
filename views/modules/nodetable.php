<table class="node-table">
	<thead>
		<tr>
			<th id="exist2D"></th>
			<th id="exist3D"></th>
			<th id="name">Наименование</th>
			<th id="owner">Разраб.</th>
			<th id="status">Статус</th>
			<th id="quantity">Кол-во</th>
			<th id="material">Материал</th>
			<th id="material_quantity">Кол-во материала</th>
			<th id="note">Примечание</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($toShow as $n) : ?>
		<tr>
			<td id="exist2D">
				<img src="/images/<?php echo $n->exist2D ? "nodeexist.jpg" : "nodeabsent.jpg";?>" class="mx-auto margin">
			</td>
			<td id="exist3D">
				<img src="/images/<?php echo $n->exist3D ? "nodeexist.jpg" : "nodeabsent.jpg";?>" class="mx-auto margin">
			</td>
			<td id="name">
				<a href="<?php echo WORK_FOLDER . $n->path; ?>" download><?php echo $n->fileName;?></a>
			</td>
			<td id="owner">
				<?php echo $n->ownerName;?>
			</td>
			<td id="status">
				<?php echo Status::getStatus($n->status);?>
			</td>
			<td id="quantity">
				<?php echo $n->quantity;?>
			</td>
			<?php if ($n instanceof Detail) : ?>
			<td id="material">
				<?php echo $n->material[0]->name; ?>
			</td>
			<td id="material_quantity">
				<?php 
				if ($node instanceof Assembly) 
					echo $n->material[0]->quantity * $n->quantity; 
				else
					echo $n->material[0]->quantity;
				?>
			</td>
			<?php else: ?>
			<td>---</td>
			<td>---</td>
			<?php endif; ?>
			<td id="note">
				<?php echo $n->note;?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<script>
$(".copy-file1").on('click', function(event) {
	event.preventDefault();
	//var newWin = window.open($(this).attr('href'));

	$.ajax({
		url: $(this).attr('href'),
		type: "POST",
		success: function(response) { 
			//var data = jQuery.parseJSON(response);
			//console.log(response);
    		//$('#node-info').html(response);
    		//download("ертеж.dwg", response);
    	}
	});

	return false;
});

function download(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}

</script>