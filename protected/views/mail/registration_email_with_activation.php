<p style="font-size:11pt;margin-top:12px;margin-bottom:12px">
    Hello, <?php echo $username; ?>!
</p>
<h3 style="font-size:14pt;margin-top:12px;margin-bottom:6px;font-weight:bold">
    Please activate your account with this url:
</h3>
<p style="font-size:14pt;margin-top:6px;margin-bottom:12px">
    <a href="<?php echo $activation_url; ?>" style="color:#20558a" target="_blank">
        <?php echo $activation_url; ?>
    </a>
</p>