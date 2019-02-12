<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<h3>Add Address Name</h3>
		<br>
		<?php $flash = $this->Session->flash('lungsod-add-error'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<!-- Row -->
		<div class="row">
			<?php echo $this->Form->create('Lungsod', 
				array(
					'id' => 'lungsodAdd',
					'style' => '',
				)); ?>

				<h4></h4>
				<div class="form-row">
					<label for="">
					Address Name *
					</label>
					<?php echo $this->Form->input('name', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
						'autocomplete' => 'off'
					)); ?>
				</div>
				<div class="form-row">
					<br>
					<button type="submit" class="btn btn-success">Submit</button>
				</div>

			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>