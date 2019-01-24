<link href="/agency/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css">


<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Announcements</h3>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
					<li class="breadcrumb-item active">Announcement list</li>
				</ol>
			</div>
		</div>

		<?php $flash = $this->Session->flash('announcement-maid-add'); ?>
		<?php if($flash): ?>
			<div class="alert alert-success">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<!-- Row -->
		<a href="/agency/announcement/add/" class="btn btn-success">Add Announcement</a>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<table id="announcement_list" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>No.</th>
							<th>Content</th>
							<th>Status</th>
							<th>Added Date</th>
						</tr>
					</thead>
					<tbody>
						<?php if($announcements): ?>
							<?php foreach ($announcements as $key => $value): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td><?php echo isset($value['Announcement']['content']) ? $value['Announcement']['content'] : '' ?></td>
									<td><?php echo isset($value['Announcement']['status']) && $value['Announcement']['status'] ? 'Available' : 'Not Available' ?></td>
									<td><?php echo isset($value['Announcement']['created']) ? $value['Announcement']['created'] : '' ?></td>
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
		$('#announcement_list').DataTable();
	});
</script>