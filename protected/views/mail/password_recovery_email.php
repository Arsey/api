<p style="font-size:11pt;margin-top:12px;margin-bottom:12px">
    Hello, <?php echo $username; ?>!
</p>
<h3 style="font-size:14pt;margin-top:12px;margin-bottom:6px;font-weight:bold">
    Click on the following link to reset your password:
</h3>
<p style="font-size:14pt;margin-top:6px;margin-bottom:12px">
    <a href="<?php echo $recovery_url; ?>" style="color:#20558a" target="_blank">
        <?php echo $recovery_url; ?>
    </a>
</p>
<p style="font-size:11pt;margin-top:12px;margin-bottom:24px">
    If you did not request this change, or if it was unintentional, simply disregard this message.
</p>
<p style="font-size:11pt;margin-top:12px;margin-bottom:24px">
    <b>The PlantEaters Team</b>
</p>