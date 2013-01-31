<p style="font-size:11pt;margin-top:12px;margin-bottom:12px">
    Hello, <?php echo $username; ?>!
</p>
<p style="font-size:11pt;margin-top:12px;margin-bottom:24px">
    You or someone else asked for password recovery for your account to Planteatrs.
</p>
<h3 style="font-size:14pt;margin-top:12px;margin-bottom:6px;font-weight:bold">
    To change the password, go to:
</h3>
<p style="font-size:14pt;margin-top:6px;margin-bottom:12px">
    <a href="<?php echo $recovery_url; ?>" style="color:#20558a" target="_blank">
        <?php echo $recovery_url; ?>
    </a>
</p>
<p style="font-size:11pt;margin-top:12px;margin-bottom:24px">
    If you did not ask for password recovery, please ignore this message.
</p>