<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<div class="row">
			<div class="col-md-12">
				<legend>User Details</legend>
				<hr>

				<?php if(isset($user['User']['image_url']) && !empty($user['User']['image_url'])): ?>
					<img style="width: 250px;" id="view-profile-pic" src="<?php echo myTools::getProfileImgSrc($user['User']['image_url']); ?>" alt="Avatar">
				<?php else: ?>
					<img style="width: 250px;" id="view-profile-pic" src="/user/images/picture.jpg" alt="Avatar">
				<?php endif; ?>

				<div class="table table-responsive">
					<table class="table">
						<tr class="">
							<td><b>Personal Info :</b></td>
							<td></td>
						</tr>
						<tr class="active">
							<td>ID :</td>
							<td><?php echo $user['User']['id'] ?></td>
						</tr>
						<tr class="active">
							<td>Email :</td>
							<td><?php echo $user['User']['email'] ?></td>
						</tr>
						<tr class="active">
							<td>Display Name :</td>
							<td><?php echo $user['User']['display_name'] ?></td>
						</tr>
						<tr class="active">
							<td>First Name :</td>
							<td><?php echo $user['User']['fname'] ?></td>
						</tr>
						<tr class="active">
							<td>Last Name :</td>
							<td><?php echo $user['User']['lname'] ?></td>
						</tr>
						<tr class="active">
							<td>Gender :</td>
							<td><?php echo isset($user['User']['gender']) ? Configure::read('gender_array')[$user['User']['gender']] : ''  ?></td>
						</tr>
						<!-- <tr class="active">
							<td>Address :</td>
							<td><?php echo $user['User']['address'] ?></td>
						</tr> -->
						<tr class="active">
							<td>Phone number :</td>
							<td><?php echo $user['User']['phone_number'] ?></td>
						</tr>
						<tr class="active">
							<td>Registered Date :</td>
							<td><?php echo $user['User']['created'] ?></td>
						</tr>
						<tr class="">
							<td><b>Documents :</b></td>
							<td></td>
						</tr>
						<tr class="active">
							<td>Valid ID :</td>
							<td>

								<?php $fileName = isset($user['User']['valid_id_url']) ? $user['User']['valid_id_url'] : ''; ?>
								<img style="width: 25%; <?php echo isset($fileName) && !empty($fileName) ? '' : 'display: none'; ?>" src="<?php echo myTools::checkHost() . '/user/img/user_ids/' . $fileName ; ?>" id="test">
								<?php if(empty($fileName) || $fileName == ''): ?>
									<p>No Valid ID.</p>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>