# Changelog
0.4
- added exclude_replies and include_rts (from statuses/user_timeline) to optionally remove replies or retweets

0.3.1
- added generator tag to RSS header output

0.3
- created separate config class to define all params; no modifications to main tweetpull.php script are needed
    - made many features configurable
- various debug levels: info messages, error messages; various debug output options: screen, log file, both
- ability to pull data via sample data response file for testing, instead of live pull from Twitter API
- better comments

0.2.1
- added media entity support in links() function; translates embedded images into HTML IMG tags within RSS content

0.2
- added function to link embedded entities (hyperlinks to site, @mentions to Twitter user, #hashtags to hashtag search)
- better comments
- enforce 15-char max username length, per Twitter policy

0.1
- First released version.

# To-do List
- ~~make embedded links linkable~~
- ~~link @username mentions back to user~~
- ~~link #hastags to tag search~~
- ~~better code comments~~
- ~~link embedded "media" entities~~
- ~~separate config from program logic~~
    - ~~add flags for debugging, enhanced features, errors, etc.~~
- ~~option to turn off retweets; on twitter or via api?~~
- maintain embedded media link/url in addition to IMG
- why are RTs getting truncated? is that me or twitter?
