<aside class="left-sidebar">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<?php if ($this->Session->read('Auth.User')): ?>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Account' ? 'active' : ''; ?>" href="/agency/account" aria-expanded="false"><i class="mdi mdi-account-check"></i><span class="hide-menu">Profile</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'NurseMaid' ? 'active' : ''; ?>" href="/agency/nursemaid/" aria-expanded="false"><i class="fa fa-user-md" aria-hidden="true"></i><span class="hide-menu">Nursemaid</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Announcement' ? 'active' : ''; ?>" href="/agency/announcement" aria-expanded="false"><i class="fa fa-bullhorn" aria-hidden="true"></i><span class="hide-menu">Announcement</span></a>
				</li>
				<li>
					<div class="notif_container hide" id="notif_requests">
						<span class="notif_count">0</span>
					</div>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Transaction' ? 'active' : ''; ?>" href="/agency/transaction" aria-expanded="false"><i class="fa fa-briefcase" aria-hidden="true"></i><span class="hide-menu">Hire Requests</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Schedule' ? 'active' : ''; ?>" href="/agency/schedules" aria-expanded="false"><i class="fa fa-calendar-check-o" aria-hidden="true"></i><span class="hide-menu">Schedules</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark" href="/agency/history" aria-expanded="false"><i class="fa fa-history" aria-hidden="true"></i><span class="hide-menu">History</span></a>
				</li>
				<?php endif; ?>
				<li>
					<a class="waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-help-circle"></i><span class="hide-menu">Help</span></a>
				</li>
			</ul>
			<?php if (!$this->Session->read('Auth.User')): ?>
			<div class="text-center m-t-30">
				<a href="/agency/login" class="btn waves-effect waves-light btn-warning hidden-md-down">Login</a>
			</div>
			<?php endif; ?>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
	<?php if ($this->Session->read('Auth.User')): ?>
	<!-- Bottom points-->
	<div class="sidebar-footer">
		<!-- item--><a href="" class="link" data-toggle="tooltip" title="Settings"><i class="ti-settings"></i></a>
		<!-- item--><a href="" class="link" data-toggle="tooltip" title="Email"><i class="mdi mdi-gmail"></i></a>
		<!-- item--><a href="/agency/logout" class="link" data-toggle="tooltip" title="Logout"><i class="mdi mdi-power"></i></a> </div>
	<!-- End Bottom points-->
	<?php endif; ?>
</aside>
<script type="text/javascript">
(function(){
	$('#notif_requests span.notif_count').text(localStorage.getItem("notif_request_count"));
	setTimeout(function(){
		if (
			localStorage.getItem("notif_request_flg") !== 'hide'
			&& $('#notif_requests span.notif_count').text() > 0
			) {
			$('#notif_requests').show();
		}
	},200);
})();
</script>