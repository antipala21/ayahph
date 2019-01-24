<header class="topbar">
	<nav class="navbar top-navbar navbar-toggleable-sm navbar-light">
		<div class="navbar-header">
			<a class="navbar-brand" href="/">
				<!-- Logo icon --><b>
				    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
				    
				    <!-- Light Logo icon -->
				    <img src="/user/images/logo-light-icon.png" alt="homepage" class="light-logo" />
				</b>
				<!--End Logo icon -->
				<!-- Logo text --><span class="text-light text-white bg-dark">
				
				 <!-- Light Logo text -->    
				Ayahph Admin</span> </a>
		</div>

		<div class="navbar-collapse">
			<?php if ($this->Session->read('Auth.User')): ?>
			<ul class="navbar-nav my-lg-0">
				<!-- ============================================================== -->
				<!-- Profile -->
				<!-- ============================================================== -->
				<li class="nav-item dropdown">
				    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="/user/account"><img src="/user/images/users/1.jpg" alt="user" class="profile-pic m-r-10" /><?php echo $this->Session->read('Auth.User.user_id') ?></a>
				</li>
			</ul>
			<?php endif; ?>
		</div>
	</nav>
</header>