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

				<legend>Nursemaid Rating Details</legend>

				<div class="row">
					<div class="col-md-1">
						<?php echo $this->Form->create('Agency', 
							array(
								'id' => 'agencyUpdateActivate',
								'style' => '',
							)); ?>
						<div class="align-right">
							<input type="hidden" name="data_value" value="1">
							<button class="btn btn-success">Show</button>
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
							<button class="btn btn-warning">Hide</button>
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
							<input type="hidden" name="data_value" value="delete">
							<button class="btn btn-danger">Delete</button>
						</div>
						</form>
					</div>
				</div>
				<hr>
				<div class="table table-responsive">
					<table class="table">
						<tr class="">
							<td><b>Rate Info :</b></td>
							<td></td>
						</tr>
						<tr class="active">
							<td>Agency Name :</td>
							<td><?php echo $nursemaid_rating['Agency']['name'] ?></td>
						</tr>
						<tr class="active">
							<td>Nursemaid Name :</td>
							<td><?php echo $nursemaid_rating['Nursemaid']['first_name'] ?></td>
						</tr>
						<tr class="active">
							<td>User Name :</td>
							<td><?php echo $nursemaid_rating['User']['display_name'] ?></td>
						</tr>
						<tr class="active">
							<td>Rate :</td>
							<td><?php echo $nursemaid_rating['NurseMaidRating']['rate'] ?></td>
						</tr>
						<tr class="active">
							<td>Comment :</td>
							<td><?php echo $nursemaid_rating['NurseMaidRating']['comment'] ?></td>
						</tr>
						<tr class="active">
							<td>Created :</td>
							<td><?php echo $nursemaid_rating['NurseMaidRating']['created'] ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>