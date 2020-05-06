# php-kodaksmarthome
php based access to the Kodak Smarthome API

Thanx to https://github.com/kairoaraujo/python-kodaksmarthome for the great work!

Everything should be working "out-of-the-box". Simply initiate the class with your credentials:

`$ksh = new phpKodakSmarthome('myUser', 'myPassword');`

You now cann access all kinds of information directly:

`$devices = $ksh->getDevices();`

`$events = $ksh->getEvents();`