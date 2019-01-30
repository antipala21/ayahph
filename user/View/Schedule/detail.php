<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Schedules </h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Schedule</a></li>
					<li class="breadcrumb-item active">Schedule Detail</li>
				</ol>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2>Schedule</h2>
				<?php echo $this->Form->create('Schedule',array(
						'id' => 'userCompleteTransaction',
						'url' => array('controller' => 'Schedule','action' => 'completeTransaction'),
					)); ?>
				<?php echo $this->Form->hidden('id', array('value' => $schedule['Transaction']['id'])); ?>
				<input class="btn btn-success" type="submit" name="value_transaction" value="Complete">
				<!-- <input class="btn btn-danger" type="submit" name="value_transaction" value="Decline"> -->
				<?php echo $this->Form->end(); ?>
				<hr>
			</div>
			<div class="col-md-8">
				<div>
					<b>Schedule Time</b>
					<p>
						From: <br>
						<span><?php echo date('Y-m-d h:i:sa', strtotime($transaction['Transaction']['transaction_start'])) ?></span>
						<br> To: <br>
						<span><?php echo date('Y-m-d h:i:sa', strtotime($transaction['Transaction']['transaction_end'])) ?></span>
					</p>
				</div>
			</div>
			<div class="col-md-8">
				<div>
					<b>Content</b>
					<p><?php echo $schedule['Transaction']['comment'] ?></p>
				</div>
			</div>
			<div class="col-md-8">
				<div>
					<b>Agency Name</b>
					<p><?php echo $schedule['Agency']['name'] ?></p>
				</div>
				<div>
					<b>Address</b>
					<p><?php echo $schedule['Transaction']['address'] ?></p>
				</div>
				<div>
					<b>Contact No.</b>
					<p><?php echo $schedule['Transaction']['user_phone_number'] ?></p>
				</div>
			</div>
		</div>
	</div>
</div>