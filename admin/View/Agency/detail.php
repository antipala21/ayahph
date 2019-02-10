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
				<br>
				<?php if(isset($agency['Agency']['image_url']) && !empty($agency['Agency']['image_url'])): ?>
					<img style="width: 250px;" id="view-profile-pic" src="<?php echo myTools::getProfileImgSrcAgency($agency['Agency']['image_url']); ?>" alt="Avatar">
				<?php else: ?>
					<img style="width: 250px;" id="view-profile-pic" src="/agency/images/picture.jpg" alt="Avatar">
				<?php endif; ?>

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
								<?php if($documents): ?>
									<?php foreach($documents as $key => $value): ?>
										<img class="legal_docu rounded d-block" src="/agency/img/agency_permit/<?php echo $value['AgencyLegalDocument']['filename']; ?>">
										<hr>
									<?php endforeach; ?>
								<?php else: ?>
									<p>Empty.</p>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>