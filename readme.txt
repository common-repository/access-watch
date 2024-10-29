=== Access Watch: Security and Traffic Insights ===
Contributors: accesswatch, znarfor
Tags: analytics, security, spam, statistics, dashboard
Requires at least: 4.0
Tested up to: 4.9.1
Stable tag: trunk
License: GPLv2 or later

Understand precisely the robot traffic on your website and take actions to improve performance and security.

== Description ==

**End of life**

**The Access Watch plugin for WordPress is not supported anymore and the plugin is unactive.**

The Access Watch plugin is a traffic analysis and security service. Our technology processes visitor activity on your WordPress website and delivers insights to improve site security and performance.

Using innovative pattern matching and signature identification, our real-time analysis clearly breaks down your traffic between human and robots, immediately identifying threats and enabling you to block them from abusing your website. All in a beautiful and easy-to-use dashboard!

Access Watch is highly efficient in detecting and mitigating numerous website threats, including:

* brute force attacks
* comment spam & trackback spam
* contact form spam
* suspicious registrations
* suspicious xml-rpc requests (spam, attacks)
* referer spam (also known as referral/referrer spam)

== Screenshots ==

1. **Dashboard**: Access Watch’s unique dashboard brings key metrics omitted by other analytics tools. Requests per second, human v robot traffic split and threat level breakdown are key indicators of your website’s performance and security.
2. **Robots**: Access Watch let’s you visualize your website’s most active robots in an innovative interface. Clear and easy to read, the more requests a robot makes, the larger it’s graphical representation. You’ll quickly spot which robots generate the most activity on your website and if they are unnecessary or abusive.
3. **Events**: Access Watch informs you each time something relevant is happening on your website. From the ‘Events’ screen, you will be able to block suspicious robots performing an attack and get an overview of existing bad robots whose actions are already blocked.
4. **Requests**: Access Watch allows you to see all website requests in real-time. This granular data is critical when troubleshooting website issues. Access Watch augments traditional website logs with multiple layers of added intelligence to give you the full picture while saving valuable time and resources.

== Installation ==

= Assisted Installation =
Installation is free, quick, and easy. [Install Access Watch from our site with our assistant](https://access.watch/wordpress/install) in a few clicks.

= Manual Alternatives =
Alternatively, install Access Watch via the plugin directory, or upload the files manually to your server and follow the on-screen instructions. If you need additional help [read our step by step instructions](https://access.watch/wordpress/manual).

== Frequently Asked Questions ==

= Who's behind Access Watch? =

The Access Watch service is operated by “Access Watch Technologies GmbH”, a company based in Berlin (Germany) with an international team. Our servers are also currently located in Germany, so is your data.

= How does it work? =

After each HTTP request on your WordPress website, the metadata are logged to the Access Watch service. The data sent is commonly named “Access Logs”, it contains things like the client IP Address, the HTTP Method, the HTTP Headers and the URL.

= Is it free? =

Yes, a free version of Access Watch will always be available for the community! The free plan allows users to connect 1 website and processes up to 100,000 requests each month. If you would like to use Access Watch for multiple websites or a higher volume of requests we also offer paid plans to suit your needs, starting at just €19 per month. More information can be found on [our pricing page](https://access.watch/pricing).

= What about performance? =

By default, Access Watch is caching data in your website MySQL database using the "Transients API" from WordPress. While it works well for most websites, for demanding websites we suggest using an alternate WordPress object cache, such as the one provided by the "W3 Total Cache" plugin. Please let us know if there is any issue or if you need help!

== Changelog ==

= 2.0.0 =
*Release Date - 09 November 2018*

* End of life
* Disabling the plugin
* Sorry

= 1.2.5 =
*Release Date - 19 September 2017*

* Fix zoom with Firefox

= 1.2.4 =
*Release Date - 18 September 2017*

* Fix issue preventing the dashboard to initialize
* Fix dashboard issues with Firefox

= 1.2.0 =
*Release Date - 14 September 2017*

* Introduce the Zoom feature in the Dashboard

= 1.1.2 =
*Release Date - 24 August 2017*

* Slightly updated UI
* Ability to upgrade plan from the interface

= 1.0.1 =
*Release Date - 08 June 2017*

* Bug fix release

= 1.0.0 =
*Release Date - 29 May 2017*

* Completely new UI. Thank you Milan & Jean!
* New onboarding
* Remove robots.txt handling

= 0.6.1.2 =
*Release Date - 15 March 2017*

* UI update: detailed request improvements

= 0.6.1 =
*Release Date - 12 March 2017*

* UI update: detailed request panel

= 0.6.0 =
*Release Date - 27 February 2017*

* Updated user interface
* Get the name and description of the bad robots
* For top identified robots, link to our public robot database
* In the request screen, click on a request to get details on the agent
* We hope you'll like it! Tell us what you think!

= 0.5.10 =
*Release Date - 18 February 2017*

* change how the urls for plugin assets are generated

= 0.5.9 =
*Release Date - 13 February 2017*

* Handle uninstall hook

= 0.5.8 =
*Release Date - 26 January 2017*

* disable db execution time tracking

= 0.5.7 =
*Release Date - 23 January 2017*

* Happy New Year!
* update PHP libraries: updated session and feedback handling
* better track execution time: db and http breakdown

= 0.5.6 =
*Release Date - 1 December 2016*

* UI update: improve Agent logs panel

= 0.5.5 =
*Release Date - 28 November 2016*

* UI update: more events

= 0.5.4 =
*Release Date - 24 November 2016*

* UI update: more robot icons

= 0.5.3 =
*Release Date - 23 November 2016*

* UI update: robots filter

= 0.5.2 =
*Release Date - 21 November 2016*

* UI update: logs pagination

= 0.5.1 =
*Release Date - 11 November 2016*

* UI update: Real Time Requests with WebSocket

= 0.5.0 =
*Release Date - 9 November 2016*

* UI update: Add Worldmap to dashboard, Focus on Robots
