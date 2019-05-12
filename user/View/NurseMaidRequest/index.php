
<link rel="stylesheet" href="/agency/css/jquery-ui.css">
<link rel="stylesheet" href="/agency/css/flat-ui.css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3>Nursemaid request form</h3>
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
					Marital Status *
					</label>
					<?php echo $this->Form->input('marital_status', array(
						'options' => Configure::read('marital_status'),
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
						'empty' => '--'
					)); ?>
				</div>
				
				<div class="form-row">
					<label for="">
					Years of experience *
					</label>
					<?php echo $this->Form->input('years_experience', array(
						'type' => 'number',
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
						'options' => array(),
						'required' => true,
						'label' => false,
						'div'=> false,
						'class'=>'form-control',
					)); ?>
				</div>

				<div class="form-row">
					<div class="tagsinput-primary">
						<?php echo $this->Form->input('skills' , array(
								'type' => 'hidden',
								'value' => (isset($detail->hobby)? $detail->hobby : '')
						)) ?>
						<label for="">
						Skills *
						</label>
						<?php echo $this->Form->input('skills_val', array(
							'type' => 'text',
							'label' => false,
							'div' => false,
							'error' => false,
							'class' => 'form-control tagsinput',
							'data-role' => 'tagsinput',
							'name' => 'tagsinput',
							'value' => (isset($detail->hobby)? $detail->hobby : '')
						)) ?>
					</div>
				</div>
				<div class="form-row">
					<div class="tagsinput-primary">
						<?php echo $this->Form->input('jobs_experience' , array(
								'type' => 'hidden',
								'value' => (isset($detail->hobby)? $detail->hobby : '')
						)) ?>
						<label for="">
						Jobs Experience *
						</label>
						<?php echo $this->Form->input('jobs_experience_val', array(
							'type' => 'text',
							'label' => false,
							'div' => false,
							'error' => false,
							'class' => 'form-control tagsinput2',
							'data-role' => 'tagsinput',
							'name' => 'tagsinput',
							'value' => (isset($detail->hobby)? $detail->hobby : '')
						)) ?>
					</div>
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
<script src="/agency/js/flat-ui.js"></script>

<script type="text/javascript">
(function(){
	$(document).ready(function() {
		$("#NurseMaidBirthdate").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});

		$('#nurseMaidAdd').submit(function(){
			$('input[name="data[NurseMaid][skills]"]').val($('.tagsinput').val());
			$('input[name="data[NurseMaid][jobs_experience]"]').val($('.tagsinput2').val());
		});


	});
})();
</script>