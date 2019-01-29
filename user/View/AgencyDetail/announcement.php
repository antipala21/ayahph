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
		right: 0;
	}

	div#ui-datepicker-div {
		z-index: 99 !important;
	}

</style>
<div class="page-wrapper">
	<div class="container-fluid">
		<br>
		<center> <h3> <?php echo $agency['Agency']['name'] ?> </h3> </center>
		<center> <h3> - Announcements - </h3> </center>
		<br>
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12">
				<div class="row">
					<div class="col-md-12">
						<?php if ($announcements): ?>
						<ul>
							<?php foreach($announcements as $key => $value): ?>
							<div class="row nurse-item">
								<div class="col-md-10">
									<div class="media-body">
										<p>****</p>
										<p><?php echo isset($value['Announcement']['content']) ? $value['Announcement']['content'] : ' ' ?></p>
										<p>****</p>
									</div>
								</div>
								<div class="col-md-2">
									<small><?php echo date('Y-m-d h:i:sa', strtotime($value['Announcement']['created'])) ?></small>
								</div>
							</div>
							<?php endforeach; ?>
						</ul>
						<?php else: ?>
							<p>Empty.</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>