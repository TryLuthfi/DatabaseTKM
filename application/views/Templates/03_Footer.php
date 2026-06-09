<?php $currentController = (string) $this->uri->segment(1); ?>
<?php if ($currentController !== 'Dashboard') : ?>
  <!-- Main Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2025 <a href="https://tkm.co.id/">PT. TKM</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.2
    </div>
  </footer>
<?php endif; ?>
</div>
<!-- ./wrapper -->
