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
			<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
				<div class="row nurse-item">
					<div class="col-md-2">
						<div class="media-left align-self-center">
							<img class="rounded-circle" src="https://randomuser.me/api/portraits/women/50.jpg">
						</div>
					</div>
					<div class="col-md-8">
						<div class="media-body">
							<?php if(isset($nurse_maid['status']) && $nurse_maid['status']): ?>
							<p>Status: Active</p>
							<?php else: ?>
							<p>Status: Not Active</p>
							<?php endif; ?>
							<h4>
								<?php echo isset($nurse_maid['first_name']) ? $nurse_maid['first_name'] : '' ?>
								<?php echo isset($nurse_maid['last_lname']) ? $nurse_maid['last_lname'] : '' ?>
							</h4>
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
					<div class="col-lg-12 col-xlg-12 col-md-12 nurse-item-container">
						<div class="media-right align-self-center">
							<a href="/agency/nursemaid/edit/<?php echo $nurse_maid['id'] ?>" class="btn btn-info">Edit</a><hr>
						</div>
					</div>
					<?php echo $this->Form->create('NurseMaid',array(
						'id' => 'nurseMaidUpdateStatus',
						)); ?>
					<?php echo $this->Form->hidden('id', array('value' => $nurse_maid['id'])); ?>
					<input class="btn btn-success" type="submit" name="value_transaction" value="Active">
					<input class="btn btn-danger" type="submit" name="value_transaction" value="Not Active">
					<input class="btn btn-danger" type="submit" name="value_transaction" value="Delete">
					<?php echo $this->Form->end(); ?>
				</div>
			</div>
		</div>
	</div>
</div>