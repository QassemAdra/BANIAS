<?php if(!get_q_bool('show_tabbar',true)) return; ?>
<nav id="mobile-tabbar" aria-label="Mobile tab bar">
<?php for($i=1;$i<=5;$i++): if(!get_q_bool("tab_{$i}_active",true)) continue;
  $label = get_q("tab_{$i}_label","Tab $i");
  $icon  = get_q("tab_{$i}_icon","fa-house");
  $href  = get_q("tab_{$i}_href","#home");
?>
  <a href="<?php echo esc_attr($href); ?>" class="tab-item<?php echo $i===1?' active':''; ?>" data-section="<?php echo esc_attr(ltrim($href,'#')); ?>">
    <i class="fas <?php echo esc_attr($icon); ?>"></i>
    <span><?php echo esc_html($label); ?></span>
  </a>
<?php endfor; ?>
</nav>
