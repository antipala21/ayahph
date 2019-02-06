<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Announcement</h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
					<li class="breadcrumb-item active">Update Announcement</li>
				</ol>
			</div>
		</div>

		<?php $flash = $this->Session->flash('announcement-maid-add-error'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<!-- Row -->
		<div class="row">
			<?php echo $this->Form->create('Announcement', 
				array(
					'id' => 'nurseMaidAdd',
					'style' => '',
				)); ?>

				<h4></h4>
				<div class="form-row">
					<label for="">
					Content *
					</label>
					<?php echo $this->Form->hidden('id', array('value' => $data['Announcement']['id'])); ?>
					<?php echo $this->Form->input('content', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
						'value' => $data['Announcement']['content']
					)); ?>
				</div>

				<div class="form-row">
					<br>
					<button type="submit" class="btn btn-success">Update</button>
					<button type="submit" name="delete" value="delete" class="btn btn-danger">Delete</button>
				</div>

			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>