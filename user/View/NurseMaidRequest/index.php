
<link rel="stylesheet" href="/agency/css/jquery-ui.css">
<link rel="stylesheet" href="/agency/css/flat-ui.css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<?php if (!empty($nurse_maids)): ?>
					<h3>Nursemaid Recommended List</h3>
				<?php else: ?>
					<h3>Nursemaid request form</h3>
				<?php endif; ?>
			</div>
		</div>

		<?php $flash = $this->Session->flash('nurse-maid-add-error'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($nurse_maids)): ?>

			<div class="row result_form">

				<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
					
					<?php foreach($nurse_maids as $key => $value): ?>
						<div class="row nurse-item">
							<div class="col-md-2">
								<div class="media-left align-self-center">
									<?php if(isset($value['NurseMaid']['image_url']) && !empty($value['NurseMaid']['image_url'])): ?>
										<img style="width: 125px;" class="rounded-circle"src="<?php echo myTools::getProfileImgSrcAgency($value['NurseMaid']['image_url']); ?>" alt="Avatar">
									<?php else: ?>
										<img class="rounded-circle" src="https://randomuser.me/api/portraits/women/50.jpg">
									<?php endif; ?>
								</div>
							</div>
							<div class="col-md-8">
								<div class="media-body">
									<h4>
										<?php echo isset($value['NurseMaid']['first_name']) ? $value['NurseMaid']['first_name'] : ' ' ?>
										<?php echo isset($value['NurseMaid']['last_lname']) ? $value['NurseMaid']['last_lname'] : ' ' ?>
									</h4>
									<p><?php echo isset($value['NurseMaid']['self_introduction']) ? $value['NurseMaid']['self_introduction'] : ' ' ?></p>
									<h5 style="color: #eaa70a">Rating : <span><b><?php echo isset($value['NurseMaid']['rating']) ? round($value['NurseMaid']['rating'],2) . ' <i class="fa fa-star text-warning" aria-hidden="true"></i>' : '-' ?></b></span>
									</h5>
									<h5>Total hire : 
										<span>
											<?php echo isset($value['NurseMaid']['total_hire']) ? $value['NurseMaid']['total_hire'] : '0' ?>
										</span>
									</h5>
									<h5>Address : 
										<span>
											<?php echo isset($value['NurseMaid']['address']) ? $value['NurseMaid']['address'] : '0' ?>
										</span>
									</h5>
									<h5>Gender : 
										<span>
											<?php 
											$gender_array = Configure::read('gender_array');
											echo isset($value['NurseMaid']['total_hire']) ? $gender_array[$value['NurseMaid']['gender']] : '-' ?>
										</span>
									</h5>
									<h5>Age : 
										<span>
											<?php 
											if(isset($value['NurseMaid']['birthdate'])):
												$datetime1 = new DateTime($value['NurseMaid']['birthdate']);
												$interval = $datetime1->diff( new DateTime());
												echo $interval->format('%y yrs');
											endif; ?>
										</span>
									</h5>
									<h5>Skills : 
										<span><?php echo isset($value['NurseMaid']['skills']) ? $value['NurseMaid']['skills'] : ' ' ?></span>
									</h5>
									<h5>Jobs Experience : 
										<span><?php echo isset($value['NurseMaid']['jobs_experience']) ? $value['NurseMaid']['jobs_experience'] : ' ' ?></span>
									</h5>
									<h5>Years Experience : 
										<span><?php echo isset($value['NurseMaid']['years_experience']) ? $value['NurseMaid']['years_experience'] : 0 ?></span>
									</h5>
								</div>
							</div>
							<div class="col-md-2">
								<div class="media-right align-self-center">
									<a href="/agency-nursemaid-detail/<?php echo isset($value['NurseMaid']['id']) ? $value['NurseMaid']['id'] : null; ?>" class="btn btn-info">View</a><hr>
									<a href="#userHireForm" class="btn btn-info hire_btn" rel="modal:open" data-nurse_maid_id="<?php echo $value['NurseMaid']['id'] ?>" data-agency_id="<?php echo $value['NurseMaid']['agency_id'] ?>" data-nursemaid_name="<?php echo $value['NurseMaid']['first_name'] ?>">Hire Now</a><hr>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		<?php else: ?>
			<!-- Row -->
			<div class="row request_form">
				<?php echo $this->Form->create('NurseMaidRequest', 
					array(
						'id' => 'nurseMaidRequest',
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
							'class'=>'form-control'
						)); ?>
					</div>
					<div class="form-row">
						<label for="">
						Years of experience *
						</label>
						<?php echo $this->Form->input('years_experience', array(
							'options' => Configure::read('years_experience'),
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
							'default' => 0
						)); ?>
					</div>
					<div class="form-row">
						<label for="">
						Education *
						</label>
						<?php echo $this->Form->input('education', array(
							'options' => Configure::read('education_list'),
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control'
						)); ?>
					</div>
					<div class="form-row">
						<label for="">
						Address *
						</label>
						<?php echo $this->Form->input('address', array(
							'options' => $address,
							'required' => true,
							'label' => false,
							'div'=> false,
							'class'=>'form-control',
						)); ?>
					</div>

					<div class="form-row">
						<div class="tagsinput-primary">
							<label for="">
							Skills *
							</label>
							<?php echo $this->Form->input('skills', array(
								'type' => 'text',
								'label' => false,
								'div' => false,
								'error' => false,
								'class' => 'form-control tagsinput'
							)) ?>
						</div>
					</div>
					<div class="form-row">
						<div class="tagsinput-primary">
							<label for="">
							Jobs Experience *
							</label>
							<?php echo $this->Form->input('jobs_experience', array(
								'type' => 'text',
								'label' => false,
								'div' => false,
								'error' => false,
								'class' => 'form-control tagsinput2'
							)) ?>
						</div>
					</div>
					<div class="form-row">
						<br>
						<button type="submit" class="btn btn-success">Submit</button>
					</div>

				<?php echo $this->Form->end(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<script src="/agency/js/jquery-ui.js"></script>
<script src="/agency/js/flat-ui.js"></script>

<script type="text/javascript">
(function(){
	$(document).ready(function() {

		// $('#nurseMaidRequest').submit(function(){
		// 	$('input[name="data[NurseMaidRequest][skills]"]').val($('.tagsinput').val());
		// 	$('input[name="data[NurseMaidRequest][jobs_experience]"]').val($('.tagsinput2').val());
		// });


	});
})();
</script>