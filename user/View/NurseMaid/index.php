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
		right: 0;
	}

	div#ui-datepicker-div {
		z-index: 99 !important;
	}

</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12"><hr></div>
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<?php if ($nurse_maids): ?>
				<h3>Nursemaid List</h3>
				<?php foreach($nurse_maids as $key => $value): ?>
					<div class="row nurse-item">
						<div class="col-md-2">
							<div class="media-left align-self-center">
								<?php if(isset($value['NurseMaid']['image_url']) && !empty($value['NurseMaid']['image_url'])): ?>
									<img style="width: 125px;" class="rounded-circle" src="<?php echo myTools::getProfileImgSrcAgency($value['NurseMaid']['image_url']); ?>" alt="Avatar">
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
								<h5 style="color: #eaa70a">Rating: <span>
									<b>
										<?php echo isset($value['NurseMaid']['rating']) ? round($value['NurseMaid']['rating'],2) . ' <i class="fa fa-star text-warning" aria-hidden="true"></i>' : '-' ?>
									</b></span></h5>
								<p><?php echo isset($value['NurseMaid']['self_introduction']) ? $value['NurseMaid']['self_introduction'] : ' ' ?></p>
							</div>
						</div>
						<div class="col-md-2">
							<div class="media-right align-self-center">
								<a href="/agency-nursemaid-detail/<?php echo isset($value['NurseMaid']['id']) ? $value['NurseMaid']['id'] : null; ?>" class="btn btn-info">View</a><hr>
								<a href="#userHireForm" class="btn btn-info hire_btn" rel="modal:open" data-nurse_maid_id="<?php echo $value['NurseMaid']['id'] ?>" data-agency_id="<?php echo $value['NurseMaid']['agency_id'] ?>">Hire Now</a><hr>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				<?php else: ?>
				<h3>Nursemaid List is empty.</h3>
				<?php endif; ?>
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
		<?php echo $this->Form->hidden('nurse_maid_id', array('value' => null)); ?>
		<?php echo $this->Form->hidden('agency_id', array('value' => null)); ?>

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

		$('a.hire_btn').click(function(){
			$('#TransactionNurseMaidId').val($(this).attr('data-nurse_maid_id'));
			$('#TransactionAgencyId').val($(this).attr('data-agency_id'));
		});

		$('#userHireForm').submit(function(e){
			e.preventDefault();

			var nurse_maid_id = $('#TransactionNurseMaidId').val();
			var agency_id = $('#TransactionAgencyId').val();

			var comment = $('#TransactionComment').val();
			var phone_number = $('#TransactionUserPhoneNumber').val();
			var user_address = $('#TransactionUserAddress').val();
			var transaction_time = $('#TransactionTransactionTime').val();

			console.log('nurse_maid_id ' + nurse_maid_id);
			console.log('agency_id ' + agency_id);
			console.log('comment ' + comment);
			console.log('phone_number ' + phone_number);
			console.log('user_address ' + user_address);
			console.log('transaction_time ' + transaction_time);

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