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
		right: 0

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
				<?php if ($nurse_maids): ?>
				<h3>Nursemaid List</h3>
				<?php foreach($nurse_maids as $key => $value): ?>
					<div class="row nurse-item">
						<div class="col-md-2">
							<div class="media-left align-self-center">
								<img class="rounded-circle" src="https://randomuser.me/api/portraits/women/50.jpg">
							</div>
						</div>
						<div class="col-md-8">
							<div class="media-body">
								<h4>
									<?php echo isset($value['NurseMaid']['first_name']) ? $value['NurseMaid']['first_name'] : ' ' ?>
									<?php echo isset($value['NurseMaid']['last_lname']) ? $value['NurseMaid']['last_lname'] : ' ' ?>
								</h4>
								<p><?php echo isset($value['NurseMaid']['self_introduction']) ? $value['NurseMaid']['self_introduction'] : ' ' ?></p>
							</div>
						</div>
						<div class="col-md-2">
							<div class="media-right align-self-center">
								<a href="/agency-nursemaid-detail/<?php echo isset($value['NurseMaid']['id']) ? $value['NurseMaid']['id'] : null; ?>" class="btn btn-info">View</a><hr>
								<a href="#userHireForm" class="btn btn-info" rel="modal:open">Hire Now</a><hr>
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



<?php echo $this->Form->create('Transaction',
		array(
			'id' => 'userHireForm',
			'class' => 'modal',
			// 'style' => 'display: inline-block;',
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
		)); ?>

		<label for=""> Phone number * </label>
		<?php echo $this->Form->input('user_phone_number', array(
				'required' => true,
				'label' => false,
				'div'=> false,
				'class'=>'form-control',
		)); ?>

		<label for=""> Address * </label>
		<?php echo $this->Form->input('user_address', array(
				'required' => true,
				'label' => false,
				'div'=> false,
				'class'=>'form-control',
		)); ?>
		<?php echo $this->Form->hidden('nurse_maid_id', array('value' => $nurse_maid['id'])); ?>
		<?php echo $this->Form->hidden('agency_id', array('value' => $agency['id'])); ?>

		<br>
		<br>
		<button class="btn btn-success">Send</button>
		<a href="#close-modal" rel="modal:close" class="close-modal ">Close</a>
<?php echo $this->Form->end(); ?>



<script type="text/javascript">
	$(document).ready(function(){

		$('#userHireForm').submit(function(e){
			e.preventDefault();

			var comment = $('#TransactionComment').val();
			var phone_number = $('#TransactionUserPhoneNumber').val();
			var nurse_maid_id = $('#TransactionNurseMaidId').val();
			var agency_id = $('#TransactionAgencyId').val();
			var user_address = $('#TransactionUserAddress').val();

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
					user_address : user_address
				},
				// dataType: 'json',
				success: function(data){
					var res = JSON.parse(data);
				},
				error: function(error){
					console.log('error' + error);
					console.log(error);
				},
				complete: function(){
					$('.close-modal').click();
				}
			});

		});

	}); // end doc ready.
</script>