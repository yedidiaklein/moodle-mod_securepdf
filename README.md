# Secure PDF
----------
- Secure PDF module allow you to upload a PDF to your course, and prevent students from downloading it.
- Students will get an image of each page and not the PDF itself.
- The images are protected from "right click" to prevent saving the image.
- Module completion will be set only while user saw all pages of document.
- You must know that people with web development skills will be able to download the images (one by one)

# Install
---------
## Please note that you have to install a PHP module that is not needed by Moodle itself.
- Install php-imagick module on your system.
- (debian/ubuntu) apt-get install php-imagick
- (Redhat/Centos) yum install php-imagick
-  Configure imagemagick to allow PDF reading, Add &lt;policy domain="coder" rights="read" pattern="PDF"&gt;  to the policy at /etc/ImageMagick-6/policy.xml see more details here : https://stackoverflow.com/questions/52703123/override-default-imagemagick-policy-xml
- Restart php-fpm or your web server.
- cd [moodle]/mod/
- git clone https://github.com/yedidiaklein/moodle-mod_securepdf.git securepdf
- Go to your moodle Notification Page and install. 

# Use
-----
- Add securepdf module in your course.
- Add a PDF fle to the module and watch it.
- Note that first view of page will be slow (20-25 seconds), then it's will cached for other users.
- Enjoy! 

# License
---
- See the LICENSE file for licensing details.

# About
-----
- Secure PDF module was written by Yedidia Klein from OpenApp Israel.

# TODO
----
- Support upload of MS-PowerPoint files. (Please contact me about that..)
