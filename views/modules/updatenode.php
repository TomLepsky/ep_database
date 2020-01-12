<div class="text-centered node">
	<p class="font-height"><?php echo $node->fileName; ?></p>		
</div>
<div class="form-wrapper">
	<form enctype="multipart/form-data" action="/updatenode/<?php echo $node->id; ?>/<?php echo $node->parentId; ?>/" method="post" id="update-node-form">

		<div class="form-row-15">
			<div class="form-col-8">
				<label for="prefix">Префикс</label>
				<input type="text" name="prefix" id="prefix" value="КУИЖ" placeholder="" required>
			</div>
			<div class="form-col-33">
				<label for="classifier">Классификатор</label>
				<select name="classifier" id="classifier" onchange="getClassifierNumber();">
					<?php foreach ($classifiers as $classifier) : ?>
					<option value="<?php echo $classifier->number; ?>" <?php echo $classifier->number == $node->classifier ? " selected" : ""; ?>><?php echo $classifier->number . ' ' . $classifier->name; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-col-8">
				<label for="number">Номер*</label>
				<input type="text" name="number" id="number" value="<?php echo $node->number; ?>" placeholder="" required>
			</div>
			<div class="form-col-33">
				<label for="detail_name">Наименование</label>
				<input type="text" name="name" id="detail_name" value="<?php echo $node->name; ?>" placeholder="" required>
			</div>
		</div>

		<?php if ($node instanceof Detail) : ?>
		<div class="form-row-15">
			<div class="form-col-74">
				<label for="detail_material">Материал</label>
				<select name="material" id="detail_material">
					<?php 
						$str = '';
						foreach($materials as $material) {
							$str .= $material->matchingId == $node->material[0]->matchingId ? 
								'<option value="' . $material->matchingId . '" selected>' . $material->name . ', ' . $material->measure . '</option>' : 
								'<option value="' . $material->matchingId . '">' . $material->name . ', ' . $material->measure . '</option>';   
						} 
						echo $str;
					?>
				</select>
			</div>
			<div class="form-col-23">
				<label for="detail_material_quantity">Кол-во материала</label>
				<input type="text" name="material_quantity" id="detail_material_quantity" value="<?php echo strval($node->material[0]->quantity); ?>">
			</div>
		</div>
		<?php endif; ?>

		<div class="form-row-15">
			<div class="form-col-23">
				<label for="detail_owner">Разработал</label>
				<select name="owner" id="detail_owner">
					<?php 
					$str = '';
					foreach($users as $user) {
						$str .= $user->name == $node->ownerName ? 
							'<option value="' . $user->id . '" selected>' . $user->name . '</option>' : '<option value="' . $user->id . '">' . $user->name . '</option>';   
					} 
					echo $str;
					?>
				</select>
			</div>
			<div class="form-col-23">
				<label for="detail_status">Статус</label>
				<select name="status" id="detail_status">
					<?php 
					foreach($statuses as $status) {
						echo '<option value="' . $status->id . '">' . $status->name . '</option>';   
					}
					?>
				</select>
			</div>
			<div class="form-col-23">
				<label for="sheet_format">Формат листа</label>
				<select name="sheet_format" id="sheet_format">
					<?php foreach ($sheetFormats as $format) : ?>
					<option value="<?php echo $format->id; ?>" <?php echo $format->id == $node->sheetFormat ? " selected" : ""; ?>><?php echo $format->name; ?></option>
					<?php endforeach; ?>	
				</select>
			</div>
			<div class="form-col-23">
				<label for="detail_quantity">Кол-во</label>
				<input type="text" name="quantity" id="detail_quantity" value="1" value="<?php echo $node->quantity; ?>" required>
			</div>
		</div>

		<div class="form-row-15">
			<div class="form-col-100">
				<label for="detail_note">Примечание</label>
				<textarea type="text" name="note" id="detail_note" rows="3" value="<?php echo $node->note; ?>"></textarea>
			</div>
		</div>

		<div class="form-row-10">
			<div class="form-col-23">
				<div class="file-upload">
					<label>
						<input type="file" name="inputfile2D" id="inputfile2D" onchange="getFile2DName();">
						<span>Чертёж узла</span>
					</label>
				</div>
			</div>
			<div class="form-col-23">
				<div id="file2D-name">

				</div>
			</div>
			<div class="form-col-23">
				<div class="file-upload">
					<label>
						<input type="file" name="inputfile3D" id="inputfile3D" onchange="getFile3DName();">
						<span>Модель узла</span>
					</label>
				</div>
			</div>
			<div class="form-col-23">
				<div id="file3D-name">

				</div>
			</div>
		</div>

		<div class="form-row-10">
			<div class="form-col-100">
				<input type="submit" name="submit" id="btn-update" value="Обновить">
				<p>* Не рекомендуется менять</p>
			</div>
		</div>

	</form>
</div>

<script>
	/*
$("#btn-update").on('click', function(event) {

	$.ajax({
		url: $("#update-node-form").attr("action"),
		type: "POST",
		data: $("#update-node-form").serialize(),
		success: function(response) { 
    		$('#node-info').html(response);
    		$('#tree').jstree(true).refresh();
    	}
	});

	return false;
});
*/
</script>