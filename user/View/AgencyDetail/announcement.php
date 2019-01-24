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
							<li><?php echo isset($value['Announcement']['content']) ? $value['Announcement']['content'] : '' ?></li>
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