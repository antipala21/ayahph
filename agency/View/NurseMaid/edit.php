<style type="text/css">

	.row.nurse-item {
		padding: 1em;
		margin: 25px;
		border:1px solid #d5dbdd;
		border-radius: 5px;
		box-shadow:0px 4px 2px rgba(0,0,0,0.04);
		background:#fff;
	}

	.row.nurse-item:hover {
		cursor: pointer;
	}

	.nurse-item-container {
		margin-top: 10px;
	}

	.page-wrapper {
		background: #FFF;
	}

	.media-right.align-self-center {
		text-align: center;
	}

	.agency-detail-container {
		margin-top: 25px;
	}

	.blocker {
		z-index: 60;
	}

	.modal a.close-modal {
		top: 1.5px;
		right: 0.5px;
	}

</style>

<link rel="stylesheet" href="/agency/css/jquery-ui.css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<div class="row nurse-item">
					<div class="col-md-2">
						<div class="media-left align-self-center">
							<img class="rounded-circle" src="https://randomuser.me/api/portraits/women/50.jpg">
						</div>
					</div>
					<div class="col-md-8">
					<?php echo $this->Form->create('NurseMaid', 
							array(
								'id' => 'nurseMaidAcountUpdate',
								'style' => '',
							)); ?>
						<div class="media-body">
							<?php echo $this->Form->hidden('id', array('value' => $nurse_maid['id'])); ?>
							<h4>First Name</h4>
								<?php echo $this->Form->input('first_name', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['first_name']
									)); ?>
							<h4>Last Name</h4>
									<?php echo $this->Form->input('last_lname', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['last_lname']
									)); ?>
							<h4>Self Introduction</h4>
								<?php echo $this->Form->input('self_introduction', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['self_introduction']
									)); ?>
							<h4>Address</h4>
								<?php echo $this->Form->input('address', array(
										'label' => false,
										'class' => 'form-control',
										'required' => true,
										'value' => $nurse_maid['address']
									)); ?>
							<h4>Gender</h4>
								<?php echo $this->Form->input('gender', array(
									'options' => Configure::read('gender_array'),
									'required' => true,
									'label' => false,
									'div'=> false,
									'class'=>'form-control',
									'value' => $nurse_maid['gender']
								)); ?>
							<h4>Birthday</h4>
								<?php echo $this->Form->input('birthdate', array(
									'type' => 'text',
									'required' => true,
									'label' => false,
									'div'=> false,
									'autocomplete' => 'off',
									'class'=>'form-control',
									'value' => $nurse_maid['birthdate']
								)); ?>
						</div>
					</div>
					<div class="col-12 nurse-item-container">
						<div class="media-right align-self-center">
							<button type="submit" class="btn btn-info">Save</button>
							<hr>
						</div>
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="/agency/js/jquery-ui.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$("#NurseMaidBirthdate").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});
	});
</script>