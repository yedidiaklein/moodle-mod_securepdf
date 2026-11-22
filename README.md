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

## Security: only real PDF files are processed

ImageMagick picks its decoder from the first bytes of a file, not from its name.
A PostScript file renamed to `.pdf` would otherwise be run by the Ghostscript
PostScript interpreter, which has a long history of remote code execution bugs.

Since version 1.4 the plugin checks the real file content (`%PDF-` header) on
upload, when the activity is saved, and again before any file is passed to
ImageMagick. Files that are not real PDFs are rejected.

### Recommended ImageMagick policy

As defence in depth, allow ImageMagick to read PDF only and block the other
risky decoders. Add these lines inside `<policymap>` in your `policy.xml`
(e.g. `/etc/ImageMagick-6/policy.xml` or `/etc/ImageMagick-7/policy.xml`), and remove any
older line that blocks `PDF`:

```xml
<policy domain="coder" rights="none" pattern="{PS,PS2,PS3,EPS,EPI,EPSF,EPSI,EPT,XPS,MVG,MSL,SVG,TEXT,URL,HTTP,HTTPS,FTP}" />
<policy domain="coder" rights="read" pattern="PDF" />
<policy domain="path" rights="none" pattern="@*" />
```

Keep ImageMagick and Ghostscript updated with your OS security updates.
