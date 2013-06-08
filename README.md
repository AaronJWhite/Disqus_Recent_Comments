Disqus_Recent_Comments
======================

Pulls your site's recent Disqus comments.

Using the Script
----------------

This script should work on any server running PHP v5+. I haven't tested it on PHPv4, but I wouldn't be surprised if it worked on that as well. I think the script works best if it functions as it's own php page and has it's contents embedded via a php include, iframe, or ajax call. If you look at the echoComments you'll notice that all of the elements in the commentHtml string have a css class. This should make it easy for you to customize the look and feel of your recent comments.

Adding Your Parameters
----------------------

- **publicKey**: Your public Disqus api key.
- **forumName**: Forum name Disqus identifies your site by.
- **commentLimit**: Max amount of recent comments you want to fetch.
- **filterUsers**: A comma delimited list of *author names* whose comments you do not want to show in the recent comment list. No spaces before or after the commas.  $filterUsers = "One Name,Two Name" not $filterUsers = "One Name, SpaceBefore Name". **Note:** Author names are different from usernames in Disqus. The author name is what shows up when someone makes a comment.
- **filterLimit**: If filterUsers is used this is the numer of comments we ask the Disqus API to send back. By default the number zero is 3 times your comment limit
- **dateFormat**: Format you want to use for comment post dates. Check php manual for formatting options.
- **commentLength**: Max character count of comments
- **apiVersion**: Version of Disqus api
- **resource**: Disqus resource to grad data from. Probably want to leave this alone.
- **outputType**: Format of response from Disqus. You'll have to change script if you want to use xml.
