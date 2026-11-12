<nav style="background: #40e90d; padding: 15px">
  <a href="task4.php?page=home" style="color: white; margin-right: 15px"
    >Home</a
  >

  <?php if(!loggedIn()){ ?>

  <a href="task4.php?page=login" style="color: white; margin-right: 15px"
    >Login</a
  >

  <a href="task4.php?page=register" style="color: white; margin-right: 15px"
    >Register Company</a
  >

  <?php } ?> <?php if(companyLoggedIn()){ ?>

  <a href="task4.php?page=dashboard" style="color: white; margin-right: 15px"
    >Dashboard</a
  >

  <a href="task4.php?page=employees" style="color: white; margin-right: 15px"
    >Employees</a
  >

  <a href="task4.php?page=training" style="color: white; margin-right: 15px"
    >Training</a
  >

  <a
    href="task4.php?page=registrations"
    style="color: white; margin-right: 15px"
    >Registrations</a
  >

  <?php } ?> <?php if(adminLoggedIn()){ ?>

  <a href="task4.php?page=admin" style="color: white; margin-right: 15px"
    >Admin</a
  >

  <a href="task4.php?page=reports" style="color: white; margin-right: 15px"
    >Reports</a
  >

  <?php } ?> <?php if(loggedIn()){ ?>

  <a href="task4.php?action=logout" style="color: white; float: right"
    >Logout</a
  >

  <?php } ?>
</nav>
