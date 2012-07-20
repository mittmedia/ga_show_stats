<?php global $hits_table; global $js_week; global $js_three_months; ?>

<div class="wrap">
  <div id="icon-options-general" class="icon32"><br></div>
  <h2><?php _e( 'Analytics' ); ?></h2>
  <div id="analytics_week" style="max-width: 500px; float: left;"></div>
  <div id="analytics_three_months" style="max-width: 500px; float: left;"></div>
  <div style="clear: both;"><!-- --></div>
  <div style="margin-top: 50px;"><?= $hits_table; ?></div>
  <?= $js_week; ?>
  <?= $js_three_months; ?>
</div>