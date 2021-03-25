<? if ($ENABLE_LIVE_CHAT == 'Yes') { ?>
    <script type="text/javascript">
        var LIVE_CHAT_LICENCE_NUMBER = '<?= $LIVE_CHAT_LICENCE_NUMBER; ?>';
        window.__lc = window.__lc || {};
        window.__lc.license = LIVE_CHAT_LICENCE_NUMBER;
        (function () {
            var lc = document.createElement('script');
            lc.type = 'text/javascript';
            lc.async = true;
            lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(lc, s);
        })();
    </script>
    <noscript>
    <a href="https://www.livechatinc.com/chat-with/<?= $LIVE_CHAT_LICENCE_NUMBER; ?>/">Chat with us</a>,
    </noscript>
<? } ?>