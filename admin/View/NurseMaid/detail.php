<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<div class="row">
			<div class="col-md-12">

				<?php $flash2 = $this->Session->flash('error'); ?>
				<?php if($flash2): ?>
					<div class="alert alert-warning">
						<?php echo $flash2; ?>
					</div>
				<?php endif; ?>

				<legend>Nursemaid Details</legend>

				<div class="row">
					<div class="col-md-1">
						<?php echo $this->Form->create('Agency', 
							array(
								'id' => 'agencyUpdateActivate',
								'style' => '',
							)); ?>
						<div class="align-right">
							<input type="hidden" name="data_value" value="1">
							<button class="btn btn-success">Available</button>
						</div>
						</form>
					</div>
					<div class="col-md-1">
						<?php echo $this->Form->create('Agency', 
							array(
								'id' => 'agencyUpdateActivate',
								'style' => '',
							)); ?>
						<div class="align-right">
							<input type="hidden" name="data_value" value="0">
							<button class="btn btn-warning">Not Available</button>
						</div>
						</form>
					</div>
				</div>
				<hr>
				<div class="table table-responsive">
					<table class="table">
						<tr class="">
							<td><b>Nursemaid Info :</b></td>
							<td></td>
						</tr>
						<tr class="active">
							<td>ID :</td>
							<td><?php echo $nurse_maid['NurseMaid']['id'] ?></td>
						</tr>
						<tr class="active">
							<td>Status :</td>
							<td><?php echo Configure::read('status')[$nurse_maid['NurseMaid']['status']] ?></td>
						</tr>
						<tr class="active">
							<td>Agency Name :</td>
							<td><?php echo $nurse_maid['Agency']['name'] ?></td>
						</tr>
						<tr class="active">
							<td>Nursemaid FirstName :</td>
							<td><?php echo $nurse_maid['NurseMaid']['first_name'] ?></td>
						</tr>
						<tr class="active">
							<td>Nursemaid Middle Name :</td>
							<td><?php echo $nurse_maid['NurseMaid']['middle_name'] ?></td>
						</tr>
						<tr class="active">
							<td>Nursemaid Last Name :</td>
							<td><?php echo $nurse_maid['NurseMaid']['last_lname'] ?></td>
						</tr>
						<tr class="active">
							<td>Self Introduction :</td>
							<td><?php echo $nurse_maid['NurseMaid']['self_introduction'] ?></td>
						</tr>
						<tr class="active">
							<td>Rate :</td>
							<td><?php echo $nurse_maid['NurseMaid']['rating'] ?></td>
						</tr>
						<tr class="active">
							<td>Gender :</td>
							<td><?php echo Configure::read('gender_array')[$nurse_maid['NurseMaid']['gender']]; ?></td>
						</tr>
						<tr class="active">
							<td>Address :</td>
							<td><?php echo $nurse_maid['NurseMaid']['address'] ?></td>
						</tr>
						<tr class="active">
							<td>Phone :</td>
							<td><?php echo $nurse_maid['NurseMaid']['phone_number'] ?></td>
						</tr>
						<tr class="active">
							<td>Created :</td>
							<td><?php echo $nurse_maid['NurseMaid']['created'] ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>