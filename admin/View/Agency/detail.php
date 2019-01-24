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

				<legend>Agency Details</legend>
				<?php echo $this->Form->create('Agency', 
					array(
						'id' => 'agencyUpdateActivate',
						'style' => '',
					)); ?>
				<div class="align-right">
					<input type="hidden" name="data_value" value="1">
					<button class="btn btn-success">Activate</button>
				</div>
				</form>
				<br>
				<?php echo $this->Form->create('Agency', 
					array(
						'id' => 'agencyUpdateActivate',
						'style' => '',
					)); ?>
				<div class="align-right">
					<input type="hidden" name="data_value" value="0">
					<button class="btn btn-warning">Deactivate</button>
				</div>
				</form>

				<hr>
				<div class="table table-responsive">
					<table class="table">
						<tr class="">
							<td><b>Personal Info :</b></td>
							<td></td>
						</tr>
						<tr class="active">
							<td>ID :</td>
							<td><?php echo $agency['Agency']['id'] ?></td>
						</tr>
						<tr class="active">
							<td>Agency Name :</td>
							<td><?php echo $agency['Agency']['name'] ?></td>
						</tr>
						<tr class="active">
							<td>Email :</td>
							<td><?php echo $agency['Agency']['email'] ?></td>
						</tr>
						<tr class="active">
							<td>Representative Name :</td>
							<td><?php echo $agency['Agency']['representative_name'] ?></td>
						</tr>
						<tr class="active">
							<td>Address :</td>
							<td><?php echo $agency['Agency']['address'] ?></td>
						</tr>
						<tr class="active">
							<td>Phone number :</td>
							<td><?php echo $agency['Agency']['phone_number'] ?></td>
						</tr>
						<tr class="active">
							<td>Status :</td>
							<td><?php echo isset($agency['Agency']['status']) && $agency['Agency']['status'] ? 'Available' : 'Not Available' ?></td>
						</tr>
						<tr class="active">
							<td>Registered Date :</td>
							<td><?php echo $agency['Agency']['created'] ?></td>
						</tr>
						<tr class="active">
							<td>Agency Descripton :</td>
							<td><?php echo $agency['Agency']['description'] ?></td>
						</tr>
						<tr class="">
							<td><b>Documents :</b></td>
							<td></td>
						</tr>
						<tr class="active">
							<td>Business Permit :</td>
							<td>
								<?php $fileName = isset($agency['Agency']['business_permit_url']) ? $agency['Agency']['business_permit_url'] : ''; ?>
								<img style="width: 25%; <?php echo isset($fileName) && !empty($fileName) ? '' : 'display: none'; ?>" src="<?php echo myTools::checkHost() . '/agency/img/agency_permit/' . $fileName ; ?>" id="test">
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>