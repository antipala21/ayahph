<link href="/admin/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Users</h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
					<li class="breadcrumb-item active">Users list</li>
				</ol>
			</div>
		</div>

		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="agency_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>Display name</th>
							<th>Last name</th>
							<th>Gender</th>
							<th>Phone number</th>
							<th>Status</th>
							<th>Start date</th>
						</tr>
					</thead>
					<tbody>
						<?php if($users): ?>
							<?php foreach ($users as $key => $value): ?>
								<tr>
									<td>
										<a href="/admin/user-detail/<?php echo $value['User']['id']; ?>"><?php echo $value['User']['id']; ?></a>
									</td>
									<td>
										<a href="/admin/user-detail/<?php echo $value['User']['id']; ?>"><?php echo isset($value['User']['display_name']) ? $value['User']['display_name'] : '' ?></a>
									</td>
									<td><?php echo isset($value['User']['lname']) ? $value['User']['lname'] : '' ?></td>
									<td><?php echo isset($value['User']['gender']) ? Configure::read('gender_array')[$value['User']['gender']] : '' ?></td>
									<td><?php echo isset($value['User']['phone_number']) ? $value['User']['phone_number'] : '' ?></td>
									<td><?php echo isset($value['User']['status']) && $value['User']['status'] ? 'Available' : 'Not Available' ?></td>
									<td><?php echo isset($value['User']['created']) ? $value['User']['created'] : '' ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="/admin/js/jquery.dataTables.min.js"></script>
<script src="/admin/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('#agency_list').DataTable({
			 "order": [[ 0, 'desc' ]]
		});
	});
</script>