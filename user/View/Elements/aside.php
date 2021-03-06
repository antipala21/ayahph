<aside class="left-sidebar">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<li>
					<a class="waves-effect waves-dark <?php echo 
					$this->params['controller'] == 'home'
					|| $this->params['controller'] == 'AgencyDetail' ? 'active' : ''; ?>" href="/" aria-expanded="false"><i class="mdi mdi-gauge"></i><span class="hide-menu">Agencies</span></a>
				</li>
				<?php if ($this->Session->read('Auth.User') || $this->Session->read('user_id')): ?>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'account' ? 'active' : ''; ?>" href="/user/account" aria-expanded="false"><i class="mdi mdi-account-check"></i><span class="hide-menu">Profile</span></a>
				</li>
				<li>
					<div class="notif_container hide" id="notif_schedules">
						<span class="notif_count">0</span>
					</div>
					<a class="waves-effect waves-dark <?php echo 
					$this->params['controller'] == 'Schedule'
					&& ($this->params['action'] == 'index' || $this->params['action'] == 'detail') ? 'active' : ''; ?>" href="/user/schedules" aria-expanded="false"><i class="fa fa-calendar-check-o" aria-hidden="true"></i><span class="hide-menu">Schedules</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo 
					$this->params['controller'] == 'Schedule'
					&& $this->params['action'] == 'to_rate' ? 'active' : ''; ?>" href="/user/to_rate" aria-expanded="false"><i class="fa fa-star-o" aria-hidden="true"></i><span class="hide-menu">To rate</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark" href="/announcements" aria-expanded="false"><i class="fa fa-bullhorn" aria-hidden="true"></i><span class="hide-menu">Announcement</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark" href="/history" aria-expanded="false"><i class="fa fa-history" aria-hidden="true"></i><span class="hide-menu">History</span></a>
				</li>
				<?php endif; ?>
				<li>
					<a class="waves-effect waves-dark" href="/about" aria-expanded="false"><i class="mdi mdi-help-circle"></i><span class="hide-menu">About</span></a>
				</li>
			</ul>
			<?php if (!$this->Session->read('Auth.User') && !$this->Session->read('user_id')): ?>
			<hr>
			<div class="text-center m-t-30">
				<a href="/user/login" class="btn waves-effect waves-light btn-warning hidden-md-down">Login as Client</a>
				<br>
				<br>
				<a href="/agency/login" class="btn waves-effect waves-light btn-warning hidden-md-down">Login as Agency</a>
			</div>
			<?php endif; ?>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
	<?php if ($this->Session->read('Auth.User') || $this->Session->read('user_id')): ?>
	<!-- Bottom points-->
	<div class="sidebar-footer">
		<!-- item--><a href="" class="link" data-toggle="tooltip" title="Settings"><i class="ti-settings"></i></a>
		<!-- item--><a href="" class="link" data-toggle="tooltip" title="Email"><i class="mdi mdi-gmail"></i></a>
		<!-- item--><a href="/user/logout" class="link" data-toggle="tooltip" title="Logout"><i class="mdi mdi-power"></i></a> </div>
	<!-- End Bottom points-->
	<?php endif; ?>
</aside>
<script type="text/javascript">
(function(){
	$('#notif_schedules span.notif_count').text(localStorage.getItem("notif_schedules_count"));
	setTimeout(function(){
		if (
			localStorage.getItem("notif_schedules_flg") !== 'hide'
			&& $('#notif_schedules span.notif_count').text() > 0
			) {
			$('#notif_schedules').show();
		}
	},200);
})();
</script>