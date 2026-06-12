<?php
$currentController = (string) $this->uri->segment(1);
$footerlessControllers = [
  'MyRepublik_Project',
  'BAK_MyRep',
  'VALSAL_MyRep',
  'Batch_Approval_MyRep',
  'DRM_MyRep',
  'Implementasi_BOQ_MyRep',
  'PO_MyRep',
  'Monitoring_RFS_MyRep',
  'ATP_MyRep',
];
$hideMainFooter = !empty($hideMainFooter) || in_array($currentController, $footerlessControllers, true);
?>
<?php if ($currentController !== 'Dashboard' && !$hideMainFooter) : ?>
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
