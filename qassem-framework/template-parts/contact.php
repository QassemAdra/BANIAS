<?php
/**
 * Template Part: contact — WordPress AJAX form
 */
$email = get_option('qassem_email', 'contact@qassem.io');
?>
<section class="pb-8" id="contact">
<div class="text-center mb-12 md:mb-16">
<p class="text-[8px] uppercase tracking-[0.6em] text-rose-600 font-black mb-4" data-i18n="contact.label">CONTACT</p>
<h2 class="section-title title-font text-4xl md:text-7xl font-black italic" data-i18n="contact.title">Travaillons Ensemble</h2>
<p class="text-gray-400 text-base md:text-2xl leading-relaxed font-light mt-6" data-i18n="contact.desc">Disponible pour conseil stratégique.</p>
</div>
<div class="max-w-2xl mx-auto">
<form id="wp-contact-form" class="space-y-4" novalidate>
  <?php wp_nonce_field('qassem_nonce', 'contact_nonce'); ?>
  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-[9px] font-black uppercase tracking-widest text-white/40 mb-2" for="contact-name" data-i18n="contact.name">Nom</label>
      <input id="contact-name" name="name" type="text" required
             class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-rose-600 focus:outline-none transition-colors placeholder:text-white/20"
             placeholder="Qassem Adra"/>
    </div>
    <div>
      <label class="block text-[9px] font-black uppercase tracking-widest text-white/40 mb-2" for="contact-email" data-i18n="contact.email">Email</label>
      <input id="contact-email" name="email" type="email" required
             class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-rose-600 focus:outline-none transition-colors placeholder:text-white/20"
             placeholder="<?php echo esc_attr($email); ?>"/>
    </div>
  </div>
  <div>
    <label class="block text-[9px] font-black uppercase tracking-widest text-white/40 mb-2" for="contact-message" data-i18n="contact.message">Message</label>
    <textarea id="contact-message" name="message" rows="5" required
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:border-rose-600 focus:outline-none transition-colors placeholder:text-white/20 resize-none"
              placeholder="<?php esc_attr_e('Your message...', 'qassem'); ?>"></textarea>
  </div>
  <button type="submit" id="contact-submit"
          class="btn-world w-full justify-center text-sm py-4">
    <span id="contact-submit-text" data-i18n="contact.send">Envoyer le message</span>
    <i class="fas fa-paper-plane text-[11px]" id="contact-icon"></i>
  </button>
  <div id="contact-feedback" class="text-center text-sm font-bold hidden"></div>
</form>

<div class="flex flex-wrap justify-center gap-6 mt-10 text-[10px] font-black uppercase tracking-widest text-white/30">
  <a href="mailto:<?php echo esc_attr($email); ?>" class="flex items-center gap-2 hover:text-rose-500 transition-colors">
    <i class="fas fa-envelope text-rose-600"></i> <?php echo esc_html($email); ?>
  </a>
</div>
</div>

<script>
(function(){
  var form = document.getElementById('wp-contact-form');
  if (!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var btn  = document.getElementById('contact-submit');
    var icon = document.getElementById('contact-icon');
    var fb   = document.getElementById('contact-feedback');
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin text-[11px]';
    var data = new FormData();
    data.append('action',  'qassem_contact');
    data.append('nonce',   document.querySelector('[name=contact_nonce]').value);
    data.append('name',    document.getElementById('contact-name').value);
    data.append('email',   document.getElementById('contact-email').value);
    data.append('message', document.getElementById('contact-message').value);
    fetch(<?php echo json_encode(admin_url('admin-ajax.php')); ?>, { method:'POST', body:data })
      .then(function(r){ return r.json(); })
      .then(function(res){
        fb.classList.remove('hidden','text-rose-500','text-green-400');
        if (res.success) {
          fb.textContent = res.data.msg;
          fb.classList.add('text-green-400');
          form.reset();
        } else {
          fb.textContent = res.data.msg;
          fb.classList.add('text-rose-500');
        }
        if(window.showToast) window.showToast(res.data.msg, res.success ? '✓' : '✗');
      })
      .catch(function(){ fb.textContent = 'Error. Please try again.'; fb.classList.remove('hidden'); fb.classList.add('text-rose-500'); })
      .finally(function(){ btn.disabled=false; icon.className='fas fa-paper-plane text-[11px]'; fb.classList.remove('hidden'); });
  });
})();
</script>
</section>
