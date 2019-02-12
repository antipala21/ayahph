<style type="text/css">
	.profile-img{
		text-align: center;
	}
	.profile-img img{
		width: 70%;
		height: 100%;
	}
	.profile-img .file {
		position: relative;
		overflow: hidden;
		margin-top: -20%;
		width: 70%;
		border: none;
		border-radius: 0;
		font-size: 15px;
		background: #212529b8;
	}
	.profile-img .file input {
		position: absolute;
		opacity: 0;
		right: 0;
		top: 0;
	}
	.profile-head h5{
		color: #333;
	}
	.profile-edit-btn{
		border: none;
		border-radius: 1.5rem;
		width: 70%;
		padding: 2%;
		font-weight: 600;
		color: #6c757d;
		cursor: pointer;
	}
	.proile-rating{
		font-size: 12px;
		color: #818182;
		margin-top: 5%;
	}
	.proile-rating span{
		color: #495057;
		font-size: 15px;
		font-weight: 600;
	}
	.profile-head .nav-tabs{
		margin-bottom:5%;
	}
	.profile-head .nav-tabs .nav-link{
		font-weight:600;
		border: none;
	}
	.profile-head .nav-tabs .nav-link.active{
		border: none;
		border-bottom:2px solid #0062cc;
	}
	.profile-work{
		padding: 14%;
		margin-top: -15%;
	}
	.profile-work p{
		font-size: 12px;
		color: #818182;
		font-weight: 600;
		margin-top: 10%;
	}
	.profile-work a{
		text-decoration: none;
		color: #495057;
		font-weight: 600;
		font-size: 14px;
	}
	.profile-work ul{
		list-style: none;
	}
	.profile-tab label{
		font-weight: 600;
	}
	.profile-tab p{
		font-weight: 600;
		color: #0062cc;
	}
	.container-fluid {
		padding-top: 1em;
	}
	#myTabContent .row {
		padding-top: 1em;
		padding-bottom: 1em;
	}
	.profile-head {
		padding-top: 3em;
	}
	.modal a.close-modal {
		top: 1.5px;
		right: 0;
	}
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12">
				<form method="post">
					<div class="row">
						<div class="col-md-4">
							<div class="profile-img">
								<?php if(isset($user['image_url']) && !empty($user['image_url'])): ?>
									<img id="view-profile-pic" src="<?php echo myTools::getProfileImgSrc($user['image_url']); ?>" alt="Avatar" title="Change the avatar">
								<?php else: ?>
									<img id="view-profile-pic" src="/images/picture.jpg" alt="Avatar" title="Change the avatar">
								<?php endif; ?>
							</div>
							<hr>
							<br>
						</div>
						<div class="col-md-6">
							<div class="profile-head">
								<h2><?php echo $user['display_name'] ?></h2>
								<h3><?php echo $user['fname'] ?> , <?php echo $user['lname'] ?></h3>
								<br>
								<ul class="nav nav-tabs" id="myTab" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">About</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Transactions</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4"></div>
						<div class="col-md-8">
							<div class="tab-content profile-tab" id="myTabContent">
								<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
									<div class="row">
										<div class="col-md-6">
											<label>User Id</label>
										</div>
										<div class="col-md-6">
											<p><?php echo $user['id'] ?></p>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<label>User Name</label>
										</div>
										<div class="col-md-6">
											<p><?php echo $user['display_name'] ?></p>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<label>First Name</label>
										</div>
										<div class="col-md-6">
											<p><?php echo $user['fname'] ?></p>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<label>Last Name</label>
										</div>
										<div class="col-md-6">
											<p><?php echo $user['lname'] ?></p>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<label>Email</label>
										</div>
										<div class="col-md-6">
											<p><?php echo $user['email'] ?></p>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<label>Phone</label>
										</div>
										<div class="col-md-6">
											<p><?php echo $user['phone_number'] ?></p>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<label>Address</label>
										</div>
										<div class="col-md-6">
											<p><?php echo isset($user['address']) ? $user['address'] : '' ?></p>
										</div>
									</div>
									<hr>
								</div>
								<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
									<div class="row">
										<div class="col-md-6">
											<label>Total transactions</label>
										</div>
										<div class="col-md-6">
											<p><?php echo isset($user['total_transaction']) ? $user['total_transaction'] : 0 ?></p>
										</div>
									</div>
									<hr>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>