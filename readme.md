This is still a working draft....

How does this plugin work?
==========================

The challenge I faced when updating a plugin from GitHub was the naming of the zipball archive. When extracted the foldername is not of your choice.
But I do like to think that you should be able to dictate the foldername of your  plugin. So after some research I came up with the solution to download
the zipball, open it with the native php ZipArchive (WordPress uses this as well), rename the folder, save the zipball and offer that as the download.
To achieve this I had to let de update-url point to wp-admin where I supply the name of the github repo and the cookie required to login to wp-admin.
The admin_init hook will then take over and sets headers equal to that of a zipball from GitHub and sends the zipball, then cleans it up.

Now, though my research was pretty extensive (for my doing at least), I am not completely sure this is the best approach. So I any feedback is most welcome.

Other than that, this structure will allows a pretty native WordPress plugin update experience. We should be able to:

* Supply extra information before updating
* The plugin author can support this plugin directly or the end-user can add any repo to the functions.php
* Adding a GUI is also very possible and if this plugin is good in what it claims to do, it will be added

Again, any feedback and testing on various platforms/ configurations is very welcome.