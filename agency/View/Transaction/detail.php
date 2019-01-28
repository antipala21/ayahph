<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Transactions </h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Transaction</a></li>
					<li class="breadcrumb-item active">Detail</li>
				</ol>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2>Hire Request</h2>
				<?php echo $this->Form->create('Transaction',array(
						'id' => 'agencyAcceptDecline',
						'url' => array('controller' => 'Transaction','action' => 'transactionUpdate'),
					)); ?>
				<?php echo $this->Form->hidden('id', array('value' => $transaction['Transaction']['id'])); ?>
				<input class="btn btn-success" type="submit" name="value_transaction" value="Accept">
				<input class="btn btn-danger" type="submit" name="value_transaction" value="Decline">
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
					<p><?php echo $transaction['Transaction']['comment'] ?></p>
				</div>
			</div>
			<div class="col-md-8">
				<div>
					<b>User Name</b>
					<p><?php echo $transaction['User']['display_name'] ?></p>
				</div>
				<div>
					<b>Address</b>
					<p><?php echo $transaction['Transaction']['address'] ?></p>
				</div>
				<div>
					<b>Contact No.</b>
					<p><?php echo $transaction['Transaction']['user_phone_number'] ?></p>
				</div>
			</div>
		</div>
	</div>
</div>