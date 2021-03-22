<?php echo $form->messages(); ?>
<?php /*echo $message */?>
<div class="row">

	<div class="col-md-6">
		<div class="box box-primary">
			<div class="box-body">
				<?php echo $form->open(); ?>
					<?php echo $form->bs3_text('Message', 'message'); ?>

					<?php echo $form->bs3_submit(); ?>
				<?php echo $form->close(); ?>
			</div>
		</div>
	</div>
	
</div>