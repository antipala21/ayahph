<link href="/agency/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Nursemaid</h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
					<li class="breadcrumb-item active">Nursemaid list</li>
				</ol>
			</div>
		</div>

		<?php $flash = $this->Session->flash('nurse-maid-add'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<?php $flash2 = $this->Session->flash('nurse-maid-edit'); ?>
		<?php if($flash2): ?>
			<div class="alert alert-success">
				<?php echo $flash2; ?>
			</div>
		<?php endif; ?>

		<!-- Row -->
		<a href="/agency/nursemaid/add/" class="btn btn-success">Add Nursemaid</a>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="nurse_maid_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID.</th>
							<th>Name</th>
							<th>Gender</th>
							<th>Age</th>
							<th>Address</th>
							<th>Status</th>
							<th>Added Date</th>
						</tr>
					</thead>
					<tbody>
						<?php if($nursemaids): ?>
							<?php foreach ($nursemaids as $key => $value): ?>
								<tr>
									<td>
										<a href="/agency/nursemaid/detail/<?php echo $value['NurseMaid']['id']; ?>"><?php echo $value['NurseMaid']['id']; ?></a>
									</td>
									<td><?php echo isset($value['NurseMaid']['first_name']) ? $value['NurseMaid']['first_name'] : '' ?></td>
									<td><?php
										$_gender = Configure::read('gender_array');
										echo isset($value['NurseMaid']['gender']) ? $_gender[$value['NurseMaid']['gender']] : '';
										?></td>
									<td><?php
										if(isset($value['NurseMaid']['birthdate'])):
											$datetime1 = new DateTime($value['NurseMaid']['birthdate']);
											$interval = $datetime1->diff( new DateTime());
											echo $interval->format('%y yrs');
										endif; ?>
									</td>
									<td><?php echo isset($value['NurseMaid']['address']) ? $value['NurseMaid']['address'] : '' ?></td>
									<td><?php echo isset($value['NurseMaid']['status']) && $value['NurseMaid']['status'] ? 'Available' : 'Not Available' ?></td>
									<td><?php echo isset($value['NurseMaid']['created']) ? $value['NurseMaid']['created'] : '' ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="/agency/js/jquery.dataTables.min.js"></script>
<script src="/agency/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('#nurse_maid_list').DataTable({
			 "order": [[ 0, 'desc' ]]
		});
	});
</script>