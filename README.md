# PHP POS Print Server

**A [php](http://php.net/) application for printing POS receipts**


## To Use

To clone and run this repository you'll need [git](https://git-scm.com), [php](http://php.net/) and [composer](https://getcomposer.org/).

From your command line:

```bash

# Clone this repository
git clone https://github.com/Tecdiary/ppp

# Go into the repository
cd ppp

# Install dependencies
composer install

# Run the app from command line
php index.php
```

Next time you can simply run the `server.sh` file to start the server.

This app runs at port 6441 ( ws://lcoalhost:6441 ) and listen for the print jobs.
