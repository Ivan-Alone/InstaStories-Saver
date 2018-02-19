# InstaStories Saver

### [ReadMe на русском](https://github.com/Ivan-Alone/InstaStories-Saver/blob/master/README.md)

Program for saving Instagram Stories

All it's easy. You must download zip-archive of this repository and unpack it in any directory. Next you must install PHP:

* Windows 7-10: 

Download and install (if wasn't installed before) [VC2015 Redist](https://www.microsoft.com/en-US/download/details.aspx?id=48145). 

Download archive of [PHP 7.2.2](http://windows.php.net/downloads/releases/php-7.2.2-Win32-VC15-x86.zip), unpack it in any directory, next you must add PHP folder's address to **%Path%** (or you can unpack all files to **bin** folder of InstaStories' unpacked program), next you must download [php.ini](https://raw.githubusercontent.com/Ivan-Alone/imageres-storage/master/php.ini) and move it to folder with **php.exe** file

* Windows XP: 

Download and install (if wasn't installed before) [VC2008 Redist](https://www.microsoft.com/en-US/download/details.aspx?id=29). 

Download archive of [PHP 5.4.45](http://windows.php.net/downloads/releases/archives/php-5.4.45-Win32-VC9-x86.zip), unpack it in any directory, next you must add PHP folder's address to **%Path%** (or you can unpack all files to **bin** folder of InstaStories' unpacked program), next you must download [php.ini](https://raw.githubusercontent.com/Ivan-Alone/imageres-storage/master/php.ini) and move it to folder with **php.exe** file

* Linux Debian/Ubuntu: 

```sudo apt install php php-curl php-gd```

* Mac OS:

You must activate **Command Line Tools** package! PHP is default installed to your system. In any other case (Saver can't working), execute this command in Terminal:

```curl -s https://php-osx.liip.ch/install.sh | bash -s 7.2```


On \*NIX systems you must give permission for executing **InstaStories.sh**, for example:
```chmod +x InstaStories.sh```

Next run the main file, depending of OS (**InstaStories.sh** - **\*NIX**, **InstaStories.cmd** - **Windows**), and you can see following window:

![](https://ivan-alone.github.io/imageres-storage/%D0%A1%D0%BD%D0%B8%D0%BC%D0%BE%D0%BA%20%D1%8D%D0%BA%D1%80%D0%B0%D0%BD%D0%B0%20(65).png)

Boot screen (decorative only, however =) )

Next you'll see program:

![](https://ivan-alone.github.io/imageres-storage/%D0%A1%D0%BD%D0%B8%D0%BC%D0%BE%D0%BA%20%D1%8D%D0%BA%D1%80%D0%B0%D0%BD%D0%B0%20(66).png)

Enter your Instagram login and password and... This program downloads every accessible Stories from your feed! You'll can find their, sorted by user's names, in **Instagram** directory.

![](https://raw.githubusercontent.com/Ivan-Alone/imageres-storage/master/test_view.png)

Program remembers your authorisation data, on next times you'll can download Stories by simple running this program and waited some time.

If anything is broken, first of all remove **temp** folder and pass the authorisation again. If your problem doesn't leaves - create Issue here, on GitHub.

Thank all for using of my program!

UPD. Ubuntu Linux test screenshot
![](https://ivan-Alone.github.io/imageres-storage/onLinuxNew.png)
