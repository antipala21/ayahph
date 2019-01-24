
<link rel="stylesheet" href="/agency/css/jquery-ui.css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Nursemaid</h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
					<li class="breadcrumb-item active">Add Nursemaid</li>
				</ol>
			</div>
		</div>

		<?php $flash = $this->Session->flash('nurse-maid-add-error'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<!-- Row -->
		<div class="row">
			<?php echo $this->Form->create('NurseMaid', 
				array(
					'id' => 'nurseMaidAdd',
					'style' => '',
				)); ?>

				<h4></h4>
				<div class="form-row">
					<label for="">
					First Name *
					</label>
					<?php echo $this->Form->input('first_name', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
					)); ?>
				</div>
				<div class="form-row">
					<label for="">
					Middle Name *
					</label>
					<?php echo $this->Form->input('middle_name', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
					)); ?>
				</div>
				<div class="form-row">
					<label for="">
					Last Name *
					</label>
					<?php echo $this->Form->input('last_name', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
					)); ?>
				</div>
				<div class="form-row">
					<label for="">
					Gender *
					</label>
					<?php echo $this->Form->input('gender', array(
						'options' => Configure::read('gender_array'),
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
					)); ?>
				</div>
				<div class="form-row">
					<label for="">
					Birthdate *
					</label>
					<?php echo $this->Form->input('birtdate', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
					)); ?>
				</div>
				<div class="form-row">
					<label for="">
					Address *
					</label>
					<?php echo $this->Form->input('address', array(
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
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

<script src="/agency/js/jquery-ui.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$("#NurseMaidBirtdate").datepicker({
			changeMonth: true,
			changeYear: true
		});
	});
</script>