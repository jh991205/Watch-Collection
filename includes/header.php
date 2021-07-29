<header>
    <div class = "title">
        <h1> <a href="/">Watch Collection</a> </h1>
    </div>

    <div class = "login">
    <?php if(is_user_logged_in() === FALSE ) { ?>
      <?php echo_login_form($url, $session_messages); ?>
    <?php } ?>

    <?php if (is_user_logged_in()) { ?>
        <a href="<?php echo logout_url(); ?>">Sign Out</a>
    <?php } ?>
    </div>
</header>
