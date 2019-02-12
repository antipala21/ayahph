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
				Ayahph</span> </a>
		</div>

		<div class="navbar-collapse">
			<!-- ============================================================== -->
			<!-- toggle and nav items -->
			<!-- ============================================================== -->
			<ul class="navbar-nav mr-auto mt-md-0">
				<!-- This is  -->
				<li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
				
				<?php if (
						strtolower($this->params['controller']) != "register"
						&& strtolower($this->params['controller']) != "login"
					): ?>
					<li class="nav-item hidden-sm-down search-box"> <a class="nav-link hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
					    <form class="app-search">
					        <input type="text" class="form-control" placeholder="Search & enter"> <a class="srh-btn"><i class="ti-close"></i></a> </form>
					</li>
				<?php endif; ?>
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
					<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="/user/account"><img src="/user/images/<?php echo $url; ?>" alt="user" class="profile-pic m-r-10" /><?php echo $this->Session->read('Auth.User.display_name') ?></a>
				</li>
			</ul>
			<?php endif; ?>
		</div>
	</nav>
</header>