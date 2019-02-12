<header class="topbar">
	<nav class="navbar top-navbar navbar-toggleable-sm navbar-light">
		<div class="navbar-header">
			<a class="navbar-brand" href="/agency">
				<img src="/user/images/logo-light-icon.png" alt="homepage" class="light-logo" />
				<span class="text-light text-white bg-dark">Ayahph</span>
			</a>
			<span><small>agency</small></span>
		</div>

		<div class="navbar-collapse">
			<!-- ============================================================== -->
			<!-- toggle and nav items -->
			<!-- ============================================================== -->
			<ul class="navbar-nav mr-auto mt-md-0">
				<!-- This is  -->
				<li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
			</ul>
			<!-- ============================================================== -->
			<!-- User profile and search -->
			<!-- ============================================================== -->
			<?php if ($this->Session->read('Auth.User')): ?>
			<ul class="navbar-nav my-lg-0">
				<!-- ============================================================== -->
				<!-- Profile -->
				<!-- ============================================================== -->
				<li class="nav-item dropdown">
					<?php $url = $this->Session->read('Auth.User.image_url') ? $this->Session->read('Auth.User.id') . '_profile.jpg?v=' . strtotime("now") : 'picture.jpg'; ?>
					<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="/agency/account"><img src="/agency/images/<?php echo $url; ?>" alt="user" class="profile-pic m-r-10" /><?php echo $this->Session->read('Auth.User.name') ?></a>
				</li>
			</ul>
			<?php endif; ?>
		</div>
	</nav>
</header>