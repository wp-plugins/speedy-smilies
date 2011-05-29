<?php

if (!WP_UNINSTALL_PLUGIN) exit;

delete_option('speedy_smilies_set');
delete_option('speedy_smilies_method');
delete_option('speedy_smilies_cache');
delete_option('speedy_smilies_themecache');
delete_option('speedy_smilies_donotify');
delete_option('speedy_smilies_datauri');
