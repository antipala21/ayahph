<link rel="stylesheet" href="/css/daterangepicker.css">
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

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 agency-detail-container">
				<p><b>Agency Name: </b><?php echo isset($agency['name']) ? $agency['name'] : '-'; ?></p>
				<p><b>Agency Address: </b><?php echo isset($agency['address']) ? $agency['address'] : '-'; ?></p>
				<p><b>Agency Contact: </b><?php echo isset($agency['phone_number']) ? $agency['phone_number'] : '-'; ?></p>
			</div>
			<div class="col-md-12"><hr></div>
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<div class="row nurse-item">
					<div class="col-md-2">
						<div class="media-left align-self-center">
							<img class="rounded-circle" src="https://randomuser.me/api/portraits/women/50.jpg">
						</div>
					</div>
					<div class="col-md-10">
						<div class="media-body">
							<h4>
								<?php echo isset($nurse_maid['first_name']) ? $nurse_maid['first_name'] : '-' ?>
								<?php echo isset($nurse_maid['last_lname']) ? $nurse_maid['last_lname'] : '-' ?>
							</h4>
							<h3 style="color: #eaa70a">Rating: <span><b><?php echo isset($nurse_maid['rating']) ? round($nurse_maid['rating'],2) : '-' ?></b></span></h3>
							<p><?php echo isset($nurse_maid['self_introduction']) ? $nurse_maid['self_introduction'] : '-' ?></p>
							<h4>Address</h4>
							<p><?php echo isset($nurse_maid['address']) ? $nurse_maid['address'] : ' ' ?></p>
							<h4>Gender</h4>
							<p><?php 
								$gender_array = Configure::read('gender_array');
								echo isset($nurse_maid['gender']) ? $gender_array[$nurse_maid['gender']] : '-';
								 ?></p>
							<h4>Age</h4>
							<p><?php
								if(isset($nurse_maid['birthdate'])):
									$datetime1 = new DateTime($nurse_maid['birthdate']);
									$interval = $datetime1->diff( new DateTime());
									echo $interval->format('%y yrs');
								endif;
							?></p>
						</div>
					</div>
				</div>
				<div class="row nurse-item">
					<h2>Comments</h2>
					<hr>
					<div class="col-md-12">
					<?php if($comments): ?>
						<ul>
						<?php foreach($comments as $key => $value): ?>
							<li>
							<p><?php echo isset($value['NurseMaidRating']['comment']) ? $value['NurseMaidRating']['comment'] : '' ?> <span><small> || <?php echo isset($value['NurseMaidRating']['created']) ? date('Y-m-d h:i:sa', strtotime($value['NurseMaidRating']['created'])) : '' ?></small></span></p>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<div class="media-right align-self-center">
					<a href="#userHireForm" class="btn btn-info" rel="modal:open">Hire Now</a><hr>
				</div>
			</div>
		</div>
	</div>
</div>

<a href="#success_modal" id="trigger_success_modal" class="btn btn-info" rel="modal:open"></a>
<div class="modal hide" id="success_modal">
	<div>
		<p>Hire request sent!</p>
	</div>
</div>

<a href="#fail_modal" id="trigger_fail_modal" class="btn btn-info" rel="modal:open"></a>
<div class="modal hide" id="fail_modal">
	<div>
		<p>Fail send request!</p>
		<p>1. Already requested for this nursemaid.</p>
		<p>2. Network Error.</p>
	</div>
</div>
<?php echo $this->Form->create('Transaction',
		array(
			'id' => 'userHireForm',
			'class' => 'modal',
			'url' => array('controller' => 'Transaction','action' => 'saveRequest'),
		)); ?>

		<h3>Send Hire request.</h3>

		<label for=""> Comment * </label>
		<?php echo $this->Form->input('comment', array(
				'type' => 'textarea',
				'required' => true,
				'label' => false,
				'div'=> false,
				'class'=>'form-control',
				'autocomplete' => 'off'
		)); ?>

		<label for=""> Phone number * </label>
		<?php echo $this->Form->input('user_phone_number', array(
				'required' => true,
				'label' => false,
				'div'=> false,
				'class'=>'form-control',
				'autocomplete' => 'off'
		)); ?>

		<label for=""> Schedule Time * </label>
		<?php echo $this->Form->input('transaction_time', array(
				'type' => 'text',
				'required' => true,
				'label' => false,
				'div'=> false,
				'class'=>'form-control',
				'autocomplete' => 'off',
				'readonly' => true,
			)); ?>

		<label for=""> Address * </label>
		<?php echo $this->Form->input('user_address', array(
				'required' => true,
				'label' => false,
				'div'=> false,
				'class'=>'form-control',
				'autocomplete' => 'off'
		)); ?>
		<?php echo $this->Form->hidden('nurse_maid_id', array('value' => $nurse_maid['id'])); ?>
		<?php echo $this->Form->hidden('agency_id', array('value' => $agency['id'])); ?>

		<br>
		<br>
		<button class="btn btn-success">Send</button>
		<a href="#close-modal" rel="modal:close" class="close-modal ">Close</a>
<?php echo $this->Form->end(); ?>

<script src="/js/daterangepicker.min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){

		var today_date_range =new Date();

		$("#TransactionTransactionTime").daterangepicker({
			startDate: today_date_range,
			endDate: today_date_range,
			timePicker: true,
			timePicker12Hour:false,
			timePickerIncrement:1,
			opens: 'center',
			drops: 'up',
			ranges: {
				'Today': [moment(), moment()]
			},
			locale : {
				format:'YYYY-MM-DD HH:mm'
			}
		});

		$('#userHireForm').submit(function(e){
			e.preventDefault();

			var comment = $('#TransactionComment').val();
			var phone_number = $('#TransactionUserPhoneNumber').val();
			var nurse_maid_id = $('#TransactionNurseMaidId').val();
			var agency_id = $('#TransactionAgencyId').val();
			var user_address = $('#TransactionUserAddress').val();
			var transaction_time = $('#TransactionTransactionTime').val();

			console.log('comment ' + comment);
			console.log('phone_number ' + phone_number);
			console.log('user_address ' + user_address);

			$.ajax({
				type: 'POST',
				url: '/ajax/send_hire_request',
				data: {
					comment : comment,
					user_phone_number : phone_number,
					nurse_maid_id : nurse_maid_id,
					agency_id : agency_id,
					user_address : user_address,
					transaction_time : transaction_time
				},
				// dataType: 'json',
				success: function(data){
					var res = JSON.parse(data);
					if (res.sucess) {
						$('.close-modal').click();
						$('#trigger_success_modal').click();
					} else {
						$('.close-modal').click();
						$('#trigger_fail_modal').click();
					}
				},
				error: function(error){
					console.log('error' + error);
					console.log(error);
				}
			});

		});

	}); // end doc ready.
</script>