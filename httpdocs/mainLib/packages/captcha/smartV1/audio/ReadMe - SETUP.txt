- Audio & Visual CAPTCHA v1.3 -


First of all, feel free to use the script as long as you leave the comments at the top of the scripts untouched!!!
That´s all I ask!!

Regards,
Nicklas Swärdh

Any questions or comments? email me at nick@nswardh.com
or visit my site: www.nswardh.com





Upload the files to a folder on your server, CHMODE the 'MP3' folder and 'image' folder to 500.
This will prevent any unauthorized access to your *.mp3 and *.png files.

When you´re done, browse to DEMO.PHP for a demonstration... oh, yeah, and dont forget to turn on the speakers ;)

If you want to make your own mp3 files, then make sure they are in 22Khz bitrate.
For a live demo, please visit: http://www.nswardh.com/shout



If you want to change font in the Visual CAPTCHA, upload a truetype font and
make the changes in visual.php



====================================
     .:.:.: What´s New :.:.:.
====================================

2006-08-04 - v1.3
-----------------
As of v.1.3, Audio & Visual CAPTCHA is now compatible with PHP4 and higher.

To make it harder to bruteforce the CAPTCHA, lowercase and uppercase letters has been added.
A few chars has been omitted, this is to prevent confusion between the Audio and Visual CAPTCHA.
The current chars are:

	0123456789acdefghijkmopqrstuvwxyACDEFGHJKLMOPQRSTUVWXY



2006-07-26 - v1.2
-----------------
By request, a visual CAPTCHA has now been added. (Check demo.php or the live demo at www.nswardh.com/shout)
Besides that, a minor bug with the loading of
the soundfiles has been fixed. Also changed from
fread() to stream_read_lines() wich reads the
files much faster (for PHP5 only!) ;)


2006-06-05 - v1.1
-----------------
Added a few different sounds (voices) to show how
you can make it way more difficult for
voice-recognition software from "picking" out the
generated code from the audio. This is done
by adding different voices and words between the
numbers.


2006-05-31 - v1.0
-----------------
First version och the Audio CAPTCHA.
Demonstrates how to stop spambots
using audio instead of an image.



