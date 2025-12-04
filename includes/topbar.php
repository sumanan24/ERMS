  <?php
    if (!isset($dbh)) {
        include('../includes/config.php');
    }
    $currentRole = 'admin';
    $currentUser = isset($_SESSION['alogin']) ? $_SESSION['alogin'] : '';
    if ($currentUser) {
        try {
            $st = $dbh->prepare("SELECT usertype FROM admin WHERE username=:u LIMIT 1");
            $st->bindParam(':u', $currentUser, PDO::PARAM_STR);
            $st->execute();
            $ut = $st->fetch(PDO::FETCH_OBJ);
            if ($ut && isset($ut->usertype)) {
                $currentRole = $ut->usertype;
            }
        } catch (Exception $e) {
        }
    }
    $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    ?>
  <nav class="navbar top-navbar bg-white box-shadow">
      <div class="container-fluid">
          <script>
              (function() {
                  var l = document.querySelector('link[rel="icon"]') || document.createElement('link');
                  l.rel = 'icon';
                  l.type = 'image/png';
                  l.href = '/ERMS/images/logo.png';
                  if (!l.parentNode) {
                      document.head.appendChild(l);
                  } else {
                      l.href = '/ERMS/images/logo.png';
                  }
              })();
          </script>
          <div class="row">
              <div class="navbar-header no-padding">
                  <a class="navbar-brand" href="../dashboard/dashboard.php" style="font-weight:700;letter-spacing:.2px">
                      ERMS Panel
                  </a>
                  <span class="small-nav-handle hidden-sm hidden-xs"><i class="fa fa-outdent"></i></span>
                  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
                      <span class="sr-only">Toggle navigation</span>
                      <i class="fa fa-ellipsis-v"></i>
                  </button>
                  <button type="button" class="navbar-toggle mobile-nav-toggle">
                      <i class="fa fa-bars"></i>
                  </button>
              </div>
              <!-- /.navbar-header -->

              <div class="collapse navbar-collapse" id="navbar-collapse-1">
                  <ul class="nav navbar-nav" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                      <li>
                          <a href="../student/transcript.php"><i class="fa fa-file-text-o"></i> Transcript Search</a>
                      </li>
                      <li class="hidden-xs hidden-xs"><!-- <a href="#">My Tasks</a> --></li>

                  </ul>
                  <!-- /.nav navbar-nav -->

                  <ul class="nav navbar-nav navbar-right" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                      <li class="hidden-xs">
                          <a href="#" style="pointer-events:none;">
                              <span class="label label-default" style="background:#111827;color:#fff;border-radius:999px;padding:6px 10px;display:inline-block;">
                                  <i class="fa fa-user"></i> <?php echo htmlentities($currentUser); ?>
                              </span>
                              <span class="label label-default" style="background:#2563eb;color:#fff;border-radius:999px;padding:6px 10px;display:inline-block;margin-left:6px;">
                                  <?php echo strtoupper($currentRole); ?>
                              </span>
                          </a>
                      </li>
                      <li><a href="../account/change-password.php"><i class="fa fa-key"></i> Change Password</a></li>
                      <li><a href="../logout.php" class="color-danger text-center"><i class="fa fa-sign-out"></i> Logout</a></li>



                  </ul>
                  <!-- /.nav navbar-nav navbar-right -->
              </div>
              <!-- /.navbar-collapse -->
          </div>
          <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
  </nav>