# TweetPull
Create an RSS feed of a selected Twitter user's recent tweets using the v1.1 API.

## Background
For some time, Twitter RSS-enabled a user's stream, meaning one could essentially follow a user without interacting with the Twitter front-end website (or app). This allowed one to add one of two specifically-crafted URLs into their RSS reader of choice:

`http://twitter.com/statuses/user_timeline/[numeric twitter id].rss` , or 
`http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=[twitter screen name]`

This was great for people like me who are devoted to RSS, want to follow people on Twitter, but generally hate sifting through the streaming mess of the Twitter site. However, in the summer of 2013, Twitter officially deprecated v1 of its API, and released [v1.1](https://dev.twitter.com/rest/public); and with it, they removed the ability to make un-authenticated requests to the API. This effectively killed any ability to quickly and easily pull recent tweets via a simple URL.

## What You'll Need

- PHP (ack! I know!)
- a web host for the RSS feeds
- a scheduler (like cron) to run the PHP script

## My Approach

Since the v1.1 API allows authenticated requests to a large number of very useful methods, the first step was to sign in to the [Twitter Developer](https://dev.twitter.com/) site and create an application that would authenticate back to me. This is done via creation of "consumer keys" and "access tokens" that are unique to the developer and are placed in the request header.

The next step was to find a way to create a valid request header with my keys/credentials along with my desired get method (in my case, `[statuses/user_timeline](https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline)`). GitHub user **themattharris** made all the authentication portion incredibly easy by developing an [OAuth library written in PHP](https://github.com/themattharris/tmhOAuth).

Once the request was formed, my code was then a matter of extracting the JSON response, parsing the user's tweets, and dropping them to a well-formed RSS/XML file. This might seem like an involved and somewhat unnecessary approach, but my primary reasoning was due to Twitter's [API rate limiting](https://dev.twitter.com/docs/rate-limiting/1.1). Read that, and the following "gotchas" should make more sense:

- Writing to file instead of STDOUT with RSS/XML headers keeps me in the control of the requests, and not busting the limit. I run the script via cron at a [safely] scheduled interval.
- Once a file is written, I need to host it so an RSS reader can get to it. No web host, no usefulness.
- Although there are different ways I could do this, I chose to pass the user id to the script as an argument. This means if I want to follow/read 5 Twitter users, I have 5 entries in my crontab. (See Installation steps below.)

In general, a lot of improvements can be made here, but it's good enough for a v0.1 release. And I only did it because the v1.1 API is so new, I couldn't find anyone else doing something similar yet.
