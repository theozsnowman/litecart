<div id="site-menu" style="grid-area: navigation;">
  <div class="container navbar">

    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#default-menu">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <nav id="default-menu" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li class="hidden-xs">
          <a href="<?php echo document::ilink(''); ?>" title="<?php echo language::translate('title_home', 'Home'); ?>"><?php echo functions::draw_fonticon('fa-home'); ?></a>
        </li>

        <li class="search hidden-xs">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle" title="<?php echo language::translate('title_search', 'Search'); ?>"><?php echo functions::draw_fonticon('fa-search'); ?></a>
          <ul class="dropdown-menu" style="width: 320px;">
            <li>
              <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
                <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
              <?php echo functions::form_draw_form_end(); ?>
            </li>
          </ul>
        </li>

        <?php if ($categories) { ?>
        <li class="categories dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_products', 'Products'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php foreach ($categories as $item) { ?>
            <li><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if ($brands) { ?>
        <li class="brands dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_brands', 'Brands'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php foreach ($brands as $item) { ?>
            <li><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if ($pages) { ?>
        <li class="information dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_information', 'Information'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php foreach ($pages as $item) { ?>
            <li><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
      </ul>

      <ul class="nav navbar-nav navbar-right">
        <li class="customer-service">
          <a href="<?php echo document::href_ilink('customer_service'); ?>"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></a>
        </li>

        <?php if (count(currency::$currencies) > 1) { ?>
        <li class="currencies dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo currency::$selected['code']; ?> <span class="caret"></span></a>
          <ul class="dropdown-menu list-unstyled">
            <?php foreach (currency::$currencies as $currency) { ?>
            <li><a href="<?php echo document::href_link(null, ['currency' => $currency['code']]); ?>" title="<?php echo currency::$selected['name']; ?>"><?php echo $currency['code']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if (count(language::$languages) > 1) { ?>
        <li class="languages dropdown" title="<?php echo language::$selected['name']; ?>">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><img src="<?php echo document::link(WS_DIR_STORAGE . 'images/languages/' . language::$selected['code'] . '.png'); ?>" alt="<?php echo language::$selected['name']; ?>" /> <span class="hidden-md hidden-lg"><?php echo language::$selected['name']; ?></span> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <?php foreach (language::$languages as $language) { ?>
            <li><a href="<?php echo document::href_link(null, ['language' => $language['code']]); ?>"><img src="<?php echo document::link(WS_DIR_STORAGE . 'images/languages/' . $language['code'] . '.png'); ?>" alt="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if (settings::get('accounts_enabled')) { ?>
        <li class="account dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo functions::draw_fonticon('fa-user'); ?> <?php echo !empty(customer::$data['id']) ? customer::$data['firstname'] : language::translate('title_sign_in', 'Sign In'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php if (!empty(customer::$data['id'])) { ?>
              <li><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
              <li><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
              <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
            <?php } else { ?>
              <li>
                <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login'), false, 'class="navbar-form"'); ?>
                  <?php echo functions::form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::link()); ?>

                <div class="form-group">
                  <?php echo functions::form_draw_email_field('email', true, 'required placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
                </div>

                <div class="form-group">
                  <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
                </div>

                  <div class="form-group">
                    <div class="checkbox">
                      <label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label>
                    </div>
                  </div>

                  <div class="btn-group btn-block">
                    <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
                  </div>
                <?php echo functions::form_draw_form_end(); ?>
              </li>
              <li class="text-center">
                <a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a>
              </li>

              <li class="text-center">
                <a href="<?php echo document::href_ilink('reset_password'); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
              </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
      </ul>
    </nav>
  </div>
</div>

<script>
  $('#default-menu .search').click(function(){
    $(this).find('input').focus();
  });
</script>