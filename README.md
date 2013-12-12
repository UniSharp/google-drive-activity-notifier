### Before starting
 * This application use applescript, and it was only tested on Mac OSX 10.9

### Install
 * Clone it
 * 
```
cd ~/ && git clone git@github.com:UniSharp/google-drive-activity-notifier.git
```

 * Modify config.php
 * 
```
<?php
define('CLIENT_ID', 'YOUR_GOOGLE_API_CLIENT_ID');
define('CLIENT_SECRET', 'YOUR_GOOGLE_API_CLIENT_SECRET');
```

 * Install the dependency.
 * 
```
sh ./install.sh
```

### First Run
 * `php GDriveActivityNotifier.php`
 * Note: At the first time running this program, it launch your browser and guides you to get the Google API Token. After getting the token, please go back to the console and enter you Token to the prompt dialog. 

### Set cronjob
 * set cronjob
 * 
```
crontab -e
```

 * run every 5 minutes
 * 
```
*/5 * * * *  cd ~/google-drive-activity-notifier/ && php GDriveActivityNotifier.php

 # Note: replace ~/google-drive-activity-notifier/ to your repository location.
```
