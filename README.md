# Perk-Tracker

Tracks Perk points. Gives the current points, total points, and points change along with graphs.

### Screenshots

http://i.imgur.com/wdBwk5d.png

http://i.imgur.com/MXKRd6S.png

# Make your own!

* Set up a database with a table named 'perk_stats'.
* Give that table four columns:
  * id - int, AUTO_INCREMENT
  * time - timestample, CURRENT_TIMESTAMP
  * current_points - int
  * total_points - int
* Copy sample_config.json to src/config.json and fill it out (leave token blank).
* Setup two cron jobs:
  * \*/5	*	*	*	*	/usr/bin/php -f \*\*\*PATH TO insert_point.php\*\*\* &> /dev/null
    * Inserts a new data point every 5 minutes.
  * 0	0	1,15	*	*	/usr/bin/php -f \*\*\*PATH TO get_token.php\*\*\* &> /dev/null
    * Gets a new access token once every 15 days.
