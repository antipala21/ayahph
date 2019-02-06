<aside class="left-sidebar">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<?php if ($this->Session->read('Auth.User')): ?>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Agency' ? 'active' : ''; ?>" href="/admin/agencies" aria-expanded="false"><i class="fa fa-users" aria-hidden="true"></i><span class="hide-menu">Agencies</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'User' ? 'active' : ''; ?>" href="/admin/users" aria-expanded="false"><i class="fa fa-user" aria-hidden="true"></i><span class="hide-menu">Users</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Transaction' ? 'active' : ''; ?>" href="/admin/transactions" aria-expanded="false"><i class="fa fa-briefcase" aria-hidden="true"></i><span class="hide-menu">Transactions</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark" href="#" aria-expanded="false"><i class="fa fa-calendar-check-o" aria-hidden="true"></i><span class="hide-menu">Schedules</span></a>
				</li>
				<li>
					<a class="waves-effect waves-dark <?php echo $this->params['controller'] == 'Transaction' ? 'active' : ''; ?>" href="/admin/nursemaid_ratings" aria-expanded="false"><i class="fa fa-comments-o" aria-hidden="true"></i><span class="hide-menu">Rating and Feedbacks</span></a>
				</li>
				<?php endif; ?>
			</ul>
			<?php if (!$this->Session->read('Auth.User')): ?>
			<div class="text-center m-t-30">
				<a href="/admin/login" class="btn waves-effect waves-light btn-warning hidden-md-down">Login</a>
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
		<!-- item--><a href="/admin/logout" class="link" data-toggle="tooltip" title="Logout"><i class="mdi mdi-power"></i></a> </div>
	<!-- End Bottom points-->
	<?php endif; ?>
</aside>