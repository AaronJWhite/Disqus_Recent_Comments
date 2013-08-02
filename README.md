Disqus_Recent_Comments
======================

Pulls your site's recent Disqus comments.

Using the Code
----------------

This class should work on any server running PHP v5+. 
Simple create a new comment object like so:
```php
	$disqus = new RecentComments('[forum_name]', '[publikc_key]');
```
Now you have a comment object using the default options. Use the getComments functions to display your comments like so:
```php
	echo($disqus->getComments());
```
Alternatively you can pass an array to the contructor that will overwrite the default options like so:
```php
	$options= array("cache"=>false,"commentLimit"=>50);
	$disqus = new RecentComments('[FORUM_NAME]', '[PUBLIC_KEY]', $options);
```
The options that are avaialable are described below.


*Note: If you are looking for the older version of this code please review the version 1.0 branch.* 

Initial Options
----------------------

- **cache**: A boolean value that decides if a cache file should be used. The cache file will automaticly be created if one does not exist.
- **cacheFile**: A string of the cache file's path. By default the cacheFile is created in the same directory as the  class file with the name *recent_comments.cache*.
- **cacheTime**: A number which is the amount of time in minutes a cache file should be used. The default is 3 minutes.
- **commentLimit**: Max amount of recent comments you want to fetch.
- **filterUsers**: A comma delimited list of *author names* whose comments you do not want to show in the recent comment list. No spaces before or after the commas.  $filterUsers = "One Name,Two Name" not $filterUsers = "One Name, SpaceBefore Name". **Note:** Author names are different from usernames in Disqus. The author name is what shows up when someone makes a comment.
- **filterLimit**: If filterUsers is used this is the numer of comments we ask the Disqus API to send back. By default the number zero is 3 times your comment limit
- **dateFormat**: Format you want to use for comment post dates. Check php manual for formatting options.
- **useRelativeTime**: If set to true the script will ignore the dateFormat parameter and use relative times like "one hour ago"
- **commentLength**: Max character count of comments

Creating a Custom Template
----------------------
You can define the output for each comment by setting a template.
A template is just a string of HTML and brackets around specific keywords in all caps.
The example below would set a template that shows only the author name and avatar for each comment.
```php
	$disqus = new RecentComments('[forum_name]', '[publikc_key]');
	$strTemplate = '<div class="commentWrap"><span>{AUTHOR_NAME}</span><br/><img src="{AUTHOR_AVATAR}" alt="{AUTHOR_NAME}" /></div>';
	$disqus->setTemplate($strTemplate);
```
**The keywords available for the template function are:**
- **AUTHOR_NAME**: The commenter's name.
- **AUTHOR_PROFILE**: The URL to the commenter's profile.
- **AUTHOR_AVATAR**: The URL the to commenter's avatar.
- **MESSAGE**: The actualy comment.
- **THREAD_TITLE**: The title of the thread the comment belongs to.
- **THREAD_LINK**: The URL to the thread.
- **COMMENT_ID**: The ID of the comment.
- **POST_TIME**: The time the comment was posted.
- **ALTER**: The value of this keyword will alternate between being the string **alter** or a blank string.



